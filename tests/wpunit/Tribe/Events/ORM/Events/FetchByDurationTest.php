<?php

namespace Tribe\Events\ORM\Events;

use Tribe\Events\Test\Factories\Event;

class FetchByDurationTest extends \Codeception\TestCase\WPTestCase {

	public function setUp() {
		// before
		parent::setUp();

		tribe_unset_var( \Tribe__Settings_Manager::OPTION_CACHE_VAR_NAME );

		// your set up methods here
		$this->factory()->event = new Event();
		// Explicitly set the timezone mode to use the site-wide setting.
		tribe_update_option( 'tribe_events_timezone_mode', 'site' );
	}

	/**
	 * It should allow ordering events by duration in ASC order
	 *
	 * @test
	 */
	public function should_allow_ordering_events_by_duration_in_asc_order() {
		$date_times = [
			'2020-01-01 09:00:00' => 2 * HOUR_IN_SECONDS,
			'2020-01-01 11:00:00' => 3 * HOUR_IN_SECONDS,
			'2020-01-01 13:00:00' => HOUR_IN_SECONDS,
			'2020-01-01 15:00:00' => 1.5 * HOUR_IN_SECONDS,
			'2020-01-01 17:00:00' => 4 * HOUR_IN_SECONDS,
		];
		foreach ( $date_times as $date_time => $duration ) {
			$event_ids[] = tribe_events()
				->set_args( [
					'title'      => $date_time . ' Event',
					'status'     => 'publish',
					'start_date' => $date_time,
					'duration'   => $duration,
				] )->create()->ID;
		}

		$fetched = tribe_events()
			->order_by( 'event_duration', 'ASC' )
			->collect()
			->pluck_meta( '_EventDuration' );

		$this->assertEquals( array_values( [
			'2020-01-01 13:00:00' => HOUR_IN_SECONDS,
			'2020-01-01 15:00:00' => 1.5 * HOUR_IN_SECONDS,
			'2020-01-01 09:00:00' => 2 * HOUR_IN_SECONDS,
			'2020-01-01 11:00:00' => 3 * HOUR_IN_SECONDS,
			'2020-01-01 17:00:00' => 4 * HOUR_IN_SECONDS,
		] ), $fetched );
	}

	/**
	 * It should allow ordering events by duration in DESC order
	 *
	 * @test
	 */
	public function should_allow_ordering_events_by_duration_in_desc_order() {
		$date_times = [
			'2020-01-01 09:00:00' => 2 * HOUR_IN_SECONDS,
			'2020-01-01 11:00:00' => 3 * HOUR_IN_SECONDS,
			'2020-01-01 13:00:00' => HOUR_IN_SECONDS,
			'2020-01-01 15:00:00' => 1.5 * HOUR_IN_SECONDS,
			'2020-01-01 17:00:00' => 4 * HOUR_IN_SECONDS,
		];
		foreach ( $date_times as $date_time => $duration ) {
			$event_ids[] = tribe_events()
				->set_args( [
					'title'      => $date_time . ' Event',
					'status'     => 'publish',
					'start_date' => $date_time,
					'duration'   => $duration,
				] )->create()->ID;
		}

		$fetched = tribe_events()
			->order_by( 'event_duration', 'DESC' )
			->collect()
			->pluck_meta( '_EventDuration' );

		$this->assertEquals( array_values( [
			'2020-01-01 17:00:00' => 4 * HOUR_IN_SECONDS,
			'2020-01-01 11:00:00' => 3 * HOUR_IN_SECONDS,
			'2020-01-01 09:00:00' => 2 * HOUR_IN_SECONDS,
			'2020-01-01 15:00:00' => 1.5 * HOUR_IN_SECONDS,
			'2020-01-01 13:00:00' => HOUR_IN_SECONDS,
		] ), $fetched );
	}

	public function date_and_direction_order_data_provider() {
		yield 'date=DESC duration=DESC' => [
			'DESC',
			'DESC',
			[
				[ '2020-01-02 09:00:00', 4 * HOUR_IN_SECONDS ],
				[ '2020-01-02 09:00:00', 2 * HOUR_IN_SECONDS ],
				[ '2020-01-02 09:00:00', HOUR_IN_SECONDS ],
				[ '2020-01-02 09:00:00', .5 * HOUR_IN_SECONDS ],
				[ '2020-01-01 09:00:00', 4 * HOUR_IN_SECONDS ],
				[ '2020-01-01 09:00:00', 2 * HOUR_IN_SECONDS ],
				[ '2020-01-01 09:00:00', HOUR_IN_SECONDS ],
				[ '2020-01-01 09:00:00', .5 * HOUR_IN_SECONDS ],
			]
		];

		yield 'date=ASC duration=DESC' => [
			'ASC',
			'DESC',
			[
				[ '2020-01-01 09:00:00', 4 * HOUR_IN_SECONDS ],
				[ '2020-01-01 09:00:00', 2 * HOUR_IN_SECONDS ],
				[ '2020-01-01 09:00:00', HOUR_IN_SECONDS ],
				[ '2020-01-01 09:00:00', .5 * HOUR_IN_SECONDS ],
				[ '2020-01-02 09:00:00', 4 * HOUR_IN_SECONDS ],
				[ '2020-01-02 09:00:00', 2 * HOUR_IN_SECONDS ],
				[ '2020-01-02 09:00:00', HOUR_IN_SECONDS ],
				[ '2020-01-02 09:00:00', .5 * HOUR_IN_SECONDS ],
			]
		];

		yield 'date=ASC duration=ASC' => [
			'ASC',
			'ASC',
			[
				[ '2020-01-01 09:00:00', .5 * HOUR_IN_SECONDS ],
				[ '2020-01-01 09:00:00', HOUR_IN_SECONDS ],
				[ '2020-01-01 09:00:00', 2 * HOUR_IN_SECONDS ],
				[ '2020-01-01 09:00:00', 4 * HOUR_IN_SECONDS ],
				[ '2020-01-02 09:00:00', .5 * HOUR_IN_SECONDS ],
				[ '2020-01-02 09:00:00', HOUR_IN_SECONDS ],
				[ '2020-01-02 09:00:00', 2 * HOUR_IN_SECONDS ],
				[ '2020-01-02 09:00:00', 4 * HOUR_IN_SECONDS ],
			]
		];

		yield 'date=DESC duration=ASC' => [
			'DESC',
			'ASC',
			[
				[ '2020-01-02 09:00:00', .5 * HOUR_IN_SECONDS ],
				[ '2020-01-02 09:00:00', HOUR_IN_SECONDS ],
				[ '2020-01-02 09:00:00', 2 * HOUR_IN_SECONDS ],
				[ '2020-01-02 09:00:00', 4 * HOUR_IN_SECONDS ],
				[ '2020-01-01 09:00:00', .5 * HOUR_IN_SECONDS ],
				[ '2020-01-01 09:00:00', HOUR_IN_SECONDS ],
				[ '2020-01-01 09:00:00', 2 * HOUR_IN_SECONDS ],
				[ '2020-01-01 09:00:00', 4 * HOUR_IN_SECONDS ],
			]
		];
	}

	/**
	 * It should allow ordering event by start date and duration
	 *
	 * @test
	 * @dataProvider date_and_direction_order_data_provider
	 */
	public function should_allow_ordering_event_by_start_date_and_duration(
		string $date_order,
		string $duration_order,
		array $expected
	) {
		$date_times = [
			[ '2020-01-01 09:00:00', HOUR_IN_SECONDS ],
			[ '2020-01-01 09:00:00', 2 * HOUR_IN_SECONDS ],
			[ '2020-01-01 09:00:00', .5 * HOUR_IN_SECONDS ],
			[ '2020-01-01 09:00:00', 4 * HOUR_IN_SECONDS ],
			[ '2020-01-02 09:00:00', 4 * HOUR_IN_SECONDS ],
			[ '2020-01-02 09:00:00', .5 * HOUR_IN_SECONDS ],
			[ '2020-01-02 09:00:00', 2 * HOUR_IN_SECONDS ],
			[ '2020-01-02 09:00:00', HOUR_IN_SECONDS ],
		];
		foreach ( $date_times as list( $date_time, $duration ) ) {
			$event_ids[] = tribe_events()
				->set_args( [
					'title'      => sprintf( 'Event %s duration %d', $date_time, $duration ),
					'status'     => 'publish',
					'start_date' => $date_time,
					'duration'   => $duration,
				] )->create()->ID;
		}

		$fetched = tribe_events()
			->order_by( [ 'event_date' => $date_order, 'event_duration' => $duration_order ] )
			->get_ids();

		$fetched_dates_and_durations = array_map( static function ( int $post_id ) {
			return [
				get_post_meta( $post_id, '_EventStartDate', true ),
				(string) (int) get_post_meta( $post_id, '_EventDuration', true ),
			];
		}, $fetched );
		$this->assertEquals( $expected, $fetched_dates_and_durations );
	}
}
