<?php

namespace Tribe\Events\Views\V2\Partials\Month;

use tad\FunctionMocker\FunctionMocker as Test;
use Tribe\Test\Products\WPBrowser\Views\V2\HtmlPartialTestCase;

class Calendar_BodyTest extends HtmlPartialTestCase
{

	protected $partial_path = 'month/calendar-body';

	/**
	 * Test static render
	 */
	public function test_static_render() {
		$this->given_month_data();
		$this->assertMatchesSnapshot( $this->get_partial_html() );
	}

	public function setUp() {
		parent::setUp();
		// Start Function Mocker.
		Test::setUp();
		// Always return the same value when creating nonces.
		Test::replace( 'wp_create_nonce', '2ab7cc6b39' );
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
