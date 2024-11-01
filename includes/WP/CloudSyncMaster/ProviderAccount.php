<?php

declare(strict_types=1);

namespace OneTeamSoftware\WP\CloudSyncMaster;

class ProviderAccount implements ProviderAccountInterface
{
	/**
	 * @var string
	 */
	private $provider;

	/**
	 * @var string
	 */
	private $bucketName;

	/**
	 * @var array
	 */
	private $config;

	/**
	 * constructor.
	 *
	 * @param array $settings
	 */
	public function __construct(array $settings)
	{
		$this->provider = $settings['provider'] ?? '';
		$this->bucketName = $settings['bucketName'] ?? '';
		$this->config = $settings['config'] ?? [];
	}

	/**
	 * @return string
	 */
	public function getProvider(): string
	{
		return $this->provider;
	}

	/**
	 * @return string
	 */
	public function getBucketName(): string
	{
		return $this->bucketName;
	}

	/**
	 * @return array
	 */
	public function getConfig(): array
	{
		return $this->config;
	}
}
