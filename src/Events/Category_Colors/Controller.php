<?php

namespace TEC\Events\Category_Colors;

use TEC\Common\Contracts\Provider\Controller as Controller_Contract;
use TEC\Events\Category_Colors\Settings as Category_Colors_Settings;

class Controller extends Controller_Contract {

	/**
	 * Register the provider.
	 *
	 * @since TBD
	 */
	public function do_register(): void {
		$this->container->singleton( Category_Colors::class );
		$this->container->singleton( Category_Colors_Settings::class );
		$this->container->singleton( Quick_Edit::class );
		$this->container->singleton( Admin\Edit_Tags::class );

		$this->add_filters();
	}

	/**
	 * Unhooks actions and filters.
	 *
	 * @since TBD
	 */
	public function unregister(): void {
		$this->remove_filters();
	}

	/**
	 * Adds the filters required.
	 *
	 * @since TBD
	 */
	protected function add_filters() {
		$this->container->make( Admin\Edit_Tags::class )->add_hooks();

		add_action( 'tribe_settings_do_tabs', [ $this, 'add_category_colors_tab' ] );
	}

	/**
	 * Removes registered filters.
	 *
	 * @since TBD
	 */
	public function remove_filters() {
		$this->container->make( Admin\Edit_Tags::class )->remove_hooks();

		remove_action( 'tribe_settings_do_tabs', [ $this, 'add_category_colors_tab' ] );
	}

	/**
	 * Adds the "Category Colors" tab to the settings page.
	 *
	 * @since TBD
	 */
	public function add_category_colors_tab(): void {
		$this->container->make( Category_Colors_Settings::class )->register_tab();
	}
}
