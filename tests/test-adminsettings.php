<?php

use WildWolf\WordPress\SMTP\Admin;
use WildWolf\WordPress\SMTP\AdminSettings;
use WildWolf\WordPress\SMTP\Settings;

/**
 * @covers \WildWolf\WordPress\SMTP\AdminSettings
 * @uses \WildWolf\WordPress\SMTP\InputFactory
 */
class Test_AdminSettings extends WP_UnitTestCase /* NOSONAR */ {
	/**
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		AdminSettings::instance()->register_settings();
	}

	public function test_settings_registered(): void {
		global $wp_registered_settings;

		self::assertIsArray( $wp_registered_settings );
		self::assertArrayHasKey( Settings::OPTION_KEY, $wp_registered_settings );
		self::assertIsArray( $wp_registered_settings[ Settings::OPTION_KEY ] );
		self::assertArrayHasKey( 'group', $wp_registered_settings[ Settings::OPTION_KEY ] );
		self::assertSame( AdminSettings::OPTION_GROUP, $wp_registered_settings[ Settings::OPTION_KEY ]['group'] );
	}

	public function test_sections_registered(): void {
		global $wp_settings_sections;

		$sections = [
			'general-settings',
			'smtp-settings',
			'sender-settings',
			'smtp-auth-settings',
		];

		self::assertIsArray( $wp_settings_sections );
		self::assertArrayHasKey( Admin::OPTIONS_MENU_SLUG, $wp_settings_sections );
		self::assertIsArray( $wp_settings_sections[ Admin::OPTIONS_MENU_SLUG ] );

		foreach ( $sections as $name ) {
			self::assertArrayHasKey( $name, $wp_settings_sections[ Admin::OPTIONS_MENU_SLUG ], "{$name} is missing" );
		}
	}

	/**
	 * @uses \WildWolf\WordPress\SMTP\Settings
	 */
	public function test_fields_registered(): void {
		global $wp_settings_fields;

		self::assertIsArray( $wp_settings_fields );
		self::assertArrayHasKey( Admin::OPTIONS_MENU_SLUG, $wp_settings_fields );
		self::assertIsArray( $wp_settings_fields[ Admin::OPTIONS_MENU_SLUG ] );

		$expected_fields = array_keys( Settings::defaults() );
		$actual_fields   = [];

		foreach ( $wp_settings_fields as $section ) {
			self::assertIsArray( $section );
			foreach ( $section as $field ) {
				foreach ( $field as $id => $_ ) {
					$actual_fields[] = $id;
				}
			}
		}

		$this->assertSameSets( $expected_fields, $actual_fields );
	}
}
