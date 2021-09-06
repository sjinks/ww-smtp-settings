<?php
/*
 * Plugin Name: WW SMTP Settings
 * Plugin URI: https://github.com/sjinks/wp-login-logger
 * Description: WordPress plugin to send emails via SMTP
 * Version: 2.0.0
 * Author: Volodymyr Kolesnykov
 * License: MIT
 * Text Domain: ww-smtp
 * Domain Path: /lang
 */

use WildWolf\WordPress\SMTP\Plugin;

// @codeCoverageIgnoreStart

if ( defined( 'ABSPATH' ) ) {
	if ( defined( 'VENDOR_PATH' ) ) {
		/** @psalm-suppress UnresolvableInclude, MixedOperand */
		require constant( 'VENDOR_PATH' ) . '/vendor/autoload.php'; // NOSONAR
	} elseif ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
		require __DIR__ . '/vendor/autoload.php';
	} elseif ( file_exists( ABSPATH . 'vendor/autoload.php' ) ) {
		/** @psalm-suppress UnresolvableInclude */
		require ABSPATH . 'vendor/autoload.php';
	}

	Plugin::instance();
}
// @codeCoverageIgnoreEnd
