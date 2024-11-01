<?php

declare(strict_types=1);

namespace OneTeamSoftware\WP\CloudSyncMaster\CronTask;

use OneTeamSoftware\CloudStorage\ProviderAclFactoryInterface;
use OneTeamSoftware\CloudStorage\StorageManagerFactoryInterface;
use OneTeamSoftware\CloudStorage\StorageManagerInterface;
use OneTeamSoftware\Logger\LoggerInterface;
use OneTeamSoftware\Queue\QueueFactoryInterface;
use OneTeamSoftware\Queue\QueueInterface;
use OneTeamSoftware\WP\CloudSyncMaster\Image\ImageSizeFetcher;
use OneTeamSoftware\WP\CloudSyncMaster\ObjectLink\ObjectLinkFactory;
use OneTeamSoftware\WP\CloudSyncMaster\ObjectLink\ObjectLinkFactoryInterface;
use OneTeamSoftware\WP\CloudSyncMaster\ObjectLink\ObjectLinkUploader;
use OneTeamSoftware\WP\CloudSyncMaster\ObjectLink\ObjectLinkUploaderInterface;
use OneTeamSoftware\WP\CloudSyncMaster\PluginSettingsInterface;
use OneTeamSoftware\WP\CloudSyncMaster\ProviderAccountInterface;
use OneTeamSoftware\WP\CloudSyncMaster\Queue\QueueNamesCreatorInterface;
use OneTeamSoftware\WP\CloudSyncMaster\Repository\ObjectLinkRepositoryFactoryInterface;
use OneTeamSoftware\WP\CloudSyncMaster\Repository\ObjectLinkRepositoryInterface;
use OneTeamSoftware\WP\CloudSyncMaster\Transformer\RelativePathTransformerInterface;

class CronTasksManager
{
	/**
	 * @var string
	 */
	protected $id;

	/**
	 * @var string
	 */
	protected $basePath;

	/**
	 * @var LoggerInterface
	 */
	protected $logger;

	/**
	 * @var RelativePathTransformerInterface
	 */
	protected $relativePathTransformer;

	/**
	 * @var StorageManagerFactoryInterface
	 */
	protected $storageManagerFactory;

	/**
	 * @var ProviderAclFactoryInterface
	 */
	protected $providerAclFactory;

	/**
	 * @var QueueNamesCreatorInterface
	 */
	protected $queueNamesCreator;

	/**
	 * @var QueueFactoryInterface
	 */
	protected $queueFactory;

	/**
	 * @var ObjectLinkRepositoryFactoryInterface
	 */
	protected $objectLinkRepositoryFactory;

	/**
	 * @var PluginSettingsInterface
	 */
	protected $pluginSettings;

	/**
	 * constructor
	 *
	 * @param string $id
	 * @param string $basePath
	 * @param LoggerInterface $logger
	 * @param RelativePathTransformerInterface $relativePathTransformer
	 * @param StorageManagerFactoryInterface $storageManagerFactory
	 * @param ProviderAclFactoryInterface $providerAclFactory
	 * @param QueueNamesCreatorInterface $queueNamesCreator
	 * @param QueueFactoryInterface $queueFactory
	 * @param ObjectLinkRepositoryFactoryInterface $objectLinkRepositoryFactory
	 * @param PluginSettingsInterface $pluginSettings
	 */
	public function __construct(
		string $id,
		string $basePath,
		LoggerInterface $logger,
		RelativePathTransformerInterface $relativePathTransformer,
		StorageManagerFactoryInterface $storageManagerFactory,
		ProviderAclFactoryInterface $providerAclFactory,
		QueueNamesCreatorInterface $queueNamesCreator,
		QueueFactoryInterface $queueFactory,
		ObjectLinkRepositoryFactoryInterface $objectLinkRepositoryFactory,
		PluginSettingsInterface $pluginSettings
	) {
		$this->id = $id;
		$this->basePath = rtrim($basePath, '/') . '/'; // typically it will be: /var/www/html/
		$this->logger = $logger;
		$this->relativePathTransformer = $relativePathTransformer;
		$this->storageManagerFactory = $storageManagerFactory;
		$this->providerAclFactory = $providerAclFactory;
		$this->queueNamesCreator = $queueNamesCreator;
		$this->queueFactory = $queueFactory;
		$this->objectLinkRepositoryFactory = $objectLinkRepositoryFactory;
		$this->pluginSettings = $pluginSettings;
	}

	/**
	 * registers cron tasks
	 *
	 * @return void
	 */
	public function register(): void
	{
		if (!$this->pluginSettings->getCreateObjectsForExistingFiles()) {
			$this->logger->error(__FILE__, __LINE__, 'Creation of the objects for existing files is disabled');
			return;
		}

		$providerAccounts = $this->pluginSettings->getProviderAccounts();
		foreach ($providerAccounts as $providerAccount) {
			$this->registerForProviderAccount($providerAccount);
		}
	}

	/**
	 * registers cron tasks for provider account
	 *
	 * @param ProviderAccountInterface $providerAccount
	 * @return void
	 */
	protected function registerForProviderAccount(ProviderAccountInterface $providerAccount): void
	{
		if (empty($providerAccount->getConfig()) || empty($providerAccount->getBucketName())) {
			$this->logger->error(__FILE__, __LINE__, 'Provider or settings are not configured');
			return;
		}

		$this->logger->debug(__FILE__, __LINE__, 'registerForProviderAccount, provider: %d, bucket: %s', $providerAccount->getProvider(), $providerAccount->getBucketName()); // phpcs:ignore

		$uploadQueueName = $this->queueNamesCreator->createUploadQueueName(
			$providerAccount->getProvider(),
			$providerAccount->getBucketName()
		);

		$uploadQueue = $this->queueFactory->create($uploadQueueName);

		$objectLinkRepository = $this->objectLinkRepositoryFactory->create(
			$providerAccount->getProvider(),
			$providerAccount->getBucketName()
		);

		$storageManager = $this->storageManagerFactory->create(
			$providerAccount->getProvider(),
			$providerAccount->getConfig()
		);

		$objectLinkFactory = new ObjectLinkFactory(
			$this->logger,
			$this->relativePathTransformer,
			$providerAccount
		);

		// implement ability to choose which method to use for filling the queue
		$this->initFillUplloadQueueFromMediaLibrartCronTask(
			$providerAccount,
			$uploadQueue,
			$objectLinkFactory,
			$objectLinkRepository
		);

		$aclFactory = $this->providerAclFactory->create($providerAccount->getProvider());
		$objectLinkUploader = new ObjectLinkUploader(
			$this->basePath,
			$this->logger,
			$aclFactory,
			new ImageSizeFetcher($this->logger),
			$objectLinkRepository,
			$storageManager,
			$this->pluginSettings->getDeleteFileAfterObjectCreated()
		);

		$this->initHandleUploadQueueCronTask(
			$providerAccount,
			$uploadQueue,
			$objectLinkUploader,
			$objectLinkRepository,
			$storageManager
		);
	}

	/**
	 * initializes fill upload queue from media library cron task
	 *
	 * @param ProviderAccountInterface $providerAccount
	 * @param QueueInterface $uploadQueue
	 * @param ObjectLinkFactoryInterface $objectLinkFactory
	 * @param ObjectLinkRepositoryInterface $objectLinkRepository
	 * @return void
	 */
	protected function initFillUplloadQueueFromMediaLibrartCronTask(
		ProviderAccountInterface $providerAccount,
		QueueInterface $uploadQueue,
		ObjectLinkFactoryInterface $objectLinkFactory,
		ObjectLinkRepositoryInterface $objectLinkRepository
	): void {
		(new FillUploadQueueFromMediaLibraryCronTask(
			$this->id,
			$this->logger,
			$this->relativePathTransformer,
			$uploadQueue,
			$objectLinkFactory,
			$objectLinkRepository,
			$this->pluginSettings,
			$providerAccount
		));
	}

	/**
	 * initializes handle upload queue cron task
	 *
	 * @param ProviderAccountInterface $providerAccount
	 * @param QueueInterface $uploadQueue
	 * @param ObjectLinkUploaderInterface $objectLinkUploader
	 * @param ObjectLinkRepositoryInterface $objectLinkRepository
	 * @param StorageManagerInterface $storageManager
	 * @return void
	 */
	protected function initHandleUploadQueueCronTask(
		ProviderAccountInterface $providerAccount,
		QueueInterface $uploadQueue,
		ObjectLinkUploaderInterface $objectLinkUploader,
		ObjectLinkRepositoryInterface $objectLinkRepository,
		StorageManagerInterface $storageManager
	): void {
		(new HandleUploadQueueCronTask(
			$this->id,
			$this->logger,
			$uploadQueue,
			$objectLinkUploader,
			$this->pluginSettings,
			$providerAccount
		));
	}
}
