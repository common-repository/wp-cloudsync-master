<?php

declare(strict_types=1);

namespace OneTeamSoftware\WP\CronTask;

abstract class AbstractCronTask
{
	/**
	 * @var string
	 */
	protected $cronTaskId;

	/**
	 * @var float
	 */
	protected $interval;

	/**
	 * @var string
	 */
	protected $title;

	/**
	 * @var bool
	 */
	protected $enabled;

	/**
	 * constructor
	 *
	 * @param string $cronTaskId
	 * @param float $interval
	 * @param string $title
	 */
	public function __construct(
		string $cronTaskId = 'custom_cron_task',
		float $interval = 60,
		string $title = 'Custom Cron Task'
	) {
		$this->cronTaskId = $cronTaskId;
		$this->interval = floatval($interval);
		$this->title = $title;
		$this->enabled = true;

		add_filter('cron_schedules', [$this, 'getCronSchedules']);
		add_action('init', [$this, 'onInit']);
	}

	/**
	 * Sets interval of how often cron job should be executed
	 *
	 * @param float $interval
	 * @return void
	 */
	public function setInterval(float $interval): void
	{
		$this->interval = $interval;
	}

	/**
	 * Enables/disabled cron task
	 *
	 * @param bool $enabled
	 * @return void
	 */
	public function setEnabled(bool $enabled): void
	{
		$this->enabled = $enabled;
	}

	/**
	 * Schedules job in the cron
	 *
	 * @return void
	 */
	public function onInit(): void
	{
		if (!$this->enabled) {
			return;
		}

		add_action($this->cronTaskId, [$this, 'execute']);

		if (!wp_next_scheduled($this->cronTaskId)) {
			wp_schedule_single_event(time() + $this->interval, $this->cronTaskId);
			//wp_schedule_event(time(), $this->cronTaskId, $this->cronTaskId);
		}
	}

	/**
	 * Adds schedule to the cron
	 *
	 * @param array $schedules
	 * @return array
	 */
	public function getCronSchedules(array $schedules = []): array
	{
		if (!$this->enabled) {
			return $schedules;
		}

		// support running cron tasks every minute
		$schedules[$this->cronTaskId] = [
			'interval' => $this->interval,
			'display' => $this->title
		];

		return $schedules;
	}

	/**
	 * Handles cron job
	 *
	 * @return void
	 */
	abstract public function execute(): void;
}
