<?php
/**
 * Date utility functions used throughout ECP
 */

// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }

if(!class_exists('TribeDateUtils')) {
	class TribeDateUtils {
		// default formats, they are overridden by WP options or by arguments to date methods
		const DATEONLYFORMAT 		= 'F j, Y';
		const TIMEFORMAT			= 'g:i A';
		const HOURFORMAT			= 'g';
		const MINUTEFORMAT			= 'i';
		const MERIDIANFORMAT			= 'A';
		const DBDATEFORMAT	 		= 'Y-m-d';
		const DBDATETIMEFORMAT 		= 'Y-m-d H:i:s';
		const DBTIMEFORMAT 		= 'H:i:s';
		const DBYEARMONTHTIMEFORMAT = 'Y-m';

		/**
		 * Returns the date only.
		 *
		 * @param int|string $date The date (timestamp or string).
		 * @param bool $isTimestamp Is $date in timestamp format?
		 * @return string The date only in DB format.
		 */
		public static function dateOnly( $date, $isTimestamp = false ) {
			$date = $isTimestamp ? $date : strtotime($date);
			return date(TribeDateUtils::DBDATEFORMAT, $date );
		}

		/**
		 * Returns the date only.
		 *
		 * @param string $date The date.
		 * @return string The time only in DB format.
		 */
		public static function timeOnly( $date ) {
			return date(TribeDateUtils::DBTIMEFORMAT, strtotime($date) );
		}

		/**
		 * Returns the hour only.
		 *
		 * @param string $date The date.
		 * @return string The hour only.
		 */	
		public static function hourOnly( $date ) {
			return date(TribeDateUtils::HOURFORMAT, strtotime($date) );
		}

		/**
		 * Returns the minute only.
		 *
		 * @param string $date The date.
		 * @return string The minute only.
		 */
		public static function minutesOnly( $date ) {
			return date(TribeDateUtils::MINUTEFORMAT, strtotime($date) );
		}

		/**
		 * Returns the meridian (am or pm) only.
		 *
		 * @param string $date The date.
		 * @return string The meridian only in DB format.
		 */
		public static function meridianOnly( $date ) {
			return date(TribeDateUtils::MERIDIANFORMAT, strtotime($date) );
		}

		/**
		 * Returns the date and time.
		 *
		 * @param int|string $date The date (timestamp or string).
		 * @param bool $isTimestamp Is $date in timestamp format?
		 * @return string The date and time in DB format.
		 */
		public static function dateAndTime( $date, $isTimestamp = false ) {
			$date = $isTimestamp ? $date : strtotime($date);
			return date(TribeDateUtils::DBDATETIMEFORMAT, $date );
		}

		/**
		 * Returns the end of a given day.
		 *
		 * @param int|string $date The date (timestamp or string).
		 * @param bool $isTimestamp Is $date in timestamp format?
		 * @return string The date and time of the end of a given day.
		 */
		public static function endOfDay( $date, $isTimestamp = false ) {
			$date = $isTimestamp ? $date : strtotime($date);
			$date = date(TribeDateUtils::DBDATEFORMAT, $date );
			$date = strtotime($date . ' 23:59:59');
			return date(TribeDateUtils::DBDATETIMEFORMAT, $date );
		}

		/**
		 * Returns the beginning of a given day.
		 *
		 * @param int|string $date The date (timestamp or string).
		 * @param bool $isTimestamp Is $date in timestamp format?
		 * @return string The date and time of the beginning of a given day.
		 */
		public static function beginningOfDay( $date, $isTimestamp = false ) {
			$date = $isTimestamp ? $date : strtotime($date);
			$date = date(TribeDateUtils::DBDATEFORMAT, $date );
			$date = strtotime($date . ' 00:00:00');
			return date(TribeDateUtils::DBDATETIMEFORMAT, $date );
		}

		/**
		 * Add a time to a date..
		 *
		 * @param string $date The date.
		 * @param string $time The time.?
		 * @return string The date plus the time.
		 */
		public static function addTimeToDate( $date, $time ) {
			$date = self::dateOnly($date);
			return date(TribeDateUtils::DBDATETIMEFORMAT, strtotime($date . $time) );
		}

	  	/**
	  	 * Returns the number of seconds (absolute value) between two dates/times.
	  	 *
	  	 * @param string $date1 The first date.
	  	 * @param string $date2 The second date.
	  	 * @return int The number of seconds between the dates.
	  	 */
		public static function timeBetween( $date1, $date2 ) {
		 return abs(strtotime($date1) - strtotime($date2));
		}
		
		/**
		 * Returns the last day of the month given a php date.
		 *
		 * @param int $timestamp THe timestamp.
		 * @return string The last day of the month.
		 */
		public static function getLastDayOfMonth( $timestamp ) {
			$curmonth = date('n', $timestamp);
			$curYear = date('Y', $timestamp);
			$nextmonth = mktime(0, 0, 0, $curmonth+1, 1, $curYear);
			$lastDay = strtotime(date(DateSeriesRules::DATE_FORMAT, $nextmonth) . " - 1 day");
			return date('j', $lastDay);
		}

		/**
		 * Returns true if the timestamp is a weekday.
		 *
		 * @param int $curDate A timestamp.
		 * @return bool If the timestamp is a weekday.
		 */
		public static function isWeekday($curdate) {
			return in_array(date('N', $curdate), array(1,2,3,4,5));
		}

		/**
		 * Returns true if the timestamp is a weekend.
		 *
		 * @param int $curDate A timestamp.
		 * @return bool If the timestamp is a weekend.
		 */		public static function isWeekend($curdate) {
			return in_array(date('N', $curdate), array(6,7));
		}

		/**
		 * Gets the last day of the week in a month (ie the last Tuesday).  Passing in -1 gives you the last day in the month.
		 *
		 * @param int $curdate A timestamp.
		 * @param int $day_of_week The index of the day of the week.
		 * @return int The timestamp of the date that fits the qualifications.
		 */
		public static function getLastDayOfWeekInMonth($curdate, $day_of_week) {
			$nextdate = mktime (date("H", $curdate), date("i", $curdate), date("s", $curdate), date('n', $curdate), TribeDateUtils::getLastDayOfMonth($curdate), date('Y', $curdate));;

			while(date('N', $nextdate) != $day_of_week  && $day_of_week != -1) {
				$nextdate = strtotime(date(DateSeriesRules::DATE_FORMAT, $nextdate) . " - 1 day");
			}

			return $nextdate;
		}


		/**
		 * Gets the first day of the week in a month (ie the first Tuesday).
		 *
		 * @param int $curdate A timestamp.
		 * @param int $day_of_week The index of the day of the week.
		 * @return int The timestamp of the date that fits the qualifications.
		 */
		public static function getFirstDayOfWeekInMonth($curdate, $day_of_week) {
			$nextdate = mktime (0, 0, 0, date('n', $curdate), 1, date('Y', $curdate));

			while(!($day_of_week > 0 && date('N', $nextdate) == $day_of_week) &&
				!($day_of_week == -1 && TribeDateUtils::isWeekday($nextdate)) &&
			   !($day_of_week == -2 && TribeDateUtils::isWeekend($nextdate))) {
				$nextdate = strtotime(date(DateSeriesRules::DATE_FORMAT, $nextdate) . " + 1 day");
			}

			return $nextdate;
		}

		/**
		 * From http://php.net/manual/en/function.date.php
		 *
		 * @param int $number A number.
		 * @return string The ordinal for that number.
		 */
		public static function numberToOrdinal($number) {
			return $number.(((strlen($number)>1)&&(substr($number,-2,1)=='1'))?
				'th' : date("S",mktime(0,0,0,0,substr($number,-1),0)));
		}
	}
}
