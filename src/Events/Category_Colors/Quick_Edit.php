<?php

namespace TEC\Events\Category_Colors;

class Quick_Edit {

	/**
	 * Instance of the Category_Colors class.
	 *
	 * This dependency provides access to shared logic and utilities for managing category colors.
	 *
	 * @since TBD
	 * @var Category_Colors
	 */
	protected Category_Colors $category_colors;

	/**
	 * Class Constructor.
	 *
	 * Initializes the Category Colors settings functionality. This includes:
	 * - Storing the provided Category_Colors instance for shared logic and utilities.
	 *
	 * @since TBD
	 *
	 * @param Category_Colors $category_colors An instance of the Category_Colors class.
	 *                                         This is used to access shared logic and utilities.
	 */
	public function __construct( Category_Colors $category_colors ) {
		$this->category_colors = $category_colors;
	}

	/**
	 * Adds Foreground, Background, and Text-Color columns to the taxonomy table.
	 *
	 * @since TBD
	 *
	 * @param array $columns Existing columns.
	 *
	 * @return array Modified columns.
	 */
	public function add_custom_taxonomy_columns( $columns ): array {
		$columns['tec-category-colors'] = __( 'Category Color Options', 'the-events-calendar' );

		return $columns;
	}

	/**
	 * Populates the values for custom taxonomy columns.
	 *
	 * @since TBD
	 *
	 * @param string $output      Default column output.
	 * @param string $column_name Column name.
	 * @param int    $term_id     Term ID.
	 *
	 * @return void
	 */
	public function populate_custom_taxonomy_column( $output, $column_name, $term_id ): void {
		if ( 'tec-category-colors' !== $column_name ) {
			return;
		}
		$meta_key_map = [
			'foreground' => Category_Colors::$meta_foreground_slug,
			'background' => Category_Colors::$meta_background_slug,
			'text_color' => Category_Colors::$meta_text_color_slug,
		];

		// @todo - Probably turn this into a template.
		foreach ( $meta_key_map as $meta_key => $meta_value ) {
			$value = get_term_meta( $term_id, $meta_value, true );
			echo esc_html( $value ?: __( 'None', 'the-events-calendar' ) ) . '</br>';
		}
	}

	/**
	 * Adds custom fields (Foreground, Background, Text-Color) to the Quick Edit form.
	 *
	 * @since TBD
	 *
	 * @param string $column_name Column name.
	 * @param string $post_type   Post type.
	 * @param string $taxonomy    Taxonomy name.
	 *
	 * @return void
	 */
	public function add_custom_quick_edit_fields( $column_name, $post_type, $taxonomy ): void {
		// Ensure we're working with the correct taxonomy.
		if ( 'tribe_events_cat' !== $taxonomy ) {
			return;
		}

		// Ensure we're working with the correct column.
		if ( 'tec-category-colors' !== $column_name ) {
			return;
		}
		// @todo - Add javascript/jquery to prepopulate the quick edit fields.

		$this->category_colors->get_template()->template( 'category-colors/quick-edit-color-selection' );
	}

	/**
	 * Save custom fields from Quick Edit for taxonomy terms.
	 *
	 * @param int    $term_id  The ID of the term being edited.
	 * @param string $taxonomy The taxonomy being edited.
	 */
	public function save_quick_edit_custom_fields( int $term_id, string $taxonomy ) {
		// Ensure this is for the correct taxonomy.
		if ( 'tribe_events_cat' !== $taxonomy ) {
			return;
		}

		// Verify nonce to ensure the request is valid.
		check_ajax_referer( 'taxinlineeditnonce', '_inline_edit' );

		$term = get_term( $term_id, $taxonomy );
		if ( is_wp_error( $term ) || empty( $term ) ) {
			return;
		}

		// Define the meta keys for the custom fields.
		$meta_keys = [
			'tec-category-color-foreground' => Category_Colors::$meta_foreground_slug,
			'tec-category-color-background' => Category_Colors::$meta_background_slug,
			'tec-category-color-text-color' => Category_Colors::$meta_text_color_slug,
		];

		update_term_meta( $term->term_id, Category_Colors::$meta_selected_category_slug, true );

		// Loop through the expected fields and save them if they are present.
		foreach ( $meta_keys as $field_key => $meta_key ) {
			$value = tec_get_request_var( $field_key, null );

			// If the value exists, sanitize and save it.
			if ( null !== $value ) {
				$sanitized_value = sanitize_text_field( $value );
				update_term_meta( $term_id, $meta_key, $sanitized_value );
			}
		}
	}

}
