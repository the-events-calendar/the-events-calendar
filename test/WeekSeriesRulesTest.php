<?php
define('ABSPATH', 1);
require_once('../../events/lib/tribe-date-utils.class.php');
require_once('../lib/tribe-date-series-rules.class.php');

class WeekSeriesRulesTest extends PHPUnit_Framework_TestCase
{
	const DATE_FORMAT = "Y-m-d";
	protected $date;

	protected function setUp() {
		$this->date = strtotime("2011-04-23"); // a saturday
	}

	public function testNextWeek()
	{
		$rules = new WeekSeriesRules();
		$nextDate = $rules->getNextDate($this->date);
		$this->assertEquals(date(self::DATE_FORMAT, $nextDate), "2011-04-30");
	}

	public function testEveryTwoWeeks()
	{
		$rules = new WeekSeriesRules(2);
		$nextDate = $rules->getNextDate($this->date);
		$this->assertEquals(date(self::DATE_FORMAT, $nextDate), "2011-05-07");
	}

	public function testEveryTwoMondays()
	{
		// find next monday
		$rules = new WeekSeriesRules(2, array(1));
		$nextDate = $rules->getNextDate($this->date);
		$this->assertEquals(date(self::DATE_FORMAT, $nextDate), "2011-05-02");
		$nextDate = $rules->getNextDate($nextDate);
		$this->assertEquals(date(self::DATE_FORMAT, $nextDate), "2011-05-16");
	}

	public function testEveryTwoThursdaysAndTuesdays() {
		$rules = new WeekSeriesRules(2, array(4,2));
		$nextDate = $rules->getNextDate($this->date);
		$this->assertEquals(date(self::DATE_FORMAT, $nextDate), "2011-05-03");
		$nextDate = $rules->getNextDate($nextDate);
		$this->assertEquals(date(self::DATE_FORMAT, $nextDate), "2011-05-05");
		$nextDate = $rules->getNextDate($nextDate);
		$this->assertEquals(date(self::DATE_FORMAT, $nextDate), "2011-05-17");
		$nextDate = $rules->getNextDate($nextDate);
		$this->assertEquals(date(self::DATE_FORMAT, $nextDate), "2011-05-19");
	}
}
?>
