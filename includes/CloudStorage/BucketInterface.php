<?php

namespace OneTeamSoftware\CloudStorage;

use GuzzleHttp\Promise\PromiseInterface;

interface BucketInterface
{
	/**
	 * uploads a file to the bucket
	 *
	 * @param string $filePath
	 * @param string $name
	 * @param AclInterface|null $acl
	 * @return PromiseInterface<ObjectInterface>
	 */
	public function uploadFile(string $filePath, string $name, ?AclInterface $acl = null): PromiseInterface;

	/**
	 * returns object by name
	 *
	 * @param string $name
	 * @return PromiseInterface<ObjectInterface>
	 */
	public function getObject(string $name): PromiseInterface;

	/**
	 * returns public URL for the object or base URL when name is empty
	 *
	 * @param string $name
	 * @return string
	 */
	public function getPublicUrl(string $name): string;
}
