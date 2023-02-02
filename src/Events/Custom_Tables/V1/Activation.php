<?php
/**
 * Handles the code that should be executed when the plugin is activated or deactivated.
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1
 */

namespace TEC\Events\Custom_Tables\V1;

use TEC\Events\Custom_Tables\V1\Migration\Events;
use TEC\Events\Custom_Tables\V1\Migration\State;
use TEC\Events\Custom_Tables\V1\Tables\Events as EventsTable;
use TEC\Events\Custom_Tables\V1\Schema_Builder\Schema_Builder;
use TEC\Events\Custom_Tables\V1\Tables\Occurrences;
use Tribe__Events__Main;
use Tribe__Main as Common;

/**
 * Class Activation
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1
 */
class Activation {
	/**
	 * The name of the transient that will be used to flag whether the plugin did activate
	 * or not.
	 *
	 * @since 6.0.0
	 */
	public const ACTIVATION_TRANSIENT = 'tec_custom_tables_v1_initialized';

	/**
	 * Handles the activation of the feature functions.
	 *
	 * @since 6.0.0
	 */
	public static function activate() {
		$schema_builder = tribe( Schema_Builder::class );
		$schema_builder->up();
	}

	/**
	 * Checks the state to determine if whether we should create or update custom tables.
	 *
	 * This method will run once a day (using transients).
	 *
	 * @since 6.0.0
	 */
	public static function init() {
		$services = tribe();

		/*
		 * Transients will use the cache when using real object cache, why check both then?
		 * Transients might be disabled. In that case we'll use the cache and work around that limitation.
		 * A user seeking to force the Activation to run again can flush the cache when using one, or clear
		 * the transient when not using one.
		 */
		if ( wp_using_ext_object_cache() ) {
			$last_run = wp_cache_get( static::ACTIVATION_TRANSIENT );
		} else {
			$last_run = get_transient( static::ACTIVATION_TRANSIENT );
		}
		$last_run = is_numeric( $last_run ) ? (int) $last_run : null;
		$now      = time();

		// If the activation last ran less than 24 hours ago, bail.
		if ( $last_run && $last_run > ( $now - DAY_IN_SECONDS ) ) {
			return;
		}

		$schema_builder = $services->make( Schema_Builder::class );
		$state          = $services->make( State::class );
		$phase          = $state->get_phase();
		$events         = $services->make( Events::class );

		// If the migration phase is not set and there are no Events to migrate, then the migration is not required.
		if ( $phase === null && $events->get_total_events() === 0 ) {
			$state->set( 'phase', State::PHASE_MIGRATION_NOT_REQUIRED );
			$state->save();
		}

		$update = $state->is_dry_run() || $state->is_running() || $state->is_completed()
			|| $state->is_migrated();

		// Update the tables if required by the migration phase.
		if ( $update ) {
			$schema_builder->up( true );

			// Ensure late activation only after we have the tables.
			if ( ! $services->getVar( 'ct1_fully_activated' ) ) {
				/**
				 * On new installations the full activation code will find an empty state and
				 * will have not activated at this point, do it now if required.
				 */
				$services->register( Full_Activation_Provider::class );
			}
		}

		if ( wp_using_ext_object_cache() ) {
			wp_cache_set( static::ACTIVATION_TRANSIENT, $now, '', DAY_IN_SECONDS );
			// Clean up.
			delete_transient( static::ACTIVATION_TRANSIENT );
		} else {
			set_transient( static::ACTIVATION_TRANSIENT, $now, DAY_IN_SECONDS );
			// Clean up.
			wp_cache_delete( static::ACTIVATION_TRANSIENT );
		}
	}

	/**
	 * Includes the Migration state into the System Information for support.
	 *
	 * @since 6.0.1
	 *
	 * @param array<string,mixed> $info The system information, as produced by the
	 *                                  default logic and previous filters.
	 *
	 * @return array<string,mixed> The filtered system information.
	 */
	public static function filter_include_migration_in_system_info( array $info = [] ): array {
		$phase = tribe( State::class )->get_phase();
		// String not translated on purpose.
		$incomplete_label = 'Incomplete';
		$status_map       = [
			State::PHASE_MIGRATION_COMPLETE     => 'Completed', // String not translated on purpose.
			State::PHASE_MIGRATION_NOT_REQUIRED => 'Not Required', // String not translated on purpose.
		];

		$migration_status = [
			'Custom Tables Migration Status' => ! empty( $phase ) && ! empty( $status_map[ $phase ] ) ?
				$status_map[ $phase ]
				: $incomplete_label,
		];

		// Prevents problems in case we don't have sys info.
		if ( empty( $info ) ) {
			return $migration_status;
		}

		$migration_status = self::filter_include_migration_health_check_info($migration_status);

		return Common::array_insert_before_key( 'Settings', $info, $migration_status );
	}

	/**
	 * Adds some health check reports to assist in troubleshooting.
	 *
	 * @since TBD
	 *
	 * @param array<string,mixed> $info The report data to add our health check to.
	 *
	 * @return array<string,mixed> The modified report data.
	 */
	public static function filter_include_migration_health_check_info( array $info ): array {
		global $wpdb;
		$migrated      = tribe( State::class )->is_migrated();
		$issue_reports = [];
		// Check if we have flagged as "migrated" but we show a mismatch of data in our tables.
		if ( $migrated ) {
			$events_table      = EventsTable::table_name();
			$occurrences_table = Occurrences::table_name();
			$posts_count       = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->posts WHERE post_type= %s", Tribe__Events__Main::POSTTYPE ) );
			$events_count      = $wpdb->get_var( "SELECT COUNT(*) FROM $events_table" );
			$occurrences_count = $wpdb->get_var( "SELECT COUNT(*) FROM $occurrences_table" );
			if ( $posts_count > 0 && $events_count < 1 ) {
				$issue_reports[] = "Missing `Event` Table Data";
			}
			if ( $posts_count > 0 && $occurrences_count < 1 ) {
				$issue_reports[] = "Missing `Occurrences` Table Data";
			}
			if ( ! is_numeric( $events_count ) ) {
				$issue_reports[] = "`Event` Table Missing";
			}
			if ( ! is_numeric( $occurrences_count ) ) {
				$issue_reports[] = "`Occurrences` Table Missing";
			}
		}
		$reports = empty( $issue_reports ) ? 'Good!' : implode( $issue_reports, ' | ' );
		// Add health checks here.
		$migration_health_check = [
			'Custom Tables Health Check' => $reports // If no bad reports, it's good.
		];

		return array_merge( $info, $migration_health_check );
	}

	/**
	 * Handles the feature deactivation.
	 *
	 * @since 6.0.0
	 */
	public static function deactivate() {
		$services = tribe();

		// @todo Should we drop the tables here, gracefully, if no data was generated?
		$services->make( Schema_Builder::class )->clean();
	}
}
