<?php

declare(strict_types=1);

namespace OneTeamSoftware\WP\CloudSyncMaster\ObjectLink;

interface ObjectLinkFactoryInterface
{
	/**
	 * create an object link from a file path
	 *
	 * @param string $filePath
	 * @return ObjectLinkInterface
	 */
	public function createFromFile(string $filePath): ObjectLinkInterface;
}
