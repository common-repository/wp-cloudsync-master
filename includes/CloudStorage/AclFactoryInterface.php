<?php

namespace OneTeamSoftware\CloudStorage;

interface AclFactoryInterface
{
	/**
	 * creates an ACL that grants public read access.
	 *
	 * @return AclInterface
	 */
	public function createPublicReadAcl(): AclInterface;

	/**
	 * creates an ACL that file private to the project and only accessible
	 * to the owner and users explicitly granted access.
	 *
	 * @return AclInterface
	 */
	public function createPrivateAcl(): AclInterface;

	/**
	 * creates an ACL that grants the bucket owner full control to the file.
	 *
	 * @return AclInterface
	 */
	public function createBucketOwnerFullControlAcl(): AclInterface;

	/**
	 * creates an ACL that grants authenticated users read access to the file.
	 *
	 * @return AclInterface
	 */
	public function createAuthenticatedReadAcl(): AclInterface;

	/**
	 * creates an custom ACL object.
	 *
	 * @param array $permissions
	 * @return AclInterface
	 */
	public function createCustomAcl(array $permissions): AclInterface;
}
