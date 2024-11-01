<?php

declare(strict_types=1);

namespace OneTeamSoftware\WP\CloudSyncMaster\Image;

use OneTeamSoftware\Cache\CacheInterface;
use OneTeamSoftware\Cache\KeyGeneratorInterface;
use OneTeamSoftware\Logger\LoggerInterface;
use OneTeamSoftware\Logger\NullLogger;

class CachedImageSizeFetcher implements ImageSizeFetcherInterface
{
	/**
	 * @var ImageSizeFetcherInterface
	 */
	private $imageSizeFetcher;

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
	 * @param ImageSizeFetcherInterface $imageSizeFetcher
	 * @param CacheInterface $cache
	 * @param KeyGeneratorInterface $keyGenerator
	 * @param LoggerInterface|null $logger
	 */
	public function __construct(
		ImageSizeFetcherInterface $imageSizeFetcher,
		CacheInterface $cache,
		KeyGeneratorInterface $keyGenerator,
		LoggerInterface $logger = null
	) {
		$this->imageSizeFetcher = $imageSizeFetcher;
		$this->cache = $cache;
		$this->keyGenerator = $keyGenerator;
		$this->logger = $logger ?? new NullLogger();
	}

	/**
	 * returns the image size
	 *
	 * @param string $url
	 * @return ImageSizeInfoInterface
	 */
	public function getImageSize(string $url): ImageSizeInfoInterface
	{
		$imageSize = null;

		$cacheKey = $this->keyGenerator->getKey($url) . '_image_size_fetcher';
		$imageSizeData = $this->cache->get($cacheKey);
		if (is_array($imageSizeData)) {
            $this->logger->debug(__FILE__, __LINE__, 'Cache hit for %s, size: %s', $url, print_r($imageSizeData, true)); // phpcs:ignore

			$imageSize = new ImageSizeInfo($imageSizeData);

			return $imageSize;
		}

		$imageSize = $this->imageSizeFetcher->getImageSize($url);
		$imageSizeData = $imageSize->toArray();

		$this->cache->set($cacheKey, $imageSizeData);

        $this->logger->debug(__FILE__, __LINE__, 'Image size of %s is %s', $url, print_r($imageSizeData, true)); // phpcs:ignore

		return $imageSize;
	}
}
