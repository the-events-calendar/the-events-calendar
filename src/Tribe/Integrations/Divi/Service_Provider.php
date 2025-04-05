<?php
/**
 * Handles The Events Calendar integration with the Divi theme builder.
 *
 * @since   6.0.1
 *
 * @package Tribe\Events\Integrations\Divi
 */

namespace Tribe\Events\Integrations\Divi;

use TEC\Common\Contracts\Service_Provider as Provider_Contract;


/**
 * Class Service_Provider
 *
 * @since   6.0.1
 *
 * @package Tribe\Events\Integrations\Divi
 */
class Service_Provider extends Provider_Contract {


	/**
	 * Binds and sets up implementations.
	 *
	 * @since 6.0.1
	 */
	public function register() {
		$this->container->singleton( static::class, $this );

		$theme = wp_get_theme();

		if ( 'divi' !== strtolower( $theme->name ) && 'divi' !== strtolower( $theme->parent_theme ) ) {
			return;
		}

	}

	/**
	 * Hooks the filters and actions required for this integration to work.
	 *
	 * @since 6.0.1
	 */
	protected function hooks(): void {
		add_filter( 'tribe_post_id', [ $this, 'filter_tribe_post_id' ] );
	}

	/**
	 * Unregisters the filters and actions required for this integration to work.
	 *
	 * @since 6.0.2
	 *
	 * @return void
	 */
	public function unregister(): void {
		remove_filter( 'tribe_post_id', [ $this, 'filter_tribe_post_id' ] );
	}

	/**
	 * Get the $event_id using get_queried_object_id() for Divi users who aren't using the Default Events Template.
	 *
	 * @since 6.0.1
	 *
	 * @param int $event_id The event ID.
	 */
	public function filter_tribe_post_id( $event_id ) {
		// Try the "normal" way first.
		if ( empty( $event_id ) ) {
			$event_id = get_the_ID();
		}

		// Look for a post in the query variables.
		if ( ! tribe_is_event( $event_id ) && tribe_get_request_var( 'et_post_id' ) ) {
			$event_id = tribe_get_request_var( 'et_post_id' );
		}

		if ( ! tribe_is_event( $event_id ) ) {
			$event_id = get_queried_object_id();
		}

		return $event_id;
	}
}
