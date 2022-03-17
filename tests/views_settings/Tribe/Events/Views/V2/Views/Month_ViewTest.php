<?php

namespace Tribe\Events\Views\V2\Views;

use Codeception\TestCase\WPTestCase;
use Tribe\Events\Views\V2\View;
use Tribe\Test\Products\Traits\With_Ascii_Calendar;
use Tribe\Test\Products\Traits\With_Json_Data_Sets;
use Tribe\Test\Products\Traits\With_View_Context;
use Tribe__Date_Utils as Dates;
use Tribe__Events__Template__Month as Month;

class Month_ViewTest extends WPTestCase {
	use With_View_Context;
	use With_Json_Data_Sets;
	use With_Ascii_Calendar;

	/**
	 * Builds the set of days we expect to see in the View results.
	 *
	 * @param array<string,mixed> $alterations The alterations to build the expected days from.
	 *
	 * @return array<string> An array of days, w/o gaps, each in the `Y-m-d` format.
	 * @throws \Exception If a missing alteration key is missing.
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
	 * It should return the expected events per day
	 *
	 * The purpose of this test is to make sure that, depending on diff. combinations of TEC settings, the View
	 * will return the events we expect in each day.
	 *
	 * @test
	 *
	 * @dataProvider json_data_sets
	 *
	 * @param       $legend
	 * @param array $alterations
	 * @param array $expected
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
		$stack_ids_per_day = array_filter(
			array_combine(
				array_keys( $template_vars['days'] ),
				array_map(
					static function ( $day ) {
						$day = array_filter( $day );
						// We need to handle null/empty values so wp_list_pluck doesn't choke on them.
						$new_day =  ( empty( $day ) || ! is_array( $day ) ? [] : empty( wp_list_pluck( $day, 'ID' ) ) ) ? [] : wp_list_pluck( $day, 'ID' );
						return array_filter( $new_day );
					},
					array_column( $template_vars['days'], 'multiday_events' )
				)
			)
		);


		$expected_days = $this->build_expected_days( $alterations );
		list( $expected_event_ids_per_day, $expected_stack_per_day ) = $this->parse_expected_events( $expected );

		// Ensure we remove the null/empty values so we match what's in $stack_ids_per_day.
		$expected_stack_per_day = array_map(
			'array_filter',
			$expected_stack_per_day
		);

		$expected = $this->render_ascii_calendar(
			reset( $expected_days ), end( $expected_days ), $expected_event_ids_per_day, 7
		);
		$actual   = $this->render_ascii_calendar(
			reset( $expected_days ), end( $expected_days ), $event_ids_per_day, 7
		);

		$this->assertEquals( $expected, $actual, $this->build_event_legend() );

		$this->assertEquals( $expected_days, $the_days, 'The list of days does not match.' );
		$this->assertEquals( $expected_event_ids_per_day, $event_ids_per_day, 'The events IDs per day do not match.' );
		$this->assertEquals( $expected_stack_per_day, $stack_ids_per_day, 'The stack IDs per day do not match.' );
	}
}
