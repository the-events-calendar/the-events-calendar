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
	 * Determines if the category color legend should be displayed on a given view.
	 *
	 * This checks if the provided template slug is in the enabled views list.
	 *
	 * @since TBD
	 *
	 * @param string $template_slug The slug of the template/view being checked.
	 *
	 * @return bool True if the legend should be displayed, false otherwise.
	 */
	public function should_display_on_view( string $template_slug ): bool {
		$enabled_views = tribe_get_option( 'category-color-legend-show', [] );

		/**
		 * Filters the enabled views where the category color legend should be displayed.
		 *
		 * @since TBD
		 *
		 * @param array  $enabled_views List of enabled views.
		 * @param string $template_slug The current view being checked.
		 */
		$enabled_views = (array) apply_filters( 'tec_events_category_color_enabled_views', $enabled_views, $template_slug );

		return in_array( $template_slug, $enabled_views, true );
	}

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

		/**
		 * Filters the final list of categories shown in the dropdown.
		 *
		 * @since TBD
		 *
		 * @param array $filtered_categories The final processed categories.
		 */
		return (array) apply_filters( 'tec_events_category_color_dropdown_categories', $this->sort_by_priority( $filtered_categories ) );
	}

	/**
	 * Fetches all event categories.
	 *
	 * @since TBD
	 *
	 * @return array
	 */
	protected function get_categories(): array {
		$categories = get_terms(
			[
				'taxonomy'   => Tribe__Events__Main::TAXONOMY,
				'hide_empty' => false,
			]
		);

		/**
		 * Filters the raw list of event categories before processing.
		 *
		 * @since TBD
		 *
		 * @param array $categories The retrieved categories.
		 */
		return (array) apply_filters( 'tec_events_category_color_raw_categories', $categories );
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

		$category_meta = [
			'slug'     => $category->slug,
			'name'     => $category->name,
			'priority' => is_numeric( $priority ) ? (int) $priority : -1,
			'primary'  => $meta_instance->get( Meta_Keys::get_key( 'primary' ) ),
			'hidden'   => (bool) $meta_instance->get( Meta_Keys::get_key( 'hide_from_legend' ) ),
		];

		/**
		 * Filters metadata of a single category.
		 *
		 * @since TBD
		 *
		 * @param array  $category_meta The metadata of the category.
		 * @param object $category      The category term object.
		 */
		return (array) apply_filters( 'tec_events_category_color_category_meta', $category_meta, $category );
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

		$filtered = array_values(
			array_filter(
				$categories,
				fn( $category ) => ! empty( $category['primary'] ) && ( $show_hidden_categories || ! $category['hidden'] )
			)
		);

		/**
		 * Filters the categories after visibility filtering.
		 *
		 * @since TBD
		 *
		 * @param array $filtered The filtered categories list.
		 */
		return (array) apply_filters( 'tec_events_category_color_filtered_categories', $filtered );
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

		/**
		 * Filters the sorted list of categories.
		 *
		 * @since TBD
		 *
		 * @param array $categories The sorted categories list.
		 */
		return (array) apply_filters( 'tec_events_category_color_sorted_categories', $categories );
	}
}
