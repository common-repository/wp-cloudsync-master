<?php

declare(strict_types=1);

namespace OneTeamSoftware\WP\CloudSyncMaster;

interface ProviderAccountInterface
{
	/**
	 * @return string
	 */
	public function getProvider(): string;

	/**
	 * @return string
	 */
	public function getBucketName(): string;

	/**
	 * @return array
	 */
	public function getConfig(): array;
}
