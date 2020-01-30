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
		yield 'set_one' => [
			[
				'event_date' => '2020-01',
				'options'    => [
					'timezone_string' => 'America/Los_Angeles',
					'start_of_week'   => '0',
				],
				'tribe_options' => [
					'multiDayCutoff' => '00:00',
				],
				'events'     => [
					'first' => [
						'title'      => 'On 2020-01-01, 8am to 5pm',
						'start_date' => '2020-01-01 09:00:00',
						'duration'   => 4 * HOUR_IN_SECONDS,
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
	public function should_return_the_expected_events_per_day( array $alterations = [] ) {
		$context = $this->setup_context( $alterations );

		/** @var Month_View $view */
		$view          = View::make( Month_View::class, $context );
		$template_vars = $view->get_template_vars();

		$this->assertArrayHasKey( 'events', $template_vars );
		$this->assertArrayHasKey( 'days', $template_vars );

		$expected_days = $this->build_expected_days( $alterations );

		$this->assertEquals( $expected_days, array_keys( $template_vars['events'] ) );
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
}
