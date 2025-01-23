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

		add_filter( 'manage_edit-tribe_events_cat_columns', [ $this, 'add_custom_taxonomy_column' ] );

		add_action( 'manage_tribe_events_cat_custom_column', [ $this, 'populate_custom_taxonomy_column' ], 10, 3 );

		add_action( 'quick_edit_custom_box', [ $this, 'add_custom_quick_edit_field' ], 10, 3 );
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

	function add_custom_taxonomy_column( $columns ) {
		$columns['custom_field'] = 'custom field';

		return $columns;
	}

	function populate_custom_taxonomy_column( $output, $column_name, $term_id ) {
		if ( 'custom_field' === $column_name ) {
			// Get all term meta for the current term.
			$term_meta = get_term_meta( $term_id );

			// Filter for meta keys prefixed with 'tec-event-*'.
			$filtered_meta = array_filter(
				$term_meta,
				function ( $key ) {
					return str_starts_with( $key, 'tec-event-' );
				},
				ARRAY_FILTER_USE_KEY
			);

			if ( empty( $filtered_meta ) ) {
				echo esc_html__( 'N/A', 'your-text-domain' );

				return;
			}

			// Display meta key-value pairs.
			foreach ( $filtered_meta as $key => $value ) {
				echo '<strong>' . esc_html( $key ) . ':</strong> ' . esc_html( $value[0] ) . '<br>';
			}
		}
	}

	function add_custom_quick_edit_field( $column_name, $post_type, $taxonomy ) {
		// Check that we're adding to the correct column and taxonomy.
		if ( 'custom_field' !== $column_name || 'tribe_events_cat' !== $taxonomy ) {
			return;
		}

		?>
		<fieldset>
			<div class="inline-edit-col">
				<label>
					<span class="title"><?php esc_html_e( 'Custom Field', 'your-text-domain' ); ?></span>
					<span class="input-text-wrap">
                    <input type="text" name="custom_field" class="custom_field" value="">
                </span>
				</label>
			</div>
		</fieldset>
		<?php
	}

}
