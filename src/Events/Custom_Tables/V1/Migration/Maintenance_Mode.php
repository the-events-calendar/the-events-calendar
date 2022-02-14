<?php
/**
 * Handles the maintenance mode set during migration to prevent WRITE operations on Events
 * and related information.
 *
 * @since   TBD
 *
 * @package TEC\Events\Custom_Tables\V1\Migration;
 */

namespace TEC\Events\Custom_Tables\V1\Migration;

/**
 * Class Maintenance_Mode.
 *
 * @since   TBD
 *
 * @package TEC\Events\Custom_Tables\V1\Migration;
 */
class Maintenance_Mode {

	/**
	 * A reference to the current migration state provider.
	 *
	 * @since TBD
	 *
	 * @var State
	 */
	private $migration_state;

	/**
	 * Maintenance_Mode constructor.
	 *
	 * since TBD
	 *
	 * @param State $state A reference to the current migration state provider.
	 */
	public function __construct( State $state ) {
		$this->migration_state = $state;
	}

	/**
	 * Activates the migration mode, disabling a number of UI elements
	 * across plugins, if required by the current migration state.
	 *
	 * @since TBD
	 *
	 * @return bool Whether the Event-wide maintenance mode was activated or not.
	 */
	public function activate() {
		if ( ! $this->migration_state->is_running() ) {
			return false;
		}

		$this->add_filters();

		return true;
	}

	/**
	 * Hooks into filters and actions disabling a number of UI across plugins to make sure
	 * no Event-related data will be modified during the migration.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	private function add_filters() {
		// @todo look at ECP migration code and disable only TEC stuff here.

		// @todo in ECP hook into this to handle ECP, ET, ET+ et al stuff.
		/**
		 * Fires an action to signal TEC requires putting the site in maintenance
		 * mode while the migration completes.
		 *
		 * @since TBD
		 */
		do_action( 'tec_events_custom_tables_v1_migration_maintenance_mode' );
	}
}