<?php

namespace TEC\Events\Custom_Tables\V1\WP_Query\Modifiers;

use TEC\Events\Custom_Tables\V1\Models\Event;
use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use Tribe\Tests\Traits\With_Uopz;
use Tribe__Events__Main as TEC;
use WP_Query;

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
	 * Tests an edge case defined in TEC-4695.
	 *
	 * @test
	 */
	public function should_get_posts_with_in_taxonomy_correctly() {
		// Create 3 events
		$post  = tribe_events()->set_args( [
			'title'      => ' Faux event 1',
			'status'     => 'publish',
			'start_date' => 'next week',
			'duration'   => 2 * HOUR_IN_SECONDS,
			'timezone'   => 'America/New_York',
		] )->create();
		$post2 = tribe_events()->set_args( [
			'title'      => ' Faux event 2',
			'status'     => 'publish',
			'start_date' => 'tomorrow',
			'duration'   => 2 * HOUR_IN_SECONDS,
			'timezone'   => 'America/New_York',
		] )->create();
		$post3 = tribe_events()->set_args( [
			'title'      => ' Faux event 3',
			'status'     => 'publish',
			'start_date' => 'next month',
			'duration'   => 2 * HOUR_IN_SECONDS,
			'timezone'   => 'America/New_York',
		] )->create();

		// Test terms
		$show_slugs = [ 'transformers', 'gi-joe', 'he-man' ];
		foreach ( $show_slugs as $show ) {
			$term = get_term_by( 'slug', $show, 'tribe_events_cat' );
			if ( ! $term ) {
				wp_insert_term(
					ucwords( str_replace( '-', ' ', $show ) ), // the term name
					'tribe_events_cat', // the taxonomy
					array(
						'slug' => $show // the term slug
					)
				);
			}
		}

		// Put a post in each category
		wp_set_object_terms( $post->ID, $show_slugs[0], 'tribe_events_cat' );
		wp_set_object_terms( $post2->ID, $show_slugs[1], 'tribe_events_cat' );
		wp_set_object_terms( $post3->ID, $show_slugs[2], 'tribe_events_cat' );

		// Search for all categories, should find these three posts.
		$query_args = [
			'post_type'        => 'tribe_events',
			'post_status'      => 'publish',
			'tax_query'        => [
				[
					'taxonomy' => 'tribe_events_cat',
					'terms'    => $show_slugs,
					'operator' => 'IN',
					'field'    => 'slug',
				],
			],
			'order'            => 'DESC',
			'orderby'          => 'date',
			'suppress_filters' => false,
		];

		/*
		 * Since our Events_Only_Modifier::filter_posts_pre_query() calls get_posts() (subsequently called a second time),
		 * an edge case bug occurs. The first run the taxonomy vars have been compiled and some added state added to them.
		 * Running a second time will use the added term vars, and generate a different query with unintended results.
		 *
		 */
		$query = new WP_Query( $query_args );

		// The `term` would get and additional`AND` on the query,
		// and result in searching for only one category instead of all categories specified.
		$this->assertCount( 3, $query->posts );
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
