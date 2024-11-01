<?php

namespace OneTeamSoftware\CloudStorage\Google;

use Google\Cloud\Core\Exception\GoogleException;
use Google\Cloud\Storage\Bucket;
use Google\Cloud\Storage\StorageObject;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\PromiseInterface;
use OneTeamSoftware\CloudStorage\AclInterface;
use OneTeamSoftware\CloudStorage\BucketInterface;
use OneTeamSoftware\CloudStorage\NullObject;
use OneTeamSoftware\CloudStorage\ObjectInterface;
use OneTeamSoftware\Logger\LoggerInterface;
use OneTeamSoftware\Logger\NullLogger;

class GoogleBucket implements BucketInterface
{
	/**
	 * @var Bucket
	 */
	private $bucket;

	/**
	 * @var LoggerInterface
	 */
	private $logger;

	/**
	 * GoogleBucket constructor.
	 *
	 * @param Bucket $bucket
	 * @param LoggerInterface $logger
	 */
	public function __construct(Bucket $bucket, LoggerInterface $logger = null)
	{
		$this->bucket = $bucket;
		$this->logger = $logger ?? new NullLogger();
	}

	/**
	 * uploads a file to the bucket
	 *
	 * @param string $filePath
	 * @param string $name
	 * @param AclInterface|null $acl
	 * @return PromiseInterface<ObjectInterface>
	 */
	public function uploadFile(string $filePath, string $name, ?AclInterface $acl = null): PromiseInterface
	{
		$this->logger->debug(__FILE__, __LINE__, 'Uploading file %s to %s', $filePath, $name);

		if (!file_exists($filePath)) {
			$this->logger->error(__FILE__, __LINE__, 'File %s does not exist', $filePath);

			return new FulfilledPromise(new NullObject());
		}

		return $this->createUploadPromise($filePath, $name, $acl);
	}

	/**
	 * returns object by name
	 *
	 * @param string $name
	 * @return PromiseInterface<ObjectInterface>
	 */
	public function getObject(string $name): PromiseInterface
	{
		$this->logger->debug(__FILE__, __LINE__, 'Getting object %s', $name);

		try {
			$googleObject = $this->bucket->object($name);
			if ($googleObject->exists()) {
				return new FulfilledPromise(new GoogleObject($googleObject));
			}
		} catch (GoogleException $e) {
			$this->logger->error(__FILE__, __LINE__, 'Error while getting object %s: %s', $name, $e->getMessage());
		}

		return new FulfilledPromise(new NullObject());
	}

	/**
	 * returns public URL for the object or base URL when name is empty
	 *
	 * @param string $name
	 * @return string
	 */
	public function getPublicUrl(string $name): string
	{
		$this->logger->debug(__FILE__, __LINE__, 'Getting public URL for object %s', $name);
		if (empty($name)) {
			return 'https://storage.googleapis.com/' . $this->bucket->name() . '/';
		}

		$object = $this->bucket->object($name);
		if ($object->exists()) {
			return (new GoogleObject($object))->getPublicUrl();
		}

		return '';
	}

	/**
	 * creates a promise for uploading a file
	 *
	 * @param string $filePath
	 * @param string $name
	 * @param ?AclInterface $acl
	 * @return PromiseInterface<ObjectInterface>
	 */
	private function createUploadPromise(string $filePath, string $name, ?AclInterface $acl): PromiseInterface
	{
		$this->logger->debug(__FILE__, __LINE__, 'Creating upload promise for file %s', $filePath);

		$uploadOptions = ['name' => $name];
		if ($acl) {
			$uploadOptions += $acl->toArray();
		}

		$promise = $this->bucket->uploadAsync(
			fopen($filePath, 'rb'),
			$uploadOptions
		);

		return $promise->then(
			function (StorageObject $googleObject) use ($filePath): ObjectInterface {
				$this->logger->debug(__FILE__, __LINE__, 'File %s uploaded', $filePath);
				return new GoogleObject($googleObject);
			},
			function (GoogleException $e) use ($filePath): ObjectInterface {
				$this->logger->error(__FILE__, __LINE__, 'Error while uploading file %s: %s', $filePath, $e->getMessage());
				return new NullObject();
			}
		);
	}
}
