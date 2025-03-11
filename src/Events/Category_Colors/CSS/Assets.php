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
		// Register asset group path
		Assets_Config::add_group_path( 'tec-category-colors', Tribe__Events__Main::instance()->plugin_path, 'src/resources' );

		// Add main CSS file
		Asset::add(
			'tec-category-colors-frontend-styles',
			'/css/category-colors/frontend-category.css',
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
}
