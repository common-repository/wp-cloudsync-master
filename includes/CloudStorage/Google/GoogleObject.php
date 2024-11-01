<?php

namespace OneTeamSoftware\CloudStorage\Google;

use Google\Cloud\Storage\StorageObject;
use OneTeamSoftware\CloudStorage\ObjectInterface;

class GoogleObject implements ObjectInterface
{
	/**
	 * @var string
	 */
	public const STORAGE_BASE_URL = 'https://storage.googleapis.com/';

	/**
	 * @var StorageObject
	 */
	private $object;

	/**
	 * GoogleObject constructor.
	 *
	 * @param StorageObject $object
	 */
	public function __construct(StorageObject $object)
	{
		$this->object = $object;
	}

	/**
	 * returns true if the object exists
	 *
	 * @return bool
	 */
	public function exists(): bool
	{
		return $this->object->exists();
	}

	/**
	 * returns the name of the bucket
	 *
	 * @return string
	 */
	public function getBucketName(): string
	{
		return $this->object->identity()['bucket'] ?? '';
	}

	/**
	 * returns the name of the object
	 *
	 * @return string
	 */
	public function getName(): string
	{
		return $this->object->name();
	}

	/**
	 * returns public URL for the object
	 *
	 * @return string
	 */
	public function getPublicUrl(): string
	{
		return self::STORAGE_BASE_URL . $this->getBucketName() . '/' . $this->getName();
	}

	/**
	 * returns the size of the object in bytes
	 *
	 * @return int
	 */
	public function getUpdatedTime(): int
	{
		return strtotime($this->object->info()['updated'] ?? '');
	}

	/**
	 * deletes the object from the bucket
	 *
	 * @return void
	 */
	public function delete(): void
	{
		$this->object->delete();
	}
}
