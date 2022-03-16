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
use TEC\Events\Custom_Tables\V1\Migration\Admin\Progress_Modal;

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
	public function __construct( State $state, Progress_Modal $progress_modal ) {
		$this->migration_state = $state;
		$this->progress_modal = $progress_modal;
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
		// Turn off Aggregator cron.
		add_filter( 'tribe_get_option', [ $this, 'filter_aggregator_disable' ], 10, 2 );
		// Disable REST endpoints for Event Aggregator by setting the permission check to false.
		add_filter( 'tribe_aggregator_batch_data_processing_enabled', '__return_false' );
		add_filter( 'tribe_aggregator_remote_status_enabled', '__return_false' );

		// @todo delegate this to upgrade tab class?
		add_action( 'admin_footer', [ $this, 'inject_progress_modal' ] );
		add_action( 'admin_print_footer_scripts', [ $this, 'inject_progress_modal_js_trigger' ], PHP_INT_MAX );

		/**
		 * Fires an action to signal TEC requires putting the site in maintenance
		 * mode while the migration completes.
		 *
		 * @since TBD
		 */
		do_action( 'tec_events_custom_tables_v1_migration_maintenance_mode' );
	}

	/**
	 * Disable Events Aggregator.
	 *
	 * @param mixed  $value  The `tribe_option` value.
	 * @param string $option The `tribe_option` name.
	 *
	 * @return mixed The filtered option value, `true` when the option
	 *               being filtered is the one to disable Events Aggregator.
	 */
	public function filter_aggregator_disable( $value, $option ) {
		if ( 'tribe_aggregator_disable' !== $option ) {
			return $value;
		}

		return true;
	}

	/**
	 * Inject the content and data of the Admin\Progress_Modal.
	 *
	 * @since TBD
	 */
	public function inject_progress_modal() {
		// @todo should this stay here?
		echo $this->progress_modal->render_modal();
	}

	/**
	 * Inject the Admin\Progress_Modal trigger that pops open the modal.
	 *
	 * @since TBD
	 */
	public function inject_progress_modal_js_trigger() {
		// @todo should this stay here?
		echo $this->progress_modal->get_modal_auto_trigger();
	}
}