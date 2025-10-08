<?php

use Spatie\Snapshots\MatchesSnapshots;
use TEC\Events\Custom_Tables\V1\Provider as CT1_Provider;
use Tribe\Tests\Traits\With_Uopz;
use Tribe\Events\Views\V2\Hooks;
use Tribe__Events__Main as TEC;
use Tribe__Date_Utils as Dates;
use Tribe__Events__Venue as Venue;

class Tribe__Events__QueryTest extends \Codeception\TestCase\WPTestCase {
	use MatchesSnapshots;
	use With_Uopz;

	/**
	 * @before
	 */
	public function pre_flight_check(): void {
		$this->assertFalse( CT1_Provider::is_active() );
	}


	/**
	 * @before
	 */
	public function set_current_moment(): void {
		add_filter( 'tec_events_query_current_moment', static function () {
			return '2022-09-28 13:00:00';
		} );
	}

	/**
	 * The `before` annotation and `setUp` method will NOT guarantee the
	 * order of execution: this is why this method is called in
	 * each test method!
	 */
	private function unregister_views_v2_hooks(): void {
		// Unregister Views v2 hooks that would modify the query.
		tribe( Hooks::class )->unregister();
	}

	private function given_events_with_post_date_not_aligned_with_start_date(): array {
		global $wpdb;
		$utc = new DateTimeZone( 'UTC' );

		$by_post_date_ids = [];
		$by_start_date_ids = [];
		foreach (
			[
				'2022-09-30 10:00:00' => '2022-09-28 10:00:00',
				'2022-09-28 14:00:00' => '2022-09-28 11:00:00',
				'2022-09-29 10:00:00' => '2022-09-28 12:00:00',
			] as $date => $post_date
		) {
			$created = tribe_events()->set_args( [
				'title'      => "Event on {$date}",
				'start_date' => $date,
				'duration'   => 2 * HOUR_IN_SECONDS,
				'status'     => 'publish',
			] )->create();
			codecept_debug( "Created event {$created->ID} with post date {$post_date} and start date {$date}" );
			$by_post_date_ids[$created->ID]    = $post_date;
			$by_start_date_ids[ $created->ID ] = $date;
			// Space out the post date by 1 second.
			if ( $wpdb->update(
					$wpdb->posts,
					[
						'post_date'     => $post_date,
						'post_date_gmt' => Dates::immutable( $post_date )
							->setTimezone( $utc )
							->format( Dates::DBDATETIMEFORMAT )
					],
					[ 'ID' => $created->ID ]
				) === false ) {
				throw new RuntimeException( 'Failed to update post date' );
			}
			clean_post_cache( $created->ID );
		}


		// Sort by date values while preserving keys (event IDs).
		// Start dates: ascending order (earliest first).
		asort( $by_start_date_ids );
		$by_start_date_ids = array_keys( $by_start_date_ids );

		// Post dates: descending order (newest first) to match WordPress default query order.
		arsort( $by_post_date_ids );
		$by_post_date_ids = array_keys( $by_post_date_ids );

		return [ $by_post_date_ids, $by_start_date_ids ];
	}

	/**
	 * It should filter and order main events query
	 *
	 * @test
	 */
	public function should_filter_and_order_main_events_query() {
		$this->unregister_views_v2_hooks();
		[ $by_post_date_ids, $by_start_date_ids ] = $this->given_events_with_post_date_not_aligned_with_start_date();

		$query = new WP_Query();
		// Make this the main query.
		global $wp_the_query;
		$wp_the_query = $query;

		$this->assertTrue( $query->is_main_query() );

		$query->query( [ 'post_type' => TEC::POSTTYPE ] );
		$sql = $query->request;

		$this->assertMatchesSnapshot( $sql );
		$this->assertEquals( $by_start_date_ids, wp_list_pluck( $query->posts, 'ID' ) );
	}

	/**
	 * It should not filter and order the main query if suppressed
	 *
	 * @test
	 */
	public function should_not_filter_and_order_the_main_query_if_suppressed() {
		$this->unregister_views_v2_hooks();
		[ $by_post_date_ids, $by_start_date_ids ] = $this->given_events_with_post_date_not_aligned_with_start_date();

		$query = new WP_Query();
		// Make this the main query.
		global $wp_the_query;
		$wp_the_query = $query;

		$this->assertTrue( $query->is_main_query() );

		$query->query( [
			'post_type'                    => TEC::POSTTYPE,
			'tribe_suppress_query_filters' => true,
		] );
		$sql = $query->request;

		$this->assertMatchesSnapshot( $sql );
		$this->assertEquals( $by_post_date_ids, wp_list_pluck( $query->posts, 'ID' ) );
	}

	/**
	 * It should filter and order an event query
	 *
	 * @test
	 */
	public function should_filter_and_order_an_event_query() {
		$this->unregister_views_v2_hooks();
		[ $by_post_date_ids, $by_start_date_ids ] = $this->given_events_with_post_date_not_aligned_with_start_date();

		$query = new WP_Query();

		$this->assertFalse( $query->is_main_query() );

		$query->query( [ 'post_type' => TEC::POSTTYPE ] );
		$sql = $query->request;

		$this->assertMatchesSnapshot( $sql );
		$this->assertEquals( $by_start_date_ids, wp_list_pluck( $query->posts, 'ID' ) );
	}

	/**
	 * It should not filter and order a query if suppressed
	 *
	 * @test
	 */
	public function should_not_filter_and_order_a_query_if_suppressed() {
		$this->unregister_views_v2_hooks();
		[ $by_post_date_ids, $by_start_date_ids ] = $this->given_events_with_post_date_not_aligned_with_start_date();

		$query = new WP_Query();

		$this->assertFalse( $query->is_main_query() );

		$query->query( [
			'post_type'                    => TEC::POSTTYPE,
			'tribe_suppress_query_filters' => true,
		] );

		$sql = $query->request;

		$this->assertMatchesSnapshot( $sql );
		$this->assertEquals( $by_post_date_ids, wp_list_pluck( $query->posts, 'ID' ) );
	}

	/**
	 * It should not filter and order a query for non Events CPT
	 *
	 * @test
	 */
	public function should_not_filter_and_order_a_query_for_non_events_cpt(): void {
		$this->unregister_views_v2_hooks();

		// Set up things to simulate a request for an Event with a non-existing ID of 1.
		$_GET[ TEC::POSTTYPE ] = '1';

		$query = new WP_Query();

		$this->assertFalse( $query->is_main_query() );

		// Now, query for Venues: still a TEC CPT, but not an Event.
		$query->query( [ 'post_type' => Venue::POSTTYPE ] );

		$sql = $query->request;

		$this->assertMatchesSnapshot( $sql );
	}

	/**
	 * It should not filter and order query for not only Events
	 * @test
	 */
	public function should_not_filter_and_order_query_for_not_only_events(): void {
		$this->unregister_views_v2_hooks();

		// Set up things to simulate a request for an Event with a non-existing ID of 1.
		$_GET[ TEC::POSTTYPE ] = '1';

		$query = new WP_Query();

		$this->assertFalse( $query->is_main_query() );

		// Now, query for Venues AND Events.
		$query->query( [ 'post_type' => [ Venue::POSTTYPE, TEC::POSTTYPE ] ] );

		$sql = $query->request;

		$this->assertMatchesSnapshot( $sql );
	}

	/**
	 * Tests our parse_query hook when wp_query is nulled.
	 *
	 * @test
	 */
	public function should_parse_query_with_null_global() {
		// Create scenario where WP Query is nulled before parse_query will attempt to access it.
		global $wp_query;
		$query          = new WP_Query();
		$query->is_home = true;
		add_filter( "tribe_context_is_main_query", "__return_false" );
		add_filter( "tribe_context_tec_post_type", "__return_false" );
		add_action( 'parse_query', static function ( $query ) {
			$query->is_home = true;
		}, 1, 1 );
		tribe_update_option( 'showEventsInMainLoop', true );
		$wp_query = null;
		$query->query( [
			'post_type'                    => TEC::POSTTYPE,
			'tribe_suppress_query_filters' => false,
		] );
	}
}
