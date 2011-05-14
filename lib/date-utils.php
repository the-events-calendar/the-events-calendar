<?php
class DateUtils {
	// default formats, they are overridden by WP options or by arguments to date methods
	const DATEONLYFORMAT 		= 'F j, Y';
	const TIMEFORMAT			= 'g:i A';
	const DBDATEFORMAT	 		= 'Y-m-d';
	const DBDATETIMEFORMAT 		= 'Y-m-d G:i:s';
	const DBTIMEFORMAT 		= 'G:i:s';
	const DBYEARMONTHTIMEFORMAT = 'Y-m';	
	
	public static function dateOnly( $date ) {
		return date(DateUtils::DBDATEFORMAT, strtotime($date) );
	}	
	
	public static function timeOnly( $date ) {
		return date(DateUtils::DBTIMEFORMAT, strtotime($date) );
	}
	
	public static function addTimeToDate( $date, $time ) {
		$date = self::dateOnly($date);
		return date(DateUtils::DBDATETIMEFORMAT, strtotime($date . $time) );
	}
}
?>
