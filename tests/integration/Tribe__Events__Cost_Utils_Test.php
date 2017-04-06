<?php

class Tribe__Events__Cost_Utils_Test extends \Codeception\TestCase\WPTestCase {
	/**
	 * Container for our test events. Each event will be stored as an array of
	 * [ cost, post_id ] - in other words this is an array of arrays.
	 *
	 * @var array
	 */
	protected $test_events = [];
	protected $post_example_settings;

	/**
	 * Ensure we have events with a range of different costs and also
	 * some events where the cost is not defined.
	 */
	public function setUp() {
		// This needs to come first so that the post_example_settings template is created
		parent::setUp();

		$this->post_example_settings = array(
			'post_author'           => 3,
			'post_title'            => 'Test event',
			'post_content'          => 'This is event content!',
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
		);

		$costs = [
			null, // a null value means the event has no cost - as distinct from being free
			'4,92',
			'4,999',
			'5',
			'5.99',
			'25',
			'100',
			'180.067',
			'3.00 8.00 125.95', // representing a range of values
			'*&^$@#%@',         // cost range related functions should ignore this
			'東京は都会です',     // ...and ignor this one, too
			'1995.95',
		];

		$iterations = 0;

		foreach ( $costs as $event_cost ) {
			$iterations ++;

			$new_event = $this->post_example_settings;
			$new_event['post_title'] .= uniqid();
			$new_event['EventStartDate'] = date_i18n( 'Y-m-d', strtotime( "+$iterations days" ) );

			if ( null !== $event_cost ) {
				$new_event['EventCost'] = $event_cost;
			}

			$this->test_events[] = [
				$event_cost,
				tribe_create_event( $new_event )
			];
		}
	}

	public function test_exists() {
		$this->assertTrue( class_exists( 'Tribe__Events__Cost_Utils' ), 'Check that Tribe__Events__iCal exists' );
	}

	/**
	 * Ensure our min/max cost helpers return the expected results.
	 */
	public function test_cost_range() {
		$this->assertEquals( 3, tribe_get_minimum_cost(),
			'Expect a minimum cost of 3 units'
		);

		$this->assertEquals( 1995.95, tribe_get_maximum_cost(),
			'Expect a maximum cost of 1995.95 units'
		);
	}

	/**
	 *
	 */
	public function test_detect_uncosted_events() {
		// Initially we should be able to detect an event without any cost
		$this->assertTrue( tribe_has_uncosted_events(),
			'We expect to find some events for which a cost has not been defined'
		);

		// Let's remove any uncosted test events and ensure the test works in reverse
		$this->remove_uncosted_events();
		delete_transient( Tribe__Events__Cost_Utils::UNCOSTED_EVENTS_TRANSIENT );

		$this->assertFalse( tribe_has_uncosted_events(),
			'We do not expect to find events without a cost after they have been removed'
		);
	}

	protected function remove_uncosted_events() {
		foreach ( $this->test_events as $costed_event ) {
			list( $cost, $event_id ) = $costed_event;

			if ( null !== $cost ) {
				continue;
			}

			wp_delete_post( $event_id, true );
			unset( $this->test_events[ $event_id ] );
		}

		delete_transient( Tribe__Events__Cost_Utils::UNCOSTED_EVENTS_TRANSIENT );
	}

	/**
	 * Test that cost ranges return appropriate data
	 */
	public function test_parse_cost_range() {
		$costs = array(
			5,
			10,
			1.5,
			'1 - 15',
			'$6',
		);

		$cost_utils = tribe( 'tec.cost-utils' );
		$range = $cost_utils->parse_cost_range( $costs );

		$this->assertEquals( array(
			10  => '1',
			15  => '1.5',
			50  => '5',
			60  => '6',
			100 => '10',
			150 => '15',
		), $range );

		$range = $cost_utils->parse_cost_range( array( 10 ) );
		$this->assertEquals( array( 10 => '10' ), $range );

		$range = $cost_utils->parse_cost_range( array( 'Free' ) );
		$this->assertEquals( array( 'free' => 'Free' ), $range );
	}
}
