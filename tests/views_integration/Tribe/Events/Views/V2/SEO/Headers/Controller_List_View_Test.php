<?php

namespace Tribe\Events\Views\V2\SEO\Headers;

use TEC\Events\SEO\Headers\Controller;
use TEC\Events\SEO\Settings;
use Tribe\Tests\Traits\With_Uopz;

/**
 * Tests for the list view date-range 404 behavior in the SEO Headers Controller.
 *
 * List view stores its date in the raw GET parameter ?tribe-bar-date rather than
 * in a WP query var (eventDate), so it requires its own check_list_view() path
 * inside filter_headers(). These tests mirror the patterns established by
 * Controller_Day_View_Test and Controller_Month_View_Test.
 */
class Controller_List_View_Test extends \Codeception\TestCase\WPTestCase {

	use With_Uopz;

	public function setUp(): void {
		parent::setUp();
		unset( $_REQUEST['tribe-bar-date'] );
	}

	public function tearDown(): void {
		unset( $_REQUEST['tribe-bar-date'] );
		parent::tearDown();
	}

	/**
	 * Create three events spanning a date range for use in range-boundary tests.
	 *
	 * @param string $range_start Earliest event date (Y-m-d).
	 * @param string $range_end   Latest event date (Y-m-d).
	 * @param string $test_day    A middle date within the range (Y-m-d).
	 */
	private function create_test_events( string $range_start, string $range_end, string $test_day ): void {
		$timezone_string = get_option( 'timezone_string' ) ?: 'UTC';

		foreach ( [
			[ $range_start, '09:00', 'Test Event Start' ],
			[ $test_day,    '12:00', 'Test Event Middle' ],
			[ $range_end,   '12:30', 'Test Event End' ],
		] as [ $date, $time, $title ] ) {
			tribe_events()->set_args( [
				'start_date' => $date . ' ' . $time,
				'timezone'   => $timezone_string,
				'duration'   => 3 * HOUR_IN_SECONDS,
				'title'      => $title,
				'status'     => 'publish',
			] )->create();
		}
	}

	/**
	 * When list view is disabled in TEC settings the URL is illegitimate regardless
	 * of what date is requested.
	 *
	 * @test
	 */
	public function test_list_view_disabled_always_shows_404(): void {
		add_filter( 'tribe_get_option_tribeEnableViews', static fn() => [ 'month' ], 10, 3 );

		$this->create_test_events( '2023-06-20', '2023-12-31', '2023-06-21' );
		tribe_update_option( 'earliest_date', false );
		tribe_update_option( 'latest_date', false );

		$_REQUEST['tribe-bar-date'] = '2023-06-20';

		global $wp_query;
		$wp_query->query = [
			'post_type'    => 'tribe_events',
			'eventDisplay' => 'list',
		];

		tribe_register_provider( Controller::class );
		tribe( Controller::class )->filter_headers();

		$this->assertTrue( $wp_query->is_404, 'When the list view is disabled, a 404 should be set.' );
	}

	/**
	 * @test
	 */
	public function test_list_view_before_range_shows_404(): void {
		$this->create_test_events( '2023-06-20', '2023-12-31', '2023-09-15' );
		tribe_update_option( 'earliest_date', false );
		tribe_update_option( 'latest_date', false );

		$_REQUEST['tribe-bar-date'] = '2023-05-15';

		global $wp_query;
		$wp_query->query = [
			'post_type'    => 'tribe_events',
			'eventDisplay' => 'list',
		];

		tribe_register_provider( Controller::class );
		tribe( Controller::class )->filter_headers();

		$this->assertTrue( $wp_query->is_404, 'A tribe-bar-date before the earliest event should trigger a 404.' );
	}

	/**
	 * @test
	 */
	public function test_list_view_after_range_shows_404(): void {
		$this->create_test_events( '2023-01-01', '2023-06-10', '2023-06-05' );
		tribe_update_option( 'earliest_date', false );
		tribe_update_option( 'latest_date', false );

		$_REQUEST['tribe-bar-date'] = '2024-01-15';

		global $wp_query;
		$wp_query->query = [
			'post_type'    => 'tribe_events',
			'eventDisplay' => 'list',
		];

		tribe_register_provider( Controller::class );
		tribe( Controller::class )->filter_headers();

		$this->assertTrue( $wp_query->is_404, 'A tribe-bar-date after the latest event should trigger a 404.' );
	}

	/**
	 * @test
	 */
	public function test_list_view_with_valid_range_no_404(): void {
		$this->create_test_events( '2023-01-01', '2023-12-31', '2023-06-15' );
		tribe_update_option( 'earliest_date', false );
		tribe_update_option( 'latest_date', false );

		$_REQUEST['tribe-bar-date'] = '2023-06-16';

		global $wp_query;
		$wp_query->query = [
			'post_type'    => 'tribe_events',
			'eventDisplay' => 'list',
		];

		tribe_register_provider( Controller::class );
		tribe( Controller::class )->filter_headers();

		$this->assertFalse( $wp_query->is_404, 'A tribe-bar-date within the event range should not trigger a 404.' );
	}

	/**
	 * Fresh install with no events: the current month must not 404 so that the
	 * live list view remains accessible before any events are created.
	 *
	 * @test
	 */
	public function test_no_events_within_current_month_shows_no_404(): void {
		tribe_update_option( 'earliest_date', false );
		tribe_update_option( 'latest_date', false );

		$date = new \DateTime( '2023-06-11 22:00:00', new \DateTimeZone( 'America/New_York' ) );
		$now  = $date->getTimestamp();
		$this->set_class_fn_return( Controller::class, 'get_current_month', static fn() => date( 'Y-m', $now ), true );

		$_REQUEST['tribe-bar-date'] = '2023-06-16';

		global $wp_query;
		$wp_query->query = [
			'post_type'    => 'tribe_events',
			'eventDisplay' => 'list',
		];

		tribe_register_provider( Controller::class );
		tribe( Controller::class )->filter_headers();

		$this->assertFalse( $wp_query->is_404, 'When there are no events and the date is in the current month, no 404 should be set.' );
	}

	/**
	 * Fresh install with no events: a date outside the current month should 404
	 * because there is no known event range to validate against.
	 *
	 * @test
	 */
	public function test_no_event_and_outside_current_month_shows_404(): void {
		tribe_update_option( 'earliest_date', false );
		tribe_update_option( 'latest_date', false );

		$date = new \DateTime( '2023-06-11 22:00:00', new \DateTimeZone( 'America/New_York' ) );
		$now  = $date->getTimestamp();
		$this->set_class_fn_return( Controller::class, 'get_current_month', static fn() => date( 'Y-m', $now ), true );

		$_REQUEST['tribe-bar-date'] = '2023-05-16';

		global $wp_query;
		$wp_query->query = [
			'post_type'    => 'tribe_events',
			'eventDisplay' => 'list',
		];

		tribe_register_provider( Controller::class );
		tribe( Controller::class )->filter_headers();

		$this->assertTrue( $wp_query->is_404, 'When there are no events and the date is outside the current month, a 404 should be set.' );
	}

	/**
	 * A malformed tribe-bar-date value cannot be parsed to a timestamp; the
	 * check should bail gracefully and leave the page accessible.
	 *
	 * @test
	 */
	public function test_malformed_date_no_404(): void {
		$this->create_test_events( '2023-01-01', '2023-12-31', '2023-06-15' );
		tribe_update_option( 'earliest_date', false );
		tribe_update_option( 'latest_date', false );

		$_REQUEST['tribe-bar-date'] = 'not-a-date';

		global $wp_query;
		$wp_query->query = [
			'post_type'    => 'tribe_events',
			'eventDisplay' => 'list',
		];

		tribe_register_provider( Controller::class );
		tribe( Controller::class )->filter_headers();

		$this->assertFalse( $wp_query->is_404, 'A malformed tribe-bar-date value should not trigger a 404.' );
	}

	/**
	 * When tribe-bar-date is absent the list view base URL should always return
	 * a 200; there is no date to validate.
	 *
	 * @test
	 */
	public function test_no_tribe_bar_date_param_no_404(): void {
		$this->create_test_events( '2023-01-01', '2023-12-31', '2023-06-15' );
		tribe_update_option( 'earliest_date', false );
		tribe_update_option( 'latest_date', false );

		global $wp_query;
		$wp_query->query = [
			'post_type'    => 'tribe_events',
			'eventDisplay' => 'list',
		];

		tribe_register_provider( Controller::class );
		tribe( Controller::class )->filter_headers();

		$this->assertFalse( $wp_query->is_404, 'When tribe-bar-date is absent, the base list view URL should not trigger a 404.' );
	}

	/**
	 * When the site owner switches the out-of-range behaviour to 'soft_noindex',
	 * a pre-range date must NOT return a 404; instead, noindex should be added
	 * to the wp_robots filter so search engines ignore the page while visitors
	 * still see content.
	 *
	 * @test
	 */
	public function test_before_range_soft_noindex_no_404_but_noindex_added(): void {
		add_filter(
			'tribe_get_option_' . Settings::OPT_OUT_OF_RANGE_BEHAVIOR,
			static fn() => 'soft_noindex'
		);

		$this->create_test_events( '2023-06-20', '2023-12-31', '2023-09-15' );
		tribe_update_option( 'earliest_date', false );
		tribe_update_option( 'latest_date', false );

		$_REQUEST['tribe-bar-date'] = '2023-05-15';

		global $wp_query;
		$wp_query->query = [
			'post_type'    => 'tribe_events',
			'eventDisplay' => 'list',
		];

		tribe_register_provider( Controller::class );
		tribe( Controller::class )->filter_headers();

		$this->assertFalse( $wp_query->is_404, 'In soft_noindex mode, an out-of-range date must not return a 404.' );

		$robots = apply_filters( 'wp_robots', [] );
		$this->assertArrayHasKey( 'noindex', $robots, 'In soft_noindex mode, noindex should be added via wp_robots.' );
		$this->assertTrue( $robots['noindex'], 'The noindex directive should be true.' );

		remove_all_filters( 'tribe_get_option_' . Settings::OPT_OUT_OF_RANGE_BEHAVIOR );
		remove_all_filters( 'wp_robots' );
	}

	/**
	 * @test
	 */
	public function test_after_range_soft_noindex_no_404_but_noindex_added(): void {
		add_filter(
			'tribe_get_option_' . Settings::OPT_OUT_OF_RANGE_BEHAVIOR,
			static fn() => 'soft_noindex'
		);

		$this->create_test_events( '2023-01-01', '2023-06-10', '2023-06-05' );
		tribe_update_option( 'earliest_date', false );
		tribe_update_option( 'latest_date', false );

		$_REQUEST['tribe-bar-date'] = '2024-01-15';

		global $wp_query;
		$wp_query->query = [
			'post_type'    => 'tribe_events',
			'eventDisplay' => 'list',
		];

		tribe_register_provider( Controller::class );
		tribe( Controller::class )->filter_headers();

		$this->assertFalse( $wp_query->is_404, 'In soft_noindex mode, a post-range date must not return a 404.' );

		$robots = apply_filters( 'wp_robots', [] );
		$this->assertArrayHasKey( 'noindex', $robots, 'In soft_noindex mode, noindex should be added via wp_robots.' );
		$this->assertTrue( $robots['noindex'] );

		remove_all_filters( 'tribe_get_option_' . Settings::OPT_OUT_OF_RANGE_BEHAVIOR );
		remove_all_filters( 'wp_robots' );
	}

	/**
	 * When OPT_DISABLED_VIEW_404 is turned off, accessing a disabled view slug
	 * should NOT trigger a 404 — the guard is deliberately suppressed.
	 *
	 * @test
	 */
	public function test_disabled_view_404_guard_can_be_turned_off(): void {
		add_filter( 'tribe_get_option_tribeEnableViews', static fn() => [ 'list', 'month' ], 10, 3 );
		add_filter(
			'tribe_get_option_' . Settings::OPT_DISABLED_VIEW_404,
			static fn() => false
		);

		global $wp_query;
		$wp_query->query = [
			'post_type'    => 'tribe_events',
			'eventDisplay' => 'week',
		];

		tribe_register_provider( Controller::class );
		tribe( Controller::class )->filter_headers();

		$this->assertFalse(
			$wp_query->is_404,
			'When the disabled-view 404 guard is turned off in settings, a disabled view slug must not 404.'
		);

		remove_all_filters( 'tribe_get_option_' . Settings::OPT_DISABLED_VIEW_404 );
	}
}
