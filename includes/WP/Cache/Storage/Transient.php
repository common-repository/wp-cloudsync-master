<?php

declare(strict_types=1);

namespace OneTeamSoftware\WP\Cache\Storage;

use OneTeamSoftware\Cache\Storage\StorageInterface;

/**
 * Transient class is used for caching data in the WordPress database for a specific period of time,
 * providing persistent caching that will be automatically loaded on all the pages.
 */
class Transient implements StorageInterface
{
	/**
	 * deletes entry with a given key
	 *
	 * @param string $key
	 * @return boolean
	 */
	public function delete(string $key): bool
	{
		$success = true;
		if (false === empty($key)) {
			$success = delete_transient($key);
		}

		// check if success is empty instead of a boolean because other plugins can overwrite the value so type is not guaranteed
		return !empty($success);
	}

	/**
	 * returns a value for a given key
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function get(string $key)
	{
		$value = null;
		if (false === empty($key)) {
			$value = get_transient($key);
			if (false === $value) {
				$value = null;
			}
		}

		return $value;
	}

	/**
	 * sets a value for a given key
	 *
	 * @param string $key
	 * @param mixed $value
	 * @param int $expiresAfter
	 * @return boolean
	 */
	public function set(string $key, $value, int $expiresAfter = -1): bool
	{
		$success = true;
		if (false === empty($key)) {
			delete_transient($key);
			$success = set_transient($key, $value, $expiresAfter <= 0 ? 0 : $expiresAfter);
		}

		// check if success is empty instead of a boolean because other plugins can overwrite the value so type is not guaranteed
		return !empty($success);
	}
}
