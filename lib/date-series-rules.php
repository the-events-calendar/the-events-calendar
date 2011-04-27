<?php
interface DateSeriesRules
{
	 const DATE_ONLY_FORMAT = "Y-m-d";
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
		$next_day_of_month = date('j', $curdate);

		if($this->week_of_month && $this->day_of_week) {
			return $this->getNthDayOfWeek($curdate, $this->day_of_week, $this->week_of_month);
		}
		else // normal date based recurrence
		{
			if(sizeof($this->days_of_month) > 0) {
				$next_day_of_month = $this->getNextDayOfMonth($next_day_of_month);

				while($this->getLastDayOfMonth($curdate) < $next_day_of_month)
				{
					$next_day_of_month = $this->days_of_month[0];
					$curdate = mktime(date("H", $curdate), date("i", $curdate), date("s", $curdate), date('n', $curdate) + $this->months_between, 1, date('Y', $curdate));
				}
			}

			if($next_day_of_month > date('j', $curdate)) {
				// no need to jump ahead stay in current month
				return mktime(date("H", $curdate), date("i", $curdate), date("s", $curdate), date('n', $curdate), $next_day_of_month, date('Y', $curdate));
			} else {
				$nextdate = mktime (0, 0, 0, date('n', $curdate) + $this->months_between, 1, date('Y', $curdate));

				while($this->getLastDayOfMonth($nextdate) < $next_day_of_month) {
					$nextdate = mktime (0, 0, 0, date('n', $nextdate) + $this->months_between, 1, date('Y', $nextdate));
				}

				return mktime(date("H", $curdate), date("i", $curdate), date("s", $curdate), date('n', $nextdate), $next_day_of_month, date('Y', $nextdate));
			}
		}
	}

	private function getNthDayOfWeek($curdate, $day_of_week, $week_of_month) {
		$curmonth = date('n', $curdate);

		if($week_of_month == -1) { // LAST WEEK
			$nextdate = $this->getLastDayOfWeek($curdate, $day_of_week);

			if($curdate == $nextdate) {
				$curdate = mktime (0, 0, 0, date('n', $curdate) + $this->months_between, 1, date('Y', $curdate));
				$nextdate = $this->getLastDayOfWeek($curdate, $day_of_week);
			}

			return $nextdate;
		} else {
			$nextdate = $this->getFirstDayOfWeek($curdate, $day_of_week);
			$maybe_date = strtotime(date(DateSeriesRules::DATE_FORMAT, $nextdate) . " + " . ($week_of_month-1) . " weeks");

			// if on the correct date, then try next month
			if(date(DateSeriesRules::DATE_ONLY_FORMAT, $maybe_date) == date(DateSeriesRules::DATE_ONLY_FORMAT, $curdate)) {
				$curdate = mktime (0, 0, 0, date('n', $curdate) + $this->months_between, 1, date('Y', $curdate));
				$nextdate = $this->getFirstDayOfWeek($curdate, $day_of_week);
				$maybe_date = strtotime(date(DateSeriesRules::DATE_FORMAT, $nextdate) . " + " . ($week_of_month-1) . " weeks");
			}

			// if this doesn't exist, then try next month
			while(date('n', $maybe_date) != date('n', $nextdate)) {
				$nextdate = mktime (0, 0, 0, date('n', $nextdate) + $this->months_between, 1, date('Y', $nextdate));
				$nextdate = $this->getFirstDayOfWeek($curdate, $day_of_week);
				$maybe_date = strtotime(date(DateSeriesRules::DATE_FORMAT, $nextdate) . " + " . ($week_of_month-1) . " weeks");
			}

			return $maybe_date;
		}
	}

	private function getFirstDayOfWeek($curdate, $day_of_week) {

		$nextdate = mktime (0, 0, 0, date('n', $curdate), 1, date('Y', $curdate));
		
		while(!($day_of_week > 0 && date('N', $nextdate) == $day_of_week) &&
			!($day_of_week == -1 && $this->isWeekday($nextdate)) &&
		   !($day_of_week == -2 && $this->isWeekend($nextdate))) {
			$nextdate = strtotime(date(DateSeriesRules::DATE_FORMAT, $nextdate) . " + 1 day");
		}

		return $nextdate;
	}

	private function getLastDayOfWeek($curdate, $day_of_week) {
		$nextdate = mktime (date("H", $curdate), date("i", $curdate), date("s", $curdate), date('n', $curdate), $this->getLastDayOfMonth($curdate), date('Y', $curdate));;

		while(date('N', $nextdate) != $day_of_week) {
			$nextdate = strtotime(date(DateSeriesRules::DATE_FORMAT, $nextdate) . " - 1 day");
		}

		return $nextdate;
	}

	private function isWeekday($curdate) {
		return in_array(date('N', $curdate), array(1,2,3,4,5));
	}

	private function isWeekend($curdate) {
		return in_array(date('N', $curdate), array(6,7));
	}

	private function getLastDayOfMonth($curdate) {
		$curmonth = date('n', $curdate);
		$curYear = date('Y', $curdate);
		$nextmonth = mktime(0, 0, 0, $curmonth+1, 1, $curYear);
		$lastDay = strtotime(date(DateSeriesRules::DATE_FORMAT, $nextmonth) . " - 1 day");
		return date('j', $lastDay);
	}

	private function getNextDayOfMonth($curDayOfMonth) {
		foreach($this->days_of_month as $day) {
			if ($day > $curDayOfMonth)
				return $day;
		}

		return $this->days_of_month[0];
	}
}

class YearSeriesRules implements DateSeriesRules
{
	private $years_between;
	private $months_of_year;
	private $week_of_month;
	private $day_of_week;

	public function __construct($years_between = 1, $months_of_year = array(), $week_of_month = null, $day_of_week = null) {
		$this->years_between = $years_between;
		$this->months_of_year = $months_of_year;
		$this->week_of_month = $week_of_month;
		$this->day_of_week = $day_of_week;

		sort($this->months_of_year);
	}

	public function getNextDate($curdate) {
		$next_month_of_year = date('n', $curdate);
		$day_of_month = date('j', $curdate);

		if(sizeof($this->months_of_year) > 0) {
			$next_month_of_year = $this->getNextMonthOfYear($next_month_of_year);
		}

		if($this->week_of_month && $this->day_of_week) {
			// 4th wednesday of next month
			return $this->getNthDayOfMonth($curdate, $this->day_of_week, $this->week_of_month, $next_month_of_year);
		}
		else // normal date based recurrence
		{
			$nextdate = $this->advanceDate($curdate, $next_month_of_year);

			// TODO: TEST AHEAD FOR INVALID RECURSIONS (ie every February 29 or September 31 which will result in an infinite loop)
			while(date('j', $curdate) != date('j', $nextdate)) { // date wrapped
				$nextdate = strtotime(date(DateSeriesRules::DATE_FORMAT, $nextdate) . " - 1 months"); // back it up a month to get to the correct one
				$next_month_of_year = $this->getNextMonthOfYear(date('n', $nextdate)); // get the next month in the series
				$nextdate = $this->advanceDate($curdate, $next_month_of_year);
			}

			return mktime(date("H", $curdate), date("i", $curdate), date("s", $curdate), date('n', $nextdate),  date('j', $nextdate), date('Y', $nextdate));
		}
	}

	private function advanceDate($curdate, $next_month_of_year, $day_of_month = null) {
		if($next_month_of_year > date('n', $curdate)) { // is curdate correct here?
			$nextdate = mktime(date("H", $curdate), date("i", $curdate), date("s", $curdate), $next_month_of_year, $day_of_month ? $day_of_month : date('j', $curdate), date('Y', $curdate));
		} else {
			$nextdate = mktime (0, 0, 0, $next_month_of_year, $day_of_month ? $day_of_month : date('j', $curdate), date('Y', $curdate) + $this->years_between);
		}

		return $nextdate;
	}

	private function getNthDayOfMonth($curdate, $day_of_week, $week_of_month, $next_month_of_year) {
		$nextdate = $this->advanceDate($curdate, $next_month_of_year, 1); // advance to correct month
		$nextdate = $this->getFirstDayOfWeekInMonth($nextdate, $day_of_week);

		if($week_of_month == -1) { // LAST WEEK
			$nextdate = $this->getLastDayOfWeekInMonth($nextdate, $day_of_week);
			return $nextdate;
		} else {
			$maybe_date = strtotime(date(DateSeriesRules::DATE_FORMAT, $nextdate) . " + " . ($week_of_month-1) . " weeks");

			// if this doesn't exist, then try next month
			while(date('n', $maybe_date) != date('n', $nextdate)) {
				// advance again
				$next_month_of_year = $this->getNextMonthOfYear(date('n', $nextdate)); 
				$nextdate = $this->advanceDate($nextdate, $next_month_of_year);
				$nextdate = $this->getFirstDayOfWeek($curdate, $day_of_week);
				$maybe_date = strtotime(date(DateSeriesRules::DATE_FORMAT, $nextdate) . " + " . ($week_of_month-1) . " weeks");
			}

			return $maybe_date;
		}
	}

	private function getFirstDayOfWeekInMonth($curdate, $day_of_week) {

		$nextdate = mktime (0, 0, 0, date('n', $curdate), 1, date('Y', $curdate));

		while(!($day_of_week > 0 && date('N', $nextdate) == $day_of_week) &&
			!($day_of_week == -1 && $this->isWeekday($nextdate)) &&
		   !($day_of_week == -2 && $this->isWeekend($nextdate))) {
			$nextdate = strtotime(date(DateSeriesRules::DATE_FORMAT, $nextdate) . " + 1 day");
		}

		return $nextdate;
	}

	private function getLastDayOfWeekInMonth($curdate, $day_of_week) {
		$nextdate = mktime (date("H", $curdate), date("i", $curdate), date("s", $curdate), date('n', $curdate), $this->getLastDayOfMonth($curdate), date('Y', $curdate));;

		while(date('N', $nextdate) != $day_of_week) {
			$nextdate = strtotime(date(DateSeriesRules::DATE_FORMAT, $nextdate) . " - 1 day");
		}

		return $nextdate;
	}

	private function isWeekday($curdate) {
		return in_array(date('N', $curdate), array(1,2,3,4,5));
	}

	private function isWeekend($curdate) {
		return in_array(date('N', $curdate), array(6,7));
	}

	private function getNextMonthOfYear($curMonth) {
		foreach($this->months_of_year as $month) {
			if ($month > $curMonth)
				return $month;
		}

		return $this->months_of_year[0];
	}

	private function getLastDayOfMonth($curdate) {
		$curmonth = date('n', $curdate);
		$curYear = date('Y', $curdate);
		$nextmonth = mktime(0, 0, 0, $curmonth+1, 1, $curYear);
		$lastDay = strtotime(date(DateSeriesRules::DATE_FORMAT, $nextmonth) . " - 1 day");
		return date('j', $lastDay);
	}
}
?>