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

		$this->container->singleton( Strings::class, Strings::class );
		$this->container->singleton( State::class, State::class );
		$this->container->singleton( Site_Report::class, Site_Report::class );
		$this->container->singleton( Events::class, Events::class );
		$this->container->singleton( Page::class, Page::class );
		$this->container->singleton( Maintenance_Mode::class, Maintenance_Mode::class );
		$this->container->singleton( Process::class, Process::class );
		$this->container->singleton( Ajax::class, Ajax::class );

		// Action Scheduler will fire this action: on it we'll migrate, or preview the migration of, an Event.
		add_action( Process::ACTION_PROCESS, [ $this, 'migrate_event' ], 10, 2 );
		add_action( Process::ACTION_UNDO, [ $this, 'undo_event_migration' ] );

		// Activate maintenance mode, if required.
		add_action( 'init', [ $this, 'activate_maintenance_mode' ] );

		// Hook on the AJAX actions that will start, report about, and cancel the migration.
		add_action( Ajax::ACTION_REPORT, [ $this, 'get_report' ] );
		add_action( Ajax::ACTION_START, [ $this, 'start_migration' ] );
		add_action( Ajax::ACTION_CANCEL, [ $this, 'cancel_migration' ] );
		add_action( Ajax::ACTION_UNDO, [ $this, 'undo_migration' ] );

		if ( is_admin() ) {
			// Hook into the Upgrade tab to show it and customize its contents.
			add_filter( 'tribe_events_show_upgrade_tab', [ $this, 'show_upgrade_tab' ] );
			add_filter( 'tribe_upgrade_fields', [ $this, 'add_phase_callback' ] );

			add_action( 'admin_enqueue_scripts', [ $this, 'register_scripts' ], 10 );
			add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ], 11 );

			// @todo delegate this to upgrade tab class?
			add_action( 'admin_footer', [ $this, 'inject_progress_modal' ] );
			add_action( 'admin_print_footer_scripts', [ $this, 'inject_progress_modal_js_trigger' ], PHP_INT_MAX );
		}
	}

	/**
	 * Registers the scripts required by the service provider.
	 *
	 * @since TBD
	 */
	public function register_scripts() {
		wp_register_style(
			'tec-ct1-upgrade-admin-css',
			TEC::instance()->plugin_url .'src/resources/css/custom-tables-v1/ct1-upgrade.css'
		);
		wp_register_script(
			'tec-ct1-upgrade-admin-js',
			TEC::instance()->plugin_url . 'src/resources/js/custom-tables-v1/ct1-upgrade.js'
		);
	}

	/**
	 * Enqueues the scripts required by the service provider.
	 *
	 * @since TBD
	 */
	public function enqueue_scripts() {
		if ( ! isset( $_GET['page'] ) ) {
			return;
		}

		if ( $_GET['page'] !== tribe( 'settings' )->adminSlug ) {
			return;
		}

		wp_enqueue_style( 'tec-ct1-upgrade-admin-css' );
		wp_enqueue_script( 'tec-ct1-upgrade-admin-js' );
		wp_localize_script( 'tec-ct1-upgrade-admin-js',
			'tecCt1Upgrade',
			[
				'ajaxUrl' => admin_url()  . 'admin-ajax.php',
				'actions' => [
					'get_report'       => str_replace( 'wp_ajax_', '', Ajax::ACTION_REPORT ),
					'start_migration'  => str_replace( 'wp_ajax_', '', Ajax::ACTION_START ),
					'cancel_migration' => str_replace( 'wp_ajax_', '', Ajax::ACTION_CANCEL ),
					'undo_migration'   => str_replace( 'wp_ajax_', '', Ajax::ACTION_UNDO ),
				]
			]
		);
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
	public function get_report() {
		return $this->container->make( Ajax::class )->get_report();
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
	 * Inject the Admin\Progress_Modal trigger that pops open the modal.
	 *
	 * @since TBD
	 */
	public function inject_progress_modal_js_trigger() {
		// @todo should this stay here?
		$modal = $this->container->make( Admin\Progress_Modal::class );
		echo $modal->get_modal_auto_trigger();
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
}