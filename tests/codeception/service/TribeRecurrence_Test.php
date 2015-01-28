<?php

/**
 * Class TribeRecurrence_Test
 *
 * @group pro
 * @group recurrence
 */
class TribeRecurrence_Test extends WP_UnitTestCase {

	public function testEndsOnNotBeforeLastDate()	{
		$start_date = strtotime("2011-09-06");
		$end_date = strtotime("2011-12-06");
		$series_rules = new Tribe__Events__Pro__Date_Series_Rules__Week(1, array(2));

		$rec = new Tribe__Events__Pro__Recurrence($start_date, $end_date, $series_rules);
		$this->assertTrue(in_array($end_date, $rec->getDates()));
	}

	public function test_max_date() {
		$recurrence = new Tribe__Events__Pro__Recurrence( strtotime('2013-09-21'), 8, new Tribe__Events__Pro__Date_Series_Rules__Year(1), TRUE, NULL );
		$this->assertCount(8, $recurrence->getDates());

		$recurrence->setMaxDate(strtotime('2016-09-21'));
		$this->assertCount(3, $recurrence->getDates());

		$recurrence->setMaxDate(strtotime('2016-09-20'));
		$this->assertCount( 2, $recurrence->getDates() );

		$recurrence = new Tribe__Events__Pro__Recurrence( strtotime('2013-09-21'), strtotime('2017-09-21'), new Tribe__Events__Pro__Date_Series_Rules__Year(1), FALSE, NULL );
		$this->assertCount(4, $recurrence->getDates());

		$recurrence->setMaxDate(strtotime('2016-09-21'));
		$this->assertCount(3, $recurrence->getDates());

		$recurrence->setMaxDate(strtotime('2016-09-20'));
		$this->assertCount( 2, $recurrence->getDates() );
	}

	public function test_min_date() {
		$recurrence = new Tribe__Events__Pro__Recurrence( strtotime('2013-09-21'), 8, new Tribe__Events__Pro__Date_Series_Rules__Year(1), TRUE, NULL );
		$this->assertCount(8, $recurrence->getDates());

		$recurrence->setMinDate(strtotime('2015-09-20'));
		$this->assertCount( 7, $recurrence->getDates() );

		$recurrence->setMinDate(strtotime('2015-09-21'));
		$this->assertCount(7, $recurrence->getDates());

		$recurrence->setMinDate(strtotime('2015-09-22'));
		$this->assertCount( 6, $recurrence->getDates() );
	}

	public function test_constraint_flag() {
		$recurrence = new Tribe__Events__Pro__Recurrence( strtotime('2013-09-21'), 8, new Tribe__Events__Pro__Date_Series_Rules__Year(1), TRUE, NULL );
		$recurrence->getDates();
		$this->assertFalse($recurrence->constrainedByMaxDate());

		$recurrence->setMaxDate(strtotime('2016-09-21'));
		$recurrence->getDates();
		$this->assertEquals('2017-09-21', date('Y-m-d', $recurrence->constrainedByMaxDate()));

		$recurrence->setMaxDate(strtotime('2028-09-20'));
		$recurrence->getDates();
		$this->assertFalse($recurrence->constrainedByMaxDate());
	}

	public function test_infinite_recurrence() {
		$recurrence = new Tribe__Events__Pro__Recurrence( strtotime('2013-09-21'), Tribe__Events__Pro__Recurrence::NO_END, new Tribe__Events__Pro__Date_Series_Rules__Year(1), TRUE, NULL );
		$dates = $recurrence->getDates();
		$this->assertEquals('2037-09-21', date('Y-m-d', end($dates))); // OK, so that's not really infinite, is it?
		if ( PHP_INT_SIZE > 4 ) {
			$this->assertEquals('2038-09-21', date('Y-m-d', $recurrence->constrainedByMaxDate()));
		} else {
			$this->assertFalse($recurrence->constrainedByMaxDate());
		}

		$recurrence->setMaxDate(strtotime('2028-09-21'));
		$dates = $recurrence->getDates();
		$this->assertEquals('2028-09-21', date('Y-m-d', end($dates)));
	}
}
