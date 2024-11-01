<?php

declare(strict_types=1);

namespace OneTeamSoftware\Queue;

interface QueueInterface
{
	/**
	 * adds an item to the end of the queue
	 *
	 * @param QueueItemInterface $item
	 * @return void
	 */
	public function enqueue(QueueItemInterface $item): void;

	/**
	 * removes and returns the item at the front of the queue
	 *
	 * @return QueueItemInterface
	 */
	public function dequeue(): QueueItemInterface;

	/**
	 * returns the item at the front of the queue without removing it
	 *
	 * @return QueueItemInterface
	 */
	public function peek(): QueueItemInterface;

	/**
	 * returns the number of items in the queue
	 *
	 * @return int
	 */
	public function size(): int;

	/**
	 * returns true if the queue is empty
	 *
	 * @return bool
	 */
	public function isEmpty(): bool;

	/**
	 * returns queue as an array
	 *
	 * @param int $maxItems
	 * @return array
	 */
	public function toArray(int $maxItems = 0): array;

	/**
	 * clears the queue
	 *
	 * @return void
	 */
	public function clear(): void;
}
