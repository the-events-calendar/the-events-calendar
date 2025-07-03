<?php
/**
 * Category Color Dropdown Provider.
 *
 * This class retrieves and processes event categories with associated color metadata
 * to be used in the frontend dropdown selection.
 *
 * @since 6.14.0
 * @package TEC\Events\Category_Colors\Repositories
 */

namespace TEC\Events\Category_Colors\Repositories;

use TEC\Events\Category_Colors\Event_Category_Meta;
use TEC\Events\Category_Colors\Meta_Keys_Trait;
use Tribe__Events__Main;
use Tribe\Events\Views\V2\View;
use WP_Term;

/**
 * Provides event categories with color metadata for the dropdown.
 *
 * This class fetches event categories, retrieves their metadata (color, priority, visibility),
 * and processes them for frontend use.
 *
 * @since 6.14.0
 */
class Category_Color_Dropdown_Provider {
	use Meta_Keys_Trait;

	/**
	 * Cache key for dropdown categories.
	 *
	 * @since 6.14.0
	 */
	const CACHE_KEY = 'tec_category_colors_dropdown_categories';

	/**
	 * List of shortcode values that should not display the category colors.
	 * Empty shortcode values are allowed by default.
	 *
	 * @since 6.14.0
	 *
	 * @var array<string> Array of shortcode identifiers that should not display category colors.
	 */
	protected array $disallowed_shortcodes = [
		'admin-manager',
	];

	/**
	 * Determines if the category color legend should be displayed on a given view.
	 *
	 * @since 6.14.0
	 *
	 * @param View $view The View object.
	 *
	 * @return bool True if the legend should be displayed, false otherwise.
	 */
	public function should_display_on_view( View $view = null ): bool {
		// Only check shortcode context if we have a view object.
		if ( $view !== null && ! $this->is_valid_shortcode_context( $view ) ) {
			return false;
		}

		$enabled_views = tribe_get_option( 'category-color-legend-show', [] );

		/**
		 * Filters the enabled views where the category color legend should be displayed.
		 *
		 * @since 6.14.0
		 *
		 * @param array<string> $enabled_views List of enabled view slugs.
		 * @param View          $view          The View object.
		 */
		$enabled_views = (array) apply_filters( 'tec_events_category_color_enabled_views', $enabled_views, $view );

		$template_slug = $view ? $view->get_template_slug() : '';
		if ( empty( $template_slug ) ) {
			return false;
		}

		return in_array( $template_slug, $enabled_views, true );
	}

	/**
	 * Checks if the current shortcode context is valid for displaying category colors.
	 *
	 * @since 6.14.0
	 *
	 * @param View $view The view object.
	 *
	 * @return bool True if the shortcode context is valid, false otherwise.
	 */
	protected function is_valid_shortcode_context( View $view ): bool {
		if ( ! $view instanceof View ) {
			return false;
		}

		$context = $view->get_context();
		if ( ! $context ) {
			return false;
		}

		$shortcode = $context->get( 'shortcode' );

		// Empty shortcode is allowed.
		if ( empty( $shortcode ) ) {
			return true;
		}

		/**
		 * Filters the list of blacklisted shortcodes.
		 *
		 * @since 6.14.0
		 *
		 * @param array<string> $disallowed_shortcodes List of shortcode values that should not display category colors.
		 * @param View          $view                   The current view object.
		 */
		$disallowed_shortcodes = apply_filters(
			'tec_events_category_color_blacklisted_shortcodes',
			$this->disallowed_shortcodes,
			$view
		);

		if ( ! is_array( $disallowed_shortcodes ) ) {
			$disallowed_shortcodes = [];
		}

		return ! in_array( $shortcode, $disallowed_shortcodes, true );
	}

	/**
	 * Retrieves categories and their colors for the dropdown.
	 *
	 * @since 6.14.0
	 *
	 * @return array<array{
	 *     slug: string,
	 *     name: string,
	 *     priority: int,
	 *     primary: string,
	 *     hidden: bool
	 * }> Array of category data with their associated colors and metadata.
	 */
	public function get_dropdown_categories(): array {
		$cached_categories = tribe_cache()->get( self::CACHE_KEY );
		if ( $cached_categories !== false ) {
			return $cached_categories;
		}

		$categories = $this->get_categories();
		if ( empty( $categories ) ) {
			$result = [];
			tribe_cache()->set( self::CACHE_KEY, $result, 3600 );
			return $result;
		}

		$categories_with_meta = array_map(
			fn( $category ) => $this->get_category_meta( $category ),
			$categories
		);

		$filtered_categories = $this->filter_categories( $categories_with_meta );

		/**
		 * Filters the final list of categories shown in the dropdown.
		 *
		 * @since 6.14.0
		 *
		 * @param array<array{
		 *     slug: string,
		 *     name: string,
		 *     priority: int,
		 *     primary: string,
		 *     hidden: bool
		 * }> $filtered_categories The final processed categories.
		 */
		$result = (array) apply_filters( 'tec_events_category_color_dropdown_categories', $this->sort_by_priority( $filtered_categories ) );

		// Cache the result for 1 hour (3600 seconds).
		tribe_cache()->set( self::CACHE_KEY, $result, 3600 );

		return $result;
	}

	/**
	 * Busts the dropdown categories cache.
	 *
	 * @since 6.14.0
	 */
	public function bust_dropdown_categories_cache(): void {
		tribe_cache()->delete( self::CACHE_KEY );
	}

	/**
	 * Checks if there are categories with colors available.
	 *
	 * @since 6.14.0
	 *
	 * @return bool True if there are categories with colors, false otherwise.
	 */
	public function has_dropdown_categories(): bool {
		$categories = $this->get_dropdown_categories();
		return ! empty( $categories );
	}

	/**
	 * Fetches all event categories.
	 *
	 * @since 6.14.0
	 *
	 * @return array<WP_Term> Array of WordPress term objects representing event categories.
	 */
	protected function get_categories(): array {
		$terms = get_terms(
			[
				'taxonomy'   => Tribe__Events__Main::TAXONOMY,
				'hide_empty' => false,
			]
		);

		// Handle potential WP_Error.
		if ( is_wp_error( $terms ) ) {
			return [];
		}

		/**
		 * Filters the raw list of event categories before processing.
		 *
		 * @since 6.14.0
		 *
		 * @param array<WP_Term> $categories The retrieved categories.
		 */
		return (array) apply_filters( 'tec_events_category_color_raw_categories', $terms );
	}

	/**
	 * Retrieves category metadata (color, priority, hidden status).
	 *
	 * @since 6.14.0
	 *
	 * @param WP_Term $category The category term object.
	 *
	 * @return array{
	 *     slug: string,
	 *     name: string,
	 *     priority: int,
	 *     primary: string,
	 *     hidden: bool
	 * } Array containing the category's metadata.
	 */
	protected function get_category_meta( WP_Term $category ): array {
		$meta_instance = tribe( Event_Category_Meta::class )->set_term( $category->term_id );
		$priority      = $meta_instance->get( $this->get_key( 'priority' ) );

		$category_meta = [
			'slug'     => $category->slug ?? '',
			'name'     => $category->name ?? '',
			'priority' => is_numeric( $priority ) ? (int) $priority : -1,
			'primary'  => $meta_instance->get( $this->get_key( 'primary' ) ) ?? '',
			'hidden'   => (bool) $meta_instance->get( $this->get_key( 'hide_from_legend' ) ),
		];

		/**
		 * Filters metadata of a single category.
		 *
		 * @since 6.14.0
		 *
		 * @param array{
		 *     slug: string,
		 *     name: string,
		 *     priority: int,
		 *     primary: string,
		 *     hidden: bool
		 * }              $category_meta The metadata of the category.
		 * @param WP_Term $category      The category term object.
		 */
		return (array) apply_filters( 'tec_events_category_color_category_meta', $category_meta, $category );
	}

	/**
	 * Filters categories based on their primary color and visibility settings.
	 *
	 * @since 6.14.0
	 *
	 * @param array $categories {
	 *     slug: string,
	 *     name: string,
	 *     priority: int,
	 *     primary: string,
	 *     hidden: bool
	 * }> $categories The list of categories to filter.
	 *
	 * @return array<array{
	 *     slug: string,
	 *     name: string,
	 *     priority: int,
	 *     primary: string,
	 *     hidden: bool
	 * }> Filtered array of categories.
	 */
	protected function filter_categories( array $categories ): array {
		if ( empty( $categories ) ) {
			return [];
		}

		$show_hidden_categories = tribe_get_option( 'category-color-show-hidden-categories', false );
		if ( ! is_bool( $show_hidden_categories ) ) {
			$show_hidden_categories = false;
		}

		$filtered = array_values(
			array_filter(
				$categories,
				fn( $category ) => ! empty( $category['primary'] ) && ( $show_hidden_categories || ! $category['hidden'] )
			)
		);

		/**
		 * Filters the categories after visibility filtering.
		 *
		 * @since 6.14.0
		 *
		 * @param array<array{
		 *     slug: string,
		 *     name: string,
		 *     priority: int,
		 *     primary: string,
		 *     hidden: bool
		 * }> $filtered The filtered categories list.
		 */
		return (array) apply_filters( 'tec_events_category_color_filtered_categories', $filtered );
	}

	/**
	 * Sorts categories by priority (highest first).
	 *
	 * @since 6.14.0
	 *
	 * @param array $categories {
	 *     slug: string,
	 *     name: string,
	 *     priority: int,
	 *     primary: string,
	 *     hidden: bool
	 * }> $categories The list of categories to sort.
	 *
	 * @return array<array{
	 *     slug: string,
	 *     name: string,
	 *     priority: int,
	 *     primary: string,
	 *     hidden: bool
	 * }> Sorted array of categories.
	 */
	protected function sort_by_priority( array $categories ): array {
		if ( empty( $categories ) ) {
			return [];
		}

		// Validate that all categories have a priority key and it's numeric.
		$valid_categories = array_filter(
			$categories,
			fn( $category ) => isset( $category['priority'] ) && is_numeric( $category['priority'] )
		);

		// Only sort if we have valid categories.
		if ( ! empty( $valid_categories ) ) {
			usort( $valid_categories, fn( $a, $b ) => $b['priority'] <=> $a['priority'] );
		}

		/**
		 * Filters the sorted list of categories.
		 *
		 * @since 6.14.0
		 *
		 * @param array<array{
		 *     slug: string,
		 *     name: string,
		 *     priority: int,
		 *     primary: string,
		 *     hidden: bool
		 * }> $categories The sorted categories list.
		 */
		return (array) apply_filters( 'tec_events_category_color_sorted_categories', $valid_categories );
	}
}
