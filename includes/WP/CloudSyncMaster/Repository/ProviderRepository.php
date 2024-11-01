<?php

declare(strict_types=1);

namespace OneTeamSoftware\WP\CloudSyncMaster\Repository;

class ProviderRepository implements ProviderRepositoryInterface
{
	/**
	 * @var int
	 */
	protected $textDomain;

	/**
	 * constructor
	 *
	 * @param string $textDomain
	 */
	public function __construct(string $textDomain)
	{
		$this->textDomain = $textDomain;
	}

	/**
	 * returns a list of providers
	 *
	 * @return array
	 */
	public function getProviders(): array
	{
		return [
			'google' => __('Google', $this->textDomain),
		];
	}
}
