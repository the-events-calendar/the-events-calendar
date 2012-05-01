<?php
define('ABSPATH', 1);
require_once('../../events/lib/tribe-date-utils.class.php');
require_once('../lib/tribe-date-series-rules.class.php');
require_once('../lib/tribe-recurrence.class.php');

class RecurrenceTest extends PHPUnit_Framework_TestCase
{
	const DATE_FORMAT = "Y-m-d";

	public function testEndsOnNotBeforeLastDate()
	{
      $start_date = strtotime("2011-09-06");
      $end_date = strtotime("2011-12-06");
      $series_rules = new WeekSeriesRules(1, array(2));

      $rec = new TribeRecurrence($start_date, $end_date, $series_rules);
      $this->assertTrue(in_array($end_date, $rec->getDates()));
   }
}

