<?php

namespace TEC\Events\Custom_Tables\V1\Events\Event_Cleaner;

use tad_DI52_ServiceProvider as Service_Provider;

/**
 * Class Provider
 *
 * This is the provider for our "Old" Event Cleaner system.
 *
 * @since   TBD
 *
 * @package TEC\Events\Custom_Tables\V1\Events\Event_Cleaner
 */
class Provider extends Service_Provider {
	/**
	 * A flag property indicating whether the Service Provide did register or not.
	 *
	 * @since TBD
	 *
	 * @var bool
	 */
	private $did_register = false;

	/**
	 * Registers the filters and implementations required by the Custom Tables implementation.
	 *
	 * @since TBD
	 *
	 * @return bool Whether the Provider did register or not.
	 */
	public function register() {

		if ( $this->did_register ) {
			// Let's avoid double filtering by making sure we're registering at most once.
			return true;
		}

		$this->did_register = true;

		add_filter( 'tribe_events_delete_old_events_sql', [ $this, 'filter_tribe_events_delete_old_events_sql' ], 9 );
	}


	/**
	 * Hooks into our automated event cleaner service, and modifies the expired events query to handle only single
	 * occurrences.
	 *
	 * @since TBD
	 *
	 * @param string $sql The original query to retrieve expired events.
	 *
	 * @return string The modified CT1 query to retrieve expired events.
	 */
	public function filter_tribe_events_delete_old_events_sql( string $sql ): string {
		return tribe( Event_Cleaner::class )->filter_tribe_events_delete_old_events_sql( $sql );
	}
}