<?php

declare(strict_types=1);

namespace OneTeamSoftware\WP\CloudSyncMaster\ObjectLink;

interface ObjectLinkInterface
{
	/**
	 * returns the file path
	 *
	 * @return string
	 */
	public function getFilePath(): string;

	/**
	 * returns the file updated time
	 *
	 * @return int
	 */
	public function getFileUpdatedTime(): int;

	/**
	 * returns the bucket name
	 *
	 * @return string
	 */
	public function getBucketName(): string;

	/**
	 * returns the remote object name
	 *
	 * @return string
	 */
	public function getObjectName(): string;

	/**
	 * returns the remote object updated time
	 *
	 * @return int
	 */
	public function getObjectUpdatedTime(): int;

	/**
	 * returns the remote object public url
	 *
	 * @return string
	 */
	public function getObjectPublicUrl(): string;

	/**
	 * returns the meta data
	 *
	 * @return array
	 */
	public function getMetaData(): array;

	/**
	 * returns the item as an array
	 *
	 * @return array
	 */
	public function toArray(): array;
}
