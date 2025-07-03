<?php
/**
 * Handles all style-related functionality for category colors.
 *
 * @since 6.14.0
 *
 * @package TEC\Events\Category_Colors\Admin
 */

namespace TEC\Events\Category_Colors\Admin;

use TEC\Common\StellarWP\Assets\Asset;
use Tribe__Events__Main;
use TEC\Events\Category_Colors\CSS\Generator;
use Tribe__Events__Main as TEC;


/**
 * Class Category_Colors_Styles
 *
 * Handles all style-related functionality for category colors, including
 * enqueuing assets and adding inline styles.
 *
 * @since 6.14.0
 */
class Category_Colors_Styles {
	/**
	 * The Generator instance.
	 *
	 * @since 6.14.0
	 *
	 * @var Generator
	 */
	protected Generator $generator;

	/**
	 * Constructor.
	 *
	 * @since 6.14.0
	 *
	 * @param Generator $generator The Generator instance.
	 */
	public function __construct( Generator $generator ) {
		$this->generator = $generator;
	}

	/**
	 * Enqueues admin assets for category colors.
	 *
	 * @since 6.14.0
	 *
	 * @return void
	 */
	public function enqueue_assets(): void {
		Asset::add(
			'tec-events-category-colors-admin-js',
			'/js/admin/category-colors/admin-category.js',
			Tribe__Events__Main::VERSION
		)
			->add_to_group_path( TEC::class . '-packages' )
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
			->add_to_group_path( TEC::class . '-packages' )
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
			->add_to_group_path( TEC::class . '-packages' )
			->add_to_group( 'tec-events-category-colors' )
			->enqueue_on( 'admin_enqueue_scripts' )
			->set_condition( [ $this, 'is_category_page' ] )
			->set_dependencies( 'tec-events-category-colors-admin-style' )
			->register();
	}

	/**
	 * Maybe adds inline styles for category colors.
	 *
	 * @since 6.14.0
	 *
	 * @return void
	 */
	public function maybe_add_inline_styles(): void {
		if ( ! $this->is_category_page() ) {
			return;
		}

		// Retrieve the dynamically generated category color CSS.
		$css = get_option( $this->generator->get_option_key(), '' );

		// Add inline styles if available.
		if ( ! empty( $css ) ) {
			wp_add_inline_style( 'tec-events-category-colors-admin-style', $css );
		}
	}

	/**
	 * Checks if the current page is a category management page.
	 *
	 * @since 6.14.0
	 *
	 * @return bool Whether the current page is a category management page.
	 */
	public function is_category_page(): bool {
		$screen = get_current_screen();

		return isset( $screen->taxonomy ) && Tribe__Events__Main::TAXONOMY === $screen->taxonomy;
	}
}
