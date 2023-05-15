<?php

namespace TEC\Test\functions\template_tags;

use Codeception\TestCase\WPTestCase;
use PHPUnit\Framework\AssertionFailedError;
use Tribe\Events\Test\Factories\Event;
use Tribe\Events\Test\Factories\Organizer;
use Tribe\Events\Test\Factories\Venue;
use Tribe\Test\PHPUnit\Traits\With_Filter_Manipulation;
use Tribe\Utils\Date_I18n_Immutable;
use Tribe__Cache_Listener as Cache_Listener;
use Tribe__Events__Timezones as Timezones;

class eventTest extends WPTestCase {
	use With_Filter_Manipulation;

	private $using_object_cache_backup;

	public function setUp() {
		parent::setUp();

		tribe_unset_var( \Tribe__Settings_Manager::OPTION_CACHE_VAR_NAME );

		static::factory()->event     = new Event();
		static::factory()->organizer = new Organizer();
		static::factory()->venue     = new Venue();
	}

	/**
	 * @before
	 */
	public function  wp_using_ext_object_cache_backup():void{
		$this->using_object_cache_backup = wp_using_ext_object_cache();
	}

	/**
	 * @after
	 */
	public function wp_using_ext_object_cache_backup_restore(): void {
		wp_using_ext_object_cache( $this->using_object_cache_backup );
	}

	/**
	 * Test tribe_get_event returns null for non-existing event
	 */
	public function test_tribe_get_event_returns_null_for_non_existing_event() {
		// Sanity check: let's make sure this does not exist.
		$this->assertNull( get_post( 23 ) );

		$this->assertNull( tribe_get_event( 23 ) );
	}

	/**
	 * Edge case bug where global post was being reset in a loop. This verifies the lazy objects doesn't do that again.
	 */
	public function test_tribe_get_event_properties_retains_global_post() {
		global $wp_query;
		$global_post     = static::factory()->event->create_and_get();
		$post            = static::factory()->event->create_and_get();
		$wp_query->post  = $global_post;
		$GLOBALS['post'] = $post;
		setup_postdata( $post );
		$this->assertEquals( get_post(), $post );
		$event = tribe_get_event( $post );
		$props = $event->to_array();
		foreach ( $props as $value ) {
			// We have some lazy objects that do magic. Let's make sure it's safe.
			json_encode( $value );
		}
		// Ensure global retains.
		$this->assertEquals( get_post(), $post );
	}

	/**
	 * Test tribe_get_event allows filtering the post before any request is made
	 */
	public function test_tribe_get_event_allows_filtering_the_post_before_any_request_is_made() {
		$event = static::factory()->event->create_and_get();

		$count = $this->queries()->countQueries();

		// Delete the cache to make sure a new fetch would be triggered by `get_post` calls.
		wp_cache_delete( $event->ID, 'posts' );

		add_filter( 'tribe_get_event_before', static function () use ( $event ) {
			return $event;
		} );

		// Pass the ID to force a `get_post` call if not filtered.
		tribe_get_event( $event->ID );

		$this->assertEquals( $count, $this->queries()->countQueries() );
	}

	/**
	 * Test tribe_get_event returns a WP_Post object
	 */
	public function test_tribe_get_event_returns_a_wp_post_object() {
		$event = static::factory()->event->create_and_get();

		$result = tribe_get_event( $event );

		$this->assertInstanceOf( \WP_Post::class, $result );
	}

	/**
	 * Test tribe_get_event attaches a default set of properties to the post
	 */
	public function test_tribe_get_event_attaches_a_default_set_of_properties_to_the_post() {
		$event_id = static::factory()->event->create();

		$event = tribe_get_event( $event_id );

		$expected = [
			'start_date'     => get_post_meta( $event_id, '_EventStartDate', true ),
			'start_date_utc' => get_post_meta( $event_id, '_EventStartDateUTC', true ),
			'end_date'       => get_post_meta( $event_id, '_EventEndDate', true ),
			'end_date_utc'   => get_post_meta( $event_id, '_EventEndDateUTC', true ),
			'timezone'       => Timezones::get_event_timezone_string( $event_id ),
			'duration'       => get_post_meta( $event_id, '_EventDuration', true ),
			'all_day'        => false,
		];

		foreach ( $expected as $key => $value ) {
			$isset_message = "Property '{$key}'' is not set on the event object.";
			$this->assertTrue( isset( $event->{$key} ), $isset_message );
			$value_message = "Property '{$key}' has wrong value.";
			$this->assertEquals( $value, $event->{$key}, $value_message );
		}
	}

	/**
	 * Test tribe_get_event multiday property
	 */
	public function test_tribe_get_event_multiday_property() {
		$non_multiday_event        = static::factory()->event->create();
		$three_days_multiday_event = static::factory()->event->create( [
			'when'     => '2018-01-01 09:00:00',
			'duration' => 2 * DAY_IN_SECONDS
		] );
		$six_days_multiday_event   = static::factory()->event->create( [
			'when'     => '2018-01-01 09:00:00',
			'duration' => 5 * DAY_IN_SECONDS
		] );

		$this->assertFalse( false, tribe_get_event( $non_multiday_event )->multiday );
		$this->assertEquals( 3, tribe_get_event( $three_days_multiday_event )->multiday );
		$this->assertEquals( 6, tribe_get_event( $six_days_multiday_event )->multiday );
	}

	/**
	 * Test tribe_get_event multiday prop w diff. cutoff
	 */
	public function test_tribe_get_event_multiday_prop_w_diff_cutoff() {
		tribe_update_option( 'multiDayCutoff', '02:00' );

		$to_11_pm    = static::factory()->event->create( [
			'when'     => '2019-01-01 22:00:00',
			'duration' => 1 * HOUR_IN_SECONDS
		] );
		$to_midnight = static::factory()->event->create( [
			'when'     => '2019-01-01 22:00:00',
			'duration' => 2 * HOUR_IN_SECONDS
		] );
		$to_1_am     = static::factory()->event->create( [
			'when'     => '2019-01-01 22:00:00',
			'duration' => 3 * HOUR_IN_SECONDS
		] );
		$to_2_am     = static::factory()->event->create( [
			'when'     => '2019-01-01 22:00:00',
			'duration' => 4 * HOUR_IN_SECONDS
		] );
		$to_6_am     = static::factory()->event->create( [
			'when'     => '2019-01-01 22:00:00',
			'duration' => 8 * HOUR_IN_SECONDS
		] );
		// Three days: [22, 02], [02, 02], [02,04].
		$to_4_am_two_days_after = static::factory()->event->create( [
			'when'     => '2019-01-01 22:00:00',
			'duration' => DAY_IN_SECONDS + 6 * HOUR_IN_SECONDS
		] );

		foreach (
			[
				$to_11_pm,
				$to_midnight,
				$to_1_am,
				$to_2_am,
			] as $id
		) {
			$this->assertFalse( false, tribe_get_event( $id )->multiday );
		}
		$this->assertEquals( 2, tribe_get_event( $to_6_am )->multiday );
		$this->assertEquals( 3, tribe_get_event( $to_4_am_two_days_after )->multiday );

		tribe_update_option( 'multiDayCutoff', '01:00' );

		foreach (
			[
				$to_11_pm,
				$to_midnight,
				$to_1_am,
			] as $id
		) {
			$this->assertFalse( false, tribe_get_event( $id )->multiday );
		}
		$this->assertEquals( 2, tribe_get_event( $to_2_am )->multiday );
		$this->assertEquals( 2, tribe_get_event( $to_6_am )->multiday );
		$this->assertEquals( 3, tribe_get_event( $to_4_am_two_days_after )->multiday );
	}


	/**
	 * Test tribe_get_event all_day property
	 */
	public function test_tribe_get_event_all_day_property() {
		$all_day = static::factory()->event->create( [ 'meta_input' => [ '_EventAllDay' => true ] ] );

		$got = tribe_get_event( $all_day )->all_day;

		$this->assertTrue( $got );
	}

	/**
	 * Test tribe_get_event all_day prop with diff. durations and cutoffs
	 */
	public function test_tribe_get_event_all_day_prop_with_diff_durations_and_cutoffs() {
		tribe_update_option( 'multiDayCutoff', '00:00' );

		$all_day_one_day = static::factory()->event->create( [
			'meta_input' => [ '_EventAllDay' => true ]
		] );
		$all_day_3_days  = static::factory()->event->create( [
			'duration'   => 3 * DAY_IN_SECONDS,
			'meta_input' => [ '_EventAllDay' => true ]
		] );

		$all_day_one_day_event = tribe_get_event( $all_day_one_day );
		$all_day_3_days_event  = tribe_get_event( $all_day_3_days );

		$this->assertTrue( $all_day_one_day_event->all_day );
		$this->assertTrue( $all_day_3_days_event->all_day );
		$this->assertFalse( $all_day_one_day_event->multiday );
		$this->assertEquals( 3, $all_day_3_days_event->multiday );

		tribe_update_option( 'multiDayCutoff', '02:00' );

		$all_day_one_day_event = tribe_get_event( $all_day_one_day );
		$all_day_3_days_event  = tribe_get_event( $all_day_3_days );

		$this->assertTrue( $all_day_one_day_event->all_day );
		$this->assertTrue( $all_day_3_days_event->all_day );
		$this->assertFalse( $all_day_one_day_event->multiday );
		$this->assertEquals( 3, $all_day_3_days_event->multiday );
	}

	/**
	 * Test tribe_get_event featured prop
	 */
	public function test_tribe_get_event_featured_prop() {
		$not_featured = static::factory()->event->create();
		$featured     = static::factory()->event->create( [
			'meta_input' => [
				'_tribe_featured' => true,
			],
		] );

		$this->assertFalse( tribe_get_event( $not_featured )->featured );
		$this->assertTrue( tribe_get_event( $featured )->featured );
	}

	/**
	 * Test tribe_get_event organizer lazy fetch
	 */
	public function test_tribe_get_event_organizer_lazy_fetch() {
		$organizer_1       = static::factory()->organizer->create();
		$organizer_2       = static::factory()->organizer->create();
		$wo_organizer      = static::factory()->event->create();
		$w_organizer_1     = static::factory()->event->create( [ 'organizers' => [ $organizer_1 ] ] );
		$w_both_organizers = static::factory()->event->create( [ 'organizers' => [ $organizer_1, $organizer_2 ] ] );

		$fail = static function () {
			$str = '`tribe_get_event` should not call `tribe_get_organizer` unless the `organizer` property is accessed.';
			throw new AssertionFailedError( $str );
		};

		add_filter( 'tribe_get_organizer', $fail );

		$wo_organizer_event      = tribe_get_event( $wo_organizer );
		$w_organizer_1_event     = tribe_get_event( $w_organizer_1 );
		$w_both_organizers_event = tribe_get_event( $w_both_organizers );

		remove_filter( 'tribe_get_organizer', $fail );

		$this->assertEquals( [], $wo_organizer_event->organizers->all() );
		$this->assertEquals(
			[ tribe_get_organizer( $organizer_1 ) ],
			tribe_get_event( $w_organizer_1 )->organizer_names->all()
		);
		$this->assertEquals(
			[ tribe_get_organizer_object( $organizer_1 ) ],
			tribe_get_event( $w_organizer_1 )->organizers->all()
		);
		$this->assertEqualSets( [
			tribe_get_organizer( $organizer_1 ),
			tribe_get_organizer( $organizer_2 )
		], tribe_get_event( $w_both_organizers )->organizer_names->all() );
		$this->assertEqualSets( [
			tribe_get_organizer_object( $organizer_1 ),
			tribe_get_organizer_object( $organizer_2 )
		], tribe_get_event( $w_both_organizers )->organizers->all() );
	}

	/**
	 * Test tribe_get_event starts_this_week property
	 */
	public function test_tribe_get_event_starts_this_week_property() {
		$monday_start_of_week    = 1;
		$wednesday_start_of_week = 3;
		$saturday_start_of_week  = 6;
		$wednesday               = '2019-07-10 09:00:00';
		$friday                  = '2019-07-12';

		update_option( 'start_of_week', $monday_start_of_week );
		tribe( 'cache' )->reset();

		$event = static::factory()->event->create( [
			'when'     => $wednesday,
			'duration' => 3 * DAY_IN_SECONDS,
		] );

		$got = tribe_get_event( $event );

		$this->assertNull( $got->starts_this_week );
		$this->assertNull( $got->ends_this_week );

		$got = tribe_get_event( $event, OBJECT, $friday );

		$this->assertTrue( $got->starts_this_week );
		$this->assertTrue( $got->ends_this_week );

		update_option( 'start_of_week', $wednesday_start_of_week );
		tribe( 'cache' )->reset();

		$got = tribe_get_event( $event, OBJECT, $friday );

		$this->assertTrue( $got->starts_this_week );
		$this->assertTrue( $got->ends_this_week );

		update_option( 'start_of_week', $saturday_start_of_week );
		tribe( 'cache' )->reset();

		$got = tribe_get_event( $event, OBJECT, $friday );

		$this->assertTrue( $got->starts_this_week );
		$this->assertFalse( $got->ends_this_week );
	}

	/**
	 * @test tribe_get_event results are cached until post save
	 */
	public function test_tribe_get_event_results_are_cached_until_post_save() {
		$event_id = static::factory()->event->create();

		$first_fetch = tribe_get_event( $event_id );

		$first_fetch_count = $this->queries()->countQueries();

		// Sanity check.
		$this->assertInstanceOf( \WP_Post::class, $first_fetch );
		$this->assertEquals( $event_id, $first_fetch->ID );

		$second_fetch = tribe_get_event( $event_id );

		$second_fetch_count = $this->queries()->countQueries();

		$this->assertInstanceOf( \WP_Post::class, $second_fetch );
		$this->assertEquals( $event_id, $second_fetch->ID );
		$this->assertEquals( $first_fetch_count, $second_fetch_count );

		// Update the event thus triggering a cache invalidation.
		wp_update_post( [ 'ID' => $event_id, 'post_title' => 'Updated' ] );

		$third_fetch = tribe_get_event( $event_id );

		$third_fetch_count = $this->queries()->countQueries();

		$this->assertInstanceOf( \WP_Post::class, $third_fetch );
		$this->assertEquals( $event_id, $third_fetch->ID );
		$this->assertGreaterThan( $first_fetch_count, $third_fetch_count );
	}

	/**
	 * Test tribe_get_event result is filterable
	 */
	public function test_tribe_get_event_result_is_filterable() {
		$event_id = static::factory()->event->create();

		add_filter( 'tribe_get_event', static function ( \WP_Post $event ) {
			$event->foo = 'bar';

			return $event;
		} );

		$event = tribe_get_event( $event_id );

		$this->assertTrue( isset( $event->foo ) );
		$this->assertEquals( 'bar', $event->foo );
	}

	/**
	 * Test tribe_get_event allows specifying the output format.
	 */
	public function test_tribe_get_event_allows_specifying_the_output_format() {
		$event_id = static::factory()->event->create();

		$event = tribe_get_event( $event_id );

		$queries_count = $this->queries()->countQueries();

		$this->assertEquals( (array) $event, tribe_get_event( $event_id, ARRAY_A ) );
		$this->assertEquals( $queries_count, $this->queries()->countQueries() );
		$this->assertEquals( array_values( (array) $event ), tribe_get_event( $event_id, ARRAY_N ) );
		$this->assertEquals( $queries_count, $this->queries()->countQueries() );
	}

	/**
	 * It should cache on shutdown and only if a lazy property was accessed
	 *
	 * @test
	 */
	public function should_cache_on_shutdown_and_only_if_a_lazy_property_was_accessed() {
		// Required as lazy properties will trigger caching only when using object cache.
		wp_using_ext_object_cache( true );
		$post_id = static::factory()->event->create();

		$cache_key = 'events_' . $post_id . '_raw';
		$cache     = new \Tribe__Cache();

		$event = tribe_get_event( $post_id );

		$cached_before = $cache->get( $cache_key, Cache_Listener::TRIGGER_SAVE_POST );

		$this->assertFalse( $cached_before );

		$this->suspending_filter_do( 'shutdown',
			function () use ( $cache, $cache_key, $event ) {
				$event->organizers->all();
				do_action( 'shutdown' );
				$cached = $cache->get( $cache_key, Cache_Listener::TRIGGER_SAVE_POST );
				$this->assertInternalType( 'array', $cached );
				$this->assertNotEmpty( array_intersect_key( get_object_vars( $event ), $cached ) );
				$this->assertArrayHasKey( 'dates', $cached, 'Dates should be cached.' );
				$this->assertInternalType( 'array', $cached['dates'], 'An array of dates should be cached.' );
				$this->assertContainsOnlyInstancesOf(
					\DateTimeImmutable::class,
					$cached['dates'],
					'Dates should have been converted to DateTimeImmutable type.'
				);
				$this->assertCount(
					0,
					array_filter( $cached['dates'], static function ( $date ) {
						return $date instanceof Date_I18n_Immutable;
					} ),
					'Dates should have been converted from the Date_I18n_Immutable type.'
				);
			}
		);
	}
}
