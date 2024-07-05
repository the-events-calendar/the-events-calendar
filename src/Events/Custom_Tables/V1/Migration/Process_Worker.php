<?php
/**
 * Does the migration and undo operations.
 *
 * @since   6.0.0
 * @package TEC\Events\Custom_Tables\V1\Migration;
 */

namespace TEC\Events\Custom_Tables\V1\Migration;

use ActionScheduler_Store;
use TEC\Events\Custom_Tables\V1\Migration\Reports\Event_Report;
use TEC\Events\Custom_Tables\V1\Migration\Strategies\Single_Event_Migration_Strategy;
use TEC\Events\Custom_Tables\V1\Migration\Strategies\Strategy_Interface;
use TEC\Events\Custom_Tables\V1\Models\Builder;
use TEC\Events\Custom_Tables\V1\Schema_Builder\Schema_Builder;
use TEC\Events\Custom_Tables\V1\Traits\With_Database_Transactions;
use TEC\Events\Custom_Tables\V1\Traits\With_String_Dictionary;
use Tribe__Admin__Notices;
use Tribe__Date_Utils as Dates;
use Tribe__Timezones as Timezones;

/**
 * Class Process_Worker. Handles the migration and undo operations.
 *
 * @since   6.0.0
 * @package TEC\Events\Custom_Tables\V1\Migration;
 */
class Process_Worker {
	use With_Database_Transactions;
	use With_String_Dictionary;

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
	 * @since 6.0.0
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
	 * @since 6.0.0
	 *
	 * @var Event_Report|null
	 */
	private $event_report;

	/**
	 * A flag property used to keep track of whether a started
	 * migration completed or not.
	 *
	 * @since 6.0.0
	 *
	 * @var bool|null
	 */
	private $migration_completed;

	/**
	 * Whether the current event migration is running in dry-run mode or not.
	 *
	 * @since 6.0.0
	 *
	 * @var bool|null
	 */
	private $dry_run;

	/**
	 * Process_Worker constructor.
	 *
	 * @since 6.0.0
	 *
	 * @param Events $events A reference to the current Events' migration repository.
	 * @param State  $state  A reference to the migration state data.
	 */
	public function __construct( Events $events, State $state ) {
		$this->events = $events;
		$this->state = $state;
	}

	/**
	 * Sets up our shutdown and buffer handlers.
	 *
	 * @since 6.0.0
	 */
	private function bind_shutdown_handlers() {
		// Watch for any errors that may occur and store them in the Event_Report.
		set_error_handler( [ $this, 'error_handler' ], E_WARNING | E_NOTICE | E_USER_WARNING | E_USER_NOTICE );

		// Set this as a fallback: we'll remove it later if everything goes fine.
		add_action( 'shutdown', [ $this, 'shutdown_handler' ] );

		/*
		 * If some calls `die` or `exit` during the migration PHP might not trigger
		 * shutdown. For that purpose let's buffer and try to capture the event and
		 * the reason for it.
		 */
		ob_start( [ $this, 'ob_flush_handler' ] );
	}

	/**
	 * Reverts and removes any shutdown or output buffer handlers we opened.
	 *
	 * @since 6.0.0
	 */
	private function unbind_shutdown_handlers() {
		// Restore error handling.
		restore_error_handler();
		// Remove shutdown hook.
		remove_action( 'shutdown', [ $this, 'shutdown_handler' ] );
		// Close the output buffer.
		ob_end_clean();
	}

	/**
	 * Any actions to be run immediately before a dry run migration will be applied.
	 *
	 * @since 6.0.0
	 *
	 * @param numeric $post_id
	 */
	public function before_dry_run( $post_id ) {
		$this->start_transaction();
	}

	/**
	 * Any actions to be run immediately after a dry run migration was applied.
	 *
	 * @since 6.0.0
	 *
	 * @param numeric $post_id
	 */
	public function after_dry_run( $post_id ) {
		$this->rollback_transaction();

		if ( wp_cache_get( $post_id, 'posts' ) ) {
			$this->add_cache_compatibility_hooks();
			/*
			 * Our event report state would have been rolled back too, so try and reapply what was set locally.
			 * Clear our cache, since it reflects local state and not aware of transaction rollbacks.
			 */
			try {
				clean_post_cache( $post_id );
			} catch ( \Exception $e ) {
				// Some plugin intervening in the caching system did something wrong: we did what we could.
			}
			$this->remove_cache_compatibility_hooks();
		}

		/**
		 * Fires after a dry run migration was applied.
		 *
		 * @since 6.3.0
		 *
		 * @param numeric $post_id The ID of the Event that was migrated.
		 */
		do_action( 'tec_events_custom_tables_v1_migration_after_dry_run', $post_id );
	}

	/**
	 * Add hooks to handle cache issues when we are clearing post cache during migration.
	 *
	 * @since 6.0.0
	 */
	public function add_cache_compatibility_hooks() {
		add_filter( 'wpsc_delete_related_pages_on_edit', [ $this, 'wpsc_delete_related_pages_on_edit' ], 10, 1 );
	}

	/**
	 * Remove hooks to handle cache issues when we are clearing post cache during migration.
	 *
	 * @since 6.0.0
	 */
	public function remove_cache_compatibility_hooks() {
		remove_filter( 'wpsc_delete_related_pages_on_edit', [ $this, 'wpsc_delete_related_pages_on_edit' ], 10 );
	}

	/**
	 * Skips some cache actions that fail in our cleanup of post cache.
	 *
	 * @since 6.0.0
	 *
	 * @param mixed $all
	 *
	 * @return false
	 */
	public function wpsc_delete_related_pages_on_edit( $all ) {
		return false;
	}

	/**
	 * Processes an Event migration.
	 *
	 * @since 6.0.0
	 *
	 * @param int  $post_id The post ID of the Event to migrate.
	 * @param bool $dry_run Whether the migration should commit or just preview
	 *                      the changes.
	 *
	 * @return Event_Report A reference to the migration report object produced by the
	 *                      migration.
	 */
	public function migrate_event( int $post_id, bool $dry_run = false ): ?Event_Report {
		global $wpdb;

		// Log our worker starting
		do_action( 'tribe_log', 'debug', 'Worker: Migrate event:start', [
			'source'  => __CLASS__ . ' ' . __METHOD__ . ' ' . __LINE__,
			'post_id' => $post_id,
			'dry_run' => $dry_run
		] );

		$this->dry_run = $dry_run;

		/*
		 * Get our Event_Report ready for the strategy.
		 * This is also used in our error catching, so needs to be defined outside that block.
		 */
		$this->event_report = new Event_Report( get_post( $post_id ) );

		if ( $this->check_phase() ) {
			// We're done, the migration is complete and there is no more work to do.
			return $this->event_report;
		}

		// Set our dead-man switch.
		$this->migration_completed = false;

		$this->bind_shutdown_handlers();

		try {
			// Before we start preview, check if transactions are supported.
			// If not, we want to stop gracefully and still allow migration to continue.
			if ( $this->dry_run && ! $this->transactions_supported( $wpdb->prefix ) ) {
				// Clear all our workers, we don't need to check anymore for preview.
				tribe( Process::class )->empty_process_queue();

				// Move us to the next phase - there will be a special message on that phase noting what happened.
				$this->state->set( 'phase', State::PHASE_MIGRATION_PROMPT );
				$this->state->set( 'preview_unsupported', true );
				$this->state->save();
				$this->migration_completed = true;
				$this->unbind_shutdown_handlers();

				return $this->event_report->migration_success();
			}
			// In the odd scenario where we previously had a transaction failure, but it was resolved later.
			$this->state->set( 'preview_unsupported', false );
			$this->state->save();

			// Check if we are still in migration phase.
			if ( ! in_array( $this->state->get_phase(), [
				State::PHASE_PREVIEW_IN_PROGRESS,
				State::PHASE_MIGRATION_IN_PROGRESS
			], true ) ) {
				$this->event_report->migration_failed( 'canceled', [
					$this->get_event_link_markup( $this->event_report->source_event_post->ID )
				] );
				$this->migration_completed = true;
				$this->unbind_shutdown_handlers();

				return $this->event_report;
			}

			/**
			 * Filters the migration strategy that should be used to migrate an Event.
			 * Returning an object implementing the TEC\Events\Custom_Tables\V1\Migration\Strategy_Interface
			 * here will prevent TEC from using the default one.
			 *
			 * @since 6.0.0
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

			// Set a strategy slug immediately; the strategy can refine it later.
			$strategy_class = get_class( $strategy );
			$this->event_report->add_strategy( $strategy_class::get_slug() );

			$this->event_report->start_event_migration();

			// In case we have an error in the strategy, and we are forced to exit early, lets start the transaction here.
			if ( $this->dry_run ) {
				$this->before_dry_run( $post_id );
			}

			$this->fix_event_meta( $post_id );

			/**
			 * Action to be fired immediately prior to applying migration strategy. Some migrations may still fail after this phase,
			 * as there are various factors internal to the strategy that could cancel this migration.
			 *
			 * @since 6.0.0
			 *
			 * @param Event_Report       $event_report The event report for this migration.
			 * @param Strategy_Interface $strategy     The strategy being applied to this post.
			 * @param numeric            $post_id      The post ID we are attempting to apply the migration to.
			 * @param bool               $dry_run      Whether this is a dry run (preview) or a final migration being applied.
			 */
			do_action( 'tec_events_custom_tables_v1_before_migration_applied', $this->event_report, $strategy, $post_id, $dry_run );

			// Apply strategy, use Event_Report to flag any pertinent details or any failure events.
			$strategy->apply( $this->event_report );

			if ( $this->dry_run ) {
				$this->after_dry_run( $post_id );
			} else {
				$this->transaction_commit();
			}

			// If no error, mark successful.
			if ( ! $this->event_report->error ) {
				$this->event_report->migration_success();
			}
		} catch ( Expected_Migration_Exception $e ) {
			if ( $this->dry_run ) {
				$this->after_dry_run( $post_id );
			}
			$this->event_report->migration_failed( 'expected-exception', [
				$e->getMessage(),
			] );
		} catch ( \Throwable $e ) {
			// In case we fail above, release transaction.
			if ( $this->dry_run ) {
				$this->after_dry_run( $post_id );
			}
			$this->event_report->migration_failed( 'exception', [
				$this->get_event_link_markup( $this->event_report->source_event_post->ID ),
				$e->getMessage(),
				'<a target="_blank" href="https://evnt.is/migrationhelp">',
				'</a>'
			] );

			// @todo Remove this. Useful for troubleshooting
			do_action( 'tribe_log', 'debug', 'Migration unexpected exception:', [
				'source' => __CLASS__ . ' ' . __METHOD__ . ' ' . __LINE__,
				'trace'  => $e->getTraceAsString()
			] );
		} catch ( \Exception $e ) {
			// In case we fail above, release transaction.
			if ( $this->dry_run ) {
				$this->after_dry_run( $post_id );
			}

			$this->event_report->migration_failed( 'exception', [
				$this->get_event_link_markup( $this->event_report->source_event_post->ID ),
				$e->getMessage(),
				'<a target="_blank" href="https://evnt.is/migrationhelp">',
				'</a>'
			] );
			// @todo Remove this. Useful for troubleshooting
			do_action( 'tribe_log', 'debug', 'Migration unexpected exception:', [
				'source' => __CLASS__ . ' ' . __METHOD__ . ' ' . __LINE__,
				'trace'  => $e->getTraceAsString()
			] );
		}

		$this->migration_completed = true;

		$this->unbind_shutdown_handlers();

		// Flag to fail on first error.
		$fail_on_first_error = (
			                       defined( 'TEC_EVENTS_CUSTOM_TABLES_V1_MIGRATION_STOP_ON_FAILURE' )
			                       && TEC_EVENTS_CUSTOM_TABLES_V1_MIGRATION_STOP_ON_FAILURE
		                       )
		                       || ! $dry_run;
		/**
		 * Filter to determine whether we should stop on first failure or not. Useful for troubleshooting in preview mode.
		 * @since 6.0.1
		 *
		 * @param bool $fail_on_first_error
		 *
		 * @returns bool Whether we should stop on first failure or not.
		 */
		$fail_on_first_error = apply_filters( 'tec_events_custom_tables_v1_migration_should_stop_on_failure', $fail_on_first_error );

		// If error in the migration phase or fail on first error flag, then we need to stop the queue.
		$did_migration_error = ( $fail_on_first_error && $this->event_report->error );
		$continue_queue = $did_migration_error ? false : true;
		$next_post_id = null;

		if ( $continue_queue ) {
			// Get next event to process.
			$next_post_id = $this->events->get_id_to_process();

			if ( $next_post_id ) {
				// Enqueue a new (Action Scheduler) action to import another Event.
				$action_id = as_enqueue_async_action( self::ACTION_PROCESS, [ $next_post_id, $dry_run ] );

				if ( empty( $action_id ) ) {
					// If we cannot migrate the next Event we need to migrate, then the migration has failed.
					$this->event_report->migration_failed( "enqueue-failed", [
						$this->get_event_link_markup( $this->event_report->source_event_post->ID ),
						$next_post_id,
					] );
				}
			}

			// Start a recursive check, but only if we are not already doing so.
			if ( ! as_has_scheduled_action( self::ACTION_CHECK_PHASE ) ) {
				$action_id = as_enqueue_async_action( self::ACTION_CHECK_PHASE );
				if ( empty( $action_id ) ) {
					// The migration might have technically completed, but we cannot know for sure and will be conservative.
					$this->event_report->migration_failed( "check-phase-enqueue-failed", [
						$this->get_event_link_markup( $this->event_report->source_event_post->ID ),
					] );
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
		$event_report = $this->event_report;
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
	 * @since 6.0.0
	 */
	public function cancel_migration_with_failure() {
		$process = tribe( Process::class );
		$process->cancel_migration_with_failure();
	}

	/**
	 * Undoes an Event migration.
	 *
	 * @since 6.0.0
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

		$seconds_to_wait = 60 * 5; // 5 minutes
		$max_time_reached = ( time() - $meta['started_timestamp'] ) > $seconds_to_wait;

		// Are we still processing some events? If so, recurse and wait to do the undo operation.
		if ( ! $max_time_reached && $this->events->get_total_events_in_progress() ) {
			as_enqueue_async_action( self::ACTION_UNDO, [ $meta ] );

			return;
		}
		$current_phase = $this->state->get_phase();

		/*
		 * Fires before the migration revert/cancellation starts.
		 *
		 * @since 6.0.0
		 */
		do_action( 'tec_events_custom_tables_v1_migration_before_cancel' );

		tribe( Schema_Builder::class )->down();

		// Clear meta values.
		$meta_keys = [
			Event_Report::META_KEY_MIGRATION_LOCK_HASH,
		];

		// If we are in migration failure, we want to preserve the report data.
		if ( $current_phase !== State::PHASE_MIGRATION_FAILURE_IN_PROGRESS ) {
			$meta_keys[] = Event_Report::META_KEY_REPORT_DATA;
			$meta_keys[] = Event_Report::META_KEY_MIGRATION_PHASE;
			$meta_keys[] = Event_Report::META_KEY_MIGRATION_CATEGORY;
		}

		/**
		 * Filters the list of post meta keys to be removed during a migration cancel.
		 *
		 * @since 6.0.0
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
				$text = tribe( String_Dictionary::class );
				$notice = $text->get( $is_cancel ? 'cancel-migration-complete-notice' : 'revert-migration-complete-notice' );

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

		/**
		 * Fires after the migration revert/cancellation has completed.
		 *
		 * @since 6.0.0
		 */
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
	 * @since 6.0.0
	 *
	 * @param int    $errno   The error code.
	 * @param string $errstr  The error message.
	 * @param string $errfile The file the error occurred in.
	 *
	 * @return bool A value indicating whether the error handler handled the error or not..
	 *
	 * @throws Migration_Exception A reference to an exception wrapping the error.
	 */
	public function error_handler( int $errno, string $errstr, string $errfile ): bool {
		$check_plugins = [ basename( TRIBE_EVENTS_FILE ) ];

		if ( defined( 'EVENTS_CALENDAR_PRO_FILE' ) ) {
			$check_plugins[] = basename( EVENTS_CALENDAR_PRO_FILE );
		}

		if ( ! tec_is_file_from_plugins( $errfile, ...$check_plugins ) ) {
			// Do not handle Warnings when coming from outside TEC or ECP codebase (e.g. caching plugins).
			return false;
		}

		// Delegate to our try/catch handler.
		throw new Migration_Exception( $errstr, $errno );
	}

	/**
	 * Hooked to the WordPress `shutdown` hook.
	 * This method should be removed during a successful migration
	 * or one that is properly handled. If not, then this method is
	 * an attempt to log the failure.
	 *
	 * @since 6.0.0
	 */
	public function shutdown_handler() {
		// In case we fail above, release transaction.
		if ( $this->dry_run ) {
			$this->transaction_rollback();
		}
		$event_link_markup = $this->get_event_link_markup( $this->event_report->source_event_post->ID );

		// If we're here, the migration failed.
		$this->event_report->migration_failed( "unknown-shutdown", [
			$event_link_markup,
			'<a target="_blank" href="https://evnt.is/migrationhelp">',
			'</a>'
		] );
	}

	/**
	 * Checks and updates the migration phase depending on the current status of the database.
	 *
	 * This is an idem-potent method that will only ste the migration state to done
	 * when done; two or more concurrent workers doing the same will not break the
	 * logic.
	 *
	 * @since 6.0.0
	 *
	 * @return bool Whether the migration, or its preview, is completed or not.
	 */
	public function check_phase() {
		$state = tribe( State::class );
		do_action( 'tribe_log', 'debug', 'Worker: Migrate event:check_phase', [
			'source' => __CLASS__ . ' ' . __METHOD__ . ' ' . __LINE__,
			'phase'  => $state->get_phase(),
		] );

		$phase = $state->get_phase();
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
		$state->set( 'phase', $next_phase );
		$state->set( 'migration', 'estimated_time_in_seconds', $this->events->calculate_time_to_completion() );
		$state->set( 'complete_timestamp', time() );
		$state->save();

		/**
		 * Triggers an action on the end of the Migration.
		 *
		 * @since 6.0.0
		 *
		 * @param bool $dry_run Whether the migration just completed is a dry-run one or not.
		 */
		do_action( 'tec_events_custom_tables_v1_migration_completed', $this->dry_run );

		do_action( 'tribe_log', 'debug', 'Worker: Migrate event:check_phase', [
			'source' => __CLASS__ . ' ' . __METHOD__ . ' ' . __LINE__,
			'phase'  => $state->get_phase(),
		] );

		return true;
	}

	/**
	 * Hooked to the `ob_start` function, this method will run consistently
	 * across PHP versions when the `die` or `exit` function is called during
	 * the migration process.
	 *
	 * @since 6.0.0
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

		$event_link_markup = $this->get_event_link_markup( $this->event_report->source_event_post->ID );

		/**
		 * Since we're storing output of arbitrary length in the database, let's
		 * trim it down to something that should not go over the `mysql_max_packet`
		 * size.
		 */
		$trimmed_buffer = substr( $buffer, 0, 1024 );

		// If we're here, some code called `die` or `exit`.
		$this->event_report->migration_failed( 'exit', [
			$event_link_markup,
			$trimmed_buffer,
			'<a target="_blank" href="https://evnt.is/migrationhelp">',
			'</a>'
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
	 * @since 6.0.0
	 *
	 * @return int The ID of the new Action scheduled to check
	 *             on the migration phase, `0` if no new Action
	 *             was queued.
	 */
	public function check_phase_complete() {
		$completed = ! $this->state->is_running() || $this->check_phase();

		do_action( 'tribe_log', 'debug', 'Worker: Check event:check_phase_complete', [
			'source'    => __CLASS__ . ' ' . __METHOD__ . ' ' . __LINE__,
			'phase'     => $this->state->get_phase(),
			'completed' => $completed
		] );

		if ( $completed ) {
			do_action( 'tribe_log', 'debug', 'Worker: Check event:check_phase_complete', [
				'source' => __CLASS__ . ' ' . __METHOD__ . ' ' . __LINE__,
			] );
			// Clear all of our queued state check workers.
			as_unschedule_all_actions( self::ACTION_CHECK_PHASE );

			return 0;
		}

		// We may already have one scheduled, bail if we do.
		if ( as_has_scheduled_action( self::ACTION_CHECK_PHASE ) ) {
			do_action( 'tribe_log', 'debug', 'Worker: Check event:check_phase_complete', [
				'source' => __CLASS__ . ' ' . __METHOD__ . ' ' . __LINE__,
			] );

			return 0;
		}

		// Check again.
		return as_enqueue_async_action( self::ACTION_CHECK_PHASE );
	}

	/**
	 * Start a transaction with fallback on no-op queries if not supported.
	 *
	 * @since 6.0.0
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
	 * @since 6.0.0
	 */
	private function rollback_transaction() {
		if ( $this->transaction_started ) {
			$this->transaction_rollback();
		} else {
			Builder::class_enable_query_execution( true );
		}
	}

	/**
	 * Updates the Event date and duration meta to make sure it's consistent.
	 *
	 * @since 6.0.1
	 *
	 * @param int $post_id The ID of the Event to update.
	 *
	 * @return void Updates the Event date and duration meta to make sure it's consistent.
	 *
	 * @throws Migration_Exception If the Event date and duration meta could not be updated.
	 */
	private function fix_event_meta( int $post_id ): void {
		/**
		 * Filters whether an Event date related meta should be fixed before migration or not.
		 *
		 * @since 6.0.0
		 *
		 * @param bool $fix_event_duration Whether the Event date related meta should be fixed before migration or not.
		 * @param int  $post_id            The ID of the post being migrated.
		 */
		$should_fix_meta = apply_filters( 'tec_events_custom_tables_v1_migration_fix_event_date_meta', true, $post_id );

		if ( ! $should_fix_meta ) {
			return;
		}

		// At this stage, we can be sure the meta will be there.
		$start_date = get_post_meta( $post_id, '_EventStartDate', true );
		$end_date = get_post_meta( $post_id, '_EventEndDate', true );
		$start_date_utc = get_post_meta( $post_id, '_EventStartDateUTC', true );
		$end_date_utc = get_post_meta( $post_id, '_EventEndDateUTC', true );

		$has_start = ! empty( $start_date ) || ! empty( $start_date_utc );
		$has_end = ! empty( $end_date ) || ! empty( $end_date_utc );

		if ( ! ( $has_start && $has_end ) ) {
			throw new Migration_Exception(
				'Required Event date data is missing: check the event for missing or invalid data in the start and end date fields.'
			);
		}

		$timezone_string = get_post_meta( $post_id, '_EventTimezone', true );

		if ( ! Timezones::is_valid_timezone( $timezone_string ) ) {
			// Use the site one, if not set.
			$timezone_string = Timezones::build_timezone_object()->getName();
			update_post_meta( $post_id, '_EventTimezone', $timezone_string );
			update_post_meta( $post_id, '_EventTimezoneAbbr', Timezones::abbr( $start_date, $timezone_string ) );
		}

		$timezone = Timezones::build_timezone_object( $timezone_string );
		$utc = new \DateTimeZone( 'UTC' );

		$dtstart = $start_date ?
			Dates::immutable( $start_date, $timezone )
			: Dates::immutable( $start_date_utc, $utc )->setTimezone( $timezone );

		$dtend = $end_date ?
			Dates::immutable( $end_date, $timezone )
			: Dates::immutable( $end_date_utc, $utc )->setTimezone( $timezone );

		$updated_duration = $dtend->getTimestamp() - $dtstart->getTimestamp();
		$event_start_date = $dtstart->format( Dates::DBDATETIMEFORMAT );
		$event_end_date = $dtend->format( Dates::DBDATETIMEFORMAT );
		$event_start_date_utc = $dtstart->setTimezone( $utc )->format( Dates::DBDATETIMEFORMAT );
		$event_end_date_utc = $dtend->setTimezone( $utc )->format( Dates::DBDATETIMEFORMAT );

		update_post_meta( $post_id, '_EventDuration', $updated_duration );
		update_post_meta( $post_id, '_EventStartDate', $event_start_date );
		update_post_meta( $post_id, '_EventEndDate', $event_end_date );
		update_post_meta( $post_id, '_EventStartDateUTC', $event_start_date_utc );
		update_post_meta( $post_id, '_EventEndDateUTC', $event_end_date_utc );
	}

	/**
	 * Migrates up to a number of not yet migrated Events.
	 *
	 * @since 6.0.2
	 *
	 * @param int $count The number of Events to migrate, at the most.
	 *
	 * @return int The number of migrated Events.
	 */
	public function migrate_many_events( int $count ): int {
		if ( $count <= 0 ) {
			return 0;
		}

		$free_ids = $this->events->get_ids_to_process( $count );
		$dry_run = $this->state->is_dry_run();
		$migrated = 0;

		if ( count( $free_ids ) > 0 ) {
			// We might have less free ids than we want but have some, roll with it.
			foreach ( $free_ids as $post_id ) {
				$report = $this->migrate_event( $post_id, $dry_run );
				$migrated ++;

				if ( ! ( $report instanceof Event_Report && $report->status === 'success' ) ) {
					// We have an error, stop here.
					break;
				}
			}

			return $migrated;
		}

		// We have no free ids, let's see if we can grab some from the Action Scheduler actions.
		$actions = as_get_scheduled_actions( [
			'hook'     => self::ACTION_PROCESS,
			'status'   => ActionScheduler_Store::STATUS_PENDING,
			'per_page' => $count,
		] );

		if ( count( $actions ) === 0 ) {
			// We have no pending actions, we're done.
			return $migrated;
		}

		/**
		 * We don't want to trigger cancellation steps - we are still processing, just taking out of queue.
		 */
		remove_action( 'action_scheduler_canceled_action', [ tribe( Provider::class ), 'cancel_async_action' ] );

		/** @var \ActionScheduler_Action $action */
		foreach ( $actions as $action ) {
			// Unschedule a pending action to migrate the Event now.
			$hook = $action->get_hook();
			$args = $action->get_args();
			$group = $action->get_group();

			$unscheduled = as_unschedule_action( $hook, $args, $group );

			if ( empty( $unscheduled ) ) {
				// The action might have been executed in the meantime, skip it.
				continue;
			}

			$report = $this->migrate_event( ...$args );
			$migrated ++;

			if ( ! ( $report instanceof Event_Report && $report->status === 'success' ) ) {
				// We have an error, stop here.
				break;
			}
		}

		return $migrated;
	}
}
