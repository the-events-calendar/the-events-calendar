<?php
/**
 * Handles quick edit functionality for category colors in the WordPress admin.
 *
 * @since TBD
 *
 * @package TEC\Events\Category_Colors\Admin
 */

namespace TEC\Events\Category_Colors\Admin;

use TEC\Events\Category_Colors\Event_Category_Meta;
use TEC\Events\Category_Colors\Meta_Keys;
use Tribe__Events__Main;
use InvalidArgumentException;

/**
 * Class Quick_Edit
 *
 * Provides functionality for editing category color settings via Quick Edit in the WordPress admin.
 * Handles displaying and saving color fields in the Quick Edit interface of the category list table.
 *
 * @since TBD
 */
class Quick_Edit extends Abstract_Admin {
	/**
	 * Adds custom columns to the Category Table.
	 *
	 * @since TBD
	 *
	 * @param array<string,string> $columns Existing columns in the category table.
	 *
	 * @return array<string,string> Modified columns with added color fields.
	 */
	public function add_columns( array $columns ): array {
		$columns['category_priority'] = __( 'Priority', 'the-events-calendar' );
		$columns['category_color']    = __( 'Category Color', 'the-events-calendar' );

		return $columns;
	}

	/**
	 * Populates the custom column data.
	 *
	 * @since TBD
	 *
	 * @param string $content     Current column content.
	 * @param string $column_name Column being processed.
	 * @param int    $term_id     The category term ID.
	 *
	 * @return string Updated content.
	 */
	public function add_custom_column_data( string $content, string $column_name, int $term_id ): string {
		try {
			$meta = tribe( Event_Category_Meta::class )->set_term( $term_id );
		} catch ( InvalidArgumentException $e ) {
			return $content;
		}

		if ( 'category_priority' === $column_name ) {
			$content = $this->get_column_category_priority( $meta );
		}

		if ( 'category_color' === $column_name ) {
			$content = $this->get_column_category_color_preview( $meta );
		}

		return $content;
	}

	/**
	 * Retrieves the category priority.
	 *
	 * @since TBD
	 *
	 * @param Event_Category_Meta $meta The metadata handler.
	 *
	 * @return string Priority value.
	 */
	protected function get_column_category_priority( Event_Category_Meta $meta ): string {
		$meta_key = tribe( Meta_Keys::class )->get_key( 'priority' );
		$priority = $meta_key ? $meta->get( $meta_key ) : '0';

		return esc_html( absint( $priority ) ?: '0' );
	}

	/**
	 * Generates the category color preview for the taxonomy table.
	 *
	 * @since TBD
	 *
	 * @param Event_Category_Meta $meta The metadata handler.
	 *
	 * @return string HTML for color preview or `-` if no colors exist.
	 */
	protected function get_column_category_color_preview( Event_Category_Meta $meta ): string {
		$meta_keys = tribe( Meta_Keys::class )->get_all_keys();
		
		$category_color_fields = array_map( function( $key, $meta_key ) use ( $meta ) {
			$value = esc_attr( $meta->get( $meta_key ) );
			return in_array( $key, [ 'primary', 'secondary', 'text' ], true ) 
				? sanitize_hex_color( $value )
				: $value;
		}, array_keys( $meta_keys ), $meta_keys );

		$category_color_fields = array_combine( array_keys( $meta_keys ), $category_color_fields );

		// If no primary or secondary color is set, return transparent
		if ( empty( $category_color_fields['primary'] ) || empty( $category_color_fields['secondary'] ) ) {
			return 'transparent';
		}

		return $this->get_template()->template( 'category-color-preview', $category_color_fields, false );
	}

	/**
	 * Adds custom fields to the Quick Edit box.
	 *
	 * @since TBD
	 *
	 * @param string $column_name Column name being processed.
	 * @param string $screen      Current screen type.
	 *
	 * @return string
	 */
	public function add_quick_edit_fields( string $column_name, string $screen ): string {
		if ( 'category_color' !== $column_name ) {
			return '';
		}

		if ( 'edit-tags' !== $screen ) {
			return '';
		}

		return $this->get_column_category_color_field();
	}

	/**
	 * Generates the Quick Edit fields for category colors.
	 *
	 * @since TBD
	 *
	 * @return string
	 */
	protected function get_column_category_color_field(): string {
		$context = [
			'taxonomy'        => Tribe__Events__Main::TAXONOMY,
			'category_colors' => [],
		];

		return $this->get_template()->template( 'quick-edit-color-selection', $context );
	}
}
