<?php
require_once('../lib/date-series-rules.php');

class YearSeriesRulesTest extends PHPUnit_Framework_TestCase
{
	const DATE_FORMAT = "Y-m-d";
	protected $date;

	protected function setUp() {
		$this->date = strtotime("2011-04-23");
	}

	public function testNextYear()
	{
		$rules = new YearSeriesRules();
		$nextDate = $rules->getNextDate($this->date);
		$this->assertEquals(date(self::DATE_FORMAT, $nextDate), "2012-04-23");
	}

	public function testEveryTwoYears()
	{
		$rules = new YearSeriesRules(2);
		$nextDate = $rules->getNextDate($this->date);
		$this->assertEquals(date(self::DATE_FORMAT, $nextDate), "2013-04-23");
	}

	public function testEveryTwoYearsMultipleMonths()
	{
		$rules = new YearSeriesRules(2, array(2,12));
		$nextDate = $rules->getNextDate($this->date);
		$this->assertEquals(date(self::DATE_FORMAT, $nextDate), "2011-12-23");
		$nextDate = $rules->getNextDate($nextDate);
		$this->assertEquals(date(self::DATE_FORMAT, $nextDate), "2013-02-23");
	}

	public function testEveryTwoYearsMultipleMonthsWithSkip()
	{
		$this->date = strtotime("2011-04-29");
		$rules = new YearSeriesRules(2, array(2,12));
		$nextDate = $rules->getNextDate($this->date);
		$this->assertEquals(date(self::DATE_FORMAT, $nextDate), "2011-12-29");
		$nextDate = $rules->getNextDate($nextDate);
		$this->assertEquals(date(self::DATE_FORMAT, $nextDate), "2013-12-29");
	}

	public function testSecondWednesdayOfJune()
	{
		$rules = new YearSeriesRules(2, array(6,12), 2, 3);
		$nextDate = $rules->getNextDate($this->date);
		$this->assertEquals(date(self::DATE_FORMAT, $nextDate), "2011-06-08");
		$nextDate = $rules->getNextDate($nextDate);
		$this->assertEquals(date(self::DATE_FORMAT, $nextDate), "2011-12-14");
	}

	public function testLastWednesdayOfJune()
	{
		$rules = new YearSeriesRules(2, array(6,12), -1, 3);
		$nextDate = $rules->getNextDate($this->date);
		$this->assertEquals(date(self::DATE_FORMAT, $nextDate), "2011-06-29");
		$nextDate = $rules->getNextDate($nextDate);
		$this->assertEquals(date(self::DATE_FORMAT, $nextDate), "2011-12-28");
	}
}
?>