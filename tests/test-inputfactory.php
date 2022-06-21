<?php

use WildWolf\WordPress\SMTP\InputFactory;

/**
 * @psalm-suppress MissingConstructor
 * @psalm-import-type InputArgs from InputFactory
 * @covers \WildWolf\WordPress\SMTP\InputFactory
 */
class Test_InputFactory extends WP_UnitTestCase /* NOSONAR */ {
	/** @var InputFactory */
	private $input_factory;

	public function setUp(): void {
		$this->input_factory = new InputFactory( 'option', [ 'somekey' => 'somevalue' ] );
	}

	public function test_input(): void {
		$output = $this->render( 'input', [
			'label_for'    => 'somekey',
			'autocomplete' => 'off',
			'help'         => 'Help Text',
		], $this->input_factory );

		self::assertStringContainsString( '<p class="help">Help Text</p>', $output );
		self::assertStringContainsString( 'autocomplete="off"', $output );
		self::assertStringContainsString( 'id="somekey"', $output );
		self::assertStringContainsString( 'name="option[somekey]"', $output );
	}

	public function test_select(): void {
		$output = $this->render( 'select', [
			'label_for' => 'somekey',
			'options'   => [
				'anotherval' => 'Another Value',
				'somevalue'  => 'Some Value',
			],
		], $this->input_factory );

		self::assertStringContainsString( '<select name="option[somekey]" id="somekey">', $output );
		self::assertStringContainsString( '<option value="anotherval">Another Value</option>', $output );
		self::assertStringContainsString( '<option value="somevalue" selected=\'selected\'>Some Value</option>', $output );
	}

	/**
	 * @dataProvider data_checkbox
	 */
	public function test_checkbox( bool $checked ): void {
		$this->input_factory = new InputFactory( 'option', [ 'somekey' => $checked ] );
		$output              = $this->render( 'checkbox', [ 'label_for' => 'somekey' ], $this->input_factory );

		self::assertStringNotContainsString( '<p class="help">', $output );
		self::assertStringContainsString( '<input type="hidden" name="option[somekey]" value="0"/>', $output );
		self::assertStringContainsString( 'type="checkbox"', $output );
		self::assertStringContainsString( 'name="option[somekey]" id="somekey" value="1"', $output );

		if ( $checked ) {
			self::assertStringContainsString( "checked='checked'", $output );
		} else {
			self::assertStringNotContainsString( "checked='checked'", $output );
		}
	}

	/**
	 * @psalm-return iterable<array-key,array{bool}>
	 */
	public function data_checkbox(): iterable {
		return [
			[ true ],
			[ false ],
		];
	}

	public function test_get_attributes_non_scalar(): void {
		$output = $this->render( 'input', [
			'label_for'    => 'somekey',
			'autocomplete' => [ 1, 2, 3 ],
		], $this->input_factory );

		self::assertStringNotContainsString( 'autocomplete', $output );
	}

	public function test_get_attributes_false(): void {
		$output = $this->render( 'input', [
			'label_for' => 'somekey',
			'required'  => false,
		], $this->input_factory );

		self::assertStringNotContainsString( 'required', $output );
	}

	public function test_get_attributes_true(): void {
		$output = $this->render( 'input', [
			'label_for' => 'somekey',
			'required'  => true,
		], $this->input_factory );

		self::assertStringContainsString( 'required="required', $output );
	}

	private function render( string $method, array $args, InputFactory $factory ): string {
		ob_start();
		$factory->$method( $args );
		$result = ob_get_clean();

		self::assertIsString( $result );
		return $result;
	}
}
