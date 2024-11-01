<?php

declare(strict_types=1);

namespace OneTeamSoftware\WP\CloudSyncMaster\Admin\Table\ColumnTypeBuilder;

use OneTeamSoftware\WP\Admin\Table\ColumnTypeBuilder\ColumnTypeBuilderInterface;

class TimestampBuilder implements ColumnTypeBuilderInterface
{
	/**
	 * returns column type
	 *
	 * @return string
	 */
	public function getColumnType(): string
	{
		return 'timestamp';
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
		$output = '-';
		if (isset($row[$columnName]) && is_numeric($row[$columnName])) {
			$output = date('Y-m-d H:i:s', intval($row[$columnName]));
		}

		return $output;
	}
}
