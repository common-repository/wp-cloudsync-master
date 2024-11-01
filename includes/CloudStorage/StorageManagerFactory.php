<?php

namespace OneTeamSoftware\CloudStorage;

use OneTeamSoftware\Logger\LoggerInterface;
use OneTeamSoftware\Logger\NullLogger;

class StorageManagerFactory implements StorageManagerFactoryInterface
{
	/**
	 * @var ProviderStorageFactoryInterface
	 */
	private $providerStorageFactory;

	/**
	 * @var LoggerInterface
	 */
	private $logger;

	/**
	 * constructor.
	 *
	 * @param ProviderStorageFactoryInterface $providerStorageFactory
	 * @param LoggerInterface $logger
	 */
	public function __construct(
		ProviderStorageFactoryInterface $providerStorageFactory,
		LoggerInterface $logger = null
	) {
		$this->providerStorageFactory = $providerStorageFactory;
		$this->logger = $logger ?? new NullLogger();
	}

	/**
	 * creates a storage manager
	 *
	 * @param string $provider
	 * @param array $config
	 * @return StorageManagerInterface
	 */
	public function create(string $provider, array $config): StorageManagerInterface
	{
		$storage = $this->providerStorageFactory->create($provider)->create($config);

		return new StorageManager($storage, $this->logger);
	}
}
