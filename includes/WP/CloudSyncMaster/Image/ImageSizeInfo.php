<?php

declare(strict_types=1);

namespace OneTeamSoftware\WP\CloudSyncMaster\Image;

class ImageSizeInfo implements ImageSizeInfoInterface
{
	/**
	 * @var string
	 */
	public const KEY_WIDTH = 'width';

	/**
	 * @var string
	 */
	public const KEY_HEIGHT = 'height';

	/**
	 * @var string
	 */
	public const KEY_MIME = 'mime';

	/**
	 * @var float
	 */
	private $width;

	/**
	 * @var float
	 */
	private $height;

	/**
	 * @var string
	 */
	private $mime;

	/**
	 * constructor
	 *
	 * @param array $data
	 */
	public function __construct(array $data)
	{
		$this->width = floatval($data[self::KEY_WIDTH] ?? 0);
		$this->height = floatval($data[self::KEY_HEIGHT] ?? 0);
		$this->mime = $data[self::KEY_MIME] ?? '';
	}

	/**
	 * returns the width of the image
	 *
	 * @return float
	 */
	public function getWidth(): float
	{
		return $this->width;
	}

	/**
	 * returns the height of the image
	 *
	 * @return float
	 */
	public function getHeight(): float
	{
		return $this->height;
	}

	/**
	 * returns the mime type of the image
	 *
	 * @return string
	 */
	public function getMime(): string
	{
		return $this->mime;
	}

	/**
	 * returns the image size as an array
	 *
	 * @return array
	 */
	public function toArray(): array
	{
		return [
			self::KEY_WIDTH => $this->width,
			self::KEY_HEIGHT => $this->height,
			self::KEY_MIME => $this->mime
		];
	}
}
