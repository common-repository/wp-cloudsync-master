<?php

namespace OneTeamSoftware\CloudStorage;

use OneTeamSoftware\Logger\LoggerInterface;
use OneTeamSoftware\Logger\NullLogger;

class ProviderStorageFactory implements ProviderStorageFactoryInterface
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
	 * returns a storage factory for a given provider
	 *
	 * @param string $provider
	 * @return StorageFactoryInterface
	 */
	public function create(string $provider): StorageFactoryInterface
	{
		$provider = ucfirst(strtolower($provider));

		$className = __NAMESPACE__ . '\\' . $provider . '\\' . $provider . 'StorageFactory';
		if (!class_exists($className)) {
			return new NullStorageFactory($this->logger);
		}

		$storageFactory = new $className($this->logger);
		if (!($storageFactory instanceof StorageFactoryInterface)) {
			return new NullStorageFactory($this->logger);
		}

		return $storageFactory;
	}
}
