<?php

/**
 * Helpers for handling timezone based event datetimes.
 *
 * In our timezone logic, the term "local" refers to the locality of an event
 * rather than the local WordPress timezone.
 */
class Tribe__Events__Timezones extends Tribe__Timezones {
	public static function init() {
		self::display_timezones();
		parent::init();
	}

	/**
	 * Takes care of appending timezone information to the display of
	 * event date/times.
	 */
	protected static function display_timezones() {
		if ( tribe_get_option( 'tribe_events_timezones_show_zone' ) ) {
			add_filter( 'tribe_events_event_schedule_details_inner', array( __CLASS__, 'append_timezone' ), 10, 2 );
		}
	}

	/**
	 * Adds the timezone to the event schedule information.
	 *
	 * @param string   $schedule_text
	 * @param int|null $event_id
	 *
	 * @return string
	 */
	public static function append_timezone( $schedule_text, $event_id = null ) {
		static $hide_for_all_day;

		if ( ! isset( $hide_for_all_day ) ) {
			$hide_for_all_day = apply_filters( 'tribe_events_hide_timezone_for_all_day_events', true );
		}

		if ( tribe_event_is_all_day( $event_id ) && $hide_for_all_day ) {
			return $schedule_text;
		}

		$timezone = self::is_mode( 'site' )
			? self::wp_timezone_abbr( tribe_get_start_date( $event_id, true, Tribe__Date_Utils::DBDATETIMEFORMAT ) )
			: self::get_event_timezone_abbr( $event_id );

		if ( ! empty( $timezone ) ) {
			$timezone_text = " <span class='timezone'> $timezone </span>";
			$schedule_text = $schedule_text . $timezone_text;
		}

		return $schedule_text;
	}

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
	 * Returns the event's timezone abbreviation if it can be determined, or else
	 * falls back on the full timezone string/offset text (again, if known - if it
	 * is not it will assume the global WP timezone setting).
	 *
	 * @param int|null $event_id
	 *
	 * @return string
	 */
	public static function get_event_timezone_abbr( $event_id = null ) {
		$abbr = get_post_meta( $event_id, '_EventTimezoneAbbr', true );

		if ( empty( $abbr ) ) {
			$timezone_string = self::get_event_timezone_string( $event_id );
			$date = tribe_get_start_date( $event_id, true, Tribe__Date_Utils::DBDATETIMEFORMAT );
			$abbr = self::abbr( $date, $timezone_string );
		}

		return empty( $abbr )
			? $timezone_string
			: $abbr;
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
		} elseif ( $current_offset < 0 ) {
			return 'UTC' . $current_offset;
		}

		return 'UTC+' . $current_offset;
	}

	/**
	 * Returns the current site-wide timezone string abbreviation, if it can be
	 * determined or falls back on the full timezone string/offset text.
	 *
	 * @param string $date
	 *
	 * @return string
	 */
	public static function wp_timezone_abbr( $date ) {
		$abbr = get_transient( 'tribe_events_wp_timezone_abbr' );

		if ( empty( $abbr ) ) {
			$timezone_string = self::wp_timezone_string();
			$abbr = self::abbr( $date, $timezone_string );
			set_transient( 'tribe_events_wp_timezone_abbr', $abbr );
		}

		return empty( $abbr )
			? $timezone_string
			: $abbr;
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
		if ( self::is_utc_offset( $tzstring ) ) {
			return self::apply_offset( $datetime, $tzstring, true );
		}

		$local = self::get_timezone( $tzstring );
		$utc   = self::get_timezone( 'UTC' );

		$new_datetime = date_create( $datetime, $local );

		if ( $new_datetime && $new_datetime->setTimezone( $utc ) ) {
			return $new_datetime->format( Tribe__Date_Utils::DBDATETIMEFORMAT );
		}

		// Fallback to the unmodified datetime if there was a failure during conversion
		return $datetime;
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
		if ( self::is_utc_offset( $tzstring ) ) {
			return self::apply_offset( $datetime, $tzstring );
		}

		$local = self::get_timezone( $tzstring );
		$utc   = self::get_timezone( 'UTC' );

		$new_datetime = date_create( $datetime, $utc );

		if ( $new_datetime && $new_datetime->setTimezone( $local ) ) {
			return $new_datetime->format( Tribe__Date_Utils::DBDATETIMEFORMAT );
		}

		// Fallback to the unmodified datetime if there was a failure during conversion
		return $datetime;
	}

	/**
	 * Tests to see if the timezone string is a UTC offset, ie "UTC+2".
	 *
	 * @param string $timezone
	 *
	 * @return bool
	 */
	public static function is_utc_offset( $timezone ) {
		$timezone = trim( $timezone );
		return ( 0 === strpos( $timezone, 'UTC' ) && strlen( $timezone ) > 3 );
	}

	/**
	 * @param string $datetime
	 * @param mixed  $offset (string or numeric offset)
	 * @param bool   $invert = false
	 *
	 * @return string
	 */
	public static function apply_offset( $datetime, $offset, $invert = false ) {
		// Normalize
		$offset = strtolower( trim( $offset ) );

		// Strip any leading "utc" text if set
		if ( 0 === strpos( $offset, 'utc' ) ) {
			$offset = substr( $offset, 3 );
		}

		// It's possible no adjustment will be needed
		if ( 0 === $offset ) {
			return $datetime;
		}

		// Convert the offset to minutes for easier handling of fractional offsets
		$offset = (int) ( $offset * 60 );

		// Invert the offset? Useful for stripping an offset that has already been applied
		if ( $invert ) {
			$offset *= -1;
		}

		if ( $offset > 0 ) {
			$offset = '+' . $offset;
		}

		$offset = $offset . ' minutes';

		$offset_datetime = date_create( $datetime );

		if ( $offset_datetime && $offset_datetime->modify( $offset ) ) {
			return $offset_datetime->format( Tribe__Date_Utils::DBDATETIMEFORMAT );
		}

		return $datetime;
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
	 * Returns a timestamp for the event end date that can be passed to tribe_format_date()
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
	 * Returns a timestamp for the event date that can be passed to tribe_format_date()
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
}
