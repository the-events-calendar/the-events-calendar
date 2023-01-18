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
use TEC\Events\Custom_Tables\V1\Schema_Builder\Schema_Builder;
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

		return Common::array_insert_before_key( 'Settings', $info, $migration_status );
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
