<?php
/**
 * Functions, or polyfills, related to URL manipulation on events
 *
 * @since TBD
 */
use Tribe__Events__Rewrite as Rewrite;

/**
 * Given a set of query strings returns the clean and canonical URL.
 *
 * @since  TBD
 *
 * @param  string|array $query Query string arguments.
 * @param  string|null  $url   Base url to apply those query arguments.
 *
 * @return string              Final clean and canonical URL for events.
 */
function tribe_events_get_url( $query = [], string $url = null ) {
	return Rewrite::instance()->get_clean_url( add_query_arg( $query, $url ) );
}