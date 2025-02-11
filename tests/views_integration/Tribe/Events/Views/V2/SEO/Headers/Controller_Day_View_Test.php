<?php

namespace Tribe\Events\Views\V2\SEO\Headers;

use TEC\Events\SEO\Headers\Controller;
use Tribe\Tests\Traits\With_Uopz;

/**
 * Tests for the day view behavior in the SEO Controller.
 */
class Controller_Day_View_Test extends \Codeception\TestCase\WPTestCase {

	use With_Uopz;

	/**
	 * Set up default dates.
	 */
	public function setUp() {
		parent::setUp();
	}

	/**
	 * Clean up after each test.
	 */
	public function tearDown() {
		remove_all_filters( 'tribe_settings_manager_get_options' );
		parent::tearDown();
	}

	/**
	 * Create test events for the given date range.
	 *
	 * This method creates one event at the start of the range, one at the end,
	 * and one on the specific test day.
	 *
	 * @param string $range_start The start date of the range (format: Y-m-d).
	 * @param string $range_end   The end date of the range (format: Y-m-d).
	 * @param string $test_day    The specific day to test (format: Y-m-d).
	 */
	private function create_test_events( string $range_start, string $range_end, string $test_day ) {
		$timezone_string = get_option( 'timezone_string' ) ?: 'UTC';

		// Create event at the start of the range.
		tribe_events()->set_args( [
			'start_date' => $range_start . ' 09:00',
			'timezone'   => $timezone_string,
			'duration'   => 3 * HOUR_IN_SECONDS,
			'title'      => 'Test Event Start',
			'status'     => 'publish',
		] )->create();

		// Create event on the test day.
		tribe_events()->set_args( [
			'start_date' => $test_day . ' 12:00',
			'timezone'   => $timezone_string,
			'duration'   => 3 * HOUR_IN_SECONDS,
			'title'      => 'Test Event Test Day',
			'status'     => 'publish',
		] )->create();

		// Create event at the end of the range.
		tribe_events()->set_args( [
			'start_date' => $range_end . ' 12:30',
			'timezone'   => $timezone_string,
			'duration'   => 3 * HOUR_IN_SECONDS,
			'title'      => 'Test Event End',
			'status'     => 'publish',
		] )->create();
	}

	/**
	 * @test
	 */
	public function test_day_view_disabled_always_shows_404() {
		// Disable the day view by only allowing the month view.
		add_filter( 'tribe_get_option_tribeEnableViews', function ( $value, $optionName, $default ) {
			return [ 'month' ];
		}, 10, 3 );

		// Create events for the range.
		$this->create_test_events( '2023-06-20', '2023-12-31', '2023-06-15' );
		tribe_update_option( 'earliest_date', false );
		tribe_update_option( 'latest_date', false );

		global $wp_query;
		$wp_query->query = [
			'post_type'    => 'tribe_events',
			'eventDisplay' => 'day',
			'eventDate'    => '2023-06-20',
		];

		// Ensure our controller is registered.
		tribe_register_provider( Controller::class );
		tribe( Controller::class )->filter_headers();

		$this->assertTrue( $wp_query->is_404, 'When the day view is disabled, a 404 should be set.' );
	}

	/**
	 * @test
	 */
	public function test_day_view_before_range_shows_404() {
		// Create events for the range.
		$this->create_test_events( '2023-06-20', '2023-12-31', '2023-09-15' );
		tribe_update_option( 'earliest_date', false );
		tribe_update_option( 'latest_date', false );

		global $wp_query;
		$wp_query->query = [
			'post_type'    => 'tribe_events',
			'eventDisplay' => 'day',
			'eventDate'    => '2023-05-15',
		];

		tribe_register_provider( Controller::class );
		tribe( Controller::class )->filter_headers();

		$this->assertTrue( $wp_query->is_404, 'An event date before the earliest allowed date should trigger a 404.' );
	}

	/**
	 * @test
	 */
	public function test_day_view_after_range_shows_404() {
		// Create events for the range.
		$this->create_test_events( '2023-01-01', '2023-06-10', '2023-06-15' );
		tribe_update_option( 'earliest_date', false );
		tribe_update_option( 'latest_date', false );

		global $wp_query;
		$wp_query->query = [
			'post_type'    => 'tribe_events',
			'eventDisplay' => 'day',
			'eventDate'    => '2024-01-15',
		];

		tribe_register_provider( Controller::class );
		tribe( Controller::class )->filter_headers();

		$this->assertTrue( $wp_query->is_404, 'An event date after the latest allowed date should trigger a 404.' );
	}

	/**
	 * @test
	 */
	public function test_day_view_with_valid_range_no_404() {
		// Create events for the range.
		$this->create_test_events( '2023-01-01', '2023-12-31', '2023-06-15' );
		tribe_update_option( 'earliest_date', false );
		tribe_update_option( 'latest_date', false );

		global $wp_query;
		$wp_query->query = [
			'post_type'    => 'tribe_events',
			'eventDisplay' => 'day',
			'eventDate'    => '2023-06-16',
		];

		tribe_register_provider( Controller::class );
		tribe( Controller::class )->filter_headers();

		$this->assertFalse( $wp_query->is_404, 'An event date within the allowed range should not trigger a 404.' );
	}

	/**
	 * @test
	 */
	public function test_no_events_within_current_month_shows_no_404() {
		// Simulate a new install: no earliest or latest event dates.
		tribe_update_option( 'earliest_date', false );
		tribe_update_option( 'latest_date', false );

		// Set up a fake "now" to simulate a Day View request done at 2023-06-11 22:00:00 in NY timezone.
		$date = new \DateTime( '2023-06-11 22:00:00', new \DateTimeZone( 'America/New_York' ) );
		$now  = $date->getTimestamp();
		// Override the Controller's current month method to use our fake "now".
		$this->set_class_fn_return( Controller::class, 'get_current_month', static fn() => date( 'Y-m', $now ), true );

		global $wp_query;
		$wp_query->query = [
			'post_type'    => 'tribe_events',
			'eventDisplay' => 'day',
			'eventDate'    => '2023-06-16', // This falls in June 2023.
		];

		tribe_register_provider( Controller::class );
		tribe( Controller::class )->filter_headers();

		$this->assertFalse( $wp_query->is_404, 'An event date within the current month should not trigger a 404 when there are no events.' );
	}

	/**
	 * @test
	 */
	public function test_no_event_and_outside_current_month() {
		// Simulate a new install: no earliest or latest event dates.
		tribe_update_option( 'earliest_date', false );
		tribe_update_option( 'latest_date', false );

		// Set up a fake "now" to simulate a Day View request done at 2023-06-11 22:00:00 in NY timezone.
		$date = new \DateTime( '2023-06-11 22:00:00', new \DateTimeZone( 'America/New_York' ) );
		$now  = $date->getTimestamp();
		// Override the Controller's current month method to use our fake "now".
		$this->set_class_fn_return( Controller::class, 'get_current_month', static fn() => date( 'Y-m', $now ), true );

		global $wp_query;
		$wp_query->query = [
			'post_type'    => 'tribe_events',
			'eventDisplay' => 'day',
			'eventDate'    => '2023-05-16',
		];

		tribe_register_provider( Controller::class );
		tribe( Controller::class )->filter_headers();

		$this->assertTrue( $wp_query->is_404, 'An event date within the current month should not trigger a 404 when there are no events.' );
	}
}
