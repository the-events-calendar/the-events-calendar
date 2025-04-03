<?php

namespace Tribe\Events\Views\V2\SEO\Headers;

use TEC\Events\SEO\Headers\Controller;
use Tribe\Tests\Traits\With_Uopz;
use Tribe__Events__Main as TEC;

/**
 * Tests for the month view behavior in the SEO Controller.
 */
class Controller_Month_View_Test extends \Codeception\TestCase\WPTestCase {

	use With_Uopz;

	/**
	 * Create test events for the given date range.
	 *
	 * This method creates one event at the start of the range, one at the test day,
	 * and one at the end of the range.
	 *
	 * @param string $range_start The start date (format: Y-m-d).
	 * @param string $range_end   The end date (format: Y-m-d).
	 * @param string $test_day    The specific test day (format: Y-m-d).
	 */
	private function create_test_events( string $range_start, string $range_end, string $test_day ) {
		$timezone_string = get_option( 'timezone_string' ) ?: 'UTC';

		$events = [
			[
				'date'  => $range_start,
				'time'  => '09:00',
				'title' => 'Test Event Start',
			],
			[
				'date'  => $test_day,
				'time'  => '12:00',
				'title' => 'Test Event Test Day',
			],
			[
				'date'  => $range_end,
				'time'  => '12:30',
				'title' => 'Test Event End',
			],
		];

		foreach ( $events as $data ) {
			tribe_events()->set_args( [
				'start_date' => $data['date'] . ' ' . $data['time'],
				'timezone'   => $timezone_string,
				'duration'   => 3 * HOUR_IN_SECONDS,
				'title'      => $data['title'],
				'status'     => 'publish',
			] )->create();
		}
	}

	/**
	 * @test
	 */
	public function test_month_view_disabled_always_shows_404() {
		// Disable month view by allowing only the day view.
		add_filter( 'tribe_get_option_tribeEnableViews', function ( $value, $optionName, $default ) {
			return [ 'day' ];
		}, 10, 3 );

		// Create some events (though they may not be used here).
		$this->create_test_events( '2023-06-20', '2023-12-31', '2023-06-15' );
		tribe_update_option( 'earliest_date', false );
		tribe_update_option( 'latest_date', false );

		global $wp_query;
		$wp_query->query = [
			'post_type'    => 'tribe_events',
			'eventDisplay' => 'month',
			'eventDate'    => '2023-06', // Month format "Y-m"
		];

		tribe_register_provider( Controller::class );
		tribe( Controller::class )->filter_headers();

		$this->assertTrue( $wp_query->is_404, 'When the month view is disabled, a 404 should be set.' );
	}

	/**
	 * @test
	 */
	public function test_month_view_before_range_shows_404() {
		// Create events that span from September to December 2023.
		$this->create_test_events( '2023-09-01', '2023-12-31', '2023-09-15' );
		tribe_update_option( 'earliest_date', false );
		tribe_update_option( 'latest_date', false );

		global $wp_query;
		$wp_query->query = [
			'post_type'    => 'tribe_events',
			'eventDisplay' => 'month',
			'eventDate'    => '2023-06', // Before the earliest event month ("2023-09")
		];

		tribe_register_provider( Controller::class );
		tribe( Controller::class )->filter_headers();

		$this->assertTrue( $wp_query->is_404, 'A month before the earliest allowed month should trigger a 404.' );
	}

	/**
	 * @test
	 */
	public function test_month_view_after_range_shows_404() {
		// Create events that span from January to June 2023.
		$this->create_test_events( '2023-01-01', '2023-06-10', '2023-06-05' );
		tribe_update_option( 'earliest_date', false );
		tribe_update_option( 'latest_date', false );

		global $wp_query;
		$wp_query->query = [
			'post_type'    => 'tribe_events',
			'eventDisplay' => 'month',
			'eventDate'    => '2023-08', // After the latest event month ("2023-06")
		];

		tribe_register_provider( Controller::class );
		tribe( Controller::class )->filter_headers();

		$this->assertTrue( $wp_query->is_404, 'A month after the latest allowed month should trigger a 404.' );
	}

	/**
	 * @test
	 */
	public function test_month_view_with_valid_range_no_404() {
		// Create events that span from January to December 2023.
		$this->create_test_events( '2023-01-01', '2023-12-31', '2023-06-15' );
		tribe_update_option( 'earliest_date', false );
		tribe_update_option( 'latest_date', false );

		global $wp_query;
		$wp_query->query = [
			'post_type'    => 'tribe_events',
			'eventDisplay' => 'month',
			'eventDate'    => '2023-06', // Within the event range
		];

		tribe_register_provider( Controller::class );
		tribe( Controller::class )->filter_headers();

		$this->assertFalse( $wp_query->is_404, 'A month within the allowed range should not trigger a 404.' );
	}

	/**
	 * @test
	 */
	public function test_no_events_within_current_month_shows_no_404() {
		// Simulate a new install: no earliest or latest event dates.
		tribe_update_option( 'earliest_date', false );
		tribe_update_option( 'latest_date', false );

		// Set up a fake "now" to simulate a Month View request at 2023-06-11 22:00:00 in NY timezone.
		$date = new \DateTime( '2023-06-11 22:00:00', new \DateTimeZone( 'America/New_York' ) );
		$now  = $date->getTimestamp();
		$this->set_class_fn_return( Controller::class, 'get_current_month', static fn() => date( 'Y-m', $now ), true );

		global $wp_query;
		$wp_query->query = [
			'post_type'    => 'tribe_events',
			'eventDisplay' => 'month',
			'eventDate'    => '2023-06', // Matches current month
		];

		tribe_register_provider( Controller::class );
		tribe( Controller::class )->filter_headers();

		$this->assertFalse( $wp_query->is_404, 'When there are no events and the requested month equals the current month, no 404 should be set.' );
	}

	/**
	 * @test
	 */
	public function test_no_event_and_outside_current_month_shows_404() {
		// Simulate a new install: no earliest or latest event dates.
		tribe_update_option( 'earliest_date', false );
		tribe_update_option( 'latest_date', false );

		// Set up a fake "now" to simulate a Month View request at 2023-06-11 22:00:00 in NY timezone.
		$date = new \DateTime( '2023-06-11 22:00:00', new \DateTimeZone( 'America/New_York' ) );
		$now  = $date->getTimestamp();
		$this->set_class_fn_return( Controller::class, 'get_current_month', static fn() => date( 'Y-m', $now ), true );

		global $wp_query;
		$wp_query->query = [
			'post_type'    => 'tribe_events',
			'eventDisplay' => 'month',
			'eventDate'    => '2023-05', // Does not match current month (2023-06)
		];

		tribe_register_provider( Controller::class );
		tribe( Controller::class )->filter_headers();

		$this->assertTrue( $wp_query->is_404, 'When there are no events and the requested month does not equal the current month, a 404 should be set.' );
	}
}
