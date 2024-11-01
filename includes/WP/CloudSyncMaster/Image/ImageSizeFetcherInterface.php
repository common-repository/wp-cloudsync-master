<?php

declare(strict_types=1);

namespace OneTeamSoftware\WP\CloudSyncMaster\Image;

interface ImageSizeFetcherInterface
{
	/**
	 * returns the image size
	 *
	 * @param string $url
	 * @return ImageSizeInfoInterface
	 */
	public function getImageSize(string $url): ImageSizeInfoInterface;
}
