<?php

declare(strict_types=1);

namespace OneTeamSoftware\WP\CloudSyncMaster\Installer;

use OneTeamSoftware\WP\Installer\Installer;

class ObjectLinkRepositoryInstaller extends Installer
{
	/**
	 * The version callbacks for the repository.
	 *
	 * @var array<string, array<string>>
	 */
	protected $versionCallbacks = [
		'1.0.0' => [
			'createTable',
		],
		'1.0.1' => [
			'addMetaDataToTable'
		]
	];

	/**
	 * constructor
	 *
	 * @param string $id
	 * @param string $title
	 * @param string $version
	 */
	public function __construct(string $id, string $title, string $version)
	{
		parent::__construct($id, 'objects', $title, $version);
	}

	/**
	 * creates repository tables.
	 *
	 * @return bool
	 */
	protected function createTable(): bool
	{
		$tableName = $this->getTablePrefix() . '_' . $this->name;
		$charsetCollate = $this->getCharsetCollate();

		$query = "CREATE TABLE IF NOT EXISTS `{$tableName}` (
            `key` VARCHAR(255) NOT NULL,
            `hash` VARCHAR(64) NOT NULL,
            `file_path` VARCHAR(255),
            `file_updated_time` INT,
            `bucket_name` VARCHAR(255),
            `object_name` VARCHAR(255),
            `object_updated_time` INT,
            `object_public_url` TEXT,
            PRIMARY KEY (`key`, `hash`),
            INDEX `key` (`key`),
            INDEX `file_path` (`file_path`),
            INDEX `file_updated_time` (`file_updated_time`),
            INDEX `bucket_name` (`bucket_name`),
            INDEX `object_name` (`object_name`),
            INDEX `object_updated_time` (`object_updated_time`),
            INDEX `object_public_url` (`object_public_url`(255))
        ) $charsetCollate;";

		return $this->query($query, __FILE__, __LINE__);
	}

	/**
	 * adds meta data column to the table.
	 *
	 * @return bool
	 */
	protected function addMetaDataToTable(): bool
	{
		global $wpdb;
		$tableName = $this->getTablePrefix() . '_' . $this->name;

		// Check if the table already has the `meta_data` column
		$query = "SHOW COLUMNS FROM `{$tableName}` LIKE 'meta_data'";
		$result = $wpdb->get_results($query, ARRAY_A);

		if ($result && count($result) > 0) {
			return true;
		}

		// Add the `meta_data` column to the table
		$query = "ALTER TABLE `{$tableName}` ADD `meta_data` TEXT NULL AFTER `object_public_url`;";
		return $this->query($query, __FILE__, __LINE__);
	}
}
