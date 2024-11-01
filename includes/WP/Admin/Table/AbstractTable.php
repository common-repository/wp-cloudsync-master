<?php

declare(strict_types=1);

namespace OneTeamSoftware\WP\Admin\Table;

use OneTeamSoftware\WP\Admin\Page\PageInterface;
use OneTeamSoftware\WP\Admin\Table\BulkActionHandler\BulkActionHandlerInterface;
use OneTeamSoftware\WP\Admin\Table\ColumnTypeBuilder\BooleanBuilder;
use OneTeamSoftware\WP\Admin\Table\ColumnTypeBuilder\ColumnTypeBuilderInterface;
use OneTeamSoftware\WP\Admin\Table\ColumnTypeBuilder\NumericBuilder;
use WP_List_Table;

// include required files so we can instantiate admin table early
require_once(ABSPATH . 'wp-admin/includes/screen.php');
require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
require_once(ABSPATH . 'wp-admin/includes/template.php');
require_once(ABSPATH . 'wp-admin/includes/class-wp-screen.php');

abstract class AbstractTable extends WP_List_Table implements PageInterface
{
	/**
	 * @var string
	 */
	protected $id;

	/**
	 * @var string
	 */
	protected $capability;

	/**
	 * @var array<ColumnTypeBuilderInterface>
	 */
	protected $columnTypeBuilders;

	/**
	 * @var array<string,string>
	 */
	protected $bulkActions;

	/**
	 * @var array<string,BulkActionHandlerInterface>
	 */
	protected $bulkActionHandlers;

	/**
	 * Redeclare in order to provide access to arguments in the children classes
	 *
	 * @var array
	 */
	protected $_args; // phpcs:ignore

	/**
	 * @var array
	 */
	protected $_columns; // phpcs:ignore

	/**
	 * @var array
	 */
	protected $_sortable_columns; // phpcs:ignore

	/**
	 * Constructor
	 *
	 * @param string $id
	 * @param array $args
	 * @param string $capability
	 */
	public function __construct(string $id, array $args, string $capability = '')
	{
		parent::__construct($args);

		$this->id = $id;
		$this->capability = $capability;

		$this->_columns = null;
		$this->_sortable_columns = null;
		$this->columnTypeBuilders = [
			'boolean' => new BooleanBuilder(),
			'numeric' => new NumericBuilder(),
		];

		$this->bulkActions = [];
		$this->bulkActionHandlers = [];

		add_action('init', [$this, 'onBulkActionRequest']);
	}

	/**
	 * adds column type handler
	 *
	 * @param ColumnTypeBuilderInterface $columnTypeBuilder
	 * @return void
	 */
	public function addColumnTypeBuilder(ColumnTypeBuilderInterface $columnTypeBuilder): void
	{
		$this->columnTypeBuilders[$columnTypeBuilder->getColumnType()] = $columnTypeBuilder;
	}

	/**
	 * adds bulk action
	 *
	 * @param string $action
	 * @param string $title
	 * @param BulkActionHandlerInterface $builkActionHandler
	 * @return void
	 */
	public function addBulkAction(string $action, string $title, BulkActionHandlerInterface $builkActionHandler): void
	{
		$this->bulkActions[$action] = $title;
		$this->bulkActionHandlers[$action] = $builkActionHandler;

		add_action('wp_ajax_' . $action, [$this, 'onBulkActionRequest']);
	}

	//-------------------- ABSTRACT LOGIC -----------------------

	/**
	 * No items found text.
	 *
	 * @return void
	 */
	public function no_items(): void // phpcs:ignore
	{
		esc_html_e('Nothing has been found', $this->id);
	}

	/**
	 * Get list columns.
	 *
	 * @return array
	 */
	public function get_columns(): array // phpcs:ignore
	{
		if (!empty($this->_columns)) {
			return $this->_columns;
		}

		$this->_columns = [];
		if (!empty($this->bulkActions)) {
			$this->_columns = [
				'cb' => '<input type="checkbox" />',
			];
		}

		$columns = $this->getTableColumns();
		foreach ($columns as $key => $column) {
			$this->_columns[$key] = $column['title'];
		}

		return $this->_columns;
	}

	/**
	 * Columns to make sortable.
	 *
	 * @return array
	 */
	public function get_sortable_columns(): array // phpcs:ignore
	{
		if (!empty($this->_sortable_columns)) {
			return $this->_sortable_columns;
		}

		$this->_sortable_columns = [];

		$columns = $this->getTableColumns();
		foreach ($columns as $key => $column) {
			if (!empty($column['sortable'])) {
				$this->_sortable_columns[$key] = [$key, true];
			}
		}

		return $this->_sortable_columns;
	}

	/**
	 * Column cb.
	 *
	 * @param array $row data.
	 * @return string
	 */
	public function column_cb($row) // phpcs:ignore
	{
		$key = $this->getPrimaryKey();

		return sprintf('<input type="checkbox" name="%s[]" value="%s" />', $key, $row[$key] ?? '');
	}

	/**
	 * Render a column when no column specific method exists.
	 *
	 * @param array $row
	 * @param string $columnName
	 * @return mixed
	 */
	public function column_default($row, $columnName) // phpcs:ignore
	{
		$columns = $this->getTableColumns();
		$output = empty($row[$columnName]) ? '-' : $row[$columnName];

		if (empty($columns[$columnName]['type'])) {
			return $output;
		}

		$type = $columns[$columnName]['type'];
		if (isset($this->columnTypeBuilders[$type])) {
			return $this->columnTypeBuilders[$type]->build($row, $columnName);
		}

		$methodName = 'getColumnTemplate' . ucfirst($type);

		if (method_exists($this, $methodName)) {
			$output = call_user_func([$this, $methodName], $row, $columnName);
		}

		return $output;
	}

	/**
	 * Prepare table list items.
	 *
	 * @return void
	 */
	public function prepare_items(): void // phpcs:ignore
	{
		$args = [];
		$args['page'] = $this->get_pagenum();
		$args['limit'] = $this->get_items_per_page($this->id . '-limit', 20);

		if (!empty($_REQUEST['limit'])) {
			$args['limit'] = intval($_REQUEST['limit']);
		}

		// allow ordering only on supported columns
		$columns = $this->getTableColumns();
		$orderBy = sanitize_text_field($_REQUEST['orderby'] ?? '');

		if (false === empty($orderBy) && isset($columns[$orderBy])) {
			$args['orderby'] = $orderBy;
			$order = sanitize_key($_REQUEST['order'] ?? '');

			if (false === empty($order) && in_array(strtolower($order), ['desc', 'asc'], true)) {
				$args['order'] = $order;
			}
		}

		if (!empty($_REQUEST['s'])) {
			$args['search'] = sanitize_text_field($_REQUEST['s']);
		}

		$this->items = $this->getItems($args);
		$totalItems = $this->getTotalItems($args);
		$totalPages = ceil($totalItems / $args['limit']);

		if ($totalPages > 1) {
			$this->set_pagination_args([
				'total_items' => $totalItems,
				'per_page' => $args['limit'],
				'total_pages' => ceil($totalItems / $args['limit']),
			]);
		}
	}

	/**
	 * Displays table.
	 *
	 * @return void
	 */
	public function display(): void
	{
		if (!empty($this->capability) && !current_user_can($this->capability)) {
			wp_die('You do not have sufficient permissions to access this page.');
		}

		$this->prepare_items();
		$this->enqueueScripts();
		$this->addInlineStyles();
		$this->displayTable();
	}

	/**
	 * handles bulk action request request
	 *
	 * @return void
	 */
	public function onBulkActionRequest(): void
	{
		// search and bulk action should be executed only when it is verified user input
		$nonceKey = 'bulk-' . $this->_args['plural'];

		$wpNonce = sanitize_key($_REQUEST['_wpnonce'] ?? '');
		if (empty($wpNonce) || false === wp_verify_nonce($wpNonce, $nonceKey)) {
			return;
		}

		if ($this->processBulkActions()) {
			$this->redirectWithoutAction();
		}
	}

	/**
	 * Returns search button text or empty
	 * If text is empty theen no search button will be displayed
	 *
	 * @return string
	 */
	abstract protected function getSearchBoxButtonText(): string;

	/**
	 * Returns column name that is used as primary key
	 *
	 * @return string
	 */
	abstract protected function getPrimaryKey(): string;

	/**
	 * Returns definition of table columns
	 *
	 * @return array
	 */
	abstract protected function getTableColumns(): array;

	/**
	 * Processes bulk actions
	 *
	 * @return boolean
	 */
	protected function processBulkActions(): bool
	{
		$action = $this->current_action();
		if (empty($this->bulkActionHandlers[$action])) {
			return false;
		}

		$ids = array_map('sanitize_text_field', $_REQUEST[$this->getPrimaryKey()] ?? []);

		return $this->bulkActionHandlers[$action]->handle($ids);
	}

	/**
	 * Get bulk actions
	 *
	 * @return array
	 */
	protected function getBulkActions(): array
	{
		return $this->bulkActions;
	}

	/**
	 * Call our helper method that returns array of bulk actions
	 *
	 * @return array
	 */
	protected function get_bulk_actions(): array // phpcs:ignore
	{
		return $this->getBulkActions();
	}

	/**
	 * Returns items that match current search criteria
	 *
	 * @param array $args
	 * @return array
	 */
	abstract protected function getItems(array $args): array;

	/**
	 * Returns total number of items that match current search criteria
	 *
	 * @param array $args
	 * @return integer
	 */
	abstract protected function getTotalItems(array $args): int;


	//-------------------- GENERIC LOGIC -----------------------

	/**
	 * Redirects back to the table without action
	 *
	 * @return void
	 */
	protected function redirectWithoutAction(): void
	{
		$skipKeys = [
			'action',
			'action2',
			'_wpnonce',
			'_wp_http_referer',
			$this->getPrimaryKey(),
		];

		$queryArgs = [];
		foreach ($_REQUEST as $key => $val) {
			$key = sanitize_text_field($key);
			if (false === empty($val) && false === in_array($key, $skipKeys, true)) {
				$queryArgs[$key] = sanitize_text_field($val);
			}
		}

		wp_redirect(add_query_arg($queryArgs, 'admin.php'));

		exit;
	}

	/**
	 * Displays table.
	 *
	 * @return void
	 */
	protected function displayTable(): void
	{
		echo '<form id="' . esc_attr($this->id) . '-table-filter" method="get">';
		foreach ($_GET as $key => $value) {
			if ('_' === $key[0] || 'paged' === $key || $this->getPrimaryKey() === $key) {
				continue;
			}
			echo sprintf(
				'<input type="hidden" name="%s" value="%s" />',
				esc_attr(sanitize_text_field($key)),
				esc_attr(sanitize_text_field($value))
			);
		}

		add_screen_option(
			'per_page',
			[
				'default' => 10,
				'option' => $this->id . '-limit',
			]
		);

		$search_button_text = $this->getSearchBoxButtonText();
		if (!empty($search_button_text)) {
			echo $this->search_box($search_button_text, $this->id . '-table-search-form'); // WPCS: XSS OK
		}

		parent::display();

		echo '</form>';
	}

	/**
	 * includes scripts
	 *
	 * @return void
	 */
	protected function enqueueScripts(): void
	{
		// it can be optionally implemented in the children classes
	}

	/**
	 * returns inline style
	 *
	 * @return string
	 */
	protected function getInlineStyles(): string
	{
		return '';
	}

	/**
	 * adds inline styles to the page
	 *
	 * @return void
	 */
	protected function addInlineStyles(): void
	{
		$styles = $this->getInlineStyles();
		if (empty($styles)) {
			return;
		}

		wp_register_style($this->id, false);
		wp_enqueue_style($this->id);
		wp_add_inline_style($this->id, $this->getInlineStyles());
	}
}
