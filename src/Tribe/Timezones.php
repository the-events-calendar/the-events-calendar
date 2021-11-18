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
			add_filter( 'tribe_events_event_schedule_details_inner', [ __CLASS__, 'append_timezone' ], 10, 2 );
			add_filter( 'tribe_events_event_short_schedule_details_inner', [ __CLASS__, 'append_timezone' ], 10, 2 );
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
	 * Returns a timestamp for the event start date that can be passed to tribe_format_date()
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
		static $cache_var_name = __METHOD__;

		$timestamps = tribe_get_var( $cache_var_name, [] );

		$cache_key = "{$event_id}:{$type}:{$timezone}";

		if ( isset( $timestamps[ $cache_key ] ) ) {
			return $timestamps[ $cache_key ];
		}

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
			$datetime = get_post_meta( $event->ID, "_Event{$type}Date", true );

			return $timestamps[ $cache_key ] = strtotime( $datetime );
		}

		// Otherwise lets load the event's UTC time and convert it
		$datetime = isset( $event->{"Event{$type}DateUTC"} )
			? $event->{"Event{$type}DateUTC"}
			: get_post_meta( $event->ID, "_Event{$type}DateUTC", true );

		$tzstring = ( self::SITE_TIMEZONE === $timezone )
			? self::wp_timezone_string()
			: $timezone;

		$localized = self::to_tz( $datetime, $tzstring );
		$timestamps[ $cache_key ] = strtotime( $localized );

		tribe_set_var( $cache_var_name, $timestamps );

		return $timestamps[ $cache_key ];
	}

	/**
	 * Returns a string representing the timezone/offset currently desired for
	 * the display of dates and times.
	 *
	 * @return string
	 */
	public static function mode() {
		$mode = self::EVENT_TIMEZONE;

		if ( 'site' === tribe_get_option( 'tribe_events_timezone_mode' ) ) {
			$mode = self::SITE_TIMEZONE;
		}

		return apply_filters( 'tribe_events_current_display_timezone', $mode );
	}

	/**
	 * Confirms if the current timezone mode matches the $possible_mode.
	 *
	 * @param string $possible_mode
	 *
	 * @return bool
	 */
	public static function is_mode( $possible_mode ) {
		return $possible_mode === self::mode();
	}
}
