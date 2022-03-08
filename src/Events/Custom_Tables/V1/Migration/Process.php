<?php
/**
 * Handles the background processing the migration will use to migrate
 * events independently of the cron and user intervention.
 *
 * @since   TBD
 * @package TEC\Events\Custom_Tables\V1\Migration;
 */

namespace TEC\Events\Custom_Tables\V1\Migration;

use ActionScheduler;
use TEC\Events\Custom_Tables\V1\Activation;
use TEC\Events\Custom_Tables\V1\Migration\Reports\Event_Report;
use TEC\Events\Custom_Tables\V1\Migration\Strategies\Single_Event_Migration_Strategy;
use TEC\Events\Custom_Tables\V1\Migration\Strategies\Strategy_Interface;
use TEC\Events\Custom_Tables\V1\Tables\Events as EventsSchema;
use TEC\Events\Custom_Tables\V1\Tables\Occurrences as OccurrencesSchema;
use TEC\Events\Custom_Tables\V1\Tables\Provider;

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
	 * A reference to the migration state object.
	 *
	 * @var State;
	 */
	private $state;
	/**
	 * Process constructor.
	 *
	 * @since TBD
	 *
	 * @param Events $events A reference to the current Events' migration repository.
	 * @param State $state A reference to the migration state data.
	 */
	public function __construct( Events $events, State $state ) {
		$this->events = $events;
		$this->state = $state;
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
		// @todo Add error handler and shutdown callback (to catch some of our errors).
		// Get our Event_Report ready for the strategy.
		// This is also used in our error catching, so needs to be defined outside that block.
		$event_report = new Event_Report( get_post( $post_id ) );

		try {
			// Check if we are still in migration phase.
			if ( ! in_array( $this->state->get_phase(), [
				State::PHASE_PREVIEW_IN_PROGRESS,
				State::PHASE_MIGRATION_IN_PROGRESS
			], true ) ) {
				$event_report->migration_failed( 'Canceled.' );

				return $event_report;
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

			$event_report->start_event_migration();

			// Apply strategy, use Event_Report to flag any pertinent details or any failure events.
			$strategy->apply( $event_report );
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
		} catch ( \Throwable $e ) {
			$event_report->migration_failed( $e->getMessage() );
		} catch ( \Exception $e ) {
			$event_report->migration_failed( $e->getMessage() );
		}

		// @todo Remove the error + shutdown hooks

		// Transition phase.
		// @todo This how we want to do this?
		// @todo Doing these State checks here is likely going to slow the processing by an order of magnitude. Better place?
		if ( $this->events->get_total_events_remaining() === 0
		     && $this->state->is_running()
		     && in_array( $this->state->get_phase(), [
				State::PHASE_MIGRATION_IN_PROGRESS,
				State::PHASE_PREVIEW_IN_PROGRESS
			] ) ) {
			$this->state->set( 'phase', $dry_run ? State::PHASE_MIGRATION_PROMPT : State::PHASE_MIGRATION_COMPLETE );
			$this->state->set( 'migration', 'estimated_time_in_seconds', $this->events->calculate_time_to_completion() );
			$this->state->set( 'complete_timestamp', time() );
			$this->state->save();
		}

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
		global $wpdb;

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
		// @todo Move this to a centralized rollback (in the schema objects, with hooks?)
		// @todo Review - missing anything? Better way?
		do_action( 'tec_events_custom_tables_v1_migration_before_cancel' );

		tribe( Provider::class )->drop_tables();

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

		// @todo This will just cause the tables to be recreated - we need something to handle create/destroy operations properly...
		delete_transient( Activation::ACTIVATION_TRANSIENT );

		$this->state->set( 'phase', State::PHASE_MIGRATION_PROMPT );
		$this->state->save();

		do_action( 'tec_events_custom_tables_v1_migration_after_cancel' );
	}

	/**
	 * Starts the migration enqueueing the first set of Events to process.
	 *
	 * @since TBD
	 *
	 * @param bool $dry_run Whether to do a preview or finalize the migration operations.
	 *
	 * @return int|false The number of Events queued for migration or false if migration already started.
	 */
	public function start( $dry_run = true ) {
		// Check if we are already doing this action?
		if ( in_array( $this->state->get_phase(), [
			State::PHASE_PREVIEW_IN_PROGRESS,
			State::PHASE_MIGRATION_IN_PROGRESS
		] ) ) {
			return false;
		}

		$action_ids = [];

		// Remove what migration phase flags might have been set by previous previews or migrations.
		delete_metadata( 'post', 0, Event_Report::META_KEY_MIGRATION_PHASE, '', true );
		delete_metadata( 'post', 0, Event_Report::META_KEY_REPORT_DATA, '', true );

		// Flag our new phase.
		$this->state->set( 'phase', $dry_run ? State::PHASE_PREVIEW_IN_PROGRESS : State::PHASE_MIGRATION_IN_PROGRESS );
		$this->state->save();

		foreach ( $this->events->get_ids_to_process( 50 ) as $post_id ) {
			$action_ids[] = as_enqueue_async_action( self::ACTION_PROCESS, [ $post_id, $dry_run ] );
		}

		return count( array_filter( $action_ids ) );
	}

	/**
	 * Clean up when a queued migration worker is canceled.
	 *
	 * @since TBD
	 *
	 * @param $action_id numeric The action scheduler action ID
	 */
	public function cancel_async_action( $action_id ) {
		$store  = ActionScheduler::store();
		$action = $store->fetch_action( $action_id );
		if ( $action->get_hook() !== self::ACTION_PROCESS ) {
			return;
		}
		$args    = $action->get_args();
		$post_id = $args[0];
		// Clear our migration state metadata so we are freed up for other operations.
		$event_report = new Event_Report( get_post( $post_id ) );
		$event_report->clear_meta();
	}

	/**
	 * Clean up when our queued migration workers are canceled.
	 *
	 * @since TBD
	 *
	 * @param array $action_ids List of action IDs.
	 */
	public function cancel_async_actions( array $action_ids ) {
		foreach ( $action_ids as $action_id ) {
			$this->cancel_async_action( $action_id );
		}
	}

	/**
	 * Starts the migration undoing process.
	 *
	 * @since TBD
	 *
	 * @return boolean False if undo already started.
	 */
	public function undo() {
		// Check if we are already doing this action?
		if ( $this->state->get_phase() === State::PHASE_UNDO_IN_PROGRESS ) {
			return false;
		}

		// Flag our new phase.
		$this->state->set( 'phase', State::PHASE_UNDO_IN_PROGRESS );
		$this->state->save();

		// Clear all of our queued migration workers.
		as_unschedule_all_actions( self::ACTION_PROCESS );

		// Now queue our undo loop.
		as_enqueue_async_action( self::ACTION_UNDO );

		return true;
	}
}