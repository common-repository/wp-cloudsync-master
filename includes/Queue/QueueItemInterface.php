<?php

declare(strict_types=1);

namespace OneTeamSoftware\Queue;

interface QueueItemInterface
{
	/**
	 * returns a unique hash for the item
	 *
	 * @return string
	 */
	public function getHash(): string;

	/**
	 * returns the item as an array
	 *
	 * @return array
	 */
	public function toArray(): array;
}
