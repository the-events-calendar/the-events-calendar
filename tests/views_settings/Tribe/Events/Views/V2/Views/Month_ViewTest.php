<?php

namespace Tribe\Events\Views\V2\Views;

use Codeception\TestCase\WPTestCase;
use Tribe\Events\Views\V2\View;
use Tribe\Test\Products\Traits\With_View_Context;
use Tribe__Date_Utils as Dates;
use Tribe__Events__Template__Month as Month;

class Month_ViewTest extends WPTestCase {
	use With_View_Context;

	public function expected_events_per_day_alterations() {
		yield 'One event, same timezone as site' => [
			[
				'event_date'           => '2020-01',
				'options'              => [
					'timezone_string' => 'America/Los_Angeles',
					'start_of_week'   => '0',
				],
				'tribe_options'        => [
					'multiDayCutoff' => '00:00',
				],
				'month_posts_per_page' => 10,
				'events'               => [
					'on_2020_01_01_8am_to_5pm'            => [
						'title'      => 'On 2020-01-01, 8am to 5pm',
						'start_date' => '2020-01-01 09:00:00',
						'duration'   => 4 * HOUR_IN_SECONDS,
						'status'     => 'publish',
					],
					'on_2020_01_01_8am_to_5pm_next_day'   => [
						'title'      => 'On 2020-01-01, 8am to 5pm on the next day',
						'start_date' => '2020-01-01 09:00:00',
						'end_date'   => '2020-01-02 17:00:00',
						'status'     => 'publish',
					],
					'all_day_on_2020_01_01'               => [
						'title'      => 'All-day on 2020-01-01',
						'start_date' => '2020-01-01',
						'end_date'   => '2020-01-01',
						'all_day'    => true,
						'status'     => 'publish',
					],
					'all_day_on_2020_01_01_to_2020_01_02' => [
						'title'      => 'All-day on 2020-01-01 to 2020-01-02',
						'start_date' => '2020-01-01',
						'end_date'   => '2020-01-02',
						'all_day'    => true,
						'status'     => 'publish',
					],
				],
			],
			// Expectations.
			[
				'events' => [
					'2020-01-01' => [
						'all_day_on_2020_01_01',
						'all_day_on_2020_01_01_to_2020_01_02',
						'on_2020_01_01_8am_to_5pm',
						'on_2020_01_01_8am_to_5pm_next_day',
					],
					'2020-01-02' => [
						'all_day_on_2020_01_01_to_2020_01_02',
						'on_2020_01_01_8am_to_5pm_next_day',
					],
				],
				'stack'  => [
					'2020-01-01' => [
						'all_day_on_2020_01_01',
						'all_day_on_2020_01_01_to_2020_01_02',
						'on_2020_01_01_8am_to_5pm_next_day',
					],
					'2020-01-02' => [
						null,
						'all_day_on_2020_01_01_to_2020_01_02',
						'on_2020_01_01_8am_to_5pm_next_day',
					],
				],
			],
		];
	}

	/**
	 * It should return the expected events per day
	 *
	 * The purpose of this test is to make sure that, depending on diff. combinations of TEC settings, the View
	 * will return the events we expect in each day.
	 *
	 * @test
	 *
	 * @dataProvider expected_events_per_day_alterations
	 */
	public function should_return_the_expected_events_per_day( array $alterations = [], array $expected = [] ) {
		$context = $this->setup_context( $alterations );

		/** @var Month_View $view */
		$view          = View::make( Month_View::class, $context );
		$template_vars = $view->get_template_vars();

		$this->assertArrayHasKey( 'events', $template_vars );
		$this->assertArrayHasKey( 'days', $template_vars );

		$days              = $template_vars['events'];
		$the_days          = array_keys( $days );
		$event_ids_per_day = array_filter( $days );
		$stack_ids_per_day = array_filter( array_combine(
			array_keys( $template_vars['days'] ),
			array_map( static function ( $day ) {
				return count( $day ) ? wp_list_pluck( $day, 'ID' ) : [];
			}, array_column( $template_vars['days'], 'multiday_events' ) )
		) );

		$expected_days = $this->build_expected_days( $alterations );
		list( $expected_event_ids_per_day, $expected_stack_per_day ) = $this->parse_expected_events( $expected );

		$this->assertEquals( $expected_days, $the_days, 'The list of days does not match.' );
		$this->assertEquals( $expected_event_ids_per_day, $event_ids_per_day, 'The events IDs per day do not match.' );
		$this->assertEquals( $expected_stack_per_day, $stack_ids_per_day, 'The stack IDs per day do not match.' );
	}

	/**
	 * Builds the set of days we expect to see in the View results.
	 *
	 * @param array<string,mixed> $alterations The alterations to build the expected days from.
	 *
	 * @return array<string> An array of days, w/o gaps, each in the `Y-m-d` format.
	 */
	protected function build_expected_days( array $alterations = [] ) {
		$this->ensure_alteration( __METHOD__, 'event_date', $alterations );
		$this->ensure_alteration( __METHOD__, 'options.timezone_string', $alterations );
		$this->ensure_alteration( __METHOD__, 'options.start_of_week', $alterations );

		$event_date    = $alterations['event_date'];
		$site_timezone = $alterations['options']['timezone_string'];
		$start_of_week = $alterations['options']['start_of_week'];

		$expected_days = [];
		$one_day       = Dates::interval( 'P1D' );
		$one_second    = Dates::interval( 'PT1S' );
		$grid_start    = Dates::build_date_object( Month::calculate_first_cell_date( $event_date ), $site_timezone );
		$grid_end      = Dates::build_date_object( Month::calculate_final_cell_date( $event_date ), $site_timezone );
		// compensate for the last day, else it will not be included in the period.
		$period = new \DatePeriod( $grid_start, $one_day, $grid_end->add( $one_second ) );

		foreach ( $period as $day ) {
			$expected_days[] = $day->format( Dates::DBDATEFORMAT );
		}

		// Sanity check.
		$this->assertEquals( $grid_start->format( Dates::DBDATEFORMAT ), reset( $expected_days ) );
		$this->assertEquals( $grid_end->format( Dates::DBDATEFORMAT ), end( $expected_days ) );
		$this->assertEquals( $start_of_week, $grid_start->format( 'w' ) );

		return $expected_days;
	}

	/**
	 * Parses the expected entry of the data provider to build an array of expectations that allow referring to the
	 * events by name, rather than by post ID.
	 *
	 * @param array<string,array> $expected An map of the expectations.
	 *
	 * @return array The expectations, in a `list` compatible format.
	 */
	protected function parse_expected_events( array $expected ) {
		$expected_events = array_combine(
			array_keys( $expected['events'] ),
			array_map( function ( array $event_names ) {
				$event_ids = [];
				foreach ( $event_names as $event_name ) {
					$event_ids[] = ( $this->events[ $event_name ] )->ID;
				}

				return $event_ids;
			}, $expected['events'] )
		);

		$expected_stack = array_combine(
			array_keys( $expected['stack'] ),
			array_map( function ( array $event_names ) {
				$event_ids = [];
				foreach ( $event_names as $event_name ) {
					// Take stack spacers into account.
					$event_ids[] = null !== $event_name ?
						( $this->events[ $event_name ] )->ID
						: null;
				}

				return $event_ids;
			}, $expected['stack'] )
		);

		return [ $expected_events, $expected_stack ];
	}
}
