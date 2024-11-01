<?php

declare(strict_types=1);

namespace OneTeamSoftware\WP\CloudSyncMaster\Hooks;

use OneTeamSoftware\Cache\CacheInterface;
use OneTeamSoftware\Cache\KeyGeneratorInterface;
use OneTeamSoftware\CloudStorage\ProviderAclFactoryInterface;
use OneTeamSoftware\CloudStorage\StorageManagerFactoryInterface;
use OneTeamSoftware\CloudStorage\StorageManagerInterface;
use OneTeamSoftware\Logger\LoggerInterface;
use OneTeamSoftware\WP\CloudSyncMaster\Cache\BucketKeyGenerator;
use OneTeamSoftware\WP\CloudSyncMaster\Image\CachedImageSizeFetcher;
use OneTeamSoftware\WP\CloudSyncMaster\Image\ImageSizeFetcher;
use OneTeamSoftware\WP\CloudSyncMaster\Image\ImageSizeFetcherInterface;
use OneTeamSoftware\WP\CloudSyncMaster\Image\RepositoryImageSizeFetcher;
use OneTeamSoftware\WP\CloudSyncMaster\ObjectLink\ObjectLinkFactory;
use OneTeamSoftware\WP\CloudSyncMaster\Parser\HtmlUrlsParser;
use OneTeamSoftware\WP\CloudSyncMaster\Parser\MultiUrlsParser;
use OneTeamSoftware\WP\CloudSyncMaster\Parser\UrlsParser;
use OneTeamSoftware\WP\CloudSyncMaster\PluginSettingsInterface;
use OneTeamSoftware\WP\CloudSyncMaster\ProviderAccountInterface;
use OneTeamSoftware\WP\CloudSyncMaster\Repository\ObjectLinkRepositoryFactoryInterface;
use OneTeamSoftware\WP\CloudSyncMaster\Repository\ObjectLinkRepositoryInterface;
use OneTeamSoftware\WP\CloudSyncMaster\Transformer\CachedCloudUrlReplacer;
use OneTeamSoftware\WP\CloudSyncMaster\Transformer\CachedCloudUrlTransformer;
use OneTeamSoftware\WP\CloudSyncMaster\Transformer\CloudUrlReplacer;
use OneTeamSoftware\WP\CloudSyncMaster\Transformer\CloudUrlTransformer;
use OneTeamSoftware\WP\CloudSyncMaster\Transformer\RelativePathTransformer;

class HooksManager
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
	protected $version;

	/**
	 * @var string
	 */
	protected $basePath;

	/**
	 * @var LoggerInterface
	 */
	protected $logger;

	/**
	 * @var CacheInterface
	 */
	protected $cache;

	/**
	 * @var PluginSettingsInterface
	 */
	protected $pluginSettings;

	/**
	 * @var ProviderAclFactoryInterface
	 */
	protected $providerAclFactory;

	/**
	 * @var ObjectLinkRepositoryFactoryInterface
	 */
	protected $objectLinkRepositoryFactory;

	/**
	 * @var StorageManagerFactoryInterface
	 */
	protected $storageManagerFactory;

	/**
	 * @var KeyGeneratorInterface
	 */
	protected $keyGenerator;

	/**
	 * constructor
	 *
	 * @param string $id
	 * @param string $pluginPath
	 * @param string $version
	 * @param string $basePath
	 * @param LoggerInterface $logger
	 * @param CacheInterface $cache
	 * @param KeyGeneratorInterface $keyGenerator
	 * @param StorageManagerFactoryInterface $storageManagerFactory
	 * @param ProviderAclFactoryInterface $providerAclFactory
	 * @param ObjectLinkRepositoryFactoryInterface $objectLinkRepositoryFactory
	 * @param PluginSettingsInterface $pluginSettings
	 */
	public function __construct(
		string $id,
		string $pluginPath,
		string $version,
		string $basePath,
		LoggerInterface $logger,
		CacheInterface $cache,
		KeyGeneratorInterface $keyGenerator,
		StorageManagerFactoryInterface $storageManagerFactory,
		ProviderAclFactoryInterface $providerAclFactory,
		ObjectLinkRepositoryFactoryInterface $objectLinkRepositoryFactory,
		PluginSettingsInterface $pluginSettings
	) {
		$this->id = $id;
		$this->pluginPath = $pluginPath;
		$this->version = $version;
		$this->basePath = rtrim($basePath, '/') . '/'; // typically it will be: /var/www/html/
		$this->logger = $logger;
		$this->cache = $cache;
		$this->keyGenerator = $keyGenerator;
		$this->storageManagerFactory = $storageManagerFactory;
		$this->providerAclFactory = $providerAclFactory;
		$this->objectLinkRepositoryFactory = $objectLinkRepositoryFactory;
		$this->pluginSettings = $pluginSettings;
	}

	/**
	 * registers hooks
	 *
	 * @return void
	 */
	public function register(): void
	{
		$providerAccount = $this->pluginSettings->getDefaultProviderAccount();
		if (empty($providerAccount)) {
			$this->logger->debug(__FILE__, __LINE__, 'No default provider account found');
			return;
		}

		$this->logger->debug(__FILE__, __LINE__, 'register');

		$objectLinkRepository = $this->objectLinkRepositoryFactory->create(
			$providerAccount->getProvider(),
			$providerAccount->getBucketName()
		);
		$storageManager = $this->storageManagerFactory->create(
			$providerAccount->getProvider(),
			$providerAccount->getConfig()
		);
		$relativePathTransformer = new RelativePathTransformer(ABSPATH);
		$objectLinkFactory = new ObjectLinkFactory($this->logger, $relativePathTransformer, $providerAccount);

		$keyGenerator = new BucketKeyGenerator(
			$providerAccount->getProvider(),
			$providerAccount->getBucketName(),
			$this->keyGenerator
		);

		$imageSizeFetcher = new ImageSizeFetcher($this->logger);

		$cachedImageSizeFetcher = $this->createCachedImageSizeFetcher(
			$imageSizeFetcher,
			$providerAccount,
			$keyGenerator,
			$objectLinkRepository,
			$storageManager
		);

		$this->initHooks(
			$providerAccount,
			$imageSizeFetcher,
			$objectLinkFactory,
			$objectLinkRepository,
			$storageManager,
			$keyGenerator,
			$cachedImageSizeFetcher
		);
	}

	/**
	 * initializes hooks
	 *
	 * @param ProviderAccountInterface $providerAccount
	 * @param ImageSizeFetcherInterface $imageSizeFetcher
	 * @param ObjectLinkFactory $objectLinkFactory
	 * @param ObjectLinkRepositoryInterface $objectLinkRepository
	 * @param StorageManagerInterface $storageManager
	 * @param KeyGeneratorInterface $keyGenerator
	 * @param ImageSizeFetcherInterface $cachedImageSizeFetcher
	 * @return void
	 */
	protected function initHooks(
		ProviderAccountInterface $providerAccount,
		ImageSizeFetcherInterface $imageSizeFetcher,
		ObjectLinkFactory $objectLinkFactory,
		ObjectLinkRepositoryInterface $objectLinkRepository,
		StorageManagerInterface $storageManager,
		KeyGeneratorInterface $keyGenerator,
		ImageSizeFetcherInterface $cachedImageSizeFetcher
	): void {
		$this->initUrlRewritingHooks(
			$providerAccount,
			$keyGenerator,
			$cachedImageSizeFetcher,
			$objectLinkRepository,
			$storageManager
		);

		$this->initMediaLibraryHooks();
	}

	/**
	 * registers URL rewriter hooks
	 *
	 * @param ProviderAccountInterface $providerAccount
	 * @param KeyGeneratorInterface $keyGenerator
	 * @param ImageSizeFetcherInterface $imageSizeFetcher
	 * @param ObjectLinkRepositoryInterface $objectLinkRepository
	 * @param StorageManagerInterface $storageManager
	 * @return void
	 */
	protected function initUrlRewritingHooks(
		ProviderAccountInterface $providerAccount,
		KeyGeneratorInterface $keyGenerator,
		ImageSizeFetcherInterface $imageSizeFetcher,
		ObjectLinkRepositoryInterface $objectLinkRepository,
		StorageManagerInterface $storageManager
	): void {
		if (!$this->pluginSettings->getRewriteFileUrlWithObjectUrl()) {
			return;
		}

		$baseUrl = get_site_url();

		$cloudUrlTransformer = new CloudUrlTransformer(
			$baseUrl,
			new RelativePathTransformer($baseUrl),
			$objectLinkRepository,
			$this->logger
		);

		$cachedUrlTransformer = new CachedCloudUrlTransformer(
			$cloudUrlTransformer,
			$this->cache,
			$keyGenerator,
			$this->logger
		);

		$multiUrlsParser = (new MultiUrlsParser())
			->withUrlParser(new HtmlUrlsParser())
			->withUrlParser(new UrlsParser());

		$cloudUrlReplacer = new CloudUrlReplacer(
			$cachedUrlTransformer,
			$multiUrlsParser,
			$this->logger
		);

		$cachedCloudUrlReplacer = new CachedCloudUrlReplacer(
			$cloudUrlReplacer,
			$this->cache,
			$keyGenerator,
			$this->logger
		);

		(new UrlRewritingHooks(
			$this->id,
			$baseUrl,
			$this->pluginSettings->getUseObjectUrlInAttachmentDialog(),
			$cachedUrlTransformer,
			$cachedCloudUrlReplacer,
			$imageSizeFetcher,
			$this->logger
		))->register();
	}

	/**
	 * registers media library hooks
	 *
	 * @return void
	 */
	protected function initMediaLibraryHooks(): void
	{
		(new MediaLibraryHooks(
			$this->id,
			$this->pluginPath,
			$this->version,
			$this->logger
		))->register();
	}

	/**
	 * creates cached image size fetcher
	 *
	 * @param ImageSizeFetcherInterface $imageSizeFetcher
	 * @param ProviderAccountInterface $providerAccount
	 * @param KeyGeneratorInterface $keyGenerator
	 * @param ObjectLinkRepositoryInterface $objectLinkRepository
	 * @param StorageManagerInterface $storageManager
	 * @return ImageSizeFetcherInterface
	 */
	protected function createCachedImageSizeFetcher(
		ImageSizeFetcherInterface $imageSizeFetcher,
		ProviderAccountInterface $providerAccount,
		KeyGeneratorInterface $keyGenerator,
		ObjectLinkRepositoryInterface $objectLinkRepository,
		StorageManagerInterface $storageManager
	): ImageSizeFetcherInterface {
		$relativePathTransformer = new RelativePathTransformer(
			$storageManager->getPublicUrl($providerAccount->getBucketName(), '')
		);

		$repositoryImageSizeFetcher = new RepositoryImageSizeFetcher(
			$imageSizeFetcher,
			$objectLinkRepository,
			$relativePathTransformer,
			$this->logger
		);

		return new CachedImageSizeFetcher(
			$repositoryImageSizeFetcher,
			$this->cache,
			$keyGenerator,
			$this->logger
		);
	}
}
