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

/**
 * Class Events_Variables
 *
 * @since 6.14.0
 *
 * @package TEC\Events\Integrations\Plugins\WordPress_SEO
 */
class Events_Variables {

	/**
	 * Register the custom variables with Yoast SEO.
	 *
	 * @since 6.14.0
	 */
	public function register() {
		add_action( 'wpseo_register_extra_replacements', [ $this, 'register_custom_variables' ] );
	}

	/**
	 * Register all custom variables with Yoast SEO.
	 *
	 * @since 6.14.0
	 */
	public function register_custom_variables() {
		wpseo_register_var_replacement( '%%event_start_date%%', [ $this, 'get_event_start_date' ], 'advanced', 'Get the event start date' );
		wpseo_register_var_replacement( '%%event_end_date%%', [ $this, 'get_event_end_date' ], 'advanced', 'Get the event end date' );
		wpseo_register_var_replacement( '%%venue_title%%', [ $this, 'get_venue_title' ], 'advanced', 'Get the venue name' );
		wpseo_register_var_replacement( '%%venue_city%%', [ $this, 'get_venue_city' ], 'advanced', 'Get the venue city' );
		wpseo_register_var_replacement( '%%venue_state%%', [ $this, 'get_venue_state' ], 'advanced', 'Get the venue state' );
		wpseo_register_var_replacement( '%%organizer_title%%', [ $this, 'get_organizer_title' ], 'advanced', 'Get the organizer name' );
	}

	/**
	 * Get the event start date.
	 *
	 * @since 6.14.0
	 *
	 * @return string The event start date.
	 */
	public function get_event_start_date() {
		$event_id = get_the_ID();
		if ( ! $event_id || TEC_Plugin::POSTTYPE !== get_post_type( $event_id ) ) {
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
		$event_id = get_the_ID();
		if ( ! $event_id || TEC_Plugin::POSTTYPE !== get_post_type( $event_id ) ) {
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
		$event_id = get_the_ID();
		if ( ! $event_id || TEC_Plugin::POSTTYPE !== get_post_type( $event_id ) ) {
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
		$event_id = get_the_ID();
		if ( ! $event_id || TEC_Plugin::POSTTYPE !== get_post_type( $event_id ) ) {
			return '';
		}

		$venue_id = tribe_get_venue_id( $event_id );
		if ( ! $venue_id ) {
			return '';
		}

		$venue_object = tribe_get_venue_object( $venue_id );
		if ( ! $venue_object || ! is_object( $venue_object ) ) {
			return '';
		}

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
		$event_id = get_the_ID();
		if ( ! $event_id || TEC_Plugin::POSTTYPE !== get_post_type( $event_id ) ) {
			return '';
		}

		$venue_id = tribe_get_venue_id( $event_id );
		if ( ! $venue_id ) {
			return '';
		}

		$venue_object = tribe_get_venue_object( $venue_id );
		if ( ! $venue_object || ! is_object( $venue_object ) ) {
			return '';
		}

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
		$event_id = get_the_ID();
		if ( ! $event_id || TEC_Plugin::POSTTYPE !== get_post_type( $event_id ) ) {
			return '';
		}

		return tribe_get_organizer( $event_id );
	}
}
