<?php

declare(strict_types=1);

namespace OneTeamSoftware\WP\CloudSyncMaster\ObjectLink;

use OneTeamSoftware\Logger\LoggerInterface;
use OneTeamSoftware\WP\CloudSyncMaster\ProviderAccountInterface;
use OneTeamSoftware\WP\CloudSyncMaster\Transformer\RelativePathTransformerInterface;
use SplFileInfo;

class ObjectLinkFactory implements ObjectLinkFactoryInterface
{
	/**
	 * @var LoggerInterface
	 */
	private $logger;

	/**
	 * @var RelativePathTransformerInterface
	 */
	private $relativePathTransformer;

	/**
	 * @var ProviderAccountInterface
	 */
	private $providerAccount;

	/**
	 * constructor
	 *
	 * @param LoggerInterface $logger
	 * @param RelativePathTransformerInterface $relativePathTransformer
	 * @param ProviderAccountInterface $providerAccount
	 */
	public function __construct(
		LoggerInterface $logger,
		RelativePathTransformerInterface $relativePathTransformer,
		ProviderAccountInterface $providerAccount
	) {
		$this->logger = $logger;
		$this->relativePathTransformer = $relativePathTransformer;
		$this->providerAccount = $providerAccount;
	}

	/**
	 * create an object link from a file path
	 *
	 * @param string $filePath
	 * @return ObjectLinkInterface
	 */
	public function createFromFile(string $filePath): ObjectLinkInterface
	{
		if (!file_exists($filePath) || !is_file($filePath) || !is_readable($filePath)) {
			$this->logger->debug(__FILE__, __LINE__, 'File %s is not a file or is not readable', $filePath);
			return new ObjectLink([]);
		}

		$file = new SplFileInfo($filePath);

		$relativeFilePath = $this->relativePathTransformer->toRelativePath($file->getPathname());

		return new ObjectLink([
			ObjectLink::FILE_PATH_KEY => $relativeFilePath,
			ObjectLink::FILE_UPDATED_TIME_KEY => $file->getMTime(),
			ObjectLink::BUCKET_NAME_KEY => $this->providerAccount->getBucketName(),
			ObjectLink::OBJECT_NAME_KEY => $relativeFilePath,
		]);
	}
}
