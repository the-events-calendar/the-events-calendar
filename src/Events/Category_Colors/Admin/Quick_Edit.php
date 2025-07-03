<?php
/**
 * Handles quick edit functionality for category colors in the WordPress admin.
 *
 * @since 6.14.0
 *
 * @package TEC\Events\Category_Colors\Admin
 */

namespace TEC\Events\Category_Colors\Admin;

use InvalidArgumentException;
use TEC\Events\Category_Colors\Event_Category_Meta;
use TEC\Events\Category_Colors\Meta_Keys_Trait;
use Tribe__Events__Main;
use WP_Term;

/**
 * Class Quick_Edit
 *
 * Provides functionality for editing category color settings via Quick Edit in the WordPress admin.
 * Handles displaying and saving color fields in the Quick Edit interface of the category list table.
 *
 * @since 6.14.0
 */
class Quick_Edit extends Abstract_Admin {
	use Meta_Keys_Trait;

	/**
	 * Adds custom columns to the Category Table.
	 *
	 * @since 6.14.0
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
	 * @since 6.14.0
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
			$content = $this->get_column_category_color_preview( $meta, $term_id );
		}

		return $content;
	}

	/**
	 * Retrieves the category priority.
	 *
	 * @since 6.14.0
	 *
	 * @param Event_Category_Meta $meta The metadata handler.
	 *
	 * @return string Priority value.
	 */
	protected function get_column_category_priority( Event_Category_Meta $meta ): string {
		try {
			$meta_key = $this->get_key( 'priority' );
			$priority = $meta->get( $meta_key );

			return esc_html( absint( $priority ) ?: '0' );
		} catch ( InvalidArgumentException $e ) {
			// If priority key is invalid, return default value.
			return '0';
		}
	}

	/**
	 * Determines what to display in the color preview column.
	 *
	 * @since 6.14.0
	 *
	 * @param Event_Category_Meta $meta    The metadata handler.
	 * @param int                 $term_id The category term ID.
	 *
	 * @return string Either 'transparent' or the HTML for the color preview.
	 */
	protected function determine_color_preview_display( Event_Category_Meta $meta, int $term_id ): string {
		// Get the term to access its slug.
		$term = get_term( $term_id, Tribe__Events__Main::TAXONOMY );
		if ( ! $term instanceof WP_Term ) {
			return __( 'transparent', 'the-events-calendar' );
		}

		// Get values in a single pass.
		$fields = [];
		foreach ( $this->get_all_keys() as $key => $meta_key ) {
			$value          = $meta->get( $meta_key );
			$fields[ $key ] = $this->sanitize_value( $key, $value );
		}

		// If both colors are empty, return transparent.
		if ( empty( $fields['primary'] ) && empty( $fields['secondary'] ) ) {
			return __( 'transparent', 'the-events-calendar' );
		}

		// Add the category class identifier.
		$fields['category_class'] = 'tribe_events_cat-' . $term->slug;

		return $this->get_template()->template( 'category-color-preview', $fields, false );
	}

	/**
	 * Generates the category color preview for the taxonomy table.
	 *
	 * @since 6.14.0
	 *
	 * @param Event_Category_Meta $meta    The metadata handler.
	 * @param int                 $term_id The category term ID.
	 *
	 * @return string HTML for color preview square.
	 */
	protected function get_column_category_color_preview( Event_Category_Meta $meta, int $term_id ): string {
		return $this->determine_color_preview_display( $meta, $term_id );
	}

	/**
	 * Adds custom fields to the Quick Edit box.
	 *
	 * @since 6.14.0
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
	 * @since 6.14.0
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
