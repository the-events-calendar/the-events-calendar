<?php
/**
 * Handles the event tags.
 *
 * @since   TBD
 *
 * @package Tribe\Events\Taxonomy
 */

namespace Tribe\Events\Taxonomy;

use WP_Term;

/**
 * Class Event_Tag
 *
 * @since   TBD
 *
 * @package Tribe\Events\Taxonomy
 */
class Event_Tag {

	/**
	 * Filters the post tag action links displayed for each term in the terms list table.
	 *
	 * @since TBD
	 *
	 * @param array<string|string> $actions An array of action links to be displayed.
	 * @param WP_Term              $tag     Term object.
	 *
	 * @return array<string|string> An array of action links to be displayed
	 */
	public function event_tag_actions( $actions, WP_Term $tag ) {
		if ( 'post_tag' !== $tag->taxonomy ) {
			return $actions;
		}

		$link = tribe_events_get_url( [ 'tag' => $tag->slug, 'post_type' => 'tribe_events', 'eventDisplay' => 'default' ] );
		if ( is_wp_error( $link ) ) {
			return $actions;
		}

		//todo translate
		$actions['event-view'] = '<a href="' . esc_url( $link ) . '" rel="tag">Event View</a>';

		return $actions;
	}
}
