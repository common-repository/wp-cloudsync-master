<?php

declare(strict_types=1);

namespace OneTeamSoftware\WP\CloudSyncMaster\Repository;

use OneTeamSoftware\Logger\LoggerInterface;
use OneTeamSoftware\WP\CloudSyncMaster\ObjectLink\ObjectLink;
use OneTeamSoftware\WP\CloudSyncMaster\ObjectLink\ObjectLinkInterface;
use wpdb;

class ObjectLinkRepository implements ObjectLinkRepositoryInterface
{
	/**
	 * @var string
	 */
	private $tableName;

	/**
	 * @var string
	 */
	private $key;

	/**
	 * @var wpdb
	 */
	private $wpdb;

	/**
	 * @var LoggerInterface
	 */
	private $logger;

	/**
	 * constructor
	 *
	 * @param string $id
	 * @param string $key
	 * @param wpdb $wpdb
	 * @param LoggerInterface $logger
	 */
	public function __construct(string $id, string $key, wpdb $wpdb, LoggerInterface $logger)
	{
		$this->tableName = $wpdb->prefix . preg_replace('/-/', '_', $id) . '_objects';
		$this->key = $key;
		$this->wpdb = $wpdb;
		$this->logger = $logger;
	}

	/**
	 * returns true when key exists in the repository
	 *
	 * @param string $key
	 * @return bool
	 */
	public function has(string $key): bool
	{
		if (empty($key)) {
			return false;
		}

		$hash = $this->getKeyHash($key);

		$query = sprintf('SELECT COUNT(*) FROM `%s` WHERE `hash` = %%s', esc_sql($this->tableName));

		$result = $this->wpdb->get_var($this->wpdb->prepare($query, $hash));

		$hasKey = $result > 0;

		$this->logger->debug(__FILE__, __LINE__, 'Does repository have %s? %s', $key, $hasKey ? 'Yes' : 'No');

		return $hasKey;
	}

	/**
	 * returns object link with a given key
	 *
	 * @param string $key
	 * @return ObjectLinkInterface
	 */
	public function get(string $key): ObjectLinkInterface
	{
		if (empty($key)) {
			return new ObjectLink([]);
		}

		$hash = $this->getKeyHash($key);

		$query = sprintf("SELECT * FROM `%s` WHERE `key` = %%s AND `hash` = '%%s'", esc_sql($this->tableName));

		$result = $this->wpdb->get_row($this->wpdb->prepare($query, $this->key, $hash), ARRAY_A);

		if ($result) {
			$result['meta_data'] = empty($result['meta_data']) ? null : json_decode($result['meta_data'], true);

			$this->logger->debug(__FILE__, __LINE__, 'Found object link for %s, data: %s', $key, json_encode($result));

			return new ObjectLink($result);
		}

		$this->logger->debug(__FILE__, __LINE__, 'Object link for %s is not found, returning empty object', $key);

		return new ObjectLink([]);
	}

	/**
	 * updates object link with a given key
	 *
	 * @param string $key
	 * @param ObjectLinkInterface $objectLink
	 * @return void
	 */
	public function update(string $key, ObjectLinkInterface $objectLink): void
	{
		if (empty($key)) {
			return;
		}

		$data = $objectLink->toArray();
		$hash = $this->getKeyHash($key);

		$this->logger->debug(__FILE__, __LINE__, 'Update object link for %s with data: %s', $key, json_encode($data));

		$row = $data;
		$row['key'] = $this->key;
		$row['hash'] = $hash;
		$row['meta_data'] = empty($row['meta_data']) ? null : json_encode((array)$row['meta_data']);

		$result = $this->wpdb->replace(
			$this->tableName,
			$row
		);

		if ($result === false) {
			$this->logger->error(__FILE__, __LINE__, 'Failed to update object link for %s, row: %s, error: %s', $key, json_encode($row), $this->wpdb->last_error); // phpcs:ignore
		}
	}

	/**
	 * delete object link with a given key
	 *
	 * @param string $key
	 * @return void
	 */
	public function delete(string $key): void
	{
		if (empty($key)) {
			return;
		}

		$hash = $this->getKeyHash($key);

		$this->logger->debug(__FILE__, __LINE__, 'Delete object link for %s', $key);

		$result = $this->wpdb->delete(
			$this->tableName,
			[
				'key' => $this->key,
				'hash' => $hash,
			]
		);

		if ($result === false) {
			$this->logger->error(__FILE__, __LINE__, 'Failed to delete object link for %s, error: %s', $key, $this->wpdb->last_error); // phpcs:ignore
		}
	}

	/**
	 * returns entire contents of the repository
	 *
	 * @param array $conditions
	 * @param array $orderBy
	 * @param integer $limit
	 * @param integer $offset
	 * @return array
	 */
	public function getList(array $conditions = [], array $orderBy = [], int $limit = 0, int $offset = 0): array
	{
		$querySearchWhere = $this->createQuerySearchWhere($conditions);
		$queryOrderBy = $this->createQueryOrderBy($orderBy);

		$query = sprintf(
			'SELECT * FROM `%s` WHERE `key` = %%s %s %s',
			esc_sql($this->tableName),
			$querySearchWhere,
			$queryOrderBy
		);

		$query = $this->wpdb->prepare($query, $this->key);

		if ($limit > 0) {
			$query .= " LIMIT {$limit}";
			$query .= $offset > 0 ? ",{$offset}" : '';
		}

		$results = $this->wpdb->get_results($query, ARRAY_A);

		foreach ($results as &$result) {
			if (!empty($result['meta_data'])) {
				$result['meta_data'] = json_decode($result['meta_data'], true);
			}
		}

		return $results;
	}

	/**
	 * returns count of the repository
	 *
	 * @param array $conditions
	 * @return integer
	 */
	public function getListCount(array $conditions = []): int
	{
		$querySearchWhere = $this->createQuerySearchWhere($conditions);

		$query = sprintf(
			'SELECT COUNT(*) FROM `%s` WHERE `key` = %%s %s',
			esc_sql($this->tableName),
			$querySearchWhere
		);

		return intval($this->wpdb->get_var($this->wpdb->prepare($query, $this->key)));
	}

	/**
	 * returns hash for the file
	 *
	 * @param string $key
	 * @return string
	 */
	private function getKeyHash(string $key): string
	{
		return hash('sha256', $key);
	}

	/**
	 * creates search where conditions for the query
	 *
	 * @param array $conditions
	 * @return string
	 */
	private function createQuerySearchWhere(array $conditions): string
	{
		if (empty($conditions)) {
			return '';
		}

		$whereClauses = [];
		foreach ($conditions as $column => $value) {
			$whereClause = $this->getQuerySearchWhereCondition($column, $value);
			if (!empty($whereClause)) {
				$whereClauses[] = $whereClause;
			}
		}

		if (empty($whereClauses)) {
			return '';
		}

		return ' AND (' . implode(' OR ', $whereClauses) . ')';
	}

	/**
	 * returns search where condition for a given column with a given value
	 *
	 * @param string $column
	 * @param string $value
	 * @return string
	 */
	private function getQuerySearchWhereCondition(string $column, string $value): string
	{
		if (empty($value) || !$this->isValidTableColumn($column)) {
			return '';
		}

		return $this->wpdb->prepare(
			'`' . esc_sql($column) . '` LIKE %s',
			'%' . $this->wpdb->esc_like($value) . '%'
		);
	}

	/**
	 * returns true when column is valid
	 *
	 * @param string $column
	 * @return bool
	 */
	private function isValidTableColumn(string $column): bool
	{
		switch ($column) {
			case ObjectLink::FILE_PATH_KEY:
			case ObjectLink::BUCKET_NAME_KEY:
			case ObjectLink::OBJECT_NAME_KEY:
				return true;
		}

		return false;
	}

	/**
	 * creates order by for the query
	 *
	 * @param array $orderBy
	 * @return string
	 */
	private function createQueryOrderBy(array $orderBy): string
	{
		if (empty($orderBy)) {
			return '';
		}

		$columnNames = array_keys((new ObjectLink([]))->toArray());

		$orderByClauses = [];
		foreach ($orderBy as $column => $direction) {
			if (in_array($column, $columnNames, true) && in_array($direction, ['ASC', 'DESC'], true)) {
				$orderByClauses[] = "`$column` $direction";
			}
		}

		if (empty($orderByClauses)) {
			return '';
		}

		return ' ORDER BY ' . implode(', ', $orderByClauses);
	}
}
