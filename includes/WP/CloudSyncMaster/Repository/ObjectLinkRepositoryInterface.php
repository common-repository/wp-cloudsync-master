<?php

declare(strict_types=1);

namespace OneTeamSoftware\WP\CloudSyncMaster\Repository;

use OneTeamSoftware\WP\CloudSyncMaster\ObjectLink\ObjectLinkInterface;

interface ObjectLinkRepositoryInterface
{
	/**
	 * returns true when key exists in the repository
	 *
	 * @param string $key
	 * @return bool
	 */
	public function has(string $key): bool;

	/**
	 * returns object link with a given key
	 *
	 * @param string $key
	 * @return ObjectLinkInterface
	 */
	public function get(string $key): ObjectLinkInterface;

	/**
	 * updates object link with a given key
	 *
	 * @param string $key
	 * @param ObjectLinkInterface $objectLink
	 * @return void
	 */
	public function update(string $key, ObjectLinkInterface $objectLink): void;

	/**
	 * delete object link with a given key
	 *
	 * @param string $key
	 * @return void
	 */
	public function delete(string $key): void;

	/**
	 * returns entire contents of the repository
	 *
	 * @param array $conditions
	 * @param array $orderBy
	 * @param integer $limit
	 * @param integer $offset
	 * @return array
	 */
	public function getList(array $conditions = [], array $orderBy = [], int $limit = 0, int $offset = 0): array;

	/**
	 * returns count of the repository
	 *
	 * @param array $conditions
	 * @return integer
	 */
	public function getListCount(array $conditions = []): int;
}
