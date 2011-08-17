<?php
require_once('../lib/date-series-rules.php');

class DaySeriesRulesTest extends PHPUnit_Framework_TestCase
{
	const DATE_FORMAT = "Y-m-d";
	protected $date;

	protected function setUp() {
		$this->date = strtotime("2011-04-23");
	}

	public function testNextDay()
	{
		$rules = new DaySeriesRules();
		$nextDate = $rules->getNextDate($this->date);
		$this->assertEquals(date(self::DATE_FORMAT, $nextDate), "2011-04-24");
	}

	 public function testMultipleDays()
	 {
		$rules = new DaySeriesRules(2);
		$nextDate = $rules->getNextDate($this->date);
		$this->assertEquals(date(self::DATE_FORMAT, $nextDate), "2011-04-25");
	 }
}
?>