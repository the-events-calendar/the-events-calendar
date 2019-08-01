<?php

namespace Tribe\Events\Views\V2\Views\HTML;

use Tribe\Test\Products\WPBrowser\Views\V2\HtmlTestCase;

class MonthTest extends HtmlTestCase {

	/**
	 * @test
	 */
	public function it_should_contain_correct_html_classes() {
		$this->given_month_data();
		$template = $this->template->template( 'month' );
		$html     = $this->document->html( $template );

		$this->assertEquals(
			$html->find( '.tribe-events-calendar-month' )->count(),
			1,
			'Month HTML needs to contain one ".tribe-events-calendar-month" element'
		);

		$this->assertTrue(
			$html->find( '.tribe-events-calendar-month' )->children()->is( '.tribe-events-calendar-month__body' ),
			'Month HTML needs to contain ".tribe-events-calendar-month__body" element'
		);
	}

	/**
	 * @test
	 */
	public function it_should_contain_a11y_attributes() {
		$this->given_month_data();
		$template   = $this->template->template( 'month' );
		$html       = $this->document->html( $template );
		$month      = $html->find( '.tribe-events-calendar-month' );
		$month_body = $month->find( '.tribe-events-calendar-month__body' );
		$week       = $month_body->find( '.tribe-events-calendar-month__week' );

		$this->assertTrue(
			$month->is( '[role="grid"]' ),
			'Month needs to be role="grid"'
		);

		$this->assertTrue(
			$month->is( '[aria-readonly="true"]' ),
			'Month needs to be aria-readonly="true"'
		);

		$this->assertTrue(
			$week->is( '[role="row"]' ),
			'Month week needs to be role="row"'
		);

	}

	protected function given_month_data() {
		$period = new \DatePeriod(
			new \DateTime( '2019-07-01 00:00:00' ),
			new \DateInterval( 'P1D' ),
			new \DateTime( '2019-08-04 23:59:00' )
		);
		$days   = [];
		/** @var \DateTime $date_object */
		foreach ( $period as $date_object ) {
			$day_date          = $date_object->format( 'Y-m-d' );
			$days[ $day_date ] = [
				'date'             => $day_date,
				'is_start_of_week' => 1 === $date_object->format( 'N' ),
				'year_number'      => (int) $date_object->format( 'Y' ),
				'month_number'     => (int) $date_object->format( 'm' ),
				'day_number'       => (int) $date_object->format( 'd' ),
				'events'           => [],
				'featured_events'  => [],
				'multiday_events'  => [],
				'found_events'     => 0,
				'more_events'      => 0,
			];
		}
		$this->template->set_values(
			array_merge(
				$this->template->get_global_values(),
				[
					'today_date'          => '2019-07-03',
					'grid_date'           => '2019-07-03',
					'formatted_grid_date' => '2019-07-03',
					'days'                => $days
				]
			),
			false
		);
	}

}
