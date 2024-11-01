<?php

namespace OneTeamSoftware\CloudStorage;

use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\PromiseInterface;

class NullStorage implements StorageInterface
{
	/**
	 * returns a bucket by name
	 *
	 * @param string $bucketName
	 * @return PromiseInterface<BucketInterface>
	 */
	public function getBucket(string $bucketName): PromiseInterface
	{
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
		return '';
	}
}
