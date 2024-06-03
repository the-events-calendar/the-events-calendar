<?php
/**
 * Class `Tribe__Events__Featured_Events__Permalinks_Helper`
 *
 * This class handles the modification of event permalinks to include a featured slug if necessary.
 * It hooks into the permalink generation process for events to potentially add a featured slug.
 *
 * @internal
 */
class Tribe__Events__Featured_Events__Permalinks_Helper {
	/**
	 * Hooks the `maybe_add_featured_slug` method into the `tribe_events_get_link` filter.
	 *
	 * @since 4.0.0
	 */
	public function hook() {
		add_filter( 'tribe_events_get_link', [ $this, 'maybe_add_featured_slug' ], 100, 6 );
	}

	/**
	 * Potentially adds a featured slug to the event permalink.
	 *
	 * This method checks if the event is marked as featured and, if so, appends the featured slug
	 * to the event URL. It does nothing if the event is explicitly set as non-featured or if the
	 * current query does not relate to featured events.
	 *
	 * @since 4.0.0
	 *
	 * @param string $url        The original event URL.
	 * @param string $type       The type of the link being generated.
	 * @param mixed  $secondary  Secondary data related to the link.
	 * @param mixed  $term       Term data related to the link.
	 * @param array  $url_args   Additional URL arguments.
	 * @param mixed  $featured   Indicates if the event is featured. Can be null, true, or false.
	 *
	 * @return string The modified or original URL.
	 */
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
