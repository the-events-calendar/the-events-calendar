<?php

interface DateSeriesRules
{
	 const DATE_FORMAT = "Y-m-d H:i:s";
    public function getNextDate($curdate);
}

class DaySeriesRules implements DateSeriesRules
{
	private $days_between;

	public function __construct($days_between = 1) {
		$this->days_between = $days_between;
	}

	public function getNextDate($curdate) {
		return strtotime(date(DateSeriesRules::DATE_FORMAT, $curdate) . " + " . $this->days_between . " days");
	}
}

class WeekSeriesRules implements DateSeriesRules
{
	private $weeks_between;
	private $days;

	public function __construct($weeks_between = 1, $days = array()) {
		$this->weeks_between = $weeks_between;
		$this->days = $days; // days are integers representing days
		sort($this->days);
	}

	public function getNextDate($curdate) {
		$nextdate = $curdate;

		if ( sizeof($this->days) > 0 ) {
			// get current day of week
			$curDayOfWeek = date("N", $curdate);

			// find the selected day that is equal or higher to the current day
			$nextDayOfWeek = $this->getNextDayOfWeek($curDayOfWeek);

			while( date("N", $nextdate) != $nextDayOfWeek ) {
				$nextdate = strtotime(date(DateSeriesRules::DATE_FORMAT, $nextdate) . " + 1 day");
			}

			if($nextDayOfWeek > $curDayOfWeek) {
				return strtotime(date(DateSeriesRules::DATE_FORMAT, $nextdate));
			} else if($nextDayOfWeek < $curDayOfWeek) {
				return strtotime(date(DateSeriesRules::DATE_FORMAT, $nextdate) . " + " . ( $this->weeks_between - 1 ) . " weeks");
			}
		}

		return strtotime(date(DateSeriesRules::DATE_FORMAT, $nextdate) . " + " . $this->weeks_between . " weeks");
	}

	private function getNextDayOfWeek($curDayOfWeek) {
		foreach($this->days as $day) {
			if ($day > $curDayOfWeek)
				return $day;
		}

		return $this->days[0];
	}
}

class MonthSeriesRules implements DateSeriesRules
{
	private $months_between;
	private $days_of_month;
	private $week_of_month;
	private $day_of_week;

	public function __construct($months_between = 1, $days_of_month = array(), $week_of_month = null, $day_of_week = null) {
		$this->months_between = $months_between;
		$this->days_of_month = $days_of_month;
		$this->week_of_month = $week_of_month;
		$this->day_of_week = $day_of_week;
		sort($this->days_of_month);
	}

	public function getNextDate($curdate) {
		$nextdate = $curdate;
		
		return strtotime(date(DateSeriesRules::DATE_FORMAT, $nextdate) . " + " . $this->months_between . " months");
	}
}
?>