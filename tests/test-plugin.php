<?php

use PHPMailer\PHPMailer\PHPMailer;
use WildWolf\WordPress\SMTP\Admin;
use WildWolf\WordPress\SMTP\Plugin;
use WildWolf\WordPress\SMTP\Settings;

// phpcs:disable WordPressVIPMinimum.Functions.RestrictedFunctions.wp_mail_wp_mail
// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- blame PHPMailer

/**
 * @covers \WildWolf\WordPress\SMTP\Plugin
 */
class Test_Plugin extends WP_UnitTestCase /* NOSONAR */ {
	public function setUp(): void {
		parent::setUp();
		self::assertTrue( reset_phpmailer_instance() );
	}

	public function test_hooks_are_set_up(): void {
		$sut = Plugin::instance();

		self::assertSame( 10, has_action( 'plugins_loaded', [ $sut, 'plugins_loaded' ] ) );
		self::assertFalse( has_action( 'init', [ Admin::class, 'instance' ] ) );
		self::assertFalse( has_action( 'phpmailer_init', [ $sut, 'phpmailer_init' ] ) );
	}

	/**
	 * @dataProvider data_plugins_loaded_hooks
	 * @param int|bool $outcome
	 * @uses \WildWolf\WordPress\SMTP\Settings
	 * @uses \WildWolf\WordPress\SMTP\SettingsValidator
	 */
	public function test_plugins_loaded_hooks( bool $enable_plugin, $outcome ): void {
		$sut = Plugin::instance();

		update_option( Settings::OPTION_KEY, [ 'enabled' => $enable_plugin ] + Settings::defaults() );
		Settings::instance()->refresh();
		$sut->plugins_loaded();

		self::assertSame( $outcome, has_action( 'phpmailer_init', [ $sut, 'phpmailer_init' ] ) );
	}

	/**
	 * @psalm-return iterable<array-key, array{bool, int|bool}>
	 */
	public function data_plugins_loaded_hooks(): iterable {
		return [
			[ true, 10 ],
			[ false, false ],
		];
	}

	/**
	 * @uses \WildWolf\WordPress\SMTP\Settings
	 * @uses \WildWolf\WordPress\SMTP\SettingsValidator
	 */
	public function test_phpmailer_init_disabled(): void {
		$settings = [ 'host' => '' ];
		update_option( Settings::OPTION_KEY, $settings );
		Settings::instance()->refresh();
		add_action( 'phpmailer_init', [ Plugin::instance(), 'phpmailer_init' ] );

		$result = wp_mail( 'test@localhost.localdomain', 'Test', 'Message' );
		self::assertTrue( $result );

		$mailer = tests_retrieve_phpmailer_instance();
		self::assertInstanceOf( PHPMailer::class, $mailer );

		self::assertNotEquals( 'smtp', $mailer->Mailer );
		self::assertEmpty( $mailer->getReplyToAddresses() );
	}

	/**
	 * @uses \WildWolf\WordPress\SMTP\Settings
	 * @uses \WildWolf\WordPress\SMTP\SettingsValidator
	 */
	public function test_phpmailer_init_full(): void {
		$settings = [
			'enabled'       => true,
			'from_email'    => 'from@example.com',
			'from_name'     => 'From',
			'sender'        => 'sender@example.com',
			'hostname'      => 'wordpress.local',
			'replyto_name'  => 'ReplyTo',
			'replyto_email' => 'replyto@example.com',
			'host'          => 'mailserver.local',
			'port'          => 587,
			'security'      => PHPMailer::ENCRYPTION_STARTTLS,
			'smtp_username' => 'user',
			'smtp_password' => 'pass',
		];

		update_option( Settings::OPTION_KEY, $settings );
		Settings::instance()->refresh();
		add_action( 'phpmailer_init', [ Plugin::instance(), 'phpmailer_init' ] );

		$result = wp_mail( 'test@localhost.localdomain', 'Test', 'Message' );
		self::assertTrue( $result );

		$mailer = tests_retrieve_phpmailer_instance();
		self::assertInstanceOf( PHPMailer::class, $mailer );

		self::assertSame( 'smtp', $mailer->Mailer );

		self::assertSame( $settings['host'], $mailer->Host );
		self::assertSame( $settings['port'], $mailer->Port );
		self::assertSame( $settings['sender'], $mailer->Sender );
		self::assertSame( $settings['hostname'], $mailer->Hostname );
		self::assertSame( $settings['security'], $mailer->SMTPSecure );

		self::assertTrue( $mailer->SMTPAuth );
		self::assertSame( $settings['smtp_username'], $mailer->Username );
		self::assertSame( $settings['smtp_password'], $mailer->Password );

		self::assertSame( $settings['from_name'], $mailer->FromName );
		self::assertSame( $settings['from_email'], $mailer->From );

		$reply_to = $mailer->getReplyToAddresses();
		self::assertCount( 1, $reply_to );
		self::assertArrayHasKey( $settings['replyto_email'], $reply_to );
		self::assertIsArray( $reply_to[ $settings['replyto_email'] ] );
		self::assertCount( 2, $reply_to[ $settings['replyto_email'] ] );
		self::assertArrayHasKey( 0, $reply_to[ $settings['replyto_email'] ] );
		self::assertArrayHasKey( 1, $reply_to[ $settings['replyto_email'] ] );
		self::assertSame( $settings['replyto_email'], $reply_to[ $settings['replyto_email'] ][0] );
		self::assertSame( $settings['replyto_name'], $reply_to[ $settings['replyto_email'] ][1] );
	}
}
