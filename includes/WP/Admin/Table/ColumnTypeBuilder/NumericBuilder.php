<?php

declare(strict_types=1);

namespace OneTeamSoftware\WP\Admin\Table\ColumnTypeBuilder;

class NumericBuilder implements ColumnTypeBuilderInterface
{
	/**
	 * returns column type
	 *
	 * @return string
	 */
	public function getColumnType(): string
	{
		return 'numeric';
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
			$output = $row[$columnName];
		}

		return $output;
	}
}
