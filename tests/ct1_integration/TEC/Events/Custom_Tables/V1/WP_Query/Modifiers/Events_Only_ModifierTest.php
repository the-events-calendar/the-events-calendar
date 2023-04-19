<?php

namespace TEC\Events\Custom_Tables\V1\WP_Query\Modifiers;

use TEC\Events\Custom_Tables\V1\Models\Event;
use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use Tribe\Tests\Traits\With_Uopz;
use Tribe__Events__Main as TEC;

class Events_Only_ModifierTest extends \Codeception\TestCase\WPTestCase {
	use With_Uopz;

	/**
	 * It should not apply to query in admin context
	 *
	 * @test
	 * @preserveGlobalState  disabled
	 * @runInSeparateProcess to avoid carrying over the const to other tests
	 */
	public function should_not_apply_to_query_in_admin_context() {
		$query = new \WP_Query( [ 'post_type' => TEC::POSTTYPE ] );
		define( 'WP_ADMIN', true );

		$modifier = new Events_Only_Modifier();
		$applies  = $modifier->applies_to( $query );

		$this->assertFalse( $applies );
	}

	/**
	 * It should apply to query in AJAX context
	 *
	 * @test
	 */
	public function should_apply_to_query_in_ajax_context() {
		$query = new \WP_Query( [ 'post_type' => TEC::POSTTYPE ] );
		add_filter( 'wp_doing_ajax', '__return_true' );

		$modifier = new Events_Only_Modifier();
		$applies  = $modifier->applies_to( $query );

		$this->assertTrue( $applies );
	}

	/**
	 * It should apply to query in REST context
	 *
	 * @test
	 * @preserveGlobalState  disabled
	 * @runInSeparateProcess to avoid carrying over the const to other tests
	 */
	public function should_apply_to_query_in_rest_context() {
		$query = new \WP_Query( [ 'post_type' => TEC::POSTTYPE ] );
		define( 'REST_REQUEST', true );

		$modifier = new Events_Only_Modifier();
		$applies  = $modifier->applies_to( $query );

		$this->assertTrue( $applies );
	}

	/**
	 * It should correctly filter get_posts queries in admin or AJAX context
	 *
	 * @test
	 */
	public function should_correctly_filter_get_posts_queries_in_admin_or_ajax_context(): void {
		// To avoid test flakiness, fix a moment in time as "now"; this will be used by the TEC query.
		add_filter( 'tec_events_query_current_moment', static fn() => '2020-03-02 08:00:00' );
		// Simulate a /wp-admin request.
		$this->set_fn_return( 'is_admin', true );
		// Create 1 past event and 2 future single events.
		$events   = [];
		$timezone = get_option( 'timezone_string' );
		foreach (
			[
				'2020-03-04 08:00:00',
				'2020-03-14 08:00:00',
				'2020-02-20 08:00:00',
			] as $start_date
		) {
			$event = tribe_events()->set_args( [
				'title'      => $start_date . ' test event',
				'status'     => 'publish',
				'start_date' => $start_date,
				'duration'   => 2 * HOUR_IN_SECONDS,
				'timezone'   => $timezone,
			] )->create();
			$this->assertInstanceOf( Event::class, Event::find( $event->ID, 'post_id' ) );
			$this->assertInstanceOf( Occurrence::class, Occurrence::find( $event->ID, 'post_id' ) );
			$this->assertEquals( 1, Occurrence::where( 'post_id', '=', $event->ID )->count() );
			$events[] = $event->ID;
		}

		$args = [
			'posts_per_page'         => 20,
			'paged'                  => 1,
			'post_type'              => 'tribe_events',
			'orderby'                => 'menu_order title',
			'order'                  => 'ASC',
			'post_status'            => 'any',
			'suppress_filters'       => false,
			'update_post_meta_cache' => false,
		];

		$found = get_posts( $args );

		// We do not care about the order here, just that no date filtering is applied.
		$this->assertEqualSets( $events, wp_list_pluck( $found, 'ID' ) );
	}

	/**
	 * It should correctly filter get_posts queries in front-end context
	 *
	 * @test
	 */
	public function should_correctly_filter_get_posts_queries_in_front_end_context(): void {
		// To avoid test flakiness, fix a moment in time as "now"; this will be used by the TEC query.
		add_filter( 'tec_events_query_current_moment', static fn() => '2020-03-02 08:00:00' );
		// This request does not come from an admin context.
		// Create 1 past event and 2 future single events.
		$events   = [];
		$timezone = get_option( 'timezone_string' );
		foreach (
			[
				'2020-03-14 08:00:00',
				'2020-02-20 08:00:00',
				'2020-03-04 08:00:00',
			] as $start_date
		) {
			$event = tribe_events()->set_args( [
				'title'      => $start_date . ' test event',
				'status'     => 'publish',
				'start_date' => $start_date,
				'duration'   => 2 * HOUR_IN_SECONDS,
				'timezone'   => $timezone,
			] )->create();
			$this->assertInstanceOf( Event::class, Event::find( $event->ID, 'post_id' ) );
			$this->assertInstanceOf( Occurrence::class, Occurrence::find( $event->ID, 'post_id' ) );
			$this->assertEquals( 1, Occurrence::where( 'post_id', '=', $event->ID )->count() );
			$events[] = $event->ID;
		}

		$args = [
			'posts_per_page'         => 20,
			'paged'                  => 1,
			'post_type'              => 'tribe_events',
			'orderby'                => 'menu_order title',
			'order'                  => 'ASC',
			'post_status'            => 'any',
			'suppress_filters'       => false,
			'update_post_meta_cache' => false,
		];

		$found = get_posts( $args );

		$this->assertEquals( [ $events[2], $events[0] ], wp_list_pluck( $found, 'ID' ) );
	}
}
