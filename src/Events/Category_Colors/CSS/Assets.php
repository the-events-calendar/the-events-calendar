<?php
/**
 * Handles the enqueueing of category color CSS assets.
 *
 * @since   TBD
 *
 * @package TEC\Events\Category_Colors\CSS
 */

namespace TEC\Events\Category_Colors\CSS;

use TEC\Common\StellarWP\Assets\Asset;
use TEC\Common\StellarWP\Assets\Config as Assets_Config;
use Tribe__Events__Main;

/**
 * Class for managing CSS assets related to category colors.
 *
 * @since TBD
 */
class Assets {
	/**
	 * Enqueues frontend styles and inline category color CSS.
	 *
	 * @since TBD
	 */
	public function enqueue_frontend_scripts(): void {
		// Register asset group path.
		Assets_Config::add_group_path( 'tec-category-colors', Tribe__Events__Main::instance()->plugin_path, 'src/resources' );

		// Add main CSS file.
		Asset::add(
			'tec-category-colors-frontend-styles',
			'/css/category-colors/frontend-category.css',
			Tribe__Events__Main::VERSION
		)
			->add_to_group_path( 'tec-category-colors' )
			->enqueue_on( 'tribe_events_views_v2_after_make_view' )
			->register();
		Asset::add(
			'tec-category-colors-frontend-legend-styles',
			'/css/category-colors/category-legend.css',
			Tribe__Events__Main::VERSION
		)
			->add_to_group_path( 'tec-category-colors' )
			->set_condition( [ $this, 'should_enqueue_frontend_styles' ] )
			->enqueue_on( 'tribe_events_views_v2_after_make_view' )
			->register();
		Asset::add(
			'tec-category-colors-frontend-scripts',
			'/js/views/category-color-selector.js',
			Tribe__Events__Main::VERSION
		)
			->add_to_group_path( 'tec-category-colors' )
			->enqueue_on( 'tribe_events_views_v2_after_make_view' )
			->register();

		// Retrieve the dynamically generated category color CSS.
		$css = get_option( 'tec_events_category_color_css', '' );

		// Add inline styles if available.
		if ( ! empty( $css ) ) {
			wp_add_inline_style( 'tec-category-colors-frontend-styles', $css );
		}
	}

	/**
	 * Determines whether to enqueue the frontend styles for category colors.
	 *
	 * If the `category-color-custom-css` option is enabled (true), this function returns false,
	 * preventing the default styles from being enqueued. If the option is disabled (false),
	 * it returns true, allowing the styles to be enqueued.
	 *
	 * @since TBD
	 *
	 * @return bool True if frontend styles should be enqueued, false otherwise.
	 */
	public function should_enqueue_frontend_styles(): bool {
		return ! tribe_get_option( 'category-color-custom-css', true );
	}
}
