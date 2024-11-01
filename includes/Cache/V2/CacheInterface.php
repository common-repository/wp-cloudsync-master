<?php

declare(strict_types=1);

namespace OneTeamSoftware\Cache\V2;

use OneTeamSoftware\Cache\CacheInterface as CacheCacheInterface;

interface CacheInterface extends CacheCacheInterface
{
	/**
	 * sets whenever cache should be stored
	 *
	 * @param bool $storeCache
	 * @return void
	 */
	public function setStoreCache(bool $storeCache): void;
}
