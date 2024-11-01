<?php

namespace OneTeamSoftware\CloudStorage;

class NullObject implements ObjectInterface
{
	/**
	 * returns true if the object exists
	 *
	 * @return bool
	 */
	public function exists(): bool
	{
		return false;
	}

	/**
	 * returns the name of the bucket
	 *
	 * @return string
	 */
	public function getBucketName(): string
	{
		return '';
	}

	/**
	 * returns the name of the object
	 *
	 * @return string
	 */
	public function getName(): string
	{
		return '';
	}

	/**
	 * returns public URL for the object
	 *
	 * @return string
	 */
	public function getPublicUrl(): string
	{
		return '';
	}

	/**
	 * returns the size of the object in bytes
	 *
	 * @return int
	 */
	public function getUpdatedTime(): int
	{
		return 0;
	}

	/**
	 * deletes the object from the bucket
	 *
	 * @return void
	 */
	public function delete(): void
	{
		// do nothing
	}
}
