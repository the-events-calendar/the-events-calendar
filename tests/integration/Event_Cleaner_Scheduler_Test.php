<?php

/**
 * Test that only past events are purged
 */

use Tribe__Events__Event_Cleaner_Scheduler as Scheduler;


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
		$past_event_date     = '2018-01-01';
		$upcoming_event_date = date( 'Y-m-d' );
		$past_event          = tribe_create_event( $this->event_settings( $past_event_date ) );
		$upcoming_event      = tribe_create_event( $this->event_settings( $upcoming_event_date ) );
		$cleaner             = new Scheduler();
		$select_past_events  = $cleaner->select_events_to_purge( 3 );

		$this->assertContains( $past_event, $select_past_events, 'Past events should be selected' );
		$this->assertNotContains( $upcoming_event, $select_past_events, 'Upcoming events are being selected' );
	}
}