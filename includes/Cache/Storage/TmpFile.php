<?php

declare(strict_types=1);

namespace OneTeamSoftware\Cache\Storage;

class TmpFile implements StorageInterface
{
	/**
	 * @var string
	 */
	private $basePath;

	/**
	 * constructor
	 */
	public function __construct()
	{
		$this->basePath = sys_get_temp_dir();
	}

	/**
	 * deletes an entry with a given key
	 *
	 * @param string $key
	 * @return boolean
	 */
	public function delete(string $key): bool
	{
		$success = true;

		$filePath = $this->getFilePath($key);
		if (file_exists($filePath)) {
			$success = unlink($filePath);
		}

		return $success;
	}

	/**
	 * returns a value stored for a given key
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function get(string $key)
	{
		$value = null;
		if ($this->isCacheAlive($key)) {
			$filePath = $this->getFilePath($key);
			if (file_exists($filePath)) {
				$value = unserialize(file_get_contents($filePath));
			}
		}

		return $value;
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
		$filePath = $this->getFilePath($key);
		$numberOfBytes = file_put_contents($filePath, serialize($value));

		if ($expiresAfter > 0) {
			$expiresAfterFilePath = $this->getExpiresAfterFilePath($key);
			file_put_contents($expiresAfterFilePath, time() + $expiresAfter);
		}

		return $numberOfBytes > 0;
	}

	/**
	 * checks if cache for a given key still alive
	 *
	 * @param string $key
	 * @return boolean
	 */
	private function isCacheAlive(string $key): bool
	{
		$isCacheAlive = false;
		$expiresAfterFilePath = $this->getExpiresAfterFilePath($key);
		if (file_exists($expiresAfterFilePath)) {
			$expiresAfter = intval(file_get_contents($expiresAfterFilePath));

			if ($expiresAfter > time()) {
				$isCacheAlive = true;
			}
		} elseif (file_exists($this->getFilePath($key))) {
			$isCacheAlive = true;
		}

		return $isCacheAlive;
	}

	/**
	 * creturns a path to a cache file for a given key
	 *
	 * @param string $key
	 * @return string
	 */
	private function getFilePath(string $key): string
	{
		return $this->basePath . '/' . $key;
	}

	/**
	 * returns a path to a file which stores when cache should expire
	 *
	 * @param string $key
	 * @return string
	 */
	private function getExpiresAfterFilePath(string $key): string
	{
		return $this->getFilePath($key) . '_expiresAfter';
	}
}
