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

if ( ! function_exists( 'tribe_get_display_end_date' ) ) {
	/**
	 * End Date formatted for display
	 *
	 * Returns the event end date that observes the end of day cutoff
	 *
	 * @category Events
	 * @see      http://php.net/manual/en/function.date.php
	 *
	 * @param int|WP_Post $event        The event (optional).
	 * @param bool        $display_time If true shows date and time, if false only shows date.
	 * @param string      $date_format  Allows date and time formatting using standard php syntax.
	 * @param string      $timezone     Timezone in which to present the date/time (or default behaviour if not set).
	 *
	 * @return string|null Date
	 */
	function tribe_get_display_end_date( $event = null, $display_time = true, $date_format = '', $timezone = null ) {
		$timestamp = tribe_get_end_date( $event, true, 'U', $timezone );
		$beginning_of_day = tribe_beginning_of_day( date( Tribe__Date_Utils::DBDATETIMEFORMAT, $timestamp ) );

		if ( tribe_event_is_multiday( $event ) && $timestamp < strtotime( $beginning_of_day ) ) {
			$timestamp -= DAY_IN_SECONDS;
		}

		$formatted_date = tribe_format_date( $timestamp, $display_time, $date_format );

		/**
		 * Filters the displayed end date of an event, which factors in the EOD cutoff.
		 *
		 * @since 4.5.10
		 *
		 * @see tribe_get_display_end_date()
		 *
		 * @param string      $formatted_date Formatted date for the last day of the event.
		 * @param int         $timestamp      Timestamp calculated for the last day of the event.
		 * @param mixed       $event          The event.
		 * @param bool        $display_time   If true shows date and time, if false only shows date.
		 * @param string      $date_format    Allows date and time formatting using standard php syntax.
		 * @param string|null $timezone       Timezone in which to present the date/time (or default behaviour if not set).
		 *
		 */
		return apply_filters( 'tribe_get_display_end_date', $formatted_date, $timestamp, $event, $display_time, $date_format, $timezone );
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
