<?php

namespace TEC\Events\Category_Colors;

use Tribe__Events__Main;
use Tribe__Template;
use WP_Term;

class Category_Colors {

	/**
	 * The taxonomy used for event categories.
	 *
	 * @var string
	 */
	public string $taxonomy = Tribe__Events__Main::TAXONOMY;

	/**
	 * Meta key for storing the foreground color of a category.
	 *
	 * @since TBD
	 * @var string
	 */
	public static string $meta_foreground_slug = 'tec-category-color-foreground';

	/**
	 * Meta key for storing the background color of a category.
	 *
	 * @since TBD
	 * @var string
	 */
	public static string $meta_background_slug = 'tec-category-color-background';

	/**
	 * Meta key for storing the text color of a category.
	 *
	 * @since TBD
	 * @var string
	 */
	public static string $meta_text_color_slug = 'tec-category-color-text-color';

	/**
	 * Meta key for marking a category as selected in the settings.
	 *
	 * Used to track whether a category is selected or not in the Category Colors settings.
	 *
	 * @since TBD
	 * @var string
	 */
	public static string $meta_selected_category_slug = 'tec-category-selected-category';

	/**
	 * Stores the instance of the template class.
	 *
	 * Used for rendering templates within the settings tab.
	 *
	 * @since TBD
	 * @var Tribe__Template|null
	 */
	protected ?Tribe__Template $template = null;

	/**
	 * Retrieves or initializes the template object.
	 *
	 * @since TBD
	 *
	 * @return Tribe__Template The initialized template object.
	 */
	public function get_template(): Tribe__Template {
		if ( is_null( $this->template ) ) {
			$this->template = ( new Tribe__Template() )
				->set_template_origin( tribe( 'tec.main' ) )
				->set_template_folder( 'src/admin-views' )
				->set_template_context_extract( true )
				->set_template_folder_lookup( false );
		}

		return $this->template;
	}

	/**
	 * Validates a hex color.
	 *
	 * Ensures the input is a valid hex color. If invalid, returns an empty string.
	 *
	 * @since TBD
	 *
	 * @param string $color The color to validate.
	 *
	 * @return string The validated hex color or an empty string.
	 * @todo  redscar - Do we have a trait, or a utility for checking valid hex colors?
	 */
	public static function validate_hex_color( $color ): string {
		if ( ! is_string( $color ) ) {
			return '';
		}

		return sanitize_hex_color( $color ) ?: '';
	}

	/**
	 * Update metadata for terms in the taxonomy.
	 *
	 * @since TBD
	 *
	 * @param array $selected_categories Slugs of the selected categories.
	 * @param array $submitted_colors    Color blueprint data keyed by term slug.
	 */
	public function update_terms_meta( array $selected_categories, array $submitted_colors ): void {
		// Get all categories in the taxonomy.
		$all_terms = get_terms(
			[
				'taxonomy'   => $this->taxonomy,
				'hide_empty' => false,
			]
		);

		foreach ( $all_terms as $term ) {
			$this->update_term_selection_meta( $term, $selected_categories );
			$this->update_term_color_meta( $term, $submitted_colors );
		}
	}

	/**
	 * Update the color-related meta for a term.
	 *
	 * @since TBD
	 *
	 * @param WP_Term $term             The term object.
	 * @param array   $submitted_colors Color blueprint data keyed by term slug.
	 */
	protected function update_term_color_meta( WP_Term $term, array $submitted_colors ): void {
		if ( isset( $submitted_colors[ $term->slug ] ) ) {
			$colors = $submitted_colors[ $term->slug ];
			update_term_meta( $term->term_id, self::$meta_foreground_slug, $this->validate_hex_color( $colors['foreground'] ?? '' ) );
			update_term_meta( $term->term_id, self::$meta_background_slug, $this->validate_hex_color( $colors['background'] ?? '' ) );
			update_term_meta( $term->term_id, self::$meta_text_color_slug, $this->validate_hex_color( $colors['text-color'] ?? '' ) );
		} else {
			// Optionally delete color meta if not in the submitted data.
			delete_term_meta( $term->term_id, self::$meta_foreground_slug );
			delete_term_meta( $term->term_id, self::$meta_background_slug );
			delete_term_meta( $term->term_id, self::$meta_text_color_slug );
		}
	}

	/**
	 * Update the `tec-event-selected` meta for a term.
	 *
	 * @since TBD
	 *
	 * @param WP_Term $term                The term object.
	 * @param array   $selected_categories Slugs of the selected categories.
	 */
	protected function update_term_selection_meta( WP_Term $term, array $selected_categories ): void {
		$is_selected = in_array( $term->slug, $selected_categories, true );

		if ( $is_selected ) {
			update_term_meta( $term->term_id, self::$meta_selected_category_slug, true );
		} else {
			delete_term_meta( $term->term_id, self::$meta_selected_category_slug );
		}
	}

	/**
	 * Filters and validates an array of terms to ensure they are valid WP_Term objects.
	 *
	 * @since TBD
	 *
	 * @param array $terms An array of terms to validate.
	 *
	 * @return WP_Term[] Array of valid WP_Term objects.
	 */
	public function get_valid_terms( array $terms ): array {
		return array_filter(
			$terms,
			function ( $term ) {
				return $term instanceof \WP_Term
				       && ! empty( $term->term_id )
				       && ! empty( $term->slug );
			}
		);
	}
}
