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

		add_filter( 'manage_edit-tribe_events_cat_columns', [ $this, 'add_custom_taxonomy_columns' ] );

		add_action( 'manage_tribe_events_cat_custom_column', [ $this, 'populate_custom_taxonomy_column' ], 10, 3 );

		add_action( 'quick_edit_custom_box', [ $this, 'add_custom_quick_edit_field' ], 10, 3 );

		add_action( 'edited_term_taxonomy', [ $this, 'save_quick_edit_custom_fields' ], 10, 2 );
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

	/**
	 * Adds custom taxonomy columns for Foreground, Background, and Text-Color.
	 *
	 * @since TBD
	 *
	 * @param array $columns An array of existing taxonomy columns.
	 *
	 * @return void
	 */
	public function add_custom_taxonomy_columns( $columns ) {
		return $this->container->make( Quick_Edit::class )->add_custom_taxonomy_columns( $columns );
	}

	/**
	 * Populates the values for the custom taxonomy columns.
	 *
	 * @since TBD
	 *
	 * @param string $output      The current column output (default empty string).
	 * @param string $column_name The name of the column being rendered.
	 * @param int    $term_id     The ID of the term being rendered.
	 *
	 * @return void
	 */
	public function populate_custom_taxonomy_column( $output, $column_name, $term_id ) {
		$this->container->make( Quick_Edit::class )->populate_custom_taxonomy_column( $output, $column_name, $term_id );
	}

	/**
	 * Adds custom Quick Edit fields for Foreground, Background, and Text-Color.
	 *
	 * @since TBD
	 *
	 * @param string $column_name The name of the column being edited.
	 * @param string $post_type   The post type of the Quick Edit form.
	 * @param string $taxonomy    The taxonomy being edited.
	 *
	 * @return void
	 */
	public function add_custom_quick_edit_field( $column_name, $post_type, $taxonomy ) {
		$this->container->make( Quick_Edit::class )->add_custom_quick_edit_fields( $column_name, $post_type, $taxonomy );
	}

	/**
	 * Save custom fields from Quick Edit for taxonomy terms using `tec_get_request_var`.
	 *
	 * @param int    $term_id  The ID of the term being edited.
	 * @param string $taxonomy The taxonomy being edited.
	 */
	public function save_quick_edit_custom_fields( $term_id, $taxonomy ) {
		$this->container->make( Quick_Edit::class )->save_quick_edit_custom_fields( $term_id, $taxonomy );
	}

}
