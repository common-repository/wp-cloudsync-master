<?php

declare(strict_types=1);

namespace OneTeamSoftware\WP\CloudSyncMaster\Queue;

class QueueNamesCreator implements QueueNamesCreatorInterface
{
	/**
	 * @param string $provider
	 * @param string $bucketName
	 * @return string
	 */
	public function createUploadQueueName(string $provider, string $bucketName): string
	{
		return $this->create($provider, $bucketName, 'upload');
	}

	/**
	 * @param string $provider
	 * @param string $bucketName
	 * @return string
	 */
	public function createFoldersToScanQueueName(string $provider, string $bucketName): string
	{
		return $this->create($provider, $bucketName, 'folders_to_scan');
	}

	/**
	 * returns a queue name for a given provider, bucket name and suffix
	 *
	 * @param string $provider
	 * @param string $bucketName
	 * @param string $suffix
	 * @return string
	 */
	private function create(string $provider, string $bucketName, string $suffix): string
	{
		return $provider . '_' . $bucketName . '_' . $suffix;
	}
}
