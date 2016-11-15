<?php
/**
 * Provides an option to position the main events view on the site homepage.
 */
class Tribe__Events__Front_Page_View {
	public function hook() {
		if ( is_admin() ) {
			// Integrate with the Settings > Reading screen
			tribe( 'tec.admin.front-page-view' )->hook();
		} elseif ( tribe_get_option( 'front_page_event_archive', false ) ) {
			// Implement front page view
			add_action( 'parse_query', array( $this, 'parse_query' ), 5 );
			add_filter( 'tribe_events_getLink', array( $this, 'main_event_page_links' ) );
		}
	}

	/**
	 * Inspect and possibly adapt the main query in order to force the main events page to the
	 * front of the house.
	 *
	 * @param WP_Query $query
	 */
	public function parse_query( WP_Query $query ) {
		// We're only interested in the main query (when it runs in relation to the site homepage),
		// we also need to make an exception for compatibility with Community Events (WP_Route)
		if ( ! $query->is_main_query() || ! $query->is_home() || $query->get( 'WP_Route' ) ) {
			return;
		}

		// We don't need this to run again after this point
		remove_action( 'parse_query', array( $this, 'parse_query' ), 5 );

		// Let's set the relevant flags in order to cause the main events page to show
		$query->set( 'page_id', 0 );
		$query->set( 'post_type', Tribe__Events__Main::POSTTYPE );
		$query->set( 'eventDisplay', 'default' );
		$query->set( 'tribe_events_front_page', true );

		// Some extra tricks required to help avoid problems when the default view is list view
		$query->is_page = false;
		$query->is_singular = false;
	}

	/**
	 * Where TEC generates a link to the nominal main events page replace it with a link to the
	 * front page instead.
	 *
	 * We'll only do this if pretty permalinks are in use.
	 *
	 * @param string $url
	 *
	 * @return string
	 */
	public function main_event_page_links( $url ) {
		// Capture the main events URL and break it into its consituent pieces for future comparison
		static $event_url;

		if ( ! isset( $event_url ) ) {
			$event_url = parse_url( $this->get_main_events_url() );
		}

		// Don't interfere if we're using ugly permalinks
		if ( '' === get_option( 'permalink_structure' ) ) {
			return $url;
		}

		// Break apart the requested URL
		$current = parse_url( $url );

		// If the URLs can't be inspected then bail
		if ( false === $event_url || false === $current ) {
			return $url;
		}

		// If this is not a request for the main events URL, bail
		if ( $event_url['path'] !== $current['path'] || $event_url['host'] !== $current['host'] ) {
			return $url;
		}

		// Reform the query
		$query = ! empty( $current['query'] ) ? '?' . $current['query'] : '';

		return home_url() . $query;
	}

	/**
	 * Supplies the nominal main events page URL (ie, the regular /events/ page that is used
	 * when front page event view is not enabled).
	 *
	 * @return string
	 */
	protected function get_main_events_url() {
		$events_slug = tribe_get_option( 'eventsSlug', 'events' );

		if ( false !== strpos( get_option( 'permalink_structure' ), 'index.php' ) ) {
			return trailingslashit( home_url() . '/index.php/' . sanitize_title( $events_slug ) );
		} else {
			return trailingslashit( home_url() . '/' . sanitize_title( $events_slug ) );
		}
	}
}