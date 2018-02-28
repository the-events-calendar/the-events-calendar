<?php
/**
 * @internal
 */
class Tribe__Events__Featured_Events__Permalinks_Helper {
	public function hook() {
		add_filter( 'tribe_events_get_link', array( $this, 'maybe_add_featured_slug' ), 100, 6 );
	}

	public function maybe_add_featured_slug( $url, $type, $secondary, $term, $url_args, $featured ) {

		if ( ! $wp_query = tribe_get_global_query_object() ) {
			return;
		}

		// Do nothing if $featured is explicitly set to (bool) false or if the current query does not
		// relate to featured events
		if (
			false === $featured
			|| 'single' === $type
			|| ( null === $featured && ! $wp_query->get( 'featured' ) )
		) {
			return $url;
		}

		return trailingslashit( trailingslashit( $url ) . Tribe__Events__Main::instance()->featured_slug );
	}
}