<?php

declare(strict_types=1);

namespace OneTeamSoftware\WP\CloudSyncMaster\Image;

use OneTeamSoftware\Logger\LoggerInterface;
use OneTeamSoftware\Logger\NullLogger;
use OneTeamSoftware\WP\CloudSyncMaster\ObjectLink\ObjectLink;
use OneTeamSoftware\WP\CloudSyncMaster\Repository\ObjectLinkRepositoryInterface;
use OneTeamSoftware\WP\CloudSyncMaster\Transformer\RelativePathTransformerInterface;

class RepositoryImageSizeFetcher implements ImageSizeFetcherInterface
{
	/**
	 * @var ImageSizeFetcherInterface
	 */
	private $imageSizeFetcher;

	/**
	 * @var ObjectLinkRepositoryInterface
	 */
	private $objectLinkRepository;

	/**
	 * @var RelativePathTransformerInterface
	 */
	private $relativePathTransformer;

	/**
	 * @var LoggerInterface
	 */
	private $logger;

	/**
	 * constructor.
	 *
	 * @param ImageSizeFetcherInterface $imageSizeFetcher
	 * @param ObjectLinkRepositoryInterface $objectLinkRepository
	 * @param RelativePathTransformerInterface $relativePathTransformer
	 * @param LoggerInterface|null $logger
	 */
	public function __construct(
		ImageSizeFetcherInterface $imageSizeFetcher,
		ObjectLinkRepositoryInterface $objectLinkRepository,
		RelativePathTransformerInterface $relativePathTransformer,
		LoggerInterface $logger = null
	) {
		$this->imageSizeFetcher = $imageSizeFetcher;
		$this->objectLinkRepository = $objectLinkRepository;
		$this->relativePathTransformer = $relativePathTransformer;
		$this->logger = $logger ?? new NullLogger();
	}

	/**
	 * returns the image size
	 *
	 * @param string $url
	 * @return ImageSizeInfoInterface
	 */
	public function getImageSize(string $url): ImageSizeInfoInterface
	{
		$this->logger->debug(__FILE__, __LINE__, 'Getting image size for %s', $url); // phpcs:ignore

		$filePath = $this->relativePathTransformer->toRelativePath($url);

		$this->logger->debug(__FILE__, __LINE__, 'Relative file path %s', $filePath); // phpcs:ignore

		$objectLink = $this->objectLinkRepository->get($filePath);
		$metaData = $objectLink->getMetaData();

		$this->logger->debug(__FILE__, __LINE__, 'File %s Meta Data %s', $filePath, print_r($metaData, true)); // phpcs:ignore

		if (
			!empty($metaData[ObjectLink::META_DATA_SIZE_KEY]) &&
			is_array($metaData[ObjectLink::META_DATA_SIZE_KEY])
		) {
			$imageSizeData = $metaData[ObjectLink::META_DATA_SIZE_KEY];
            $this->logger->debug(__FILE__, __LINE__, 'Image size of %s is %s found in repository', $url, print_r($imageSizeData, true)); // phpcs:ignore

			return new ImageSizeInfo($imageSizeData);
		}

		$imageSize = $this->imageSizeFetcher->getImageSize($url);
		$imageSizeData = $imageSize->toArray();
        $this->logger->debug(__FILE__, __LINE__, 'Image size of %s is %s, we will store it in the repository', $url, print_r($imageSizeData, true)); // phpcs:ignore

		$metaData[ObjectLink::META_DATA_SIZE_KEY] = $imageSizeData;

		$objectLinkData = $objectLink->toArray();
		$objectLinkData[ObjectLink::META_DATA_KEY] = $metaData;

		$this->objectLinkRepository->update($filePath, new ObjectLink($objectLinkData));

		return $imageSize;
	}
}
