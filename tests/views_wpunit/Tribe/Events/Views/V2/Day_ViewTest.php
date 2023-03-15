<?php

namespace Tribe\Events\Views\V2;

use Tribe\Events\Views\V2\Views\Day_View;
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
class Day_ViewTest extends \Codeception\TestCase\WPTestCase {
	use With_Post_Remapping;

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

		$events            = array_map(
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
		$event_ids         = wp_list_pluck( $events, 'ID' );
		$mock_and_insert   = function ( $template, $id ) {
			$this->wp_insert_post( $this->get_mock_event( $template, [ 'id' => $id ] ) );

			return $id;
		};

		$remapped_post_ids = array_combine( $event_ids, [
			$mock_and_insert( 'events/single/id.template.json', 234234234 ),
			$mock_and_insert( 'events/single/id.template.json', 2453454355 ),
			$mock_and_insert( 'events/single/id.template.json', 3094853477 ),
			$mock_and_insert( 'events/single/id.template.json', 3094855477 ),
		] );

		/* @var Day_View $day_view */
		$day_view = View::make( Day_View::class, $this->context );

		$next_event_date = $day_view->get_next_event_date( $now );

		$this->assertInstanceOf( DateTime::class, $next_event_date );
		$this->assertEquals( '2019-03-11', $next_event_date->format( 'Y-m-d' ), 'Expect the only next day event to be the next event date.' );
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

		$events            = array_map(
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
		$event_ids         = wp_list_pluck( $events, 'ID' );
		$mock_and_insert   = function ( $template, $id ) {
			$this->wp_insert_post( $this->get_mock_event( $template, [ 'id' => $id ] ) );

			return $id;
		};

		$remapped_post_ids = array_combine( $event_ids, [
			$mock_and_insert( 'events/single/id.template.json', 234234234 ),
			$mock_and_insert( 'events/single/id.template.json', 2453454355 ),
			$mock_and_insert( 'events/single/id.template.json', 3094853477 ),
			$mock_and_insert( 'events/single/id.template.json', 3094855477 ),
		] );

		/* @var Day_View $day_view */
		$day_view = View::make( Day_View::class, $this->context );

		$previous_event_date = $day_view->get_previous_event_date( $now );

		$this->assertInstanceOf( DateTime::class, $previous_event_date );
		$this->assertEquals( '2019-03-09', $previous_event_date->format( 'Y-m-d' ), 'Expect the only previous day event to be the previous event date.' );
	}

}