<?php
/**
 * Category Color Dropdown Provider.
 *
 * This class retrieves and processes event categories with associated color metadata
 * to be used in the frontend dropdown selection.
 *
 * @since   TBD
 * @package TEC\Events\Category_Colors\Repositories
 */

namespace TEC\Events\Category_Colors\Repositories;

use TEC\Events\Category_Colors\Event_Category_Meta;
use TEC\Events\Category_Colors\Meta_Keys;
use Tribe__Events__Main;

/**
 * Provides event categories with color metadata for the dropdown.
 *
 * This class fetches event categories, retrieves their metadata (color, priority, visibility),
 * and processes them for frontend use.
 *
 * @since TBD
 */
class Category_Color_Dropdown_Provider {

	/**
	 * Retrieves categories and their colors for the dropdown.
	 *
	 * @since TBD
	 *
	 * @return array[]
	 */
	public function get_dropdown_categories(): array {
		$categories           = $this->get_categories();
		$categories_with_meta = array_map( fn( $category ) => $this->get_category_meta( $category ), $categories );
		$filtered_categories  = $this->filter_categories( $categories_with_meta );

		return $this->sort_by_priority( $filtered_categories );
	}

	/**
	 * Fetches all event categories.
	 *
	 * @since TBD
	 *
	 * @return array
	 */
	protected function get_categories(): array {
		return get_terms(
			[
				'taxonomy'   => Tribe__Events__Main::TAXONOMY,
				'hide_empty' => false,
			]
		);
	}

	/**
	 * Retrieves category metadata (color, priority, hidden status).
	 *
	 * @since TBD
	 *
	 * @param object $category The category term object.
	 *
	 * @return array
	 */
	protected function get_category_meta( object $category ): array {
		$meta_instance = tribe( Event_Category_Meta::class )->set_term( $category->term_id );
		$priority      = $meta_instance->get( Meta_Keys::get_key( 'priority' ) );

		return [
			'slug'     => $category->slug,
			'name'     => $category->name,
			'priority' => is_numeric( $priority ) ? (int) $priority : -1,
			'primary'  => $meta_instance->get( Meta_Keys::get_key( 'primary' ) ),
			'hidden'   => (bool) $meta_instance->get( Meta_Keys::get_key( 'hidden' ) ),
		];
	}

	/**
	 * Filters categories based on their primary color and visibility settings.
	 *
	 * @since TBD
	 *
	 * @param array $categories The list of categories.
	 *
	 * @return array
	 */
	protected function filter_categories( array $categories ): array {
		$show_hidden_categories = tribe_get_option( 'category-color-show-hidden-categories', false );

		return array_values(
			array_filter(
				$categories,
				fn( $category ) => ! empty( $category['primary'] ) && ( $show_hidden_categories || ! $category['hidden'] )
			)
		);
	}

	/**
	 * Sorts categories by priority (highest first).
	 *
	 * @since TBD
	 *
	 * @param array $categories The list of categories.
	 *
	 * @return array
	 */
	protected function sort_by_priority( array $categories ): array {
		usort( $categories, fn( $a, $b ) => $b['priority'] <=> $a['priority'] );

		return $categories;
	}
}
