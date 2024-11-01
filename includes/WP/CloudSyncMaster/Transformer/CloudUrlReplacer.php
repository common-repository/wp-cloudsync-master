<?php

declare(strict_types=1);

namespace OneTeamSoftware\WP\CloudSyncMaster\Transformer;

use OneTeamSoftware\Logger\LoggerInterface;
use OneTeamSoftware\WP\CloudSyncMaster\Parser\UrlsParserInterface;

class CloudUrlReplacer implements CloudUrlReplacerInterface
{
	/**
	 * @var CloudUrlTransformerInterface
	 */
	private $cloudUrlTransformer;

	/**
	 * @var UrlsParserInterface
	 */
	private $urlsParser;

	/**
	 * @var LoggerInterface
	 */
	private $logger;

	/**
	 * constructor.
	 *
	 * @param CloudUrlTransformerInterface $cloudUrlTransformer
	 * @param UrlsParserInterface $urlsParser
	 * @param LoggerInterface $logger
	 */
	public function __construct(
		CloudUrlTransformerInterface $cloudUrlTransformer,
		UrlsParserInterface $urlsParser,
		LoggerInterface $logger
	) {
		$this->cloudUrlTransformer = $cloudUrlTransformer;
		$this->urlsParser = $urlsParser;
		$this->logger = $logger;
	}

	/**
	 * Replaces all image URLs with cloud URLs in a given HTML string
	 *
	 * @param string $content
	 * @return string
	 */
	public function replace(string $content): string
	{
		$this->logger->debug(__FILE__, __LINE__, 'Replacing local URLs with cloud URLs');

		$urls = $this->urlsParser->parseUrls($content);
		foreach ($urls as $url) {
			$cloudUrl = $this->cloudUrlTransformer->toCloudUrl($url);
			if ($cloudUrl !== $url) {
				$this->logger->debug(__FILE__, __LINE__, 'Replacing URL: %s with cloud URL: %s', $url, $cloudUrl);

				$content = str_replace($url, $cloudUrl, $content);
			}
		}

		return $content;
	}
}
