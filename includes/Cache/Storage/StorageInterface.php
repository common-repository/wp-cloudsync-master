<?php

declare(strict_types=1);

namespace OneTeamSoftware\Cache\Storage;

interface StorageInterface
{
	/**
	 * deletes an entry with a given key
	 *
	 * @param string $key
	 * @return boolean
	 */
	public function delete(string $key): bool;

	/**
	 * returns a value stored for a given key
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function get(string $key);

	/**
	 * sets a value for a given key for certain period of time
	 *
	 * @param string $key
	 * @param mixed $value
	 * @param int $expiresAfter (seconds)
	 * @return boolean
	 */
	public function set(string $key, $value, int $expiresAfter = -1): bool;
}
