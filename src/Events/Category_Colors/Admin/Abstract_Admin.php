<?php
/**
 * Abstract base class for category color admin functionality.
 *
 * @since TBD
 *
 * @package TEC\Events\Category_Colors\Admin
 */

namespace TEC\Events\Category_Colors\Admin;

use TEC\Common\StellarWP\Assets\Asset;
use TEC\Common\StellarWP\Assets\Config as Assets_Config;
use TEC\Events\Category_Colors\Event_Category_Meta;
use TEC\Events\Category_Colors\Meta_Keys;
use Tribe__Events__Main;
use Tribe__Template;

/**
 * Class Abstract_Admin
 *
 * Abstract base class that provides shared functionality for handling category colors
 * in the WordPress admin. Includes methods for template rendering and default values.
 *
 * @since TBD
 */
abstract class Abstract_Admin {

	/**
	 * Stores the instance of the template engine used for rendering.
	 *
	 * @since TBD
	 *
	 * @var Tribe__Template
	 */
	protected $template;

	/**
	 * Gets the template instance used for rendering HTML.
	 *
	 * @since TBD
	 *
	 * @return Tribe__Template
	 */
	public function get_template(): Tribe__Template {
		if ( empty( $this->template ) ) {
			$this->template = new Tribe__Template();
			$this->template->set_template_origin( Tribe__Events__Main::instance() );
			$this->template->set_template_folder( 'src/admin-views/category-colors/' );
			$this->template->set_template_context_extract( true );
			$this->template->set_template_folder_lookup( false );
		}

		return $this->template;
	}

	/**
	 * Gets the default values for category fields.
	 *
	 * @since TBD
	 *
	 * @return array<string, mixed>
	 */
	protected function get_default_values(): array {
		return [
			'primary'    => '',
			'secondary' => '',
			'text'      => '',
			'priority'  => 0,
			'hide_from_legend' => '',
		];
	}

	/**
	 * Gets the color field keys that should be validated as hex colors.
	 *
	 * @since TBD
	 *
	 * @return array<string>
	 */
	protected function get_color_fields(): array {
		return [ 'primary', 'secondary', 'text' ];
	}

	/**
	 * Gets the category colors for a term.
	 *
	 * @since TBD
	 *
	 * @param int $term_id The term ID to get colors for.
	 * @return array<string, mixed> The category colors.
	 */
	protected function get_category_colors( int $term_id ): array {
		// Get all meta keys.
		$meta_keys = tribe( Meta_Keys::class )->get_all_keys();

		// Initialize category colors array with default empty values.
		$category_colors = array_fill_keys( array_keys( $meta_keys ), '' );

		// Set default for 'priority' explicitly as 0 (since it's numeric).
		$category_colors['priority'] = 0;

		// Retrieve the meta data for the given term.
		$meta = tribe( Event_Category_Meta::class )->set_term( $term_id );

		// Loop through meta keys and fetch values.
		foreach ( $meta_keys as $key => $full_key ) {
			// Ensure we store the value using the correct short key (primary, secondary, text, etc.).
			$category_colors[ $key ] = $meta->get( $full_key, '' );
		}

		return $category_colors;
	}

	/**
	 * Saves category fields to term meta.
	 *
	 * @since TBD
	 *
	 * @param int $term_id The term ID to save fields for.
	 * @return void
	 */
	public function save_category_fields( int $term_id ): void {
		// Verify nonce.
		if ( ! wp_verify_nonce( tec_get_request_var( 'tec_category_colors_nonce', '' ), 'save_category_colors' ) ) {
			return;
		}

		// Retrieve submitted category colors.
		$category_colors = tribe_get_request_var( 'tec_events_category-color', false );

		// Bail early if data doesn't exist or isn't an array.
		if ( empty( $category_colors ) || ! is_array( $category_colors ) ) {
			return;
		}

		// Get the Event_Category_Meta instance for this term.
		$meta = tribe( Event_Category_Meta::class )->set_term( $term_id );

		// Use `wp_parse_args()` to apply defaults only when needed.
		$category_colors = wp_parse_args( $category_colors, $this->get_default_values() );

		// Save meta values in a loop.
		foreach ( $category_colors as $key => $value ) {
			// Retrieve the full meta key using Meta_Keys helper.
			$meta_key = tribe( Meta_Keys::class )->get_key( $key );

			// Skip if the key isn't valid.
			if ( empty( $meta_key ) ) {
				continue;
			}

			// Sanitize values based on type.
			if ( in_array( $key, $this->get_color_fields(), true ) ) {
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
	}

	/**
	 * Enqueues admin assets for category colors.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function enqueue_assets(): void {
		Assets_Config::add_group_path( 'tec-category-colors', Tribe__Events__Main::instance()->plugin_path, 'src/resources' );

		Asset::add(
			'tec-category-colors-admin-js',
			'/js/admin/category-colors/admin-category.js',
			Tribe__Events__Main::VERSION
		)
			->add_to_group_path( 'tec-category-colors' )
			->enqueue_on( 'admin_enqueue_scripts' )
			->set_condition( [ $this, 'is_category_page' ] )
			->set_dependencies( 'jquery', 'wp-color-picker' )
			->register();

		Asset::add(
			'tec-category-colors-admin-style',
			'/css/admin/category-colors/admin-category.css',
			Tribe__Events__Main::VERSION
		)
			->add_to_group_path( 'tec-category-colors' )
			->enqueue_on( 'admin_enqueue_scripts' )
			->set_condition( [ $this, 'is_category_page' ] )
			->register();
	}

	/**
	 * Checks if the current page is a category management page.
	 *
	 * @since TBD
	 *
	 * @return bool Whether the current page is a category management page.
	 */
	public function is_category_page(): bool {
		$screen = get_current_screen();
		return isset( $screen->taxonomy ) && Tribe__Events__Main::TAXONOMY === $screen->taxonomy;
	}
}
