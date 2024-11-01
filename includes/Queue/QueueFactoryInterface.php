<?php

declare(strict_types=1);

namespace OneTeamSoftware\Queue;

interface QueueFactoryInterface
{
	/**
	 * returns a queue with the given name
	 *
	 * @param string $queueName
	 * @return QueueInterface
	 */
	public function create(string $queueName): QueueInterface;
}
