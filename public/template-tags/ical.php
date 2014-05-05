<?php

if ( !function_exists('tribe_get_single_ical_link') ) {
	/**
	 * iCal Link (Single)
	 *
	 * Returns an ical feed for a single event. Must be used in the loop.
	 *
	 * @return string URL for ical for single event.
	 * @since 3.6
	 */
	function tribe_get_single_ical_link() {
		$output = TribeiCal::get_ical_link();
		return apply_filters( 'tribe_get_ical_link', $output );
	}
}

if ( !function_exists('tribe_get_ical_link') ) {
	/**
	 * iCal Link
	 *
	 * Returns a sitewide ical link
	 *
	 * @param $protocol bool|string any alternative protocol for the link, false to leave it unchanged
	 * @return string URL for ical dump.
	 * @since 3.6
	 */
	function tribe_get_ical_link( $protocol = false ) {
		$output = TribeiCal::get_ical_link( $protocol );
		return apply_filters( 'tribe_get_ical_link', $output );
	}
}