<?php
namespace TEC\Test\functions\template_tags;

use Codeception\TestCase\WPTestCase;

class generalTest extends WPTestCase {

	public function setUp() {
		// before
		parent::setUp();

		// your set up methods here
	}

	public function tearDown() {
		// your tear down methods here

		// then
		parent::tearDown();
	}

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
