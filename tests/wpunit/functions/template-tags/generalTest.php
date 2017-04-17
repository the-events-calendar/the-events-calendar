<?php

namespace Tribe\Events\functions\templateTags;

use Codeception\TestCase\WPTestCase;

class generalTest extends WPTestCase {
	public function separated_field_inputs() {
		return [
			[ '', ' | ', 'Hello', 'Hello' ],
			[ 'Something', ' | ', 'Hello', 'Something | Hello' ],
			[ 'Something', ' | ', '', 'Something' ],
			[ '', '', '', '' ],
			[ 'Something', '', '', 'Something' ],
			[ 'Something', '', 'Hello', 'SomethingHello' ],
		];
	}

	/**
	 * @dataProvider separated_field_inputs
	 */
	public function test_tribe_separated_field( $body, $sep, $field, $expected ) {
		$this->assertEquals( $expected, tribe_separated_field( $body, $sep, $field ) );
	}
}
