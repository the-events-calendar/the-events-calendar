<?php
/**
 * Category Color Priority Category Provider.
 *
 * This class retrieves and processes event categories to determine the highest-priority category
 * based on predefined category metadata.
 *
 * @since TBD
 * @package TEC\Events\Category_Colors\Repositories
 */

namespace TEC\Events\Category_Colors\Repositories;

use TEC\Events\Category_Colors\Meta_Keys_Trait;
use WP_Post;

/**
 * Provides the highest-priority event category based on category metadata.
 *
 * This class fetches event categories, retrieves their priority metadata, and returns
 * the most relevant category for display.
 *
 * @since TBD
 */
class Category_Color_Priority_Category_Provider {
	use Meta_Keys_Trait;

	/**
	 * Retrieves the highest-priority category for a given event.
	 *
	 * @since TBD
	 *
	 * @param WP_Post $event The post object of the event.
	 *
	 * @return object|null The highest-priority category object, or null if none found.
	 */
	public function get_highest_priority_category( WP_Post $event ): ?object {
		$categories = $this->get_event_categories( $event->ID );

		if ( empty( $categories ) ) {
			return null;
		}

		$priorities = $this->get_category_priorities( $categories );

		// Sort categories by priority (highest first).
		usort( $categories, fn( $a, $b ) => $priorities[ $b->term_id ] <=> $priorities[ $a->term_id ] );

		/**
		 * Filters the highest-priority category after sorting.
		 *
		 * @since TBD
		 *
		 * @param object|null $category   The highest-priority category.
		 * @param array       $categories The sorted list of categories.
		 */
		return apply_filters( 'tec_events_category_color_highest_priority_category', reset( $categories ), $categories );
	}

	/**
	 * Retrieves the categories associated with an event.
	 *
	 * @since TBD
	 *
	 * @param int $event_id The event ID.
	 *
	 * @return array The list of category term objects.
	 */
	protected function get_event_categories( int $event_id ): array {
		$categories = get_the_terms( $event_id, 'tribe_events_cat' );

		/**
		 * Filters the raw list of event categories before processing.
		 *
		 * @since TBD
		 *
		 * @param array $categories The retrieved categories.
		 * @param int   $event_id   The event ID.
		 */
		return apply_filters( 'tec_events_category_color_event_categories', is_array( $categories ) ? $categories : [], $event_id );
	}

	/**
	 * Retrieves category priority values.
	 *
	 * @since TBD
	 *
	 * @param array $categories The list of category term objects.
	 *
	 * @return array Associative array of category term ID => priority.
	 */
	protected function get_category_priorities( array $categories ): array {
		$priorities = [];

		foreach ( $categories as $category ) {
			$priority                         = get_term_meta( $category->term_id, $this->get_key( 'priority' ), true );
			$priorities[ $category->term_id ] = is_numeric( $priority ) ? (int) $priority : -1;
		}

		/**
		 * Filters the priority values for event categories.
		 *
		 * @since TBD
		 *
		 * @param array $priorities Associative array of category term ID => priority.
		 * @param array $categories The list of categories being processed.
		 */
		return apply_filters( 'tec_events_category_color_category_priorities', $priorities, $categories );
	}
}
