<?php

declare(strict_types=1);

namespace OneTeamSoftware\WP\Queue;

use OneTeamSoftware\Queue\QueueInterface;
use OneTeamSoftware\Queue\QueueItem;
use OneTeamSoftware\Queue\QueueItemInterface;

class Queue implements QueueInterface
{
	/**
	 * @var \wpdb
	 */
	private $wpdb;

	/**
	 * @var string
	 */
	private $tableName;

	/**
	 * @var string
	 */
	private $queueName;

	/**
	 * constructor.
	 *
	 * @param string $id
	 * @param string $queueName
	 */
	public function __construct(string $id, string $queueName)
	{
		global $wpdb;

		$this->wpdb = $wpdb;
		$this->tableName = $wpdb->prefix . preg_replace('/-/', '_', $id) . '_queue';
		$this->queueName = $queueName;
	}

	/**
	 * adds an item to the end of the queue
	 *
	 * @param QueueItemInterface $item
	 * @return void
	 */
	public function enqueue(QueueItemInterface $item): void
	{
		$hash = $item->getHash();

		$query = sprintf(
			'INSERT IGNORE INTO `%s` (`queue`, `hash`, `data`) VALUES (%%s, %%s, %%s)',
			esc_sql($this->tableName)
		);
		$this->wpdb->query($this->wpdb->prepare($query, $this->queueName, $hash, json_encode($item->toArray())));
	}

	/**
	 * removes an item from the front of the queue
	 *
	 * @return QueueItemInterface
	 */
	public function dequeue(): QueueItemInterface
	{
		$query = sprintf(
			'SELECT * FROM `%s` WHERE `queue` = %%s ORDER BY `id` ASC LIMIT 1 FOR UPDATE',
			esc_sql($this->tableName)
		);

		$row = $this->wpdb->get_row($this->wpdb->prepare($query, $this->queueName), ARRAY_A);
		if (empty($row)) {
			return new QueueItem([]);
		}

		$query = sprintf(
			'DELETE FROM `%s` WHERE `id` = %%d',
			esc_sql($this->tableName)
		);

		$this->wpdb->query($this->wpdb->prepare($query, $row['id']));

		return new QueueItem(json_decode($row['data'], true));
	}

	/**
	 * returns the item at the front of the queue without removing it
	 *
	 * @return QueueItemInterface
	 */
	public function peek(): QueueItemInterface
	{
		$query = sprintf(
			'SELECT * FROM `%s` WHERE `queue` = %%s ORDER BY `id` ASC LIMIT 1',
			esc_sql($this->tableName)
		);

		$row = $this->wpdb->get_row($this->wpdb->prepare($query, $this->queueName), ARRAY_A);
		if (empty($row)) {
			return new QueueItem([]);
		}

		return new QueueItem(json_decode($row['data'], true));
	}

	/**
	 * returns the number of items in the queue
	 *
	 * @return int
	 */
	public function size(): int
	{
		$query = sprintf(
			'SELECT COUNT(*) FROM `%s` WHERE `queue` = %%s',
			esc_sql($this->tableName)
		);

		return (int) $this->wpdb->get_var($this->wpdb->prepare($query, $this->queueName));
	}

	/**
	 * returns true if the queue is empty
	 *
	 * @return bool
	 */
	public function isEmpty(): bool
	{
		return $this->size() === 0;
	}

	/**
	 * returns queue as an array
	 *
	 * @param int $maxItems
	 * @return array
	 */
	public function toArray(int $maxItems = 0): array
	{
		$query = sprintf(
			'SELECT * FROM `%s` WHERE `queue` = %%s ORDER BY `id` ASC',
			esc_sql($this->tableName)
		);

		if ($maxItems > 0) {
			$query = $this->wpdb->prepare($query . ' LIMIT %d', $this->queueName, $maxItems);
		} else {
			$query = $this->wpdb->prepare($query, $this->queueName);
		}

		$rows = $this->wpdb->get_results($query, ARRAY_A);
		$items = [];
		foreach ($rows as $row) {
			$items[] = json_decode($row['data'], true);
		}
		return $items;
	}

	/**
	 * clears the queue
	 *
	 * @return void
	 */
	public function clear(): void
	{
		$query = sprintf(
			'DELETE FROM `%s` WHERE `queue` = %%s',
			esc_sql($this->tableName)
		);
		$this->wpdb->query($this->wpdb->prepare($query, $this->queueName));
	}
}
