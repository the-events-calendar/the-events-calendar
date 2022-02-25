<?php
/**
 * Handles the background processing the migration will use to migrate
 * events independently of the cron and user intervention.
 *
 * @since   TBD
 * @package TEC\Events\Custom_Tables\V1\Migration;
 */

namespace TEC\Events\Custom_Tables\V1\Migration;

use TEC\Events\Custom_Tables\V1\Migration\Reports\Event_Report;

/**
 * Class Process.
 *
 * @since   TBD
 * @package TEC\Events\Custom_Tables\V1\Migration;
 */
class Process {

	/**
	 * The full name of the action that will be fired to signal one
	 * Event should be migrated, or have its migration previewed.
	 */
	const ACTION_PROCESS = 'tec_events_custom_tables_v1_migration_process';
	/**
	 * The full name of the action that will be fired to signal one
	 * Event should be canceled.
	 */
	const ACTION_CANCEL = 'tec_events_custom_tables_v1_migration_cancel';
	/**
	 * The full name of the action that will be fired to signal one
	 * Event should be undone.
	 */
	const ACTION_UNDO = 'tec_events_custom_tables_v1_migration_cancel';
	/**
	 * A reference to the current Events' migration repository.
	 *
	 * @since TBD
	 * @var Events
	 */
	private $events;

	/**
	 * Process constructor.
	 * since TBD
	 *
	 * @param Events $events A reference to the current Events' migration repository.
	 */
	public function __construct( Events $events ) {
		$this->events = $events;
	}

	/**
	 * Processes an Event migration.
	 *
	 * @since TBD
	 *
	 * @param int  $post_id The post ID of the Evente to migrate.
	 * @param bool $dry_run Whether the migration should commit or just preview
	 *                      the changes.
	 *
	 * @return Event_Report A reference to the migration report object produced by the
	 *                      migration.
	 */
	public function migrate_event( $post_id, $dry_run = false ) {
		try {
			/**
			 * Filters the migration strategy that should be used to migrate an Event.
			 * Returning an object implementing the TEC\Events\Custom_Tables\V1\Migration\Strategy_Interface
			 * here will prevent TEC from using the default one.
			 *
			 * @since TBD
			 *
			 * @param Strategy_Interface A reference to the migration strategy that should be used.
			 *                          Initially `null`.
			 * @param int  $post_id     The post ID of the Event to migrate.
			 * @param bool $dry_run     Whether the strategy should be provided for a real migration
			 *                          or its preview.
			 */
			$strategy = apply_filters( 'tec_events_custom_tables_v1_migration_strategy', null, $post_id, $dry_run );

			if ( ! $strategy instanceof Strategy_Interface ) {
				$strategy = new Single_Event_Migration_Strategy( $post_id, $dry_run );
			}

			// Get our Event_Report ready for the strategy.
			$event_report = new Event_Report( get_post( $post_id ) );
			$event_report->start_event_migration();

			// Apply strategy, use Event_Report to flag any pertinent details or any failure events.
			$strategy->apply( $event_report );
		} catch ( \Throwable $e ) {
			$event_report->migration_failed( $e->getMessage() );
		} catch ( \Exception $e ) {
			$event_report->migration_failed( $e->getMessage() );
		}

		// If no error, mark successful.
		if ( ! $event_report->error ) {
			$event_report->migration_success();
		}

		$post_id = $this->events->get_id_to_process();

		if ( $post_id ) {
			// Enqueue a new (Action Scheduler) action to import another Event.
			$action_id = as_enqueue_async_action( self::ACTION_PROCESS, [ $post_id, $dry_run ] );

			//@todo check action ID here and log on failure.
		}

		// @todo Doing these State checks here is likely going to slow the processing by an order of magnitude. Better place?
		$events_repo = tribe( Events::class );
		if ( $events_repo->get_total_events_remaining() === 0 ) {
			$state = tribe( State::class );
			$state->set( 'migration', 'estimated_time_in_seconds', $events_repo->calculate_time_to_completion() );
			$state->save();
		}

		return $event_report;
	}

	/**
	 * Undoes an Event migration.
	 *
	 * @since TBD
	 *
	 * @param int $post_id The post ID of the Event to undo the migration for.
	 *
	 * @return Event_Report A reference to the migration report object produced by the
	 *                      migration undoing.
	 */
	public function undo_event_migration( $post_id ) {
		try {
			/**
			 * Filters the migration strategy that should be used to undo an Event migration.
			 * Returning an object implementing the TEC\Events\Custom_Tables\V1\Migration\Strategy_Interface
			 * here will prevent TEC from using the default one.
			 *
			 * @since TBD
			 *
			 * @param Strategy_Interface A reference to the migration strategy that should be used.
			 *                           Initially `null`.
			 * @param int $post_id       The post ID of the Event to undo the migration for.
			 */
			$strategy = apply_filters( 'tec_events_custom_tables_v1_migration_undo_strategy', null, $post_id );

			if ( ! $strategy instanceof Strategy_Interface ) {
				$strategy = new Single_Event_Migration_Strategy( $post_id, false );
			}

			// Get our Event_Report ready for the strategy.
			$event_report = new Event_Report( get_post( $post_id ) );
			$event_report->start_event_undo_migration();

			$event_report = $strategy->undo( $event_report );
		} catch ( \Throwable $e ) {
			$event_report->undo_failed( $e->getMessage() );
		} catch ( \Exception $e ) {
			$event_report->undo_failed( $e->getMessage() );
		}

		// If we were successful, clear our report.
		if ( ! $event_report->error ) {
			$event_report->undo_success();
		}

		$post_id = $this->events->get_id_to_process( true );

		if ( $post_id ) {
			// Enqueue a new (Action Scheduler) action to undo another Event migration.
			$action_id = as_enqueue_async_action( self::ACTION_UNDO, [ $post_id ] );

			//@todo check action ID here and log on failure.
		}

		return $event_report;
	}

	/**
	 * Starts the migration enqueueing the first set of Events to process.
	 *
	 * @since TBD
	 *
	 * @param bool $dry_run Whether to do a preview or finalize the migration operations.
	 *
	 * @return int The number of Events queued for migration.
	 */
	public function start( $dry_run = true ) {
		$action_ids = [];

		foreach ( $this->events->get_ids_to_process( 50 ) as $post_id ) {
			$action_ids[] = as_enqueue_async_action( self::ACTION_PROCESS, [ $post_id, $dry_run ] );
		}

		return count( array_filter( $action_ids ) );
	}

	/**
	 * Starts the migration cancellation.
	 *
	 * @since TBD
	 * @return int The number of Events queued for undo.
	 */
	public function cancel() {
		// This will target all of our processing actions
		as_unschedule_all_actions( self::ACTION_PROCESS );

		// Now kick-off the undo
		return $this->undo();
	}

	/**
	 * Starts the migration undoing process.
	 *
	 * @since TBD
	 * @return int The number of Events queued for undo.
	 */
	public function undo() {
		$action_ids = [];

		foreach ( $this->events->get_ids_to_process( 50, true ) as $post_id ) {
			$action_ids[] = as_enqueue_async_action( self::ACTION_UNDO, [ $post_id ] );
		}

		return count( array_filter( $action_ids ) );
	}
}