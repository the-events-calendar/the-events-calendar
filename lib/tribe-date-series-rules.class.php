<?php
/**
 * This file contains all of the classes that define recurrence rules
 * Corresponding unit tests live in the /test directory.  Tests should be run when changing anything in here
 * as the rules are quite complex in many cases.
 * @author John Gadbois
 */

/**
 *  The interface for all DateSeriesRules.  They all implement a function called getNextDate 
 *  that returns the next date in a series based on it's particular set of rules. 
 */
interface DateSeriesRules
{
	const DATE_ONLY_FORMAT = "Y-m-d";
	const DATE_FORMAT = "Y-m-d H:i:s";
    public function getNextDate($curdate);
}

/**
 * Rules for daily recurrences
 */
class DaySeriesRules implements DateSeriesRules
{
	private $days_between;

	/**
	 * The class constructor.
	 *
	 * @param int $days_between The days between occurrences.
	 */
	public function __construct($days_between = 1) {
		$this->days_between = $days_between;
	}
	
	/**
	 * Gets the timestamp of the next date of recurrence.
	 *
	 * @param int $curdate The current date's timestamp.
	 * @return int The next date's timestamp.
	 */
	public function getNextDate($curdate) {
		return strtotime(date(DateSeriesRules::DATE_FORMAT, $curdate) . " + " . $this->days_between . " days");
	}
}

/**
 * Rules for weekly recurrences
 */
class WeekSeriesRules implements DateSeriesRules
{
	private $weeks_between;
	private $days;

	/**
	 * The class constructor.
	 *
	 * @param int $weeks_between The number of weeks between recurrences.
	 * @param array $days The days on which an event recurs.
	 */
	public function __construct($weeks_between = 1, $days = array()) {
		$this->weeks_between = $weeks_between;
		$this->days = $days; // days are integers representing days
		sort($this->days);
	}

	/**
	 * Get the next date of a recurrence.
	 *
	 * @param int $curdate The timestamp of the current instance of event.
	 * @return int The timestamp of the next recurrence.
	 */
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

	/**
	 * Get the next day-of-the-week that the event occurs on.
	 *
	 * @param int $curDayOfWeek The index of the current day-of-week.
	 * @return int The index of the next date of recurrence.
	 */
	private function getNextDayOfWeek($curDayOfWeek) {
		foreach($this->days as $day) {
			if ($day > $curDayOfWeek)
				return $day;
		}

		return $this->days[0];
	}
}

/**
 * Rules for monthly recurrences
 */
class MonthSeriesRules implements DateSeriesRules
{
	private $months_between;
	private $days_of_month;
	private $week_of_month;
	private $day_of_week;

	/**
	 * The class constructor.
	 *
	 * @param int $months_between The number of months between recurrences.
	 * @param array $days_of_month The days of the month on which recurrences occur.
	 * @param int $week_of_month The week of the month on which recurrences occur.
	 * @param int $day_of_week The index of the day of the week on which recurrences occur.
	 */
	public function __construct($months_between = 1, $days_of_month = array(), $week_of_month = null, $day_of_week = null) {
		$this->months_between = $months_between;
		$this->days_of_month = (array)$days_of_month;
		$this->week_of_month = $week_of_month;
		$this->day_of_week = $day_of_week;
		
		sort($this->days_of_month);
	}

	/**
	 * Get the timestamp of the next occurrence.
	 *
	 * @param int $curdate The current timestamp of a given occurrence.
	 * @return int The timestamp of the next occurrence.
	 */
	public function getNextDate($curdate) {
		$next_day_of_month = date('j', $curdate);

		if($this->week_of_month && $this->day_of_week) {
			return $this->getNthDayOfWeek($curdate, $this->day_of_week, $this->week_of_month);
		}
		else // normal date based recurrence
		{
			if(sizeof($this->days_of_month) > 0) {
				$next_day_of_month = $this->getNextDayOfMonth($next_day_of_month);

				while(TribeDateUtils::getLastDayOfMonth($curdate) < $next_day_of_month)
				{
					$next_day_of_month = $this->days_of_month[0];
					$curdate = mktime(date("H", $curdate), date("i", $curdate), date("s", $curdate), date('n', $curdate) + $this->months_between, 1, date('Y', $curdate));
				}
			}

			if($next_day_of_month > date('j', $curdate)) {
				// no need to jump ahead stay in current month
				return mktime(date("H", $curdate), date("i", $curdate), date("s", $curdate), date('n', $curdate), $next_day_of_month, date('Y', $curdate));
			} else {
				$nextdate = mktime (date("H", $curdate), date("i", $curdate), date("s", $curdate), date('n', $curdate) + $this->months_between, 1, date('Y', $curdate));

				while(TribeDateUtils::getLastDayOfMonth($nextdate) < $next_day_of_month) {
					$nextdate = mktime (date("H", $curdate), date("i", $curdate), date("s", $curdate), date('n', $nextdate) + $this->months_between, 1, date('Y', $nextdate));
				}

				return mktime(date("H", $curdate), date("i", $curdate), date("s", $curdate), date('n', $nextdate), $next_day_of_month, date('Y', $nextdate));
			}
		}
	}

	/**
	 * Gets a given occurence on a given day-of-week.
	 *
	 * @param int $curdate The current timestamp of an occurence.
	 * @param int $day_of_week The index of a given day-of-week.
	 * @param int $week_of_month The index of a given week-of-month.
	 * @return int The timestamp of the requested occurrence.
	 */
	private function getNthDayOfWeek($curdate, $day_of_week, $week_of_month) {

		if($week_of_month == -1) { // LAST WEEK
			$nextdate = TribeDateUtils::getLastDayOfWeekInMonth($curdate, $day_of_week);

			// If the date returned above is the same as the date we're starting from
			// move on to the next month by interval to consider.
			if($curdate == $nextdate) {
				$curdate = mktime (0, 0, 0, date('n', $curdate) + $this->months_between, 1, date('Y', $curdate));
				$nextdate = TribeDateUtils::getLastDayOfWeekInMonth($curdate, $day_of_week);
			}

			return $nextdate;
		} else {
			// get the first occurrence of the requested day of the week from the requested $curdate's month
			$first_occurring_day_of_week = TribeDateUtils::getFirstDayOfWeekInMonth($curdate, $day_of_week);

			// get that day of the week in the requested nth week
			$maybe_date = strtotime( date( DateSeriesRules::DATE_FORMAT, $first_occurring_day_of_week ) . " + " . ( $week_of_month-1 ) . " weeks" );

			// if $maybe_date equals or is before the $curdate, then try next month 
			// (this should only be true if $week_of_month is 1)
			if( date( DateSeriesRules::DATE_ONLY_FORMAT, $maybe_date ) <= date( DateSeriesRules::DATE_ONLY_FORMAT, $curdate ) ) {

				// get the first day of the next month according to $this->months_between
				$next_month = mktime ( 0, 0, 0, date( 'n', $curdate ) + $this->months_between, 1, date( 'Y', $curdate ) );

				// Get the first occurrence of the requested day of the week from $next_month's month
				$first_occurring_day_of_week = TribeDateUtils::getFirstDayOfWeekInMonth( $next_month, $day_of_week );

				// Get that day of the week in the requested nth week
				$maybe_date = strtotime( date( DateSeriesRules::DATE_FORMAT, $first_occurring_day_of_week ) . " + " . ( $week_of_month-1 ) . " weeks" );
			}

			// if $maybe_date doesn't have the same month as $first_occurring_day_of_week, keep incrementing by $this->months_between 
			// until they do, but don't infinitely loop past the 'recurrenceMaxMonthsAfter' setting
			$i = 0;
			while( date( 'n', $maybe_date ) != date( 'n', $first_occurring_day_of_week ) && $i <= tribe_get_option( 'recurrenceMaxMonthsAfter', 24 ) ) {
				$next_month = mktime ( 0, 0, 0, date( 'n', $first_occurring_day_of_week ) + $this->months_between, 1, date( 'Y', $first_occurring_day_of_week ) );
				$first_occurring_day_of_week = TribeDateUtils::getFirstDayOfWeekInMonth( $next_month, $day_of_week );
				$maybe_date = strtotime( date( DateSeriesRules::DATE_FORMAT, $first_occurring_day_of_week ) . " + " . ( $week_of_month-1 ) . " weeks" );
				$i += $this->months_between;
			}
			return $maybe_date;
		}
	}

	/**
	 * Gets the next day of the month on which an occurrence occurs.
	 *
	 * @param int $curDayOfMonth The index of the current day of the month.
	 * @return int The index of the next day of the month.
	 */
	private function getNextDayOfMonth($curDayOfMonth) {
		foreach($this->days_of_month as $day) {
			if ($day > $curDayOfMonth)
				return $day;
		}

		return $this->days_of_month[0];
	}

	private function intToOrdinal($number) {
		switch( $number ) {
			case 1: return "First";
			case 2: return "Second";
			case 3: return "Third";
			case 4: return "Fourth";
			case 5: return "Fifth";
			case -1: return "Last";
		    default: return null;
}
	}
}

/**
 * Rules for yearly recurrences
 */
class YearSeriesRules implements DateSeriesRules
{
	private $years_between;
	private $months_of_year;
	private $week_of_month;
	private $day_of_week;

	/**
	 * The class constructor.
	 *
	 * @param int $years_between The number of years between recurrences.
	 * @param array $months_of_year The months in which recurrences occur.
	 * @param int $week_of_month The week of the month on which occurences occur.
	 * @param int $day_of_week The day of the week on which occurrences occur.
	 */
	public function __construct($years_between = 1, $months_of_year = array(), $week_of_month = null, $day_of_week = null) {
		$this->years_between = $years_between;
		$this->months_of_year = $months_of_year;
		$this->week_of_month = $week_of_month;
		$this->day_of_week = $day_of_week;

		sort($this->months_of_year);
	}

	/**
	 * Get next date of occurence.
	 *
	 * @param int $curdate The timestamp of the current recurrence.
	 * @return int The timestamp of the next occurence.
	 */
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

	/**
	 * Advance to the next recurrence date.
	 *
	 * @param int $curdate The timestamp of the current recurrence.
	 * @param int $next_month_of_year The index of the next month of the year.
	 * @param int $day_of_month The index of the day of month.
	 * @return int The timestamp of the next date.
	 */
	private function advanceDate($curdate, $next_month_of_year, $day_of_month = null) {
		if($next_month_of_year > date('n', $curdate)) { // is curdate correct here?
			$nextdate = mktime(date("H", $curdate), date("i", $curdate), date("s", $curdate), $next_month_of_year, $day_of_month ? $day_of_month : date('j', $curdate), date('Y', $curdate));
		} else {
			$nextdate = mktime (0, 0, 0, $next_month_of_year, $day_of_month ? $day_of_month : date('j', $curdate), date('Y', $curdate) + $this->years_between);
		}

		return $nextdate;
	}

	/**
	 * Get the timestamp of the Nth day of month.
	 * 
	 * @param int $curdate The current occurrence's timestamp.
	 * @param int $day_of_week The index of the day-of-week.
	 * @param int $week_of_month The index of the week of the month.
	 * @param int $next_month_of_year The index of the next month of the year.
	 * @return int The timestamp of the next occurrence on the nth day of the month.
	 */
	private function getNthDayOfMonth($curdate, $day_of_week, $week_of_month, $next_month_of_year) {
		$nextdate = $this->advanceDate($curdate, $next_month_of_year, 1); // advance to correct month
		$nextdate = TribeDateUtils::getFirstDayOfWeekInMonth($nextdate, $day_of_week);

		if($week_of_month == -1) { // LAST WEEK
			$nextdate = TribeDateUtils::getLastDayOfWeekInMonth($nextdate, $day_of_week);
			return $nextdate;
		} else {
			$maybe_date = strtotime(date(DateSeriesRules::DATE_FORMAT, $nextdate) . " + " . ($week_of_month-1) . " weeks");

			// if this doesn't exist, then try next month
			while(date('n', $maybe_date) != date('n', $nextdate)) {
				// advance again
				$next_month_of_year = $this->getNextMonthOfYear(date('n', $nextdate)); 
				$nextdate = $this->advanceDate($nextdate, $next_month_of_year);
				$nextdate = TribeDateUtils::getFirstDayOfWeekInMonth($curdate, $day_of_week);
				$maybe_date = strtotime(date(DateSeriesRules::DATE_FORMAT, $nextdate) . " + " . ($week_of_month-1) . " weeks");
			}

			return $maybe_date;
		}
	}

	/**
	 * Get the index of the next month of the year on which an occurrence occurs.
	 *
	 * @param int $curMonth The index of the current month.
	 * @return int The index of the next month bearing a recurrence.
	 */
	private function getNextMonthOfYear($curMonth) {
		foreach($this->months_of_year as $month) {
			if ($month > $curMonth)
				return $month;
		}

		return $this->months_of_year[0];
	}
}
