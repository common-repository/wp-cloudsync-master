<?php

declare(strict_types=1);

namespace OneTeamSoftware\WP\CloudSyncMaster\Repository;

interface ProviderRepositoryInterface
{
	/**
	 * returns a list of providers
	 *
	 * @return array
	 */
	public function getProviders(): array;
}
