<?php

declare(strict_types=1);

namespace OneTeamSoftware\WP\CloudSyncMaster\CronTask;

use OneTeamSoftware\Logger\LoggerInterface;
use OneTeamSoftware\Queue\QueueInterface;
use OneTeamSoftware\Queue\QueueItem;
use OneTeamSoftware\WP\CloudSyncMaster\ObjectLink\ObjectLinkFactoryInterface;
use OneTeamSoftware\WP\CloudSyncMaster\PluginSettings;
use OneTeamSoftware\WP\CloudSyncMaster\PluginSettingsInterface;
use OneTeamSoftware\WP\CloudSyncMaster\ProviderAccountInterface;
use OneTeamSoftware\WP\CloudSyncMaster\Repository\ObjectLinkRepositoryInterface;
use OneTeamSoftware\WP\CloudSyncMaster\Transformer\RelativePathTransformerInterface;
use OneTeamSoftware\WP\CronTask\AbstractCronTask;
use WP_Query;

class FillUploadQueueFromMediaLibraryCronTask extends AbstractCronTask
{
	/**
	 * @var LoggerInterface
	 */
	protected $logger;

	/**
	 * @var RelativePathTransformerInterface
	 */
	protected $relativePathTransformer;

	/**
	 * @var ObjectLinkFactoryInterface
	 */
	protected $objectLinkFactory;

	/**
	 * @var ObjectLinkRepositoryFactoryInterface
	 */
	protected $objectLinkRepositoryFactory;

	/**
	 * @var QueueInterface
	 */
	protected $uploadQueue;

	/**
	 * @var ObjectLinkRepositoryInterface
	 */
	protected $objectLinkRepository;

	/**
	 * @var ProviderAccountInterface
	 */
	protected $providerAccount;

	/**
	 * @var int
	 */
	protected $batchSize;

	/**
	 * constructor
	 *
	 * @param string $id
	 * @param LoggerInterface $logger
	 * @param RelativePathTransformerInterface $relativePathTransformer
	 * @param QueueInterface $uploadQueue
	 * @param ObjectLinkFactoryInterface $objectLinkFactory
	 * @param ObjectLinkRepositoryInterface $objectLinkRepository
	 * @param PluginSettingsInterface $pluginSettings
	 * @param ProviderAccountInterface $providerAccount
	 */
	public function __construct(
		string $id,
		LoggerInterface $logger,
		RelativePathTransformerInterface $relativePathTransformer,
		QueueInterface $uploadQueue,
		ObjectLinkFactoryInterface $objectLinkFactory,
		ObjectLinkRepositoryInterface $objectLinkRepository,
		PluginSettingsInterface $pluginSettings,
		ProviderAccountInterface $providerAccount
	) {
		// Call parent constructor
		parent::__construct(
			$id . '_' . $providerAccount->getProvider() . '_' . $providerAccount->getBucketName() . '_fill_upload_queue',
			PluginSettings::DEFAULT_FILL_UPLOAD_QUEUE_INTERVAL,
			sprintf(__('Fill Upload Queue (Provider: %s, Bucket: %s)', $id), $providerAccount->getProvider(), $providerAccount->getBucketName())
		);

		// Initialize properties
		$this->logger = $logger;
		$this->relativePathTransformer = $relativePathTransformer;
		$this->uploadQueue = $uploadQueue;
		$this->objectLinkFactory = $objectLinkFactory;
		$this->objectLinkRepository = $objectLinkRepository;
		$this->providerAccount = $providerAccount;
		$this->batchSize = PluginSettings::DEFAULT_UPLOAD_BATCH_SIZE;
	}

	/**
	 * Execute the cron task.
	 *
	 * @return void
	 */
	public function execute(): void
	{
		$this->logger->debug(__FILE__, __LINE__, 'Executing cron task: %s', $this->cronTaskId);

		// Get the offset from the options. Default to 0 if not set.
		$offset = get_option($this->getUploadQueueOffsetKey(), 0);

		$query = new WP_Query([
			'post_type' => 'attachment',
			'post_status' => 'inherit',
			'posts_per_page' => $this->batchSize,
			'offset' => $offset,
			'orderby' => 'ID',
			'order' => 'ASC',
		]);

		// If no posts are found, reset the offset to 0 and return
		if (!$query->have_posts()) {
			update_option($this->getUploadQueueOffsetKey(), 0);
			return;
		}

		$posts = $query->get_posts();

		$numberOfEnqueuedAttachments = 0;
		foreach ($posts as $post) {
			$this->enqueueAttachmentById($post->ID);
			$numberOfEnqueuedAttachments++;
		}

		// Update the offset in the options.
		update_option($this->getUploadQueueOffsetKey(), $offset + $numberOfEnqueuedAttachments);
	}

	/**
	 * returns upload queue offset key
	 *
	 * @return string
	 */
	protected function getUploadQueueOffsetKey(): string
	{
		return $this->cronTaskId . '_upload_queue_offset';
	}

	/**
	 * Enqueue attachment by ID.
	 *
	 * @param int $attachmentId
	 * @return void
	 */
	protected function enqueueAttachmentById(int $attachmentId): void
	{
		$attachmentPath = realpath(get_attached_file($attachmentId));
		if ($attachmentPath === false) {
			return;
		}

		$this->logger->debug(__FILE__, __LINE__, 'Enqueuing attachment (ID: %d, Path: %s) to upload queue', $attachmentId, $attachmentPath); // phpcs:ignore

		$this->enqueueFile($attachmentPath);
		$this->enqueueAttachmentThumbnailsById($attachmentId);
	}

	/**
	 * Enqueue attachment thumbnails by ID.
	 *
	 * @param int $attachmentId
	 * @return void
	 */
	protected function enqueueAttachmentThumbnailsById(int $attachmentId): void
	{
		$metadata = wp_get_attachment_metadata($attachmentId);
		if (empty($metadata['sizes']) || !is_array($metadata['sizes'])) {
			return;
		}

		$this->logger->debug(__FILE__, __LINE__, 'Enqueuing attachment thumbnails (ID: %d) to upload queue', $attachmentId); // phpcs:ignore

		$uploadDir = wp_upload_dir();
		$baseDir = $uploadDir['basedir'] ?? '';

		foreach ($metadata['sizes'] as $size) {
			// Construct the path of the resized image
			$resizedImagePath = $baseDir . '/' . dirname($metadata['file']) . '/' . $size['file'];

			$this->enqueueFile($resizedImagePath);
		}
	}

	/**
	 * enqueues a file for upload
	 *
	 * @param string $filePath
	 * @return void
	 */
	protected function enqueueFile(string $filePath): void
	{
		$filePath = realpath($filePath);
		if (!$filePath || !$this->shouldUploadFile($filePath)) {
			return;
		}

		$this->logger->debug(__FILE__, __LINE__, 'Enqueuing file for upload: %s', $filePath);

		$objectLink = $this->objectLinkFactory->createFromFile($filePath);
		if (empty($objectLink->getFilePath())) {
			$this->logger->debug(__FILE__, __LINE__, 'File %s is not a valid file, so skipping it', $filePath);
			return;
		}

		$queueItem = new QueueItem($objectLink->toArray());

		$this->uploadQueue->enqueue($queueItem);
	}

	/**
	 * checks if the file should be uploaded
	 *
	 * @param string $filePath
	 * @return bool
	 */
	protected function shouldUploadFile(string $filePath): bool
	{
		if (!file_exists($filePath) || !is_file($filePath) || !is_readable($filePath)) {
			$this->logger->debug(__FILE__, __LINE__, 'File %s does not exist, is not a file or not readable, so skipping it', $filePath); // phpcs:ignore
			return false;
		}

		$relativeFilePath = $this->relativePathTransformer->toRelativePath($filePath);

		// Check if the file is already in the repository
		if (!$this->objectLinkRepository->has($relativeFilePath)) {
			return true;
		}

		// Check if the file has been modified since it was last uploaded
		$objectLink = $this->objectLinkRepository->get($relativeFilePath);
		if ($objectLink->getFileUpdatedTime() !== filemtime($filePath)) {
			return true;
		}

		$this->logger->debug(__FILE__, __LINE__, 'File has not been modified since last upload: %s', $relativeFilePath);

		return false;
	}
}
