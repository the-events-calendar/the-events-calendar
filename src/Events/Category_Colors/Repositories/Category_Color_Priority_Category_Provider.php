<?php
/**
 * Category Color Priority Category Provider.
 *
 * This class retrieves and processes event categories to determine the highest-priority category
 * based on predefined category metadata.
 *
 * @since 6.14.0
 * @package TEC\Events\Category_Colors\Repositories
 */

namespace TEC\Events\Category_Colors\Repositories;

use TEC\Events\Category_Colors\Meta_Keys_Trait;
use TEC\Events\Category_Colors\Event_Category_Meta;
use WP_Post;

/**
 * Provides the highest-priority event category based on category metadata.
 *
 * This class fetches event categories, retrieves their priority metadata, and returns
 * the most relevant category for display.
 *
 * @since 6.14.0
 */
class Category_Color_Priority_Category_Provider {
	use Meta_Keys_Trait;

	/**
	 * Retrieves the highest-priority category for a given event.
	 *
	 * @since 6.14.0
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
		 * @since 6.14.0
		 *
		 * @param object|null $category   The highest-priority category.
		 * @param array       $categories The sorted list of categories.
		 */
		return apply_filters( 'tec_events_category_color_highest_priority_category', reset( $categories ), $categories );
	}

	/**
	 * Retrieves the highest-priority category with all its metadata for a given event.
	 *
	 * @since 6.14.0
	 * @since 6.15.14 Return null if the primary color is empty.
	 *
	 * @param WP_Post $event The post object of the event.
	 *
	 * @return array|null Array containing the category object and metadata, or null if none found or if category has no color.
	 */
	public function get_highest_priority_category_with_meta( WP_Post $event ): ?array {
		$category = $this->get_highest_priority_category( $event );

		if ( ! $category ) {
			return null;
		}

		$meta_instance = tribe( Event_Category_Meta::class )->set_term( $category->term_id );

		$primary_color = $meta_instance->get( $this->get_key( 'primary' ) );

		// Return null if the category has no primary color set.
		if ( empty( $primary_color ) ) {
			return null;
		}

		return [
			'category' => $category,
			'meta'     => [
				'primary'          => $primary_color,
				'secondary'        => $meta_instance->get( $this->get_key( 'secondary' ) ),
				'text'             => $meta_instance->get( $this->get_key( 'text' ) ),
				'priority'         => $meta_instance->get( $this->get_key( 'priority' ) ),
				'hide_from_legend' => $meta_instance->get( $this->get_key( 'hide_from_legend' ) ),
			],
		];
	}

	/**
	 * Retrieves the categories associated with an event.
	 *
	 * @since 6.14.0
	 *
	 * @param int $event_id The event ID.
	 *
	 * @return array The list of category term objects.
	 */
	protected function get_event_categories( int $event_id ): array {
		$categories = get_the_terms( $event_id, 'tribe_events_cat' );

		$categories = is_array( $categories ) ? $categories : [];
		/**
		 * Filters the raw list of event categories before processing.
		 *
		 * @since 6.14.0
		 *
		 * @param array $categories The retrieved categories.
		 * @param int   $event_id   The event ID.
		 */
		return apply_filters( 'tec_events_category_color_event_categories', $categories, $event_id );
	}

	/**
	 * Retrieves category priority values.
	 *
	 * @since 6.14.0
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
		 * @since 6.14.0
		 *
		 * @param array $priorities Associative array of category term ID => priority.
		 * @param array $categories The list of categories being processed.
		 */
		return apply_filters( 'tec_events_category_color_category_priorities', $priorities, $categories );
	}
}
