<?php

declare(strict_types=1);

namespace OneTeamSoftware\WP\CloudSyncMaster\CronTask;

use GuzzleHttp\Promise\Each;
use GuzzleHttp\Promise\PromiseInterface;
use OneTeamSoftware\CloudStorage\ObjectInterface;
use OneTeamSoftware\Logger\LoggerInterface;
use OneTeamSoftware\Queue\QueueInterface;
use OneTeamSoftware\Queue\QueueItemInterface;
use OneTeamSoftware\WP\CloudSyncMaster\ObjectLink\ObjectLink;
use OneTeamSoftware\WP\CloudSyncMaster\ObjectLink\ObjectLinkUploaderInterface;
use OneTeamSoftware\WP\CloudSyncMaster\PluginSettings;
use OneTeamSoftware\WP\CloudSyncMaster\PluginSettingsInterface;
use OneTeamSoftware\WP\CloudSyncMaster\ProviderAccountInterface;
use OneTeamSoftware\WP\CronTask\AbstractCronTask;

class HandleUploadQueueCronTask extends AbstractCronTask
{
	/**
	 * @var int
	 */
	protected $batchSize;

	/**
	 * @var int
	 */
	protected $concurrency;

	/**
	 * @var LoggerInterface
	 */
	protected $logger;

	/**
	 * @var QueueInterface
	 */
	protected $uploadQueue;

	/**
	 * @var ObjectLinkUploaderInterface
	 */
	protected $objectLinkUploader;

	/**
	 * constructor
	 *
	 * @param string $id
	 * @param LoggerInterface $logger
	 * @param QueueInterface $uploadQueue
	 * @param ObjectLinkUploaderInterface $objectLinkUploader
	 * @param PluginSettingsInterface $pluginSettings
	 * @param ProviderAccountInterface $providerAccount
	 */
	public function __construct(
		string $id,
		LoggerInterface $logger,
		QueueInterface $uploadQueue,
		ObjectLinkUploaderInterface $objectLinkUploader,
		PluginSettingsInterface $pluginSettings,
		ProviderAccountInterface $providerAccount
	) {
		parent::__construct(
			$id . '_' . $providerAccount->getProvider() . '_' . $providerAccount->getBucketName() . '_handle_upload_queue',
			PluginSettings::DEFAULT_HANDLE_UPLOAD_QUEUE_INTERVAL,
			sprintf(
				__('Handle Upload Queue (Provider: %s, Bucket: %s)', $id),
				$providerAccount->getProvider(),
				$providerAccount->getBucketName()
			)
		);

		$this->batchSize = PluginSettings::DEFAULT_UPLOAD_BATCH_SIZE;
		$this->concurrency = PluginSettings::DEFAULT_UPLOAD_CONCURRENCY;
		$this->logger = $logger;
		$this->providerAccount = $providerAccount;

		$this->uploadQueue = $uploadQueue;
		$this->objectLinkUploader = $objectLinkUploader;
	}

	/**
	 * executes the cron task
	 *
	 * @return void
	 */
	public function execute(): void
	{
		$this->logger->debug(__FILE__, __LINE__, 'Executing cron task: %s', $this->cronTaskId);

		$numberOfItemsToUpload = $this->getNumberOfItemsToUpload();

		$this->logger->debug(__FILE__, __LINE__, 'Uploading %d items from the upload queue. (batch size: %d, concurrency: %d)', $numberOfItemsToUpload, $this->batchSize, $this->concurrency); // phpcs:ignore

		$promises = [];
		while ($numberOfItemsToUpload-- > 0 && !$this->uploadQueue->isEmpty()) {
			$promises[] = $this->handleQueueItem($this->uploadQueue->dequeue());
		}

		Each::ofLimit($promises, $this->concurrency)->wait();
	}

	/**
	 * returns the number of items to upload
	 *
	 * @return int
	 */
	protected function getNumberOfItemsToUpload(): int
	{
		$numberOfItemsToUpload = $this->batchSize;
		if ($numberOfItemsToUpload > $this->uploadQueue->size()) {
			$numberOfItemsToUpload = $this->uploadQueue->size();
		}

		return $numberOfItemsToUpload;
	}

	/**
	 * handles a queue item
	 *
	 * @param QueueItemInterface $queueItem
	 * @return PromiseInterface
	 */
	protected function handleQueueItem(QueueItemInterface $queueItem): PromiseInterface
	{
		return $this->objectLinkUploader
			->upload(new ObjectLink($queueItem->toArray()))
			->then(function (ObjectInterface $object) use ($queueItem) {
				return $this->handleUploadResult($object, $queueItem);
			})
			->otherwise(function ($reason): void {
				$this->logger->error(__FILE__, __LINE__, 'Upload promise was rejected: %s', $reason);
			});
	}

	/**
	 * handles a failed upload
	 *
	 * @param ObjectInterface $object
	 * @param QueueItemInterface $queueItem
	 * @return ObjectInterface
	 */
	protected function handleUploadResult(ObjectInterface $object, QueueItemInterface $queueItem): ObjectInterface
	{
		if ($object->exists()) {
			$this->logger->debug(__FILE__, __LINE__, 'Successfully uploaded the queue item: %s', print_r($queueItem->toArray(), true));

			return $object;
		}

		$this->logger->debug(__FILE__, __LINE__, 'Failed to upload item: %s, we will enqueue it again', print_r($queueItem->toArray(), true));

		$this->uploadQueue->enqueue($queueItem);

		return $object;
	}
}
