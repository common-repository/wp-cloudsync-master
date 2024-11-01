<?php

declare(strict_types=1);

namespace OneTeamSoftware\WP\CloudSyncMaster\Transformer;

use OneTeamSoftware\Cache\CacheInterface;
use OneTeamSoftware\Cache\KeyGeneratorInterface;
use OneTeamSoftware\Logger\LoggerInterface;

class CachedCloudUrlTransformer implements CloudUrlTransformerInterface
{
	/**
	 * @var CloudUrlTransformerInterface
	 */
	private $cloudUrlTransformer;

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
	 * @param CloudUrlTransformerInterface $cloudUrlTransformer
	 * @param CacheInterface $cache
	 * @param KeyGeneratorInterface $keyGenerator
	 * @param LoggerInterface $logger
	 */
	public function __construct(
		CloudUrlTransformerInterface $cloudUrlTransformer,
		CacheInterface $cache,
		KeyGeneratorInterface $keyGenerator,
		LoggerInterface $logger = null
	) {
		$this->cloudUrlTransformer = $cloudUrlTransformer;
		$this->cache = $cache;
		$this->keyGenerator = $keyGenerator;
		$this->logger = $logger ?? new NullLogger();
	}

	/**
	 * transform local URL to Cloud URL
	 *
	 * @param string $url
	 * @return string
	 */
	public function toCloudUrl(string $url): string
	{
		$cacheKey = 'cloudsync_' . $this->keyGenerator->getKey($url) . '_cloud_url_transformer';
		$cloudUrl = $this->cache->get($cacheKey);

		if (is_null($cloudUrl)) {
			$cloudUrl = $this->cloudUrlTransformer->toCloudUrl($url);
			$this->cache->set($cacheKey, $cloudUrl);
		} else {
			$this->logger->debug(__FILE__, __LINE__, 'Cache hit for %s', $url); // phpcs:ignore
		}

		if ($cloudUrl !== $url) {
			$this->logger->debug(__FILE__, __LINE__, 'Rewrote URL %s to %s', $url, $cloudUrl); // phpcs:ignore
		}

		return $cloudUrl;
	}
}
