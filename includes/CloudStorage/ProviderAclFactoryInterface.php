<?php

namespace OneTeamSoftware\CloudStorage;

interface ProviderAclFactoryInterface
{
	/**
	 * returns a ACL factory for a given provider
	 *
	 * @param string $provider
	 * @return AclFactoryInterface
	 */
	public function create(string $provider): AclFactoryInterface;
}
