<?php

use Codeception\TestCase\WPTestCase;

class Tribe__Events__Adjacent_EventsTest extends WPTestCase {
	/**
	 * Test a linear closest event list
	 *
	 * The order should be:
	 *
	 *   ID  EventStartDate
	 *   1   2015-12-01 15:00:00
	 *   2   2015-12-02 15:00:00
	 *   3   2015-12-02 15:00:00
	 *   4   2015-12-03 15:00:00
	 */
	public function test_closest_event_linear() {
		$adjacent_events = new Tribe__Events__Adjacent_Events();

		$post_1 = tribe_events()
			->set_args( [
				'title'      => 'Event 1',
				'start_date' => '+1 days 14:00:00',
				'duration'   => HOUR_IN_SECONDS,
				'status'     => 'publish',
			] )
			->create();
		$post_2 = tribe_events()
			->set_args( [
				'title'      => 'Event 2',
				'start_date' => '+1 days 15:00:00',
				'duration'   => HOUR_IN_SECONDS,
				'status'     => 'publish',
			] )
			->create();
		$post_3 = tribe_events()
			->set_args( [
				'title'      => 'Event 3',
				'start_date' => '+2 days 14:00:00',
				'duration'   => HOUR_IN_SECONDS,
				'status'     => 'publish',
			] )
			->create();
		$post_4 = tribe_events()
			->set_args( [
				'title'      => 'Event 4',
				'start_date' => '+2 days 15:00:00',
				'duration'   => HOUR_IN_SECONDS,
				'status'     => 'publish',
			] )
			->create();

		$adjacent_events->set_current_event_id( $post_1->ID );
		$this->assertEquals( null, $adjacent_events->get_closest_event( 'previous' ), "Post 1's previous post should be null" );
		$this->assertEquals( $post_2->ID, $adjacent_events->get_closest_event( 'next' )->ID, "Post 1's next post should be Post 2" );

		$adjacent_events->set_current_event_id( $post_2->ID );
		$this->assertEquals( $post_1->ID, $adjacent_events->get_closest_event( 'previous' )->ID, "Post 2's previous post should be Post 1" );
		$this->assertEquals( $post_3->ID, $adjacent_events->get_closest_event( 'next' )->ID, "Post 2's next post should be Post 3" );

		$adjacent_events->set_current_event_id( $post_3->ID );
		$this->assertEquals( $post_2->ID, $adjacent_events->get_closest_event( 'previous' )->ID, "Post 3's previous post should be Post 2" );
		$this->assertEquals( $post_4->ID, $adjacent_events->get_closest_event( 'next' )->ID, "Post 3's next post should be Post 4" );

		$adjacent_events->set_current_event_id( $post_4->ID );
		$this->assertEquals( $post_3->ID, $adjacent_events->get_closest_event( 'previous' )->ID, "Post 4's previous post should be Post 3" );
		$this->assertEquals( null, $adjacent_events->get_closest_event( 'next' ), "Post 4's next post should be null" );
	}

	/**
	 * Test a non-linear closest event list
	 *
	 * The order should be:
	 *
	 *   ID  EventStartDate
	 *   2   2015-12-01 12:00:00
	 *   1   2015-12-02 15:00:00
	 *   3   2015-12-02 15:00:00
	 *   4   2015-12-02 15:00:00
	 *   5   2015-12-03 16:00:00
	 */
	public function test_closest_event_non_linear() {
		$adjacent_events = new Tribe__Events__Adjacent_Events();

		$post_1 = tribe_events()
			->set_args( [
				'title'      => 'Event 1',
				'start_date' => '+2 days 14:00:00',
				'duration'   => HOUR_IN_SECONDS,
				'status'     => 'publish',
			] )
			->create();
		$post_2 = tribe_events()
			->set_args( [
				'title'      => 'Event 2',
				'start_date' => '+1 days 12:00:00',
				'duration'   => HOUR_IN_SECONDS,
				'status'     => 'publish',
			] )
			->create();
		$post_3 = tribe_events()
			->set_args( [
				'title'      => 'Event 3',
				'start_date' => '+2 days 14:30:00',
				'duration'   => HOUR_IN_SECONDS,
				'status'     => 'publish',
			] )
			->create();
		$post_4 = tribe_events()
			->set_args( [
				'title'      => 'Event 4',
				'start_date' => '+2 days 15:00:00',
				'duration'   => HOUR_IN_SECONDS,
				'status'     => 'publish',
			] )
			->create();
		$post_5 = tribe_events()
			->set_args( [
				'title'      => 'Event 5',
				'start_date' => '+3 days 16:00:00',
				'duration'   => HOUR_IN_SECONDS,
				'status'     => 'publish',
			] )
			->create();

		$adjacent_events->set_current_event_id( $post_1->ID );
		$this->assertEquals( $post_2->ID, $adjacent_events->get_closest_event(  'previous' )->ID, "Post 1's previous post should be Post 2" );
		$this->assertEquals( $post_3->ID, $adjacent_events->get_closest_event(  'next' )->ID, "Post 1's next post should be Post 3" );

		$adjacent_events->set_current_event_id( $post_2->ID );
		$this->assertEquals( null, $adjacent_events->get_closest_event( 'previous' ), "Post 2's previous post should be null" );
		$this->assertEquals( $post_1->ID, $adjacent_events->get_closest_event(  'next' )->ID, "Post 2's next post should be Post 1" );

		$adjacent_events->set_current_event_id( $post_3->ID );
		$this->assertEquals( $post_1->ID, $adjacent_events->get_closest_event(  'previous' )->ID, "Post 3's previous post should be Post 1" );
		$this->assertEquals( $post_4->ID, $adjacent_events->get_closest_event(  'next' )->ID, "Post 3's next post should be Post 4" );

		$adjacent_events->set_current_event_id( $post_4->ID );
		$this->assertEquals( $post_3->ID, $adjacent_events->get_closest_event(  'previous' )->ID, "Post 4's previous post should be Post 3" );
		$this->assertEquals( $post_5->ID, $adjacent_events->get_closest_event(  'next' )->ID, "Post 4's next post should be Post 5" );

		$adjacent_events->set_current_event_id( $post_5->ID );
		$this->assertEquals( $post_4->ID, $adjacent_events->get_closest_event(  'previous' )->ID, "Post 5's previous post should be Post 4" );
		$this->assertEquals( null, $adjacent_events->get_closest_event(  'next' ), "Post 5's next post should be null" );
	}
}
