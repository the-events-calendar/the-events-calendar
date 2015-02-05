<?php

/**
 * Determine and return attributes on week view nav (needed for week view ajax)
 *
 * @param string $direction
 * @param bool $echo
 *
 * @return string
 * @deprecated
 * @todo remove in 4.0
 */
function tribe_events_the_nav_attributes( $direction = 'prev', $deprecated = true ) {
	_deprecated_function( __FUNCTION__, 3.10 );

	if ( $deprecated ) {
		echo tribe_events_week_the_nav_attributes( $direction );
	} else {
		return tribe_events_week_the_nav_attributes( $direction );
	}
}

/**
 * return if the current day is today
 *
 * @return bool
 * @deprecated
 */
function tribe_events_week_is_current_today() {
	_deprecated_function( __FUNCTION__, 3.10 );
	$week_days = Tribe_Events_Pro_Week_Template::get_week_days();
	$status    = ! empty( $week_days[ Tribe_Events_Pro_Week_Template::get_current_day() ]->today ) ? $week_days[ Tribe_Events_Pro_Week_Template::get_current_day() ]->today : false;

	return apply_filters( 'tribe_events_week_is_current_today', $status );
}

/**
 * set the loop type for week view between all day and hourly events
 *
 * @param string $loop_type
 *
 * @return void
 * @deprecated
 * @todo remove in 4.0
 */
function tribe_events_week_set_loop_type( $loop_type = 'hourly' ) {
	_deprecated_function( __FUNCTION__, 3.10 );
	Tribe_Events_Pro_Week_Template::reset_the_day();
	Tribe_Events_Pro_Week_Template::$loop_type = $loop_type;
}

/**
 * increment the row for the all day map
 *
 * @return void
 * @deprecated
 * @todo remove in 4.0
 */
function tribe_events_week_the_day_map() {
	_deprecated_function( __FUNCTION__, 3.10 );
	Tribe_Events_Pro_Week_Template::the_day_map();
	$all_day_map    = tribe_events_week_get_all_day_map();
	$all_day_offset = Tribe_Events_Pro_Week_Template::get_current_day() < Tribe_Events_Pro_Week_Template::$start_of_week ? 7 + Tribe_Events_Pro_Week_Template::get_current_day() : Tribe_Events_Pro_Week_Template::get_current_day();
	tribe_events_week_setup_event( $all_day_map[ Tribe_Events_Pro_Week_Template::get_the_day_map() ][ $all_day_offset ] );
}

/**
 * provide a clean way to reset the counter for the all day map row iterator
 *
 * @return void
 * @deprecated
 * @todo remove in 4.0
 */
function tribe_events_week_reset_the_day_map() {
	_deprecated_function( __FUNCTION__, 3.10 );
	Tribe_Events_Pro_Week_Template::reset_the_day_map();
}
/**
 * display the date in a nice formated view for headers
 *
 * @param boolean $echo
 *
 * @return string $html
 * @deprecated
 * @todo remove in 4.0
 */
function tribe_events_week_get_the_day_display( $echo = true ) {
	_deprecated_function( __FUNCTION__, 3.10 );
	$week_days = Tribe_Events_Pro_Week_Template::get_week_days();
	$html      = ! empty( $week_days[ Tribe_Events_Pro_Week_Template::get_current_day() ]->display ) ? $week_days[ Tribe_Events_Pro_Week_Template::get_current_day() ]->display : null;
	if ( has_filter( 'tribe_events_week_get_the_day_display' ) ) {
		_deprecated_function( "The 'tribe_events_week_get_the_day_display' filter", '3.9', " the 'tribe_events_pro_week_header_date_format' filter" );
		$html      = apply_filters( 'tribe_events_week_get_the_day_display', $html );
	}
	if ( $echo ) {
		echo $html;
	} else {
		return $html;
	}
}

/**
 * get map of all day events for week view
 *
 * @return array of event ids
 * @deprecated
 * @todo remove in 4.0
 */
function tribe_events_week_get_all_day_map() {
	_deprecated_function( __FUNCTION__, 3.10 );
	$all_day_map = (array) Tribe_Events_Pro_Week_Template::get_events( 'all_day_map' );
	if ( has_filter( 'tribe_events_week_get_all_day_map' ) ) {
		_deprecated_function( "The 'tribe_events_week_get_all_day_map' filter", '3.9' );
	}
	return apply_filters( 'tribe_events_week_get_all_day_map', $all_day_map );
}

/**
 * get array of hourly event objects
 *
 * @return array of hourly event objects
 * @deprecated
 * @todo remove in 4.0
 */
function tribe_events_week_get_hourly() {
	_deprecated_function( __FUNCTION__, 3.10 );
	$hourly_events = (array) Tribe_Events_Pro_Week_Template::get_events( 'hourly' );

	if ( has_filter( 'tribe_events_week_get_hourly' ) ) {
		_deprecated_function( "The 'tribe_events_week_get_hourly' filter", '3.9' );
	}
	return apply_filters( 'tribe_events_week_get_hourly', $hourly_events );
}

/**
 * set internal mechanism for setting event id for retrieval with other tags
 *
 * @param int $event_id
 *
 * @return boolean
 * @deprecated
 * @todo remove in 4.0
 */
function tribe_events_week_setup_event( $event_id = null ) {
	_deprecated_function( __FUNCTION__, 3.10 );
	if ( has_action( 'tribe_events_week_pre_setup_event' ) ) {
		_deprecated_function( "The 'tribe_events_week_pre_setup_event' filter", '3.9' );
		do_action( 'tribe_events_week_pre_setup_event', $event_id );
	}
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
 * @deprecated
 * @todo remove in 4.0
 */
function tribe_events_week_get_event_id( $echo = true ) {
	_deprecated_function( __FUNCTION__, 3.10 );
	$event_id = Tribe_Events_Pro_Week_Template::get_event_id();
	if ( has_filter( 'tribe_events_week_get_event_id' ) ) {
		_deprecated_function( "The 'tribe_events_week_get_event_id' filter", '3.9' );
		$event_id = apply_filters( 'tribe_events_week_get_event_id', $event_id );
	}
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
 * @deprecated
 * @todo remove in 4.0
 */
function tribe_events_week_is_all_day_placeholder() {
	_deprecated_function( __FUNCTION__, 3.10 );
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
 * @deprecated
 * @todo remove in 4.0
 */
function tribe_events_week_get_event() {
	_deprecated_function( __FUNCTION__, 3.10 );
	switch ( Tribe_Events_Pro_Week_Template::$loop_type ) {
		case 'allday':
			$event                                             = Tribe_Events_Pro_Week_Template::get_allday_event();
			break;
		case 'hourly':
			$event = Tribe_Events_Pro_Week_Template::get_hourly_event();
			break;
	}
	if ( has_filter( 'tribe_events_week_get_event' ) ) {
		_deprecated_function( "The 'tribe_events_week_get_event' filter", '3.9' );
		$event = apply_filters( 'tribe_events_week_get_event', $event );
	}
	return $event;
}

/**
 * For use within the week view template to determine if the current day in the
 * loop contains events.
 *
 * @return bool
 * @deprecated
 * @todo remove in 4.0
 */
function tribe_events_current_week_day_has_events() {
	_deprecated_function( __FUNCTION__, 3.10 );
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
