<?php

namespace TEC\Events\Custom_Tables\V1\Events\Event_Cleaner;

use TEC\Common\Contracts\Service_Provider;
use Tribe__Events__Event_Cleaner_Scheduler;
use Tribe__Events__Main;
use Tribe__Main;

/**
 * Class Provider
 *
 * This is the provider for our "Old" Event Cleaner system.
 *
 * @since   6.0.13
 *
 * @package TEC\Events\Custom_Tables\V1\Events\Event_Cleaner
 */
class Provider extends Service_Provider {
	/**
	 * A flag property indicating whether the Service Provide did register or not.
	 *
	 * @since 6.0.13
	 *
	 * @var bool
	 */
	private $did_register = false;

	/**
	 * Registers the filters and implementations required by the Custom Tables implementation.
	 *
	 * @since 6.0.13
	 *
	 * @return bool If successfully registered. Will only register once, if called again will return false to indicate
	 *              already registered.
	 */
	public function register(): bool {

		if ( $this->did_register ) {
			// Let's avoid double filtering by making sure we're registering at most once.
			return false;
		}

		$this->did_register = true;

		$this->remove_old_recurrence_cleaners();
		add_filter( 'tribe_events_delete_old_events_sql', [ $this, 'redirect_old_events_sql' ], 9 );

		return true;
	}

	/**
	 * Deprecating/removing 'tec.event-cleaner' and the scheduler. This is now being handled by the CT1 Event Cleaner.
	 * system in CT1.
	 *
	 * @since 6.0.13
	 */
	public function remove_old_recurrence_cleaners() {
		/**
		 * Triggering the old event cleaner on update of tribe option is causing some conflicts. Something is attempting to clean simultaneously, and
		 * creating a race condition and failure to do database updates, due the dissecting and trashing of recurring events and Custom Table relationships.
		 */
		add_action( 'tribe_common_loaded', function () {
			remove_action( 'update_option_' . Tribe__Main::OPTIONNAME, tribe_callback( 'tec.event-cleaner', 'move_old_events_to_trash' ), 10 );
		}, 99 );

		// Hide from settings page.
		add_filter( 'tribe_general_settings_tab_fields', function ( $args ) {
			$event_cleaner = tribe( 'tec.event-cleaner' );
			unset( $args[ $event_cleaner->key_delete_events ] );

			return $args;
		}, 99, 1 );

		// Remove scheduled cleaner task.
		add_action( 'init', function () {
			$main = Tribe__Events__Main::instance();
			if ( isset( $main->scheduler ) ) {
				remove_action( Tribe__Events__Event_Cleaner_Scheduler::$del_cron_hook, [
					$main->scheduler,
					'permanently_delete_old_events'
				], 10 );
				wp_unschedule_event( time(), Tribe__Events__Event_Cleaner_Scheduler::$del_cron_hook );
			}
		}, 999 );
	}


	/**
	 * Hooks into our automated event cleaner service, and modifies the expired events query to handle only single
	 * occurrences.
	 *
	 * @since 6.0.13
	 *
	 * @param string $sql The original query to retrieve expired events.
	 *
	 * @return string The modified CT1 query to retrieve expired events.
	 */
	public function redirect_old_events_sql( string $sql ): string {
		return tribe( Event_Cleaner::class )->redirect_old_events_sql( $sql );
	}
}
