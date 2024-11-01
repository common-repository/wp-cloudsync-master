<?php

declare(strict_types=1);

namespace OneTeamSoftware\WP\Queue;

use OneTeamSoftware\WP\Installer\Installer;

class QueueInstaller extends Installer
{
	/**
	 * The version callbacks for the queue table.
	 *
	 * @var array<string, array<string>>
	 */
	protected $versionCallbacks = [
		'1.0.0' => [
			'createTableV100',
		],
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
		parent::__construct($id, 'queue', $title, $version);
	}

	/**
	 * Creates the queue table.
	 *
	 * @return bool
	 */
	protected function createTableV100(): bool
	{
		$tableName = $this->getTablePrefix() . '_' . $this->name;
		$charsetCollate = $this->getCharsetCollate();

		$query = "CREATE TABLE IF NOT EXISTS `{$tableName}` (
            `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            `queue` VARCHAR(255) NOT NULL,
            `hash` VARCHAR(64) NOT NULL,
            `data` LONGTEXT NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `hash` (`queue`, `hash`)
        ) $charsetCollate;";

		return $this->query($query, __FILE__, __LINE__);
	}
}
