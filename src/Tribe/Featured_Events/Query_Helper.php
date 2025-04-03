<?php
/**
 * Class `Tribe__Events__Featured_Events__Query_Helper`
 *
 * This class handles the modification of event queries to include only featured events if specified.
 * It hooks into the `pre_get_posts` action to adjust the query parameters accordingly.
 *
 * @internal
 */
class Tribe__Events__Featured_Events__Query_Helper {
	/**
	 * Hooks the pre_get_posts method into the tribe_events_pre_get_posts action.
	 *
	 * @since 4.0.0
	 */
	public function hook() {
		add_action( 'tribe_events_pre_get_posts', [ $this, 'pre_get_posts' ] );
	}

	/**
	 * Modifies the query to include only featured events.
	 *
	 * This method checks if the query is for featured events and, if so, adds a meta query
	 * to filter events that are marked as featured.
	 *
	 * @since 4.0.0
	 *
	 * @param WP_Query $query The WP_Query instance (passed by reference).
	 */
	public function pre_get_posts( $query ) {
		if ( ! $query->get( 'featured' ) ) {
			return;
		}

		$meta_query = (array) $query->get( 'meta_query' );

		$meta_query[] = [
			'key'     => Tribe__Events__Featured_Events::FEATURED_EVENT_KEY,
			'compare' => 'EXISTS',
		];

		$query->set( 'meta_query', $meta_query );
	}
}
