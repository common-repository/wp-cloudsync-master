<?php

declare(strict_types=1);

namespace OneTeamSoftware\WP\CloudSyncMaster\Transformer;

interface RelativePathTransformerInterface
{
	/**
	 * transforms path to relative path
	 *
	 * @param string $path
	 * @return string
	 */
	public function toRelativePath(string $path): string;
}
