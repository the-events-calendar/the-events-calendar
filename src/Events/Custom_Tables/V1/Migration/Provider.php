<?php
/**
 * Registers the implementations and hooks required for the Migration of
 * existing events to the custom tables v1 implementation.
 *
 * @since   TBD
 *
 * @package TEC\Events\Custom_Tables\V1\Migration;
 */

namespace TEC\Events\Custom_Tables\V1\Migration;

use tad_DI52_ServiceProvider as Service_Provider;
use TEC\Events\Custom_Tables\V1\Migration\Admin\Upgrade_Tab;
use TEC\Events\Custom_Tables\V1\Migration\Reports\Site_Report;
use TEC\Events\Custom_Tables\V1\Provider_Contract;
use Tribe__Events__Main as TEC;

/**
 * Class Provider.
 *
 * @since   TBD
 *
 * @package TEC\Events\Custom_Tables\V1\Migration;
 */
class Provider extends Service_Provider implements Provider_Contract {

	/**
	 * Registers the required implementations and hooks into the required
	 * actions and filters.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function register() {
		if ( ! (
			defined( 'TEC_EVENTS_CUSTOM_TABLES_V1_MIGRATION_ENABLED' )
			&& TEC_EVENTS_CUSTOM_TABLES_V1_MIGRATION_ENABLED
		) ) {
			// @todo remove this feature flag once the Migration work is completed.
			return;
		}

		// Register the provider in the container.
		$this->container->singleton( self::class, $this );

		$this->container->singleton( Events::class, Events::class );
		$this->container->singleton( State::class, State::class );
		$this->container->singleton( String_Dictionary::class, String_Dictionary::class );
		$this->container->singleton( Site_Report::class, Site_Report::class );
		$this->container->singleton( Page::class, Page::class );
		$this->container->singleton( Maintenance_Mode::class, Maintenance_Mode::class );
		$this->container->singleton( Process::class, Process::class );
		$this->container->singleton( Ajax::class, Ajax::class );
		$this->container->singleton( Asset_Loader::class, Asset_Loader::class );

		$this->load_action_scheduler();

		add_action( 'init', [ $this, 'init' ] );

		// Action Scheduler will fire this action: on it we'll migrate, or preview the migration of, an Event.
		add_action( Process_Worker::ACTION_PROCESS, [ $this, 'migrate_event' ], 10, 2 );
		add_action( Process_Worker::ACTION_UNDO, [ $this, 'undo_event_migration' ] );
		add_action( Process_Worker::ACTION_CHECK_PHASE, [ $this, 'check_migration_phase' ], 10, 2 );

		// Hook on the AJAX actions that will start, report about, and cancel the migration.
		add_action( Ajax::ACTION_REPORT, [ $this, 'send_report' ] );
		add_action( Ajax::ACTION_START, [ $this, 'start_migration' ] );
		add_action( Ajax::ACTION_CANCEL, [ $this, 'cancel_migration' ] );
		add_action( Ajax::ACTION_REVERT, [ $this, 'revert_migration' ] );
		add_action( 'action_scheduler_bulk_cancel_actions', [ $this, 'cancel_async_actions' ]  );
		add_action( 'action_scheduler_canceled_action', [ $this, 'cancel_async_action' ]  );

		if ( is_admin() ) {
			add_action( 'admin_enqueue_scripts', $this->container->callback( Asset_Loader::class, 'enqueue_scripts' ) );
			// Hook into the Upgrade tab to show it and customize its contents.
			add_filter( 'tec_events_upgrade_tab_has_content', [ $this, 'show_upgrade_tab' ] );
			add_filter( 'tribe_upgrade_fields', [ $this, 'add_phase_callback' ] );
		}
	}

	/**
	 * Set our state appropriately.
	 */
	public function init_migration_state() {
		$state = $this->container->make( State::class );
		if ( ! $state->get_phase() ) {
			$state->set( 'phase', State::PHASE_PREVIEW_PROMPT );
		}
	}

	/**
	 * Run actions on WordPress 'init' action.
	 */
	public function init() {
		// Initial state setup.
		$this->init_migration_state();

		// Activate maintenance mode, if required.
		$this->activate_maintenance_mode();
	}

	/**
	 * Unhooks the hooks set by the Provider in the `register` method.
	 *
	 * @since TBD
	 *
	 * @return void The method does not return any value and will have the side
	 *              effect of unsetting the hooks set in the `register` method.
	 */
	public function unregister() {
		// TODO: Implement unregister() method.
	}

	/**
	 * Executes one step of the migration process to migrate, or preview
	 * the migration of, one Event.
	 *
	 * @since TBD
	 *
	 * @param int  $post_id The post ID of the Event to migrate.
	 * @param bool $dry_run Whether the step should be executed in dry-run mode or not.
	 *
	 * @return void The method does not return any value but will trigger the action
	 *              that will migrate the Event.
	 */
	public function migrate_event( $post_id, $dry_run = false ) {
		$this->container->make( Process_Worker::class )->migrate_event( $post_id, $dry_run );
	}

	/**
	 * Executes a check on the current migration phase state to transition it to the correct
	 * one if no Worker took care of that.
	 *
	 * @since TBD
	 *
	 * @return void The method does not return any value and will have the side-effect of
	 *              updating the phase, if completed.
	 */
	public function check_migration_phase() {
		$this->container->make( Process_Worker::class )->check_phase_complete();
	}

	/**
	 * Executes one step of the migration process to undo the migration of one Event.
	 *
	 * @since TBD
	 *
	 * @param array<string,mixed> The metadata the worker passes itself to track state.
	 *
	 * @return void The method does not return any value but will trigger the action
	 *              that will undo the Event migration.
	 */
	public function undo_event_migration( $meta = [] ) {
		$this->container->make( Process_Worker::class )->undo_event_migration( $meta );
	}

	/**
	 * Respond to canceled Action Scheduler actions.
	 *
	 * @since TBD
	 *
	 * @param numeric $action_id The action scheduler action ID.
	 */
	public function cancel_async_action( $action_id ) {
		$this->container->make( Process::class )->cancel_async_action( $action_id );
	}

	/**
	 * Respond to bulk canceled Action Scheduler actions.
	 *
	 * @since TBD
	 *
	 * @param array $action_id A list of the action scheduler action IDs.
	 */
	public function cancel_async_actions( array $action_ids ) {
		$this->container->make( Process::class )->cancel_async_actions( $action_ids );
	}

	/**
	 * Sends (echoes) a JSON format report of the site migration.
	 *
	 * @since TBD
	 *
	 * @return void The method does not return any value and will have the side effect
	 *              of echoing a JSON format string back for the Migration UI JS component
	 *              to consume.
	 */
	public function send_report() {
		return $this->container->make( Ajax::class )->send_report();
	}

	/**
	 * Starts the migration and sends the initial report.
	 *
	 * @since TBD
	 *
	 * @return void The method does not return any value and will have the side effect
	 *              of echoing a JSON format string back for the Migration UI JS component
	 *              to consume.
	 */
	public function start_migration() {
		$this->container->make( Ajax::class )->start_migration();
	}

	/**
	 * Stops the migration and sends the final report.
	 *
	 * @since TBD
	 *
	 * @return void The method does not return any value and will have the side effect
	 *              of echoing a JSON format string back for the Migration UI JS component
	 *              to consume.
	 */
	public function cancel_migration() {
		$this->container->make( Ajax::class )->cancel_migration();
	}

	/**
	 * Undoes the migration and sends the initial report.
	 *
	 * @since TBD
	 *
	 * @return void The method does not return any value and will have the side effect
	 *              of echoing a JSON format string back for the Migration UI JS component
	 *              to consume.
	 */
	public function revert_migration() {
		$this->container->make( Ajax::class )->revert_migration();
	}

	/**
	 * Activate the Event-only maintenance mode, if required by the current
	 * migration state.
	 *
	 * @since TBD
	 *
	 * @return void The method does not return any value and will have the side
	 *              effect of putting the site Events, and related data, in maintenance
	 *              mode.
	 */
	public function activate_maintenance_mode() {
		$this->container->make( Maintenance_Mode::class )->activate();
	}

	/**
	 * Filters whether the Upgrade tab, hosting the migration report, should show or not.
	 *
	 * @since TBD
	 *
	 * @param bool $show_tab The initial value as worked out by TEC and other plugins.
	 *
	 * @return bool Whether the Upgrade tab should show or not. A logic OR on the input
	 *              value depending on the Migration state.
	 */
	public function show_upgrade_tab( $show_tab ) {
		return $show_tab || $this->container->make( Upgrade_Tab::class )->should_show();
	}

	/**
	 * Filters the Upgrade tab fields to add the ones dedicated to the Migration.
	 *
	 * @since TBD
	 *
	 * @param array<string,mixed> $upgrade_fields The Upgrade page fields, as set up
	 *                                            by The Events Calendar and other plugins.
	 *
	 * @return array<string,mixed> The filtered Upgrade tab fields, including the fields
	 *                             dedicated to Migration.
	 */
	public function add_phase_callback( $upgrade_fields ) {
		return $this->container->make( Upgrade_Tab::class )->add_phase_content( $upgrade_fields );
	}

	/**
	 * Loads the Action Scheduler library by loading the main plugin file shipped with
	 * this plugin.
	 *
	 * This method would, usually, run on the `plugins_loaded` action and might, in that
	 * case, further delay the loading of the Action Scheduler library to the `init` action.
	 *
	 * @since TBD
	 */
	private function load_action_scheduler() {
		$load_action_scheduler = [ $this, 'load_action_scheduler_late' ];

		if ( ! has_action( 'tec_events_custom_tables_v1_load_action_scheduler', $load_action_scheduler ) ) {
			// Add a custom action that will allow triggering the load of Action Scheduler.
			add_action( 'tec_events_custom_tables_v1_load_action_scheduler', $load_action_scheduler );
		}

		/*
		 * We do not sense around for of the functions defined by Action Scheduler by design:
		 * Action Scheduler will take care of loading the most recent version. If we looked
		 * for some of Action Scheduler API functions, then, we would let a possibly older
		 * version load instead of ours just because it did init Action Scheduler before
		 * this plugin.
		 */
		if ( did_action( 'plugins_loaded' ) || doing_action( 'plugins_loaded' ) ) {
			add_action( 'init', $load_action_scheduler, - 99999 );

			return;
		}

		$action_scheduler_file = TEC::instance()->plugin_path . '/vendor/woocommerce/action-scheduler/action-scheduler.php';
		require_once $action_scheduler_file;
	}

	/**
	 * Loads Action Scheduler late, after the `plugins_loaded` hook, at the
	 * start of the `init` one.
	 *
	 * Action Scheduler does support loading after the `plugins_loaded` hook, but
	 * not during it. The provider will register exactly during the `plugins_loaded`
	 * action, so we need to retry setting up Action Scheduler again.
	 *
	 * @since TBD
	 */
	public function load_action_scheduler_late() {
		$action_scheduler_file = TEC::instance()->plugin_path . '/vendor/woocommerce/action-scheduler/action-scheduler.php';
		require_once $action_scheduler_file;
	}
}