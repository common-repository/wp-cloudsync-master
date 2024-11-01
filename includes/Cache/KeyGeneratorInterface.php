<?php

declare(strict_types=1);

namespace OneTeamSoftware\Cache;

interface KeyGeneratorInterface
{
	/**
	 * returns a key for a given value
	 *
	 * @param mixed $value
	 * @return string
	 */
	public function getKey($value): string;
}
