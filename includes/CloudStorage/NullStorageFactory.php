<?php

namespace OneTeamSoftware\CloudStorage;

use OneTeamSoftware\Logger\LoggerInterface;
use OneTeamSoftware\Logger\NullLogger;

class NullStorageFactory implements StorageFactoryInterface
{
	/**
	 * @var LoggerInterface
	 */
	private $logger;

	/**
	 * constructor.
	 *
	 * @param LoggerInterface $logger
	 */
	public function __construct(LoggerInterface $logger = null)
	{
		$this->logger = $logger ?? new NullLogger();
	}

	/**
	 * returns a storage instance
	 *
	 * @param array $config
	 * @param LoggerInterface $logger
	 * @return StorageInterface
	 */
	public function create(array $config, LoggerInterface $logger = null): StorageInterface
	{
		$this->logger->error(__FILE__, __LINE__, 'Creating null storage instance');

		return new NullStorage();
	}
}
