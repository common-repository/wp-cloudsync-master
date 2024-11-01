<?php

declare(strict_types=1);

namespace OneTeamSoftware\WP\CloudSyncMaster\Transformer;

class RelativePathTransformer implements RelativePathTransformerInterface
{
	/**
	 * @var string
	 */
	private $rootPath;

	/**
	 * constructor.
	 *
	 * @param string $rootPath
	 */
	public function __construct(string $rootPath)
	{
		$this->rootPath = $rootPath;
	}

	/**
	 * transforms path to relative path
	 *
	 * @param string $path
	 * @return string
	 */
	public function toRelativePath(string $path): string
	{
		$relativePath = preg_replace('/^' . preg_quote($this->rootPath, '/') . '/', '', $path);

		return ltrim($relativePath, '/');
	}
}
