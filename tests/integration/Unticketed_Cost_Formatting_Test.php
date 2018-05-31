<?php

namespace Tribe\Events;

use Tribe\Events\Test\Testcases\Events_TestCase;

class Unticketed_Cost_Formatting_Test extends Events_TestCase {

	/**
	 * Set some default/common event meta to be used across test events.
	 *
	 * @since 4.6.17
	 */
	public function setUp() {
		parent::setUp();
	}

	/**
	 * Teardown upon test completion.
	 *
	 * @since 4.6.17
 	 */
	public function tearDown() {
		parent::tearDown();
	}

	public function costs_and_currency_positions() {
		return [
			[ '4,92',               'prefix', '$4,92' ],
			[ '4,92',               'suffix', '4,92$' ],
			[ '£5',                 'prefix', '$5' ], // Mixed chars and numbers not allowed!
			[ '£5',                 'suffix', '5$' ],
			[ '180.067',            'prefix', '$180.067' ],
			[ '180.067',            'suffix', '180.067$' ],
			[ 'Free',               'prefix', 'Free' ],
			[ 'Free',               'suffix', 'Free' ],
			[ 'Testing out words.', 'prefix', 'Testing out words.' ],
			[ 'Testing out words.', 'suffix', 'Testing out words.' ],
			[ 'd0 l33t sp35k',      'prefix', 'Free – $35' ], // Mixed letters and numbers aren't allowed!
			[ 'd0 l33t sp35k',      'suffix', 'Free – 35$' ],
			[ '*&^$@#%@',           'prefix', '*&amp;^$@#%@' ],
			[ '*&^$@#%@',           'suffix', '*&amp;^$@#%@' ],
			[ '東京は都会です',       'prefix', '東京は都会です' ],
			[ '東京は都会です',       'suffix', '東京は都会です' ],
			[ '3.00 8.00 125.95',   'prefix', '$3.00 – $125.95' ],
			[ '3.00 8.00 125.95',   'suffix', '3.00$ – 125.95$' ],
			[ '125.95 3 8.00',      'prefix', '$3 – $125.95' ],
			[ '125.95 3 8.00',      'suffix', '3$ – 125.95$' ],
			[ null,                 'prefix', '' ],
			[ null,                 'suffix', '' ],
			[ 0,                    'prefix', 'Free' ],
			[ 0,                    'suffix', 'Free' ],
			[ '0',                  'prefix', 'Free' ],
			[ '0',                  'suffix', 'Free' ],
		];
	}

	/**
	 * Formatting methods rely on Cost Utils heavily, so ensure it exists.
	 *
	 * @since 4.6.17
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
				'_EventCurrencySymbol'   => '$',
			],
		] );

		$this->assertEquals( $output, tribe_get_cost( $event_id, true ) );
	}
}