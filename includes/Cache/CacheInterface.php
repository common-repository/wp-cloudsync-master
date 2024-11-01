<?php

declare(strict_types=1);

namespace OneTeamSoftware\Cache;

interface CacheInterface extends Storage\StorageInterface
{
	/**
	 * sets whenever cache should be used
	 *
	 * @param bool $useCache
	 * @return void
	 */
	public function setUseCache(bool $useCache): void;

	/**
	 * sets default cache expiration time
	 *
	 * @param int $defaultExpiresAfter
	 * @return void
	 */
	public function setDefaultExpiresAfter(int $defaultExpiresAfter): void;

	/**
	 * returns true when cache has a value for a given key
	 *
	 * @param string $key
	 * @return true
	 */
	public function has(string $key): bool;
}
