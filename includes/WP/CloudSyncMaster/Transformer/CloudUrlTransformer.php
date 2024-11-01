<?php

declare(strict_types=1);

namespace OneTeamSoftware\WP\CloudSyncMaster\Transformer;

use OneTeamSoftware\Logger\LoggerInterface;
use OneTeamSoftware\WP\CloudSyncMaster\Repository\ObjectLinkRepositoryInterface;

class CloudUrlTransformer implements CloudUrlTransformerInterface
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
	 * @var ObjectLinkRepositoryInterface
	 */
	private $objectLinkRepository;

	/**
	 * @var string
	 */
	private $baseUrl;

	/**
	 * constructor.
	 *
	 * @param string $baseUrl
	 * @param RelativePathTransformerInterface $relativePathTransformer
	 * @param ObjectLinkRepositoryInterface $objectLinkRepository
	 * @param LoggerInterface $logger
	 */
	public function __construct(
		string $baseUrl,
		RelativePathTransformerInterface $relativePathTransformer,
		ObjectLinkRepositoryInterface $objectLinkRepository,
		LoggerInterface $logger
	) {
		$this->baseUrl = $baseUrl;
		$this->relativePathTransformer = $relativePathTransformer;
		$this->objectLinkRepository = $objectLinkRepository;
		$this->logger = $logger;
	}

	/**
	 * transform local URL to Cloud URL
	 *
	 * @param string $url
	 * @return string
	 */
	public function toCloudUrl(string $url): string
	{
		$this->logger->debug(__FILE__, __LINE__, 'Transforming Local URL: %s', $url);

		if (strpos($url, 'http') === 0 && strpos($url, $this->baseUrl) === false) {
			$this->logger->debug(__FILE__, __LINE__, 'URL %s is not local, so return as is', $url);

			return $url;
		}

		$relativePath = $this->relativePathTransformer->toRelativePath($url);
		if (empty($relativePath)) {
			$this->logger->debug(__FILE__, __LINE__, 'Relative path is empty for url: %s', $url);

			return $url;
		}

		$this->logger->debug(__FILE__, __LINE__, 'Relative path is: %s', $relativePath);

		if (!$this->objectLinkRepository->has($relativePath)) {
			$this->logger->debug(__FILE__, __LINE__, 'Object link not found for url: %s', $url);

			return $url;
		}

		$objectLink = $this->objectLinkRepository->get($relativePath);

		$publicUrl = $objectLink->getObjectPublicUrl();

		$this->logger->debug(__FILE__, __LINE__, 'Object URL for local URL: %s is: %s', $url, $publicUrl);

		return empty($publicUrl) ? $url : $publicUrl;
	}
}
