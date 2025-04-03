<?php
/**
 * Handles the Custom Tables integration, and compatibility, with
 * the Repositories.
 *
 * Here what implementations and filters are not relevant, are disconnected.
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Repository
 */

namespace TEC\Events\Custom_Tables\V1\Repository;

use TEC\Common\Contracts\Service_Provider;
use TEC\Events\Custom_Tables\V1\Provider_Contract;

/** * Class Provider.
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Repository
 */
class Provider extends Service_Provider implements Provider_Contract {
	/**
	 * Hooks on the filters used in the Repository to handle the creation and update of custom
	 * tables data.
	 *
	 * @since 6.0.0
	 */
	public function register() {
		$this->container->singleton( self::class, $this );
		add_filter( 'tribe_repository_events_create_callback', [ $this, 'update_callback' ], 10, 2 );
		add_filter( 'tribe_repository_events_update_callback', [ $this, 'update_callback' ], 10, 2 );
	}

	/**
	 * Removes the hooks in the Filters API to handle the creation and update of custom tables data.
	 *
	 * @since 6.0.0
	 */
	public function unregister() {
		remove_filter( 'tribe_repository_events_create_callback', [ $this, 'update_callback' ] );
		remove_filter( 'tribe_repository_events_update_callback', [ $this, 'update_callback' ] );
	}

	/**
	 * Replaces the default Event Repository create and update callback with one that will operate on
	 * custom tables.
	 *
	 * @since 6.0.0
	 *
	 * @param callable            $repository_callback The default repository callback.
	 * @param array<string,mixed> $postarr             An array of datat to create or update the Event.
	 *
	 * @return callable The callback that will handle upsertions of an Event custom tables data
	 *                  in the context of a repository call.
	 */
	public function update_callback( callable $repository_callback, array $postarr = [] ): callable {
		return $this->container->make( Events::class )->update_callback( $repository_callback, $postarr );
	}
}
