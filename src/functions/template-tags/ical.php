<?php

// @TODO two functions that do the exact same thing
if ( ! function_exists( 'tribe_get_single_ical_link' ) ) {
	/**
	 * iCal Link (Single)
	 *
	 * Returns an ical feed for a single event. Must be used in the loop.
	 *
	 * @return string URL for ical for single event.
	 */
	function tribe_get_single_ical_link() {
		$output = tribe( 'tec.iCal' )->get_ical_link( 'single' );

		/**
		 * Filters the "Export Event" iCal link on single events.
		 *
		 * @param string $output The URL for the "Export Event" iCal link on single events.
		 */
		return apply_filters( 'tribe_get_single_ical_link', $output );
	}
}

if ( ! function_exists( 'tribe_get_ical_link' ) ) {
	/**
	 * iCal Link
	 *
	 * Returns a sitewide "Export Events" iCal link
	 *
	 * @return string URL for ical dump.
	 */
	function tribe_get_ical_link() {
		$output = tribe( 'tec.iCal' )->get_ical_link();

		/**
		 * Filters the "Export Events" iCal link.
		 *
		 * Please note that tribe-events.js dynamically sets the iCal link in most contexts. To
		 * override this behavior so that a custom link from the tribe_get_ical_link filter is the
		 * one that's always used, please also use the tribe_events_force_filtered_ical_link filter.
		 *
		 * @see tribe_events_force_filtered_ical_link
		 * @param string $output The "Export Events" iCal link URL.
		 */
		return apply_filters( 'tribe_get_ical_link', $output );
	}
}

if ( ! function_exists( 'tribe_get_gcal_link' ) ) {
	/**
	 * Google Calendar Link
	 *
	 * Returns an "Add to Google Calendar" link for a single event. Must be used in the loop.
	 *
	 * @param int $postId (optional)
	 *
	 * @return string URL for google calendar.
	 */
	function tribe_get_gcal_link( $postId = null ) {
		$postId = Tribe__Events__Main::postIdHelper( $postId );
		$output = Tribe__Events__Main::instance()->googleCalendarLink( $postId );

		/**
		 * Filters the Google Calendar gcal link
		 *
		 * @param string $output Gcal link
		 * @param int $postId WP Post ID of an event
		 */
		return apply_filters( 'tribe_get_gcal_link', $output, $postId );
	}
}