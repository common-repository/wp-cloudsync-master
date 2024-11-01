<?php

 /**
 * Plugin Name: CloudSync Master
 * Plugin URI: https://1teamsoftware.com/product/wordpress-cloudsync-master/
 * Description: CloudSync Master is an all-in-one media management solution that seamlessly integrates your WordPress site with popular cloud storage services like Google Cloud Storage. With automatic media uploads, efficient cloud-based serving, and easy retrieval of previously uploaded files, CloudSync Master takes the hassle out of managing your website's media assets and optimizes performance for an enhanced user experience.
 * Version: 1.0.5
 * Tested up to: 6.6
 * Requires PHP: 7.4
 * Author: OneTeamSoftware
 * Author URI: http://oneteamsoftware.com/
 * Developer: OneTeamSoftware
 * Developer URI: http://oneteamsoftware.com/
 * Text Domain: wp-cloudsync-master
 * Domain Path: /languages
 *
 * Copyright: © 2024 FlexRC, Canada.
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

/*********************************************************************
 *  PROGRAM          FlexRC                                          *
 *  PROPERTY         604-1097 View St                                 *
 *  OF               Victoria BC   V8V 0G9                          *
 *                   Voice 604 800-7879                              *
 *                                                                   *
 *  Any usage / copying / extension or modification without          *
 *  prior authorization is prohibited                                *
 *********************************************************************/


namespace OneTeamSoftware\WP\CloudSyncMaster;

defined('ABSPATH') || exit;

if (file_exists(__DIR__ . '/src/autoloader.php') && file_exists(__DIR__ . '/includes/AutoLoader/AutoLoader.php')) {
	include_once __DIR__ . '/includes/AutoLoader/AutoLoader.php';
	include_once __DIR__ . '/src/autoloader.php';
}

if (file_exists(__DIR__ . '/includes/autoloader.php')) {
	include_once __DIR__ . '/includes/autoloader.php';
} else if (file_exists('phar://' . __DIR__ . '/includes.phar/autoloader.php')) {
	include_once 'phar://' . __DIR__ . '/includes.phar/autoloader.php';
}

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
	include_once __DIR__ . '/vendor/autoload.php';
}

if (class_exists(__NAMESPACE__ . '\\Plugin')) {
	(new Plugin(
		__FILE__,
		__('CloudSync Master', 'wp-cloudsync-master'),
		sprintf(
			'<div class="oneteamsoftware notice notice-info inline">
				<p><strong>%s</strong> - %s</p>
				<li><a href="%s" target="_blank">%s</a><br/>
				<li><a href="%s" target="_blank">%s</a><br/>
				<li><a href="https://1teamsoftware.com/documentation/%s/" target="_blank">%s</a><br/>
				<p></p>
			</div>',
			__('CloudSync Master', 'wp-cloudsync-master'),
			__('is an all-in-one media management solution that seamlessly integrates your WordPress site with popular cloud storage services like Google Cloud Storage.', 'wp-cloudsync-master'),
			'https://1teamsoftware.com/contact-us/',
			__('Do you have any questions or requests?', 'wp-cloudsync-master'),
			'https://wordpress.org/plugins/wp-cloudsync-master/',
			__('Do you like our plugin and can recommend it to others?', 'wp-cloudsync-master'),
			'cloudsync-master',
			__('Learn how to configure and use this plugin.', 'wp-cloudsync-master')
		),
		'1.0.5'
	)
	)->register();
} else if (is_admin()) {
	add_action(
		'admin_notices',
		function () {
			echo sprintf(
				'<div class="oneteamsoftware notice notice-error error"><p><strong>%s</strong> %s %s <a href="%s" target="_blank">%s</a> %s</p></div>',
				__('CloudSync Master', 'wp-cloudsync-master'),
				__('plugin can not be loaded.', 'wp-cloudsync-master'),
				__('Please contact', 'wp-cloudsync-master'),
				'https://1teamsoftware.com/contact-us/',
				__('1TeamSoftware support', 'wp-cloudsync-master'),
				__('for assistance.', 'wp-cloudsync-master')
			);
		}
	);
}
