<?php

declare(strict_types=1);

namespace OneTeamSoftware\WP\CloudSyncMaster;

use OneTeamSoftware\Cache\V2\Cache;
use OneTeamSoftware\Cache\KeyGenerator;
use OneTeamSoftware\Cache\Storage\InMemory;
use OneTeamSoftware\CloudStorage\ProviderAclFactory;
use OneTeamSoftware\CloudStorage\ProviderStorageFactory;
use OneTeamSoftware\CloudStorage\StorageManagerFactory;
use OneTeamSoftware\Mutex\FileMutex;
use OneTeamSoftware\WC\Logger\Logger;
use OneTeamSoftware\WP\Admin\OneTeamSoftware;
use OneTeamSoftware\WP\Cache\Storage\Transient;
use OneTeamSoftware\WP\CloudSyncMaster\Admin\SettingsPage;
use OneTeamSoftware\WP\CloudSyncMaster\CronTask\CronTasksManager;
use OneTeamSoftware\WP\CloudSyncMaster\Hooks\HooksManager;
use OneTeamSoftware\WP\CloudSyncMaster\Installer\ObjectLinkRepositoryInstaller;
use OneTeamSoftware\WP\CloudSyncMaster\Queue\QueueNamesCreator;
use OneTeamSoftware\WP\CloudSyncMaster\Repository\ObjectLinkRepositoryFactory;
use OneTeamSoftware\WP\CloudSyncMaster\Repository\ProviderRepository;
use OneTeamSoftware\WP\CloudSyncMaster\Transformer\RelativePathTransformer;
use OneTeamSoftware\WP\Queue\QueueFactory;
use OneTeamSoftware\WP\Queue\QueueInstaller;
use OneTeamSoftware\WP\SettingsStorage\SettingsStorage;

class Plugin
{
	/**
	 * @var string
	 */
	protected $id;

	/**
	 * @var string
	 */
	protected $pluginPath;

	/**
	 * @var string
	 */
	protected $title;

	/**
	 * @var string
	 */
	protected $description;

	/**
	 * @var string
	 */
	protected $version;

	/**
	 * @var array
	 */
	protected $defaultSettings;

	/**
	 * @var string
	 */
	protected $proFeatureSuffix;

	/**
	 * @var Logger
	 */
	protected $logger;

	/**
	 * @var StorageManagerFactory
	 */
	protected $storageManagerFactory;

	/**
	 * @var SettingsStorage
	 */
	protected $settingsStorage;

	/**
	 * @var ProviderRepository
	 */
	protected $providerRepository;

	/**
	 * @var QueueNamesCreator
	 */
	protected $queueNamesCreator;

	/**
	 * @var QueueFactory
	 */
	protected $queueFactory;

	/**
	 * @var ObjectLinkRepositoryFactory
	 */
	protected $objectLinkRepositoryFactory;

	/**
	 * @var ProviderAclFactory
	 */
	protected $providerAclFactory;

	/**
	 * @var Cache
	 */
	protected $cache;

	/**
	 * @var KeyGenerator
	 */
	protected $keyGenerator;

	/**
	 * @var PluginSettings
	 */
	protected $pluginSettings;

	/**
	 * @var bool
	 */
	protected $isPluginLoadedHandled;

	/**
	 * constructor
	 *
	 * @param string $pluginPath
	 * @param string $title
	 * @param string $description
	 * @param string $version
	 */
	public function __construct(
		string $pluginPath,
		string $title = '',
		string $description = '',
		string $version = null
	) {
		global $wpdb;

		$this->id = preg_replace('/-pro$/', '', basename($pluginPath, '.php'));
		$this->pluginPath = realpath($pluginPath);
		$this->title = $title;
		$this->description = $description;
		$this->version = $version;
		$this->logger = new Logger($this->id);

		$this->settingsStorage = new SettingsStorage($this->id, new FileMutex($this->id));
		$this->providerRepository = new ProviderRepository($this->id);
		$this->storageManagerFactory = new StorageManagerFactory(
			new ProviderStorageFactory($this->logger),
			$this->logger
		);
		$this->providerAclFactory = new ProviderAclFactory();
		$this->queueNamesCreator = new QueueNamesCreator();
		$this->queueFactory = new QueueFactory($this->id);

		// we want to store cache per page and request method, that is why we use page url and request method as a cache key
		$this->cache = new Cache(new InMemory(new Transient(), $this->getPageCacheKey()));
		$this->keyGenerator = new KeyGenerator($this->id);

		$this->objectLinkRepositoryFactory = new ObjectLinkRepositoryFactory($this->id, $wpdb, $this->logger);

		$this->defaultSettings = [
			'debug' => false,
			'cache' => true,
			'cacheExpirationInSecs' => 7 * DAY_IN_SECONDS,
			'accounts' => [],
			'rewriteFileUrlWithObjectUrl' => true,
			'useObjectUrlInAttachmentDialog' => true,
			'createObjectOnFileUpload' => true,
			'deleteObjectOnFileDelete' => true,
			'createObjectsForExistingFiles' => true,
			'deleteFileAfterObjectCreated' => false,
			'fillUploadQueueInterval' => PluginSettings::DEFAULT_FILL_UPLOAD_QUEUE_INTERVAL,
			'handleUploadQueueInterval' => PluginSettings::DEFAULT_HANDLE_UPLOAD_QUEUE_INTERVAL,
			'uploadBatchSize' => PluginSettings::DEFAULT_UPLOAD_BATCH_SIZE,
			'uploadConcurrency' => PluginSettings::DEFAULT_UPLOAD_CONCURRENCY,
		];

		$this->proFeatureSuffix = sprintf(
			' <strong>(%s <a href="%s" target="_blank">%s</a>)</strong>',
			__('Requires', $this->id),
			'https://1teamsoftware.com/product/' . preg_replace('/^wp-/', '', $this->id) . '-pro/',
			__('PRO Version', $this->id)
		);

		$this->pluginSettings = null;
		$this->isPluginLoadedHandled = false;
	}

	/**
	 * registers plugin
	 *
	 * @return void
	 */
	public function register(): void
	{
		if (false === $this->canRegister()) {
			return;
		}

		add_action('plugins_loaded', [$this, 'onPluginsLoaded'], PHP_INT_MAX, 0);
		add_filter($this->id . '_settingsstorage_get', [$this, 'addDefaultSettings'], 1, 1);
	}

	/**
	 * adds link to settings page
	 *
	 * @param array $links
	 * @return array
	 */
	public function onPluginActionLinks(array $links): array
	{
		$link = sprintf('<a href="%s">%s</a>', admin_url('admin.php?page=' . $this->id), __('Settings', $this->id));
		array_unshift($links, $link);
		return $links;
	}

	/**
	 * handles plugins loaded hook
	 *
	 * @return void
	 */
	public function onPluginsLoaded(): void
	{
		// plugins_loaded event can be triggered more than one time and we need to handle it only once
		if ($this->isPluginLoadedHandled) {
			return;
		}
		$this->isPluginLoadedHandled = true;

		$this->install();
		$this->loadSettings();
		$this->initAdminFeatures();
		$this->initCronTasks();
		$this->initHooks();
	}

	/**
	 * adds default settings to the given settings
	 *
	 * @param array $settings
	 * @return array
	 */
	public function addDefaultSettings(array $settings): array
	{
		return array_merge($this->defaultSettings, $settings);
	}

	/**
	 * clears license cache and refreshes page when settings are saved
	 *
	 * @return void
	 */
	public function onSettingsSaved(): void
	{
		$this->loadSettings();
	}

	/**
	 * creates and returns page cache key based on a path and post data
	 *
	 * @return string
	 */
	protected function getPageCacheKey(): string
	{
		$requestUri = home_url(add_query_arg(null, null));

		// remove some parameters to reduce cache duplication
		$paramsToRemove = ['doing_wp_cron', 'swcfpc', 'removed_item', 'loggedout', 'add-to-cart'];
		$requestUri = remove_query_arg($paramsToRemove, $requestUri);

		// add request method to the cache key, to distinguish between GET and POST requests
		$method = filter_input(INPUT_SERVER, 'REQUEST_METHOD', FILTER_SANITIZE_SPECIAL_CHARS);

		$cacheKey = 'page_' . $method . '_' . $requestUri;

		return $cacheKey;
	}

	/**
	 * returns true when page can be cached
	 *
	 * @return bool
	 */
	protected function canCachePage(): bool
	{
		// Use WordPress's own method to check for request type instead of directly accessing super globals.
		if (strtolower($_SERVER['REQUEST_METHOD']) === 'get' || empty($_POST)) {
			return true;
		}

		return false;
	}

	/**
	 * runs plugin installation
	 *
	 * @return void
	 */
	protected function install(): void
	{
		(new ObjectLinkRepositoryInstaller($this->id, $this->title, $this->version))->run();
		(new QueueInstaller($this->id, $this->title, $this->version))->run();
	}

	/**
	 * initializes admin features
	 *
	 * @return void
	 */
	protected function initAdminFeatures(): void
	{
		if (false === is_admin()) {
			return;
		}

		add_filter('plugin_action_links_' . plugin_basename($this->pluginPath), [$this, 'onPluginActionLinks'], 1, 1);
		add_action($this->id . '_settings_saved', [$this, 'onSettingsSaved']);

		$this->initSettingsPage();
	}

	/**
	 * loads settings
	 *
	 * @return void
	 */
	protected function loadSettings(): void
	{
		$settings = $this->settingsStorage->get();
		$this->setPluginSettings($settings);
	}

	/**
	 * initializes settings page
	 *
	 * @return void
	 */
	protected function initSettingsPage(): void
	{
		(new SettingsPage(
			$this->id,
			$this->title,
			$this->description,
			$this->pluginPath,
			$this->version,
			OneTeamSoftware::instance(),
			$this->logger,
			$this->settingsStorage,
			$this->queueNamesCreator,
			$this->queueFactory,
			$this->objectLinkRepositoryFactory,
			$this->getProFeatureSuffix()
		))->register();
	}

	/**
	 * initializes cron tasks
	 *
	 * @return void
	 */
	protected function initCronTasks(): void
	{
		(new CronTasksManager(
			$this->id,
			ABSPATH,
			$this->logger,
			new RelativePathTransformer(ABSPATH),
			$this->storageManagerFactory,
			$this->providerAclFactory,
			$this->queueNamesCreator,
			$this->queueFactory,
			$this->objectLinkRepositoryFactory,
			$this->pluginSettings
		))->register();
	}

	/**
	 * initializes hooks
	 *
	 * @return void
	 */
	protected function initHooks(): void
	{
		(new HooksManager(
			$this->id,
			$this->pluginPath,
			$this->version,
			ABSPATH,
			$this->logger,
			$this->cache,
			$this->keyGenerator,
			$this->storageManagerFactory,
			$this->providerAclFactory,
			$this->objectLinkRepositoryFactory,
			$this->pluginSettings
		))->register();
	}

	/**
	 * sets plugin settings
	 *
	 * @param array $settings
	 * @return void
	 */
	protected function setPluginSettings(array $settings): void
	{
		$storeCache = true;
		$useCache = filter_var($settings['cache'] ?? true, FILTER_VALIDATE_BOOLEAN);
		$cacheExpirationInSecs = intval($settings['cacheExpirationInSecs'] ?? 0);

		// do not cache pages for POST because they are highly dynamic and cache will eat up memory
		if (false === $this->canCachePage()) {
			$useCache = false;
			$storeCache = false;
		}

		$this->cache->setStoreCache($storeCache);
		$this->cache->setUseCache($useCache);
		$this->cache->setDefaultExpiresAfter($cacheExpirationInSecs);

		$enableLogger = filter_var($settings['debug'] ?? false, FILTER_VALIDATE_BOOLEAN);
		$this->logger->setEnabled($enableLogger);

		$this->logger->debug(__FILE__, __LINE__, 'setPluginSettings, settings: %s', print_r($settings, true));
		$this->pluginSettings = $this->createPluginSettings($settings);
	}

	/**
	 * returns plugin settings
	 *
	 * @param array $settings
	 * @return PluginSettingsInterface
	 */
	protected function createPluginSettings(array $settings): PluginSettingsInterface
	{
		return new PluginSettings($settings);
	}

	/**
	 * returns pro feature suffix
	 *
	 * @return string
	 */
	protected function getProFeatureSuffix(): string
	{
		return $this->proFeatureSuffix;
	}

	/**
	 * returns true when plugin can register
	 *
	 * @return bool
	 */
	protected function canRegister(): bool
	{
		return false === $this->isProVersionEnabled();
	}

	/**
	 * returns true when pro version is enabled
	 *
	 * @return bool
	 */
	protected function isProVersionEnabled(): bool
	{
		include_once ABSPATH . 'wp-admin/includes/plugin.php';

		$proPluginName = preg_replace('/(\.php|\/)/i', '-pro\\1', plugin_basename($this->pluginPath));
		if (is_plugin_active($proPluginName)) {
			return true;
		}

		return false;
	}
}
