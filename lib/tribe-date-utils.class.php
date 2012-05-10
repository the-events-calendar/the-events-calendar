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

		public static function dateOnly( $date, $isTimestamp = false ) {
			$date = $isTimestamp ? $date : strtotime($date);
			return date(TribeDateUtils::DBDATEFORMAT, $date );
		}

		public static function timeOnly( $date ) {
			return date(TribeDateUtils::DBTIMEFORMAT, strtotime($date) );
		}

		public static function hourOnly( $date ) {
			return date(TribeDateUtils::HOURFORMAT, strtotime($date) );
		}

		public static function minutesOnly( $date ) {
			return date(TribeDateUtils::MINUTEFORMAT, strtotime($date) );
		}

		public static function meridianOnly( $date ) {
			return date(TribeDateUtils::MERIDIANFORMAT, strtotime($date) );
		}

		public static function dateAndTime( $date, $isTimestamp = false ) {
			$date = $isTimestamp ? $date : strtotime($date);
			return date(TribeDateUtils::DBDATETIMEFORMAT, $date );
		}

		public static function endOfDay( $date, $isTimestamp = false ) {
			$date = $isTimestamp ? $date : strtotime($date);
			$date = date(TribeDateUtils::DBDATEFORMAT, $date );
			$date = strtotime($date . ' 23:59:59');
			return date(TribeDateUtils::DBDATETIMEFORMAT, $date );
		}

		public static function beginningOfDay( $date, $isTimestamp = false ) {
			$date = $isTimestamp ? $date : strtotime($date);
			$date = date(TribeDateUtils::DBDATEFORMAT, $date );
			$date = strtotime($date . ' 00:00:00');
			return date(TribeDateUtils::DBDATETIMEFORMAT, $date );
		}

		public static function addTimeToDate( $date, $time ) {
			$date = self::dateOnly($date);
			return date(TribeDateUtils::DBDATETIMEFORMAT, strtotime($date . $time) );
		}

      public static function timeBetween( $date1, $date2 ) {
         return abs(strtotime($date1) - strtotime($date2));
      }
		// returns the last day of the month given a php date
		public static function getLastDayOfMonth( $timestamp ) {
			$curmonth = date('n', $timestamp);
			$curYear = date('Y', $timestamp);
			$nextmonth = mktime(0, 0, 0, $curmonth+1, 1, $curYear);
			$lastDay = strtotime(date(DateSeriesRules::DATE_FORMAT, $nextmonth) . " - 1 day");
			return date('j', $lastDay);
		}

		// returns true if the timestamp is a weekday
		public static function isWeekday($curdate) {
			return in_array(date('N', $curdate), array(1,2,3,4,5));
		}

		// returns true if the timestamp is a weekend
		public static function isWeekend($curdate) {
			return in_array(date('N', $curdate), array(6,7));
		}

		// gets the last day of the week in a month (ie the last Tuesday).  Passing in -1 gives you the last day in the month
		public static function getLastDayOfWeekInMonth($curdate, $day_of_week) {
			$nextdate = mktime (date("H", $curdate), date("i", $curdate), date("s", $curdate), date('n', $curdate), TribeDateUtils::getLastDayOfMonth($curdate), date('Y', $curdate));;

			while(date('N', $nextdate) != $day_of_week  && $day_of_week != -1) {
				$nextdate = strtotime(date(DateSeriesRules::DATE_FORMAT, $nextdate) . " - 1 day");
			}

			return $nextdate;
		}


		// gets the first day of the week in a month (ie the first Tuesday).
		public static function getFirstDayOfWeekInMonth($curdate, $day_of_week) {
			$nextdate = mktime (0, 0, 0, date('n', $curdate), 1, date('Y', $curdate));

			while(!($day_of_week > 0 && date('N', $nextdate) == $day_of_week) &&
				!($day_of_week == -1 && TribeDateUtils::isWeekday($nextdate)) &&
			   !($day_of_week == -2 && TribeDateUtils::isWeekend($nextdate))) {
				$nextdate = strtotime(date(DateSeriesRules::DATE_FORMAT, $nextdate) . " + 1 day");
			}

			return $nextdate;
		}

		// from http://php.net/manual/en/function.date.php
		public static function numberToOrdinal($number) {
			return $number.(((strlen($number)>1)&&(substr($number,-2,1)=='1'))?
				'th' : date("S",mktime(0,0,0,0,substr($number,-1),0)));
		}
	}
}
