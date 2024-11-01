<?php

declare(strict_types=1);

namespace OneTeamSoftware\WP\CloudSyncMaster\Parser;

class UrlsParser implements UrlsParserInterface
{
	/**
	 * parses urls from html
	 *
	 * @param string $html
	 * @return array
	 */
	public function parseUrls(string $html): array
	{
		$urls = [];
		$offset = 0;
		while (preg_match('/https?:\/\/[^\s"<>]+/i', $html, $matches, PREG_OFFSET_CAPTURE, $offset)) {
			$url = $matches[0][0];
			$offset = $matches[0][1] + strlen($matches[0][0]);
			$urls[] = $url;
		}

		return $urls;
	}
}
