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
function tribe_events_the_nav_attributes( $direction = 'prev', $deprecated = false ) {
	if ( $direction == 'prev' ) {
		_deprecated_function( __FUNCTION__, 3.10, 'tribe_events_week_previous_link' );
	} else {
		_deprecated_function( __FUNCTION__, 3.10, 'tribe_events_week_next_link' );
	}
}

/**
 * return if the current day is today
 *
 * @return bool
 * @deprecated
 */
function tribe_events_week_is_current_today() {
	_deprecated_function( __FUNCTION__, 3.10, '$day = tribe_events_week_get_current_day(); $day[\'is_today\'];' );

	$current_day = tribe_events_week_get_current_day();

	return apply_filters( 'tribe_events_week_is_current_today', $current_day['is_today'] );
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
}

/**
 * increment the row for the all day map
 *
 * @return void
 * @deprecated
 * @todo remove in 4.0
 */
function tribe_events_week_the_day_map() {
	_deprecated_function( __FUNCTION__, 3.10, 'tribe_events_week_the_day' );

}

/**
 * provide a clean way to reset the counter for the all day map row iterator
 *
 * @return void
 * @deprecated
 * @todo remove in 4.0
 */
function tribe_events_week_reset_the_day_map() {
	_deprecated_function( __FUNCTION__, 3.10, 'rewind_days' );
	Tribe_Events_Pro_Week_Template::rewind_days();
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
	_deprecated_function( __FUNCTION__, 3.10, 'tribe_events_week_day_header' );
	$current_day  = tribe_events_week_get_current_day();
	$html = $current_day['formatted_date'];
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
}

/**
 * get internal event id pointer
 *
 * @return int $event_id
 * @deprecated
 * @todo remove in 4.0
 */
function tribe_events_week_get_event_id( $echo = true ) {
	_deprecated_function( __FUNCTION__, 3.10, 'global $post' );
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
	global $post;
	return $post->ID;
}

/**
 * get event object
 *
 * @return object
 * @deprecated
 * @todo remove in 4.0
 */
function tribe_events_week_get_event() {
	_deprecated_function( __FUNCTION__, 3.10, 'global $post' );
	global $post;
	return $post;
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
	_deprecated_function( __FUNCTION__, 3.10, 'tribe_events_week_get_current_day' );
	$current_day = tribe_events_week_get_current_day();
	return $current_day['has_events'];
}

/**
 * Echo html attributes required for proper week view js functionality
 *
 * @return void
 * @deprecated
 * @todo remove in 4.0
 */
function tribe_events_the_week_event_attributes( $event = null ) {
	_deprecated_function( __FUNCTION__, 3.10, 'tribe_events_week_event_attributes' );
	echo tribe_events_week_event_attributes( $event );
}


/**
 * Return the previous week link
 *
 * @return string
 * @deprecated
 * @todo remove in 4.0
 */
function tribe_previous_week_link() {
	_deprecated_function( __FUNCTION__, 3.10, 'tribe_events_week_previous_link' );

	return tribe_events_week_previous_link();
}

/**
 * Return the next week link
 *
 * @return string
 * @deprecated
 * @todo remove in 4.0
 */
function tribe_next_week_link() {
	_deprecated_function( __FUNCTION__, 3.10, 'tribe_events_week_next_link' );

	return tribe_events_week_next_link();
}
