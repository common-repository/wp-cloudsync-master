<?php

declare(strict_types=1);

namespace OneTeamSoftware\WP\CloudSyncMaster;

class PluginSettings implements PluginSettingsInterface
{
	/**
	 * @var int
	 */
	public const DEFAULT_HANDLE_UPLOAD_QUEUE_INTERVAL = 60;

	/**
	 * @var int
	 */
	public const DEFAULT_UPLOAD_BATCH_SIZE = 10;

	/**
	 * @var int
	 */
	public const DEFAULT_UPLOAD_CONCURRENCY = 1;

	/**
	 * @var int
	 */
	public const DEFAULT_FILL_UPLOAD_QUEUE_INTERVAL = 60;

	/**
	 * @var bool
	 */
	protected $createObjectOnFileUpload;

	/**
	 * @var bool
	 */
	protected $rewriteFileUrlWithObjectUrl;

	/**
	 * @var bool
	 */
	protected $useObjectUrlInAttachmentDialog;

	/**
	 * @var bool
	 */
	protected $deleteObjectOnFileDelete;

	/**
	 * @var bool
	 */
	protected $createObjectsForExistingFiles;

	/**
	 * @var bool
	 */
	protected $deleteFileAfterObjectCreated;

	/**
	 * @var int
	 */
	protected $fillUploadQueueInterval;

	/**
	 * @var int
	 */
	protected $handleUploadQueueInterval;

	/**
	 * @var int
	 */
	protected $uploadBatchSize;

	/**
	 * @var int
	 */
	protected $uploadConcurrency;

	/**
	 * @var array<ProviderAccountInterface>
	 */
	protected $providerAccounts;

	/**
	 * constructor.
	 *
	 * @param array $settings
	 */
	public function __construct(array $settings)
	{
		$this->createObjectOnFileUpload = filter_var($settings['createObjectOnFileUpload'] ?? false, FILTER_VALIDATE_BOOLEAN);
		$this->rewriteFileUrlWithObjectUrl = filter_var($settings['rewriteFileUrlWithObjectUrl'] ?? false, FILTER_VALIDATE_BOOLEAN);
		$this->useObjectUrlInAttachmentDialog = filter_var($settings['useObjectUrlInAttachmentDialog'] ?? false, FILTER_VALIDATE_BOOLEAN);
		$this->deleteObjectOnFileDelete = false;
		$this->deleteFileAfterObjectCreated = false;
		$this->createObjectsForExistingFiles = filter_var($settings['createObjectsForExistingFiles'] ?? false, FILTER_VALIDATE_BOOLEAN);
		$this->fillUploadQueueInterval = self::DEFAULT_FILL_UPLOAD_QUEUE_INTERVAL;
		$this->handleUploadQueueInterval = self::DEFAULT_HANDLE_UPLOAD_QUEUE_INTERVAL;
		$this->uploadBatchSize = self::DEFAULT_UPLOAD_BATCH_SIZE;
		$this->uploadConcurrency = self::DEFAULT_UPLOAD_CONCURRENCY;

		$this->providerAccounts = [];
		foreach (($settings['accounts'] ?? []) as $account) {
			$this->providerAccounts[] = new ProviderAccount($account);
		}
	}

	/**
	 * returns true if we should allow direct file uploads
	 *
	 * @return bool
	 */
	public function getCreateObjectOnFileUpload(): bool
	{
		return $this->createObjectOnFileUpload;
	}

	/**
	 * returns true if we should delete objects from cloud
	 *
	 * @return bool
	 */
	public function getDeleteObjectOnFileDelete(): bool
	{
		return $this->deleteObjectOnFileDelete;
	}

	/**
	 * returns true if we should delete files from local filesystem
	 *
	 * @return bool
	 */
	public function getDeleteFileAfterObjectCreated(): bool
	{
		return $this->deleteFileAfterObjectCreated;
	}

	/**
	 * returns true if we should rewrite media urls
	 *
	 * @return bool
	 */
	public function getRewriteFileUrlWithObjectUrl(): bool
	{
		return $this->rewriteFileUrlWithObjectUrl;
	}

	/**
	 * returns true if we should use the object url in the attachment dialog
	 *
	 * @return bool
	 */
	public function getUseObjectUrlInAttachmentDialog(): bool
	{
		return $this->useObjectUrlInAttachmentDialog;
	}

	/**
	 * returns true if we should create objects for existing files
	 *
	 * @return bool
	 */
	public function getCreateObjectsForExistingFiles(): bool
	{
		return $this->createObjectsForExistingFiles;
	}

	/**
	 * returns the interval in seconds between runs of the fill upload queue cron task
	 *
	 * @return int
	 */
	public function getFillUploadQueueInterval(): int
	{
		return $this->fillUploadQueueInterval;
	}

	/**
	 * returns the interval in seconds between runs of the handle upload queue cron task
	 *
	 * @return int
	 */
	public function getHandleUploadQueueInterval(): int
	{
		return $this->handleUploadQueueInterval;
	}

	/**
	 * returns the maximum number of files to upload in a single batch
	 *
	 * @return int
	 */
	public function getUploadBatchSize(): int
	{
		return $this->uploadBatchSize;
	}

	/**
	 * returns the maximum number of concurrent uploads
	 *
	 * @return int
	 */
	public function getUploadConcurrency(): int
	{
		return $this->uploadConcurrency;
	}

	/**
	 * returns a list of provider accounts
	 *
	 * @return array<ProviderAccountInterface>
	 */
	public function getProviderAccounts(): array
	{
		return $this->providerAccounts;
	}

	/**
	 * returns the default provider account
	 *
	 * @return ProviderAccountInterface|null
	 */
	public function getDefaultProviderAccount(): ?ProviderAccountInterface
	{
		if (empty($this->providerAccounts)) {
			return null;
		}

		return current($this->providerAccounts);
	}
}
