<?php

namespace WildWolf\WordPress\SMTP;

use PHPMailer\PHPMailer\PHPMailer;
use WildWolf\Utils\Singleton;

final class Plugin {
	use Singleton;

	/**
	 * @codeCoverageIgnore
	 */
	private function __construct() {
		add_action( 'plugins_loaded', [ $this, 'plugins_loaded' ] );

		if ( is_admin() ) {
			add_action( 'init', [ Admin::class, 'instance' ] );
		}
	}

	public function plugins_loaded(): void {
		if ( Settings::instance()->is_enabled() ) {
			add_action( 'phpmailer_init', [ $this, 'phpmailer_init' ] );
		}
	}

	public function phpmailer_init( PHPMailer $mailer ): void {
	// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		$settings = Settings::instance();
		$host     = $settings->get_host();

		if ( ! empty( $host ) ) {
			$mailer->isSMTP();
			$mailer->Host = $host;

			$port = $settings->get_port();
			if ( ! empty( $port ) ) {
				$mailer->Port = $port;
			}

			$from_email = $settings->get_from_email();
			$from_name  = $settings->get_from_name();
			if ( ! empty( $from_email ) ) {
				$mailer->setFrom( $from_email, $from_name );
			}

			$sender = $settings->get_sender();
			if ( ! empty( $sender ) ) {
				$mailer->Sender = $sender;
			}

			$replyto_name  = $settings->get_replyto_name();
			$replyto_email = $settings->get_replyto_email();
			if ( ! empty( $replyto_email ) ) {
				$mailer->addReplyTo( $replyto_email, $replyto_name );
			}

			$mailer->SMTPSecure = $settings->get_security();

			$hostname = $settings->get_hostname();
			if ( ! empty( $hostname ) ) {
				$mailer->Hostname = $hostname;
			}

			$username = $settings->get_smtp_username();
			$password = $settings->get_smtp_password();
			if ( ! empty( $username ) ) {
				$mailer->SMTPAuth = true;
				$mailer->Username = $username;
				$mailer->Password = $password;
			}
		}
	// phpcs:enable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
	}
}
