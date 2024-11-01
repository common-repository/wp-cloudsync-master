<?php

declare(strict_types=1);

namespace OneTeamSoftware\WP\CloudSyncMaster\Transformer;

interface CloudUrlTransformerInterface
{
	/**
	 * transform local URL to Cloud URL
	 *
	 * @param string $url
	 * @return string
	 */
	public function toCloudUrl(string $url): string;
}
