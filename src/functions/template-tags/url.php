<?php
/**
 * Functions, or polyfills, related to URL manipulation on events
 *
 * @since 4.9.4
 */

/**
 * Given a set of query strings returns the clean and canonical URL.
 *
 * @since  4.9.4
 *
 * @param  string|array $query Query string arguments.
 * @param  string|null  $url   Base url to apply those query arguments.
 *
 * @return string              Final clean and canonical URL for events.
 */
function tribe_events_get_url( $query = [], $url = null ) {
	if ( empty( $url ) ) {
		$events_archive_base = tribe_get_option( 'eventsSlug', 'events' );
		$url = home_url( '/' . $events_archive_base );
	}

	return tribe( 'events.rewrite' )->get_clean_url( add_query_arg( $query, $url ) );
}
