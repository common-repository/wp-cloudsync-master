<?php

declare(strict_types=1);

namespace OneTeamSoftware\WP\CloudSyncMaster\Transformer;

interface CloudUrlReplacerInterface
{
	/**
	 * Replaces all image URLs with cloud URLs in a given HTML string
	 *
	 * @param string $html
	 * @return string
	 */
	public function replace(string $html): string;
}
