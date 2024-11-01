<?php

declare(strict_types=1);

namespace OneTeamSoftware\WP\CloudSyncMaster\ObjectLink;

class ObjectLink implements ObjectLinkInterface
{
	/**
	 * @var string
	 */
	public const FILE_PATH_KEY = 'file_path';

	/**
	 * @var int
	 */
	public const FILE_UPDATED_TIME_KEY = 'file_updated_time';

	/**
	 * @var string
	 */
	public const BUCKET_NAME_KEY = 'bucket_name';

	/**
	 * @var string
	 */
	public const OBJECT_NAME_KEY = 'object_name';

	/**
	 * @var int
	 */
	public const OBJECT_UPDATED_TIME_KEY = 'object_updated_time';

	/**
	 * @var string
	 */
	public const OBJECT_PUBLIC_URL_KEY = 'object_public_url';

	/**
	 * @var string
	 */
	public const META_DATA_KEY = 'meta_data';

	/**
	 * @var string
	 */
	public const META_DATA_SIZE_KEY = 'size';

	/**
	 * @var string
	 */
	private $filePath;

	/**
	 * @var int
	 */
	private $fileUpdatedTime;

	/**
	 * @var string
	 */
	private $bucketName;

	/**
	 * @var string
	 */
	private $objectName;

	/**
	 * @var int
	 */
	private $objectUpdatedTime;

	/**
	 * @var string
	 */
	private $objectPublicUrl;

	/**
	 * @var array
	 */
	private $metaData;

	/**
	 * constructor
	 *
	 * @param array $data
	 */
	public function __construct(array $data)
	{
		$this->filePath = $data[self::FILE_PATH_KEY] ?? '';
		$this->fileUpdatedTime = intval($data[self::FILE_UPDATED_TIME_KEY] ?? 0);

		if (empty($this->fileUpdatedTime) && file_exists($this->filePath)) {
			$this->fileUpdatedTime = filemtime($this->filePath);
		}

		$this->bucketName = $data[self::BUCKET_NAME_KEY] ?? '';

		$this->objectName = $data[self::OBJECT_NAME_KEY] ?? '';
		$this->objectUpdatedTime = intval($data[self::OBJECT_UPDATED_TIME_KEY] ?? 0);
		$this->objectPublicUrl = $data[self::OBJECT_PUBLIC_URL_KEY] ?? '';
		$this->metaData = $data[self::META_DATA_KEY] ?? [];
	}

	/**
	 * returns the local file path
	 *
	 * @return string
	 */
	public function getFilePath(): string
	{
		return $this->filePath;
	}

	/**
	 * returns the local file updated time
	 *
	 * @return int
	 */
	public function getFileUpdatedTime(): int
	{
		return $this->fileUpdatedTime;
	}

	/**
	 * returns the bucket name
	 *
	 * @return string
	 */
	public function getBucketName(): string
	{
		return $this->bucketName;
	}

	/**
	 * returns the remote object name
	 *
	 * @return string
	 */
	public function getObjectName(): string
	{
		return $this->objectName;
	}

	/**
	 * returns the remote object updated time
	 *
	 * @return int
	 */
	public function getObjectUpdatedTime(): int
	{
		return $this->objectUpdatedTime;
	}

	/**
	 * returns the remote object public url
	 *
	 * @return string
	 */
	public function getObjectPublicUrl(): string
	{
		return $this->objectPublicUrl;
	}

	/**
	 * returns the meta data
	 *
	 * @return array
	 */
	public function getMetaData(): array
	{
		return $this->metaData;
	}

	/**
	 * returns the item as an array
	 *
	 * @return array
	 */
	public function toArray(): array
	{
		return [
			self::FILE_PATH_KEY => $this->filePath,
			self::FILE_UPDATED_TIME_KEY => $this->fileUpdatedTime,
			self::BUCKET_NAME_KEY => $this->bucketName,
			self::OBJECT_NAME_KEY => $this->objectName,
			self::OBJECT_UPDATED_TIME_KEY => $this->objectUpdatedTime,
			self::OBJECT_PUBLIC_URL_KEY => $this->objectPublicUrl,
			self::META_DATA_KEY => $this->metaData,
		];
	}
}
