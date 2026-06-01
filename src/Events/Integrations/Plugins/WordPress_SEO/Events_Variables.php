<?php
/**
 * Handles The Events Calendar custom variables for Yoast SEO.
 *
 * @since 6.14.0
 *
 * @package TEC\Events\Integrations\Plugins\WordPress_SEO
 */

namespace TEC\Events\Integrations\Plugins\WordPress_SEO;

use Tribe__Events__Main as TEC_Plugin;
use WP_Term;

/**
 * Class Events_Variables
 *
 * @since 6.14.0
 *
 * @package TEC\Events\Integrations\Plugins\WordPress_SEO
 */
class Events_Variables {

	/**
	 * Flag to track if variables have been registered to prevent duplicate registration.
	 *
	 * @since 6.15.17
	 *
	 * @var bool
	 */
	private static $registered = false;

	/**
	 * Register the custom variables with Yoast SEO.
	 *
	 * @since 6.14.0
	 */
	public function register() {
		// Register variables when Yoast fires the registration action (inside setup_statics_once).
		// This is the primary registration method recommended by Yoast.
		add_action( 'wpseo_register_extra_replacements', [ $this, 'register_custom_variables' ] );

		// Populate term data for variable replacement on frontend.
		add_filter( 'wpseo_replacements', [ $this, 'populate_term_replace_vars' ], 10, 2 );
	}

	/**
	 * Register all custom variables with Yoast SEO.
	 *
	 * @since 6.14.0
	 *
	 * @return void
	 */
	public function register_custom_variables() {
		// Prevent duplicate registration.
		if ( self::$registered ) {
			return;
		}

		wpseo_register_var_replacement( '%%event_start_date%%', [ $this, 'get_event_start_date' ], 'advanced', 'Get the event start date' );
		wpseo_register_var_replacement( '%%event_end_date%%', [ $this, 'get_event_end_date' ], 'advanced', 'Get the event end date' );
		wpseo_register_var_replacement( '%%venue_title%%', [ $this, 'get_venue_title' ], 'advanced', 'Get the venue name' );
		wpseo_register_var_replacement( '%%venue_city%%', [ $this, 'get_venue_city' ], 'advanced', 'Get the venue city' );
		wpseo_register_var_replacement( '%%venue_state%%', [ $this, 'get_venue_state' ], 'advanced', 'Get the venue state' );
		wpseo_register_var_replacement( '%%organizer_title%%', [ $this, 'get_organizer_title' ], 'advanced', 'Get the organizer name' );

		self::$registered = true;
	}

	/**
	 * Get the current event ID if it's a valid event post.
	 *
	 * @since 6.15.17
	 *
	 * @return int|false The event ID, or false if not a valid event.
	 */
	private function get_current_event_id() {
		$event_id = get_the_ID();
		if ( ! $event_id || TEC_Plugin::POSTTYPE !== get_post_type( $event_id ) ) {
			return false;
		}

		return $event_id;
	}

	/**
	 * Get the venue object for the current event.
	 *
	 * @since 6.15.17
	 *
	 * @param int $event_id The event ID.
	 *
	 * @return object|null The venue object, or null if not found.
	 */
	private function get_venue_object( $event_id ) {
		$venue_id = tribe_get_venue_id( $event_id );
		if ( ! $venue_id ) {
			return null;
		}

		$venue_object = tribe_get_venue_object( $venue_id );
		if ( ! $venue_object || ! is_object( $venue_object ) ) {
			return null;
		}

		return $venue_object;
	}

	/**
	 * Get the event start date.
	 *
	 * @since 6.14.0
	 *
	 * @return string The event start date.
	 */
	public function get_event_start_date() {
		$event_id = $this->get_current_event_id();
		if ( ! $event_id ) {
			return '';
		}

		return tribe_get_start_date( $event_id, false, tribe_get_date_format() );
	}

	/**
	 * Get the event end date.
	 *
	 * @since 6.14.0
	 *
	 * @return string The event end date.
	 */
	public function get_event_end_date() {
		$event_id = $this->get_current_event_id();
		if ( ! $event_id ) {
			return '';
		}

		return tribe_get_end_date( $event_id, false, tribe_get_date_format() );
	}

	/**
	 * Get the venue title.
	 *
	 * @since 6.14.0
	 *
	 * @return string The venue title.
	 */
	public function get_venue_title() {
		$event_id = $this->get_current_event_id();
		if ( ! $event_id ) {
			return '';
		}

		return tribe_get_venue( $event_id );
	}

	/**
	 * Get the venue city.
	 *
	 * @since 6.14.0
	 *
	 * @return string The venue city.
	 */
	public function get_venue_city() {
		$event_id = $this->get_current_event_id();
		if ( ! $event_id ) {
			return '';
		}

		$venue_object = $this->get_venue_object( $event_id );

		return $venue_object->city ?? '';
	}

	/**
	 * Get the venue state.
	 *
	 * @since 6.14.0
	 *
	 * @return string The venue state.
	 */
	public function get_venue_state() {
		$event_id = $this->get_current_event_id();
		if ( ! $event_id ) {
			return '';
		}

		$venue_object = $this->get_venue_object( $event_id );

		return $venue_object->state ?? '';
	}

	/**
	 * Get the organizer title.
	 *
	 * @since 6.14.0
	 *
	 * @return string The organizer title.
	 */
	public function get_organizer_title() {
		$event_id = $this->get_current_event_id();
		if ( ! $event_id ) {
			return '';
		}

		return tribe_get_organizer( $event_id );
	}

	/**
	 * Populate term data in the args object for Yoast SEO variable replacement.
	 *
	 * @since 6.15.17
	 *
	 * @param WP_Term $term The term object.
	 * @param object  $args The args object (passed by reference).
	 *
	 * @return void
	 */
	private function populate_term_args( WP_Term $term, $args ): void {
		$args->name     = $term->name;
		$args->term_id  = $term->term_id;
		$args->taxonomy = $term->taxonomy;
	}

	/**
	 * Check if we're on an Event Category archive page.
	 *
	 * @since 6.15.17
	 *
	 * @param WP_Term $term The queried term.
	 *
	 * @return bool True if on an Event Category archive.
	 */
	private function is_event_category_archive( WP_Term $term ): bool {
		return is_tax( TEC_Plugin::TAXONOMY ) && $term->taxonomy === TEC_Plugin::TAXONOMY;
	}

	/**
	 * Check if we're on an Event Tag archive page.
	 *
	 * @since 6.15.17
	 *
	 * @param WP_Term $term The queried term.
	 *
	 * @return bool True if on an Event Tag archive.
	 */
	private function is_event_tag_archive( WP_Term $term ): bool {
		return is_tag()
			&& $term->taxonomy === 'post_tag'
			&& function_exists( 'tribe_is_event_query' )
			&& tribe_is_event_query();
	}

	/**
	 * Populate term data for Yoast SEO replace vars on Event Category and Tag archive pages.
	 *
	 * This ensures that Yoast SEO can properly replace %%term_title%% and other term-related
	 * variables when viewing Event Category or Event Tag archive pages. The method populates
	 * the $args object with term data (name, term_id, taxonomy) so that Yoast's variable
	 * replacement system can access it.
	 *
	 * @since 6.15.17
	 *
	 * @param array  $replacements The current replacements array.
	 * @param object $args         The args object passed to wpseo_replace_vars (passed by reference).
	 *
	 * @return array The unmodified replacements array (we only modify $args).
	 */
	public function populate_term_replace_vars( $replacements, $args ) {
		if ( is_admin() ) {
			return $replacements;
		}

		$term = get_queried_object();
		if ( ! $term instanceof WP_Term ) {
			return $replacements;
		}

		if ( $this->is_event_category_archive( $term ) || $this->is_event_tag_archive( $term ) ) {
			$this->populate_term_args( $term, $args );
		}

		return $replacements;
	}
}

