<?php

declare(strict_types=1);

namespace OneTeamSoftware\WP\CloudSyncMaster\Parser;

interface UrlsParserInterface
{
	/**
	 * parses image urls from html
	 *
	 * @param string $html
	 * @return array
	 */
	public function parseUrls(string $html): array;
}
