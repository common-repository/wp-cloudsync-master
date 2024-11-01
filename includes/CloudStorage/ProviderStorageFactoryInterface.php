<?php

namespace OneTeamSoftware\CloudStorage;

interface ProviderStorageFactoryInterface
{
	/**
	 * returns a storage factory for a given provider
	 *
	 * @param string $provider
	 * @return StorageFactoryInterface
	 */
	public function create(string $provider): StorageFactoryInterface;
}
