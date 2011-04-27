<?php
require_once('../lib/date-series-rules.php');

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
	}

	public function testNextMonthEndOfMonth() {
		$this->date = strtotime("2011-01-31");
		$rules = new MonthSeriesRules();
		$nextDate = $rules->getNextDate($this->date);
		$this->assertEquals(date(self::DATE_FORMAT, $nextDate), "2011-03-31");
	}
}
?>