<?php

/**
 * Class DaySeriesRules_Test
 *
 * @group pro
 * @group recurrence
 */
class DaySeriesRules_Test extends Tribe__Events__WP_UnitTestCase
{
	const DATE_FORMAT = "Y-m-d";
	protected $date;

	public function setUp() {
		$this->date = strtotime("2011-04-23");
		parent::setUp();
	}

	public function testNextDay()
	{
		$rules = new Tribe__Events__Pro__Date_Series_Rules__Day();
		$nextDate = $rules->getNextDate($this->date);
		$this->assertEquals(date(self::DATE_FORMAT, $nextDate), "2011-04-24");
	}

	 public function testMultipleDays()
	 {
		$rules = new Tribe__Events__Pro__Date_Series_Rules__Day(2);
		$nextDate = $rules->getNextDate($this->date);
		$this->assertEquals(date(self::DATE_FORMAT, $nextDate), "2011-04-25");
	 }
}
?>