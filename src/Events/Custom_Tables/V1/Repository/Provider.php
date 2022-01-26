<?php
/**
 * Handles the Custom Tables integration, and compatibility, with
 * the Repositories.
 *
 * Here what implementations and filters are not relevant, are disconnected.
 *
 * @since   TBD
 *
 * @package TEC\Events\Custom_Tables\V1\Repository
 */

namespace TEC\Events\Custom_Tables\V1\Repository;

use tad_DI52_ServiceProvider as Service_Provider;
use TEC\Events\Custom_Tables\V1\WP_Query\Provider_Contract;

/** * Class Provider.
 *
 * @since   TBD
 *
 * @package TEC\Events\Custom_Tables\V1\Repository
 */
class Provider extends Service_Provider implements Provider_Contract {

	/**
	 * A reference to the callback that will handle the creation and update of Events.
	 *
	 * @since TBD
	 *
	 * @var callable
	 */
	private $update_callback;

	/**
	 * Hooks on the filters used in the Repository to handle the creation and update of custom
	 * tables data.
	 *
	 * @since TBD
	 */
	public function register() {
		$this->container->singleton( self::class, $this );
		$this->update_callback = $this->container->callback( Events::class, 'update_callback' );
		add_filter( 'tribe_repository_events_create_callback', $this->update_callback, 10, 2 );
		add_filter( 'tribe_repository_events_update_callback', $this->update_callback, 10, 2 );
	}

	/**
	 * Removes the hooks in the Filters API to handle the creation and update of custom tables data.
	 *
	 * @since TBD
	 */
	public function unregister() {
		remove_filter( 'tribe_repository_events_create_callback', $this->update_callback );
		remove_filter( 'tribe_repository_events_update_callback', $this->update_callback );
	}
}