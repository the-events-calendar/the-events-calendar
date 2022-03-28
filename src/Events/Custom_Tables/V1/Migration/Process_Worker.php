<?php
/**
 * Does the migration and undo operations.
 *
 * @since   TBD
 * @package TEC\Events\Custom_Tables\V1\Migration;
 */

namespace TEC\Events\Custom_Tables\V1\Migration;

use TEC\Events\Custom_Tables\V1\Migration\Reports\Event_Report;
use TEC\Events\Custom_Tables\V1\Migration\Strategies\Single_Event_Migration_Strategy;
use TEC\Events\Custom_Tables\V1\Migration\Strategies\Strategy_Interface;
use TEC\Events\Custom_Tables\V1\Schema_Builder\Schema_Builder;
use TEC\Events\Custom_Tables\V1\Traits\With_Database_Transactions;

/**
 * Class Process_Worker. Handles the migration and undo operations.
 *
 * @since   TBD
 * @package TEC\Events\Custom_Tables\V1\Migration;
 */
class Process_Worker {
	use With_Database_Transactions;

	/**
	 * The full name of the action that will be fired to signal one
	 * Event should be migrated, or have its migration previewed.
	 */
	const ACTION_PROCESS = 'tec_events_custom_tables_v1_migration_process';
	/**
	 * The full name of the action that will be fired to signal one
	 * Event should be undone.
	 */
	const ACTION_UNDO = 'tec_events_custom_tables_v1_migration_cancel';

	/**
	 * The full name of the action that will be fired to signal the background
	 * operation the phase should be checked.
	 */
	const ACTION_CHECK_PHASE = 'tec_events_custom_tables_v1_migration_check_state';

	/**
	 * A reference to the current Events' migration repository.
	 *
	 * @since TBD
	 * @var Events
	 */
	private $events;

	/**
	 * A reference to the migration state object.
	 *
	 * @var State;
	 */
	private $state;

	/**
	 * A reference to the current event report, the one associated
	 * to the Event that is being migrated.
	 *
	 * @since TBD
	 *
	 * @var Event_Report|null
	 */
	private $event_report;

	/**
	 * A flag property used to keep track of whether a started
	 * migration completed or not.
	 *
	 * @since TBD
	 *
	 * @var bool|null
	 */
	private $migration_completed;

	/**
	 * Whether the current event migration is running in dry-run mode or not.
	 *
	 * @since TBD
	 *
	 * @var bool|null
	 */
	private $dry_run;

	/**
	 * Process_Worker constructor.
	 *
	 * @since TBD
	 *
	 * @param Events $events A reference to the current Events' migration repository.
	 * @param State  $state  A reference to the migration state data.
	 */
	public function __construct( Events $events, State $state ) {
		$this->events = $events;
		$this->state  = $state;
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
		/*
		 * Get our Event_Report ready for the strategy.
		 * This is also used in our error catching, so needs to be defined outside that block.
		 */
		$this->event_report = new Event_Report( get_post( $post_id ) );

		if ( $this->check_phase() ) {
			// We're done, the migration is complete and there is no more work to do.
			return $this->event_report;
		}

		$this->dry_run = $dry_run;

		// Set our dead-man switch.
		$this->migration_completed = false;

		// Watch for any errors that may occur and store them in the Event_Report.
		set_error_handler( [ $this, 'error_handler' ] );

		// Set this as a fallback: we'll remove it later if everything goes fine.
		add_action( 'shutdown', [ $this, 'shutdown_handler' ] );

		/*
		 * If some calls `die` or `exit` during the migration PHP might not trigger
		 * shutdown. For that purpose let's buffer and try to capture the event and
		 * the reason for it.
		 */
		ob_start( [ $this, 'ob_flush_handler' ] );

		try {
			// Check if we are still in migration phase.
			if ( ! in_array( $this->state->get_phase(), [
				State::PHASE_PREVIEW_IN_PROGRESS,
				State::PHASE_MIGRATION_IN_PROGRESS
			], true ) ) {
				$this->event_report->migration_failed( 'Canceled.' );
				$this->migration_completed = true;

				return $this->event_report;
			}

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

			$this->event_report->start_event_migration();

			if($this->dry_run) {
				$this->transaction_start();
			}

			// Apply strategy, use Event_Report to flag any pertinent details or any failure events.
			$strategy->apply( $this->event_report );

			// If no error, mark successful.
			if ( ! $this->event_report->error ) {
				$this->event_report->migration_success();
			}
		} catch ( \Throwable $e ) {
			// In case we fail above, release transaction.
			if ( $this->dry_run ) {
				$this->transaction_rollback();
			}
			$this->event_report->migration_failed( $e->getMessage() );
		} catch ( \Exception $e ) {
			// In case we fail above, release transaction.
			if ( $this->dry_run ) {
				$this->transaction_rollback();
			}
			$this->event_report->migration_failed( $e->getMessage() );
		}

		$this->migration_completed = true;

		// Restore error handling.
		restore_error_handler();
		// Remove shutdown hook.
		remove_action( 'shutdown', [ $this, 'shutdown_handler' ] );
		// Close the output buffer.
		ob_end_clean();

		// Get next event to process.
		$post_id = $this->events->get_id_to_process();

		if ( $post_id ) {
			// Enqueue a new (Action Scheduler) action to import another Event.
			$action_id = as_enqueue_async_action( self::ACTION_PROCESS, [ $post_id, $dry_run ] );

			if ( empty( $action_id ) ) {
				// If we cannot migrate the next Event we need to migrate, then the migration has failed.
				$this->event_report->migration_failed( "Cannot enqueue action to migrate Event with post ID $post_id." );
			}
		} else if ( ! $this->check_phase() ) {
			$action_id = as_enqueue_async_action( self::ACTION_CHECK_PHASE );

			if ( empty( $action_id ) ) {
				// The migration might have technically completed, but we cannot know for sure and will be conservative.
				$this->event_report->migration_failed( "Cannot enqueue action to check migration status." );
			}
		}

		// Do not hold a reference to the Report once the worker is done.
		$event_report       = $this->event_report;
		$this->event_report = null;

		$this->check_phase();

		return $event_report;
	}

	/**
	 * Undoes an Event migration.
	 *
	 * @since TBD
	 *
	 * @param array<string, mixed> The metadata we pass to ourselves.
	 *
	 */
	public function undo_event_migration( $meta ) {

		if ( ! isset( $meta['started_timestamp'] ) ) {
			$meta['started_timestamp'] = time();
		}

		$seconds_to_wait  = 60 * 5; // 5 minutes
		$max_time_reached = ( time() - $meta['started_timestamp'] ) > $seconds_to_wait;

		// Are we still processing some events? If so, recurse and wait to do the undo operation.
		if ( ! $max_time_reached && $this->events->get_total_events_in_progress() ) {
			as_enqueue_async_action( self::ACTION_UNDO, [ $meta ] );

			return;
		}

		// @todo Review - missing anything? Better way?
		do_action( 'tec_events_custom_tables_v1_migration_before_cancel' );

		tribe( Schema_Builder::class )->down();

		// Clear meta values.
		$meta_keys = [
			Event_Report::META_KEY_MIGRATION_LOCK_HASH,
			Event_Report::META_KEY_REPORT_DATA,
			Event_Report::META_KEY_MIGRATION_PHASE,
		];

		/**
		 * Filters the list of post meta keys to be removed during a migration cancel.
		 *
		 * @since TBD
		 *
		 * @param array<string> $meta_keys List of keys to delete.
		 */
		$meta_keys = apply_filters( 'tec_events_custom_tables_v1_delete_meta_keys', $meta_keys );
		foreach ( $meta_keys as $meta_key ) {
			delete_metadata( 'post', 0, $meta_key, '', true );
		}

		$this->state->set( 'phase', State::PHASE_PREVIEW_PROMPT );
		$this->state->save();

		do_action( 'tec_events_custom_tables_v1_migration_after_cancel' );
	}

	/**
	 * Handles non-fatal errors that might be triggered during the migration.
	 *
	 * @since TBD
	 *
	 * @param int    $errno   The error code.
	 * @param string $errstr  The error message.
	 * @param string $errfile The path to the file the error was triggered from.
	 * @param int    $errline The file line the error was triggered from.
	 *
	 * @return void The method never returns and will always throw when encountering
	 *              an error during the migration.
	 *
	 * @throws Migration_Exception A reference to an exception wrapping the error.
	 */
	public function error_handler( $errno, $errstr, $errfile, $errline ) {
		// Delegate to our try/catch handler.
		throw new Migration_Exception( $errstr, $errno );
	}

	/**
	 * Hooked to the WordPress `shutdown` hook.
	 * This method should be removed during a successful migration
	 * or one that is properly handled. If not, then this method is
	 * an attempt to log the failure.
	 *
	 * @since TBD
	 */
	public function shutdown_handler(  ) {
		// In case we fail above, release transaction.
		if ( $this->dry_run ) {
			$this->transaction_rollback();
		}
		// If we're here, the migration failed.
		$this->event_report->migration_failed( 'Unknown error occurred, shutting down.' );
	}

	/**
	 * Checks and updates the migration phase depending on the current status of the database.
	 *
	 * This is an idem-potent method that will only ste the migration state to done
	 * when done; two or more concurrent workers doing the same will not break the
	 * logic.
	 *
	 * @since TBD
	 *
	 * @return bool Whether the migration, or its preview, is completed or not.
	 */
	public function check_phase( ) {
		$phase               = $this->state->get_phase();
		$migration_completed = in_array(
			                       $phase, [
			                       State::PHASE_MIGRATION_IN_PROGRESS,
			                       State::PHASE_PREVIEW_IN_PROGRESS
		                       ], true )
		                       && $this->events->get_total_events_remaining() === 0
		                       && $this->events->get_total_events_in_progress() === 0;

		if ( ! $migration_completed ) {
			return false;
		}

		$next_phase = $phase === State::PHASE_PREVIEW_IN_PROGRESS ?
			State::PHASE_MIGRATION_PROMPT
			: State::PHASE_MIGRATION_COMPLETE;
		$this->state->set( 'phase', $next_phase );
		$this->state->set( 'migration', 'estimated_time_in_seconds', $this->events->calculate_time_to_completion() );
		$this->state->set( 'complete_timestamp', time() );
		$this->state->save();

		return true;
	}

	/**
	 * Hooked to the `ob_start` function, this method will run consistently
	 * across PHP versions when the `die` or `exit` function is called during
	 * the migration process.
	 *
	 * @since TBD
	 *
	 * @param string $buffer A string buffer that will contain all the output
	 *                       produced by the PHP code before the `die` or `exit`
	 *                       call.
	 */
	public function ob_flush_handler( $buffer ) {
		if ( $this->migration_completed ) {
			// If we set the switch flag, then we already handled possible errors.

			return;
		}
		// In case we fail above, release transaction.
		if ( $this->dry_run ) {
			$this->transaction_rollback();
		}

		/**
		 * Since we're storing output of arbitrary length in the database, let's
		 * trim it down to something that should not go over the `mysql_max_packet`
		 * size.
		 */
		$trimmed_buffer = substr( $buffer, 0, 1024 );

		// If we're here, some code called `die` or `exit`.
		$this->event_report->migration_failed(
			'The "die" or "exit" function was called during the migration process; output: ' . $trimmed_buffer
		);

		/*
		 * This method might be the last executing before a hard `die` or `exit` call, let's check the phase.
		 * If we could not queue further actions to process more Events or check the phase, let's do it now.
		 */
		$this->check_phase();
	}

	/**
	 * Checks if the current phase is completed or not,
	 * else queue another action to run the same check.
	 *
	 * @since TBD
	 *
	 * @return int The ID of the new Action scheduled to check
	 *             on the migration phase, `0` if no new Action
	 *             was queued.
	 */
	public function check_phase_complete() {
		$completed = ! $this->state->is_running() || $this->check_phase();

		if ( $completed ) {
			// Clear all of our queued state check workers.
			as_unschedule_all_actions( self::ACTION_CHECK_PHASE );

			return 0;
		}

		// Check again.
		return as_enqueue_async_action( self::ACTION_CHECK_PHASE );
	}
}
