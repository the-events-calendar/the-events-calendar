<?php
class DateUtils {
	// default formats, they are overridden by WP options or by arguments to date methods
	const DATEONLYFORMAT 		= 'F j, Y';
	const TIMEFORMAT			= 'g:i A';
	const DBDATEFORMAT	 		= 'Y-m-d';
	const DBDATETIMEFORMAT 		= 'Y-m-d H:i:s';
	const DBTIMEFORMAT 		= 'H:i:s';
	const DBYEARMONTHTIMEFORMAT = 'Y-m';	
	
	public static function dateOnly( $date, $isTimestamp = false ) {
		$date = $isTimestamp ? $date : strtotime($date);
		return date(DateUtils::DBDATEFORMAT, $date );
	}	
	
	public static function timeOnly( $date ) {
		return date(DateUtils::DBTIMEFORMAT, strtotime($date) );
	}
	
	public static function dateAndTime( $date, $isTimestamp = false ) {
		$date = $isTimestamp ? $date : strtotime($date);
		return date(DateUtils::DBDATETIMEFORMAT, $date );
	}		
	
	public static function endOfDay( $date, $isTimestamp = false ) {
		$date = $isTimestamp ? $date : strtotime($date);
		$date = date(DateUtils::DBDATEFORMAT, $date );
		$date = strtotime($date . ' 11:59:59');
		return date(DateUtils::DBDATETIMEFORMAT, $date );		
	}
	
	public static function addTimeToDate( $date, $time ) {
		$date = self::dateOnly($date);
		return date(DateUtils::DBDATETIMEFORMAT, strtotime($date . $time) );
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
		$nextdate = mktime (date("H", $curdate), date("i", $curdate), date("s", $curdate), date('n', $curdate), DateUtils::getLastDayOfMonth($curdate), date('Y', $curdate));;

		while(date('N', $nextdate) != $day_of_week  && $day_of_week != -1) {
			$nextdate = strtotime(date(DateSeriesRules::DATE_FORMAT, $nextdate) . " - 1 day");
		}

		return $nextdate;
	}
	

	// gets the first day of the week in a month (ie the first Tuesday).
	public static function getFirstDayOfWeekInMonth($curdate, $day_of_week) {
		$nextdate = mktime (0, 0, 0, date('n', $curdate), 1, date('Y', $curdate));

		while(!($day_of_week > 0 && date('N', $nextdate) == $day_of_week) &&
			!($day_of_week == -1 && DateUtils::isWeekday($nextdate)) &&
		   !($day_of_week == -2 && DateUtils::isWeekend($nextdate))) {
			$nextdate = strtotime(date(DateSeriesRules::DATE_FORMAT, $nextdate) . " + 1 day");
		}

		return $nextdate;
	}	
}
?>
