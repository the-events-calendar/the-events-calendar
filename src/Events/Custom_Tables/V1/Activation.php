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
use TEC\Events\Custom_Tables\V1\Tables\Provider as Tables;

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
		// Delete the transient to make sure the activation code will run again.
		delete_transient( self::ACTIVATION_TRANSIENT );

		self::init();
	}

	/**
	 * Initializes the custom tables required by the feature to work.
	 *
	 * This method will run once a day (using transients) and is idem-potent
	 * in the context of the same day.
	 *
	 * @since TBD
	 */
	public static function init() {
		$initialized = get_transient( self::ACTIVATION_TRANSIENT );

		if ( $initialized ) {
			return;
		}

		set_transient( self::ACTIVATION_TRANSIENT, 1, DAY_IN_SECONDS );

		$services = tribe();
		$services->register( Tables::class );
		$tables = $services->make( Tables::class );

		if ( $tables->exist() ) {
			$tables->update_tables( true );
		}

		// Check if we have not "migrated", then attempt to activate.
		$state = $services->make( State::class );
		if ( $state->get_phase() !== State::PHASE_MIGRATION_COMPLETE ) {
			$tables->update_tables( true );

			// Check if we have any events to migrate.
			if ( $services->make( Events::class )->get_total_events() === 0 ) {
				$state->set( 'phase', State::PHASE_MIGRATION_COMPLETE );
				$state->save();
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

		$services->register( Tables::class );
		// @todo Should we drop the tables here, gracefully, if no data was generated?
		$services->make( Tables::class )->clean();
	}
}
