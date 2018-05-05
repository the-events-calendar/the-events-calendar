<?php

namespace Tribe\Events;

class Unticketed_Cost_Formatting_Test extends \Codeception\TestCase\WPTestCase {

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
		// This needs to come first so that the post_example_settings template is created
		parent::setUp();

		$this->post_example_settings = array(
			'post_author'           => 3,
			'post_title'            => 'Unticketed Cost Formatting Test Event ',
			'post_content'          => 'Testing out cost formatting on events when neither Event Tickets nor Event Tickets Plus are active.',
			'post_status'           => 'publish',
			'EventAllDay'           => false,
			'EventHideFromUpcoming' => true,
			'EventOrganizerID'      => 5,
			'EventVenueID'          => 8,
			'EventShowMapLink'      => true,
			'EventShowMap'          => true,
			'EventStartDate'        => '2012-01-01',
			'EventEndDate'          => '2012-01-03',
			'EventStartHour'        => '01',
			'EventStartMinute'      => '15',
			'EventStartMeridian'    => 'am',
			'EventEndHour'          => '03',
			'EventEndMinute'        => '25',
			'EventEndMeridian'      => 'pm',
			'EventCurrencySymbol'   => '$'
		);

		$costs = [
			null, // A null value means the event has no cost, as distinct from being "Free".
			0,    // Should output "Free" string.
			'4,92',
			'4,999',
			'5',
			'5.99',
			'25',
			'100',
			'180.067',
			'3.00 8.00 125.95', // Represents a range of values.
			'Free',             // The cost field should not manipulate this, and just output it literally.
			'*&^$@#%@',         // Ditto: Should not be manipulated, just output literally.
			'東京は都会です',     // Ditto: Should not be manipulated, just output literally.
			'1995.95',
		];

		$iterations = 0;

		foreach ( $costs as $event_cost ) {

			$iterations ++;

			$new_event                    = $this->post_example_settings;
			$new_event['post_title']     .= uniqid();
			$new_event['EventStartDate']  = date_i18n( 'Y-m-d', strtotime( "+$iterations days" ) );

			if ( null !== $event_cost ) {
				$new_event['EventCost'] = $event_cost;
			}

			$this->test_events[] = [
				$event_cost,
				tribe_create_event( $new_event )
			];
		}
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
		$settings = $this->post_example_settings;

		// "$4,92"
		$settings['EventCost']             = '4,92';
		$settings['EventCurrencyPosition'] = 'prefix';
		$post_id_1                         = tribe_create_event( $settings );

		$this->assertEquals( '$4,92', tribe_get_cost( $post_id_1, true ), 'Simply add the currency symbol in the correct location.' );

		// "4,92$"
		$settings['EventCost']             = '4,92';
		$settings['EventCurrencyPosition'] = 'suffix';
		$post_id_2                         = tribe_create_event( $settings );

		$this->assertEquals( '4,92$', tribe_get_cost( $post_id_2, true ), 'Simply add the currency symbol in the correct location.' );

		// "$180.067"
		$settings['EventCost']             = '180.067';
		$settings['EventCurrencyPosition'] = 'prefix';
		$post_id_3                         = tribe_create_event( $settings );

		$this->assertEquals( '$180.067', tribe_get_cost( $post_id_3, true ), 'Simply add the currency symbol in the correct location.'  );

		// "180.067$"
		$settings['EventCost']             = '180.067';
		$settings['EventCurrencyPosition'] = 'suffix';
		$post_id_4                         = tribe_create_event( $settings );

		$this->assertEquals( '180.067$', tribe_get_cost( $post_id_4, true ), 'Simply add the currency symbol in the correct location.' );
	}

	/**
	 * Test for non-numeric event cost values (that aren't "null" either).
	 *
	 * @since TBD
	 */
	public function test_non_numeric_non_null_event_costs_formatting() {
		$settings = $this->post_example_settings;

		// "Free"
		$settings['EventCost']             = 'Free';
		$settings['EventCurrencyPosition'] = 'prefix';
		$post_id_1                         = tribe_create_event( $settings );

		$this->assertEquals( 'Free', tribe_get_cost( $post_id_1, true ), 'The string "Free" should be escaped and rendered as-is, with no added currency symbol.' );

		// "東京は都会です"
		$settings['EventCost']             = '*&^$@#%@';
		$settings['EventCurrencyPosition'] = 'suffix';
		$post_id_2                         = tribe_create_event( $settings );

		$this->assertEquals( '*&amp;^$@#%@', tribe_get_cost( $post_id_2, true ), 'The string "*&^$@#%@" should be escaped and rendered as-is, with no added currency symbol.' );

		// "東京は都会です"
		$settings['EventCost']             = '東京は都会です';
		$settings['EventCurrencyPosition'] = 'suffix';
		$post_id_3                         = tribe_create_event( $settings );

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