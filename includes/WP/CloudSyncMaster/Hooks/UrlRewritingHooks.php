<?php

declare(strict_types=1);

namespace OneTeamSoftware\WP\CloudSyncMaster\Hooks;

use OneTeamSoftware\Logger\LoggerInterface;
use OneTeamSoftware\WP\CloudSyncMaster\Image\ImageSizeFetcherInterface;
use OneTeamSoftware\WP\CloudSyncMaster\Transformer\CloudUrlReplacerInterface;
use OneTeamSoftware\WP\CloudSyncMaster\Transformer\CloudUrlTransformerInterface;

class UrlRewritingHooks
{
	/**
	 * @var string
	 */
	private $id;

	/**
	 * @var string
	 */
	private $baseUrl;

	/**
	 * @var CloudUrlTransformerInterface
	 */
	private $cloudUrlTransformer;

	/**
	 * @var CloudUrlReplacerInterface
	 */
	private $cloudUrlReplacer;

	/**
	 * @var ImageSizeFetcherInterface
	 */
	private $imageSizeFetcher;

	/**
	 * @var LoggerInterface
	 */
	private $logger;

	/**
	 * @var bool
	 */
	private $useObjectUrlInAttachmentDialog;

	/**
	 * @var bool
	 */
	private $disableHandlingOfAttachmentUrl;

	/**
	 * constructor.
	 *
	 * @param string $id
	 * @param string $baseUrl
	 * @param bool $useObjectUrlInAttachmentDialog
	 * @param CloudUrlTransformerInterface $cloudUrlTransformer
	 * @param CloudUrlReplacerInterface $cloudUrlReplacer
	 * @param ImageSizeFetcherInterface $imageSizeFetcher
	 * @param LoggerInterface $logger
	 */
	public function __construct(
		string $id,
		string $baseUrl,
		bool $useObjectUrlInAttachmentDialog,
		CloudUrlTransformerInterface $cloudUrlTransformer,
		CloudUrlReplacerInterface $cloudUrlReplacer,
		ImageSizeFetcherInterface $imageSizeFetcher,
		LoggerInterface $logger
	) {
		$this->id = $id;
		$this->baseUrl = $baseUrl;
		$this->cloudUrlTransformer = $cloudUrlTransformer;
		$this->cloudUrlReplacer = $cloudUrlReplacer;
		$this->imageSizeFetcher = $imageSizeFetcher;
		$this->logger = $logger;
		$this->useObjectUrlInAttachmentDialog = $useObjectUrlInAttachmentDialog;
		$this->disableHandlingOfAttachmentUrl = false;
	}

	/**
	 * registers hooks
	 *
	 * @return void
	 */
	public function register(): void
	{
		$this->logger->debug(__FILE__, __LINE__, 'register');

		// WooCommerce Digital Downloads
		add_filter('woocommerce_download_product_filepath', [$this, 'handleWooCommerceDownloadProductFilePath'], 0, 1);

		// Attachments
		add_filter('wp_get_attachment_url', [$this, 'handleAttachmentUrl'], 0, 1);
		add_filter('wp_get_attachment_thumb_url', [$this, 'handleAttachmentThumbUrl'], 0, 1);
		add_filter('attachment_link', [$this, 'handleAttachmentLink'], 0, 1);

		// Frontend page
		add_filter('wp_get_attachment_image_src', [$this, 'handleAttachmentImageSrc'], 10, 4);

		// srcset
		add_filter('wp_calculate_image_srcset', [$this, 'handleCalculateImageSrcSet'], 0, 1);
		// attributes
		add_filter('wp_get_attachment_image_attributes', [$this, 'handleAttachmentImageAttributes'], 0, 1);

		// Posts
		add_filter('the_content', [$this, 'handleTheContent'], PHP_INT_MAX);
		add_filter('the_excerpt', [$this, 'handleTheExcerpt'], PHP_INT_MAX);
		add_filter('content_edit_pre', [$this, 'handleContentEditPre']);
		add_filter('excerpt_edit_pre', [$this, 'handleExcerptEditPre']);

		// Customizer
		add_filter('customize_value_custom_css', [$this, 'handleCustomizeValueCustomCss'], 10, 1);
		add_filter('wp_get_custom_css', [$this, 'handleGetCustomCss'], 10, 1);
		add_filter('theme_mod_background_image', [$this, 'handleThemeModBackgroundImage']);
		add_filter('theme_mod_header_image', [$this, 'handleThemeModHeaderImage']);

		// Widgets
		add_filter('widget_form_callback', [$this, 'replaceUrlsInWidget']);
		add_filter('widget_display_callback', [$this, 'replaceUrlsInWidget']);
		add_filter('customize_value_widget_block', [$this, 'replaceUrlsInWidget']);
		add_filter('widget_block_content', [$this, 'handleWidgetBlockContent']);

		// Block
		add_filter('render_block', [$this, 'handleRenderBlock'], PHP_INT_MAX);

		// EDD
		add_filter('edd_download_files', [$this, 'handleEddDownloadFiles']);

		// Redirect attachment pages to the cloud URL
		add_action('template_redirect', [$this, 'handleTemplateRedirect']);

		// Media Modal Query
		add_action('wp_ajax_query-attachments', [$this, 'handleAjaxQueryAttachment'], 0);
	}

	/**
	 * handles WooCommerce Download Product File Path
	 *
	 * @param string $filePath
	 * @return string
	 */
	public function handleWooCommerceDownloadProductFilePath(string $filePath): string
	{
		$this->logger->debug(__FILE__, __LINE__, 'handleWooCommerceDownloadProductFilePath, filePath: %s', $filePath);

		return $this->toCloudUrl($filePath);
	}

	/**
	 * handles attachment url
	 *
	 * @param string $url
	 * @return string
	 */
	public function handleAttachmentUrl(string $url): string
	{
		// it can be optionally disabled for WP Medial Library
		if ($this->disableHandlingOfAttachmentUrl) {
			return $url;
		}

		$this->logger->debug(__FILE__, __LINE__, 'handleAttachmentUrl URL: %s', $url);

		return $this->toCloudUrl($url);
	}

	/**
	 * handles attachment thumb url
	 *
	 * @param string $url
	 * @return string
	 */
	public function handleAttachmentThumbUrl(string $url): string
	{
		$this->logger->debug(__FILE__, __LINE__, 'handleAttachmentThumbUrl URL: %s', $url);

		return $this->toCloudUrl($url);
	}

	/**
	 * handles attachment link
	 *
	 * @param string $url
	 * @return string
	 */
	public function handleAttachmentLink(string $url): string
	{
		$this->logger->debug(__FILE__, __LINE__, 'handleAttachmentLink URL: %s', $url);

		return $this->toCloudUrl($url);
	}

	/**
	 * handles the content
	 *
	 * @param string $content
	 * @return string
	 */
	public function handleTheContent(string $content): string
	{
		$this->logger->debug(__FILE__, __LINE__, 'handleTheContent');

		return $this->replaceUrls($content);
	}

	/**
	 * handles the excerpt
	 *
	 * @param string $content
	 * @return string
	 */
	public function handleTheExcerpt(string $content): string
	{
		$this->logger->debug(__FILE__, __LINE__, 'handleTheExcerpt');

		return $this->replaceUrls($content);
	}

	/**
	 * handles content edit pre
	 *
	 * @param string $content
	 * @return string
	 */
	public function handleContentEditPre(string $content): string
	{
		$this->logger->debug(__FILE__, __LINE__, 'handleContentEditPre');

		return $this->replaceUrls($content);
	}

	/**
	 * handles excerpt edit pre
	 *
	 * @param string $content
	 * @return string
	 */
	public function handleExcerptEditPre(string $content): string
	{
		$this->logger->debug(__FILE__, __LINE__, 'handleExcerptEditPre');

		return $this->replaceUrls($content);
	}

	/**
	 * handles customize value custom css
	 *
	 * @param string $content
	 * @return string
	 */
	public function handleCustomizeValueCustomCss(string $content): string
	{
		$this->logger->debug(__FILE__, __LINE__, 'handleCustomizeValueCustomCss');

		return $this->replaceUrls($content);
	}

	/**
	 * handles get custom css
	 *
	 * @param string $content
	 * @return string
	 */
	public function handleGetCustomCss(string $content): string
	{
		$this->logger->debug(__FILE__, __LINE__, 'handleGetCustomCss');

		return $this->replaceUrls($content);
	}

	/**
	 * handles theme mod background image
	 *
	 * @param string $url
	 * @return string
	 */
	public function handleThemeModBackgroundImage(string $url): string
	{
		$this->logger->debug(__FILE__, __LINE__, 'handleThemeModBackgroundImage');

		return $this->toCloudUrl($url);
	}

	/**
	 * handles theme mod header image
	 *
	 * @param string $url
	 * @return string
	 */
	public function handleThemeModHeaderImage(string $url): string
	{
		$this->logger->debug(__FILE__, __LINE__, 'handleThemeModHeaderImage');

		return $this->toCloudUrl($url);
	}

	/**
	 * handles widget block content
	 *
	 * @param string $content
	 * @return string
	 */
	public function handleWidgetBlockContent(string $content): string
	{
		$this->logger->debug(__FILE__, __LINE__, 'handleWidgetBlockContent');

		return $this->replaceUrls($content);
	}

	/**
	 * handles render block
	 *
	 * @param string $content
	 * @return string
	 */
	public function handleRenderBlock(string $content): string
	{
		$this->logger->debug(__FILE__, __LINE__, 'handleRenderBlock');

		return $this->replaceUrls($content);
	}

	/**
	 * replaces image url and size
	 *
	 * @param array|false $image
	 * @param int $attachmentId
	 * @return array|false
	 */
	public function handleAttachmentImageSrc($image, $attachmentId) // phpcs:ignore
	{
		if (!is_array($image) || !is_int($attachmentId)) {
			return $image;
		}

		// if it is a local URL then we can't continue
		if ($this->isLocalUrl($image[0])) {
			return $image;
		}

		// if the image already has a size then we can't continue
		if (!empty($image[1]) && !empty($image[2])) {
			return $image;
		}

		$this->logger->debug(__FILE__, __LINE__, 'handleAttachmentImageSrc, attachment ID: %d, image: %s', $attachmentId, print_r($image, true)); //phpcs:ignore

		$url = $image[0];

		$imageSize = $this->imageSizeFetcher->getImageSize($url);
		if (empty($imageSize->getWidth()) || empty($imageSize->getHeight())) {
			$this->logger->debug(__FILE__, __LINE__, 'unable to get image size for %s, we can not continue', $url); //phpcs:ignore
			return $image;
		}

		$image[1] = $imageSize->getWidth();
		$image[2] = $imageSize->getHeight();

		$this->logger->debug(__FILE__, __LINE__, 'handleAttachmentImageSrc, attachment ID: %d, output image: %s', $attachmentId, print_r($image, true)); //phpcs:ignore

		return $image;
	}

	/**
	 * replaces urls in sources
	 *
	 * @param array $sources
	 * @return array
	 */
	public function handleCalculateImageSrcSet(array $sources): array
	{
		$this->logger->debug(__FILE__, __LINE__, 'handleCalculateImageSrcSet, sources: %s', print_r($sources, true)); //phpcs:ignore

		foreach ($sources as &$source) {
			$source['url'] = $this->toCloudUrl($source['url'] ?? '');
		}
		return $sources;
	}

	/**
	 * replaces urls in attributes
	 *
	 * @param array $attributes
	 * @return array
	 */
	public function handleAttachmentImageAttributes(array $attributes): array
	{
		$this->logger->debug(__FILE__, __LINE__, 'handleAttachmentImageAttributes, attributes: %s', print_r($attributes, true)); //phpcs:ignore

		if (isset($attributes['src'])) {
			$attributes['src'] = $this->toCloudUrl($attributes['src']);
		}

		if (isset($attributes['srcset'])) {
			$attributes['srcset'] = $this->replaceUrls($attributes['srcset']);
		}

		return $attributes;
	}

	/**
	 * replaces urls in widget
	 *
	 * @param array $widget
	 * @return array
	 */
	public function replaceUrlsInWidget(array $widget): array
	{
		$this->logger->debug(__FILE__, __LINE__, 'replaceUrlsInWidget, widget: %s', print_r($widget, true)); //phpcs:ignore

		foreach (['text', 'content'] as $key) {
			if (isset($widget[$key])) {
				$widget[$key] = $this->replaceUrls($widget[$key]);
			}
		}

		return $widget;
	}

	/**
	 * replaces urls in download files
	 *
	 * @param array $files
	 * @return array
	 */
	public function handleEddDownloadFiles(array $files): array
	{
		$this->logger->debug(__FILE__, __LINE__, 'handleEddDownloadFiles, files: %s', print_r($files, true)); //phpcs:ignore

		foreach ($files as $key => $file) {
			if (isset($file['file'])) {
				$files[$key]['file'] = $this->toCloudUrl($file['file']);
			}
		}

		return $files;
	}

	/**
	 * redirects attachment URL to Cloud URL
	 *
	 * @return void
	 */
	public function handleTemplateRedirect(): void
	{
		if (!is_404()) {
			return;
		}

		$this->logger->debug(__FILE__, __LINE__, 'handleTemplateRedirect');

		global $wp;

		// Get the requested URL
		$requestedUrl = home_url($wp->request);
		$cloudUrl = $this->toCloudUrl($requestedUrl);

		if (empty($cloudUrl) || $cloudUrl === $requestedUrl) {
			return;
		}

		wp_redirect($cloudUrl, 301);
		exit;
	}

	/**
	 * handles ajax query attachment
	 *
	 * @return void
	 */
	public function handleAjaxQueryAttachment(): void
	{
		add_filter('wp_prepare_attachment_for_js', [$this, 'handlePrepareAttachmentForJs'], 10, 1);

		$this->disableHandlingOfAttachmentUrl = !$this->useObjectUrlInAttachmentDialog;
	}

	/**
	 * prepares attachment for JS
	 *
	 * @param array $response
	 * @return array
	 */
	public function handlePrepareAttachmentForJs(array $response): array
	{
		$this->logger->debug(__FILE__, __LINE__, 'handlePrepareAttachmentForJs, response: %s', print_r($response, true)); //phpcs:ignore

		if (isset($response['url'])) {
			$response['hasCloudStorageObject'] = false === $this->isLocalUrl($this->toCloudUrl($response['url'] ?? ''));
		}

		if (empty($response['sizes']) || !is_array($response['sizes'])) {
			return $response;
		}

		foreach ($response['sizes'] as $key => $size) {
			if (isset($size['url'])) {
				$response['sizes'][$key]['url'] = $this->toCloudUrl($size['url']);
			}
		}

		if (isset($response['sizes']['medium']['url'])) {
			$response['icon'] = $response['sizes']['medium']['url'];
		} elseif (isset($response['url']) && preg_match('/\.(jpg|jpeg|png|gif|svg|bmp|ico|webp)$/i', $response['url'])) {
			$response['icon'] = $this->toCloudUrl($response['url']);
		}

		return $response;
	}

	/**
	 * replaces urls
	 *
	 * @param string $content
	 * @return string
	 */
	private function replaceUrls(string $content): string
	{
		$this->logger->debug(__FILE__, __LINE__, 'replaceUrls');

		return $this->cloudUrlReplacer->replace($content);
	}

	/**
	 * rewrites url
	 *
	 * @param string $url
	 * @return string
	 */
	private function toCloudUrl(string $url): string
	{
		return $this->cloudUrlTransformer->toCloudUrl($url);
	}

	/**
	 * returns true when url is a local url
	 *
	 * @param string $url
	 * @return bool
	 */
	private function isLocalUrl(string $url): bool
	{
		return strpos($url, $this->baseUrl) === 0;
	}
}
