<?php

declare(strict_types=1);

namespace OneTeamSoftware\WP\Installer;

class Installer
{
	/**
	 * @var string
	 */
	protected $id;

	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @var string
	 */
	protected $title;

	/**
	 * @var string
	 */
	protected $version;

	/**
	 * @var array
	 */
	protected $errors;

	/**
	 * @var array
	 */
	protected $versionCallbacks;

	/**
	 * constructor
	 *
	 * @param string $id
	 * @param string $name
	 * @param string $title
	 * @param string $version
	 */
	public function __construct(string $id, string $name, string $title, string $version)
	{
		$this->id = $id;
		$this->name = $name;
		$this->title = $title;
		$this->version = $version;
		$this->errors = [];
	}

	/**
	 * registers the installer
	 *
	 * @return void
	 */
	public function register(): void
	{
		add_action('admin_init', [$this, 'run'], 5);
		add_action('admin_init', [$this, 'displayErrors']);
	}

	/**
	 * runs the installer
	 *
	 * @return void
	 */
	public function run(): void
	{
		$installedVersion = $this->getInstalledVersion();

		if ($this->isUpToDate($installedVersion)) {
			return;
		}

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$success = apply_filters($this->id . '_before_install', true);

		if ($success) {
			$success = $this->executeVersionCallbacks($installedVersion);
		}

		$success = apply_filters($this->id . '_after_install', $success);

		if ($success) {
			$this->updateInstalledVersion();
		}
	}

	/**
	 * displays the errors
	 *
	 * @return void
	 */
	public function displayErrors(): void
	{
		if (empty($this->errors)) {
			return;
		}

		$messages = '';
		foreach ($this->errors as $error) {
			$messages .= sprintf('<p>%s</p>', wp_kses($error, []));
		}

		echo sprintf('<div id="message" class="error"><p><strong>%s</strong></p>%s</div>', esc_html($this->title), wp_kses_post($messages));
	}

	/**
	 * returns the installed version
	 *
	 * @return string
	 */
	protected function getInstalledVersion(): string
	{
		return get_option($this->id . '_' . $this->name . '_version') ?: '0.0.0';
	}

	/**
	 * updates the installed version
	 *
	 * @return void
	 */
	protected function updateInstalledVersion(): void
	{
		update_option($this->id . '_' . $this->name . '_version', $this->version);
	}

	/**
	 * checks if the plugin is up to date
	 *
	 * @param string $installedVersion
	 * @return bool
	 */
	protected function isUpToDate(string $installedVersion): bool
	{
		return defined('IFRAME_REQUEST') || version_compare($installedVersion, $this->version, '>=');
	}

	/**
	 * returns charset and collate
	 *
	 * @return string
	 */
	protected function getCharsetCollate(): string
	{
		global $wpdb;

		$collate = 'ENGINE=INNODB ';

		if ($wpdb->has_cap('collation')) {
			$collate .= $wpdb->get_charset_collate();
		}

		return $collate;
	}

	/**
	 * returns the table prefix
	 *
	 * @return string
	 */
	protected function getTablePrefix(): string
	{
		global $wpdb;

		$tablePrefix = $wpdb->prefix . $this->id;
		$tablePrefix = str_replace('-', '_', $tablePrefix);

		return $tablePrefix;
	}

	/**
	 * executes query
	 *
	 * @param string $query
	 * @param string $file
	 * @param int $line
	 * @return bool
	 */
	protected function query(string $query, string $file, int $line): bool
	{
		global $wpdb;

		$wpdb->query($query);

		if ($this->addLastError($file, $line)) {
			return false;
		}

		return true;
	}

	/**
	 * adds last error
	 *
	 * @param string $file
	 * @param int $line
	 * @return bool
	 */
	protected function addLastError(string $file, int $line): bool
	{
		global $wpdb;

		if (!empty($wpdb->last_error) && !in_array($wpdb->last_error, $this->errors, true)) {
			$this->errors[] = $wpdb->last_error . ' (FILE: ' . $file . ', LINE: ' . $line . ')';

			return true;
		}

		return false;
	}

	/**
	 * executes version callbacks
	 *
	 * @param string $installedVersion
	 * @return bool
	 */
	private function executeVersionCallbacks(string $installedVersion): bool
	{
		foreach ($this->versionCallbacks as $version => $callbacks) {
			if (!$this->isCurrentVersionPriorTo($installedVersion, $version)) {
				continue;
			}

			if (!$this->executeCallbacks($callbacks)) {
				return false;
			}
		}

		return true;
	}

	/**
	 * returns true if the current version is prior to the given version
	 *
	 * @param string $installedVersion
	 * @param string $version
	 * @return bool
	 */
	private function isCurrentVersionPriorTo(string $installedVersion, string $version): bool
	{
		return version_compare($installedVersion, $version, '<');
	}

	/**
	 * executes callbacks
	 *
	 * @param array $callbacks
	 * @return bool
	 */
	private function executeCallbacks(array $callbacks): bool
	{
		foreach ($callbacks as $callback) {
			if (method_exists($this, $callback) && !$this->$callback()) {
				return false;
			}
		}

		return true;
	}
}
