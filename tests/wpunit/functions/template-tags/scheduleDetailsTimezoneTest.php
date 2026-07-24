<?php

namespace TEC\Test\functions\template_tags;

use Codeception\TestCase\WPTestCase;
use Spatie\Snapshots\MatchesSnapshots;
use tad\WP\Snapshots\WPHtmlOutputDriver;

/**
 * Regression tests ensuring `tribe_events_event_schedule_details()` renders the
 * same schedule regardless of the PHP default timezone in effect.
 */

// phpcs:disable WordPress.DateTime.RestrictedFunctions.timezone_change_date_default_timezone_set -- Simulating third-party code changing PHP's default timezone is the behavior under test.
class scheduleDetailsTimezoneTest extends WPTestCase {

	use MatchesSnapshots;

	/**
	 * @var string The PHP default timezone before the test ran.
	 */
	protected $php_timezone_backup;

	/**
	 * @var WPHtmlOutputDriver
	 */
	protected $driver;

	public function setUp() {
		parent::setUp();

		$this->php_timezone_backup = date_default_timezone_get();

		update_option( 'timezone_string', 'America/Chicago' );
		tribe_update_option( 'tribe_events_timezone_mode', 'event' );

		$this->driver = new WPHtmlOutputDriver( home_url(), 'http://tribe.dev' );

		$this->flush_date_caches();
	}

	public function tearDown() {
		date_default_timezone_set( $this->php_timezone_backup );

		update_option( 'timezone_string', '' );
		update_option( 'gmt_offset', 0 );
		tribe_update_option( 'multiDayCutoff', '00:00' );

		// Flush the request-lifetime caches so options and rendered details mutated
		// by this test are not served to unrelated tests after the DB rollback.
		$this->flush_date_caches();

		parent::tearDown();
	}

	/**
	 * Flushes every request-lifetime cache sitting between an event and its
	 * rendered schedule details, so back-to-back renders re-run the date math.
	 */
	protected function flush_date_caches() {
		tribe_set_var( 'tribe_events_event_schedule_details', [] );
		tribe_set_var( 'tribe_get_start_date', [] );
		tribe_set_var( 'tribe_get_end_date', [] );
		tribe_unset_var( 'Tribe__Events__Timezones::get_event_timestamp' );
		tribe_unset_var( \Tribe__Settings_Manager::OPTION_CACHE_VAR_NAME );
		tribe( 'cache' )->reset();
	}

	/**
	 * Renders the plain-text schedule details of an event with cold caches.
	 *
	 * @param int $event_id The event post ID.
	 *
	 * @return string
	 */
	protected function render_schedule_details( $event_id ) {
		$this->flush_date_caches();

		return wp_strip_all_tags( tribe_events_event_schedule_details( $event_id ) );
	}

	/**
	 * The same failure-relevant PHP default timezones exercised by
	 * WallClockTimestampTest.
	 *
	 * @return \Generator
	 */
	public function php_timezones() {
		$timezones = [
			'UTC',
			'America/Chicago',
			'America/Los_Angeles',
			'Asia/Tokyo',
			'Asia/Kolkata',
			'Asia/Kathmandu',
			'Pacific/Chatham',
			'Pacific/Kiritimati',
		];

		foreach ( $timezones as $timezone ) {
			yield "php={$timezone}" => [ $timezone ];
		}
	}

	/**
	 * Creates the single-day event backing the reported bug: July 22, 2050,
	 * 11:30 am - 1:00 pm America/Chicago.
	 *
	 * @return int The event post ID.
	 */
	protected function create_single_day_event() {
		$event = tribe_events()->set_args(
			[
				'title'      => 'Timezone regression single day event',
				'status'     => 'publish',
				'start_date' => '2050-07-22 11:30:00',
				'end_date'   => '2050-07-22 13:00:00',
				'timezone'   => 'America/Chicago',
			]
		)->create();

		if ( ! $event instanceof \WP_Post ) {
			$this->fail( 'Could not create the single-day event.' );
		}

		return $event->ID;
	}

	/**
	 * Creates a multiday all-day event with a 06:00 multiday cutoff. The ORM
	 * normalizes all-day events to cutoff-aligned storage (end date
	 * `2050-07-25 05:59:59`), which sits below the beginning-of-day boundary, so
	 * the displayed end date must roll back to July 24.
	 *
	 * @return int The event post ID.
	 */
	protected function create_multiday_all_day_event() {
		tribe_update_option( 'multiDayCutoff', '06:00' );

		$event = tribe_events()->set_args(
			[
				'title'      => 'Timezone regression multiday all-day event',
				'status'     => 'publish',
				'start_date' => '2050-07-22 00:00:00',
				'end_date'   => '2050-07-24 05:00:00',
				'all_day'    => true,
				'timezone'   => 'America/Chicago',
			]
		)->create();

		if ( ! $event instanceof \WP_Post ) {
			$this->fail( 'Could not create the multiday all-day event.' );
		}

		return $event->ID;
	}

	/**
	 * A single-day schedule must render identically under any PHP default
	 * timezone, and must show the event wall-clock time — not a UTC-shifted one.
	 *
	 * @dataProvider php_timezones
	 */
	public function test_single_day_schedule_details_ignore_php_default_timezone( $php_tz ) {
		$event_id = $this->create_single_day_event();

		date_default_timezone_set( 'UTC' );
		$baseline = $this->render_schedule_details( $event_id );

		if ( false === strpos( $baseline, '11:30 am' ) || false === strpos( $baseline, '1:00 pm' ) ) {
			$this->fail( "Baseline render does not show the event wall-clock time: {$baseline}" );
		}

		date_default_timezone_set( $php_tz );

		$this->assertSame( $baseline, $this->render_schedule_details( $event_id ) );
	}

	/**
	 * A multiday all-day event stores its end at one second before the multiday
	 * cutoff (here `2050-07-25 05:59:59` with a 06:00 cutoff), so the displayed
	 * end date must roll back to July 24 under any PHP default timezone: a
	 * positive UTC offset used to push the end date off by one day.
	 *
	 * @dataProvider php_timezones
	 */
	public function test_multiday_all_day_cutoff_end_date_ignores_php_default_timezone( $php_tz ) {
		$event_id = $this->create_multiday_all_day_event();

		date_default_timezone_set( 'UTC' );
		$baseline = $this->render_schedule_details( $event_id );

		if ( false === strpos( $baseline, 'July 24' ) || false !== strpos( $baseline, 'July 25' ) ) {
			$this->fail( "Baseline render does not roll the end date back to July 24: {$baseline}" );
		}

		date_default_timezone_set( $php_tz );

		$rendered = $this->render_schedule_details( $event_id );

		$this->assertSame( $baseline, $rendered );
		$this->assertContains( 'July 24', $rendered );
		$this->assertNotContains( 'July 25', $rendered );
	}

	/**
	 * Snapshot of the single-day schedule details HTML rendered under a UTC and
	 * under a non-UTC PHP default timezone: both must match the same snapshot.
	 */
	public function test_single_day_schedule_details_html_snapshot() {
		$event_id = $this->create_single_day_event();

		date_default_timezone_set( 'UTC' );
		$this->flush_date_caches();
		$utc_html = tribe_events_event_schedule_details( $event_id );

		date_default_timezone_set( 'America/Chicago' );
		$this->flush_date_caches();
		$chicago_html = tribe_events_event_schedule_details( $event_id );

		$this->assertSame( $utc_html, $chicago_html );
		$this->assertMatchesSnapshot( $utc_html, $this->driver );
	}

	/**
	 * Snapshot of the multiday all-day schedule details HTML rendered under a UTC
	 * and under a non-UTC PHP default timezone: both must match the same snapshot.
	 */
	public function test_multiday_all_day_schedule_details_html_snapshot() {
		$event_id = $this->create_multiday_all_day_event();

		date_default_timezone_set( 'UTC' );
		$this->flush_date_caches();
		$utc_html = tribe_events_event_schedule_details( $event_id );

		date_default_timezone_set( 'Asia/Tokyo' );
		$this->flush_date_caches();
		$tokyo_html = tribe_events_event_schedule_details( $event_id );

		$this->assertSame( $utc_html, $tokyo_html );
		$this->assertMatchesSnapshot( $utc_html, $this->driver );
	}
}
