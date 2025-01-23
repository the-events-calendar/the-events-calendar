<?php

namespace TEC\Events\Category_Colors;

use Tribe__Settings_Tab;
use Tribe\Events\Admin\Settings as Admin_Settings;
use Tribe__Template;
use Tribe__Events__Main;
use WP_Term;

/**
 * Class Settings
 *
 * Handles the settings for Category Colors, including registering the settings tab
 * and rendering the associated fields.
 *
 * @since TBD
 */
class Settings {
	/**
	 * The taxonomy used for event categories.
	 *
	 * @var string
	 */
	public string $taxonomy = 'Tribe__Events__Main::TAXONOMY';

	/**
	 * Tab name identifier.
	 *
	 * Used to register and identify the settings tab for category colors.
	 *
	 * @since TBD
	 * @var string
	 */
	public static string $tab_slug = 'category-colors';

	/**
	 * List of terms for the taxonomy.
	 *
	 * Contains an array of terms that are filtered and used within the class.
	 *
	 * @since TBD
	 * @var WP_Term[]
	 */
	protected array $terms = [];

	/**
	 * Stores category data for the settings form.
	 *
	 * This includes both the list of categories and their associated blueprint data.
	 *
	 * @since TBD
	 * @var array
	 */
	protected array $category_data = [];

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
	 * - Initializing the terms to be used in the settings.
	 * - Populating the form data with saved settings and defaults.
	 *
	 * @since TBD
	 *
	 * @param Category_Colors $category_colors An instance of the Category_Colors class.
	 *                                         This is used to access shared logic and utilities.
	 */
	public function __construct( Category_Colors $category_colors ) {
		$this->category_colors = $category_colors;
		$this->initialize_terms();
		$this->populate_form();
	}

	/**
	 * Populates the form data with the saved category color settings.
	 *
	 * This method retrieves the saved categories and their associated color blueprint data
	 * from the database and assigns them to the `category_data` property for use in the form.
	 * It uses helper methods to process selected categories and term blueprints.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	protected function populate_form(): void {
		if ( empty( $this->terms ) ) {
			return;
		}

		$this->category_data['categories'] = $this->get_selected_categories();
		$this->category_data['blueprint']  = $this->get_term_blueprint();
	}

	/**
	 * Retrieves the selected categories based on the stored metadata.
	 *
	 * This method validates the terms before processing and skips invalid or malformed data.
	 *
	 * @since TBD
	 *
	 * @return array An array of slugs for the selected categories.
	 */
	private function get_selected_categories(): array {
		if ( empty( $this->terms ) ) {
			return [];
		}

		$valid_terms = $this->category_colors->get_valid_terms( $this->terms );

		$selected_categories = [];

		foreach ( $valid_terms as $term ) {
			// Retrieve metadata and check if the category is selected.
			$is_selected = get_term_meta( $term->term_id, $this->category_colors::$meta_selected_category_slug, true );

			if ( $is_selected ) {
				$selected_categories[] = sanitize_text_field( $term->slug );
			}
		}

		return $selected_categories;
	}

	/**
	 * Retrieves the color blueprint for each term.
	 *
	 * This method creates a blueprint for each term by fetching the associated
	 * metadata for foreground, background, and text colors. If no metadata exists,
	 * the values will default to an empty string.
	 *
	 * @since TBD
	 *
	 * @return array An associative array of term slugs mapped to their color blueprint data:
	 *               - `foreground`: The hex color for the foreground.
	 *               - `background`: The hex color for the background.
	 *               - `text-color`: The hex color for the text.
	 */
	private function get_term_blueprint(): array {
		if ( empty( $this->terms ) ) {
			return [];
		}

		$valid_terms = $this->category_colors->get_valid_terms( $this->terms );

		$blueprint = [];

		foreach ( $valid_terms as $term ) {
			$term_slug = sanitize_text_field( $term->slug );

			// Retrieve metadata for each term and assign default empty strings if not found.
			$blueprint[ $term_slug ] = [
				'foreground' => $this->category_colors->validate_hex_color( get_term_meta( $term->term_id, $this->category_colors::$meta_foreground_slug, true ) ),
				'background' => $this->category_colors->validate_hex_color( get_term_meta( $term->term_id, $this->category_colors::$meta_background_slug, true ) ),
				'text-color' => $this->category_colors->validate_hex_color( get_term_meta( $term->term_id, $this->category_colors::$meta_text_color_slug, true ) ),
			];
		}

		return $blueprint;
	}

	/**
	 * Registers the Category Colors tab to the settings page.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function register_tab(): void {
		// Only load the tab for event settings, not ticket settings.
		if ( ! tribe( Admin_Settings::class )->is_tec_events_settings() ) {
			return;
		}

		new Tribe__Settings_Tab(
			self::$tab_slug,
			esc_html__( 'Category Colors', 'the-events-calendar' ),
			[] // Add settings fields if required.
		);
	}

	/**
	 * Renders the fields for the Category Colors settings tab.
	 *
	 * @since TBD
	 *
	 * @return string Rendered HTML for the settings tab.
	 */
	public function render_fields(): string {
		$categories = $this->get_filtered_terms();

		$selected_category_slugs = $this->get_selected_categories();

		$selected_categories = array_filter(
			$categories,
			function ( $term ) use ( $selected_category_slugs ) {
				return in_array( $term->slug, $selected_category_slugs, true );
			}
		);

		return $this->category_colors->get_template()->template(
			'category-colors/settings',
			[
				'categories'          => $categories,
				'selected_categories' => $selected_categories,
				'form_data'           => $this->category_data,
			]
		);
	}


	/**
	 * Initializes and filters category terms.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	protected function initialize_terms(): void {
		if ( ! taxonomy_exists( $this->category_colors->taxonomy ) ) {
			$this->terms = [];

			return;
		}

		$all_terms = get_terms(
			[
				'taxonomy'   => $this->category_colors->taxonomy,
				'hide_empty' => false,
			]
		);

		// Filter out hidden terms.
		$visible_terms = $this->filter_hidden_terms( $all_terms );

		/**
		 * Filters the list of category terms for Category Colors.
		 *
		 * This filter allows the modification or removal of terms retrieved from the taxonomy.
		 *
		 * @since TBD
		 *
		 * @param WP_Term[] $visible_terms An array of all terms retrieved from the taxonomy.
		 *                                 Already partially filtered to hide hidden terms.
		 *                                 Each term is an instance of the `WP_Term` class.
		 *                                 The default array includes terms matching the criteria of the `get_terms` query.
		 *
		 * @return WP_Term[] Modified array of terms.
		 */
		$filtered_terms = apply_filters( 'tec_category_colors_categories', $visible_terms );

		// Ensure we have an array of terms after filtering.
		$this->terms = is_array( $filtered_terms ) ? $filtered_terms : [];
	}

	/**
	 * Filters out hidden terms based on a customizable suffix in their slug.
	 *
	 * This method removes terms whose slugs end with the configured hidden suffix,
	 * ensuring only visible terms are included in the results.
	 *
	 * @since TBD
	 *
	 * @param WP_Term[] $terms Array of terms to filter. Each term is an instance of the `WP_Term` class.
	 *
	 * @return WP_Term[] Array of terms with hidden terms removed.
	 */
	protected function filter_hidden_terms( array $terms ): array {
		/**
		 * Filter the suffix used to identify hidden terms in their slugs.
		 *
		 * By default, terms with slugs ending in `-hide` are considered hidden.
		 *
		 * @since TBD
		 *
		 * @param string $hidden_suffix The suffix used to identify hidden terms. Defaults to `-hide`.
		 */
		$hidden_suffix = apply_filters( 'tec_category_colors_hidden_suffix', '-hide' );

		return array_filter(
			$terms,
			function ( WP_Term $term ) use ( $hidden_suffix ) {
				return ! str_ends_with( $term->slug, $hidden_suffix );
			}
		);
	}

	/**
	 * Retrieves the filtered list of terms.
	 *
	 * @since TBD
	 *
	 * @return array Filtered list of category terms.
	 */
	public function get_filtered_terms(): array {
		return $this->terms;
	}

	/**
	 * Save the category color settings.
	 *
	 * Combines validation and saving to ensure only valid data is saved.
	 *
	 * @since TBD
	 */
	public function save_category_color_settings() {
		// Validate nonce.
		if ( ! wp_verify_nonce( tribe_get_request_var( 'tribe-save-settings' ), 'saving' ) ) {
			return;
		}

		// Process the selected categories.
		$selected_categories = $this->process_selected_categories();

		// Process the color blueprint data.
		$submitted_colors = tec_get_request_var( 'tec_category_colors_blueprint', [] );

		// Update the metadata for all terms in the taxonomy.
		$this->category_colors->update_terms_meta( $selected_categories, $submitted_colors );
	}

	/**
	 * Process and validate selected categories.
	 *
	 * Ensures only valid and sanitized categories are included.
	 *
	 * @since TBD
	 *
	 * @return array Validated and sanitized category slugs.
	 */
	protected function process_selected_categories(): array {
		$selected_categories = tec_get_request_var( 'tec_category_color_categories', [] );
		$selected_categories = is_array( $selected_categories ) ? array_map( 'sanitize_text_field', $selected_categories ) : [];

		$valid_categories = array_column(
			get_terms(
				[
					'taxonomy'   => $this->category_colors->taxonomy,
					'hide_empty' => false,
				]
			),
			'slug'
		);

		return array_values( array_unique( array_intersect( $selected_categories, $valid_categories ) ) );
	}

	/**
	 * Process and validate the submitted color blueprint.
	 *
	 * Ensures only valid categories and sanitized color data are included.
	 *
	 * @since TBD
	 *
	 * @param array $submitted_colors The raw submitted color blueprint data.
	 * @param array $valid_categories The validated and sanitized category slugs.
	 *
	 * @return array Validated and sanitized color blueprint.
	 */
	protected function process_color_blueprint( array $submitted_colors, array $valid_categories ): array {
		$color_blueprint = [];

		if ( empty( $submitted_colors ) ) {
			return $color_blueprint;
		}

		foreach ( $valid_categories as $slug ) {
			$colors                   = $submitted_colors[ $slug ] ?? [];
			$color_blueprint[ $slug ] = [
				'foreground' => $this->validate_hex_color( $colors['foreground'] ?? '' ),
				'background' => $this->validate_hex_color( $colors['background'] ?? '' ),
				'text-color' => $this->validate_hex_color( $colors['text-color'] ?? '' ),
			];
		}

		return $color_blueprint;
	}

}
