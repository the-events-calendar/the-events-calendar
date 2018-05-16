<?php

namespace Tribe\Events;

use Tribe\Events\Test\Testcases\Events_TestCase;

class Unticketed_Cost_Formatting_Test extends Events_TestCase {

	/**
	 * Set some default/common event meta to be used across test events.
	 *
	 * @since TBD
	 */
	public function setUp() {
		parent::setUp();
	}

	/**
	 * Teardown upon test completion.
	 *
	 * @since TBD
 	 */
	public function tearDown() {
		parent::tearDown();
	}

	public function costs_and_currency_positions() {
		return [
			[ '4,92',             'prefix', '$4,92' ],
			[ '4,92',             'suffix', '4,92$' ],
			[ '180.067',          'prefix', '$180.067' ],
			[ '180.067',          'suffix', '180.067$' ],
			[ 'Free',             'prefix', 'Free' ],
			[ 'Free',             'suffix', 'Free' ],
			// [ 'd0 l33t sp35k',    'prefix', 'd0 l33t sp35k' ], @todo Will currently return "Free - $35", need to fix.
			// [ 'd0 l33t sp35k',    'suffix', 'd0 l33t sp35k' ], @todo Will currently return "Free - 35$", need to fix.
			[ '*&^$@#%@',         'prefix', '*&amp;^$@#%@' ],
			[ '*&^$@#%@',         'suffix', '*&amp;^$@#%@' ],
			[ '東京は都会です',     'prefix', '東京は都会です' ],
			[ '東京は都会です',     'suffix', '東京は都会です' ],
			[ '3.00 8.00 125.95', 'prefix', '$3.00 - $125.95' ],
			[ '3.00 8.00 125.95', 'suffix', '3.00$ - 125.95$' ],
			[ '125.95 3 8.00',    'prefix', '$3 - $125.95' ],
			[ '125.95 3 8.00',    'suffix', '3$ - 125.95$' ],
			[ null,               'prefix', '' ],
			[ null,               'suffix', '' ],
			[ 0,                  'prefix', 'Free' ],
			[ 0,                  'suffix', 'Free' ],
			[ '0',                'prefix', 'Free' ],
			[ '0',                'suffix', 'Free' ],
		];
	}

	/**
	 * Formatting methods rely on Cost Utils heavily, so ensure it exists.
	 *
	 * @since TBD
	 */
	public function test_cost_utils_exists() {
		$this->assertTrue( class_exists( 'Tribe__Events__Cost_Utils' ), 'Check that Tribe__Events__Cost_Utils exists' );
	}

	/**
	 * @param mixed $cost_input
	 * @param string $currency_position
	 *
	 * @dataProvider costs_and_currency_positions
	 */
	public function test_unticketed_event_cost_formatting( $cost_input, $currency_position, $output ) {

		$event_id = $this->factory()->event->create( [
			'meta_input' => [
				'_EventCost'             => $cost_input,
				'_EventCurrencyPosition' => $currency_position,
			]
		] );

		$this->assertEquals( $output, tribe_get_cost( $event_id, true ) );
	}

	/**
	 * Test to ensure the "currency follows value" option in Tribe General Settings is the default
	 * currency symbol position choice when making a new event, but *also* that if the event's
	 * currency symbol position is set to something else, it is respected and overrides the
	 * "currency follows value" option.
  	 *
	 * @todo
	 */
	//public function test_inheritance_of_currency_symbol_position_option() {}
}