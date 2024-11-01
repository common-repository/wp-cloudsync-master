<?php

declare(strict_types=1);

namespace OneTeamSoftware\WP\CloudSyncMaster\Repository;

interface ObjectLinkRepositoryFactoryInterface
{
	/**
	 * returns a object link repository for a given option key
	 *
	 * @param string $provider
	 * @param string $bucketName
	 * @return ObjectLinkRepositoryInterface
	 */
	public function create(string $provider, string $bucketName): ObjectLinkRepositoryInterface;
}
