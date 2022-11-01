<?php
/**
 * Handles the `Tribe__Events__Main` class deprecated methods.
 *
 * This trait will only make sense in the context of the `Tribe__Events__Main` class
 * and it should not be used elsewhere.
 *
 * @since 6.0.2
 */

/**
 * Trait Tribe__Events__Main_Deprecated.
 *
 * @since 6.0.2
 */
trait Tribe__Events__Main_Deprecated {
	/**
	 * Get the prev/next post for a given event. Ordered by start date instead of ID.
	 *
	 * @deprecated 6.0.0 Use Tribe__Events__Adjacent_Events::get_closest_event instead.
	 *
	 * @param WP_Post $post The post/event.
	 * @param string  $mode Either 'next' or 'previous'.
	 *
	 * @return null|WP_Post Either the closest Event post, or `null` if not found.
	 */
	public function get_closest_event( $post, $mode = 'next' ) {
		_deprecated_function(
			'Tribe__Events__Main::get_closest_event',
			'6.0.0',
			'Tribe__Events__Adjacent_Events::get_closest_event'
		);

		/** @var Tribe__Events__Adjacent_Events $adjacent_events */
		$adjacent_events = tribe( 'tec.adjacent-events' );
		$post_id = $post instanceof WP_Post ? $post->ID : $post;
		$adjacent_events->set_current_event_id( $post_id );

		return $adjacent_events->get_closest_event( $mode );
	}
}