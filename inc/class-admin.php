<?php

namespace WildWolf\WordPress\SMTP;

use WildWolf\Utils\Singleton;

final class Admin {
	use Singleton;

	const OPTIONS_MENU_SLUG = 'smtp-settings';

	/**
	 * Constructed during `init`
	 *
	 * @codeCoverageIgnore
	 */
	private function __construct() {
		$this->init();
	}

	public function init(): void {
		load_plugin_textdomain( 'ww-smtp', false, plugin_basename( dirname( __DIR__ ) ) . '/lang/' );

		add_action( 'admin_init', [ AdminSettings::class, 'instance' ] );
		add_action( 'admin_init', [ $this, 'admin_init' ] );
		add_action( 'admin_menu', [ $this, 'admin_menu' ] );

		if ( defined( 'DOING_AJAX' ) && constant( 'DOING_AJAX' ) === true ) {
			// @codeCoverageIgnoreStart
			add_action( 'admin_init', [ Ajax::class, 'instance' ] );
			// @codeCoverageIgnoreEnd
		}
	}

	public function admin_init(): void {
		$plugin = plugin_basename( dirname( __DIR__ ) . '/index.php' );
		add_filter( 'plugin_action_links_' . $plugin, [ $this, 'plugin_action_links' ] );
	}

	/**
	 * @codeCoverageIgnore
	 */
	public function admin_menu(): void {
		$hook = add_options_page( __( 'SMTP Settings', 'ww-smtp' ), __( 'SMTP Settings', 'ww-smtp' ), 'manage_options', self::OPTIONS_MENU_SLUG, [ __CLASS__, 'options_page' ] );
		if ( $hook ) {
			add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ], 10, 1 );
		}
	}

	/**
	 * @param array<string,string> $links
	 * @return array<string,string>
	 */
	public function plugin_action_links( array $links ): array {
		$url               = esc_url( admin_url( 'options-general.php?page=' . self::OPTIONS_MENU_SLUG ) );
		$link              = '<a href="' . $url . '">' . __( 'Settings', 'ww-smtp' ) . '</a>';
		$links['settings'] = $link;
		return $links;
	}

	/**
	 * @psalm-suppress UnusedVariable
	 * @codeCoverageIgnore
	 */
	public static function options_page(): void {
		$default_subject = self::get_test_subject();    // NOSONAR
		$default_message = self::get_test_body();       // NOSONAR
		require __DIR__ . '/../views/options.php';
	}

	/**
	 * @param string $hook
	 * @codeCoverageIgnore
	 */
	public function admin_enqueue_scripts( $hook ): void {
		if ( 'settings_page_smtp-settings' === $hook ) {
			wp_enqueue_script(
				'smtp-settings',
				plugins_url( '/assets/settings.min.js', dirname( __DIR__ ) . '/index.php' ),
				[ 'jquery' ],
				(string) filemtime( __DIR__ . '/../assets/settings.min.js' ),
				true
			);

			wp_localize_script(
				'smtp-settings',
				'smtp_settings',
				[
					'ajax_url' => admin_url( 'admin-ajax.php' ),
					'nonce'    => wp_create_nonce( 'smtp-send_test_email' ),
				]
			);
		}
	}

	/**
	 * @codeCoverageIgnore
	 */
	public static function get_test_subject(): string {
		// translators: 1 = blog name
		return sprintf( __( 'Test Email from %s', 'ww-smtp' ), get_bloginfo( 'name' ) );
	}

	/**
	 * @codeCoverageIgnore
	 */
	public static function get_test_body(): string {
		// translators: 1 = blog name, 2: blog URL
		return sprintf( __( 'Test message from %1$s (%2$s)', 'ww-smtp' ), get_bloginfo( 'name' ), get_bloginfo( 'url' ) );
	}
}
