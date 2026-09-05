<?php

namespace TEC\Events\Custom_Tables\V1\Models\Formatters;

use Codeception\TestCase\WPTestCase;

class Rset_FormatterTest extends WPTestCase {
	public function values_data_provider(): array {
		$rset = "DTSTART;TZID=America/Sao_Paulo:20500105T090000\nRDATE;TZID=America/Sao_Paulo;VALUE=PERIOD:20500112T090000/20500112T100000";

		return [
			'empty string'           => [ '', '' ],
			'null'                   => [ null, '' ],
			'false'                  => [ false, '' ],
			'zero'                   => [ 0, '' ],
			'integer'                => [ 123, '' ],
			'array'                  => [ [ 'RRULE:FREQ=DAILY' ], '' ],
			'plain object'           => [ new \stdClass(), '' ],
			'dates-only rset'        => [ $rset, $rset ],
			'rule-based rset'        => [ "DTSTART:20500105T090000\nRRULE:FREQ=WEEKLY;COUNT=10", "DTSTART:20500105T090000\nRRULE:FREQ=WEEKLY;COUNT=10" ],
			'stringable object'      => [
				new class( $rset ) {
					private string $value;

					public function __construct( string $value ) {
						$this->value = $value;
					}

					public function __toString(): string {
						return $this->value;
					}
				},
				$rset,
			],
		];
	}

	/**
	 * It should format the rset as an opaque string
	 *
	 * @test
	 * @dataProvider values_data_provider
	 */
	public function should_format_the_rset_as_an_opaque_string( $value, string $expected ): void {
		$this->assertSame( $expected, ( new Rset_Formatter() )->format( $value ) );
	}

	/**
	 * It should prepare the value as a string placeholder
	 *
	 * @test
	 */
	public function should_prepare_the_value_as_a_string_placeholder(): void {
		$this->assertSame( '%s', ( new Rset_Formatter() )->prepare() );
	}
}
