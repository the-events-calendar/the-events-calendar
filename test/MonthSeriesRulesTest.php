<?php
define('ABSPATH', 1);
require_once('../../events/lib/tribe-date-utils.class.php');
require_once('../lib/tribe-date-series-rules.class.php');

class MonthSeriesRulesTest extends PHPUnit_Framework_TestCase
{
	const DATE_FORMAT = "Y-m-d";
	protected $date;

	protected function setUp() {
		$this->date = strtotime("2011-04-23");
	}

	public function testNextMonth()
	{
		$rules = new MonthSeriesRules();
		$nextDate = $rules->getNextDate($this->date);
		$this->assertEquals(date(self::DATE_FORMAT, $nextDate), "2011-05-23");
		$nextDate = $rules->getNextDate($nextDate);
		$this->assertEquals(date(self::DATE_FORMAT, $nextDate), "2011-06-23");
		$nextDate = $rules->getNextDate($nextDate);
		$this->assertEquals(date(self::DATE_FORMAT, $nextDate), "2011-07-23");
	}

   public function testStartsOnNextMonth() {
		$this->date = strtotime("2011-10-27");
		$rules = new MonthSeriesRules(1, array(), 3, 4);
		$nextDate = $rules->getNextDate($this->date);
		$this->assertEquals(date(self::DATE_FORMAT, $nextDate), "2011-11-17");
   }

	public function testEvery3Months()
	{
		$rules = new MonthSeriesRules(3);
		$nextDate = $rules->getNextDate($this->date);
		$this->assertEquals(date(self::DATE_FORMAT, $nextDate), "2011-07-23");
	}

	public function testNextMonthEndOfMonth() {
		$this->date = strtotime("2011-01-31");
		$rules = new MonthSeriesRules();
		$nextDate = $rules->getNextDate($this->date);
		$this->assertEquals(date(self::DATE_FORMAT, $nextDate), "2011-03-31");
	}

	public function testDaysOfMonth() {
		$rules = new MonthSeriesRules(1, array(1,3,29));
		$nextDate = $rules->getNextDate($this->date);
		$this->assertEquals(date(self::DATE_FORMAT, $nextDate), "2011-04-29");
		$nextDate = $rules->getNextDate($nextDate);
		$this->assertEquals(date(self::DATE_FORMAT, $nextDate), "2011-05-01");
	}

	public function testDaysOfMonthWithSkip() {
		$this->date = strtotime("2011-01-31");
		$rules = new MonthSeriesRules(1, array(27, 31));
		$nextDate = $rules->getNextDate($this->date);
		$this->assertEquals(date(self::DATE_FORMAT, $nextDate), "2011-02-27");
		$nextDate = $rules->getNextDate($nextDate);
		$this->assertEquals(date(self::DATE_FORMAT, $nextDate), "2011-03-27");
	}

	public function testDaysOfMultiMonthWithSkip() {
		$this->date = strtotime("2010-12-31");
		$rules = new MonthSeriesRules(2, array(27, 31));
		$nextDate = $rules->getNextDate($this->date);
		$this->assertEquals(date(self::DATE_FORMAT, $nextDate), "2011-02-27");
		$nextDate = $rules->getNextDate($nextDate);
		$this->assertEquals(date(self::DATE_FORMAT, $nextDate), "2011-04-27");
	}

	public function testDaysOfMultiMonthWithSkip2() {
		$this->date = strtotime("2010-12-31");
		$rules = new MonthSeriesRules(2, array(29, 31));
		$nextDate = $rules->getNextDate($this->date);
		$this->assertEquals(date(self::DATE_FORMAT, $nextDate), "2011-04-29");
		$nextDate = $rules->getNextDate($nextDate);
		$this->assertEquals(date(self::DATE_FORMAT, $nextDate), "2011-06-29");
	}

	public function testDayOfWeek() {
		// 4th wednesday
		$rules = new MonthSeriesRules(1, array(), 4, 3);
		$nextDate = $rules->getNextDate($this->date);
		$this->assertEquals(date(self::DATE_FORMAT, $nextDate), "2011-04-27");
		$nextDate = $rules->getNextDate($nextDate);
		$this->assertEquals(date(self::DATE_FORMAT, $nextDate), "2011-05-25");
	}

	public function testLastDayOfWeek() {
		// 4th wednesday
		$rules = new MonthSeriesRules(1, array(), -1, 3);
		$nextDate = $rules->getNextDate($this->date);
		$this->assertEquals(date(self::DATE_FORMAT, $nextDate), "2011-04-27");
		$nextDate = $rules->getNextDate($nextDate);
		$this->assertEquals(date(self::DATE_FORMAT, $nextDate), "2011-05-25");
	}
}
?>
