<?php

namespace TEC\Events\Category_Colors\Admin;

use TEC\Common\StellarWP\Assets\Asset;
use TEC\Common\StellarWP\Assets\Config as Assets_Config;
use Tribe__Events__Main;
use Tribe__Template;

abstract class Abstract_Admin {

	/**
	 * Stores the instance of the template engine used for rendering.
	 *
	 * @since TBD
	 *
	 * @var Tribe__Template
	 */
	protected $template;

	/**
	 * Gets the template instance used for rendering HTML.
	 *
	 * @since TBD
	 *
	 * @return Tribe__Template
	 */
	public function get_template(): Tribe__Template {
		if ( empty( $this->template ) ) {
			$this->template = new Tribe__Template();
			$this->template->set_template_origin( Tribe__Events__Main::instance() );
			$this->template->set_template_folder( 'src/admin-views/category-colors/' );
			$this->template->set_template_context_extract( true );
			$this->template->set_template_folder_lookup( false );
		}

		return $this->template;
	}

	/**
	 * Enqueues admin assets for category colors.
	 *
	 * @since @TBD
	 *
	 * @param string $hook The current admin page.
	 *
	 * @return void
	 */
	public function enqueue_assets() {

		Assets_Config::add_group_path( 'tec-category-colors', Tribe__Events__Main::instance()->plugin_path, 'src/resources' );

		Asset::add(
			'tec-category-colors-admin-js',
			'/js/admin/category-colors/admin-category.js',
			Tribe__Events__Main::VERSION
		)
			->add_to_group_path( 'tec-category-colors' )
			->enqueue_on( 'admin_enqueue_scripts' )
			->set_condition( [ __CLASS__, 'is_category_page' ] )
			->set_dependencies( 'jquery', 'wp-color-picker' )
			->register();

		Asset::add(
			'tec-category-colors-admin-style',
			'/css/admin/category-colors/admin-category.css',
			Tribe__Events__Main::VERSION
		)
			->add_to_group_path( 'tec-category-colors' )
			->enqueue_on( 'admin_enqueue_scripts' )
			->set_condition( [ __CLASS__, 'is_category_page' ] )
			->register();
	}

	public static function is_category_page() {
		// Ensure this only loads for the taxonomy edit page.
		$screen = get_current_screen();

		return isset( $screen->taxonomy ) && 'tribe_events_cat' === $screen->taxonomy;
	}
}
