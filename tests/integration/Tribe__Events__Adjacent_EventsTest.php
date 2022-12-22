<?php

use Codeception\TestCase\WPTestCase;
use Tribe__Events__Adjacent_Events as Adjacent_Events;
use Tribe__Cache_Listener as Cache_Listener;

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
		$adjacent_events = new Adjacent_Events();

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
		$adjacent_events = new Adjacent_Events();

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
		$this->assertEquals( $post_2->ID, $adjacent_events->get_closest_event( 'previous' )->ID, "Post 1's previous post should be Post 2" );
		$this->assertEquals( $post_3->ID, $adjacent_events->get_closest_event( 'next' )->ID, "Post 1's next post should be Post 3" );

		$adjacent_events->set_current_event_id( $post_2->ID );
		$this->assertEquals( null, $adjacent_events->get_closest_event( 'previous' ), "Post 2's previous post should be null" );
		$this->assertEquals( $post_1->ID, $adjacent_events->get_closest_event( 'next' )->ID, "Post 2's next post should be Post 1" );

		$adjacent_events->set_current_event_id( $post_3->ID );
		$this->assertEquals( $post_1->ID, $adjacent_events->get_closest_event( 'previous' )->ID, "Post 3's previous post should be Post 1" );
		$this->assertEquals( $post_4->ID, $adjacent_events->get_closest_event( 'next' )->ID, "Post 3's next post should be Post 4" );

		$adjacent_events->set_current_event_id( $post_4->ID );
		$this->assertEquals( $post_3->ID, $adjacent_events->get_closest_event( 'previous' )->ID, "Post 4's previous post should be Post 3" );
		$this->assertEquals( $post_5->ID, $adjacent_events->get_closest_event( 'next' )->ID, "Post 4's next post should be Post 5" );

		$adjacent_events->set_current_event_id( $post_5->ID );
		$this->assertEquals( $post_4->ID, $adjacent_events->get_closest_event( 'previous' )->ID, "Post 5's previous post should be Post 4" );
		$this->assertEquals( null, $adjacent_events->get_closest_event( 'next' ), "Post 5's next post should be null" );
	}

	/**
	 * It should cache the result of the closest event query
	 *
	 * @test
	 */
	public function should_cache_the_result_of_the_closest_event_query(): void {
		$previous                = tribe_events()->set_args( [
			'title'      => 'Event 1',
			'start_date' => '-2 days 14:00:00',
			'duration'   => HOUR_IN_SECONDS,
			'status'     => 'publish',
		] )->create();
		$event                   = tribe_events()->set_args( [
			'title'      => 'Event 1',
			'start_date' => '+2 days 14:00:00',
			'duration'   => HOUR_IN_SECONDS,
			'status'     => 'publish',
		] )->create();
		$next                    = tribe_events()->set_args( [
			'title'      => 'Event 1',
			'start_date' => '+10 days 14:00:00',
			'duration'   => HOUR_IN_SECONDS,
			'status'     => 'publish',
		] )->create();
		$before_previous_queries = $this->queries()->countQueries();

		$adjacent_events = new Adjacent_Events();
		$adjacent_events->set_current_event_id( $event->ID );
		$adjacent_events->get_closest_event( 'previous' );
		$adjacent_events->get_closest_event( 'previous' );
		$adjacent_events->get_closest_event( 'previous' );

		$this->assertEquals( 2,
			$this->queries()->countQueries() - $before_previous_queries,
			'The method should make 2 queries: one to fetch the post, one to fetch the closest.'
		);
		$cached = tribe_cache()->get( 'tec_events_closest_event_' . $event->ID . '_previous', Cache_Listener::TRIGGER_SAVE_POST, false );
		$this->assertEquals( $previous->ID, $cached );

		$before_next_queries = $this->queries()->countQueries();

		// Create a new object to make sure the queries are not cached in an instance property.
		$adjacent_events = new Adjacent_Events();
		$adjacent_events->set_current_event_id( $event->ID );
		$adjacent_events->get_closest_event( 'next' );
		$adjacent_events->get_closest_event( 'next' );
		$adjacent_events->get_closest_event( 'next' );

		$this->assertEquals( 2,
			$this->queries()->countQueries() - $before_next_queries,
			'The method should make 2 queries: one to fetch the post, one to fetch the closest.'
		);
		$cached = tribe_cache()->get( 'tec_events_closest_event_' . $event->ID . '_next', Cache_Listener::TRIGGER_SAVE_POST, false );
		$this->assertEquals( $next->ID, $cached );
	}

	/**
	 * It should cache the not found result correctly
	 *
	 * @test
	 */
	public function should_cache_the_not_found_result_correctly(): void {
		$event                   = tribe_events()->set_args( [
			'title'      => 'Event 1',
			'start_date' => '+2 days 14:00:00',
			'duration'   => HOUR_IN_SECONDS,
			'status'     => 'publish',
		] )->create();
		$before_previous_queries = $this->queries()->countQueries();

		$adjacent_events = new Adjacent_Events();
		$adjacent_events->set_current_event_id( $event->ID );
		$adjacent_events->get_closest_event( 'previous' );
		$adjacent_events->get_closest_event( 'previous' );
		$adjacent_events->get_closest_event( 'previous' );

		$this->assertEquals( 1,
			$this->queries()->countQueries() - $before_previous_queries,
			'The method should make 1 query to fetch the closest.'
		);
		$cached = tribe_cache()->get( 'tec_events_closest_event_' . $event->ID . '_previous', Cache_Listener::TRIGGER_SAVE_POST, false );
		$this->assertEquals( null, $cached );

		$before_next_queries = $this->queries()->countQueries();

		// Create a new object to make sure the queries are not cached in an instance property.
		$adjacent_events = new Adjacent_Events();
		$adjacent_events->set_current_event_id( $event->ID );
		$adjacent_events->get_closest_event( 'next' );
		$adjacent_events->get_closest_event( 'next' );
		$adjacent_events->get_closest_event( 'next' );

		$this->assertEquals( 1,
			$this->queries()->countQueries() - $before_next_queries,
			'The method should make 1 query to fetch the closest.'
		);
		$cached = tribe_cache()->get( 'tec_events_closest_event_' . $event->ID . '_next', Cache_Listener::TRIGGER_SAVE_POST, false );
		$this->assertEquals( null, $cached );
	}

	public function bad_cache_values(): \Generator {
		yield 'empty string' => [ '' ];
		yield 'bad post array' => [ [ 'id' => PHP_INT_MAX ] ];
		yield 'wrong post type object' => [ fn() => static::factory()->post->create_and_get() ];
		yield 'wrong post type id' => [ fn() => static::factory()->post->create() ];
		yield 'global post is an event, cached value is false' => [
			function () {
				$GLOBALS['post'] = tribe_events()->set_args( [
					'title'      => 'Event 1',
					'start_date' => '+30 days 14:00:00',
					'duration'   => HOUR_IN_SECONDS,
					'status'     => 'publish',
				] )->create();

				// The event is not cached, but the
				return false;
			}
		];
		yield 'global post is an event, cached value is post' => [
			function () {
				$GLOBALS['post'] = tribe_events()->set_args( [
					'title'      => 'Event 1',
					'start_date' => '+30 days 14:00:00',
					'duration'   => HOUR_IN_SECONDS,
					'status'     => 'publish',
				] )->create();

				// The event is not cached, but the
				return static::factory()->post->create();
			}
		];
	}

	/**
	 * It should survive cache poisoning of the closest event
	 *
	 * @test
	 * @dataProvider bad_cache_values
	 */
	public function should_survive_cache_poisoning_of_the_closest_event( $bad_cache_value ): void {
		if ( $bad_cache_value instanceof Closure ) {
			$bad_cache_value = $bad_cache_value();
		}
		$previous = tribe_events()->set_args( [
			'title'      => 'Event 1',
			'start_date' => '-2 days 14:00:00',
			'duration'   => HOUR_IN_SECONDS,
			'status'     => 'publish',
		] )->create();
		$event    = tribe_events()->set_args( [
			'title'      => 'Event 1',
			'start_date' => '+2 days 14:00:00',
			'duration'   => HOUR_IN_SECONDS,
			'status'     => 'publish',
		] )->create();
		$next     = tribe_events()->set_args( [
			'title'      => 'Event 1',
			'start_date' => '+10 days 14:00:00',
			'duration'   => HOUR_IN_SECONDS,
			'status'     => 'publish',
		] )->create();
		// Poison the caches.
		tribe_cache()->set( 'tec_events_closest_event_' . $event->ID . '_next', $bad_cache_value, WEEK_IN_SECONDS, Cache_Listener::TRIGGER_SAVE_POST );
		tribe_cache()->set( 'tec_events_closest_event_' . $event->ID . '_previous', $bad_cache_value, WEEK_IN_SECONDS, Cache_Listener::TRIGGER_SAVE_POST );

		$adjacent_events = new Adjacent_Events();
		$adjacent_events->set_current_event_id( $event->ID );
		$this->assertEquals( $previous->ID, $adjacent_events->get_closest_event( 'previous' )->ID );
		$this->assertEquals( $next->ID, $adjacent_events->get_closest_event( 'next' )->ID );
	}
}
