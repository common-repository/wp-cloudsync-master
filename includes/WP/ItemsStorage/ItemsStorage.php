<?php

declare(strict_types=1);

namespace OneTeamSoftware\WP\ItemsStorage;

use OneTeamSoftware\WP\SettingsStorage\SettingsStorage;

class ItemsStorage
{
	/**
	 * @var SettingsStorage
	 */
	private $settingsStorage;

	/**
	 * @var string
	 */
	private $key;

	/**
	 * constructor
	 *
	 * @param SettingsStorage $settingsStorage
	 * @param string $key
	 */
	public function __construct(SettingsStorage $settingsStorage, string $key)
	{
		$this->settingsStorage = $settingsStorage;
		$this->key = $key;
	}

	/**
	 * adds a new item and returns new item id
	 *
	 * @param array $item
	 * @return string
	 */
	public function add(array $item): string
	{
		$itemId = hash('sha256', uniqid('', true));
		$item['id'] = $itemId;

		if ($this->update($itemId, $item)) {
			return $itemId;
		}

		return '';
	}

	/**
	 * updates a requested item
	 *
	 * @param string $itemId
	 * @param array $item
	 * @return boolean
	 */
	public function update(string $itemId, array $item): bool
	{
		if (empty($itemId) || empty($item)) {
			return false;
		}

		$settings = $this->settingsStorage->get();
		$settings[$this->key][$itemId] = array_merge($settings[$this->key][$itemId] ?? [], $item);

		$this->settingsStorage->update($settings);

		return true;
	}

	/**
	 * deletes a requested item
	 *
	 * @param string $itemId
	 * @return boolean
	 */
	public function delete(string $itemId): bool
	{
		if (empty($itemId)) {
			return false;
		}

		$settings = $this->settingsStorage->get();

		unset($settings[$this->key][$itemId]);

		$this->settingsStorage->update($settings);

		return true;
	}

	/**
	 * returns true when item already exists
	 *
	 * @param string $itemId
	 * @return bool
	 */
	public function has(string $itemId): bool
	{
		return false === empty($itemId) && isset($this->settingsStorage->get()[$this->key][$itemId]);
	}

	/**
	 * returns requested item
	 *
	 * @param string $itemId
	 * @return array
	 */
	public function get(string $itemId): array
	{
		return $this->settingsStorage->get()[$this->key][$itemId] ?? [];
	}

	/**
	 * returns all items
	 *
	 * @return array
	 */
	public function getList(): array
	{
		return $this->settingsStorage->get()[$this->key] ?? [];
	}
}
