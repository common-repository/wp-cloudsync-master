<?php

declare(strict_types=1);

namespace OneTeamSoftware\WP\CloudSyncMaster\Image;

use OneTeamSoftware\Logger\LoggerInterface;
use OneTeamSoftware\Logger\NullLogger;

class ImageSizeFetcher implements ImageSizeFetcherInterface
{
	/**
	 * @var LoggerInterface
	 */
	private $logger;

	/**
	 * constructor.
	 *
	 * @param LoggerInterface|null $logger
	 */
	public function __construct(LoggerInterface $logger = null)
	{
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
		$this->logger->debug(__FILE__, __LINE__, 'Fetching image size for %s', $url);

		if (!$this->isImage($url)) {
			$this->logger->debug(__FILE__, __LINE__, 'URL %s is not an image', $url);
			return new ImageSizeInfo([]);
		}

		$imageSize = @getimagesize($url);
		if ($imageSize === false) {
			$this->logger->debug(__FILE__, __LINE__, 'Failed to get image size for %s', $url);

			return new ImageSizeInfo([]);
		}

		$this->logger->debug(__FILE__, __LINE__, 'Image size for %s is %dx%d and mime is %s', $url, $imageSize[0], $imageSize[1], $imageSize['mime']); // phpcs:ignore

		return new ImageSizeInfo([
			ImageSizeInfo::KEY_WIDTH => $imageSize[0],
			ImageSizeInfo::KEY_HEIGHT => $imageSize[1],
			ImageSizeInfo::KEY_MIME => $imageSize['mime'],
		]);
	}

	/**
	 * checks if the given url is an image based on its extension
	 *
	 * @param string $url
	 * @return bool
	 */
	private function isImage(string $url): bool
	{
		$imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg', 'ico', 'tif', 'tiff'];
		$extension = strtolower(pathinfo($url, PATHINFO_EXTENSION));
		return in_array($extension, $imageExtensions, true);
	}
}
