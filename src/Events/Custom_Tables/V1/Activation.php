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
	const ACTIVATION_TRANSIENT = 'tec_custom_tables_v1_initialized';

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
	 * Checks the state to determine if whether we can create custom tables.
	 *
	 * This method will run once a day (using transients).
	 *
	 * @since 6.0.0
	 */
	public static function init() {
		// Check if we ran recently.
		$db_hash = get_transient( static::ACTIVATION_TRANSIENT );

		$schema_builder = tribe( Schema_Builder::class );
		$hash           = $schema_builder->get_registered_schemas_version_hash();

		if ( $db_hash == $hash ) {
			return;
		}

		set_transient( static::ACTIVATION_TRANSIENT, $hash, DAY_IN_SECONDS );

		// Sync any schema changes we may have.
		if ( $schema_builder->all_tables_exist( 'tec' ) ) {
			$schema_builder->up();
		}

		$services = tribe();
		$state    = $services->make( State::class );

		// Check if we have any events to migrate, if not we can set up our schema and flag the migration complete.
		if (
			$services->make( Events::class )->get_total_events() === 0
			&& in_array( $state->get_phase(), [ null, State::PHASE_MIGRATION_NOT_REQUIRED ], true )
		) {
			$schema_builder->up();
			$state->set( 'phase', State::PHASE_MIGRATION_NOT_REQUIRED );
			$state->save();

			if ( ! tribe()->getVar( 'ct1_fully_activated' ) ) {
				/**
				 * On new installations the full activation code will find an empty state and
				 * will have not activated at this point, do it now if required.
				 */
				tribe()->register( Full_Activation_Provider::class );
			}
		}
	}

	/**
	 * Includes the Migration state into the System Information for support.
	 *
	 * @since TBD
	 *
	 * @param array $info
	 *
	 * @return array
	 */
	public static function filter_include_migration_to_system_info( array $info = [] ): array {
		$phase = tribe( State::class )->get_phase();
		$incomplete_label = esc_html__( 'Incomplete', 'the-events-calendar' );
		$status_map = [
			State::PHASE_MIGRATION_COMPLETE => esc_html__( 'Completed', 'the-events-calendar' ),
			State::PHASE_MIGRATION_NOT_REQUIRED => esc_html__( 'Not Required', 'the-events-calendar' ),
		];

		$migration_status = [
			'Custom Tables Migration Status' => ! empty( $phase ) && ! empty( $status_map[ $phase ] ) ? $status_map[ $phase ] : $incomplete_label,
		];

		// Prevents problems in case we don't have sys info.
		if ( empty( $info ) ) {
			return $migration_status;
		}

		return \Tribe__Main::array_insert_before_key( 'Settings', $info, $migration_status );
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
