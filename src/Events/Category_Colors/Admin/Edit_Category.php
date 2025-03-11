<?php

namespace TEC\Events\Category_Colors\Admin;

use TEC\Events\Category_Colors\Event_Category_Meta;
use TEC\Events\Category_Colors\Meta_Keys;

class Edit_Category extends Abstract_Admin {

	/**
	 * Displays custom fields in the "Add New Category" form.
	 *
	 * @param object $tag      The term object.
	 * @param object $taxonomy The taxonomy object.
	 *
	 * @return string
	 */
	/**
	 * Displays custom fields in the "Add New Category" form.
	 *
	 * @param object $tag      The term object.
	 * @param object $taxonomy The taxonomy object.
	 *
	 * @return string
	 */
	public function display_category_fields( $tag, $taxonomy ) {
		if ( empty( $tag->term_id ) ) {
			return '';
		}

		// Get all meta keys.
		$meta_keys = tribe( Meta_Keys::class )->get_all_keys();

		// Initialize category colors array with default empty values.
		$category_colors = array_fill_keys( array_keys( $meta_keys ), '' );

		// Set default for 'priority' explicitly as 0 (since it's numeric).
		$category_colors['priority'] = 0;

		// Retrieve the meta data for the given term.
		$meta = tribe( Event_Category_Meta::class )->set_term( $tag->term_id );

		// Loop through meta keys and fetch values.
		foreach ( $meta_keys as $key => $full_key ) {
			// Ensure we store the value using the correct short key (primary, secondary, text, etc.).
			$category_colors[ $key ] = $meta->get( $full_key, '' );
		}

		$context = [
			'taxonomy'        => $taxonomy,
			'category_colors' => $category_colors,
		];

		return $this->get_template()->template( 'edit-category-fields-table', $context );
	}



	/**
	 * Saves the custom field when a category is created.
	 *
	 * @param int $term_id The ID of the newly created term.
	 */
	public function save_category_fields( $term_id ) {
		// Retrieve submitted category colors.
		$category_colors = tribe_get_request_var( 'tec_events_category-color', false );

		// Bail early if data doesn't exist or isn't an array.
		if ( empty( $category_colors ) || ! is_array( $category_colors ) ) {
			return;
		}

		// Get the Event_Category_Meta instance for this term.
		$meta = tribe( Event_Category_Meta::class )->set_term( $term_id );

		// Define default values.
		$defaults = [
			'primary'    => '',
			'secondary' => '',
			'text'       => '',
			'priority'   => 0,
		];

		// Define which keys should be validated as hex colors.
		$color_fields = [ 'primary', 'secondary', 'text' ];

		// Use `wp_parse_args()` to apply defaults only when needed.
		$category_colors = wp_parse_args( $category_colors, $defaults );

		// Save meta values in a loop.
		foreach ( $category_colors as $key => $value ) {
			// Retrieve the full meta key using Meta_Keys helper.
			$meta_key = tribe( Meta_Keys::class )->get_key( $key );

			// Skip if the key isn't valid.
			if ( empty( $meta_key ) ) {
				continue;
			}

			// Sanitize values based on type.
			if ( in_array( $key, $color_fields, true ) ) {
				$sanitized_value = sanitize_hex_color( $value );
			} elseif ( 'priority' === $key ) {
				$sanitized_value = absint( $value );
			} else {
				$sanitized_value = sanitize_text_field( $value );
			}


			// Store in meta.
			$meta->set( $meta_key, $sanitized_value );
		}
		$meta->save();
		/**
		 * Fires after category colors are saved.
		 *
		 * Allows additional actions to be performed after a category's color fields are stored.
		 *
		 * @since TBD
		 *
		 * @param int   $term_id          The ID of the updated term.
		 * @param array $category_colors  The sanitized category color values that were saved.
		 */
		do_action( 'tec_events_category_colors_saved', $term_id, $category_colors );
	}
}
