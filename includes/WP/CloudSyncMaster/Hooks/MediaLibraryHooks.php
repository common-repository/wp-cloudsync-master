<?php

declare(strict_types=1);

namespace OneTeamSoftware\WP\CloudSyncMaster\Hooks;

use OneTeamSoftware\Logger\LoggerInterface;

class MediaLibraryHooks
{
	/**
	 * @var string
	 */
	private $id;

	/**
	 * @var string
	 */
	private $pluginPath;

	/**
	 * @var string
	 */
	private $version;

	/**
	 * @var LoggerInterface
	 */
	private $logger;

	/**
	 * constructor.
	 *
	 * @param string $id
	 * @param string $pluginPath
	 * @param string $version
	 * @param LoggerInterface $logger
	 */
	public function __construct(
		string $id,
		string $pluginPath,
		string $version,
		LoggerInterface $logger
	) {
		$this->id = $id;
		$this->pluginPath = $pluginPath;
		$this->version = $version;
		$this->logger = $logger;
	}

	/**
	 * registers hooks
	 *
	 * @return void
	 */
	public function register(): void
	{
		$this->logger->debug(__FILE__, __LINE__, 'register');

		add_action('admin_footer', [$this, 'addCloudIconToAttachment'], 0, PHP_INT_MAX);
	}

	/**
	 * Adds a cloud icon to the attachment in the media library
	 *
	 * @return void
	 */
	public function addCloudIconToAttachment(): void
	{
		$this->enqueueScripts();

		// @codingStandardsIgnoreStart
		?>
		<script>
			jQuery(document).ready(function($) {
				console.log('addCloudIconToAttachment');
                if (!wp.media || !wp.media.view || !wp.media.view.Attachment) {
                    return;
                }

                var oldAttachmentLibrary = wp.media.view.Attachment.Library;

				wp.media.view.Attachment.Library = oldAttachmentLibrary.extend({
					render: function() {
						oldAttachmentLibrary.prototype.render.apply(this, arguments);

						// Check if the attachment has been uploaded to the cloud
						if (this.model.attributes.hasCloudStorageObject) {
							this.$el.append('<i class="mdi mdi-cloud-outline cloud-icon"></i>');
						}

						return this;
					}
				});
            });
		</script>
		<?php
		// @codingStandardsIgnoreEnd
	}

	/**
	 * Enqueues scripts
	 *
	 * @return void
	 */
	public function enqueueScripts(): void
	{
		$cssExt = 'min.css';
		if (defined('WP_DEBUG') && WP_DEBUG) {
			$cssExt = 'css';
		}

		wp_register_style($this->id . '_materialdesignicons', plugins_url('assets/css/materialdesignicons.' . $cssExt, str_replace('phar://', '', $this->pluginPath)), [], $this->version); // phpcs:ignore
		wp_enqueue_style($this->id . '_materialdesignicons');

		wp_register_style($this->id . '_MediaLibrary', plugins_url('assets/css/MediaLibrary.' . $cssExt, str_replace('phar://', '', $this->pluginPath)), [], $this->version); // phpcs:ignore
		wp_enqueue_style($this->id . '_MediaLibrary');
	}
}