<?php

declare(strict_types=1);

namespace OneTeamSoftware\WP\Admin\Table\ColumnTypeBuilder;

interface ColumnTypeBuilderInterface
{
	/**
	 * returns column type
	 *
	 * @return string
	 */
	public function getColumnType(): string;

	/**
	 * builds and returns contents for the given row and column
	 *
	 * @param array $row
	 * @param string $columnName
	 * @return string
	 */
	public function build(array $row, string $columnName): string;
}
