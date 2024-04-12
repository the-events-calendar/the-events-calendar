<?php

/**
 * Test that only past events are purged
 */

use Tribe__Events__Event_Cleaner_Scheduler as Scheduler;
use Tribe__Date_Utils as Dates;


class Event_Cleaner_Scheduler_Test extends \Codeception\TestCase\WPTestCase {
	protected $settings;

	public function event_settings( $event_date ) {
		$this->settings = array(
			'post_author'           => 1,
			'post_title'            => 'Test event',
			'post_content'          => 'This is event content!',
			'post_status'           => 'publish',
			'EventAllDay'           => false,
			'EventHideFromUpcoming' => true,
			'EventOrganizerID'      => 1,
			'EventVenueID'          => 1,
			'EventShowMapLink'      => true,
			'EventShowMap'          => true,
			'EventStartDate'        => $event_date,
			'EventEndDate'          => $event_date,
			'EventStartHour'        => '01',
			'EventStartMinute'      => '15',
			'EventStartMeridian'    => 'am',
			'EventEndHour'          => '03',
			'EventEndMinute'        => '25',
			'EventEndMeridian'      => 'pm',
		);

		return $this->settings;
	}

	/**
	 * Check to make sure that past events are being correctly selected.
	 */
	public function test_tribe_select_only_past_events() {
		$past_event     = tribe_events()->set_args( [
			'title'      => 'Past Event',
			'start_date' => Dates::build_date_object( '7 months ago' ),
			'duration'   => 3 * HOUR_IN_SECONDS,
			'status'     => 'publish',
		] )->create()->ID;
		$upcoming_event = tribe_events()->set_args( [
			'title'      => 'Upcoming Event',
			'start_date' => Dates::build_date_object( 'tomorrow 10am' ),
			'duration'   => 3 * HOUR_IN_SECONDS,
			'status'     => 'publish',
		] )->create()->ID;

		$cleaner            = new Scheduler();
		$select_past_events = array_map( 'absint', $cleaner->select_events_to_purge( 3 ) );

		$this->assertContains( $past_event, $select_past_events, 'Past events should be selected' );
		$this->assertNotContains( $upcoming_event, $select_past_events, 'Upcoming events are being selected' );
	}
}