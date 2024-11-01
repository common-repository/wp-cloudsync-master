<?php

declare(strict_types=1);

namespace OneTeamSoftware\WP\CloudSyncMaster\Admin\Table\BulkActionHandler;

use OneTeamSoftware\Queue\QueueInterface;
use OneTeamSoftware\Queue\QueueItem;
use OneTeamSoftware\WP\Admin\Notices\Notices;
use OneTeamSoftware\WP\Admin\Table\BulkActionHandler\BulkActionHandlerInterface;
use OneTeamSoftware\WP\CloudSyncMaster\ObjectLink\ObjectLink;
use OneTeamSoftware\WP\CloudSyncMaster\ObjectLink\ObjectLinkInterface;
use OneTeamSoftware\WP\CloudSyncMaster\Repository\ObjectLinkRepositoryInterface;

class ReuploadHandler implements BulkActionHandlerInterface
{
	/**
	 * @var string
	 */
	private $id;

	/**
	 * @var Notices
	 */
	private $notices;

	/**
	 * @var ObjectLinkRepositoryInterface
	 */
	private $objectLinkRepository;

	/**
	 * @var QueueInterface
	 */
	private $uploadQueue;

	/**
	 * constructor
	 *
	 * @param string $id
	 * @param Notices $notices
	 * @param ObjectLinkRepositoryInterface $objectLinkRepository
	 * @param QueueInterface $uploadQueue
	 */
	public function __construct(
		string $id,
		Notices $notices,
		ObjectLinkRepositoryInterface $objectLinkRepository,
		QueueInterface $uploadQueue
	) {
		$this->id = $id;
		$this->notices = $notices;
		$this->objectLinkRepository = $objectLinkRepository;
		$this->uploadQueue = $uploadQueue;
	}

	/**
	 * handles bulk action and returns true or false
	 *
	 * @param array $ids
	 * @return boolean
	 */
	public function handle(array $ids): bool
	{
		$numberOfItemsHandled = 0;

		foreach ($ids as $id) {
			if ($this->enqueueObjectUpload($this->objectLinkRepository->get($id))) {
				$numberOfItemsHandled++;
			}
		}

		if ($numberOfItemsHandled === count($ids)) {
			$this->notices->type = 'success';
			$this->notices->add(__('The uploading process for the files has been rescheduled', $this->id));
		} else {
			$this->notices->type = 'error';
			$this->notices->add(__('Some of the files could not be scheduled for re-upload', $this->id));
		}

		return true;
	}

	/**
	 * returns true when object link is enqueued for upload
	 *
	 * @param ObjectLinkInterface $objectLink
	 * @return bool
	 */
	private function enqueueObjectUpload(ObjectLinkInterface $objectLink): bool
	{
		if (empty($objectLink->toArray())) {
			return false;
		}

		$queueItem = new QueueItem([
			ObjectLink::FILE_PATH_KEY => $objectLink->getFilePath(),
			ObjectLink::FILE_UPDATED_TIME_KEY => $objectLink->getFileUpdatedTime(),
			ObjectLink::BUCKET_NAME_KEY => $objectLink->getBucketName(),
			ObjectLink::OBJECT_NAME_KEY => $objectLink->getObjectName(),
		]);

		$this->uploadQueue->enqueue($queueItem);

		return true;
	}
}
