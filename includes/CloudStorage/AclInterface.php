<?php

namespace OneTeamSoftware\CloudStorage;

interface AclInterface
{
	/**
	 * returns the ACL as an array suitable for use with the cloud provider's API
	 *
	 * @return array
	 */
	public function toArray(): array;
}
