<?php

namespace Tribe\Events\Views\V2;

use Tribe\Events\Views\V2\Views\Month_View;
use Tribe\Test\PHPUnit\Traits\With_Post_Remapping;
use Tribe__Events__Main as TEC;
use DateTime;

/**
 * Class Month_ViewTest
 *
 * @since   TBD
 *
 * @package Tribe\Events\Views\V2
 */
class Month_ViewTest extends \Codeception\TestCase\WPTestCase {

	/**
	 * When mocking the `date` function this is the value that will be used to generate the date in place of the real
	 * one.
	 *
	 * @var string
	 */
	protected $mock_date_value = '2019-01-05 09:00:00';

	/**
	 * The mock rendering context.
	 *
	 * @var \Tribe__Context|\WP_UnitTest_Factory|null
	 */
	protected $context;

	public function setUp() {
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

		$now = new \DateTime( $this->mock_date_value, $timezone );

		$events_data = [
			[ // Event next month.
				'start_date' => '2019-02-10 14:00:00',
				'end_date'   => '2019-02-10 17:00:00',
			],
			[ // Event this month but in the future from now.
				'start_date' => '2019-01-15 14:00:00',
				'end_date'   => '2019-01-15 17:00:00',
			],
			[ // Event far in the future from now.
				'start_date' => '2019-05-10 14:00:00',
				'end_date'   => '2019-05-10 17:00:00',
			],
			[ // Event before now.
				'start_date' => '2019-01-01 14:00:00',
				'end_date'   => '2019-01-01 17:00:00',
			],
		];

		$events = array_map(
			static function ( $data ) use ( $timezone ) {
				return tribe_events()->set_args(
					[
						'start_date' => $data['start_date'],
						'end_date'   => $data['end_date'],
						'timezone'   => $timezone,
						'duration'   => 3 * HOUR_IN_SECONDS,
						'title'      => 'Test Event - ' . substr( md5( wp_json_encode( $data ) ), 10 ),
						'status'     => 'publish',
					]
				)->create();
			},
			$events_data
		);

		$month_view = View::make( Month_View::class, $this->context );

		$next_event_date = $month_view->get_next_event_date( $now );

		$this->assertInstanceOf( DateTime::class, $next_event_date );
		$this->assertEquals( '2019-02-10', $next_event_date->format( 'Y-m-d' ), 'Expect the only next month event to be the next event date.' );
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
			[ // Event previous month.
				'start_date' => '2018-12-01 14:00:00',
				'end_date'   => '2018-12-01 17:00:00',
			],
			[ // Event this month but in the future from now.
				'start_date' => '2019-01-15 14:00:00',
				'end_date'   => '2019-01-15 17:00:00',
			],
			[ // Event far in the future from now.
				'start_date' => '2019-05-10 14:00:00',
				'end_date'   => '2019-05-10 17:00:00',
			],
			[ // Event before now.
				'start_date' => '2019-01-01 14:00:00',
				'end_date'   => '2019-01-01 17:00:00',
			],
		];

		$events = array_map(
			static function ( $data ) use ( $timezone ) {
				return tribe_events()->set_args(
					[
						'start_date' => $data['start_date'],
						'end_date'   => $data['end_date'],
						'timezone'   => $timezone,
						'duration'   => 3 * HOUR_IN_SECONDS,
						'title'      => 'Test Event - ' . substr( md5( wp_json_encode( $data ) ), 10 ),
						'status'     => 'publish',
					]
				)->create();
			},
			$events_data
		);

		/* @var Month_View $month_view */
		$month_view = View::make( Month_View::class, $this->context );

		$previous_event_date = $month_view->get_previous_event_date( $now );

		$this->assertInstanceOf( DateTime::class, $previous_event_date );
		$this->assertEquals( '2018-12-01', $previous_event_date->format( 'Y-m-d' ), 'Expect the only previous month event to be the previous event date.' );
	}

}