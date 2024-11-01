<?php

namespace OneTeamSoftware\CloudStorage;

interface ObjectInterface
{
	/**
	 * returns true if the object exists
	 *
	 * @return bool
	 */
	public function exists(): bool;

	/**
	 * returns the name of the bucket
	 *
	 * @return string
	 */
	public function getBucketName(): string;

	/**
	 * returns the name of the object
	 *
	 * @return string
	 */
	public function getName(): string;

	/**
	 * returns public URL for the object
	 *
	 * @return string
	 */
	public function getPublicUrl(): string;

	/**
	 * returns the size of the object in bytes
	 *
	 * @return int
	 */
	public function getUpdatedTime(): int;

	/**
	 * deletes the object from the bucket
	 *
	 * @return void
	 */
	public function delete(): void;
}
