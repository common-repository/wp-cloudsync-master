<?php

declare(strict_types=1);

namespace OneTeamSoftware\Mutex;

interface MutexManagerInterface
{
	/**
	 * adds a mutex to the manager
	 *
	 * @param string $name
	 * @param MutexInterface $mutex
	 * @return void
	 */
	public function addMutex(string $name, MutexInterface $mutex): void;

	/**
	 * locks a mutex by name
	 *
	 * @param string $name
	 * @param bool $nonBlocking
	 * @return bool
	 */
	public function lock(string $name, bool $nonBlocking = false): bool;

	/**
	 * unlocks a mutex by name
	 *
	 * @param string $name
	 * @return bool
	 */
	public function unlock(string $name): bool;
}
