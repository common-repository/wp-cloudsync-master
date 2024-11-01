<?php

declare(strict_types=1);

namespace OneTeamSoftware\WP\CloudSyncMaster\Transformer;

use OneTeamSoftware\Cache\CacheInterface;
use OneTeamSoftware\Cache\KeyGeneratorInterface;
use OneTeamSoftware\Logger\LoggerInterface;
use OneTeamSoftware\Logger\NullLogger;

class CachedCloudUrlReplacer implements CloudUrlReplacerInterface
{
	/**
	 * @var CloudUrlReplacerInterface
	 */
	private $cloudUrlReplacer;

	/**
	 * @var CacheInterface
	 */
	private $cache;

	/**
	 * @var KeyGeneratorInterface
	 */
	private $keyGenerator;

	/**
	 * @var LoggerInterface
	 */
	private $logger;

	/**
	 * constructor.
	 *
	 * @param CloudUrlReplacerInterface $cloudUrlReplacer
	 * @param CacheInterface $cache
	 * @param KeyGeneratorInterface $keyGenerator
	 * @param LoggerInterface|null $logger
	 */
	public function __construct(
		CloudUrlReplacerInterface $cloudUrlReplacer,
		CacheInterface $cache,
		KeyGeneratorInterface $keyGenerator,
		LoggerInterface $logger = null
	) {
		$this->cloudUrlReplacer = $cloudUrlReplacer;
		$this->cache = $cache;
		$this->keyGenerator = $keyGenerator;
		$this->logger = $logger ?? new NullLogger();
	}

	/**
	 * Replaces all image URLs with cloud URLs in a given HTML string
	 *
	 * @param string $content
	 * @return string
	 */
	public function replace(string $content): string
	{
		$cacheKey = 'cloudsync_' . $this->keyGenerator->getKey($content) . '_cloud_url_replacer';
		$cloudContent = $this->cache->get($cacheKey);
		if (is_null($cloudContent)) {
			$cloudContent = $this->cloudUrlReplacer->replace($content);
			$this->cache->set($cacheKey, $cloudContent);
		} else {
			$this->logger->debug(__FILE__, __LINE__, 'Cache hit for %s', esc_html(substr($content, 0, 100))); // phpcs:ignore
		}

		if ($cloudContent !== $content) {
			$this->logger->debug(__FILE__, __LINE__, 'Rewrote content %s', esc_html(substr($content, 0, 100))); // phpcs:ignore
		}

		return $cloudContent;
	}
}
