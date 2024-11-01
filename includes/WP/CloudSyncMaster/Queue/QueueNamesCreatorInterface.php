<?php

declare(strict_types=1);

namespace OneTeamSoftware\WP\CloudSyncMaster\Queue;

interface QueueNamesCreatorInterface
{
	/**
	 * @param string $provider
	 * @param string $bucketName
	 * @return string
	 */
	public function createUploadQueueName(string $provider, string $bucketName): string;

	/**
	 * @param string $provider
	 * @param string $bucketName
	 * @return string
	 */
	public function createFoldersToScanQueueName(string $provider, string $bucketName): string;
}
