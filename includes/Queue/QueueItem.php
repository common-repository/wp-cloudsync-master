<?php

declare(strict_types=1);

namespace OneTeamSoftware\Queue;

class QueueItem implements QueueItemInterface
{
	/**
	 * @var array
	 */
	private $data;

	/**
	 * QueueItem constructor.
	 *
	 * @param array $data
	 */
	public function __construct(array $data)
	{
		$this->data = $data;
	}

	/**
	 * returns a unique hash for the item
	 *
	 * @return string
	 */
	public function getHash(): string
	{
		return md5(json_encode($this->data));
	}

	/**
	 * returns the item as an array
	 *
	 * @return array
	 */
	public function toArray(): array
	{
		return $this->data;
	}
}
