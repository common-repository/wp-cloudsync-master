<?php

declare(strict_types=1);

namespace OneTeamSoftware\WP\CloudSyncMaster\Admin;

use OneTeamSoftware\WC\Admin\PageForm\AbstractPageForm;
use OneTeamSoftware\WP\SettingsStorage\SettingsStorage;

class GeneralForm extends AbstractPageForm
{
	/**
	 * @var string
	 */
	protected $id;

	/**
	 * @var string
	 */
	protected $description;

	/**
	 * @var string
	 */
	protected $proFeatureSuffix;

	/**
	 * @var SettingsStorage
	 */
	protected $settingsStorage;

	/**
	 * constructor
	 *
	 * @param string $id
	 * @param string $description
	 * @param string $proFeatureSuffix
	 * @param SettingsStorage $settingsStorage
	 */
	public function __construct(
		string $id,
		string $description,
		string $proFeatureSuffix,
		SettingsStorage $settingsStorage
	) {
		$this->id = $id;
		$this->description = $description;
		$this->proFeatureSuffix = $proFeatureSuffix;
		$this->settingsStorage = $settingsStorage;

		parent::__construct($id . '-general', 'manage_options', $id);
	}

	/**
	 * Returns fields for the plugin settings form
	 *
	 * @return array
	 */
	public function getFormFields(): array
	{
		$formFields = [
			'description' => [
				'id' => 'description',
				'type' => 'title',
				'desc' => $this->description,
			],
			'description_end' => [
				'type' => 'sectionend',
			],
		];

		$formFields += $this->getFormFieldsBefore();
		$formFields += $this->getGeneralSettingsFields();
		$formFields += $this->getGoogleFormFields();

		$formFields += [
			'save' => [
				'id' => 'save',
				'title' => __('Save Changes', $this->id),
				'type' => 'submit',
				'class' => 'button-primary',
			],
		];

		return $formFields;
	}

	/**
	 * Returns fields for the plugin settings form
	 *
	 * @return array
	 */
	protected function getGeneralSettingsFields(): array
	{
		return [
			'general_settings_title' => [
				'id' => 'general_settings_title',
				'type' => 'title',
				'title' => __('General Settings', $this->id),
				'desc' => __('Adjust the general settings to control the file upload process, performance, and concurrency, as well as define the permitted file extensions.', $this->id), // phpcs:ignore
			],
			'debug' => [
				'id' => 'debug',
				'title' => __('Debug', $this->id),
				'type' => 'checkbox',
				'filter' => FILTER_VALIDATE_BOOLEAN,
				'optional' => true,
				//'desc' => sprintf(
				//  '<strong>%s</strong><br/> %s <a href="https://www.loom.com/" target="_blank">loom.com</a>, %s
				//	<a href="%s" target="_blank">%s</a> %s <a href="%s" target="_blank">%s</a> %s.',
				//	__('Do you experience any issues?', $this->id),
				//	__('Enable a debug mode, reproduce the issue, while recording screen with', $this->id),
				//	__('then', $this->id),
				//	$this->logExporter->getExportUrl(),
				//	__('click to download a log file', $this->id),
				//	__('and send it via our', $this->id),
				//	'https://1teamsoftware.com/contact-us/',
				//	__('contact form', $this->id),
				//	__('with the detailed description of the issue', $this->id),
				//),
			],
			'cache' => [
				'id' => 'cache',
				'title' => __('Use Cache', $this->id),
				'type' => 'checkbox',
				'filter' => FILTER_VALIDATE_BOOLEAN,
				'desc' => __('Enable or disable caching to improve performance. When enabled, the plugin will cache results to reduce server load and increase response times.', $this->id), // phpcs:ignore
			],
			'cacheExpirationInSecs' => [
				'id' => 'cacheExpirationInSecs',
				'title' => __('Cache Expiration (secs)', $this->id),
				'type' => 'number',
				'custom_attributes' => [
					'min' => 0,
					'step' => 1,
				],
				'filter' => FILTER_VALIDATE_INT,
				'desc' => __('Specify the cache expiration time in seconds. Cached data will be automatically removed after the set duration, ensuring that the plugin uses fresh data.', $this->id), // phpcs:ignore
			],
			'rewriteFileUrlWithObjectUrl' => [
				'id' => 'rewriteFileUrlWithObjectUrl',
				'title' => __('Rewrite Media URLs', $this->id),
				'type' => 'checkbox',
				'filter' => FILTER_VALIDATE_BOOLEAN,
				'desc' => __('When enabled, the plugin will rewrite media URLs to point to the cloud storage.', $this->id), // phpcs:ignore
			],
			'useObjectUrlInAttachmentDialog' => [
				'id' => 'useObjectUrlInAttachmentDialog',
				'title' => __('Use Cloud URL in Attachment Dialog', $this->id),
				'type' => 'checkbox',
				'filter' => FILTER_VALIDATE_BOOLEAN,
				'desc' => __('When enabled, the plugin will use the cloud URL in the attachment dialog.', $this->id), // phpcs:ignore
			],
			'createObjectsForExistingFiles' => [
				'id' => 'createObjectsForExistingFiles',
				'title' => __('Upload Existing Files', $this->id),
				'type' => 'checkbox',
				'filter' => FILTER_VALIDATE_BOOLEAN,
				'desc' => __('When enabled, the plugin will upload existing files in the media library to the cloud.', $this->id), // phpcs:ignore
			],
			'createObjectOnFileUpload' => [
				'id' => 'createObjectOnFileUpload',
				'title' => __('Direct File Upload', $this->id),
				'type' => 'checkbox',
				'filter' => FILTER_VALIDATE_BOOLEAN,
				'desc' => __('When enabled, the plugin will upload files directly to cloud storage, when they are uploaded to the website.', $this->id) . $this->proFeatureSuffix, // phpcs:ignore
				'custom_attributes' => $this->getProFeaturesRequiredCustomAttributes(),
			],
			'deleteObjectOnFileDelete' => [
				'id' => 'deleteObjectOnFileDelete',
				'title' => __('Delete Files from Cloud', $this->id),
				'type' => 'checkbox',
				'filter' => FILTER_VALIDATE_BOOLEAN,
				'desc' => __('When enabled, the plugin will delete files from cloud storage when they are deleted from the website.', $this->id) . $this->proFeatureSuffix, // phpcs:ignore
				'custom_attributes' => $this->getProFeaturesRequiredCustomAttributes(),
			],
			'deleteFileAfterObjectCreated' => [
				'id' => 'deleteFileAfterObjectCreated',
				'title' => __('Delete Files from Server After Upload', $this->id),
				'type' => 'checkbox',
				'filter' => FILTER_VALIDATE_BOOLEAN,
				'desc' => __('When enabled, the plugin will delete files from the server after they are uploaded to the cloud.', $this->id) . $this->proFeatureSuffix, // phpcs:ignore
				'custom_attributes' => $this->getProFeaturesRequiredCustomAttributes(),
			],
			'fillUploadQueueInterval' => [
				'id' => 'fillUploadQueueInterval',
				'title' => __('Fill Upload Queue Interval', $this->id),
				'type' => 'number',
				'desc' => __('Specify the interval (in seconds) at which the plugin will attempt to fill the upload queue with new files.', $this->id) . $this->proFeatureSuffix, // phpcs:ignore
				'custom_attributes' => $this->getProFeaturesRequiredCustomAttributes(),
			],
			'handleUploadQueueInterval' => [
				'id' => 'handleUploadQueueInterval',
				'title' => __('Handle Upload Queue Interval', $this->id),
				'type' => 'number',
				'desc' => __('Specify the interval (in seconds) at which the plugin will attempt to handle the upload queue.', $this->id) . $this->proFeatureSuffix, // phpcs:ignore
				'custom_attributes' => $this->getProFeaturesRequiredCustomAttributes(),
			],
			'uploadBatchSize' => [
				'id' => 'uploadBatchSize',
				'title' => __('Upload Batch Size', $this->id),
				'type' => 'number',
				'desc' => __('Specify the number of files to upload per iteration.', $this->id) . $this->proFeatureSuffix, // phpcs:ignore
				'custom_attributes' => $this->getProFeaturesRequiredCustomAttributes(),
			],
			'uploadConcurrency' => [
				'id' => 'uploadConcurrency',
				'title' => __('Upload Concurrency', $this->id),
				'type' => 'number',
				'desc' => __('Specify the number of concurrent uploads.', $this->id) . $this->proFeatureSuffix, // phpcs:ignore
				'custom_attributes' => $this->getProFeaturesRequiredCustomAttributes(),
			],
			'general_settings_end' => [
				'type' => 'sectionend',
			],
		];
	}

	/**
	 * Returns fields for the Google Cloud Storage settings form
	 *
	 * @return array
	 */
	protected function getGoogleFormFields(): array
	{
		return [
			'google_title' => [
				'id' => 'google_title',
				'type' => 'title',
				'title' => __('Google Cloud Storage', $this->id),
				'desc' => __('Complete this section to quickly and easily link plugin with your secure, scalable Google Cloud Storage account.', $this->id), // phpcs:ignore
			],
			'accounts[google_1][provider]' => [
				'id' => 'accounts[google_1][provider]',
				'type' => 'hidden',
				'default' => 'google',
			],
			'accounts[google_1][bucketName]' => [
				'id' => 'accounts[google_1][bucketName]',
				'title' => __('Bucket Name', $this->id),
				'type' => 'text',
				'desc' => __('Specify the name of the Google Cloud Storage bucket you wish to connect to. This bucket will be used for storing and retrieving data. Ensure that the provided bucket name is unique and adheres to the naming conventions set by Google Cloud Storage.', $this->id), // phpcs:ignore
			],
			'accounts[google_1][config][keyFile]' => [
				'id' => 'accounts[google_1][config][keyFile]',
				'title' => __('Service Account JSON', $this->id),
				'type' => 'textarea',
				'custom_attributes' => [
					'rows' => 10,
				],
				'desc' => sprintf('%s<br/><a href="%s" target="_blank">%s</a>', // phpcs:ignore
					__('Provide the contents of the JSON key file associated with your Google Cloud service account. This file contains the credentials necessary to authenticate and authorize our system to access your Google Cloud Storage resources. Ensure that the service account has the required permissions to perform the desired actions (e.g., read, write, or delete objects) in your storage buckets.', $this->id), // phpcs:ignore
					'https://1teamsoftware.com/documentation/cloudsync-master/installation-and-configuration/how-to-set-up-google-cloud-storage-for-cloudsync-master-for-wordpress-plugin/', // phpcs:ignore
					__('Learn more about how to set up Google Cloud Storage for CloudSync Master for WordPress plugin', $this->id) // phpcs:ignore
				),
			],
			'google_end' => [
				'type' => 'sectionend',
			],
		];
	}

	/**
	 * returns form fields at the begining of the form
	 *
	 * @return array
	 */
	protected function getFormFieldsBefore(): array
	{
		return [];
	}

	/**
	 * Return success message
	 *
	 * @return string
	 */
	protected function getSuccessMessageText(): string
	{
		return __('Settings have been successfully saved', $this->id);
	}

	/**
	 * returns data that will be displayed in the form
	 *
	 * @return array
	 */
	protected function getFormData(): array
	{
		return $this->settingsStorage->get();
	}

	/**
	 * Saves data and returns true or false and it can also modify input data
	 *
	 * @param array $data
	 * @return bool
	 */
	protected function saveFormData(array &$data): bool
	{
		// remove null values, so they won't override existing values
		foreach ($data as $key => $value) {
			if (is_null($value)) {
				unset($data[$key]);
			}
		}

		// merge previous data with new data, so we can do partial updates
		$this->settingsStorage->update(array_merge($this->settingsStorage->get(), $data));

		do_action($this->id . '_settings_saved', $data);

		return true;
	}

	/**
	 * Returns PRO features required custom attributes
	 *
	 * @return array
	 */
	protected function getProFeaturesRequiredCustomAttributes(): array
	{
		if (empty($this->proFeatureSuffix)) {
			return [];
		}

		return [
			'disabled' => true,
		];
	}
}
