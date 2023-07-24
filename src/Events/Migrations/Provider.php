<?php
/**
 * Service Provider for interfacing with TEC\Common\Migrations.
 *
 * @since   TBD
 *
 * @package TEC\Events\Migrations
 */

namespace TEC\Events\Migrations;

use TEC\Common\Contracts\Service_Provider;
use Tribe__Events__Main;

/**
 * Class Provider
 *
 * @since   TBD
 * @package TEC\Events\Migrations
 */
class Provider extends Service_Provider {
	/**
	 * Handles the registering of the provider. Add various migrations here.
	 *
	 * @since TBD
	 */
	public function register() {
		$this->add_migration_actions();
		add_action( 'tec_events_plugin_updater_version_change', [ $this, 'schedule_pending_migrations' ], 10, 1 );
	}

	/**
	 * Callback that will see if a particular migration is required.
	 *
	 * @since TBD
	 *
	 * @param string $plugin_schema_key The schema option key.
	 */
	public function schedule_pending_migrations( $plugin_schema_key ) {
		// e.g. TEC only
		if ( $plugin_schema_key !== 'schema-version' ) {
			return;
		}
		
		/**
		 * Intended to evaluation when a particular migration should run.
		 * Often this may be as simple as checking if a particular version has been
		 * passed on this update, like the below.
		 */
		$target_version = '6.1.4';
		$updater        = Tribe__Events__Main::instance()->updater();
		if ( $updater->is_version_in_db_less_than( $target_version ) ) {
			// Async this so we don't overload plugin update actions.
			$timestamp = time() + 5;
			wp_schedule_single_event( $timestamp, 'tec_events_migrate_all_day_eod_times' );
		}
	}

	/**
	 * This is intended to be where all migrations that may need to be run via an action,
	 * e.g. for async migrations, can define their specific action callbacks.
	 */
	public function add_migration_actions() {
		add_action( 'tec_events_migrate_all_day_eod_times', [ $this, 'migrate_all_day_eod_times' ] );
	}

	/**
	 * This is relevant to TEC-4748 and TEC-4840, where we are fixing
	 * the EOD cut off times to be as expected and not tied to the multidayCutOff setting.
	 */
	public function migrate_all_day_eod_times() {
		global $wpdb;
		// Check if our data might be out of sync.
		if ( tribe_get_option( 'multiDayCutoff', '00:00' ) === '00:00' ) {
			// Nothing to do, exit.
			return;
		}
		
		// This will fix all day events with any start time
		$fix_start_dates = "UPDATE $wpdb->postmeta AS pm1
				INNER JOIN $wpdb->postmeta pm2
					ON (pm1.post_id = pm2.post_id AND pm2.meta_key = '_EventAllDay' AND pm2.`meta_value` = 'yes')
				SET pm1.meta_value = CONCAT(DATE(pm1.meta_value), ' ', '00:00:00')
				WHERE pm1.meta_key = '_EventStartDate'";

		// Query to set the end time to the start time plus the duration on every all day event
		$fix_end_dates =
			"UPDATE $wpdb->postmeta AS pm1
				INNER JOIN $wpdb->postmeta pm2
					ON (pm1.post_id = pm2.post_id AND pm2.meta_key = '_EventAllDay' AND pm2.meta_value = 'yes')
				INNER JOIN $wpdb->postmeta pm3
					ON (pm1.post_id = pm3.post_id AND pm3.meta_key = '_EventStartDate')
				INNER JOIN $wpdb->postmeta pm4
					ON (pm1.post_id = pm4.post_id AND pm4.meta_key = '_EventDuration')
				SET pm1.meta_value = DATE_FORMAT( DATE_ADD( pm3.meta_value, INTERVAL pm4.meta_value SECOND ), '%Y-%m-%d %H:%i:%s' )
				WHERE pm1.meta_key = '_EventEndDate'";
		$wpdb->query( $fix_start_dates );
		$wpdb->query( $fix_end_dates );
	}

}
