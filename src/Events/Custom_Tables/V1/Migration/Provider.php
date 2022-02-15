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
use TEC\Events\Custom_Tables\V1\WP_Query\Provider_Contract;

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

		$this->container->singleton( State::class, State::class );
		$this->container->singleton( Reports::class, Reports::class );
		$this->container->singleton( Events::class, Reports::class );
		$this->container->singleton( Page::class, Page::class );
		$this->container->singleton( Maintenance_Mode::class, Maintenance_Mode::class );
		$this->container->singleton( Process::class, Process::class );
		$this->container->singleton( Ajax::class, Ajax::class );

		// Action Scheduler will fire this action: on it we'll migrate, or preview the migration of, an Event.
		add_action( Process::ACTION_PROCESS, [ $this, 'migrate_event' ] );
		add_action( Process::ACTION_CANCEL, [ $this, 'cancel_event_migration' ] );
		add_action( Process::ACTION_UNDO, [ $this, 'undo_event_migration' ] );

		// Activate maintenance mode, if required.
		add_action( 'init', [ $this, 'activate_maintenance_mode' ] );

		// Hook on the AJAX actions that will start, report about, and cancel the migration.
		add_action( Ajax::ACTION_REPORT, [ $this, 'send_report' ] );
		add_action( Ajax::ACTION_START, [ $this, 'start_migration' ] );
		add_action( Ajax::ACTION_CANCEL, [ $this, 'cancel_migration' ] );
		add_action( Ajax::ACTION_UNDO, [ $this, 'undo_migration' ] );

		if ( is_admin() ) {
			// @todo delegate this to upgrade tab class
			add_action( 'admin_footer', [ $this, 'inject_progress_modal' ] );
			add_action( 'admin_print_footer_scripts', [ $this, 'inject_progress_modal_js_trigger' ], PHP_INT_MAX );
			add_action( 'admin_footer', [ $this, 'inject_v2_disable_modal' ] );
			$phase_callback = $this->container->callback( Admin\Upgrade_Tab::class, 'add_phase_content' );
			add_filter( 'tribe_upgrade_fields', $phase_callback );
			// @todo this should be the entry point since the Views v2 upgrade tab was removed ...
			add_action( 'tribe_settings_do_tabs', [ $this, 'show_upgrade_tab' ] );
		}
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
		$this->container->make( Process::class )->migrate_event( $post_id, $dry_run );
	}

	/**
	 * Executes one step of the migration process to cancel the migration of one Event.
	 *
	 * @since TBD
	 *
	 * @param int $post_id The post ID of the Event to cancel the migration for.
	 *
	 * @return void The method does not return any value but will trigger the action
	 *              that will cancel the Event migration.
	 */
	public function cancel_event_migration( $post_id ) {
		$this->container->make( Process::class )->cancel_event_migration( $post_id );
	}

	/**
	 * Executes one step of the migration process to undo the migration of one Event.
	 *
	 * @since TBD
	 *
	 * @param int $post_id The post ID of the Event to undo the migration for.
	 *
	 * @return void The method does not return any value but will trigger the action
	 *              that will undo the Event migration.
	 */
	public function undo_event_migration( $post_id ) {
		$this->container->make( Process::class )->undo_event_migration( $post_id );
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
		$this->container->make( Ajax::class )->get_report();
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
	public function undo_migration() {
		$this->container->make( Ajax::class )->undo_migration();
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
	 * Filters whether the upgrade tab should show.
	 *
	 * @param bool $should_show Show tab state.
	 *
	 * @return bool
	 */
	public function show_upgrade_tab( $should_show ) {
		// @todo review this logic and move to Upgrade_Tab
		if ( $should_show ) {
			return $should_show;
		}

		$upgrade_tab = $this->container->make( Admin\Upgrade_Tab::class );

		return $upgrade_tab->should_show();
	}

	/**
	 * Inject the content and data of the Admin\Progress_Modal.
	 *
	 * @since TBD
	 */
	public function inject_progress_modal() {
		// @todo should this stay here?
		$modal = $this->container->make( Admin\Progress_Modal::class );
		echo $modal->render_modal();
	}

	/**
	 * Inject the content and data of the Admin\V2_Disable_Modal.
	 *
	 * @since TBD
	 */
	public function inject_v2_disable_modal() {
		// @todo should this stay at all?
		$modal = $this->container->make( Admin\V2_Disable_Modal::class );
		echo $modal->render_modal();
	}

	/**
	 * Inject the Admin\Progress_Modal trigger that pops open the modal.
	 *
	 * @since TBD
	 */
	public function inject_progress_modal_js_trigger() {
		// @todo should this stay here?
		$modal = $this->container->make( Admin\Progress_Modal::class );
		echo $modal->get_modal_auto_trigger();
	}
}