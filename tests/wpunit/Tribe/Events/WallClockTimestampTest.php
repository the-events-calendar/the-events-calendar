<?php

namespace Tribe\Events;

use Tribe__Events__Timezones as Timezones;

/**
 * Regression tests for the wall-clock timestamp contract of
 * `Tribe__Events__Timezones::get_event_timestamp()`.
 *
 * The timestamps produced by `event_start_timestamp()` and `event_end_timestamp()`
 * are "wall-clock" timestamps: they must equal the event local datetime string
 * parsed as if it were UTC, regardless of the PHP default timezone in effect.
 * Any dependence on `date_default_timezone_set()` is a regression.
 */

// phpcs:disable WordPress.DateTime.RestrictedFunctions.timezone_change_date_default_timezone_set -- Simulating third-party code changing PHP's default timezone is the behavior under test.
class WallClockTimestampTest extends \Codeception\TestCase\WPTestCase {

	/**
	 * @var string The PHP default timezone before the test ran.
	 */
	protected $php_timezone_backup;

	public function setUp() {
		parent::setUp();

		$this->php_timezone_backup = date_default_timezone_get();

		tribe_unset_var( \Tribe__Settings_Manager::OPTION_CACHE_VAR_NAME );
		tribe_unset_var( 'Tribe__Events__Timezones::get_event_timestamp' );

		tribe( 'cache' )->reset();
	}

	public function tearDown() {
		date_default_timezone_set( $this->php_timezone_backup );

		update_option( 'timezone_string', '' );
		update_option( 'gmt_offset', 0 );

		// Flush the request-lifetime caches so options and timestamps mutated by
		// this test are not served to unrelated tests after the DB rollback.
		tribe_unset_var( \Tribe__Settings_Manager::OPTION_CACHE_VAR_NAME );
		tribe_unset_var( 'Tribe__Events__Timezones::get_event_timestamp' );

		tribe( 'cache' )->reset();

		parent::tearDown();
	}

	/**
	 * The PHP default timezones covering all failure-relevant characteristics:
	 * zero offset (control), negative offsets with northern DST, large positive
	 * without DST, half-hour and 45-minute fractional offsets, southern DST with
	 * fractional offset, and the extreme +14 offset past the date line.
	 *
	 * @return array<string>
	 */
	protected function php_timezones() {
		return [
			'UTC',                 // Control: must be byte-identical to the pre-fix behavior.
			'America/Chicago',     // -6/-5, northern DST; the reported customer scenario.
			'America/Los_Angeles', // -8/-7, large negative with northern DST.
			'Asia/Tokyo',          // +9, no DST.
			'Asia/Kolkata',        // +5:30, half-hour fractional, no DST.
			'Asia/Kathmandu',      // +5:45, 45-minute fractional, no DST.
			'Pacific/Chatham',     // +12:45/+13:45, fractional with southern DST.
			'Pacific/Kiritimati',  // +14, extreme positive, crosses the date line.
		];
	}

	/**
	 * Fixed UTC datetimes, each exercising a distinct boundary.
	 *
	 * @return array<string,string>
	 */
	protected function boundary_dates() {
		return [
			'jul15-midday'        => '2024-07-15 14:00:00', // Northern summer: DST on for Chicago/LA, off for Chatham.
			'jan15-midday'        => '2024-01-15 14:00:00', // Southern summer: DST states inverted.
			'dec31-year-boundary' => '2024-12-31 23:30:00', // Offsets cross the year boundary in both directions.
			'jun15-day-boundary'  => '2024-06-15 23:30:00', // Offsets cross the day boundary, isolated from the year.
		];
	}

	/**
	 * Computes the expected wall-clock timestamp independently of the code under test.
	 *
	 * @param string           $utc_datetime The event UTC datetime string.
	 * @param string|int|float $target_tz    A timezone name, or a numeric UTC offset in hours.
	 *
	 * @return int The localized wall-clock string parsed as UTC.
	 */
	protected function wall_clock_reference( $utc_datetime, $target_tz ) {
		$utc  = new \DateTimeZone( 'UTC' );
		$date = new \DateTimeImmutable( $utc_datetime, $utc );

		if ( is_numeric( $target_tz ) ) {
			return $date->getTimestamp() + (int) round( $target_tz * 3600 );
		}

		$local = $date->setTimezone( new \DateTimeZone( $target_tz ) )->format( 'Y-m-d H:i:s' );

		return ( new \DateTimeImmutable( $local, $utc ) )->getTimestamp();
	}

	/**
	 * Configures the WordPress site timezone from a matrix entry.
	 *
	 * @param array $site_tz Either [ 'timezone_string' => name ] or [ 'gmt_offset' => hours ].
	 */
	protected function set_site_timezone( array $site_tz ) {
		if ( isset( $site_tz['timezone_string'] ) ) {
			update_option( 'timezone_string', $site_tz['timezone_string'] );
			update_option( 'gmt_offset', 0 );
		} else {
			update_option( 'timezone_string', '' );
			update_option( 'gmt_offset', $site_tz['gmt_offset'] );
		}
	}

	/**
	 * Resolves the expected target timezone of a site timezone matrix entry.
	 *
	 * @param array $site_tz Either [ 'timezone_string' => name ] or [ 'gmt_offset' => hours ].
	 *
	 * @return string|int|float
	 */
	protected function site_tz_target( array $site_tz ) {
		return $site_tz['timezone_string'] ?? $site_tz['gmt_offset'];
	}

	/**
	 * Creates an event through the ORM (the production creation path) and asserts
	 * the stored UTC meta is internally consistent, failing fast on invalid setup.
	 *
	 * The event is intentionally created while the PHP default timezone is still
	 * UTC: this mirrors the real-world failure mode, where events are created
	 * normally and only rendered while a non-UTC default timezone is in effect.
	 *
	 * @param string $utc_datetime The event start in UTC.
	 * @param string $event_tz     The event timezone name.
	 *
	 * @return int The event post ID.
	 */
	protected function create_event_or_fail( $utc_datetime, $event_tz ) {
		$local_start = ( new \DateTimeImmutable( $utc_datetime, new \DateTimeZone( 'UTC' ) ) )
			->setTimezone( new \DateTimeZone( $event_tz ) )
			->format( 'Y-m-d H:i:s' );

		$event = tribe_events()->set_args(
			[
				'title'      => "Wall clock test event {$utc_datetime} {$event_tz}",
				'status'     => 'publish',
				'start_date' => $local_start,
				'duration'   => 7200,
				'timezone'   => $event_tz,
			]
		)->create();

		if ( ! $event instanceof \WP_Post ) {
			$this->fail( "Could not create event for {$utc_datetime} ({$event_tz})." );
		}

		if ( get_post_meta( $event->ID, '_EventStartDateUTC', true ) !== $utc_datetime ) {
			$this->fail( "ORM produced inconsistent _EventStartDateUTC meta for {$utc_datetime} ({$event_tz})." );
		}

		return $event->ID;
	}

	/**
	 * Tier 1: PHP timezone x event timezone x boundary dates, in event timezone mode.
	 *
	 * @return \Generator
	 */
	public function event_mode_matrix() {
		foreach ( $this->php_timezones() as $php_tz ) {
			foreach ( [ 'UTC', 'America/Chicago' ] as $event_tz ) {
				foreach ( $this->boundary_dates() as $date_key => $utc_datetime ) {
					yield "php={$php_tz}|event={$event_tz}|date={$date_key}" => [ $php_tz, $event_tz, $utc_datetime ];
				}
			}
		}
	}

	/**
	 * Event start/end timestamps must be wall-clock values in the event timezone,
	 * regardless of the PHP default timezone.
	 *
	 * @dataProvider event_mode_matrix
	 */
	public function test_event_timestamps_are_wall_clock_in_event_mode( $php_tz, $event_tz, $utc_datetime ) {
		update_option( 'timezone_string', 'America/Chicago' );
		tribe_update_option( 'tribe_events_timezone_mode', 'event' );
		tribe( 'cache' )->reset();

		$event_id = $this->create_event_or_fail( $utc_datetime, $event_tz );

		$expected_start = $this->wall_clock_reference( $utc_datetime, $event_tz );
		$utc_end        = gmdate( 'Y-m-d H:i:s', strtotime( $utc_datetime . ' UTC' ) + 7200 );
		$expected_end   = $this->wall_clock_reference( $utc_end, $event_tz );

		date_default_timezone_set( $php_tz );

		$this->assertSame( $expected_start, Timezones::event_start_timestamp( $event_id ) );
		$this->assertSame( $expected_end, Timezones::event_end_timestamp( $event_id ) );
	}

	/**
	 * Tier 2: PHP timezone x site timezone configurations, in site timezone mode.
	 *
	 * @return \Generator
	 */
	public function site_mode_matrix() {
		$site_timezones = [
			'named-chicago' => [ 'timezone_string' => 'America/Chicago' ],
			'named-utc'     => [ 'timezone_string' => 'UTC' ],
			'named-kolkata' => [ 'timezone_string' => 'Asia/Kolkata' ],
			'manual+0'      => [ 'gmt_offset' => 0 ],
			'manual-5'      => [ 'gmt_offset' => -5 ],
			'manual+5.5'    => [ 'gmt_offset' => 5.5 ],
		];

		foreach ( $this->php_timezones() as $php_tz ) {
			foreach ( $site_timezones as $site_key => $site_tz ) {
				yield "php={$php_tz}|site={$site_key}" => [ $php_tz, $site_tz ];
			}
		}
	}

	/**
	 * In site timezone mode the timestamps must be wall-clock values in the site
	 * timezone — for named timezone strings and manual UTC offsets alike —
	 * regardless of the PHP default timezone.
	 *
	 * @dataProvider site_mode_matrix
	 */
	public function test_event_timestamps_are_wall_clock_in_site_mode( $php_tz, array $site_tz ) {
		$utc_datetime = '2024-07-15 14:00:00';

		$this->set_site_timezone( $site_tz );
		tribe_update_option( 'tribe_events_timezone_mode', 'site' );
		tribe( 'cache' )->reset();

		$event_id = $this->create_event_or_fail( $utc_datetime, 'America/Chicago' );

		$expected = $this->wall_clock_reference( $utc_datetime, $this->site_tz_target( $site_tz ) );

		date_default_timezone_set( $php_tz );

		$this->assertSame( $expected, Timezones::event_start_timestamp( $event_id ) );
	}

	/**
	 * Tier 3: hand-picked boundary-stressing combinations, including negative
	 * tests where the PHP default timezone matches the display timezone.
	 *
	 * @return \Generator
	 */
	public function targeted_boundary_matrix() {
		$cases = [
			// PHP tz, site tz config, event tz, UTC datetime, mode.
			'kiritimati-year-boundary-site'    => [ 'Pacific/Kiritimati', [ 'timezone_string' => 'America/Chicago' ], 'UTC', '2024-12-31 23:30:00', 'site' ],
			'la-year-boundary-utc-site'        => [ 'America/Los_Angeles', [ 'timezone_string' => 'UTC' ], 'UTC', '2024-12-31 23:30:00', 'site' ],
			'chatham-vs-manual-half-hour'      => [ 'Pacific/Chatham', [ 'gmt_offset' => 5.5 ], 'UTC', '2024-01-15 14:00:00', 'site' ],
			'kathmandu-vs-kolkata'             => [ 'Asia/Kathmandu', [ 'timezone_string' => 'Asia/Kolkata' ], 'UTC', '2024-07-15 14:00:00', 'site' ],
			'chatham-event-manual-13-site'     => [ 'Pacific/Chatham', [ 'gmt_offset' => 13 ], 'Pacific/Chatham', '2024-06-15 23:30:00', 'site' ],
			'kiritimati-chatham-year-event'    => [ 'Pacific/Kiritimati', [ 'timezone_string' => 'America/Chicago' ], 'Pacific/Chatham', '2024-12-31 23:30:00', 'event' ],
			'negative-php-matches-site'        => [ 'America/Chicago', [ 'gmt_offset' => -5 ], 'UTC', '2024-06-15 23:30:00', 'site' ],
			'tokyo-newyork-year-site'          => [ 'Asia/Tokyo', [ 'timezone_string' => 'America/Chicago' ], 'America/New_York', '2024-12-31 23:30:00', 'site' ],
			'kiritimati-kiritimati-year-event' => [ 'Pacific/Kiritimati', [ 'timezone_string' => 'America/Chicago' ], 'Pacific/Kiritimati', '2024-12-31 23:30:00', 'event' ],
			'chatham-kiritimati-year-event'    => [ 'Pacific/Chatham', [ 'timezone_string' => 'America/Chicago' ], 'Pacific/Kiritimati', '2024-12-31 23:30:00', 'event' ],
			'kathmandu-chatham-year-event'     => [ 'Asia/Kathmandu', [ 'timezone_string' => 'America/Chicago' ], 'Pacific/Chatham', '2024-12-31 23:30:00', 'event' ],
			'la-tokyo-year-event'              => [ 'America/Los_Angeles', [ 'timezone_string' => 'America/Chicago' ], 'Asia/Tokyo', '2024-12-31 23:30:00', 'event' ],
			'la-tokyo-day-event'               => [ 'America/Los_Angeles', [ 'timezone_string' => 'America/Chicago' ], 'Asia/Tokyo', '2024-06-15 23:30:00', 'event' ],
			'tokyo-tokyo-day-event'            => [ 'Asia/Tokyo', [ 'timezone_string' => 'America/Chicago' ], 'Asia/Tokyo', '2024-06-15 23:30:00', 'event' ],
			'kolkata-tokyo-year-event'         => [ 'Asia/Kolkata', [ 'timezone_string' => 'America/Chicago' ], 'Asia/Tokyo', '2024-12-31 23:30:00', 'event' ],
			'kolkata-tokyo-day-event'          => [ 'Asia/Kolkata', [ 'timezone_string' => 'America/Chicago' ], 'Asia/Tokyo', '2024-06-15 23:30:00', 'event' ],
			'chicago-spring-forward-event'     => [ 'America/Chicago', [ 'timezone_string' => 'America/Chicago' ], 'America/Chicago', '2024-03-10 08:00:00', 'event' ],
			'chicago-fall-back-event'          => [ 'America/Chicago', [ 'timezone_string' => 'America/Chicago' ], 'America/Chicago', '2024-11-03 06:30:00', 'event' ],
			'tokyo-spring-forward-event'       => [ 'Asia/Tokyo', [ 'timezone_string' => 'America/Chicago' ], 'America/Chicago', '2024-03-10 08:00:00', 'event' ],
			'utc-control-year-event'           => [ 'UTC', [ 'timezone_string' => 'America/Chicago' ], 'Pacific/Kiritimati', '2024-12-31 23:30:00', 'event' ],
			'utc-control-manual-site'          => [ 'UTC', [ 'gmt_offset' => -5 ], 'UTC', '2024-12-31 23:30:00', 'site' ],
			'negative-php-matches-event'       => [ 'America/Chicago', [ 'timezone_string' => 'America/Chicago' ], 'America/Chicago', '2024-06-15 23:30:00', 'event' ],
			'kiritimati-manual-negative-site'  => [ 'Pacific/Kiritimati', [ 'gmt_offset' => -5 ], 'America/Chicago', '2024-12-31 23:30:00', 'site' ],
		];

		foreach ( $cases as $key => $case ) {
			yield $key => $case;
		}
	}

	/**
	 * @dataProvider targeted_boundary_matrix
	 */
	public function test_event_timestamps_on_targeted_boundaries( $php_tz, array $site_tz, $event_tz, $utc_datetime, $mode ) {
		$this->set_site_timezone( $site_tz );
		tribe_update_option( 'tribe_events_timezone_mode', $mode );
		tribe( 'cache' )->reset();

		$event_id = $this->create_event_or_fail( $utc_datetime, $event_tz );

		$target   = 'event' === $mode ? $event_tz : $this->site_tz_target( $site_tz );
		$expected = $this->wall_clock_reference( $utc_datetime, $target );

		date_default_timezone_set( $php_tz );

		$this->assertSame( $expected, Timezones::event_start_timestamp( $event_id, $mode ) );
	}

	/**
	 * A `null` timezone argument and the explicitly resolved mode must share the
	 * same cache entry, and a mode change mid-request must not serve a stale value.
	 */
	public function test_get_event_timestamp_cache_key_resolves_timezone_mode() {
		update_option( 'timezone_string', 'America/Chicago' );
		tribe_update_option( 'tribe_events_timezone_mode', 'event' );
		tribe( 'cache' )->reset();

		$utc_datetime = '2024-07-15 14:00:00';
		$event_id     = $this->create_event_or_fail( $utc_datetime, 'Asia/Tokyo' );

		$expected_event = $this->wall_clock_reference( $utc_datetime, 'Asia/Tokyo' );
		$expected_site  = $this->wall_clock_reference( $utc_datetime, 'America/Chicago' );

		// Null argument resolves to the current ('event') mode.
		$this->assertSame( $expected_event, Timezones::event_start_timestamp( $event_id ) );
		// The explicit mode must return the same value from the same cache entry.
		$this->assertSame( $expected_event, Timezones::event_start_timestamp( $event_id, Timezones::EVENT_TIMEZONE ) );

		// Changing the mode mid-request must not serve the value cached for the previous mode.
		tribe_update_option( 'tribe_events_timezone_mode', 'site' );

		$this->assertSame( $expected_site, Timezones::event_start_timestamp( $event_id ) );
	}
}
