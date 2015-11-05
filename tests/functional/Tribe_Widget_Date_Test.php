<?php

/**
 * Class Tribe_Widget_Date_Test
 *
 * @group pro
 * @group related
 */
class Tribe_Widget_Date_Test extends Tribe__Events__Pro__WP_UnitTestCase {
	/**
	 * Builds a simple post and sticks it in the global scope
	 */
	public function build_post( $start, $end, $args = array() ) {
		$post_args = [
			'post_title'       => __FUNCTION__,
			'post_content'     => __CLASS__ . ' ' . __FUNCTION__,
			'post_status'      => 'publish',
			'EventStartDate'   => date( 'Y-m-d', $start ),
			'EventEndDate'     => date( 'Y-m-d', $end ),
			'EventStartHour'   => date( 'H', $start ),
			'EventEndHour'     => date( 'H', $end ),
			'EventStartMinute' => date( 'i', $start ),
			'EventEndMinute'   => date( 'i', $end ),
		];

		$post_args = wp_parse_args( $post_args, $args );

		$post_id = Tribe__Events__API::createEvent( $post_args );

		$query = new WP_Query( [
			'p' => $post_id,
			'post_type' => 'tribe_events',
		] );

		$GLOBALS['post'] = $post = $query->post;

		// make sure the global post object holds the post we just created
		setup_postdata( $post );

		return $post;
	}

	/**
	 * Tests the output of the upcoming widget event post date handline for a single event from today
	 */
	public function test_upcoming_widget_date_today() {
		$today = strtotime( date( 'Y-m-d H:00:00' ) );
		$start = strtotime( date( 'Y-m-d H:00:00' ) );
		$end   = strtotime( date( 'Y-m-d H:00:00', strtotime( '+1 hour' ) ) );

		$post = $this->build_post( $start, $end );

		$this->assertEquals(
			date( 'Y-m-d', $start ),
			date( 'Y-m-d', tribe_events_get_widget_event_post_date() ),
			'Upcoming widget start date matches event start date.'
		);
	}

	/**
	 * Tests the output of the upcoming widget event post date handling. The expected behavior is that
	 * it returns the event date OR today's date if the event has already started and is not over yet
	 */
	public function test_upcoming_widget_date_multiday() {
		$today = strtotime( date( 'Y-m-d H:00:00' ) );

		// -------------
		// Test for event that starts yesterday and ends today
		// -------------
		$start = strtotime( date( 'Y-m-d H:00:00', strtotime( '-1 day' ) ) );
		$end   = strtotime( date( 'Y-m-d H:00:00', strtotime( '+1 hour' ) ) );

		$post = $this->build_post( $start, $end );

		$this->assertEquals(
			date( 'Y-m-d', $today ),
			date( 'Y-m-d', tribe_events_get_widget_event_post_date() ),
			'Upcoming widget start date for multi-day event that starts yesterday and ends today.'
		);

		// -------------
		// Test for event that starts yesterday and ends tomorrow
		// -------------
		$start = strtotime( date( 'Y-m-d H:00:00', strtotime( '-1 day' ) ) );
		$end   = strtotime( date( 'Y-m-d H:00:00', strtotime( '+1 day' ) ) );

		$post = $this->build_post( $start, $end );

		$this->assertEquals(
			date( 'Y-m-d', $today ),
			date( 'Y-m-d', tribe_events_get_widget_event_post_date() ),
			'Upcoming widget start date for multi-day event that starts yesterday and ends tomorrow.'
		);

		// -------------
		// Test for event that starts today and ends tomorrow
		// -------------
		$start = strtotime( date( 'Y-m-d H:00:00' ) );
		$end   = strtotime( date( 'Y-m-d H:00:00', strtotime( '+1 day' ) ) );

		$post = $this->build_post( $start, $end );

		$this->assertEquals(
			date( 'Y-m-d', $today ),
			date( 'Y-m-d', tribe_events_get_widget_event_post_date() ),
			'Upcoming widget start date for multi-day event that starts today and ends tomorrow.'
		);

		// -------------
		// Test for event that starts tomorrow and ends two days from now
		// -------------
		$start = strtotime( date( 'Y-m-d H:00:00', strtotime( '+1 day' ) ) );
		$end   = strtotime( date( 'Y-m-d H:00:00', strtotime( '+2 day' ) ) );

		$post = $this->build_post( $start, $end );

		$this->assertEquals(
			date( 'Y-m-d', $start ),
			date( 'Y-m-d', tribe_events_get_widget_event_post_date() ),
			'Upcoming widget start date for multi-day event that starts tomorrow and ends two days from now.'
		);
	}

	/**
	 * Tests the output of the upcoming widget event post date handling. The expected behavior is that
	 * it returns the event date OR today's date if the event has already started and is not over yet
	 */
	public function test_upcoming_widget_date_allday() {
		$today = strtotime( date( 'Y-m-d H:00:00' ) );

		// -------------
		// Test for event that starts and ends today
		// -------------
		$start = strtotime( tribe_beginning_of_day( date( 'Y-m-d' ) ) );
		$end   = strtotime( tribe_end_of_day( date( 'Y-m-d' ) ) );

		$post = $this->build_post( $start, $end, [
			'EventAllDay' => 'yes',
		] );

		$this->assertEquals(
			date( 'Y-m-d', $today ),
			date( 'Y-m-d', tribe_events_get_widget_event_post_date() ),
			'Upcoming widget start date for all-day event that starts and ends today.'
		);

		// -------------
		// Test for event that starts and ends tomorrow
		// -------------
		$start = strtotime( tribe_beginning_of_day( date( 'Y-m-d', strtotime( '+1 day' ) ) ) );
		$end   = strtotime( tribe_end_of_day( date( 'Y-m-d', strtotime( '+1 day' ) ) ) );

		$post = $this->build_post( $start, $end, [
			'EventAllDay' => 'yes',
		] );

		$this->assertEquals(
			date( 'Y-m-d', $start ),
			date( 'Y-m-d', tribe_events_get_widget_event_post_date() ),
			'Upcoming widget start date for all-day event that starts and ends tomorrow.'
		);
	}
}