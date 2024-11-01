<?php

declare(strict_types=1);

namespace OneTeamSoftware\Cache\Storage;

class InMemory implements StorageInterface
{
	/**
	 * @var string
	 */
	private const KEY_VALUE = 'value';

	/**
	 * @var int
	 */
	private const KEY_EXPIRES_AT = 'expires_at';

	/**
	 * @var StorageInterface
	 */
	private $storage;

	/**
	 * @var string
	 */
	private $group;

	/**
	 * @var array
	 */
	private $data;

	/**
	 * @var bool
	 */
	private $changed;

	/**
	 * @var int
	 */
	private $maxExpiresAfter;

	/**
	 * constructor
	 *
	 * @param StorageInterface $storage
	 * @param string $group
	 */
	public function __construct(StorageInterface $storage, string $group = 'default')
	{
		$this->storage = $storage;
		$this->group = $group;
		$this->data = null;
		$this->changed = false;
		$this->maxExpiresAfter = 0;
	}

	/**
	 * saves data to the storage
	 */
	public function __destruct()
	{
		$this->save();
	}

	/**
	 * deletes an entry with a given key
	 *
	 * @param string $key
	 * @return boolean
	 */
	public function delete(string $key): bool
	{
		$this->load();

		if (isset($this->data[$key])) {
			unset($this->data[$key]);
			$this->changed = true;
		}

		return true;
	}

	/**
	 * returns a value stored for a given key
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function get(string $key)
	{
		$this->load();

		if (!isset($this->data[$key][self::KEY_VALUE])) {
			return null;
		}

		$expiresAt = $this->data[$key][self::KEY_EXPIRES_AT] ?? 0;
		if ($expiresAt > 0 && $expiresAt < time()) {
			unset($this->data[$key][self::KEY_VALUE]);
			return null;
		}

		return $this->data[$key][self::KEY_VALUE];
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
		$this->load();

		if ($expiresAfter < 0) {
			$expiresAfter = 0;
		}

		$this->data[$key] = [
			self::KEY_VALUE => $value,
			self::KEY_EXPIRES_AT => time() + $expiresAfter
		];

		if ($expiresAfter > $this->maxExpiresAfter) {
			$this->maxExpiresAfter = $expiresAfter;
		}

		$this->changed = true;

		return true;
	}

	/**
	 * loads cache from storage
	 *
	 * @return void
	 */
	private function load(): void
	{
		if (!is_null($this->data)) {
			return;
		}

		$this->data = (array)($this->storage->get($this->group) ?? []);
	}

	/**
	 * saves the data
	 *
	 * @return boolean
	 */
	private function save(): bool
	{
		$this->removeExpiredItems();

		if (!$this->changed) {
			return true;
		}

		$this->changed = false;

		return $this->storage->set($this->group, $this->data, $this->getMaxExpiresAfter());
	}

	/**
	 * removes expired items
	 *
	 * @return void
	 */
	private function removeExpiredItems(): void
	{
		if (!is_array($this->data)) {
			return;
		}

		foreach ($this->data as $key => $value) {
			if (isset($value[self::KEY_EXPIRES_AT]) && $value[self::KEY_EXPIRES_AT] > 0 && $value[self::KEY_EXPIRES_AT] < time()) {
				unset($this->data[$key]);
				$this->changed = true;
			}
		}
	}

	/**
	 * returns the maximum expires after value
	 *
	 * @return integer
	 */
	private function getMaxExpiresAfter(): int
	{
		$maxExpiresAt = 0;

		foreach ($this->data as $value) {
			if (isset($value[self::KEY_EXPIRES_AT]) && $value[self::KEY_EXPIRES_AT] > $maxExpiresAt) {
				$maxExpiresAt = $value[self::KEY_EXPIRES_AT];
			}
		}

		$maxExpiresAfter = $maxExpiresAt - time();
		if ($maxExpiresAfter < $this->maxExpiresAfter) {
			$maxExpiresAfter = $this->maxExpiresAfter;
		}

		return $maxExpiresAfter;
	}
}
