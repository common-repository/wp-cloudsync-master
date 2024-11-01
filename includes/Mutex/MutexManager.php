<?php

declare(strict_types=1);

namespace OneTeamSoftware\Mutex;

class MutexManager implements MutexManagerInterface
{
	/**
	 * @var MutexInterface[]
	 */
	private $mutexes = [];

	/**
	 * adds a mutex to the manager
	 *
	 * @param string $name
	 * @param MutexInterface $mutex
	 * @return void
	 */
	public function addMutex(string $name, MutexInterface $mutex): void
	{
		$this->mutexes[$name] = $mutex;
	}

	/**
	 * locks a mutex by name
	 *
	 * @param string $name
	 * @param bool $nonBlocking
	 * @return bool
	 */
	public function lock(string $name, bool $nonBlocking = false): bool
	{
		if (!isset($this->mutexes[$name])) {
			return false;
		}

		return $this->mutexes[$name]->lock($nonBlocking);
	}

	/**
	 * unlocks a mutex by name
	 *
	 * @param string $name
	 * @return bool
	 */
	public function unlock(string $name): bool
	{
		if (!isset($this->mutexes[$name])) {
			return false;
		}

		return $this->mutexes[$name]->unlock();
	}
}
