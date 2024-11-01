<?php

namespace OneTeamSoftware\CloudStorage;

interface StorageManagerFactoryInterface
{
	/**
	 * creates a storage manager
	 *
	 * @param string $provider
	 * @param array $config
	 * @return StorageManagerInterface
	 */
	public function create(string $provider, array $config): StorageManagerInterface;
}
