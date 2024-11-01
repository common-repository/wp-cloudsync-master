<?php

namespace OneTeamSoftware\CloudStorage;

class Acl implements AclInterface
{
	/**
	 * @var array
	 */
	private $permissions;

	/**
	 * constructor.
	 *
	 * @param array $permissions
	 */
	public function __construct(array $permissions = [])
	{
		$this->permissions = $permissions;
	}

	/**
	 * returns the ACL as an array suitable for use with the cloud provider's API
	 *
	 * @return array
	 */
	public function toArray(): array
	{
		return $this->permissions;
	}
}
