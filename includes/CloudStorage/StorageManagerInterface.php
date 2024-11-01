<?php

namespace OneTeamSoftware\CloudStorage;

use GuzzleHttp\Promise\PromiseInterface;

interface StorageManagerInterface
{
	/**
	 * returns public URL for the object
	 *
	 * @param string $bucketName
	 * @param string $objectName
	 * @return string
	 */
	public function getPublicUrl(string $bucketName, string $objectName): string;

	/**
	 * uploads a file to the bucket
	 *
	 * @param string $filePath
	 * @param string $bucketName
	 * @param string $objectName
	 * @param AclInterface|null $acl
	 * @return PromiseInterface<ObjectInterface>
	 */
	public function uploadFile(string $filePath, string $bucketName, string $objectName, ?AclInterface $acl = null): PromiseInterface;

	/**
	 * deletes an object from the bucket
	 *
	 * @param string $bucketName
	 * @param string $objectName
	 * @return PromiseInterface
	 */
	public function deleteObject(string $bucketName, string $objectName): PromiseInterface;
}
