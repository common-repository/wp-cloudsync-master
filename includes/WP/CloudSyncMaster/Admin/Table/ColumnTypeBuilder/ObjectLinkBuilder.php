<?php

declare(strict_types=1);

namespace OneTeamSoftware\WP\CloudSyncMaster\Admin\Table\ColumnTypeBuilder;

use OneTeamSoftware\WP\Admin\Table\ColumnTypeBuilder\ColumnTypeBuilderInterface;
use OneTeamSoftware\WP\CloudSyncMaster\ObjectLink\ObjectLink;

class ObjectLinkBuilder implements ColumnTypeBuilderInterface
{
	/**
	 * returns column type
	 *
	 * @return string
	 */
	public function getColumnType(): string
	{
		return 'objectlink';
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
		if (empty($row[ObjectLink::OBJECT_PUBLIC_URL_KEY]) || empty($row[$columnName]) || !is_string($row[$columnName])) {
			return '-';
		}

		$publicUrl = $row[ObjectLink::OBJECT_PUBLIC_URL_KEY];
		$objectName = $row[$columnName];

		return sprintf(
			'<a href="%s" target="_blank">%s</a>',
			$publicUrl,
			$objectName
		);
	}
}
