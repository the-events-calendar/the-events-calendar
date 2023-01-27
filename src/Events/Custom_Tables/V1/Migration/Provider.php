<?php
/**
 * Registers the implementations and hooks required for the Migration of
 * existing events to the custom tables v1 implementation.
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Migration;
 */

namespace TEC\Events\Custom_Tables\V1\Migration;

use tad_DI52_ServiceProvider as Service_Provider;
use TEC\Events\Custom_Tables\V1\Migration\Admin\Upgrade_Tab;
use TEC\Events\Custom_Tables\V1\Migration\CSV_Report\Download_Report_Provider;
use TEC\Events\Custom_Tables\V1\Migration\Reports\Event_Report_Categories;
use TEC\Events\Custom_Tables\V1\Migration\Reports\Site_Report;
use TEC\Events\Custom_Tables\V1\Provider_Contract;
use Tribe__Events__Main as TEC;

/**
 * Class Provider.
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Migration;
 */
class Provider extends Service_Provider implements Provider_Contract {

	/**
	 * Registers the required implementations and hooks into the required
	 * actions and filters.
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function register() {
		// Register the provider in the container.
		$this->container->singleton( self::class, $this );

		$this->container->singleton( Events::class, Events::class );
		$this->container->singleton( State::class, State::class );
		$this->container->singleton( String_Dictionary::class, String_Dictionary::class );
		$this->container->singleton( Site_Report::class, Site_Report::class );
		$this->container->singleton( Admin\Template::class, Admin\Template::class );
		$this->container->singleton( Maintenance_Mode::class, Maintenance_Mode::class );
		$this->container->singleton( Process::class, Process::class );
		$this->container->singleton( Ajax::class, Ajax::class );
		$this->container->singleton( Asset_Loader::class, Asset_Loader::class );
		$this->container->singleton( Event_Report_Categories::class, Event_Report_Categories::class );
		$this->container->register( Download_Report_Provider::class );

		$this->load_action_scheduler();

		add_action( 'init', [ $this, 'init' ] );

		// Action Scheduler will fire this action: on it we'll migrate, or preview the migration of, an Event.
		add_action( Process_Worker::ACTION_PROCESS, [ $this, 'migrate_event' ], 10, 2 );
		add_action( Process_Worker::ACTION_UNDO, [ $this, 'undo_event_migration' ] );
		add_action( Process_Worker::ACTION_CHECK_PHASE, [ $this, 'check_migration_phase' ], 10, 2 );

		// Hook on the AJAX actions that will start, report about, and cancel the migration.
		// Before the JSON report is dispatched (and the request exits) migrate some events.
		add_action( Ajax::ACTION_REPORT, [ $this, 'migrate_events_on_js_poll' ], 9 );
		add_action( Ajax::ACTION_REPORT, [ $this, 'send_report' ] );
		add_action( Ajax::ACTION_PAGINATE_EVENTS, [ $this, 'paginate_events' ] );
		add_action( Ajax::ACTION_START, [ $this, 'start_migration' ] );
		add_action( Ajax::ACTION_CANCEL, [ $this, 'cancel_migration' ] );
		add_action( Ajax::ACTION_REVERT, [ $this, 'revert_migration' ] );
		add_action( 'action_scheduler_bulk_cancel_actions', [ $this, 'cancel_async_actions' ] );
		add_action( 'action_scheduler_canceled_action', [ $this, 'cancel_async_action' ] );

		if ( is_admin() ) {
			add_action( 'admin_enqueue_scripts', $this->container->callback( Asset_Loader::class, 'enqueue_scripts' ) );
			// Hook into the Upgrade tab to show it and customize its contents.
			add_filter( 'tec_events_upgrade_tab_has_content', [ $this, 'show_upgrade_tab' ] );
			add_filter( 'tribe_upgrade_fields', [ $this, 'add_phase_callback' ] );
		}
	}

	/**
	 * Run actions on WordPress 'init' action.
	 */
	public function init() {
		// Activate maintenance mode, if required.
		$this->activate_maintenance_mode();
	}

	/**
	 * Unhooks the hooks set by the Provider in the `register` method.
	 *
	 * @since 6.0.0
	 *
	 * @return void The method does not return any value and will have the side
	 *              effect of unsetting the hooks set in the `register` method.
	 */
	public function unregister() {
		remove_action( Process_Worker::ACTION_PROCESS, [ $this, 'migrate_event' ], );
		remove_action( Process_Worker::ACTION_UNDO, [ $this, 'undo_event_migration' ] );
		remove_action( Process_Worker::ACTION_CHECK_PHASE, [ $this, 'check_migration_phase' ], );
		remove_action( Ajax::ACTION_REPORT, [ $this, 'send_report' ] );
		remove_action( Ajax::ACTION_PAGINATE_EVENTS, [ $this, 'paginate_events' ] );
		remove_action( Ajax::ACTION_START, [ $this, 'start_migration' ] );
		remove_action( Ajax::ACTION_CANCEL, [ $this, 'cancel_migration' ] );
		remove_action( Ajax::ACTION_REVERT, [ $this, 'revert_migration' ] );
		remove_action( 'action_scheduler_bulk_cancel_actions', [ $this, 'cancel_async_actions' ] );
		remove_action( 'action_scheduler_canceled_action', [ $this, 'cancel_async_action' ] );
		remove_action( 'admin_enqueue_scripts', $this->container->callback( Asset_Loader::class, 'enqueue_scripts' ) );
		remove_filter( 'tec_events_upgrade_tab_has_content', [ $this, 'show_upgrade_tab' ] );
		remove_filter( 'tribe_upgrade_fields', [ $this, 'remove_phase_callback' ] );
	}

	/**
	 * Executes one step of the migration process to migrate, or preview
	 * the migration of, one Event.
	 *
	 * @since 6.0.0
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
	 * @since 6.0.0
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
	 * @since 6.0.0
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
	 * @since 6.0.0
	 *
	 * @param numeric $action_id The action scheduler action ID.
	 */
	public function cancel_async_action( $action_id ) {
		$this->container->make( Process::class )->cancel_async_action( $action_id );
	}

	/**
	 * Respond to bulk canceled Action Scheduler actions.
	 *
	 * @since 6.0.0
	 *
	 * @param array $action_id A list of the action scheduler action IDs.
	 */
	public function cancel_async_actions( $action_ids ) {
		if ( ! is_array( $action_ids ) ) {
			return;
		}

		$this->container->make( Process::class )->cancel_async_actions( $action_ids );
	}

	/**
	 * Sends (echoes) a JSON format report of the site migration.
	 *
	 * @since 6.0.0
	 *
	 * @return void The method does not return any value and will have the side effect
	 *              of echoing a JSON format string back for the Migration UI JS component
	 *              to consume.
	 */
	public function send_report() {
		return $this->container->make( Ajax::class )->send_report();
	}

	/**
	 * Sends (echoes) a JSON format report of a batch of paginated events.
	 *
	 * @since 6.0.0
	 *
	 * @return void The method does not return any value and will have the side effect
	 *              of echoing a JSON format string back for the Migration UI JS component
	 *              to consume.
	 */
	public function paginate_events() {
		return $this->container->make( Ajax::class )->paginate_events();
	}

	/**
	 * Starts the migration and sends the initial report.
	 *
	 * @since 6.0.0
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
	 * @since 6.0.0
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
	 * @since 6.0.0
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
	 * @since 6.0.0
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
	 * @since 6.0.0
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
	 * @since 6.0.0
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
	 * @since 6.0.0
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
	 * @since 6.0.0
	 */
	public function load_action_scheduler_late() {
		$action_scheduler_file = TEC::instance()->plugin_path . '/vendor/woocommerce/action-scheduler/action-scheduler.php';
		require_once $action_scheduler_file;
	}

	/**
	 * Piggy-back on the Migration UI JS component polling of the backend to migrate some events, if possible.
	 *
	 * @since 6.0.2
	 *
	 * @return void Some Events might be migrated.
	 */
	public function migrate_events_on_js_poll(): void {
		if ( ! in_array( $this->container->make( State::class )->get_phase(), [
			State::PHASE_MIGRATION_IN_PROGRESS,
			State::PHASE_PREVIEW_IN_PROGRESS
		] ) ) {
			return;
		}

		/**
		 * Filters how many Events should be migrated in a single AJAX request to the Migration UI backend.
		 *
		 * @since 6.0.2
		 *
		 * @param int $count The number of Events to migrate on the migration UI JS component polling; returning
		 *                   `0` will disable the functionality.
		 */
		$count = apply_filters( 'tec_events_custom_tables_v1_migration_js_poll_count', 10 );

		if ( ! $count ) {
			return;
		}

		$this->container->make( Process_Worker::class )->migrate_many_events( $count );
	}
}