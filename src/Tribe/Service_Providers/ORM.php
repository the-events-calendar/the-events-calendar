<?php
/**
 * Hooks and registers the functions and implementations needed to provide
 * the ORM/Repository classes.
 *
 * @since TBD
 */

/**
 * Class Tribe__Events__Service_Providers__ORM
 *
 * @since TBD
 */
class Tribe__Events__Service_Providers__ORM extends tad_DI52_ServiceProvider {

	/**
	 * Binds and sets up implementations.
	 */
	public function register() {
		require_once tribe( 'tec.main' )->plugin_path . 'src/functions/template-tags/orm.php';

		// Not bound as a singleton to leverage the repository instance properties and to allow decoration and injection.
		$this->container->bind( 'events.event-repository', 'Tribe__Events__Repositories__Event' );
		$this->container->bind( 'events.organizer-repository', 'Tribe__Events__Repositories__Organizer' );
		$this->container->bind( 'events.venue-repository', 'Tribe__Events__Repositories__Venue' );
	}
}
