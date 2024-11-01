<?php

declare(strict_types=1);

namespace OneTeamSoftware\Cache;

class Cache implements CacheInterface
{
	/**
	 * @var Storage\StorageInterface
	 */
	private $storage;

	/**
	 * @var bool
	 */
	private $useCache;

	/**
	 * @var int
	 */
	private $defaultExpiresAfter;

	/**
	 * constructor
	 *
	 * @param Storage\StorageInterface $storage
	 * @param int $defaultExpiresAfter
	 */
	public function __construct(Storage\StorageInterface $storage, int $defaultExpiresAfter = 0)
	{
		$this->storage = $storage;
		$this->useCache = true;
		$this->defaultExpiresAfter = $defaultExpiresAfter;
	}

	/**
	 * sets whenever cache should be used
	 *
	 * @param bool $useCache
	 * @return void
	 */
	public function setUseCache(bool $useCache): void
	{
		$this->useCache = $useCache;
	}

	/**
	 * sets default cache expiration time
	 *
	 * @param int $defaultExpiresAfter
	 * @return void
	 */
	public function setDefaultExpiresAfter(int $defaultExpiresAfter): void
	{
		$this->defaultExpiresAfter = $defaultExpiresAfter;
	}

	/**
	 * deletes an entry with a given key
	 *
	 * @param string $key
	 * @return boolean
	 */
	public function delete(string $key): bool
	{
		return $this->storage->delete($key);
	}

	/**
	 * returns true when cache has a value for a given key
	 *
	 * @param string $key
	 * @return true
	 */
	public function has(string $key): bool
	{
		return null !== $this->get($key);
	}

	/**
	 * returns a value stored for a given key
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function get(string $key)
	{
		if ($this->useCache) {
			return $this->storage->get($key);
		}

		return null;
	}

	/**
	 * sets a value for a given key for certain period of time
	 *
	 * @param string $key
	 * @param mixed $value
	 * @param int $expiresAfter (seconds)
	 * @return boolean
	 */
	public function set(string $key, $value, int $expiresAfter = -1): bool
	{
		if ($expiresAfter < 0) {
			$expiresAfter = $this->defaultExpiresAfter;
		}

		return $this->storage->set($key, $value, $expiresAfter);
	}
}
