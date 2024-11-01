<?php

declare(strict_types=1);

namespace OneTeamSoftware\WP\CloudSyncMaster\Cache;

use OneTeamSoftware\Cache\KeyGeneratorInterface;

class BucketKeyGenerator implements KeyGeneratorInterface
{
	/**
	 * @var string
	 */
	private $prefix;

	/**
	 * @var KeyGeneratorInterface
	 */
	private $keyGenerator;

	/**
	 * constructor
	 *
	 * @param string $provider
	 * @param string $bucketName
	 * @param KeyGeneratorInterface $keyGenerator
	 */
	public function __construct(string $provider, string $bucketName, KeyGeneratorInterface $keyGenerator)
	{
		$this->prefix = $provider . '_' . $bucketName . '_';
		$this->keyGenerator = $keyGenerator;
	}

	/**
	 * returns a key for a given value
	 *
	 * @param mixed $value
	 * @return string
	 */
	public function getKey($value): string
	{
		return $this->prefix . $this->keyGenerator->getKey($value);
	}
}
