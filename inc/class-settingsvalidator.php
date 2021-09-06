<?php

namespace WildWolf\WordPress\SMTP;

use PHPMailer\PHPMailer\PHPMailer;
use WP_Error;

/**
 * @psalm-import-type SettingsArray from Settings
 */
abstract class SettingsValidator {
	/**
	 * @psalm-param mixed[] $settings
	 * @psalm-return SettingsArray
	 */
	public static function ensure_data_shape( array $settings ): array {
		$defaults = Settings::defaults();
		$result   = $settings + $defaults;
		foreach ( $result as $key => $_value ) {
			if ( ! isset( $defaults[ $key ] ) ) {
				unset( $result[ $key ] );
			}
		}

		/** @var mixed $value */
		foreach ( $result as $key => $value ) {
			$my_type    = gettype( $value );
			$their_type = gettype( $defaults[ $key ] );
			if ( $my_type !== $their_type ) {
				settype( $result[ $key ], $their_type );
			}
		}

		/** @psalm-var SettingsArray */
		return $result;
	}

	/**
	 * @param mixed $settings
	 * @psalm-return SettingsArray $settings
	 */
	public static function sanitize( $settings ): array {
		if ( is_array( $settings ) ) {
			$settings = self::ensure_data_shape( $settings );

			$settings['port'] = filter_var( $settings['port'], FILTER_VALIDATE_INT, [
				'options' => [
					'default'   => 0,
					'min_range' => 0,
					'max_range' => 65535,
				],
			] );

			$settings['from_email'] = filter_var( $settings['from_email'], FILTER_VALIDATE_EMAIL, [
				'options' => [ 'default' => '' ],
				'flags'   => FILTER_FLAG_EMAIL_UNICODE,
			] );

			$settings['replyto_email'] = filter_var( $settings['replyto_email'], FILTER_VALIDATE_EMAIL, [
				'options' => [ 'default' => '' ],
				'flags'   => FILTER_FLAG_EMAIL_UNICODE,
			] );

			$settings['sender'] = filter_var( $settings['sender'], FILTER_VALIDATE_EMAIL, [
				'options' => [ 'default' => '' ],
				'flags'   => FILTER_FLAG_EMAIL_UNICODE,
			] );

			if ( ! in_array( $settings['security'], [ PHPMailer::ENCRYPTION_SMTPS, PHPMailer::ENCRYPTION_STARTTLS, '' ], true ) ) {
				$settings['security'] = '';
			}

			return $settings;
		}

		return Settings::defaults();
	}
}
