<?php

namespace OneTeamSoftware\CloudStorage;

use GuzzleHttp\Promise\PromiseInterface;
use OneTeamSoftware\Logger\LoggerInterface;
use OneTeamSoftware\Logger\NullLogger;

class StorageManager implements StorageManagerInterface
{
	/**
	 * @var StorageInterface
	 */
	private $storage;

	/**
	 * @var LoggerInterface
	 */
	private $logger;

	/**
	 * StorageManager constructor.
	 *
	 * @param StorageInterface $storage
	 * @param LoggerInterface $logger
	 */
	public function __construct(StorageInterface $storage, LoggerInterface $logger = null)
	{
		$this->storage = $storage;
		$this->logger = $logger ?? new NullLogger();
	}

	/**
	 * returns public URL for the object
	 *
	 * @param string $bucketName
	 * @param string $objectName
	 * @return string
	 */
	public function getPublicUrl(string $bucketName, string $objectName): string
	{
		$this->logger->debug(__FILE__, __LINE__, 'Getting public URL for bucket: %s, object: %s', $bucketName, $objectName);

		$publicUrl = $this->storage->getPublicUrl($bucketName, $objectName);

		$this->logger->debug(__FILE__, __LINE__, 'Public URL for bucket: %s, object: %s is %s', $bucketName, $objectName, $publicUrl);

		return $publicUrl;
	}

	/**
	 * uploads a file to the bucket
	 *
	 * @param string $filePath
	 * @param string $bucketName
	 * @param string $objectName
	 * @param AclInterface|null $acl
	 * @return PromiseInterface<ObjectInterface>
	 */
	public function uploadFile(string $filePath, string $bucketName, string $objectName, ?AclInterface $acl = null): PromiseInterface
	{
		$this->logger->debug(__FILE__, __LINE__, 'Uploading file: %s to bucket: %s, object: %s', $filePath, $bucketName, $objectName);

		return $this->storage->getBucket($bucketName)
			->then(function (BucketInterface $bucket) use ($filePath, $objectName, $acl) {
				return $bucket->uploadFile($filePath, $objectName, $acl);
			});
	}

	/**
	 * deletes an object from the bucket
	 *
	 * @param string $bucketName
	 * @param string $objectName
	 * @return PromiseInterface
	 */
	public function deleteObject(string $bucketName, string $objectName): PromiseInterface
	{
		$this->logger->debug(__FILE__, __LINE__, 'Deleting object: %s from bucket: %s', $objectName, $bucketName);

		return $this->storage->getBucket($bucketName)
			->then(function (BucketInterface $bucket) use ($objectName) {
				return $bucket->getObject($objectName);
			})
			->then(function (ObjectInterface $object) {
				$object->delete();

				return $object;
			});
	}
}
