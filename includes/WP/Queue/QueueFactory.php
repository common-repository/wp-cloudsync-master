<?php

declare(strict_types=1);

namespace OneTeamSoftware\WP\Queue;

use OneTeamSoftware\Queue\QueueFactoryInterface;
use OneTeamSoftware\Queue\QueueInterface;

class QueueFactory implements QueueFactoryInterface
{
	/**
	 * @var string
	 */
	private $id;

	/**
	 * constructor
	 *
	 * @param string $id
	 */
	public function __construct(string $id)
	{
		$this->id = $id;
	}

	/**
	 * returns a queue with the given name
	 *
	 * @param string $queueName
	 * @return QueueInterface
	 */
	public function create(string $queueName): QueueInterface
	{
		return new Queue($this->id, $queueName);
	}
}
