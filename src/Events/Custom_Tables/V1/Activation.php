<?php
/**
 * Handles the code that should be executed when the plugin is activated or deactivated.
 *
 * @since   TBD
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
 * @since   TBD
 *
 * @package TEC\Events\Custom_Tables\V1
 */
class Activation {
	/**
	 * The name of the transient that will be used to flag whether the plugin did activate
	 * or not.
	 *
	 * @since TBD
	 */
	const ACTIVATION_TRANSIENT = 'tec_custom_tables_v1_initialized';

	/**
	 * Handles the activation of the feature functions.
	 *
	 * @since TBD
	 */
	public static function activate() {
		$schema_builder = tribe( Schema_Builder::class);
		$schema_builder->up();
	}

	/**
	 * Checks the state to determine if whether we can create custom tables.
	 *
	 * This method will run once a day (using transients).
	 *
	 * @since TBD
	 */
	public static function init() {
		// Check if we ran recently.
		$initialized = get_transient( self::ACTIVATION_TRANSIENT );

		if ( $initialized ) {
			return;
		}

		set_transient( self::ACTIVATION_TRANSIENT, 1, DAY_IN_SECONDS );

		$services       = tribe();
		$schema_builder = $services->make( Schema_Builder::class );

		// Sync any schema changes we may have.
		if ( $schema_builder->all_tables_exist( 'tec' ) ) {
			$schema_builder->up();
		}

		$state = $services->make( State::class );

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
	 * Handles the feature deactivation.
	 *
	 * @since TBD
	 */
	public static function deactivate() {
		$services = tribe();

		// @todo Should we drop the tables here, gracefully, if no data was generated?
		$services->make( Schema_Builder::class )->clean();
	}
}
