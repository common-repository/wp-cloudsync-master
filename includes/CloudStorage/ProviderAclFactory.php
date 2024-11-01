<?php

namespace OneTeamSoftware\CloudStorage;

class ProviderAclFactory implements ProviderAclFactoryInterface
{
	/**
	 * returns a ACL factory for a given provider
	 *
	 * @param string $provider
	 * @return AclFactoryInterface
	 */
	public function create(string $provider): AclFactoryInterface
	{
		$provider = ucfirst(strtolower($provider));

		$className = __NAMESPACE__ . '\\' . $provider . '\\' . $provider . 'AclFactory';
		if (!class_exists($className)) {
			return new NullAclFactory();
		}

		$aclFactory = new $className();
		if (!($aclFactory instanceof AclFactoryInterface)) {
			return new NullAclFactory();
		}

		return $aclFactory;
	}
}
