<?php
/**
 * Handles the maintenance mode set during migration to prevent WRITE operations on Events
 * and related information.
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Migration;
 */

namespace TEC\Events\Custom_Tables\V1\Migration;

use TEC\Events\Custom_Tables\V1\Migration\Admin\Progress_Modal;

/**
 * Class Maintenance_Mode.
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Migration;
 */
class Maintenance_Mode {
	/**
	 * A reference to the current migration state provider.
	 *
	 * @since 6.0.0
	 *
	 * @var State
	 */
	private $migration_state;

	/**
	 * A reference to the progress modal displayed to lock several pages.
	 *
	 * @since 6.0.0
	 *
	 * @var Progress_Modal
	 */
	private $progress_modal;

	/**
	 * Maintenance_Mode constructor.
	 *
	 * @since 6.0.0
	 *
	 * @param State          $state          A reference to the current migration state provider.
	 * @param Progress_Modal $progress_modal A reference to the progress modal displayed to lock several pages.
	 */
	public function __construct( State $state, Progress_Modal $progress_modal ) {
		$this->migration_state = $state;
		$this->progress_modal  = $progress_modal;
	}

	/**
	 * Output our special maintenance modal for all settings tabs except the Upgrade tab.
	 *
	 * @since 6.0.0
	 *
	 * @param string $tab The settings tab this action is running for.
	 */
	public function inject_settings_page_modal( $tab ) {
		if ( $tab === 'upgrade' || ! $this->migration_state->should_lock_for_maintenance() ) {
			return;
		}

		$text = tribe( String_Dictionary::class );
		include tribe( 'tec.main' )->plugin_path . 'src/Events/Custom_Tables/V1/admin-views/migration/settings-maintenance-modal.php';
	}

	/**
	 * Activates the migration mode, disabling a number of UI elements
	 * across plugins, if required by the current migration state.
	 *
	 * @since 6.0.0
	 *
	 * @return bool Whether the Event-wide maintenance mode was activated or not.
	 */
	public function activate() {
		if ( ! $this->migration_state->should_lock_for_maintenance() ) {
			return false;
		}

		$this->add_filters();

		return true;
	}

	/**
	 * Hooks into filters and actions disabling a number of UI across plugins to make sure
	 * no Event-related data will be modified during the migration.
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	private function add_filters() {
		// Turn off Aggregator cron.
		add_filter( 'tribe_get_option', [ $this, 'filter_aggregator_disable' ], 10, 2 );

		// Disable REST endpoints for Event Aggregator by setting the permission check to false.
		add_filter( 'tribe_aggregator_batch_data_processing_enabled', '__return_false' );
		add_filter( 'tribe_aggregator_remote_status_enabled', '__return_false' );

		// Modal that locks events access, to be rendered on several pages.
		add_action( 'admin_footer', [ $this, 'inject_progress_modal' ] );
		add_action( 'admin_print_footer_scripts', [ $this, 'inject_progress_modal_js_trigger' ], PHP_INT_MAX );

		// A special overlay for our settings pages.
		add_action( 'tribe_settings_after_content', [ $this, 'inject_settings_page_modal' ] );

		/**
		 * Fires an action to signal TEC requires putting the site in maintenance
		 * mode while the migration completes.
		 *
		 * @since 6.0.0
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
	 * @since 6.0.0
	 */
	public function inject_progress_modal() {
		// @todo should this stay here?
		echo $this->progress_modal->render_modal();
	}

	/**
	 * Inject the Admin\Progress_Modal trigger that pops open the modal.
	 *
	 * @since 6.0.0
	 */
	public function inject_progress_modal_js_trigger() {
		// @todo should this stay here?
		echo $this->progress_modal->get_modal_auto_trigger();
	}
}