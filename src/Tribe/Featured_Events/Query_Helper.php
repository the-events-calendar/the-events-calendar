<?php
/**
 * @internal
 */
class Tribe__Events__Featured_Events__Query_Helper {
	public function hook() {
		add_action( 'tribe_events_pre_get_posts', array( $this, 'pre_get_posts' ) );
	}

	public function pre_get_posts( $query ) {
		if ( ! $query->get( 'featured' ) ) {
			return;
		}

		$meta_query = (array) $query->get( 'meta_query' );

		$meta_query[] = array(
			'key' => Tribe__Events__Featured_Events::FEATURED_EVENT_KEY,
			'compare' => 'EXISTS',
		);

		$query->set( 'meta_query', $meta_query );
	}
}
