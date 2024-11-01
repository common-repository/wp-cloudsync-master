<?php

declare(strict_types=1);

namespace OneTeamSoftware\WP\CloudSyncMaster\Admin\Table;

use OneTeamSoftware\Queue\QueueInterface;
use OneTeamSoftware\WP\Admin\Notices\Notices;
use OneTeamSoftware\WP\Admin\Table\AbstractTable;
use OneTeamSoftware\WP\CloudSyncMaster\Admin\Table\BulkActionHandler\ReuploadHandler;
use OneTeamSoftware\WP\CloudSyncMaster\Admin\Table\ColumnTypeBuilder\FileLinkBuilder;
use OneTeamSoftware\WP\CloudSyncMaster\Admin\Table\ColumnTypeBuilder\ObjectLinkBuilder;
use OneTeamSoftware\WP\CloudSyncMaster\Admin\Table\ColumnTypeBuilder\TimestampBuilder;
use OneTeamSoftware\WP\CloudSyncMaster\ObjectLink\ObjectLink;
use OneTeamSoftware\WP\CloudSyncMaster\Repository\ObjectLinkRepositoryInterface;

class ObjectsTable extends AbstractTable
{
	/**
	 * @var string
	 */
	protected $pluginPath;

	/**
	 * @var string
	 */
	protected $version;

	/**
	 * @var ObjectLinkRepositoryInterface
	 */
	protected $objectLinkRepository;

	/**
	 * @var QueueInterface
	 */
	protected $uploadQueue;

	/**
	 * @var Notices
	 */
	protected $notices;

	/**
	 * @var int
	 */
	protected $totalNumberOfItems;

	/**
	 * Constructor
	 *
	 * @param string $id
	 * @param string $pluginPath
	 * @param string $version
	 * @param ObjectLinkRepositoryInterface $objectLinkRepository
	 * @param QueueInterface $uploadQueue
	 */
	public function __construct(
		string $id,
		string $pluginPath,
		string $version,
		ObjectLinkRepositoryInterface $objectLinkRepository,
		QueueInterface $uploadQueue
	) {
		parent::__construct(
			$id,
			[
				'singular' => __('Objects', $id),
				'plural' => __('Objects', $id),
				'ajax' => false,
				'screen' => $id . '-objects',
			],
			'manage_options'
		);

		$this->pluginPath = $pluginPath;
		$this->version = $version;
		$this->objectLinkRepository = $objectLinkRepository;
		$this->uploadQueue = $uploadQueue;

		$this->notices = new Notices($id . '-object-notices');
		$this->totalNumberOfItems = 0;

		$this->addColumnTypeBuilder(new TimestampBuilder());
		$this->addColumnTypeBuilder(new ObjectLinkBuilder());
		$this->addColumnTypeBuilder(new FileLinkBuilder(get_site_url()));

		$this->addBulkActions();
	}

	/**
	 * adds bulk actions
	 *
	 * @return void
	 */
	protected function addBulkActions(): void
	{
		$this->addBulkAction(
			$this->id . '-reupload',
			__('Re-upload', $this->id),
			new ReuploadHandler(
				$this->id,
				$this->notices,
				$this->objectLinkRepository,
				$this->uploadQueue
			)
		);
	}

	/**
	 * Returns text used in search button
	 *
	 * @return string
	 */
	protected function getSearchBoxButtonText(): string
	{
		return 'Search';
	}

	/**
	 * Returns column name that is used as primary key
	 *
	 * @return string
	 */
	protected function getPrimaryKey(): string
	{
		return ObjectLink::OBJECT_NAME_KEY;
	}

	/**
	 * Returns definition of table columns
	 *
	 * @return array
	 */
	protected function getTableColumns(): array
	{
		return [
			ObjectLink::OBJECT_NAME_KEY => [
				'title' => __('Object Name', $this->id),
				'type' => 'objectlink',
				'sortable' => true,
			],
			ObjectLink::OBJECT_UPDATED_TIME_KEY => [
				'title' => __('Object Updated', $this->id),
				'type' => 'timestamp',
				'sortable' => true,
			],
			ObjectLink::FILE_PATH_KEY => [
				'title' => __('Local File Path', $this->id),
				'type' => 'filelink',
				'sortable' => true,
			],
			ObjectLink::FILE_UPDATED_TIME_KEY => [
				'title' => __('File Updated', $this->id),
				'type' => 'timestamp',
				'sortable' => true,
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
		echo sprintf('<p>%s</p>', esc_html__('This page displays a list of cloud objects that are recognized by the plugin, along with their respective local files.', $this->id)); // phpcs:ignore

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
		$conditions = [
			ObjectLink::BUCKET_NAME_KEY => $args['search'] ?? '',
			ObjectLink::OBJECT_NAME_KEY => $args['search'] ?? '',
			ObjectLink::FILE_PATH_KEY => $args['search'] ?? '',
		];

		$this->totalNumberOfItems = $this->objectLinkRepository->getListCount($conditions);

		$limit = $args['limit'] ?? 20;
		$offset = ($args['page'] ?? 1) * $limit - $limit;

		return $this->objectLinkRepository->getList(
			$conditions,
			[$args['orderby'] ?? '' => strtoupper($args['order'] ?? '')],
			$limit,
			$offset
		);
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
	 * includes scripts
	 *
	 * @return void
	 */
	protected function enqueueScripts(): void
	{
		//$jsExt = defined('WP_DEBUG') && WP_DEBUG ? 'js' : 'min.js' ;

		//wp_register_script(
		//	$this->id . '-ObjectsTable',
		//	plugins_url('assets/js/ObjectsTable.' . $jsExt, str_replace('phar://', '', $this->pluginPath)),
		//	['jquery'],
		//	$this->version
		//);
		//wp_enqueue_script($this->id . '-ObjectsTable');

		//$settings = [
		//	'id' => $this->id,
		//	'tab' => 'rules',
		//	'ajaxurl' => admin_url('admin-ajax.php'),
		//];

		//wp_localize_script($this->id . '-ObjectsTable', 'objectsTableSettings', $settings);
	}

	/**
	 * returns inline style
	 *
	 * @return string
	 */
	protected function getInlineStyles(): string
	{
		$styles = '
			.column-objectUpdatedTime, .column-fileUpdatedTime {
				width: 200px;
			}
		';

		return $styles;
	}
}
