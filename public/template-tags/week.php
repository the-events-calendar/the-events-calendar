<?php
/**
 * Events Calendar Pro Week Template Tags
 *
 * Display functions for use in WordPress templates.
 */

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( class_exists( 'TribeEventsPro' ) ) {

	/**
	 * Add attributes to nav elements for week view
	 *
	 * @param string $direction
	 * @param bool   $echo
	 *
	 * @return string
	 * @todo rename to week-specific function name
	 */
	function tribe_events_the_nav_attributes( $direction = 'prev', $echo = true ) {
		global $wp_query;
		$direction    = $direction == 'prev' ? '-' : '+';
		$current_week = tribe_get_first_week_day( $wp_query->get( 'start_date' ) );
		$attributes   = sprintf( ' data-week="%s" ', date( 'Y-m-d', strtotime( $current_week . ' ' . $direction . '7 days' ) ) );
		if ( $echo ) {
			echo $attributes;
		} else {
			return $attributes;
		}
	}

	/**
	 * set the loop type for week view between all day and hourly events
	 *
	 * @param string $loop_type
	 *
	 * @return void
	 */
	function tribe_events_week_set_loop_type( $loop_type = 'hourly' ) {
		Tribe_Events_Pro_Week_Template::reset_the_day();
		Tribe_Events_Pro_Week_Template::$loop_type = $loop_type;
	}

	/**
	 * Whether there are more calendar days available in the loop.
	 *
	 * @return bool True if calendar days are available, false if end of loop.
	 * @return  void
	 * */
	function tribe_events_week_have_days() {
		return Tribe_Events_Pro_Week_Template::have_days();
	}

	/**
	 * increment the current day loop
	 *
	 * @return void
	 */
	function tribe_events_week_the_day() {
		Tribe_Events_Pro_Week_Template::the_day();
	}

	/**
	 * increment the row for the all day map
	 *
	 * @return void
	 */
	function tribe_events_week_the_day_map() {
		Tribe_Events_Pro_Week_Template::the_day_map();
		$all_day_map    = tribe_events_week_get_all_day_map();
		$all_day_offset = Tribe_Events_Pro_Week_Template::get_current_day() < Tribe_Events_Pro_Week_Template::$start_of_week ? Tribe_Events_Pro_Week_Template::$week_length + Tribe_Events_Pro_Week_Template::get_current_day() : Tribe_Events_Pro_Week_Template::get_current_day();
		tribe_events_week_setup_event( $all_day_map[ Tribe_Events_Pro_Week_Template::get_the_day_map() ][ $all_day_offset ] );
	}

	/**
	 * provide a clean way to reset the counter for the all day map row iterator
	 *
	 * @return void
	 */
	function tribe_events_week_reset_the_day_map() {
		Tribe_Events_Pro_Week_Template::reset_the_day_map();
	}

	/**
	 * setup css classes for daily columns in week view
	 *
	 * @return void
	 */
	function tribe_events_week_column_classes() {
		echo apply_filters( 'tribe_events_week_column_classes', Tribe_Events_Pro_Week_Template::column_classes() );
	}

	/**
	 * return the current date of the day set by $current_day
	 *
	 * @param boolean $echo
	 *
	 * @return string $html
	 */
	function tribe_events_week_get_the_date( $echo = true ) {
		$week_days = Tribe_Events_Pro_Week_Template::get_week_days();
		$html      = ! empty( $week_days[ Tribe_Events_Pro_Week_Template::get_current_day() ]->date ) ? $week_days[ Tribe_Events_Pro_Week_Template::get_current_day() ]->date : null;
		$html      = apply_filters( 'tribe_events_week_get_the_date', $html );
		if ( $echo ) {
			echo $html;
		} else {
			return $html;
		}
	}

	/**
	 * display the date in a nice formated view for headers
	 *
	 * @param boolean $echo
	 *
	 * @return string $html
	 */
	function tribe_events_week_get_the_day_display( $echo = true ) {
		$week_days = Tribe_Events_Pro_Week_Template::get_week_days();
		$html      = ! empty( $week_days[ Tribe_Events_Pro_Week_Template::get_current_day() ]->display ) ? $week_days[ Tribe_Events_Pro_Week_Template::get_current_day() ]->display : null;
		$html      = apply_filters( 'tribe_events_week_get_the_day_display', $html );
		if ( $echo ) {
			echo $html;
		} else {
			return $html;
		}
	}

	/**
	 * return if the current day is today
	 *
	 * @return bool
	 */
	function tribe_events_week_is_current_today() {
		$week_days = Tribe_Events_Pro_Week_Template::get_week_days();
		$status    = ! empty( $week_days[ Tribe_Events_Pro_Week_Template::get_current_day() ]->today ) ? $week_days[ Tribe_Events_Pro_Week_Template::get_current_day() ]->today : false;

		return apply_filters( 'tribe_events_week_is_current_today', $status );
	}

	/**
	 * get map of all day events for week view
	 *
	 * @return array of event ids
	 */
	function tribe_events_week_get_all_day_map() {
		$all_day_map = (array) Tribe_Events_Pro_Week_Template::get_events( 'all_day_map' );

		return apply_filters( 'tribe_events_week_get_all_day_map', $all_day_map );
	}

	/**
	 * get array of hourly event objects
	 *
	 * @return array of hourly event objects
	 */
	function tribe_events_week_get_hourly() {
		$hourly_events = (array) Tribe_Events_Pro_Week_Template::get_events( 'hourly' );

		return apply_filters( 'tribe_events_week_get_hourly', $hourly_events );
	}

	/**
	 * set internal mechanism for setting event id for retrieval with other tags
	 *
	 * @param int $event_id
	 *
	 * @return boolean
	 */
	function tribe_events_week_setup_event( $event_id = null ) {
		do_action( 'tribe_events_week_pre_setup_event', $event_id );
		switch ( Tribe_Events_Pro_Week_Template::$loop_type ) {
			case 'allday':
				Tribe_Events_Pro_Week_Template::set_event_id( $event_id );

				return true;
			case 'hourly':
				$event = Tribe_Events_Pro_Week_Template::get_hourly_event( $event_id );
				if ( empty( $event->EventStartDate ) ) {
					return false;
				}
				$calendar_date = tribe_events_week_get_the_date( false );
				// use rounded beginning/end of day because calendar grid only starts on the hour
				$beginning_of_day = tribe_event_beginning_of_day( $calendar_date, 'Y-m-d H:00:00' );
				$end_of_day       = tribe_event_end_of_day( $calendar_date, 'Y-m-d H:00:00' );
				if ( $event->EventStartDate > $end_of_day ) {
					return false;
				}
				if ( $event->EventEndDate <= $beginning_of_day ) {
					return false;
				}
				Tribe_Events_Pro_Week_Template::set_event_id( $event_id );

				return true;
		}

		return false;
	}

	/**
	 * get internal event id pointer
	 *
	 * @return int $event_id
	 */
	function tribe_events_week_get_event_id( $echo = true ) {
		$event_id = apply_filters( 'tribe_events_week_get_event_id', Tribe_Events_Pro_Week_Template::get_event_id() );
		if ( $echo ) {
			echo $event_id;
		} else {
			return $event_id;
		}
	}

	/**
	 * check to see if placeholder should be used in template in place of event block
	 *
	 * @return boolean
	 */
	function tribe_events_week_is_all_day_placeholder() {
		$event_key_id = tribe_events_week_get_event_id( false );
		if ( is_null( $event_key_id ) || in_array( $event_key_id, Tribe_Events_Pro_Week_Template::$event_key_track ) ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * get event object
	 *
	 * @return object
	 */
	function tribe_events_week_get_event() {
		switch ( Tribe_Events_Pro_Week_Template::$loop_type ) {
			case 'allday':
				$event                                             = Tribe_Events_Pro_Week_Template::get_allday_event();
				Tribe_Events_Pro_Week_Template::$event_key_track[] = Tribe_Events_Pro_Week_Template::get_event_id();
				break;
			case 'hourly':
				$event = Tribe_Events_Pro_Week_Template::get_hourly_event();
				break;
		}

		return apply_filters( 'tribe_events_week_get_event', $event );
	}

	function tribe_previous_week_link( $text = '' ) {
		try {
			$date = tribe_get_first_week_day();
			if ( $date <= tribe_events_earliest_date( TribeDateUtils::DBDATEFORMAT ) ) {
				return '';
			}

			$url = tribe_get_last_week_permalink();
			if ( empty( $text ) ) {
				$text = __( '<span>&laquo;</span> Previous Week', 'tribe-events-calendar-pro' );
			}

			if ( ! empty( $url ) ) {
				return sprintf( '<a %s href="%s" rel="prev">%s</a>', tribe_events_the_nav_attributes( 'prev', false ), esc_url( $url ), $text );
			}
		} catch ( OverflowException $e ) {
			return '';
		}
	}

	function tribe_next_week_link( $text = '' ) {
		try {
			$date = date( TribeDateUtils::DBDATEFORMAT, strtotime( tribe_get_first_week_day() . ' +1 week' ) );
			if ( $date >= tribe_events_latest_date( TribeDateUtils::DBDATEFORMAT ) ) {
				return '';
			}

			$url = tribe_get_next_week_permalink();
			if ( empty( $text ) ) {
				$text = __( 'Next Week <span>&raquo;</span>', 'tribe-events-calendar-pro' );
			}

			return sprintf( '<a %s href="%s" rel="next">%s</a>', tribe_events_the_nav_attributes( 'next', false ), esc_url( $url ), $text );
		} catch ( OverflowException $e ) {
			return '';
		}
	}

	/**
	 * For use within the week view template to determine if the current day in the
	 * loop contains events.
	 *
	 * @return bool
	 */
	function tribe_events_current_week_day_has_events() {
		// Do we have any all day events taking place today?
		$day_counter = Tribe_Events_Pro_Week_Template::get_current_day();
		$map         = tribe_events_week_get_all_day_map();
		if ( null !== $map[0][ $day_counter ] ) {
			return true;
		}

		// Do we have any hourly events taking place today?
		$hourly = Tribe_Events_Pro_Week_Template::get_events( 'hourly_map' );

		return empty( $hourly[ $day_counter ] ) ? false : true;
	}

}
