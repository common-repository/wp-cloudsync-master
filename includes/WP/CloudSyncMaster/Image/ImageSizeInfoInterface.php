<?php

declare(strict_types=1);

namespace OneTeamSoftware\WP\CloudSyncMaster\Image;

interface ImageSizeInfoInterface
{
	/**
	 * returns the width of the image
	 *
	 * @return float
	 */
	public function getWidth(): float;

	/**
	 * returns the height of the image
	 *
	 * @return float
	 */
	public function getHeight(): float;

	/**
	 * returns the mime type of the image
	 *
	 * @return string
	 */
	public function getMime(): string;

	/**
	 * returns the image size as an array
	 *
	 * @return array
	 */
	public function toArray(): array;
}
