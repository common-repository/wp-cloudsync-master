<?php

namespace OneTeamSoftware\CloudStorage;

use OneTeamSoftware\Logger\LoggerInterface;

interface StorageFactoryInterface
{
	/**
	 * constructor.
	 *
	 * @param LoggerInterface $logger
	 */
	public function __construct(LoggerInterface $logger = null);

	/**
	 * returns a storage instance
	 *
	 * @param array $config
	 * @return StorageInterface
	 */
	public function create(array $config): StorageInterface;
}
