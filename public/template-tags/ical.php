<?php

// @TODO two functions that do the exact same thing
// @TODO move these to the link.php template tags file
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
	 * @return string URL for ical dump.
	 * @since 3.6
	 */
	function tribe_get_ical_link() {
		$output = TribeiCal::get_ical_link();
		return apply_filters( 'tribe_get_ical_link', $output );
	}
}
