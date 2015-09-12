<?php

/**
 * Class MonthSeriesRules_Test
 *
 * @group pro
 * @group recurrence
 */
class MonthSeriesRules_Test extends Tribe__Events__WP_UnitTestCase
{
	const DATE_FORMAT = "Y-m-d";
	protected $date;

	public function setUp() {
		$this->date = strtotime("2011-04-23");
		parent::setUp();
	}

	public function testNextMonth()
	{
		$rules = new Tribe__Events__Pro__Date_Series_Rules__Month();
		$nextDate = $rules->getNextDate($this->date);
		$this->assertEquals(date(self::DATE_FORMAT, $nextDate), "2011-05-23");
		$nextDate = $rules->getNextDate($nextDate);
		$this->assertEquals(date(self::DATE_FORMAT, $nextDate), "2011-06-23");
		$nextDate = $rules->getNextDate($nextDate);
		$this->assertEquals(date(self::DATE_FORMAT, $nextDate), "2011-07-23");
	}

   public function testStartsOnNextMonth() {
		$this->date = strtotime("2011-10-27");
		$rules = new Tribe__Events__Pro__Date_Series_Rules__Month(1, array(), 3, 4);
		$nextDate = $rules->getNextDate($this->date);
		$this->assertEquals(date(self::DATE_FORMAT, $nextDate), "2011-11-17");
   }

	public function testEvery3Months()
	{
		$rules = new Tribe__Events__Pro__Date_Series_Rules__Month(3);
		$nextDate = $rules->getNextDate($this->date);
		$this->assertEquals(date(self::DATE_FORMAT, $nextDate), "2011-07-23");
	}

	public function testNextMonthEndOfMonth() {
		$this->date = strtotime("2011-01-31");
		$rules = new Tribe__Events__Pro__Date_Series_Rules__Month();
		$nextDate = $rules->getNextDate($this->date);
		$this->assertEquals(date(self::DATE_FORMAT, $nextDate), "2011-03-31");
	}

	public function testDaysOfMonth() {
		$rules = new Tribe__Events__Pro__Date_Series_Rules__Month(1, array(1,3,29));
		$nextDate = $rules->getNextDate($this->date);
		$this->assertEquals(date(self::DATE_FORMAT, $nextDate), "2011-04-29");
		$nextDate = $rules->getNextDate($nextDate);
		$this->assertEquals(date(self::DATE_FORMAT, $nextDate), "2011-05-01");
	}

	public function testDaysOfMonthWithSkip() {
		$this->date = strtotime("2011-01-31");
		$rules = new Tribe__Events__Pro__Date_Series_Rules__Month(1, array(27, 31));
		$nextDate = $rules->getNextDate($this->date);
		$this->assertEquals(date(self::DATE_FORMAT, $nextDate), "2011-02-27");
		$nextDate = $rules->getNextDate($nextDate);
		$this->assertEquals(date(self::DATE_FORMAT, $nextDate), "2011-03-27");
	}

	public function testDaysOfMultiMonthWithSkip() {
		$this->date = strtotime("2010-12-31");
		$rules = new Tribe__Events__Pro__Date_Series_Rules__Month(2, array(27, 31));
		$nextDate = $rules->getNextDate($this->date);
		$this->assertEquals(date(self::DATE_FORMAT, $nextDate), "2011-02-27");
		$nextDate = $rules->getNextDate($nextDate);
		$this->assertEquals(date(self::DATE_FORMAT, $nextDate), "2011-04-27");
	}

	public function testDaysOfMultiMonthWithSkip2() {
		$this->date = strtotime("2010-12-31");
		$rules = new Tribe__Events__Pro__Date_Series_Rules__Month(2, array(29, 31));
		$nextDate = $rules->getNextDate($this->date);
		$this->assertEquals(date(self::DATE_FORMAT, $nextDate), "2011-04-29");
		$nextDate = $rules->getNextDate($nextDate);
		$this->assertEquals(date(self::DATE_FORMAT, $nextDate), "2011-06-29");
	}

	public function testDayOfWeek() {
		// 4th wednesday
		$rules = new Tribe__Events__Pro__Date_Series_Rules__Month(1, array(), 4, 3);
		$nextDate = $rules->getNextDate($this->date);
		$this->assertEquals(date(self::DATE_FORMAT, $nextDate), "2011-04-27");
		$nextDate = $rules->getNextDate($nextDate);
		$this->assertEquals(date(self::DATE_FORMAT, $nextDate), "2011-05-25");
	}

	public function testLastDayOfWeek() {
		// 4th wednesday
		$rules = new Tribe__Events__Pro__Date_Series_Rules__Month(1, array(), -1, 3);
		$nextDate = $rules->getNextDate($this->date);
		$this->assertEquals(date(self::DATE_FORMAT, $nextDate), "2011-04-27");
		$nextDate = $rules->getNextDate($nextDate);
		$this->assertEquals(date(self::DATE_FORMAT, $nextDate), "2011-05-25");
	}
}
?>
