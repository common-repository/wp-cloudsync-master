<?php

declare(strict_types=1);

namespace OneTeamSoftware\WP\CloudSyncMaster\Admin;

use OneTeamSoftware\Queue\QueueFactoryInterface;
use OneTeamSoftware\WC\Logger\Logger;
use OneTeamSoftware\WP\Admin\OneTeamSoftware;
use OneTeamSoftware\WP\Admin\Page\AbstractPage;
use OneTeamSoftware\WP\Admin\Page\PageTab;
use OneTeamSoftware\WP\Admin\Page\PageTabs;
use OneTeamSoftware\WP\CloudSyncMaster\Admin\Table\ObjectsTable;
use OneTeamSoftware\WP\CloudSyncMaster\Admin\Table\UploadQueueTable;
use OneTeamSoftware\WP\CloudSyncMaster\PluginSettings;
use OneTeamSoftware\WP\CloudSyncMaster\ProviderAccountInterface;
use OneTeamSoftware\WP\CloudSyncMaster\Queue\QueueNamesCreatorInterface;
use OneTeamSoftware\WP\CloudSyncMaster\Repository\ObjectLinkRepositoryFactoryInterface;
use OneTeamSoftware\WP\SettingsStorage\SettingsStorage;

class SettingsPage extends AbstractPage
{
	/**
	 * @var string
	 */
	protected $id;

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
	protected $pluginPath;

	/**
	 * @var string
	 */
	protected $version;

	/**
	 * @var OneTeamSoftware
	 */
	protected $mainMenu;

	/**
	 * @var Logger
	 */
	protected $logger;

	/**
	 * @var SettingsStorage
	 */
	protected $settingsStorage;

	/**
	 * @var QueueNamesCreatorInterface
	 */
	protected $queueNamesCreator;

	/**
	 * @var QueueFactoryInterface
	 */
	protected $queueFactory;

	/**
	 * @var ObjectLinkRepositoryFactoryInterface
	 */
	protected $objectLinkRepositoryFactory;

	/**
	 * @var string
	 */
	protected $proFeatureSuffix;

	/**
	 * @var PageTabs
	 */
	protected $pageTabs;

	/**
	 * constructor
	 *
	 * @param string $id
	 * @param string $title
	 * @param string $description
	 * @param string $pluginPath
	 * @param string $version
	 * @param OneTeamSoftware $mainMenu
	 * @param Logger $logger
	 * @param SettingsStorage $settingsStorage
	 * @param QueueNamesCreatorInterface $queueNamesCreator
	 * @param QueueFactoryInterface $queueFactory
	 * @param ObjectLinkRepositoryFactoryInterface $objectLinkRepositoryFactory
	 * @param string $proFeatureSuffix
	 */
	public function __construct(
		string $id,
		string $title,
		string $description,
		string $pluginPath,
		string $version,
		OneTeamSoftware $mainMenu,
		Logger $logger,
		SettingsStorage $settingsStorage,
		QueueNamesCreatorInterface $queueNamesCreator,
		QueueFactoryInterface $queueFactory,
		ObjectLinkRepositoryFactoryInterface $objectLinkRepositoryFactory,
		string $proFeatureSuffix
	) {
		parent::__construct($id, 'oneteamsoftware', $title, $title, 'manage_options');

		$this->id = $id;
		$this->title = $title;
		$this->description = $description;
		$this->pluginPath = $pluginPath;
		$this->version = $version;
		$this->mainMenu = $mainMenu;
		$this->logger = $logger;
		$this->settingsStorage = $settingsStorage;
		$this->queueNamesCreator = $queueNamesCreator;
		$this->queueFactory = $queueFactory;
		$this->objectLinkRepositoryFactory = $objectLinkRepositoryFactory;
		$this->proFeatureSuffix = $proFeatureSuffix;

		$this->pageTabs = new PageTabs($this->id);
	}

	/**
	 * registers settings page
	 *
	 * @return void
	 */
	public function register(): void
	{
		$this->mainMenu->register();

		$this->addGeneralTab();

		$pluginSettings = new PluginSettings($this->settingsStorage->get());

		$providerAccounts = $pluginSettings->getProviderAccounts();
		foreach ($providerAccounts as $providerAccount) {
			$this->addProviderBucketTabs($providerAccount);
		}
	}

	/**
	 * displays page
	 *
	 * @return void
	 */
	public function display(): void
	{
		$this->enqueueScripts();

		echo sprintf('<h1 class="wp-heading-inline">%s</h1>', esc_html($this->title));

		$this->pageTabs->display();
	}

	/**
	 * includes scripts
	 *
	 * @return void
	 */
	public function enqueueScripts(): void
	{
		$cssExt = defined('WP_DEBUG') && WP_DEBUG ? 'css' : 'min.css' ;
		//$jsExt = defined('WP_DEBUG') && WP_DEBUG ? 'js' : 'min.js' ;

		wp_register_style(
			$this->id . '-SettingsPage',
			plugins_url('assets/css/SettingsPage.' . $cssExt, str_replace('phar://', '', $this->pluginPath)),
			['wp-jquery-ui-dialog'],
			$this->version
		);
		wp_enqueue_style($this->id . '-SettingsPage');

		//wp_register_style(
		//	$this->id . '-switchify',
		//	plugins_url('assets/css/switchify.' . $cssExt, str_replace('phar://', '', $this->pluginPath)),
		//	[],
		//	$this->version
		//);
		//wp_enqueue_style($this->id . '-switchify');

		//wp_register_script(
		//	$this->id . '-switchify',
		//	plugins_url('assets/js/switchify.' . $jsExt, str_replace('phar://', '', $this->pluginPath)),
		//	['jquery'],
		//	$this->version
		//);
		//wp_enqueue_script($this->id . '-switchify');
	}

	/**
	 * adds general tab
	 *
	 * @return void
	 */
	protected function addGeneralTab(): void
	{
		$this->pageTabs->addTab(
			new PageTab(
				'general',
				'manage_options',
				__('General Settings', $this->id),
				$this->getGeneralForm()
			)
		);
	}

	/**
	 * returns general form
	 *
	 * @return GeneralForm
	 */
	protected function getGeneralForm(): GeneralForm
	{
		return new GeneralForm($this->id, $this->description, $this->proFeatureSuffix, $this->settingsStorage);
	}

	/**
	 * adds tabs for a given provider and bucket
	 *
	 * @param ProviderAccountInterface $providerAccount
	 * @return void
	 */
	protected function addProviderBucketTabs(ProviderAccountInterface $providerAccount): void
	{
		$this->addUploadQueueTab($providerAccount);
		$this->addObjectsTable($providerAccount);
	}

	/**
	 * adds upload queue tab
	 *
	 * @param ProviderAccountInterface $providerAccount
	 * @return void
	 */
	protected function addUploadQueueTab(ProviderAccountInterface $providerAccount): void
	{
		$queueName = $this->queueNamesCreator->createUploadQueueName(
			$providerAccount->getProvider(),
			$providerAccount->getBucketName()
		);

		$this->pageTabs->addTab(
			new PageTab(
				$queueName,
				'manage_options',
				__('Upload Queue', $this->id),
				new UploadQueueTable(
					$this->id,
					$queueName,
					$this->queueFactory->create($queueName)
				)
			)
		);
	}

	/**
	 * adds objects table
	 *
	 * @param ProviderAccountInterface $providerAccount
	 * @return void
	 */
	protected function addObjectsTable(ProviderAccountInterface $providerAccount): void
	{
		$uploadQueueName = $this->queueNamesCreator->createUploadQueueName(
			$providerAccount->getProvider(),
			$providerAccount->getBucketName()
		);

		$uploadQueue = $this->queueFactory->create($uploadQueueName);

		$this->pageTabs->addTab(
			new PageTab(
				$providerAccount->getProvider() . '_' . $providerAccount->getBucketName() . '_objects',
				'manage_options',
				__('Objects', $this->id),
				new ObjectsTable(
					$this->id,
					$this->pluginPath,
					$this->version,
					$this->objectLinkRepositoryFactory->create(
						$providerAccount->getProvider(),
						$providerAccount->getBucketName()
					),
					$uploadQueue
				)
			)
		);
	}
}
