<?php

declare(strict_types=1);

namespace OneTeamSoftware\WP\CloudSyncMaster\Admin\Table;

use OneTeamSoftware\Queue\QueueInterface;
use OneTeamSoftware\WP\Admin\Table\AbstractTable;
use OneTeamSoftware\WP\CloudSyncMaster\Admin\Table\ColumnTypeBuilder\FileLinkBuilder;
use OneTeamSoftware\WP\CloudSyncMaster\Admin\Table\ColumnTypeBuilder\TimestampBuilder;
use OneTeamSoftware\WP\CloudSyncMaster\ObjectLink\ObjectLink;

class UploadQueueTable extends AbstractTable
{
	/**
	 * @var int
	 */
	private const DEFAULT_MAX_ITEMS = 20;

	/**
	 * @var QueueInterface
	 */
	protected $queue;

	/**
	 * @var int
	 */
	protected $totalNumberOfItems;

	/**
	 * constructor
	 *
	 * @param string $id
	 * @param string $queueName
	 * @param QueueInterface $queue
	 */
	public function __construct(
		string $id,
		string $queueName,
		QueueInterface $queue
	) {
		parent::__construct(
			$id,
			[
				'singular' => __('Upload Queue', $this->id),
				'plural' => __('Upload Queue', $this->id),
				'ajax' => false,
				'screen' => $queueName,
			],
			'manage_options'
		);

		$this->addColumnTypeBuilder(new TimestampBuilder());

		$this->queue = $queue;
		$this->totalNumberOfItems = 0;

		$this->addColumnTypeBuilder(new FileLinkBuilder(get_site_url()));
	}

	/**
	 * Returns text used in search button
	 *
	 * @return string
	 */
	protected function getSearchBoxButtonText(): string
	{
		return '';
	}

	/**
	 * Returns column name that is used as primary key
	 *
	 * @return string
	 */
	protected function getPrimaryKey(): string
	{
		return ObjectLink::FILE_PATH_KEY;
	}

	/**
	 * Returns definition of table columns
	 *
	 * @return array
	 */
	protected function getTableColumns(): array
	{
		return [
			ObjectLink::FILE_PATH_KEY => [
				'title' => __('File Path', $this->id),
				'type' => 'filelink',
			],
			ObjectLink::FILE_UPDATED_TIME_KEY => [
				'title' => __('File Updated', $this->id),
				'type' => 'timestamp',
			],
		];
	}

	/**
	 * Displays table.
	 *
	 * @return void
	 */
	protected function displayTable(): void
	{
		echo sprintf('<p>%s</p>', __('This page provides a snapshot of the current file queue for uploads to the cloud.', $this->id)); // phpcs:ignore
		echo sprintf('<p>%s <strong>%d</strong></p>', __('Total number of files remaining to be uploaded is:', $this->id), $this->queue->size()); // phpcs:ignore

		parent::displayTable();
	}

	/**
	 * Returns items that match current search criteria
	 *
	 * @param array $args
	 * @return array
	 */
	protected function getItems(array $args): array
	{
		$items = $this->queue->toArray(self::DEFAULT_MAX_ITEMS);
		$this->totalNumberOfItems = count($items);

		return $items;
	}

	/**
	 * Returns total number of items that match current search criteria
	 *
	 * @param array $args
	 * @return integer
	 */
	protected function getTotalItems(array $args): int
	{
		return $this->totalNumberOfItems;
	}

	/**
	 * returns inline style
	 *
	 * @return string
	 */
	protected function getInlineStyles(): string
	{
		$styles = '
			.column-fileUpdatedTime {
				width: 200px;
			}
		';

		return $styles;
	}
}
