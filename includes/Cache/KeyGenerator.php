<?php

declare(strict_types=1);

namespace OneTeamSoftware\Cache;

class KeyGenerator implements KeyGeneratorInterface
{
	/**
	 * @var string
	 */
	protected $suffix;

	/**
	 * constructor
	 *
	 * @param string $suffix
	 */
	public function __construct(string $suffix = '')
	{
		$this->suffix = $suffix;
	}

	/**
	 * returns a key for a given value
	 *
	 * @param mixed $value
	 * @return string
	 */
	public function getKey($value): string
	{
		$jsonData = json_encode($value);
		$key = md5($jsonData);
		$key .= $this->suffix;

		return $key;
	}
}
