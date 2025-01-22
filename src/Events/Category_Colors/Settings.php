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

	public $taxonomy = Tribe__Events__Main::TAXONOMY;

	/**
	 * Tab name identifier.
	 *
	 * @var string
	 */
	public static $tab_slug = 'category-colors';

	protected $terms = [];

	/**
	 * Stores the instance of the template.
	 *
	 * @since TBD
	 *
	 * @var Tribe__Template|null
	 */
	protected $template = null;

	protected $category_data = [];

	/**
	 * Class Constructor.
	 *
	 * Initializes terms and registers hooks.
	 *
	 * @since TBD
	 */
	public function __construct() {
		$this->initialize_terms();
		$this->populate_form();
	}

	/**
	 * Populates the form data with the saved category color settings.
	 *
	 * This method retrieves the saved categories from the database option
	 * and assigns them to the `category_data` property for use in the form.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	protected function populate_form() {
		$this->category_data['categories'] = get_option( 'tec_category_color_categories', [] );
		$this->category_data['blueprint']  = get_option( 'tec_category_color_blueprint', [] );
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

		return $this->get_template()->template(
			'category-colors/settings',
			[
				'categories' => $categories,
				'form_data'  => $this->category_data,
			]
		);
	}

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
	 * Initializes and filters category terms.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	protected function initialize_terms(): void {
		$all_terms = get_terms(
			[
				'taxonomy'   => $this->taxonomy,
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

		// Process and save selected categories.
		$selected_categories = $this->process_selected_categories();
		update_option( 'tec_category_color_categories', array_values( $selected_categories ) );

		// Process and save color blueprint.
		$submitted_colors = tec_get_request_var( 'tec_category_colors_blueprint', [] );
		$color_blueprint  = $this->process_color_blueprint( $submitted_colors, $selected_categories );
		update_option( 'tec_category_color_blueprint', $color_blueprint );
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
					'taxonomy'   => $this->taxonomy,
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
	 */
	private function validate_hex_color( $color ): string {
		if ( ! is_string( $color ) ) {
			return '';
		}

		return sanitize_hex_color( $color ) ?: '';
	}
}
