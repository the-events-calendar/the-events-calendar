<?php
/**
 * Date Functions
 *
 * Display functions (template-tags) for use in WordPress templates.
 */

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( ! class_exists( 'Tribe__Events__Main' ) ) {
	return;
}

if ( ! function_exists( 'tribe_get_start_time' ) ) {
	/**
	 * Start Time
	 *
	 * Returns the event start time
	 *
	 * @category Events
	 * @param int    $event       (optional)
	 * @param string $dateFormat  Allows date and time formating using standard php syntax (http://php.net/manual/en/function.date.php)
	 * @param string $timezone    Timezone in which to present the date/time (or default behaviour if not set)
	 *
	 * @return string|null Time
	 */
	function tribe_get_start_time( $event = null, $dateFormat = '', $timezone = null ) {
		if ( is_null( $event ) ) {
			global $post;
			$event = $post;
		}

		if ( is_numeric( $event ) ) {
			$event = get_post( $event );
		}

		if ( ! is_object( $event ) ) {
			return;
		}

		if ( tribe_event_is_all_day( $event ) ) {
			return;
		}

		$start_date = Tribe__Events__Timezones::event_start_timestamp( $event->ID, $timezone );

		if ( '' == $dateFormat ) {
			$dateFormat = tribe_get_time_format();
		}

		return tribe_format_date( $start_date, false, $dateFormat );
	}
}

if ( ! function_exists( 'tribe_get_end_time' ) ) {
	/**
	 * End Time
	 *
	 * Returns the event end time
	 *
	 * @category Events
	 * @param int    $event       (optional)
	 * @param string $dateFormat  Allows date and time formating using standard php syntax (http://php.net/manual/en/function.date.php)
	 * @param string $timezone    Timezone in which to present the date/time (or default behaviour if not set)
	 *
	 * @return string|null Time
	 */
	function tribe_get_end_time( $event = null, $dateFormat = '', $timezone = null ) {
		if ( is_null( $event ) ) {
			global $post;
			$event = $post;
		}

		if ( is_numeric( $event ) ) {
			$event = get_post( $event );
		}

		if ( ! is_object( $event ) ) {
			return;
		}

		if ( tribe_event_is_all_day( $event ) ) {
			return;
		}

		$end_date = Tribe__Events__Timezones::event_end_timestamp( $event->ID, $timezone );

		if ( '' == $dateFormat ) {
			$dateFormat = tribe_get_time_format();
		}

		return tribe_format_date( $end_date, false, $dateFormat );
	}
}

if ( ! function_exists( 'tribe_get_start_date' ) ) {
	/**
	 * Start Date
	 *
	 * Returns the event start date and time
	 *
	 * @category Events
	 * @param int    $event       (optional)
	 * @param bool   $displayTime If true shows date and time, if false only shows date
	 * @param string $dateFormat  Allows date and time formating using standard php syntax (http://php.net/manual/en/function.date.php)
	 * @param string $timezone    Timezone in which to present the date/time (or default behaviour if not set)
	 * @return string|null Date
	 */
	function tribe_get_start_date( $event = null, $displayTime = true, $dateFormat = '', $timezone = null ) {
		if ( is_null( $event ) ) {
			global $post;
			$event = $post;
		}

		if ( is_numeric( $event ) ) {
			$event = get_post( $event );
		}

		if ( ! is_object( $event ) ) {
			return '';
		}

		if ( tribe_event_is_all_day( $event ) ) {
			$displayTime = false;
		}

		$start_date = Tribe__Events__Timezones::event_start_timestamp( $event->ID, $timezone );
		return tribe_format_date( $start_date, $displayTime, $dateFormat );
	}
}

if ( ! function_exists( 'tribe_get_end_date' ) ) {
	/**
	 * End Date
	 *
	 * Returns the event end date
	 *
	 * @category Events
	 * @param int    $event       (optional)
	 * @param bool   $displayTime If true shows date and time, if false only shows date
	 * @param string $dateFormat  Allows date and time formating using standard php syntax (http://php.net/manual/en/function.date.php)
	 * @param string $timezone    Timezone in which to present the date/time (or default behaviour if not set)
	 *
	 * @return string|null Date
	 */
	function tribe_get_end_date( $event = null, $displayTime = true, $dateFormat = '', $timezone = null ) {
		if ( is_null( $event ) ) {
			global $post;
			$event = $post;
		}

		if ( is_numeric( $event ) ) {
			$event = get_post( $event );
		}

		if ( ! is_object( $event ) ) {
			return '';
		}

		if ( tribe_event_is_all_day( $event ) ) {
			$displayTime = false;
		}

		$end_date = Tribe__Events__Timezones::event_end_timestamp( $event->ID, $timezone );
		return tribe_format_date( $end_date, $displayTime, $dateFormat );
	}
}

if ( ! function_exists( 'tribe_event_is_on_date' ) ) {
	/**
	 * Given a date and an event, returns true or false if the event is happening on that date
	 * This function properly adjusts for the EOD cutoff and multi-day events
	 *
	 * @param null $date
	 * @param null $event
	 *
	 * @return mixed|void
	 */
	function tribe_event_is_on_date( $date = null, $event = null ) {

		if ( null === $date ) {
			$date = current_time( 'mysql' );
		}

		if ( null === $event ) {
			global $post;
			$event = $post;
			if ( empty( $event ) ) {
				_doing_it_wrong( __FUNCTION__, esc_html__( 'The function needs to be passed an $event or used in the loop.', 'the-events-calendar' ) );
				return false;
			}
		}

		$start_of_day     = tribe_beginning_of_day( $date, 'U' );
		$end_of_day       = tribe_end_of_day( $date, 'U' );
		$event_start      = tribe_get_start_date( $event, null, 'U' );
		$event_end        = tribe_get_end_date( $event, null, 'U' );

		// kludge
		if ( ! empty( $event->_end_date_fixed ) ) {
			// @todo remove this once we can have all day events without a start / end time
			$event_end = date_create( date( Tribe__Date_Utils::DBDATETIMEFORMAT, $event_end ) );
			$event_end->modify( '+1 day' );
			$event_end    = $event_end->format( 'U' );
		}

		/* note:
		 * events that start exactly on the EOD cutoff will count on the following day
		 * events that end exactly on the EOD cutoff will count on the previous day
		 */

		$event_is_on_date = Tribe__Date_Utils::range_coincides( $start_of_day, $end_of_day, $event_start, $event_end );

		return apply_filters( 'tribe_event_is_on_date', $event_is_on_date, $date, $event );
	}
}