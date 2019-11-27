<?php
/**
 * ${CARET}
 *
 * @since   TBD
 *
 * @package Tribe\Events\Views\V2\Repository
 */


namespace Tribe\Events\Views\V2\Repository;


use Codeception\TestCase\WPTestCase;
use Tribe\Events\Test\Factories\Event;

class Event_Period_Test extends WPTestCase {

	public function setUp() {
		parent::setUp();
		$this->factory()->event = new Event();
	}

	protected function create_for_date( $date ) {
		return static::factory()->event->create( [ 'when' => $date ] );
	}

	/**
	 * It should allow fetching events in a period
	 *
	 * @test
	 */
	public function should_allow_fetching_events_in_a_period() {
		$start_date = '2019-08-26';
		$end_date   = '2019-10-04';

		$before_period_events = array_map( [ $this, 'create_for_date' ], [ '2019-06-30', '2019-08-01' ] );
		$in_period_events     = array_map( [ $this, 'create_for_date' ], [ '2019-08-30', '2019-09-23' ] );
		$after_period_events  = array_map( [ $this, 'create_for_date' ], [ '2019-10-05', '2019-11-23' ] );

		$found_ids = tribe_events( 'period' )->where( 'in_period', $start_date, $end_date )->get_ids();

		$this->assertEquals( $in_period_events, $found_ids );
	}
}
