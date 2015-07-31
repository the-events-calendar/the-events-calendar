<?php

/**
 * Helpers for handling timezone based event datetimes.
 *
 * In our timezone logic, the term "local" refers to the locality of an event
 * rather than the local WordPress timezone.
 */
class Tribe__Events__Timezones {
	const SITE_TIMEZONE  = 100;
	const EVENT_TIMEZONE = 200;


	/**
	 * Container for reusable DateTimeZone objects.
	 *
	 * @var array
	 */
	protected static $timezones = array();


	/**
	 * Returns the timezone string for the specified event (if null it assumes the
	 * current event where that can be determined).
	 *
	 * If no timezone has explicitly been set for the event, it returns the global
	 * WordPress timezone string.
	 *
	 * @param int|null $event_id
	 *
	 * @return string
	 */
	public static function get_event_timezone_string( $event_id = null ) {
		$event_id = Tribe__Events__Main::postIdHelper( $event_id );
		$tzstring = get_post_meta( $event_id, '_EventTimezone', true );
		return $tzstring ? $tzstring : self::wp_timezone_string();
	}

	/**
	 * Returns the current site-wide timezone string.
	 *
	 * Based on the core WP code found in wp-admin/options-general.php.
	 *
	 * @return string
	 */
	public static function wp_timezone_string() {
		$current_offset = get_option( 'gmt_offset' );
		$tzstring       = get_option( 'timezone_string' );

		// Return the timezone string if already set
		if ( ! empty( $tzstring ) ) {
			return $tzstring;
		}

		// Otherwise return the UTC offset
		if ( 0 == $current_offset ) {
			return 'UTC+0';
		} elseif ($current_offset < 0) {
			return 'UTC' . $current_offset;
		}

		return 'UTC+' . $current_offset;
	}

	/**
	 * Tried to convert the provided $datetime to UTC from the timezone represented by $tzstring.
	 *
	 * Though the usual range of formats are allowed, $datetime ordinarily ought to be something
	 * like the "Y-m-d H:i:s" format (ie, no timezone information). If it itself contains timezone
	 * data, the results may be unexpected.
	 *
	 * In those cases where the conversion fails to take place, the $datetime string will be
	 * returned untouched.
	 *
	 * @param string $datetime
	 * @param string $tzstring
	 *
	 * @return string
	 */
	public static function to_utc( $datetime, $tzstring ) {
		try {
			$local = self::get_timezone( $tzstring );
			$utc   = self::get_timezone( 'UTC' );

			$datetime = date_create( $datetime, $local )->setTimezone( $utc );
			return $datetime->format( Tribe__Events__Date_Utils::DBDATETIMEFORMAT );
		}
		catch ( Exception $e ) {
			return $datetime;
		}
	}

	/**
	 * Tries to convert the provided $datetime to the timezone represented by $tzstring.
	 *
	 * This is the sister function of self::to_utc() - please review the docs for that method
	 * for more information.
	 *
	 * @param string $datetime
	 * @param string $tzstring
	 *
	 * @return string
	 */
	public static function to_tz( $datetime, $tzstring ) {
		try {
			$local = self::get_timezone( $tzstring );
			$utc   = self::get_timezone( 'UTC' );

			$datetime = date_create( $datetime, $utc )->setTimezone( $local );
			return $datetime->format( Tribe__Events__Date_Utils::DBDATETIMEFORMAT );
		}
		catch ( Exception $e ) {
			return $datetime;
		}
	}

	/**
	 * Returns a timestamp for the event start date that can be passed to tribe_event_format_date()
	 * in order to produce the time in the correct timezone.
	 *
	 * @param int    $event_id
	 * @param string $timezone
	 *
	 * @return int
	 */
	public static function event_start_timestamp( $event_id, $timezone = null ) {
		return self::get_event_timestamp( $event_id, 'Start', $timezone );
	}

	/**
	 * Returns a timestamp for the event end date that can be passed to tribe_event_format_date()
	 * in order to produce the time in the correct timezone.
	 *
	 * @param int    $event_id
	 * @param string $timezone
	 *
	 * @return int
	 */
	public static function event_end_timestamp( $event_id, $timezone = null ) {
		return self::get_event_timestamp( $event_id, 'End', $timezone );
	}

	/**
	 * Returns a timestamp for the event date that can be passed to tribe_event_format_date()
	 * in order to produce the time in the correct timezone.
	 *
	 * @param int    $event_id
	 * @param string $type (expected to be 'Start' or 'End')
	 * @param string $timezone
	 *
	 * @return int
	 */
	protected static function get_event_timestamp( $event_id, $type = 'Start', $timezone = null ) {
		$event    = get_post( Tribe__Events__Main::postIdHelper( $event_id ) );
		$event_tz = get_post_meta( $event->ID, '_EventTimezone', true );
		$site_tz  = self::wp_timezone_string();

		if ( null === $timezone ) {
			$timezone = self::mode();
		}

		// Should we use the event specific timezone or the site-wide timezone?
		$use_event_tz = self::EVENT_TIMEZONE === $timezone;
		$use_site_tz  = self::SITE_TIMEZONE === $timezone;

		// Determine if the event timezone and site timezone the same *or* if the event does not have timezone
		// information (in which case, we'll assume the event time inherits the site timezone)
		$site_zone_is_event_zone = ( $event_tz === $site_tz || empty( $event_tz ) );

		// If the event-specific timezone is suitable, we can obtain it without any conversion work
		if ( $use_event_tz || ( $use_site_tz && $site_zone_is_event_zone ) ) {
			$datetime = isset( $event->{"Event{$type}StartDate"} )
				? $event->{"Event{$type}StartDate"}
				: get_post_meta( $event->ID, "_Event{$type}Date", true );

			return strtotime( $datetime );
		}

		// Otherwise lets load the event's UTC time and convert it
		$datetime = isset( $event->{"Event{$type}StartDateUTC"} )
			? $event->{"Event{$type}StartDateUTC"}
			: get_post_meta( $event->ID, "_Event{$type}DateUTC", true );

		$tzstring = ( self::SITE_TIMEZONE === $timezone )
			? self::wp_timezone_string()
			: $timezone;

		$localized = self::to_tz( $datetime, $tzstring );
		return strtotime( $localized );
	}

	/**
	 * Accepts a unix timestamp and adjusts it so that when it is used to consitute
	 * a new datetime string, that string reflects the designated timezone.
	 *
	 * @param string $unix_timestamp
	 * @param string $tzstring
	 *
	 * @return string
	 */
	public static function adjust_timestamp( $unix_timestamp, $tzstring ) {
		try {
			$local = self::get_timezone( $tzstring );
			$datetime = date_create_from_format( 'U', $unix_timestamp )->format( Tribe__Events__Date_Utils::DBDATETIMEFORMAT );
			return date_create_from_format( 'Y-m-d H:i:s', $datetime, $local )->getTimestamp();
		}
		catch( Exception $e ) {
			return $unix_timestamp;
		}
	}

	/**
	 * Returns a DateTimeZone object matching the representation in $tzstring where
	 * possible, or else representing UTC (or, in the worst case, false).
	 *
	 * If optional parameter $with_fallback is true, which is the default, then in
	 * the event it cannot find/create the desired timezone it will try to return the
	 * UTC DateTimeZone before bailing.
	 *
	 * @param  string $tzstring
	 * @param  bool   $with_fallback = true
	 *
	 * @return DateTimeZone|false
	 */
	public static function get_timezone( $tzstring, $with_fallback = true ) {
		if ( isset( self::$timezones[$tzstring] ) ) {
			return self::$timezones[$tzstring];
		}

		try {
			self::$timezones[$tzstring] = new DateTimeZone( $tzstring );
			return self::$timezones[$tzstring];
		}
		catch ( Exception $e ) {
			if ( $with_fallback ) {
				return self::get_timezone( 'UTC', true );
			}
		}

		return false;
	}

	/**
	 * Returns a string representing the timezone/offset currently desired for
	 * the display of dates and times.
	 *
	 * @return string
	 */
	public static function mode() {
		return apply_filters( 'tribe_events_current_display_timezone', self::EVENT_TIMEZONE );
	}
}