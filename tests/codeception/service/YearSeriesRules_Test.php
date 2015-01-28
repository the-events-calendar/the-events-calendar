<?php

/**
 * Class YearSeriesRules_Test
 *
 * @group pro
 * @group recurrence
 */
class YearSeriesRules_Test extends WP_UnitTestCase
{
	const DATE_FORMAT = "Y-m-d";
	protected $date;

	public function setUp() {
		$this->date = strtotime("2011-04-23");
		parent::setUp();
	}

	public function testNextYear()
	{
		$rules = new Tribe__Events__Pro__Date_Series_Rules__Year();
		$nextDate = $rules->getNextDate($this->date);
		$this->assertEquals(date(self::DATE_FORMAT, $nextDate), "2012-04-23");
	}

	public function testEveryTwoYears()
	{
		$rules = new Tribe__Events__Pro__Date_Series_Rules__Year(2);
		$nextDate = $rules->getNextDate($this->date);
		$this->assertEquals(date(self::DATE_FORMAT, $nextDate), "2013-04-23");
	}

	public function testEveryTwoYearsMultipleMonths()
	{
		$rules = new Tribe__Events__Pro__Date_Series_Rules__Year(2, array(2,12));
		$nextDate = $rules->getNextDate($this->date);
		$this->assertEquals(date(self::DATE_FORMAT, $nextDate), "2011-12-23");
		$nextDate = $rules->getNextDate($nextDate);
		$this->assertEquals(date(self::DATE_FORMAT, $nextDate), "2013-02-23");
	}

	public function testEveryTwoYearsMultipleMonthsWithSkip()
	{
		$this->date = strtotime("2011-04-29");
		$rules = new Tribe__Events__Pro__Date_Series_Rules__Year(2, array(2,12));
		$nextDate = $rules->getNextDate($this->date);
		$this->assertEquals(date(self::DATE_FORMAT, $nextDate), "2011-12-29");
		$nextDate = $rules->getNextDate($nextDate);
		$this->assertEquals(date(self::DATE_FORMAT, $nextDate), "2013-12-29");
	}

	public function testSecondWednesdayOfJune()
	{
		$rules = new Tribe__Events__Pro__Date_Series_Rules__Year(2, array(6,12), 2, 3);
		$nextDate = $rules->getNextDate($this->date);
		$this->assertEquals(date(self::DATE_FORMAT, $nextDate), "2011-06-08");
		$nextDate = $rules->getNextDate($nextDate);
		$this->assertEquals(date(self::DATE_FORMAT, $nextDate), "2011-12-14");
	}

	public function testLastWednesdayOfJune()
	{
		$rules = new Tribe__Events__Pro__Date_Series_Rules__Year(2, array(6,12), -1, 3);
		$nextDate = $rules->getNextDate($this->date);
		$this->assertEquals(date(self::DATE_FORMAT, $nextDate), "2011-06-29");
		$nextDate = $rules->getNextDate($nextDate);
		$this->assertEquals(date(self::DATE_FORMAT, $nextDate), "2011-12-28");
	}
}
?>