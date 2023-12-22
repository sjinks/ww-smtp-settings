<?php
/*
 * Plugin Name: WW SMTP Settings
 * Plugin URI: https://github.com/sjinks/ww-smtp-settings
 * Description: Send emails via SMTP
 * Version: 1.0.1
 * Author: Volodymyr Kolesnykov
 * Author URI: https://wildwolf.name/
 * License: MIT
 * Text Domain: ww-smtp
 * Domain Path: /lang
 */

use WildWolf\WordPress\SMTP\Plugin;

// @codeCoverageIgnoreStart

if ( defined( 'ABSPATH' ) ) {
	if ( defined( 'VENDOR_PATH' ) ) {
		/** @psalm-suppress UnresolvableInclude, MixedOperand */
		require_once constant( 'VENDOR_PATH' ) . '/vendor/autoload.php';
	} elseif ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
		require_once __DIR__ . '/vendor/autoload.php';
	} elseif ( file_exists( ABSPATH . 'vendor/autoload.php' ) ) {
		require_once ABSPATH . 'vendor/autoload.php';
	}

	Plugin::instance();
}
// @codeCoverageIgnoreEnd
