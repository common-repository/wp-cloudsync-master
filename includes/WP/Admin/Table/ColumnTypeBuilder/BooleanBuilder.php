<?php

declare(strict_types=1);

namespace OneTeamSoftware\WP\Admin\Table\ColumnTypeBuilder;

class BooleanBuilder implements ColumnTypeBuilderInterface
{
	/**
	 * returns column type
	 *
	 * @return string
	 */
	public function getColumnType(): string
	{
		return 'boolean';
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
		$output = '';
		if (empty($row[$columnName])) {
			$output = '-';
		} else {
			$output = '<svg style="display: inline; border: none; height: 1em; width: 1em; margin: 0 .07em; vertical-align: -0.1em; background: none; padding: 0; box-shadow: none;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 36 36"><path fill="#31373D" d="M34.459 1.375c-1.391-.902-3.248-.506-4.149.884L13.5 28.17l-8.198-7.58c-1.217-1.125-3.114-1.051-4.239.166-1.125 1.216-1.051 3.115.166 4.239l10.764 9.952s.309.266.452.359c.504.328 1.07.484 1.63.484.982 0 1.945-.482 2.52-1.368L35.343 5.524c.902-1.39.506-3.248-.884-4.149z"/></svg>'; // phpcs:ignore
		}

		return $output;
	}
}
