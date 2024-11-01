<?php

declare(strict_types=1);

namespace OneTeamSoftware\WP\CloudSyncMaster\Parser;

class HtmlUrlsParser implements UrlsParserInterface
{
	/**
	 * parses image urls from html
	 *
	 * @param string $html
	 * @return array
	 */
	public function parseUrls(string $html): array
	{
		$urls = [];
		$offset = 0;

		// A regular expression pattern to match URLs within standard HTML attributes
		$pattern = '/(?:href|src|action|cite|data|manifest|poster)=["\']([^"\']+)[\"\']/i';

		while (preg_match($pattern, $html, $matches, PREG_OFFSET_CAPTURE, $offset)) {
			$url = $matches[1][0];
			$offset = $matches[0][1] + strlen($matches[0][0]);
			$urls[] = $url;
		}

		return $urls;
	}
}
