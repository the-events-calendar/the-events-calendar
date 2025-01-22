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
		add_action( 'tribe_settings_do_tabs', [ $this, 'add_category_colors_tab' ] );
		add_filter(
			'tribe_settings_no_save_tabs',
			[ $this, 'allow_tab_to_save' ],
			15
		);
		add_action( 'tribe_settings_content_tab_category-colors', [ $this, 'category_colors_tab_content' ] );

		add_action(
			'tribe_settings_save_tab_category-colors',
			[ $this, 'save_category_colors' ]
		);
	}

	/**
	 * Removes registered filters.
	 *
	 * @since TBD
	 */
	public function remove_filters() {}

	/**
	 * Allows the "Category Colors" tab to save settings by removing it from the no-save tabs array.
	 *
	 * @since TBD
	 *
	 * @param array $no_save_tabs An array of tab slugs that should not allow saving.
	 *
	 * @return array The modified array of no-save tabs.
	 */
	public function allow_tab_to_save( $no_save_tabs ) {
		$key = array_search( Settings::$tab_slug, $no_save_tabs, true );
		if ( false !== $key ) {
			unset( $no_save_tabs[ $key ] );
		}

		return $no_save_tabs;
	}

	/**
	 * Adds the "Category Colors" tab to the settings page.
	 *
	 * @since TBD
	 */
	public function add_category_colors_tab(): void {
		$this->container->make( Category_Colors_Settings::class )->register_tab();
	}

	/**
	 * Renders the content for the "Category Colors" settings tab.
	 *
	 * @since TBD
	 */
	public function category_colors_tab_content(): void {
		$this->container->make( Category_Colors_Settings::class )->render_fields();
	}

	/**
	 * Saves the settings for the "Category Colors" tab.
	 *
	 * @since TBD
	 */
	public function save_category_colors(): void {
		$this->container->make( Category_Colors_Settings::class )->save_category_color_settings();
	}
}
