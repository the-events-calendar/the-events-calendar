<?php

/**
 * @todo [BTRIA-614]: two functions that do the exact same thing
 */

/**
 * Get iCal Link (Single)
 *
 * Returns an iCal feed for a single event. Must be used in the loop.
 *
 * @since 6.1.1 Added $post_id parameter.
 *
 * @param int $post_id (optional) The post ID of the event. If not set, the current post ID will be used.
 *
 * @return string URL for iCal for single event.
 */
function tribe_get_single_ical_link( $post_id = null ) {
	if ( ( null !== $post_id ) && is_numeric( $post_id ) ) {
		$output = add_query_arg( [ 'ical' => 1 ], get_the_permalink( $post_id ) );
	} else {
		$output = tribe( 'tec.iCal' )->get_ical_link( 'single' );
	}

	/**
	 * Filters the "Export Event" iCal link on single events.
	 *
	 * @param string $output The URL for the "Export Event" iCal link on single events.
	 */
	return apply_filters( 'tribe_get_single_ical_link', $output );
}

/**
 * iCal Link
 *
 * Returns a sitewide "Export Events" iCal link.
 *
 * @return string URL for iCal dump.
 */
function tribe_get_ical_link() {
	$output = tribe( 'tec.iCal' )->get_ical_link();

	/**
	 * Filters the "Export Events" iCal link.
	 *
	 * @param string $output The "Export Events" iCal link URL.
	 */
	return apply_filters( 'tribe_get_ical_link', $output );
}

/**
 * Google Calendar Link
 *
 * Returns an "Add to Google Calendar" link for a single event.
 *
 * @param int $post_id (optional) Function must be used in the loop if empty.
 *
 * @return string URL for Google Calendar.
 */
function tribe_get_gcal_link( $post_id = null ) {
	$post_id = Tribe__Events__Main::postIdHelper( $post_id );
	$output  = tribe( \Tribe\Events\Views\V2\iCalendar\Links\Google_Calendar::class )->generate_single_url( $post_id );

	/**
	 * Filters the Google Calendar link
	 *
	 * @param string $output Google Calendar link
	 * @param int $postId WP Post ID of an event
	 */
	return apply_filters( 'tribe_get_gcal_link', $output, $post_id );
}
