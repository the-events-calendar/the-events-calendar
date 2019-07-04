<?php
/**
 * Functions, or polyfills, related to URL manipulation on events
 *
 * @since 4.9.4
 */
use Tribe__Events__Rewrite as Rewrite;

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
	return Rewrite::instance()->get_clean_url( add_query_arg( $query, $url ) );
}