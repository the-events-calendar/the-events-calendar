<?php
/**
 * Single Day Test
 *
 * Returns true if the query is set for single day, false otherwise
 *
 * @category Events
 * @return bool
 */
function tribe_is_day() {
	$tribe_ecp = Tribe__Events__Main::instance();
	$is_day    = ( $tribe_ecp->displaying == 'day' ) ? true : false;

	return apply_filters( 'tribe_is_day', $is_day );
}

/**
 * Link Event Day
 *
 * @category Events
 * @param string $date
 *
 * @return string URL
 */
function tribe_get_day_link( $date = null ) {
	$tribe_ecp = Tribe__Events__Main::instance();

	return apply_filters( 'tribe_get_day_link', $tribe_ecp->getLink( 'day', $date ), $date );
}

/**
 * Get the next day's date
 *
 * @category Events
 *
 * @return string
 */
function tribe_get_next_day_date() {
	return null;
}

/**
 * Get the previous day's date
 *
 * @category Events
 *
 * @return string
 */
function tribe_get_previous_day_date( ) {
	return null;
}
