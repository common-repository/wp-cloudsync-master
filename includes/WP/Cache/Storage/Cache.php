<?php

declare(strict_types=1);

namespace OneTeamSoftware\WP\Cache\Storage;

use OneTeamSoftware\Cache\Storage\StorageInterface;

/**
 * The data stored using this class is typically available for the duration of the page load
 * and can be accessed across different parts of your code within that page load.
 */
class Cache implements StorageInterface
{
	/**
	 * @var string
	 *
	 * The cache group. This is used to segment different types of cache.
	 * Cached data with the same key in different groups will be stored separately.
	 */
	private $group;

	/**
	 * constructor
	 *
	 * @param string $group
	 */
	public function __construct(string $group = '')
	{
		$this->group = $group;
	}

	/**
	 * deletes entry with a given key
	 *
	 * @param string $key
	 * @return boolean
	 */
	public function delete(string $key): bool
	{
		return wp_cache_delete($key, $this->group);
	}

	/**
	 * returns a value for a given key
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function get(string $key)
	{
		return wp_cache_get($key, $this->group);
	}

	/**
	 * sets a value for a given key
	 *
	 * @param string $key
	 * @param mixed $value
	 * @param int $expiresAfter Time until expiration in seconds from now. Default < 0 (no expiration).
	 * @return boolean
	 */
	public function set(string $key, $value, int $expiresAfter = -1): bool
	{
		return wp_cache_set($key, $value, $this->group, $expiresAfter <= 0 ? 0 : $expiresAfter);
	}
}
