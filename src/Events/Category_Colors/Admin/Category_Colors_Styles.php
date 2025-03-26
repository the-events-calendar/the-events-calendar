<?php
/**
 * Handles all style-related functionality for category colors.
 *
 * @since   TBD
 *
 * @package TEC\Events\Category_Colors\Admin
 */

namespace TEC\Events\Category_Colors\Admin;

use TEC\Common\StellarWP\Assets\Asset;
use Tribe__Events__Main;

/**
 * Class Category_Colors_Styles
 *
 * Handles all style-related functionality for category colors, including
 * enqueuing assets and adding inline styles.
 *
 * @since TBD
 */
class Category_Colors_Styles {

	/**
	 * Enqueues admin assets for category colors.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function enqueue_assets(): void {
		Asset::add(
			'tec-events-category-colors-admin-js',
			'/js/admin/category-colors/admin-category.js',
			Tribe__Events__Main::VERSION
		)
			->add_to_group_path( 'tec-events-resources' )
			->add_to_group( 'tec-events-category-colors' )
			->enqueue_on( 'admin_enqueue_scripts' )
			->set_condition( [ $this, 'is_category_page' ] )
			->set_dependencies( 'jquery', 'wp-color-picker' )
			->register();

		Asset::add(
			'tec-events-category-colors-admin-style',
			'/css/admin/category-colors/admin-category.css',
			Tribe__Events__Main::VERSION
		)
			->add_to_group_path( 'tec-events-resources' )
			->add_to_group( 'tec-events-category-colors' )
			->enqueue_on( 'admin_enqueue_scripts' )
			->set_condition( [ $this, 'is_category_page' ] )
			->set_dependencies( 'wp-color-picker' )
			->register();

		Asset::add(
			'tec-events-category-colors-wp-picker-style',
			'/css/admin/category-colors/wp-picker.css',
			Tribe__Events__Main::VERSION
		)
			->add_to_group_path( 'tec-events-resources' )
			->add_to_group( 'tec-events-category-colors' )
			->enqueue_on( 'admin_enqueue_scripts' )
			->set_condition( [ $this, 'is_category_page' ] )
			->set_dependencies( 'tec-events-category-colors-admin-style' )
			->register();
	}

	/**
	 * Maybe adds inline styles for category colors.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function maybe_add_inline_styles(): void {
		if ( ! $this->is_category_page() ) {
			return;
		}

		// Retrieve the dynamically generated category color CSS.
		$css = get_option( 'tec_events_category_color_css', '' );

		// Add inline styles if available.
		if ( ! empty( $css ) ) {
			wp_add_inline_style( 'tec-events-category-colors-admin-style', $css );
		}
	}

	/**
	 * Checks if the current page is a category management page.
	 *
	 * @since TBD
	 *
	 * @return bool Whether the current page is a category management page.
	 */
	public function is_category_page(): bool {
		$screen = get_current_screen();

		return isset( $screen->taxonomy ) && Tribe__Events__Main::TAXONOMY === $screen->taxonomy;
	}
} 