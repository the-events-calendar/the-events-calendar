<?php

namespace Tribe\Events\Views\V2;

use Tribe\Events\Views\V2\Views\Day_View;
use DateTime;

/**
 * Class Month_ViewTest
 *
 * @since 6.1.3
 *
 * @package Tribe\Events\Views\V2
 */
class Day_ViewTest extends \Codeception\TestCase\WPTestCase {

	/**
	 * When mocking the `date` function this is the value that will be used to generate the date in place of the real
	 * one.
	 *
	 * @var string
	 */
	protected $mock_date_value = '2019-03-10 09:00:00';

	/**
	 * The mock rendering context.
	 *
	 * @var \Tribe__Context|\WP_UnitTest_Factory|null
	 */
	protected $context;

	public function setUp(): void {
		parent::setUp();
		tribe( 'cache' )->reset();

		tribe_unset_var( \Tribe__Settings_Manager::OPTION_CACHE_VAR_NAME );

		$now = new \DateTime( $this->mock_date_value );

		$this->context = tribe_context()->alter(
			[
				'today'      => $this->mock_date_value,
				'now'        => $this->mock_date_value,
				'event_date' => $now->format( 'Y-m-d' ),
			]
		);

		tribe( 'cache' )->reset();
	}

	/**
	 * @test
	 */
	public function should_have_next_date() {
		$timezone_string = 'Europe/Paris';
		$timezone        = new \DateTimeZone( $timezone_string );
		update_option( 'timezone_string', $timezone_string );

		$now = new DateTime( $this->mock_date_value, $timezone );

		$events_data = [
			[ // Event Today
				'start_date' => '2019-03-10 11:00:00',
				'end_date'   => '2019-03-10 14:00:00',
			],
			[ // Event Today
				'start_date' => '2019-03-10 14:00:00',
				'end_date'   => '2019-03-10 17:00:00',
			],
			[ // Event tomorrow
				'start_date' => '2019-03-11 14:00:00',
				'end_date'   => '2019-03-11 17:00:00',
			],
			[ // Event yesterday
				'start_date' => '2019-03-09 14:00:00',
				'end_date'   => '2019-03-09 17:00:00',
			],
		];

		foreach ( $events_data as $data ) {
			tribe_events()->set_args(
				[
					'start_date' => $data['start_date'],
					'end_date'   => $data['end_date'],
					'timezone'   => $timezone,
					'duration'   => 3 * HOUR_IN_SECONDS,
					'title'      => 'Test Event - ' . substr( md5( wp_json_encode( $data ) ), 10 ),
					'status'     => 'publish',
				]
			)->create();
		}

		/* @var Day_View $day_view */
		$day_view = View::make( Day_View::class, $this->context );

		$check_events = [];
		$was_hit      = false;
		// Should be a cold cache - should query.
		add_filter( 'query', function ( $query ) use ( &$was_hit ) {
			$was_hit = true;

			return $query;
		} );
		$check_events[] = $day_view->get_next_event_date( clone $now );
		$this->assertTrue( $was_hit, 'Cold cache - should do a query to fetch' );
		$was_hit        = false;
		$check_events[] = $day_view->get_next_event_date( clone $now );
		$this->assertFalse( $was_hit, 'Warm cache - should not do a query to fetch' );
		$was_hit        = false;
		$check_events[] = $day_view->get_next_event_date( clone $now );
		$this->assertFalse( $was_hit, 'Warm cache - should not do a query to fetch' );
		$was_hit = false;

		foreach ( $check_events as $next_event_date ) {
			$this->assertInstanceOf( DateTime::class, $next_event_date );
			$this->assertEquals( '2019-03-11', $next_event_date->format( 'Y-m-d' ), 'Expect the only next day event to be the next event date.' );
		}
	}

	/**
	 * @test
	 */
	public function should_have_previous_date() {
		$timezone_string = 'Europe/Paris';
		$timezone        = new \DateTimeZone( $timezone_string );
		update_option( 'timezone_string', $timezone_string );

		$now = new \DateTime( $this->mock_date_value, $timezone );

		$events_data = [
			[ // Event Today
				'start_date' => '2019-03-10 11:00:00',
				'end_date'   => '2019-03-10 14:00:00',
			],
			[ // Event Today
				'start_date' => '2019-03-10 14:00:00',
				'end_date'   => '2019-03-10 17:00:00',
			],
			[ // Event tomorrow
				'start_date' => '2019-03-11 14:00:00',
				'end_date'   => '2019-03-11 17:00:00',
			],
			[ // Event yesterday
				'start_date' => '2019-03-09 14:00:00',
				'end_date'   => '2019-03-09 17:00:00',
			],
		];

		foreach ( $events_data as $data ) {
			tribe_events()->set_args(
				[
					'start_date' => $data['start_date'],
					'end_date'   => $data['end_date'],
					'timezone'   => $timezone,
					'duration'   => 3 * HOUR_IN_SECONDS,
					'title'      => 'Test Event - ' . substr( md5( wp_json_encode( $data ) ), 10 ),
					'status'     => 'publish',
				]
			)->create();
		}

		/* @var Day_View $day_view */
		$day_view = View::make( Day_View::class, $this->context );

		$check_events = [];
		$was_hit      = false;
		// Should be a cold cache - should query.
		add_filter( 'query', function ( $query ) use ( &$was_hit ) {
			$was_hit = true;

			return $query;
		} );
		$check_events[] = $day_view->get_previous_event_date( clone $now );
		$this->assertTrue( $was_hit, 'Cold cache - should do a query to fetch' );
		$was_hit        = false;
		$check_events[] = $day_view->get_previous_event_date( clone $now );
		$this->assertFalse( $was_hit, 'Warm cache - should not do a query to fetch' );
		$was_hit        = false;
		$check_events[] = $day_view->get_previous_event_date( clone $now );
		$this->assertFalse( $was_hit, 'Warm cache - should not do a query to fetch' );
		$was_hit = false;

		foreach ( $check_events as $previous_event_date ) {
			$this->assertInstanceOf( DateTime::class, $previous_event_date );
			$this->assertEquals( '2019-03-09', $previous_event_date->format( 'Y-m-d' ), 'Expect the only previous day event to be the previous event date.' );
		}
	}

}
