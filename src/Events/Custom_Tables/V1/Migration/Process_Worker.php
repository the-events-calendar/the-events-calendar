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
use TEC\Events\Custom_Tables\V1\Migration\Expected_Migration_Exception;
use TEC\Events\Custom_Tables\V1\Migration\Strategies\Strategy_Interface;
use TEC\Events\Custom_Tables\V1\Models\Builder;
use TEC\Events\Custom_Tables\V1\Schema_Builder\Schema_Builder;
use TEC\Events\Custom_Tables\V1\Traits\With_Database_Transactions;
use Tribe__Admin__Notices;

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
	 * @since TBD
	 *
	 * @return string The HTML markup with the event link.
	 */
	public function get_event_link_markup() {
		$post_id = $this->event_report->source_event_post->ID;
		$post    = get_post( $post_id );

		return '<a target="_blank" href="' . get_edit_post_link( $post_id ) . '">' . $post->post_title . '</a>';
	}

	/**
	 * Processes an Event migration.
	 *
	 * @since TBD
	 *
	 * @param int  $post_id The post ID of the Event to migrate.
	 * @param bool $dry_run Whether the migration should commit or just preview
	 *                      the changes.
	 *
	 * @return Event_Report A reference to the migration report object produced by the
	 *                      migration.
	 */
	public function migrate_event( $post_id, $dry_run = false ) {
		// Log our worker starting
		do_action( 'tribe_log', 'debug', 'Worker: Migrate event:start', [
			'source'  => __CLASS__ . ' ' . __METHOD__ . ' ' . __LINE__,
			'post_id' => $post_id,
			'dry_run' => $dry_run
		] );

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
				$this->event_report->migration_failed( 'canceled' );
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

			// In case we have an error in the strategy, and we are forced to exit early, lets start the transaction here.
			if ( $this->dry_run ) {
				$this->start_transaction();
			}

			// Apply strategy, use Event_Report to flag any pertinent details or any failure events.
			$strategy->apply( $this->event_report );

			if ( $this->dry_run ) {
				$this->rollback_transaction();

				/*
				 * Our event report state would have been rolled back too, so try and reapply what was set locally.
				 * Clear our cache, since it reflects local state and not aware of transaction rollbacks.
				 */
				clean_post_cache( $post_id );

				$this->event_report->migration_success();
			} else {
				$this->transaction_commit();
			}

			// If no error, mark successful.
			if ( ! $this->event_report->error ) {
				$this->event_report->migration_success();
			}
		} catch ( Expected_Migration_Exception $e ) {
			if ( $this->dry_run ) {
				$this->rollback_transaction();
			}
			$this->event_report->migration_failed( 'expected-exception', [
				$e->getMessage(),
			] );
		} catch ( \Throwable $e ) {
			// In case we fail above, release transaction.
			if ( $this->dry_run ) {
				$this->rollback_transaction();
			}
			$this->event_report->migration_failed( 'exception', [
				'<p>',
				$this->get_event_link_markup(),
				$e->getMessage(),
				'</p>',
				'<p>',
				'</p>'
			] );
		} catch ( \Exception $e ) {
			// In case we fail above, release transaction.
			if ( $this->dry_run ) {
				$this->rollback_transaction();
			}

			$this->event_report->migration_failed( 'exception', [
				'<p>',
				$this->get_event_link_markup(),
				$e->getMessage(),
				'</p>',
				'<p>',
				'</p>'
			] );
		}

		$this->migration_completed = true;

		// Restore error handling.
		restore_error_handler();
		// Remove shutdown hook.
		remove_action( 'shutdown', [ $this, 'shutdown_handler' ] );
		// Close the output buffer.
		ob_end_clean();

		$did_migration_error = ! $dry_run && $this->event_report->error;
		$continue_queue      = true;
		// If error in the migration phase, need to stop the queue.
		if ( $did_migration_error ) {
			$continue_queue = false;
		}

		if ( $continue_queue ) {
			// Get next event to process.
			$next_post_id = $this->events->get_id_to_process();

			if ( $next_post_id ) {
				// Enqueue a new (Action Scheduler) action to import another Event.
				$action_id = as_enqueue_async_action( self::ACTION_PROCESS, [ $next_post_id, $dry_run ] );

				if ( empty( $action_id ) ) {
					// If we cannot migrate the next Event we need to migrate, then the migration has failed.
					$this->event_report->migration_failed( "enqueue-failed", [
						'<p>',
						$this->get_event_link_markup(),
						$next_post_id,
						'</p>',
						'<p>',
						'</p>'
					] );
				}
			} else if ( ! $this->check_phase() ) {
				// Start a recursive check, but only if we are not already doing so.
				if ( ! as_has_scheduled_action( self::ACTION_CHECK_PHASE ) ) {
					$action_id = as_enqueue_async_action( self::ACTION_CHECK_PHASE );
					if ( empty( $action_id ) ) {
						// The migration might have technically completed, but we cannot know for sure and will be conservative.
						$this->event_report->migration_failed( "check-phase-enqueue-failed", [
							'<p>',
							$this->get_event_link_markup(),
							'</p>',
							'<p>',
							'</p>'
						] );
					}
				}
			}
		}

		// If any error in migration phase, we need to stop and put back in a preview state for the user.
		if ( $did_migration_error ) {
			$this->cancel_migration_with_failure();
		} else {
			$this->check_phase();
		}

		// Do not hold a reference to the Report once the worker is done.
		$event_report       = $this->event_report;
		$this->event_report = null;


		// Log our worker ending
		do_action( 'tribe_log', 'debug', 'Worker: Migrate event:end', [
			'source'       => __CLASS__ . ' ' . __METHOD__ . ' ' . __LINE__,
			'post_id'      => $post_id,
			'next_post_id' => $next_post_id,
			'dry_run'      => $dry_run,
			'event_report' => $event_report,
		] );

		return $event_report;
	}

	/**
	 * Will trigger the migration failure handling.
	 *
	 * @since TBD
	 */
	public function cancel_migration_with_failure() {
		$process = tribe( Process::class );
		$process->cancel_migration_with_failure();
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
		// Log our worker starting
		do_action( 'tribe_log', 'debug', 'Worker: Undo event migration:start', [
			'source' => __CLASS__ . ' ' . __METHOD__ . ' ' . __LINE__,
			'meta'   => $meta,
		] );

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
		$current_phase = $this->state->get_phase();

		// @todo Review - missing anything? Better way?
		do_action( 'tec_events_custom_tables_v1_migration_before_cancel' );

		tribe( Schema_Builder::class )->down();

		// Clear meta values.
		$meta_keys = [
			Event_Report::META_KEY_MIGRATION_LOCK_HASH,
			Event_Report::META_KEY_MIGRATION_PHASE,
		];

		// If we are in migration failure, we want to preserve the report data.
		if ( $current_phase !== State::PHASE_MIGRATION_FAILURE_IN_PROGRESS ) {
			$meta_keys[] = Event_Report::META_KEY_REPORT_DATA;
		}

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

		// Setup success admin notice.
		switch ( $current_phase ) {
			case State::PHASE_CANCEL_IN_PROGRESS:
			case State::PHASE_REVERT_IN_PROGRESS:
				$is_cancel = $current_phase === State::PHASE_CANCEL_IN_PROGRESS;
				$text      = tribe( String_Dictionary::class );
				$notice    = $text->get( $is_cancel ? 'cancel-migration-complete-notice' : 'revert-migration-complete-notice' );

				Tribe__Admin__Notices::instance()->register_transient(
					'admin_notice_undo_migration_complete',
					"<p>$notice</p>",
					[
						'type'      => 'success',
						'dismiss'   => true,
						'recurring' => true,
					],
					MONTH_IN_SECONDS
				);
				break;
		}

		// Which is our next phase?
		switch ( $current_phase ) {
			case State::PHASE_CANCEL_IN_PROGRESS:
				$next_phase = State::PHASE_CANCEL_COMPLETE;
				break;
			case State::PHASE_REVERT_IN_PROGRESS:
				$next_phase = State::PHASE_REVERT_COMPLETE;
				break;
			case State::PHASE_MIGRATION_FAILURE_IN_PROGRESS:
				$next_phase = State::PHASE_MIGRATION_FAILURE_COMPLETE;
				break;
			default:
				// Should not happen.
				// Graceful fallback.
				$next_phase = State::PHASE_PREVIEW_PROMPT;
				do_action( 'tribe_log', 'error', 'Worker: on undo, next phase not mapped.', [
					'source' => __CLASS__ . ' ' . __METHOD__ . ' ' . __LINE__,
					'meta'   => $meta,
					'phase'  => $current_phase,
				] );
				break;
		}

		$this->state->set( 'complete_timestamp', time() );
		$this->state->set( 'phase', $next_phase );
		$this->state->save();

		do_action( 'tec_events_custom_tables_v1_migration_after_cancel' );
		// Log our worker ending
		do_action( 'tribe_log', 'debug', 'Worker: Undo event migration:end', [
			'source' => __CLASS__ . ' ' . __METHOD__ . ' ' . __LINE__,
			'meta'   => $meta,
		] );
	}

	/**
	 * Handles non-fatal errors that might be triggered during the migration.
	 *
	 * @since TBD
	 *
	 * @param int    $errno  The error code.
	 * @param string $errstr The error message.
	 *
	 * @return void The method never returns and will always throw when encountering
	 *              an error during the migration.
	 *
	 * @throws Migration_Exception A reference to an exception wrapping the error.
	 */
	public function error_handler( $errno, $errstr ) {
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
	public function shutdown_handler() {
		// In case we fail above, release transaction.
		if ( $this->dry_run ) {
			$this->transaction_rollback();
		}
		$event_link_markup = $this->get_event_link_markup();

		// If we're here, the migration failed.
		$this->event_report->migration_failed( "unknown-shutdown", [
			'<p>',
			$event_link_markup,
			'</p>',
			'<p>',
			'</p>'
		] );
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
	public function check_phase() {
		do_action( 'tribe_log', 'debug', 'Worker: Migrate event:check_phase', [
			'source' => __CLASS__ . ' ' . __METHOD__ . ' ' . __LINE__,
			'phase'  => $this->state->get_phase(),
		] );

		$phase               = $this->state->get_phase();
		$migration_completed = in_array(
			                       $phase, [
			                       State::PHASE_MIGRATION_IN_PROGRESS,
			                       State::PHASE_PREVIEW_IN_PROGRESS
		                       ], true )
		                       && $this->events->get_total_events_remaining() === 0
		                       && $this->events->get_total_events_in_progress() === 0;

		if ( ! $migration_completed ) {
			do_action( 'tribe_log', 'debug', 'Worker: Migrate event:check_phase', [
				'source' => __CLASS__ . ' ' . __METHOD__ . ' ' . __LINE__,
			] );

			return false;
		}

		$next_phase = $phase === State::PHASE_PREVIEW_IN_PROGRESS ?
			State::PHASE_MIGRATION_PROMPT
			: State::PHASE_MIGRATION_COMPLETE;
		$this->state->set( 'phase', $next_phase );
		$this->state->set( 'migration', 'estimated_time_in_seconds', $this->events->calculate_time_to_completion() );
		$this->state->set( 'complete_timestamp', time() );
		$this->state->save();
		do_action( 'tribe_log', 'debug', 'Worker: Migrate event:check_phase', [
			'source' => __CLASS__ . ' ' . __METHOD__ . ' ' . __LINE__,
			'phase'  => $this->state->get_phase(),
		] );

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

		$event_link_markup = $this->get_event_link_markup();

		/**
		 * Since we're storing output of arbitrary length in the database, let's
		 * trim it down to something that should not go over the `mysql_max_packet`
		 * size.
		 */
		$trimmed_buffer = substr( $buffer, 0, 1024 );

		// If we're here, some code called `die` or `exit`.
		$this->event_report->migration_failed( 'exit', [
			'<p>',
			$event_link_markup,
			$trimmed_buffer,
			'</p>',
			'<p>',
			'</p>'
		] );

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

		// We may already have one scheduled, bail if we do.
		if ( as_has_scheduled_action( self::ACTION_CHECK_PHASE ) ) {
			return 0;
		}

		// Check again.
		return as_enqueue_async_action( self::ACTION_CHECK_PHASE );
	}

	/**
	 * Start a transaction with fallback on no-op queries if not supported.
	 *
	 * @since TBD
	 */
	private function start_transaction() {
		$this->transaction_started = $this->transaction_start();

		if ( ! $this->transaction_started ) {
			// Transactions might be not supported or blocked: do not actually execute queries.
			Builder::class_enable_query_execution( false );
		}
	}

	/**
	 * Rolls back a transaction with fallback on no-op queries if not supported.
	 *
	 * @since TBD
	 */
	private function rollback_transaction() {
		if ( $this->transaction_started ) {
			$this->transaction_rollback();
		} else {
			Builder::class_enable_query_execution( true );
		}
	}
}
