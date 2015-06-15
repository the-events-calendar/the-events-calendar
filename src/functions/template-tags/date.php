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

if ( class_exists( 'Tribe__Events__Main' ) ) {

	/**
	 * Start Time
	 *
	 * Returns the event start time
	 *
	 * @category Events
	 * @param int    $event       (optional)
	 * @param string $dateFormat  Allows date and time formating using standard php syntax (http://php.net/manual/en/function.date.php)
	 *
	 * @return string|null Time
	 */
	function tribe_get_start_time( $event = null, $dateFormat = '' ) {
		if ( is_null( $event ) ) {
			global $post;
			$event = $post;
		}
		if ( is_numeric( $event ) ) {
			$event = get_post( $event );
		}

		if ( tribe_event_is_all_day( $event ) ) {
			return ;
		}

		if ( empty( $event->EventStartDate ) && is_object( $event ) ) {
			$event->EventStartDate = tribe_get_event_meta( $event->ID, '_EventStartDate', true );
		}

		if ( isset( $event->EventStartDate ) ) {
			$date = strtotime( $event->EventStartDate );
		} else {
			return;
		}

		if ( '' == $dateFormat ) {
			$dateFormat = tribe_get_time_format();
		}

		return tribe_event_format_date( $date, false, $dateFormat );
	}

	/**
	 * End Time
	 *
	 * Returns the event end time
	 *
	 * @category Events
	 * @param int    $event       (optional)
	 * @param string $dateFormat  Allows date and time formating using standard php syntax (http://php.net/manual/en/function.date.php)
	 *
	 * @return string|null Time
	 */
	function tribe_get_end_time( $event = null, $dateFormat = '' ) {
		if ( is_null( $event ) ) {
			global $post;
			$event = $post;
		}
		if ( is_numeric( $event ) ) {
			$event = get_post( $event );
		}

		if ( tribe_event_is_all_day( $event ) ) {
			return;
		}

		if ( empty( $event->EventEndDate ) && is_object( $event ) ) {
			$event->EventEndDate = tribe_get_event_meta( $event->ID, '_EventEndDate', true );
		}

		if ( isset( $event->EventEndDate ) ) {
			$date = strtotime( $event->EventEndDate );
		} else {
			return;
		}

		if ( '' == $dateFormat ) {
			$dateFormat = tribe_get_time_format();
		}

		return tribe_event_format_date( $date, false, $dateFormat );
	}

	/**
	 * Start Date
	 *
	 * Returns the event start date and time
	 *
	 * @category Events
	 * @param int    $event       (optional)
	 * @param bool   $displayTime If true shows date and time, if false only shows date
	 * @param string $dateFormat  Allows date and time formating using standard php syntax (http://php.net/manual/en/function.date.php)
	 *
	 * @return string|null Date
	 */
	function tribe_get_start_date( $event = null, $displayTime = true, $dateFormat = '' ) {
		if ( is_null( $event ) ) {
			global $post;
			$event = $post;
		}
		if ( is_numeric( $event ) ) {
			$event = get_post( $event );
		}

		if ( tribe_event_is_all_day( $event ) ) {
			$displayTime = false;
		}

		if ( empty( $event->EventStartDate ) && is_object( $event ) ) {
			$event->EventStartDate = tribe_get_event_meta( $event->ID, '_EventStartDate', true );
		}

		if ( isset( $event->EventStartDate ) ) {
			$date = strtotime( $event->EventStartDate );
		} else {
			return;
		}

		return tribe_event_format_date( $date, $displayTime, $dateFormat );
	}

	/**
	 * End Date
	 *
	 * Returns the event end date
	 *
	 * @category Events
	 * @param int    $event       (optional)
	 * @param bool   $displayTime If true shows date and time, if false only shows date
	 * @param string $dateFormat  Allows date and time formating using standard php syntax (http://php.net/manual/en/function.date.php)
	 *
	 * @return string|null Date
	 */
	function tribe_get_end_date( $event = null, $displayTime = true, $dateFormat = '' ) {
		if ( is_null( $event ) ) {
			global $post;
			$event = $post;
		}
		if ( is_numeric( $event ) ) {
			$event = get_post( $event );
		}

		if ( tribe_event_is_all_day( $event ) ) {
			$displayTime = false;
		}

		if ( empty( $event->EventEndDate ) && is_object( $event ) ) {
			$event->EventEndDate = tribe_get_event_meta( $event->ID, '_EventEndDate', true );
		}

		if ( isset( $event->EventEndDate ) ) {
			if (
				tribe_event_is_all_day( $event ) &&
				empty( $event->_end_date_fixed ) &&
				'23:59:59' !== Tribe__Events__Date_Utils::time_only( $event->EventEndDate ) &&
				'23:59:59' !== Tribe__Events__Date_Utils::time_only( tribe_event_end_of_day() )
			) {
				// set the event end date to be one day earlier, if it's an all day event and the cutoff is past midnight
				// @todo remove this once we can have all day events without a start / end time
				$event->EventEndDate = date_create( $event->EventEndDate );
				$event->EventEndDate->modify( '-1 day' );
				$event->EventEndDate    = $event->EventEndDate->format( Tribe__Events__Date_Utils::DBDATEFORMAT );
				$event->_end_date_fixed = true;
			}
			$date = strtotime( $event->EventEndDate );
		} else {
			return;
		}

		return tribe_event_format_date( $date, $displayTime, $dateFormat );
	}

	/**
	 * Formatted Date
	 *
	 * Returns formatted date
	 *
	 * @category Events
	 * @param string $date
	 * @param bool   $displayTime If true shows date and time, if false only shows date
	 * @param string $dateFormat  Allows date and time formating using standard php syntax (http://php.net/manual/en/function.date.php)
	 *
	 * @return string
	 */
	function tribe_event_format_date( $date, $displayTime = true, $dateFormat = '' ) {

		if ( ! Tribe__Events__Date_Utils::is_timestamp( $date ) ) {
			$date = strtotime( $date );
		}

		if ( $dateFormat ) {
			$format = $dateFormat;
		} else {
			$date_year = date( 'Y', $date );
			$cur_year  = date( 'Y', current_time( 'timestamp' ) );

			// only show the year in the date if it's not in the current year
			$with_year = $date_year == $cur_year ? false : true;

			if ( $displayTime ) {
				$format = tribe_get_datetime_format( $with_year );
			} else {
				$format = tribe_get_date_format( $with_year );
			}
		}

		$date = date_i18n( $format, $date );

		return apply_filters( 'tribe_event_formatted_date', $date, $displayTime, $dateFormat );

	}

	/**
	 * Returns formatted date for the official beginning of the day according to the Multi-day cutoff time option
	 *
	 * @category Events
	 * @param string $date   The date to find the beginning of the day, defaults to today
	 * @param string $format Allows date and time formating using standard php syntax (http://php.net/manual/en/function.date.php)
	 *
	 * @return string
	 */
	function tribe_event_beginning_of_day( $date = null, $format = 'Y-m-d H:i:s' ) {
		$multiday_cutoff = explode( ':', tribe_get_option( 'multiDayCutoff', '00:00' ) );
		$hours_to_add    = $multiday_cutoff[0];
		$minutes_to_add  = $multiday_cutoff[1];
		if ( is_null( $date ) || empty( $date ) ) {
			return apply_filters( 'tribe_event_beginning_of_day', date( $format, strtotime( date( 'Y-m-d' ) . ' +' . $hours_to_add . ' hours ' . $minutes_to_add . ' minutes' ) ) );
		} else {
			return apply_filters( 'tribe_event_beginning_of_day', date( $format, strtotime( date( 'Y-m-d', strtotime( $date ) ) . ' +' . $hours_to_add . ' hours ' . $minutes_to_add . ' minutes' ) ) );
		}
	}

	/**
	 * Returns formatted date for the official end of the day according to the Multi-day cutoff time option
	 *
	 * @category Events
	 * @param string $date   The date to find the end of the day, defaults to today
	 * @param string $format Allows date and time formating using standard php syntax (http://php.net/manual/en/function.date.php)
	 *
	 * @return string
	 */
	function tribe_event_end_of_day( $date = null, $format = 'Y-m-d H:i:s' ) {
		$multiday_cutoff = explode( ':', tribe_get_option( 'multiDayCutoff', '00:00' ) );
		$hours_to_add    = $multiday_cutoff[0];
		$minutes_to_add  = $multiday_cutoff[1];
		if ( is_null( $date ) || empty( $date ) ) {
			return apply_filters( 'tribe_event_end_of_day', date( $format, strtotime( 'tomorrow  +' . $hours_to_add . ' hours ' . $minutes_to_add . ' minutes' ) - 1 ) );
		} else {
			return apply_filters( 'tribe_event_end_of_day', date( $format, strtotime( date( 'Y-m-d', strtotime( $date ) ) . ' +1 day ' . $hours_to_add . ' hours ' . $minutes_to_add . ' minutes' ) - 1 ) );
		}
	}

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
				_doing_it_wrong( __FUNCTION__, __( 'The function needs to be passed an $event or used in the loop.', 'tribe-events-calendar' ) );
				return false;
			}
		}

		$event_is_on_date = false;
		$start_of_day     = tribe_event_beginning_of_day( $date, 'U' );
		$end_of_day       = tribe_event_end_of_day( $date, 'U' );
		$event_start      = tribe_get_start_date( $event, null, 'U' );
		$event_end        = tribe_get_end_date( $event, null, 'U' );

		// kludge
		if ( ! empty( $event->_end_date_fixed ) ) {
			// @todo remove this once we can have all day events without a start / end time
			$event_end = date_create( date( Tribe__Events__Date_Utils::DBDATETIMEFORMAT, $event_end ) );
			$event_end->modify( '+1 day' );
			$event_end    = $event_end->format( 'U' );
		}

		/**
		 * conditions:
		 * event starts on this day (event start time is between start and end of day)
		 * event ends on this day (event end time is between start and end of day)
		 * event starts before start of day and ends after end of day (spans across this day)
		 * note:
		 * events that start exactly on the EOD cutoff will count on the following day
		 * events that end exactly on the EOD cutoff will count on the previous day
		 */

		$event_starts_today       = $event_start >= $start_of_day && $event_start < $end_of_day;
		$event_ends_today         = $event_end > $start_of_day && $event_end <= $end_of_day;
		$event_spans_across_today = $event_start < $start_of_day && $event_end > $end_of_day;

		if ( $event_starts_today || $event_ends_today || $event_spans_across_today ) {
			$event_is_on_date = true;
		}

		return apply_filters( 'tribe_event_is_on_date', $event_is_on_date, $date, $event );

	}


	/**
	 * Get the datetime saparator from the database option with escaped characters or not ;)
	 *
	 * @param string $default Default Separator if it's blank on the Database
	 * @param bool $esc If it's going to be used on a `date` function or method it needs to be escaped
	 *
	 * @filter tribe_datetime_separator
	 *
	 * @return string
	 */
	function tribe_get_datetime_separator( $default = ' @ ', $esc = false ) {
		$separator = (string) tribe_get_option( 'dateTimeSeparator', $default );
		if ( $esc ) {
			$separator = (array) str_split( $separator );
			$separator = ( ! empty( $separator ) ? '\\' : '' ) . implode( '\\', $separator );
		}
		return apply_filters( 'tribe_datetime_separator', $separator );
	}

}
