<?php

namespace OneTeamSoftware\CloudStorage\Google;

use Google\Cloud\Core\Exception\GoogleException;
use Google\Cloud\Storage\StorageClient;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\PromiseInterface;
use OneTeamSoftware\CloudStorage\NullBucket;
use OneTeamSoftware\CloudStorage\StorageInterface;
use OneTeamSoftware\Logger\LoggerInterface;
use OneTeamSoftware\Logger\NullLogger;

/*
To authenticate the Google Cloud Storage client properly, you need to provide the credentials in
the form of a JSON key file that you obtain from the Google Cloud Console. Here's how to set up authentication:

Go to the Google Cloud Console.
Select your project or create a new one.
Go to the APIs & Services section.
Click "Create credentials" and select "Service account."
Fill in the required information and click "Create."
Grant the necessary permissions to your service account (e.g., "Storage Admin" for full access to the Google Cloud Storage).
Click "Done."
Click on the newly created service account and go to the "Keys" tab.
Click "Add Key" and select "JSON."
Download the JSON key file and store it securely.
Now, you can use this JSON key file to authenticate your Google Cloud Storage client in your PHP application.
Update the GoogleStorage class to accept an additional parameter, $keyFilePath, which should contain the path to the JSON key file:
*/

class GoogleStorage implements StorageInterface
{
	/**
	 * @var StorageClient
	 */
	private $storageClient;

	/**
	 * @var LoggerInterface
	 */
	private $logger;

	/**
	 * constructor
	 *
	 * @param StorageClient $storageClient
	 * @param LoggerInterface|null $logger
	 */
	public function __construct(StorageClient $storageClient, LoggerInterface $logger = null)
	{
		$this->storageClient = $storageClient;
		$this->logger = $logger ?? new NullLogger();
	}

	/**
	 * returns a bucket by name
	 *
	 * @param string $bucketName
	 * @return PromiseInterface<BucketInterface>
	 */
	public function getBucket(string $bucketName): PromiseInterface
	{
		$this->logger->debug(__FILE__, __LINE__, 'Getting bucket %s ', $bucketName);

		try {
			$bucket = $this->storageClient->bucket($bucketName);
			if (!$bucket->exists()) {
				$this->logger->debug(__FILE__, __LINE__, 'Bucket %s does not exist, creating it', $bucketName);

				$bucket = $this->storageClient->createBucket($bucketName);
			}

			$this->logger->debug(__FILE__, __LINE__, 'Bucket %s has been successfully fetched', $bucketName);

			return new FulfilledPromise(new GoogleBucket($bucket, $this->logger));
		} catch (GoogleException $e) {
			$this->logger->error(__FILE__, __LINE__, 'Failed to get bucket %s: %s', $bucketName, $e->getMessage());
		}

		return new FulfilledPromise(new NullBucket());
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

		return GoogleObject::STORAGE_BASE_URL . $bucketName . '/' . $objectName;
	}
}
