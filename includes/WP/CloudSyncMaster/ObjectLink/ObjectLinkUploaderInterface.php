<?php

declare(strict_types=1);

namespace OneTeamSoftware\WP\CloudSyncMaster\ObjectLink;

use GuzzleHttp\Promise\PromiseInterface;

interface ObjectLinkUploaderInterface
{
	/**
	 * uploads a file
	 *
	 * @param ObjectLinkInterface $objectLink
	 * @return PromiseInterface
	 */
	public function upload(ObjectLinkInterface $objectLink): PromiseInterface;
}
