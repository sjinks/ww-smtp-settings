<?php

namespace WildWolf\WordPress\SMTP;

use WildWolf\Utils\Singleton;
use WP_Error;

final class Ajax {
	use Singleton;

	private function __construct() {
		$this->admin_init();
	}

	public function admin_init(): void {
		add_action( 'wp_ajax_smtp_test_email', [ $this, 'wp_ajax_smtp_test_email' ] );
	}

	public function wp_ajax_smtp_test_email(): void {
		if ( false === check_ajax_referer( 'smtp-send_test_email', false, false ) ) {
			wp_send_json_error( __( 'The nonce has expired. Please reload the page and try again', 'ww-smtp' ), 400 );
		}

		add_action( 'wp_mail_failed', [ $this, 'wp_mail_failed' ], 10, 2 );

		/** @psalm-suppress RiskyTruthyFalsyComparison */
		$to = ! empty( $_POST['to'] ) && is_scalar( $_POST['to'] ) ? sanitize_email( (string) $_POST['to'] ) : wp_get_current_user()->user_email;
		/** @psalm-suppress RiskyTruthyFalsyComparison */
		$subject = ! empty( $_POST['subject'] ) && is_scalar( $_POST['subject'] ) ? sanitize_text_field( (string) $_POST['subject'] ) : Admin::get_test_subject();
		/** @psalm-suppress RiskyTruthyFalsyComparison */
		$message = ! empty( $_POST['message'] ) && is_scalar( $_POST['message'] ) ? sanitize_textarea_field( (string) $_POST['message'] ) : Admin::get_test_body();

		if ( wp_mail( $to, $subject, $message ) ) { // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.wp_mail_wp_mail
			wp_send_json_success( __( 'Message has been sent.', 'ww-smtp' ) );
		}
		// @codeCoverageIgnoreStart
	}
	// @codeCoverageIgnoreEnd

	public function wp_mail_failed( WP_Error $error ): void {
		wp_send_json_error( $error->get_error_message(), 400 );
		// @codeCoverageIgnoreStart
	}
	// @codeCoverageIgnoreEnd
}
