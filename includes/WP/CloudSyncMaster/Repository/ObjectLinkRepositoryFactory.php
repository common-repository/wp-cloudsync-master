<?php

declare(strict_types=1);

namespace OneTeamSoftware\WP\CloudSyncMaster\Repository;

use OneTeamSoftware\Logger\LoggerInterface;
use wpdb;

class ObjectLinkRepositoryFactory implements ObjectLinkRepositoryFactoryInterface
{
	/**
	 * @var string
	 */
	private $id;

	/**
	 * @var wpdb
	 */
	private $wpdb;

	/**
	 * @var LoggerInterface
	 */
	private $logger;

	/**
	 * constructor
	 *
	 * @param string $id
	 * @param wpdb $wpdb
	 * @param LoggerInterface $logger
	 */
	public function __construct(string $id, wpdb $wpdb, LoggerInterface $logger)
	{
		$this->id = $id;
		$this->wpdb = $wpdb;
		$this->logger = $logger;
	}

	/**
	 * returns a object link repository for a given option key
	 *
	 * @param string $provider
	 * @param string $bucketName
	 * @return ObjectLinkRepositoryInterface
	 */
	public function create(string $provider, string $bucketName): ObjectLinkRepositoryInterface
	{
		return new ObjectLinkRepository(
			$this->id,
			$provider . '_' . $bucketName,
			$this->wpdb,
			$this->logger
		);
	}
}
