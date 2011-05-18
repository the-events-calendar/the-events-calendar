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
	
	public static function addTimeToDate( $date, $time ) {
		$date = self::dateOnly($date);
		return date(DateUtils::DBDATETIMEFORMAT, strtotime($date . $time) );
	}
}
?>
