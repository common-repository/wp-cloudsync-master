<?php

namespace OneTeamSoftware\CloudStorage\Google;

use OneTeamSoftware\CloudStorage\Acl;
use OneTeamSoftware\CloudStorage\AclFactoryInterface;

class GoogleAclFactory implements AclFactoryInterface
{
	/**
	 * creates an ACL that grants public read access.
	 *
	 * @return Acl
	 */
	public function createPublicReadAcl(): Acl
	{
		return new Acl(['predefinedAcl' => 'publicRead']);
	}

	/**
	 * creates an ACL that file private to the project and only accessible
	 * to the owner and users explicitly granted access.
	 *
	 * @return Acl
	 */
	public function createPrivateAcl(): Acl
	{
		return new Acl(['predefinedAcl' => 'private']);
	}

	/**
	 * creates an ACL that grants the bucket owner full control to the file.
	 *
	 * @return Acl
	 */
	public function createBucketOwnerFullControlAcl(): Acl
	{
		return new Acl(['predefinedAcl' => 'bucketOwnerFullControl']);
	}

	/**
	 * creates an ACL that grants authenticated users read access to the file.
	 *
	 * @return Acl
	 */
	public function createAuthenticatedReadAcl(): Acl
	{
		return new Acl(['predefinedAcl' => 'authenticatedRead']);
	}

	/**
	 * creates a custom ACL object.
	 *
	 * @param array $permissions
	 * @return Acl
	 */
	public function createCustomAcl(array $permissions): Acl
	{
		return new Acl($permissions);
	}
}
