<?php
namespace Tribe\Events\Pro;

use Tribe__Events__Pro__Date_Series_Rules__Week as Week;
use Tribe__Events__Pro__Recurrence as Recurrence;
use Tribe__Events__Pro__Date_Series_Rules__Year as Year;

/**
 * Class TribeRecurrence_Test
 *
 * @group pro
 * @group recurrence
 */
class RecurrenceTest extends \Codeception\TestCase\WPTestCase {

	/*
	 * testEndsOnNotBeforeLastDate()
	 * This test creates a series rules and then makes sure that the event ends on the end date and not before it
	 */
	public function testEndsOnNotBeforeLastDate() {
		$start_date   = strtotime( '2011-09-06' );
		$end_date     = strtotime( '2011-12-06' );
		$series_rules = new Week( 1, array( 2 ) );

		$rec = new Recurrence( $start_date, $end_date, $series_rules );
		//put the dates in dates
		$dates = $rec->getDates();
		$end   = end( $dates );
		//Checks to see that the end date is in the array of the dates
		$this->assertTrue( in_array( $end_date, $end ) );
	}//ends testEndsOnNotBeforeLastDate

	public function test_max_date() {
		$recurrence = new Recurrence( strtotime( '2013-09-21' ), 8, new Year( 1 ), true, null );
		$this->assertCount( 8, $recurrence->getDates() );

		$recurrence->setMaxDate( strtotime( '2016-09-21' ) );
		$this->assertCount( 3, $recurrence->getDates() );

		$recurrence->setMaxDate( strtotime( '2016-09-20' ) );
		$this->assertCount( 2, $recurrence->getDates() );

		$recurrence = new Recurrence( strtotime( '2013-09-21' ),
			strtotime( '2017-09-21' ),
			new Year( 1 ),
			false,
			null );
		$this->assertCount( 4, $recurrence->getDates() );

		$recurrence->setMaxDate( strtotime( '2016-09-21' ) );
		$this->assertCount( 3, $recurrence->getDates() );

		$recurrence->setMaxDate( strtotime( '2016-09-20' ) );
		$this->assertCount( 2, $recurrence->getDates() );
	}

	public function test_min_date() {
		$recurrence = new Recurrence( strtotime( '2013-09-21' ), 8, new Year( 1 ), true, null );
		$this->assertCount( 8, $recurrence->getDates() );

		$recurrence->setMinDate( strtotime( '2015-09-20' ) );
		$this->assertCount( 7, $recurrence->getDates() );

		$recurrence->setMinDate( strtotime( '2015-09-21' ) );
		$this->assertCount( 7, $recurrence->getDates() );

		$recurrence->setMinDate( strtotime( '2015-09-22' ) );
		$this->assertCount( 6, $recurrence->getDates() );
	}

	public function test_constraint_flag() {
		$recurrence = new Recurrence( strtotime( '2013-09-21' ), 8, new Year( 1 ), true, null );
		$recurrence->getDates();
		$this->assertFalse( $recurrence->constrainedByMaxDate() );

		$recurrence->setMaxDate( strtotime( '2016-09-21' ) );
		$recurrence->getDates();
		$this->assertEquals( '2017-09-21', date( 'Y-m-d', $recurrence->constrainedByMaxDate() ) );

		$recurrence->setMaxDate( strtotime( '2028-09-20' ) );
		$recurrence->getDates();
		$this->assertFalse( $recurrence->constrainedByMaxDate() );
	}

	public function test_infinite_recurrence() {
		$recurrence = new Recurrence( strtotime( '2013-09-21' ), Recurrence::NO_END, new Year( 1 ), true, null );
		$dates      = $recurrence->getDates();
		$end_date   = end( $dates );
		$this->assertEquals( '2037-09-21',
			date( 'Y-m-d', $end_date['timestamp'] ) ); // OK, so that's not really infinite, is it?
		if ( PHP_INT_SIZE > 4 ) {
			$this->assertEquals( '2038-09-21', date( 'Y-m-d', $recurrence->constrainedByMaxDate() ) );
		} else {
			$this->assertFalse( $recurrence->constrainedByMaxDate() );
		}

		$recurrence->setMaxDate( strtotime( '2028-09-21' ) );
		$dates    = $recurrence->getDates();
		$end_date = end( $dates );

		$this->assertEquals( '2028-09-21', date( 'Y-m-d', $end_date['timestamp'] ) );
	}

	/**
	 * will yield no recurrences if series rules is WP_Error instance
	 */
	public function test_will_yield_no_recurrences_if_series_rules_is_wp_error_instance() {
		$recurrence = new Recurrence( strtotime( '2013-09-21' ),
			Recurrence::NO_END,
			new \WP_Error( 'bad-type', 'Bad Type' ),
			true,
			null );

		$dates = $recurrence->getDates();

		$this->assertEquals( [ ], $dates );
	}
}
