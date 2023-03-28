<?php

use Tribe__Events__Integrations__WPML__Option as Option;

class Tribe__Events__Integrations__WPML__OptionTest extends \Codeception\TestCase\WPTestCase {

	public function translate_data_provider(): \Generator {
		yield 'empty string' => [
			'',
			'',
			true
		];

		yield 'numeric string' => [
			'123',
			'123',
			false
		];

		// Strings should be translated, numeric strings should not.
		yield 'string' => [
			'some string',
			'some string',
			true
		];

		yield 'array' => [
			[ 'some string' ],
			[ 'some string' ],
			false
		];

		yield 'object' => [
			(object) [ 'some string' ],
			(object) [ 'some string' ],
			false
		];

		yield 'null' => [
			null,
			null,
			false
		];

		yield 'boolean false' => [
			false,
			false,
			false
		];

		yield 'boolean true' => [
			true,
			true,
			false
		];

		yield 'integer' => [
			123,
			123,
			false
		];

		yield 'float' => [
			123.456,
			123.456,
			false
		];
	}

	/**
	 * @dataProvider translate_data_provider
	 */
	public function test_translate( $option_value, $expected, $expected_called ): void {
		$called = false;
		add_filter( 'wpml_translate_single_string', function ( $option_value ) use ( &$called ) {
			$called = true;

			return $option_value;
		} );
		$option = new Option();
		$this->assertEquals( $expected, $option->translate( $option_value, null, 'some_option' ) );
		$this->assertEquals( $expected_called, $called );
	}
}
