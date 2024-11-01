<?php

declare(strict_types=1);

namespace OneTeamSoftware\WP\Admin\Table\BulkActionHandler;

interface BulkActionHandlerInterface
{
	/**
	 * handles bulk action and returns true or false
	 *
	 * @param array $ids
	 * @return boolean
	 */
	public function handle(array $ids): bool;
}
