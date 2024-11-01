<?php

declare(strict_types=1);

namespace OneTeamSoftware\WP\CloudSyncMaster\ObjectLink;

use GuzzleHttp\Promise\PromiseInterface;

interface ObjectLinkDeleterInterface
{
	/**
	 * deletes an object
	 *
	 * @param ObjectLinkInterface $objectLink
	 * @return PromiseInterface
	 */
	public function delete(ObjectLinkInterface $objectLink): PromiseInterface;
}
