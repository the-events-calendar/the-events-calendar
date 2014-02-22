<?php
/**
 * Events Calendar Pro Week Template Tags
 *
 * Display functions for use in WordPress templates.
 */

// Don't load directly
if ( !defined( 'ABSPATH' ) )
	die( '-1' );

if ( class_exists( 'TribeEventsPro' ) ) {

	function tribe_events_the_nav_attributes( $direction = 'prev', $echo = true ){
		global $wp_query;
		$direction = $direction == 'prev' ? '-' : '+';
		$current_week = tribe_get_first_week_day( $wp_query->get( 'start_date' ) );
		$attributes = sprintf(' data-week="%s" ', date( 'Y-m-d', strtotime( $current_week . ' ' . $direction . '7 days' ) ) );
		if( $echo ){
			echo $attributes;
		} else {
			return $attributes;
		}
	}

	/**
	 * set the loop type for week view between all day and hourly events
	 *
	 * @since  3.0
	 * @author tim@imaginesimplicty.com
	 * @param string  $loop_type
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
	 * @since 3.0
	 * @return  void
	 * */
	function tribe_events_week_have_days() {
		return Tribe_Events_Pro_Week_Template::have_days();
	}

	/**
	 * increment the current day loop
	 *
	 * @since 3.0
	 * @author tim@imaginesimplicity.com
	 * @return void
	 */
	function tribe_events_week_the_day() {
		Tribe_Events_Pro_Week_Template::the_day();
	}

	/**
	 * increment the row for the all day map
	 *
	 * @since 3.0
	 * @author tim@imaginesimplicity.com
	 * @return void
	 */
	function tribe_events_week_the_day_map() {
		Tribe_Events_Pro_Week_Template::the_day_map();
		$all_day_map = tribe_events_week_get_all_day_map();
		$all_day_offset = Tribe_Events_Pro_Week_Template::get_current_day() < Tribe_Events_Pro_Week_Template::$start_of_week ? Tribe_Events_Pro_Week_Template::$week_length + Tribe_Events_Pro_Week_Template::get_current_day() : Tribe_Events_Pro_Week_Template::get_current_day();
		tribe_events_week_setup_event( $all_day_map[ Tribe_Events_Pro_Week_Template::get_the_day_map() ][ $all_day_offset ] );
	}

	/**
	 * provide a clean way to reset the counter for the all day map row iterator
	 *
	 * @since 3.0
	 * @author tim@imaginesimplicity.com
	 * @return void
	 */
	function tribe_events_week_reset_the_day_map() {
		Tribe_Events_Pro_Week_Template::reset_the_day_map();
	}

	/**
	 * setup css classes for daily columns in week view
	 *
	 * @since  3.0
	 * @author tim@imaginesimplicty.com
	 * @return void
	 */
	function tribe_events_week_column_classes() {
		echo apply_filters( 'tribe_events_week_column_classes', Tribe_Events_Pro_Week_Template::column_classes() );
	}

	/**
	 * return the current date of the day set by $current_day
	 *
	 * @param boolean $echo
	 * @return string $html
	 */
	function tribe_events_week_get_the_date( $echo = true ) {
		$week_days = Tribe_Events_Pro_Week_Template::get_week_days();
		$html = !empty( $week_days[ Tribe_Events_Pro_Week_Template::get_current_day() ]->date ) ? $week_days[ Tribe_Events_Pro_Week_Template::get_current_day() ]->date : null;
		$html = apply_filters( 'tribe_events_week_get_the_date', $html );
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
	 * @return string $html
	 */
	function tribe_events_week_get_the_day_display( $echo = true ) {
		$week_days = Tribe_Events_Pro_Week_Template::get_week_days();
		$html = !empty( $week_days[ Tribe_Events_Pro_Week_Template::get_current_day() ]->display ) ? $week_days[ Tribe_Events_Pro_Week_Template::get_current_day() ]->display : null;
		$html = apply_filters( 'tribe_events_week_get_the_day_display', $html );
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
		$status = !empty( $week_days[ Tribe_Events_Pro_Week_Template::get_current_day() ]->today ) ? $week_days[ Tribe_Events_Pro_Week_Template::get_current_day() ]->today : false;
		return apply_filters( 'tribe_events_week_is_current_today', $status );
	}

	/**
	 * get map of all day events for week view
	 *
	 * @since  3.0
	 * @author tim@imaginesimplicty.com
	 * @return array of event ids
	 */
	function tribe_events_week_get_all_day_map() {
		$all_day_map = (array) Tribe_Events_Pro_Week_Template::get_events( 'all_day_map' );
		return apply_filters( 'tribe_events_week_get_all_day_map', $all_day_map );
	}

	/**
	 * get array of hourly event objects
	 *
	 * @since  3.0
	 * @author tim@imaginesimplicty.com
	 * @return array of hourly event objects
	 */
	function tribe_events_week_get_hourly() {
		$hourly_events = (array) Tribe_Events_Pro_Week_Template::get_events( 'hourly' );
		return apply_filters( 'tribe_events_week_get_hourly', $hourly_events );
	}

	/**
	 * set internal mechanism for setting event id for retrieval with other tags
	 *
	 * @since  3.0
	 * @author tim@imaginesimplicty.com
	 * @param int     $event_id
	 * @return boolean
	 */
	function tribe_events_week_setup_event( $event_id = null ) {
		do_action( 'tribe_events_week_pre_setup_event', $event_id);
		switch ( Tribe_Events_Pro_Week_Template::$loop_type ) {
		case 'allday':
			Tribe_Events_Pro_Week_Template::set_event_id( $event_id );
			return true;
			break;
		case 'hourly':
			$event = Tribe_Events_Pro_Week_Template::get_hourly_event( $event_id );
			if ( !empty( $event->EventStartDate ) && date( 'Y-m-d', strtotime( $event->EventStartDate ) ) <= tribe_events_week_get_the_date( false ) && date( 'Y-m-d', strtotime( $event->EventEndDate ) ) >= tribe_events_week_get_the_date( false ) ) {
				Tribe_Events_Pro_Week_Template::set_event_id( $event_id );
				return true;
			} else {
				return false;
			}
			break;
		}
		return false;
	}

	/**
	 * get internal event id pointer
	 *
	 * @since  3.0
	 * @author tim@imaginesimplicty.com
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
	 * @since  3.0
	 * @author tim@imaginesimplicty.com
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
	 * @since  3.0
	 * @author tim@imaginesimplicty.com
	 * @return object
	 */
	function tribe_events_week_get_event() {
		switch ( Tribe_Events_Pro_Week_Template::$loop_type ) {
		case 'allday':
			$event = Tribe_Events_Pro_Week_Template::get_allday_event();
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
			$url = tribe_get_last_week_permalink();
			if ( empty($text) ) {
				$text = __('&laquo; Previous Week', 'tribe-events-calendar-pro' );
			}
			$html = sprintf( '<a %s href="%s" rel="prev">%s</a>', tribe_events_the_nav_attributes( 'prev', false ), $url, $text );
		} catch ( OverflowException $e ) {
			$html = '';
		}
		return $html;
	}

	function tribe_next_week_link( $text = '' ) {
		try {
			$url = tribe_get_next_week_permalink();
			if ( empty($text) ) {
				$text = __( 'Next Week &raquo;', 'tribe-events-calendar-pro' );
			}
			$html = sprintf( '<a %s href="%s" rel="next">%s</a>', tribe_events_the_nav_attributes( 'next', false ), $url, $text );
		} catch ( OverflowException $e ) {
			$html = '';
		}
		return $html;
	}

}
