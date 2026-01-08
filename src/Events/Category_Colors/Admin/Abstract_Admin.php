<?php
/**
 * Abstract base class for category color admin functionality.
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
use Tribe__Template;

/**
 * Class Abstract_Admin
 *
 * Abstract base class that provides shared functionality for handling category colors
 * in the WordPress admin. Includes methods for template rendering and default values.
 *
 * @since 6.14.0
 */
abstract class Abstract_Admin {
	use Meta_Keys_Trait;

	/**
	 * Stores the instance of the template engine used for rendering.
	 *
	 * @since 6.14.0
	 *
	 * @var Tribe__Template
	 */
	protected $template;

	/**
	 * Constructor for the Abstract_Admin class.
	 *
	 * @since 6.14.0
	 *
	 * @param Tribe__Template|null $template The template instance to use for rendering.
	 */
	public function __construct( ?Tribe__Template $template = null ) {
		if ( null === $template || empty( $template->get_template_folder() ) ) {
			$template = new Tribe__Template();
			$template->set_template_origin( Tribe__Events__Main::instance() );
			$template->set_template_folder( 'src/admin-views/category-colors/' );
			$template->set_template_context_extract( true );
			$template->set_template_folder_lookup( false );
		}

		$this->template = $template;
	}

	/**
	 * Gets the template instance used for rendering HTML.
	 *
	 * @since 6.14.0
	 *
	 * @return Tribe__Template
	 */
	public function get_template(): Tribe__Template {
		return $this->template;
	}

	/**
	 * Gets the default values for category fields.
	 *
	 * @since 6.14.0
	 *
	 * @return array<string, mixed>
	 */
	protected function get_default_values(): array {
		return [
			'primary'          => '',
			'secondary'        => '',
			'text'             => '',
			'priority'         => 0,
			'hide_from_legend' => '',
		];
	}

	/**
	 * Gets the category colors for a term.
	 *
	 * @since 6.14.0
	 *
	 * @param int $term_id The term ID to get colors for.
	 *
	 * @return array<string, mixed> The category colors.
	 */
	protected function get_category_colors( int $term_id ): array {
		// Get all meta keys.
		$meta_keys = $this->get_all_keys();

		// Initialize category colors array with default empty values.
		$category_colors = array_fill_keys( array_keys( $meta_keys ), '' );

		// Set default for 'priority' explicitly as 0 (since it's numeric).
		$category_colors['priority'] = 0;

		// Retrieve the meta data for the given term.
		$meta = tribe( Event_Category_Meta::class )->set_term( $term_id );

		// Loop through meta keys and fetch values.
		foreach ( $meta_keys as $key => $full_key ) {
			// Ensure we store the value using the correct short key (primary, secondary, text, etc.).
			$category_colors[ $key ] = $meta->get( $full_key );
		}

		return $category_colors;
	}

	/**
	 * Sanitizes a value based on its key.
	 *
	 * @since 6.14.0
	 *
	 * @param string $key   The key identifying the type of value.
	 * @param mixed  $value The value to sanitize.
	 *
	 * @return mixed The sanitized value.
	 */
	protected function sanitize_value( string $key, $value ) {
		if ( 'hide_from_legend' === $key ) {
			return sanitize_text_field( $value );
		} elseif ( 'priority' === $key ) {
			return absint( $value );
		} else {
			return sanitize_hex_color( $value );
		}
	}

	/**
	 * Saves category fields to term meta.
	 *
	 * @since 6.14.0
	 *
	 * @param int $term_id The term ID to save fields for.
	 *
	 * @return void
	 */
	public function save_category_fields( int $term_id ): void {
		// Verify nonce.
		if ( ! wp_verify_nonce( tec_get_request_var( 'tec_category_colors_nonce', '' ), 'save_category_colors' ) ) {
			return;
		}

		// Verify user can edit this term.
		if ( ! current_user_can( 'edit_term', $term_id ) ) {
			return;
		}

		// Retrieve submitted category colors.
		$category_colors = tec_get_request_var( 'tec_events_category-color', false );

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
			try {
				// Retrieve the full meta key using Meta_Keys helper.
				$meta_key = $this->get_key( $key );

				// Sanitize and store the value.
				$meta->set( $meta_key, $this->sanitize_value( $key, $value ) );
			} catch ( InvalidArgumentException $e ) {
				// Skip invalid keys silently.
				continue;
			}
		}

		$meta->save();

		/**
		 * Fires after category colors have been saved.
		 *
		 * @since 6.14.0
		 *
		 * @param int $term_id The term ID that was updated.
		 */
		do_action( 'tec_events_category_colors_saved', $term_id );
	}
}
