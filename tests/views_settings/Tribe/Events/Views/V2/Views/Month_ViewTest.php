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
		$stack_ids_per_day = array_filter( array_combine(
			array_keys( $template_vars['days'] ),
			array_map( static function ( $day ) {
				return count( $day ) ? wp_list_pluck( $day, 'ID' ) : [];
			}, array_column( $template_vars['days'], 'multiday_events' ) )
		) );

		$expected_days = $this->build_expected_days( $alterations );
		list( $expected_event_ids_per_day, $expected_stack_per_day ) = $this->parse_expected_events( $expected );

		$expected = $this->render_ascii_calendar(
			reset( $expected_days ), end( $expected_days ), $expected_event_ids_per_day, 7
		);
		$actual = $this->render_ascii_calendar(
			reset( $expected_days ), end( $expected_days ), $event_ids_per_day, 5
		);

		$this->assertEquals( $expected, $actual, $this->build_event_legend() );

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

	/**
	 * Renders an ASCII calendar.
	 *
	 * @todo move this to the Trait.
	 *
	 * @param string|\DateTimeInterface|int $start_day     The start date.
	 * @param string|\DateTimeInterface|int $end_day       The end date.
	 * @param array<string,array> $events_by_day           A complete, including all days, or partial (w/ gaps), list
	 *                                                     of each day expected event post IDs.
	 * @param int                           $week_size     The size of the week to format the calendar to.
	 *
	 * @return string The ASCII representation of the calendar.
	 */
	protected function render_ascii_calendar( $start_day, $end_day, array $events_by_day = [], $week_size = 7 ) {
		$pad = static function ( $input ) {
			return str_pad( $input, 5, ' ', STR_PAD_BOTH );
		};
		$all_days = [];
		$header_row = [];
		$week_rows = [];
		$one_day = Dates::interval( 'P1D' );
		$period = new \DatePeriod(
			Dates::build_date_object( $start_day ),
			$one_day,
			Dates::build_date_object( $end_day )->add( $one_day )
		);
		foreach ( $period as $day ) {
			$all_days[ $day->format( 'Y-m-d' ) ] = [];
		}
		$events_by_day = array_merge( $all_days, $events_by_day );
		foreach ( array_chunk( $events_by_day, $week_size, true ) as $week ) {
			$week_key               = array_keys( $week )[0];
			$week_rows[ $week_key ] = [];

			foreach ( $week as $day_date => $events ) {
				// 3-letter representation.
				$date_time = Dates::build_date_object( $day_date );
				$day_name  = $date_time->format( 'D' );
				$day_num   = $date_time->format('d');

				if ( count( $header_row ) < $week_size ) {
					$header_row[] = $day_name;
				}
				$week_rows[ $week_key ][ $day_num ] = $events_by_day[ $day_date ];
			}
		}

		$weeks = implode( "\n", array_map( static function ( $week_row ) use($pad) {
			$week_height = max( ...array_values( array_map( 'count', $week_row ) ) );
			$result      = [];
			$i           = 0;
			do {
				$array_map = array_map( static function ( $week_day ) use ( $i ) {
					return isset( $week_day[ $i ] ) ? $week_day[ $i ] : ' ';
				}, $week_row );
				$i++;
				$pieces    = array_map( $pad, $array_map );
				$result[]  = implode( '|', $pieces );
				$week_height --;
			} while ( $week_height > 0 );

			$result_header = str_repeat( '______', count( $week_row ) );
			$result_header .= "\n" . implode( '|', array_map( $pad, array_keys( $week_row ) ) );
			$result_header .= "\n" . str_repeat( '------', count( $week_row ) );

			return $result_header . "\n" . implode( "\n", $result );
		}, $week_rows ) );


		$str = implode( ' | ', $header_row ) . "|\n" . $weeks;

		return $str;
	}

	/**
	 * Builds a "legend" of each event, by post ID.
	 *
	 * @todo move this to the Trait.
	 *
	 * The legend should be used to provide more information during debug of failures.
	 *
	 * @return string The event legend.
	 */
	protected function build_event_legend(){
		return "Event legend:\n\n" . implode( "\n", array_map( static function ( $event ) {
			return sprintf(
				'%d - tz: %s; start: %s; end: %s; all-day: %s',
				$event->ID,
				get_post_meta( $event->ID, '_EventTimezone', true ),
				get_post_meta( $event->ID, '_EventStartDate', true ),
				get_post_meta( $event->ID, '_EventEndDate', true ),
				get_post_meta( $event->ID, '_EventAllDay', true ) ? 'yes' : 'no'
			);
		}, $this->events ) ) . "\n";
	}
}
