<?php

use WildWolf\WordPress\SMTP\Settings;

/**
 * @psalm-import-type SettingsArray from Settings
 * @uses \WildWolf\WordPress\SMTP\Settings
 * @uses \WildWolf\WordPress\SMTP\SettingsValidator
 */
class Test_Settings extends WP_UnitTestCase /* NOSONAR */ {
	public function setUp(): void {
		parent::setUp();
		delete_option( Settings::OPTION_KEY );
		Settings::instance()->refresh();
	}

	/**
	 * @covers \WildWolf\WordPress\SMTP\Settings::offsetSet
	 */
	public function test_offsetSet(): void {
		$sut = Settings::instance();

		$this->expectException( LogicException::class );
		$sut['enabled'] = true;
	}

	/**
	 * @covers \WildWolf\WordPress\SMTP\Settings::offsetUnset
	 */
	public function test_offsetUnset(): void {
		$sut = Settings::instance();

		$this->expectException( LogicException::class );
		unset( $sut['enabled'] );
	}

	/**
	 * @covers \WildWolf\WordPress\SMTP\Settings::offsetExists
	 * @dataProvider data_offsetExists
	 */
	public function test_offsetExists( string $offset, bool $expected ): void {
		$sut = Settings::instance();

		$actual = isset( $sut[ $offset ] );
		self::assertSame( $expected, $actual );
	}

	/**
	 * @psalm-return iterable<int,array{string, bool}>
	 */
	public function data_offsetExists(): iterable {
		return [
			[ 'enabled', true ],
			[ 'disabled', false ],
		];
	}

	/**
	 * @covers \WildWolf\WordPress\SMTP\Settings::offsetGet
	 * @dataProvider data_offsetGet
	 * @param mixed $expected
	 */
	public function test_offsetGet( string $offset, $expected ): void {
		$sut = Settings::instance();

		$actual = $sut[ $offset ];
		self::assertSame( $expected, $actual );
	}

	/**
	 * @psalm-return iterable<int,array{string, mixed}>
	 */
	public function data_offsetGet(): iterable {
		$defaults = Settings::defaults();
		$keys     = array_keys( $defaults );
		$values   = array_values( $defaults );

		return array_map( function ( $key, $value ): array {
			return [ $key, $value ];
		}, $keys, $values);
	}

	/**
	 * @covers \WildWolf\WordPress\SMTP\Settings::as_array
	 * @uses \WildWolf\WordPress\SMTP\Settings::defaults
	 */
	public function test_as_array(): void {
		$sut      = Settings::instance();
		$expected = Settings::defaults();
		$actual   = $sut->as_array();

		self::assertSame( $expected, $actual );
	}

	/**
	 * @covers \WildWolf\WordPress\SMTP\Settings::defaults
	 */
	public function test_defaults(): void {
		$expected = [
			'enabled'       => false,
			'from_email'    => '',
			'from_name'     => '',
			'sender'        => '',
			'hostname'      => '',
			'replyto_name'  => '',
			'replyto_email' => '',
			'host'          => '',
			'port'          => 25,
			'security'      => '',
			'smtp_username' => '',
			'smtp_password' => '',
		];

		$actual = Settings::defaults();
		self::assertSame( $expected, $actual );
	}

	/**
	 * @covers \WildWolf\WordPress\SMTP\Settings::refresh
	 * @uses \WildWolf\WordPress\SMTP\Settings::defaults
	 * @uses \WildWolf\WordPress\SMTP\Settings::as_array
	 * @uses \WildWolf\WordPress\SMTP\SettingsValidator
	 */
	public function test_refresh(): void {
		$sut            = Settings::instance();
		$expected       = Settings::defaults();
		$new            = $expected;
		$new['enabled'] = true;

		update_option( Settings::OPTION_KEY, $new );

		$sut->refresh();

		$actual = $sut->as_array();

		self::assertNotSame( $expected, $actual );
		self::assertSame( array_keys( $expected ), array_keys( $actual ) );
		self::assertSame( $actual, $new );
	}

	/**
	 * @psalm-param mixed[] $settings
	 * @dataProvider data_getters
	 * @uses \WildWolf\WordPress\SMTP\Settings::refresh
	 * @uses \WildWolf\WordPress\SMTP\Settings::defaults
	 * @uses \WildWolf\WordPress\SMTP\SettingsValidator
	 * @covers \WildWolf\WordPress\SMTP\Settings::is_enabled
	 * @covers \WildWolf\WordPress\SMTP\Settings::get_from_email
	 * @covers \WildWolf\WordPress\SMTP\Settings::get_from_name
	 * @covers \WildWolf\WordPress\SMTP\Settings::get_replyto_email
	 * @covers \WildWolf\WordPress\SMTP\Settings::get_replyto_name
	 * @covers \WildWolf\WordPress\SMTP\Settings::get_sender
	 * @covers \WildWolf\WordPress\SMTP\Settings::get_hostname
	 * @covers \WildWolf\WordPress\SMTP\Settings::get_host
	 * @covers \WildWolf\WordPress\SMTP\Settings::get_port
	 * @covers \WildWolf\WordPress\SMTP\Settings::get_security
	 * @covers \WildWolf\WordPress\SMTP\Settings::get_smtp_username
	 * @covers \WildWolf\WordPress\SMTP\Settings::get_smtp_password
	 */
	public function test_getters( array $settings, string $key ): void {
		self::assertArrayHasKey( $key, $settings );
		/** @var mixed */
		$expected = $settings[ $key ];

		$sut = Settings::instance();
		update_option( Settings::OPTION_KEY, $settings );
		$sut->refresh();

		$prefix = gettype( $expected ) === 'boolean' ? 'is_' : 'get_';
		$method = $prefix . $key;
		self::assertTrue( method_exists( $sut, $method ) );

		/** @var mixed */
		$actual = $sut->$method();
		self::assertSame( $settings[ $key ], $actual );
	}

	/**
	 * @psalm-return iterable<array-key, array{array<string,mixed>, string}>
	 */
	public function data_getters(): iterable {
		$settings = [
			'enabled'       => true,
			'from_email'    => 'from@example.com',
			'from_name'     => 'Jane Doe',
			'sender'        => 'sender@example.org',
			'hostname'      => 'local.nato.int',
			'replyto_email' => 'noreply@example.com',
			'replyto_name'  => '/dev/null',
			'host'          => 'nato.int',
			'port'          => 587,
			'security'      => 'tls',
			'smtp_username' => 'secret_user',
			'smtp_password' => 'secret_pass',
		];

		return array_map( function ( $key ) use ( $settings ): array {
			return [ $settings, $key ];
		}, array_keys( $settings ) );
	}
}
