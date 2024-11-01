<?php

declare(strict_types=1);

namespace OneTeamSoftware\WP\CloudSyncMaster\ObjectLink;

use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\PromiseInterface;
use OneTeamSoftware\CloudStorage\AclFactoryInterface;
use OneTeamSoftware\CloudStorage\NullObject;
use OneTeamSoftware\CloudStorage\ObjectInterface;
use OneTeamSoftware\CloudStorage\StorageManagerInterface;
use OneTeamSoftware\Logger\LoggerInterface;
use OneTeamSoftware\WP\CloudSyncMaster\Image\ImageSizeFetcherInterface;
use OneTeamSoftware\WP\CloudSyncMaster\Repository\ObjectLinkRepositoryInterface;

class ObjectLinkUploader implements ObjectLinkUploaderInterface
{
	/**
	 * @var string
	 */
	private $basePath;

	/**
	 * @var LoggerInterface
	 */
	private $logger;

	/**
	 * @var AclFactoryInterface
	 */
	private $aclFactory;

	/**
	 * @var ImageSizeFetcherInterface
	 */
	private $imageSizeFetcher;

	/**
	 * @var ObjectLinkRepositoryInterface
	 */
	private $objectLinkRepository;

	/**
	 * @var StorageManagerInterface
	 */
	private $storageManager;

	/**
	 * @var bool
	 */
	private $deleteFileAfterObjectCreated;

	/**
	 * constructor
	 *
	 * @param string $basePath
	 * @param LoggerInterface $logger
	 * @param AclFactoryInterface $aclFactory
	 * @param ImageSizeFetcherInterface $imageSizeFetcher
	 * @param ObjectLinkRepositoryInterface $objectLinkRepository
	 * @param StorageManagerInterface $storageManager
	 * @param bool $deleteFileAfterObjectCreated
	 */
	public function __construct(
		string $basePath,
		LoggerInterface $logger,
		AclFactoryInterface $aclFactory,
		ImageSizeFetcherInterface $imageSizeFetcher,
		ObjectLinkRepositoryInterface $objectLinkRepository,
		StorageManagerInterface $storageManager,
		bool $deleteFileAfterObjectCreated = false
	) {
		$this->basePath = rtrim($basePath, '/') . '/';
		$this->logger = $logger;
		$this->aclFactory = $aclFactory;
		$this->imageSizeFetcher = $imageSizeFetcher;
		$this->objectLinkRepository = $objectLinkRepository;
		$this->storageManager = $storageManager;
		$this->deleteFileAfterObjectCreated = $deleteFileAfterObjectCreated;
	}

	/**
	 * uploads a file
	 *
	 * @param ObjectLinkInterface $objectLink
	 * @return PromiseInterface
	 */
	public function upload(ObjectLinkInterface $objectLink): PromiseInterface
	{
		$this->logger->debug(__FILE__, __LINE__, 'Upload: %s', print_r($objectLink->toArray(), true)); //phpcs:ignore

		if (empty($objectLink->getFilePath())) {
			$this->logger->error(__FILE__, __LINE__, 'File path is empty for the object link, so can not upload'); //phpcs:ignore

			return new FulfilledPromise(new NullObject());
		}

		return $this->storageManager
			->uploadFile(
				$this->basePath . $objectLink->getFilePath(),
				$objectLink->getBucketName(),
				$objectLink->getObjectName(),
				$this->aclFactory->createPublicReadAcl()
			)
			->then(function (ObjectInterface $object) use ($objectLink) {
				return $this->updateObjectLink($object, $objectLink);
			})
			->then(function (ObjectInterface $object) use ($objectLink) {
				return $this->deleteFileWhenRequested($object, $objectLink);
			})
			->otherwise(function ($reason): void {
				$this->logger->error(__FILE__, __LINE__, 'Upload promise was rejected: %s', $reason);
			});
	}

	/**
	 * updates an object link
	 *
	 * @param ObjectInterface $object
	 * @param ObjectLinkInterface $objectLink
	 * @return ObjectInterface
	 */
	private function updateObjectLink(ObjectInterface $object, ObjectLinkInterface $objectLink): ObjectInterface
	{
		if (!$object->exists()) {
			$this->logger->error(__FILE__, __LINE__, 'Object for the file: %s - does not exist', $objectLink->getFilePath()); //phpcs:ignore
			return $object;
		}

		$this->logger->debug(__FILE__, __LINE__, 'Updating object link for file: %s', $objectLink->getFilePath());

		$data = $objectLink->toArray();
		$data[ObjectLink::OBJECT_NAME_KEY] = $object->getName();
		$data[ObjectLink::OBJECT_UPDATED_TIME_KEY] = $object->getUpdatedTime();
		$data[ObjectLink::OBJECT_PUBLIC_URL_KEY] = $objectPublicUrl = $this->storageManager->getPublicUrl(
			$objectLink->getBucketName(),
			$object->getName()
		);
		$data[ObjectLink::META_DATA_KEY][ObjectLink::META_DATA_SIZE_KEY] = $this->imageSizeFetcher->getImageSize(
			$objectPublicUrl
		)->toArray();

		$this->objectLinkRepository->update($objectLink->getFilePath(), new ObjectLink($data));

		return $object;
	}

	/**
	 * deletes a file when requested
	 *
	 * @param ObjectInterface $object
	 * @param ObjectLinkInterface $objectLink
	 * @return ObjectInterface
	 */
	private function deleteFileWhenRequested(ObjectInterface $object, ObjectLinkInterface $objectLink): ObjectInterface
	{
		if (!$object->exists()) {
			$this->logger->error(__FILE__, __LINE__, 'Object for the file: %s - does not exist', $objectLink->getFilePath()); //phpcs:ignore
			return $object;
		}

		$filePath = $this->basePath . $objectLink->getFilePath();

		if (!$this->deleteFileAfterObjectCreated || !file_exists($filePath)) {
			$this->logger->debug(__FILE__, __LINE__, 'File: %s does not exist or delete file after upload is not requested', $filePath); //phpcs:ignore
			return $object;
		}

		$this->logger->debug(__FILE__, __LINE__, 'Deleting file: %s', $filePath);
		unlink($filePath);

		return $object;
	}
}
