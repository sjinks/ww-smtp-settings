<?php

namespace WildWolf\WordPress\SMTP;

use PHPMailer\PHPMailer\PHPMailer;
use WildWolf\Utils\Singleton;

final class AdminSettings {
	use Singleton;

	const OPTION_GROUP = 'smtp_settings';

	/** @var InputFactory */
	private $input_factory;

	/**
	 * Constructed during `admin_init`
	 *
	 * @codeCoverageIgnore
	 */
	private function __construct() {
		$this->register_settings();
	}

	public function register_settings(): void {
		$this->input_factory = new InputFactory( Settings::OPTION_KEY, Settings::instance() );
		register_setting(
			self::OPTION_GROUP,
			Settings::OPTION_KEY,
			[
				'default'           => [],
				'sanitize_callback' => [ SettingsValidator::class, 'sanitize' ],
			]
		);

		$this->add_general_settings();
		$this->add_smtp_settings();
		$this->add_sender_settings();
		$this->add_smtp_auth_settings();
	}

	private function add_general_settings(): void {
		$settings_section = 'general-settings';
		add_settings_section(
			$settings_section,
			__( 'General Settings', 'ww-smtp' ),
			'__return_empty_string', // NOSONAR
			Admin::OPTIONS_MENU_SLUG
		);

		add_settings_field(
			'enabled',
			__( 'Enable plugin', 'ww-smtp' ),
			[ $this->input_factory, 'checkbox' ],
			Admin::OPTIONS_MENU_SLUG,
			$settings_section,
			[
				'label_for' => 'enabled',
			]
		);
	}

	private function add_smtp_settings(): void {
		// @codeCoverageIgnoreStart
		if ( ! class_exists( PHPMailer::class ) ) {
			/** @psalm-suppress UnresolvableInclude, UndefinedConstant */
			require_once ABSPATH . WPINC . '/PHPMailer/PHPMailer.php';
		}
		// @codeCoverageIgnoreEnd

		/** @psalm-var non-empty-array<string> */
		$hostnames = array_filter([
			$_SERVER['SERVER_NAME'] ?? '', // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			gethostname(),
			php_uname( 'n' ),
			'localhost.localdomain',
		]);

		$default_hostname = array_shift( $hostnames );

		$settings_section = 'smtp-settings';
		add_settings_section(
			$settings_section,
			__( 'SMTP Settings', 'ww-smtp' ),
			'__return_empty_string',
			Admin::OPTIONS_MENU_SLUG
		);

		add_settings_field(
			'host',
			__( 'SMTP Host', 'ww-smtp' ),
			[ $this->input_factory, 'input' ],
			Admin::OPTIONS_MENU_SLUG,
			$settings_section,
			[
				'label_for' => 'host',
				'required'  => true,
				'help'      => __(
					'Either a single hostname or multiple semicolon-delimited hostnames.<br/>You can also specify a different port for each host by using this format: <code>smtp1.example.com:25;smtp2.example.com</code>.<br/>You can also specify encryption type, for example: <code>tls://smtp1.example.com:587;ssl://smtp2.example.com:465</code><br/>Hosts will be tried in order.',
					'ww-smtp'
				),
			]
		);

		add_settings_field(
			'port',
			__( 'SMTP Port', 'ww-smtp' ),
			[ $this->input_factory, 'input' ],
			Admin::OPTIONS_MENU_SLUG,
			$settings_section,
			[
				'type'      => 'number',
				'min'       => 0,
				'max'       => 65535,
				'label_for' => 'port',
			]
		);

		add_settings_field(
			'security',
			__( 'SMTP Encryption', 'ww-smtp' ),
			[ $this->input_factory, 'select' ],
			Admin::OPTIONS_MENU_SLUG,
			$settings_section,
			[
				'label_for' => 'security',
				'options'   => [
					''                             => __( 'None', 'ww-smtp' ),
					PHPMailer::ENCRYPTION_SMTPS    => __( 'SSL', 'ww-smtp' ),
					PHPMailer::ENCRYPTION_STARTTLS => __( 'TLS', 'ww-smtp' ),
				],
			]
		);

		add_settings_field(
			'sender',
			__( 'Sender (Return-Path)', 'ww-smtp' ),
			[ $this->input_factory, 'input' ],
			Admin::OPTIONS_MENU_SLUG,
			$settings_section,
			[
				'type'      => 'email',
				'label_for' => 'sender',
				'help'      => __( 'The Return-Path header is an SMTP email source address (SMTP MAIL FROM) used to process the bounces that occur in your emails. The default value is the sender\'s email address.', 'ww-smtp' ),
			]
		);

		add_settings_field(
			'hostname',
			__( 'Sender Hostname (SMTP HELO)', 'ww-smtp' ),
			[ $this->input_factory, 'input' ],
			Admin::OPTIONS_MENU_SLUG,
			$settings_section,
			[
				'type'      => 'email',
				'label_for' => 'sender',
				// translators: %s is the default hostname
				'help'      => sprintf( __( 'Used to identify the domain name of the sending host to SMTP. The default value is <code>%s</code>', 'ww-smtp' ), $default_hostname ),
			]
		);
	}

	private function add_sender_settings(): void {
		$settings_section = 'sender-settings';
		add_settings_section(
			$settings_section,
			__( 'Sender Settings', 'ww-smtp' ),
			'__return_empty_string',
			Admin::OPTIONS_MENU_SLUG
		);

		add_settings_field(
			'from_email',
			__( 'Override sender email address', 'ww-smtp' ),
			[ $this->input_factory, 'input' ],
			Admin::OPTIONS_MENU_SLUG,
			$settings_section,
			[
				'type'      => 'email',
				'label_for' => 'from_email',
			]
		);

		add_settings_field(
			'from_name',
			__( 'Override sender name', 'ww-smtp' ),
			[ $this->input_factory, 'input' ],
			Admin::OPTIONS_MENU_SLUG,
			$settings_section,
			[
				'label_for' => 'from_name',
			]
		);

		add_settings_field(
			'replyto_email',
			__( 'Set Reply-To email address', 'ww-smtp' ),
			[ $this->input_factory, 'input' ],
			Admin::OPTIONS_MENU_SLUG,
			$settings_section,
			[
				'type'      => 'email',
				'label_for' => 'replyto_email',
			]
		);

		add_settings_field(
			'replyto_name',
			__( 'Set Reply-To name', 'ww-smtp' ),
			[ $this->input_factory, 'input' ],
			Admin::OPTIONS_MENU_SLUG,
			$settings_section,
			[
				'label_for' => 'replyto_name',
			]
		);
	}

	private function add_smtp_auth_settings(): void {
		$settings_section = 'smtp-auth-settings';
		add_settings_section(
			$settings_section,
			__( 'SMTP Authentication', 'ww-smtp' ),
			'__return_empty_string',
			Admin::OPTIONS_MENU_SLUG
		);

		add_settings_field(
			'smtp_username',
			__( 'SMTP Username', 'ww-smtp' ),
			[ $this->input_factory, 'input' ],
			Admin::OPTIONS_MENU_SLUG,
			$settings_section,
			[
				'label_for'    => 'smtp_username',
				'help'         => __( 'Leave blank to disable SMTP authentication.', 'ww-smtp' ),
				'autocomplete' => 'off',
			]
		);

		add_settings_field(
			'smtp_password',
			__( 'SMTP Password', 'ww-smtp' ),
			[ $this->input_factory, 'input' ],
			Admin::OPTIONS_MENU_SLUG,
			$settings_section,
			[
				'type'         => 'password',
				'label_for'    => 'smtp_password',
				'autocomplete' => 'off',
			]
		);
	}
}
