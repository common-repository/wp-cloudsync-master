<?php

declare(strict_types=1);

namespace OneTeamSoftware\WP\CloudSyncMaster\Admin\Table\ColumnTypeBuilder;

use OneTeamSoftware\WP\Admin\Table\ColumnTypeBuilder\ColumnTypeBuilderInterface;

class FileLinkBuilder implements ColumnTypeBuilderInterface
{
	/**
	 * @var string
	 */
	private $siteUrl;

	/**
	 * @param string $siteUrl
	 */
	public function __construct(string $siteUrl)
	{
		$this->siteUrl = $siteUrl;
	}

	/**
	 * returns column type
	 *
	 * @return string
	 */
	public function getColumnType(): string
	{
		return 'filelink';
	}

	/**
	 * builds and returns contents for the given row and column
	 *
	 * @param array $row
	 * @param string $columnName
	 * @return string
	 */
	public function build(array $row, string $columnName): string
	{
		if (empty($row[$columnName]) || !is_string($row[$columnName])) {
			return '-';
		}

		$filePath = $row[$columnName];

		return sprintf(
			'<a href="%s" target="_blank">%s</a>',
			$this->siteUrl . '/' . $filePath,
			$filePath
		);
	}
}
