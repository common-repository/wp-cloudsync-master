<?php

declare(strict_types=1);

namespace OneTeamSoftware\WP\CloudSyncMaster\Parser;

class MultiUrlsParser implements UrlsParserInterface
{
	/**
	 * @var UrlsParserInterface[]
	 */
	private $parsers = [];

	/**
	 * adds a UrlParserInterface instance to the list of parsers.
	 *
	 * @param UrlsParserInterface $parser
	 * @return MultiUrlsParser
	 */
	public function withUrlParser(UrlsParserInterface $parser): MultiUrlsParser
	{
		$this->parsers[] = $parser;

		return $this;
	}

	/**
	 * parses URLs from the HTML using multiple parsers.
	 *
	 * @param string $html
	 * @return array
	 */
	public function parseUrls(string $html): array
	{
		$urls = [];

		foreach ($this->parsers as $parser) {
			$parsedUrls = $parser->parseUrls($html);
			$urls = array_merge($urls, $parsedUrls);
		}

		// Remove duplicate URLs and reindex the array
		$urls = array_values(array_unique($urls));

		return $urls;
	}
}
