<?php

declare(strict_types=1);

namespace OneTeamSoftware\WP\CloudSyncMaster;

interface PluginSettingsInterface
{
	/**
	 * returns true if we should allow direct file uploads
	 *
	 * @return bool
	 */
	public function getCreateObjectOnFileUpload(): bool;

	/**
	 * returns true if we should rewrite media urls
	 *
	 * @return bool
	 */
	public function getRewriteFileUrlWithObjectUrl(): bool;

	/**
	 * returns true if we should use the object url in the attachment dialog
	 *
	 * @return bool
	 */
	public function getUseObjectUrlInAttachmentDialog(): bool;

	/**
	 * returns true if we should delete objects from cloud
	 *
	 * @return bool
	 */
	public function getDeleteObjectOnFileDelete(): bool;

	/**
	 * returns true if we should delete files from local filesystem
	 *
	 * @return bool
	 */
	public function getDeleteFileAfterObjectCreated(): bool;

	/**
	 * returns true if we should create objects for existing files
	 *
	 * @return bool
	 */
	public function getCreateObjectsForExistingFiles(): bool;

	/**
	 * returns the interval in seconds between runs of the fill upload queue cron task
	 *
	 * @return int
	 */
	public function getFillUploadQueueInterval(): int;

	/**
	 * returns the interval in seconds between runs of the handle upload queue cron task
	 *
	 * @return int
	 */
	public function getHandleUploadQueueInterval(): int;

	/**
	 * returns the maximum number of files to upload in a single batch
	 *
	 * @return int
	 */
	public function getUploadBatchSize(): int;

	/**
	 * returns the maximum number of concurrent uploads
	 *
	 * @return int
	 */
	public function getUploadConcurrency(): int;

	/**
	 * returns a list of provider accounts
	 *
	 * @return array<ProviderAccountInterface>
	 */
	public function getProviderAccounts(): array;

	/**
	 * returns the default provider account
	 *
	 * @return ProviderAccountInterface|null
	 */
	public function getDefaultProviderAccount(): ?ProviderAccountInterface;
}
