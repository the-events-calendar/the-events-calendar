<?php

namespace Tribe\Events;

use Tribe\Events\Test\Testcases\Events_TestCase;

class Unticketed_Cost_Formatting_Test extends Events_TestCase {

	/**
	 * Container for our test events. Each event will be stored as an array of
	 * [ cost, post_id ] - in other words, this is an array of arrays.
	 *
	 * @var array
	 */
	protected $test_events = [];

	/**
	 * Data and meta data for the test posts we'll be using.
	 *
	 * @var array
	 */
	protected $post_example_settings;

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

	/**
	 * Formatting methods rely on Cost Utils heavily, so ensure it exists.
	 *
	 * @since TBD
	 */
	public function test_cost_utils_exists() {
		$this->assertTrue( class_exists( 'Tribe__Events__Cost_Utils' ), 'Check that Tribe__Events__Cost_Utils exists' );
	}

	/**
	 * Test for single numeric event costs.
	 *
	 * @since TBD
	 */
	public function test_single_numeric_event_cost_formatting() {

		// "$4,92"
		$post_id_1 = $this->factory()->event->create( [
			'meta_input' => [
				'_EventCost'             => '4,92',
				'_EventCurrencyPosition' => 'prefix',
			]
		] );

		$this->assertEquals( '$4,92', tribe_get_cost( $post_id_1, true ), 'Simply add the currency symbol in the correct location.' );

		// // "4,92$"
		$post_id_2 = $this->factory()->event->create( [
			'meta_input' => [
				'_EventCost'             => '4,92',
				'_EventCurrencyPosition' => 'suffix',
			]
		] );

		$this->assertEquals( '4,92$', tribe_get_cost( $post_id_2, true ), 'Simply add the currency symbol in the correct location.' );

		// "$180.067"
		$post_id_3 = $this->factory()->event->create( [
			'meta_input' => [
				'_EventCost'             => '180.067',
				'_EventCurrencyPosition' => 'prefix',
			]
		] );

		$this->assertEquals( '$180.067', tribe_get_cost( $post_id_3, true ), 'Simply add the currency symbol in the correct location.'  );

		// "180.067$"
		$post_id_4 = $this->factory()->event->create( [
			'meta_input' => [
				'_EventCost'             => '180.067',
				'_EventCurrencyPosition' => 'suffix',
			]
		] );

		$this->assertEquals( '180.067$', tribe_get_cost( $post_id_4, true ), 'Simply add the currency symbol in the correct location.' );
	}

	/**
	 * Test for non-numeric event cost values (that aren't "null" either).
	 *
	 * @since TBD
	 */
	public function test_non_numeric_non_null_event_costs_formatting() {

		// "Free"
		$post_id_1 = $this->factory()->event->create( [
			'meta_input' => [
				'_EventCost'             => 'Free',
				'_EventCurrencyPosition' => 'prefix',
			]
		] );

		$this->assertEquals( 'Free', tribe_get_cost( $post_id_1, true ), 'The string "Free" should be escaped and rendered as-is, with no added currency symbol.' );

		// "*&^$@#%@"
		$post_id_2 = $this->factory()->event->create( [
			'meta_input' => [
				'_EventCost'             => '*&^$@#%@',
				'_EventCurrencyPosition' => 'suffix',
			]
		] );

		$this->assertEquals( '*&amp;^$@#%@', tribe_get_cost( $post_id_2, true ), 'The string "*&^$@#%@" should be escaped and rendered as-is, with no added currency symbol.' );

		// "東京は都会です"
		$post_id_3 = $this->factory()->event->create( [
			'meta_input' => [
				'_EventCost'             => '東京は都会です',
				'_EventCurrencyPosition' => 'suffix',
			]
		] );

		$this->assertEquals( '東京は都会です', tribe_get_cost( $post_id_3, true ), 'The string "東京は都会です" should be escaped and rendered as-is, with no added currency symbol.' );
	}

	/**
	 * Test for when the event cost is null.
	 *
	 * @todo
	 */
	// public function test_null_event_cost_formatting() {}

	/**
	 * Test for when the event cost is the int 0.
	 *
	 * @todo should output "Free" string
	 */
	// public function test_zero_int_event_cost_formatting() {}

	/**
	 * Test for when there are a number of costs entered into the event cost field.
	 *
	 * @todo When something like "3.00 8.00 125.95", should output "$3.00 - $125.95" or "3.00$ - 125.95$" depending on post meta option, test both.
	 */
	// public function test_cost_range_event_cost_formatting() {}

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