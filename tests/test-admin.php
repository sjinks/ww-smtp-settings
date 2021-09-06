<?php

use WildWolf\WordPress\SMTP\Admin;
use WildWolf\WordPress\SMTP\AdminSettings;

/**
 * @covers \WildWolf\WordPress\SMTP\Admin
 * @uses \WildWolf\WordPress\SMTP\InputFactory
 */
class Test_Admin extends WP_UnitTestCase /* NOSONAR */ {
	public function test_initial_hooks_are_set(): void {
		$sut = Admin::instance();
		$sut->init();

		self::assertSame( 10, has_action( 'admin_init', [ AdminSettings::class, 'instance' ] ) );
		self::assertSame( 10, has_action( 'admin_init', [ $sut, 'admin_init' ] ) );
		self::assertSame( 10, has_action( 'admin_menu', [ $sut, 'admin_menu' ] ) );
	}

	/**
	 * @uses \WildWolf\WordPress\SMTP\AdminSettings
	 */
	public function test_admin_init_sets_hooks(): void {
		$sut    = Admin::instance();
		$plugin = plugin_basename( dirname( __DIR__ ) . '/index.php' );

		remove_all_actions( 'admin_init' );
		$sut->init();

		do_action( 'admin_init' );
		self::assertSame( 10, has_action( 'plugin_action_links_' . $plugin, [ $sut, 'plugin_action_links' ] ) );
	}

	public function test_plugin_action_links(): void {
		$actual = Admin::instance()->plugin_action_links( [] );

		self::assertArrayHasKey( 'settings', $actual );
		self::assertIsString( $actual['settings'] );
		self::assertStringContainsString( Admin::OPTIONS_MENU_SLUG, $actual['settings'] );
	}
}
