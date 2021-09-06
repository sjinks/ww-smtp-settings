<?php

namespace WildWolf\WordPress\SMTP;

use ArrayAccess;
use LogicException;
use WildWolf\Utils\Singleton;

/**
 * @psalm-type SettingsArray = array{
 *  enabled: bool,
 *  from_email: string,
 *  from_name: string,
 *  sender: string,
 *  hostname: string,
 *  replyto_name: string,
 *  replyto_email: string,
 *  host: string,
 *  port: int,
 *  security: string,
 *  smtp_username: string,
 *  smtp_password: string
 * }
 *
 * @template-implements ArrayAccess<string, scalar>
 */
final class Settings implements ArrayAccess {
	use Singleton;

	/** @var string  */
	const OPTION_KEY = 'ww_smtp_settings';

	/**
	 * @psalm-readonly
	 * @psalm-var SettingsArray
	 */
	private static $defaults = [
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

	/**
	 * @var array
	 * @psalm-var SettingsArray
	 */
	private $options;

	/**
	 * @codeCoverageIgnore
	 */
	private function __construct() {
		$this->refresh();
	}

	public function refresh(): void {
		/** @var mixed */
		$settings      = get_option( self::OPTION_KEY );
		$this->options = SettingsValidator::ensure_data_shape( is_array( $settings ) ? $settings : [] );
	}

	/**
	 * @psalm-return SettingsArray
	 */
	public static function defaults(): array {
		return self::$defaults;
	}

	/**
	 * @param mixed $offset
	 */
	public function offsetExists( $offset ): bool {
		return isset( $this->options[ (string) $offset ] );
	}

	/**
	 * @param mixed $offset
	 * @return int|string|bool|null
	 */
	public function offsetGet( $offset ) {
		return $this->options[ (string) $offset ] ?? null;
	}

	/**
	 * @param mixed $_offset
	 * @param mixed $_value
	 * @psalm-return never
	 * @throws LogicException
	 */
	public function offsetSet( $_offset, $_value ): void {
		throw new LogicException();
	}

	/**
	 * @param mixed $_offset
	 * @psalm-return never
	 * @throws LogicException
	 */
	public function offsetUnset( $_offset ): void {
		throw new LogicException();
	}

	/**
	 * @psalm-return SettingsArray
	 */
	public function as_array(): array {
		return $this->options;
	}

	public function is_enabled(): bool {
		return $this->options['enabled'];
	}

	public function get_from_name(): string {
		return $this->options['from_name'];
	}

	public function get_from_email(): string {
		return $this->options['from_email'];
	}

	public function get_sender(): string {
		return $this->options['sender'];
	}

	public function get_hostname(): string {
		return $this->options['hostname'];
	}

	public function get_replyto_name(): string {
		return $this->options['replyto_name'];
	}

	public function get_replyto_email(): string {
		return $this->options['replyto_email'];
	}

	public function get_host(): string {
		return $this->options['host'];
	}

	public function get_port(): int {
		return $this->options['port'];
	}

	public function get_security(): string {
		return $this->options['security'];
	}

	public function get_smtp_username(): string {
		return $this->options['smtp_username'];
	}

	public function get_smtp_password(): string {
		return $this->options['smtp_password'];
	}
}
