<?php

use WildWolf\WordPress\SMTP\Settings;
use WildWolf\WordPress\SMTP\SettingsValidator;

/**
 * @psalm-import-type SettingsArray from Settings
 */
class Test_SettingsValidator extends WP_UnitTestCase /* NOSONAR */ {
	/**
	 * @psalm-param array<string,mixed> $input
	 * @psalm-param SettingsArray $expected
	 * @dataProvider data_ensure_data_shape
	 * @covers \WildWolf\WordPress\SMTP\SettingsValidator::ensure_data_shape
	 * @uses \WildWolf\WordPress\SMTP\Settings
	 */
	public function test_ensure_data_shape( array $input, array $expected ): void {
		$actual = SettingsValidator::ensure_data_shape( $input );
		self::assertSame( $expected, $actual );
	}

	/**
	 * @psalm-return iterable<string, array{array<string,mixed>, SettingsArray}>
	 */
	public function data_ensure_data_shape(): iterable {
		return [
			'empty'      => [ [], Settings::defaults() ],
			'defaults'   => [ Settings::defaults(), Settings::defaults() ],
			'extra keys' => [ Settings::defaults() + [ 'something' => 'extra' ], Settings::defaults() ],
			'wrong type' => [ [ 'enabled' => 1 ] + Settings::defaults(), [ 'enabled' => true ] + Settings::defaults() ],
		];
	}

	/**
	 * @param mixed $input
	 * @psalm-param SettingsArray $expected
	 * @dataProvider data_sanitize
	 * @covers \WildWolf\WordPress\SMTP\SettingsValidator::sanitize
	 * @covers \WildWolf\WordPress\SMTP\SettingsValidator::ensure_data_shape
	 * @uses \WildWolf\WordPress\SMTP\Settings
	 */
	public function test_sanitize( $input, array $expected ): void {
		$actual = SettingsValidator::sanitize( $input );
		self::assertSame( $expected, $actual );
	}

	/**
	 * @psalm-return iterable<string,array{mixed, SettingsArray}>
	 */
	public function data_sanitize(): iterable {
		return [
			'all at once'  => [
				[
					'port'          => 'bad',
					'from_email'    => 'bad',
					'replyto_email' => 'bad',
					'sender'        => 'bad',
					'security'      => 'bad',
				] + Settings::defaults(),
				[
					'port'          => 0,
					'from_email'    => '',
					'replyto_email' => '',
					'sender'        => '',
					'security'      => '',
				] + Settings::defaults(),
			],
			'bad port'     => [
				[
					'port'          => -1,
					'from_email'    => 'from@example.com',      // NOSONAR
					'replyto_email' => 'replyto@example.com',   // NOSONAR
					'sender'        => 'sender@example.com',    // NOSONAR
					'security'      => '',
				] + Settings::defaults(),
				[
					'port'          => 0,
					'from_email'    => 'from@example.com',
					'replyto_email' => 'replyto@example.com',
					'sender'        => 'sender@example.com',
					'security'      => '',
				] + Settings::defaults(),
			],
			'bad from'     => [
				[
					'port'          => 25,
					'from_email'    => 'from',
					'replyto_email' => 'replyto@example.com',
					'sender'        => 'sender@example.com',
					'security'      => '',
				] + Settings::defaults(),
				[
					'port'          => 25,
					'from_email'    => '',
					'replyto_email' => 'replyto@example.com',
					'sender'        => 'sender@example.com',
					'security'      => '',
				] + Settings::defaults(),
			],
			'bad reply-to' => [
				[
					'port'          => 25,
					'from_email'    => 'from@example.com',
					'replyto_email' => 'replyto',
					'sender'        => 'sender@example.com',
					'security'      => '',
				] + Settings::defaults(),
				[
					'port'          => 25,
					'from_email'    => 'from@example.com',
					'replyto_email' => '',
					'sender'        => 'sender@example.com',
					'security'      => '',
				] + Settings::defaults(),
			],
			'bad sender'   => [
				[
					'port'          => 25,
					'from_email'    => 'from@example.com',
					'replyto_email' => 'replyto@example.com',
					'sender'        => 'sender',
					'security'      => '',
				] + Settings::defaults(),
				[
					'port'          => 25,
					'from_email'    => 'from@example.com',
					'replyto_email' => 'replyto@example.com',
					'sender'        => '',
					'security'      => '',
				] + Settings::defaults(),
			],
			'bad security' => [
				[
					'port'          => 25,
					'from_email'    => 'from@example.com',
					'replyto_email' => 'replyto@example.com',
					'sender'        => 'sender@example.com',
					'security'      => 's s l',
				] + Settings::defaults(),
				[
					'port'          => 25,
					'from_email'    => 'from@example.com',
					'replyto_email' => 'replyto@example.com',
					'sender'        => 'sender@example.com',
					'security'      => '',
				] + Settings::defaults(),
			],
			'not array'    => [ false, Settings::defaults() ],
		];
	}
}
