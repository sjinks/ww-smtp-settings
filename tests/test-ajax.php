<?php

use WildWolf\WordPress\SMTP\Admin;
use WildWolf\WordPress\SMTP\Ajax;

/**
 * @covers \WildWolf\WordPress\SMTP\Ajax
 * @uses \WildWolf\WordPress\SMTP\Admin::get_test_subject
 * @uses \WildWolf\WordPress\SMTP\Admin::get_test_body
 */
class Test_Ajax extends WP_Ajax_UnitTestCase /* NOSONAR */ {
	/**
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		self::assertTrue( reset_phpmailer_instance() );

		wp_set_current_user( 1 );

		Ajax::instance()->admin_init();
	}

	public function test_wp_ajax_smtp_test_email_defaults(): void {
		$_POST['_wpnonce'] = wp_create_nonce( 'smtp-send_test_email' );

		try {
			$this->_handleAjax( 'smtp_test_email' );
		} catch ( WPAjaxDieContinueException $e ) {
			unset( $e );
		}

		$response = json_decode( $this->_last_response, true, 512, JSON_THROW_ON_ERROR );
		self::assertIsArray( $response );
		self::assertArrayHasKey( 'success', $response );
		self::assertTrue( $response['success'] );

		$mailer = tests_retrieve_phpmailer_instance();
		self::assertInstanceOf( MockPHPMailer::class, $mailer );

		$mail = $mailer->get_sent();
		self::assertIsObject( $mail );
		self::assertObjectHasAttribute( 'to', $mail );
		self::assertIsArray( $mail->to );
		self::assertArrayHasKey( 0, $mail->to );
		self::assertIsArray( $mail->to[0] );
		self::assertArrayHasKey( 0, $mail->to[0] );
		self::assertObjectHasAttribute( 'subject', $mail );
		self::assertObjectHasAttribute( 'body', $mail );

		self::assertSame( wp_get_current_user()->user_email, $mail->to[0][0] );
		self::assertSame( Admin::get_test_subject(), $mail->subject );
		self::assertSame( Admin::get_test_body(), trim( $mail->body ) );
	}

	public function test_wp_ajax_smtp_test_email_bad_nonce(): void {
		try {
			$this->_handleAjax( 'smtp_test_email' );
		} catch ( WPAjaxDieContinueException $e ) {
			unset( $e );
		}

		$this->check_response( true );
		$this->check_no_mail_sent( false );
	}

	public function test_wp_ajax_smtp_test_email_fail(): void {
		$_POST['_wpnonce'] = wp_create_nonce( 'smtp-send_test_email' );
		$_POST['to']       = 'invalid';

		try {
			$this->_handleAjax( 'smtp_test_email' );
		} catch ( WPAjaxDieContinueException $e ) {
			unset( $e );
		}

		$this->check_response( false );
		$this->check_no_mail_sent( true );
	}

	private function check_response( bool $success ): void {
		$response = json_decode( $this->_last_response, true, 512, JSON_THROW_ON_ERROR );
		self::assertIsArray( $response );
		self::assertArrayHasKey( 'success', $response );
		self::assertSame( $success, $response['success'] );
	}

	private function check_no_mail_sent( bool $should_have_triggered_hook ): void {
		$mailer = tests_retrieve_phpmailer_instance();
		self::assertInstanceOf( MockPHPMailer::class, $mailer );

		$mail = $mailer->get_sent();
		self::assertFalse( $mail );

		self::assertSame( $should_have_triggered_hook ? 1 : 0, did_action( 'wp_mail_failed' ) );
	}
}
