<?php
/**
 * Handles the background processing the migration will use to migrate
 * events independently of the cron and user intervention.
 *
 * @since   6.0.0
 * @package TEC\Events\Custom_Tables\V1\Migration;
 */

namespace TEC\Events\Custom_Tables\V1\Migration;

use ActionScheduler;
use TEC\Events\Custom_Tables\V1\Activation;
use TEC\Events\Custom_Tables\V1\Migration\Reports\Event_Report;

/**
 * Class Process. Responsible for overseeing some phase management, and delegating workers.
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Migration;
 */
class Process {

	/**
	 * The meta key that will be used to flag an Event as migrated during the migration process.
	 *
	 * @since 6.0.0
	 */
	const EVENT_CREATED_BY_MIGRATION_META_KEY = '_tec_event_created_by_migration';

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
	 * Process constructor.
	 *
	 * @since 6.0.0
	 *
	 * @param Events $events A reference to the current Events' migration repository.
	 * @param State  $state  A reference to the migration state data.
	 */
	public function __construct( Events $events, State $state ) {
		$this->events = $events;
		$this->state  = $state;
	}

	/**
	 * Starts the migration enqueueing the first set of Events to process.
	 *
	 * @since 6.0.0
	 *
	 * @param bool $dry_run Whether to do a preview or finalize the migration operations.
	 *
	 * @return int|false The number of Events queued for migration or false if migration already started.
	 */
	public function start( $dry_run = true ) {
		// Check if we are already doing this action?
		if ( in_array( $this->state->get_phase(), [
			State::PHASE_MIGRATION_IN_PROGRESS
		] ) ) {
			return false;
		}
		// Reset our undo state.
		if ( $this->state->get( 'locked_by_undo' ) ) {
			$this->state->set( 'locked_by_undo', null );
			$this->state->save();
		}
		// Ensure we have our database setup.
		Activation::activate();

		// Ensure Action Scheduler tables are there.
		ActionScheduler::store()->init();

		$action_ids = [];
		$this->remove_migration_report_meta();

		// Flag our new phase.
		$this->state->set( 'phase', $dry_run ? State::PHASE_PREVIEW_IN_PROGRESS : State::PHASE_MIGRATION_IN_PROGRESS );
		$this->state->set( 'started_timestamp', time() );
		$this->state->save();

		foreach ( $this->events->get_ids_to_process( 50 ) as $post_id ) {
			$action_ids[] = as_enqueue_async_action( Process_Worker::ACTION_PROCESS, [ $post_id, $dry_run ] );
		}

		return count( array_filter( $action_ids ) );
	}

	/**
	 * Starts the cancel migration process.
	 *
	 * @since 6.0.0
	 *
	 * @return boolean False if undo blocked.
	 */
	public function cancel() {
		// Check if we are already doing this action?
		if ( $this->state->get_phase() === State::PHASE_CANCEL_IN_PROGRESS ) {
			return false;
		}
		// Check if we are allowed.
		if ( ! $this->state->should_allow_reverse_migration() ) {
			return false;
		}

		// Flag our new phase.
		$this->state->set( 'phase', State::PHASE_CANCEL_IN_PROGRESS );
		$this->state->set( 'locked_by_undo', true );
		$this->state->save();

		// Ensure Action Scheduler tables are there.
		$this->undo();

		return true;
	}

	/**
	 * Starts the revert migration process.
	 *
	 * @since 6.0.0
	 *
	 * @return boolean False if undo blocked.
	 */
	public function revert() {
		// Check if we are already doing this action?
		if ( $this->state->get_phase() === State::PHASE_REVERT_IN_PROGRESS ) {
			return false;
		}
		// Check if we are allowed.
		if ( ! $this->state->should_allow_reverse_migration() ) {
			return false;
		}

		// Flag our new phase.
		$this->state->set( 'phase', State::PHASE_REVERT_IN_PROGRESS );
		$this->state->set( 'locked_by_undo', true );
		$this->state->save();

		$this->undo();

		return true;
	}

	/**
	 * When doing a migration failure cleanup, handle the appropriate steps.
	 *
	 * @since 6.0.0
	 *
	 * @return boolean False if undo blocked.
	 */
	public function cancel_migration_with_failure() {
		if ( $this->state->get_phase() === State::PHASE_MIGRATION_FAILURE_IN_PROGRESS ) {
			return false;
		}

		// Flag our new phase.
		$this->state->set( 'phase', State::PHASE_MIGRATION_FAILURE_IN_PROGRESS );
		$this->state->set( 'locked_by_undo', true );
		$this->state->save();

		$this->undo();

		return true;
	}

	/**
	 * Starts the migration undoing process.
	 *
	 * @since 6.0.0
	 *
	 */
	protected function undo() {
		// Ensure Action Scheduler tables are there.
		ActionScheduler::store()->init();

		// Clear all of our queued workers.
		$this->empty_process_queue();

		// Now queue our undo loop.
		as_enqueue_async_action( Process_Worker::ACTION_UNDO );
	}

	/**
	 * Unschedules all of our process workers in the Action Schedule queue.
	 *
	 * @since 6.0.0
	 */
	public function empty_process_queue() {
		// Clear all of our queued migration workers.
		as_unschedule_all_actions( Process_Worker::ACTION_PROCESS );

		// Clear all of our queued state check workers.
		as_unschedule_all_actions( Process_Worker::ACTION_CHECK_PHASE );

		// Clear all of our queued undo workers.
		as_unschedule_all_actions( Process_Worker::ACTION_UNDO );
	}

	/**
	 * Clean up when a queued migration worker is canceled.
	 *
	 * @since 6.0.0
	 *
	 * @param $action_id numeric The action scheduler action ID
	 */
	public function cancel_async_action( $action_id ) {
		$store  = ActionScheduler::store();
		$action = $store->fetch_action( $action_id );
		if ( $action->get_hook() !== Process_Worker::ACTION_PROCESS ) {
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
	 * @since 6.0.0
	 *
	 * @param array $action_ids List of action IDs.
	 */
	public function cancel_async_actions( array $action_ids ) {
		foreach ( $action_ids as $action_id ) {
			$this->cancel_async_action( $action_id );
		}
	}

	/**
	 * Remove the migration report meta from all events.
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function remove_migration_report_meta(): void {
		// Remove what migration phase flags might have been set by previous previews or migrations.
		delete_metadata( 'post', 0, Event_Report::META_KEY_MIGRATION_PHASE, '', true );
		delete_metadata( 'post', 0, Event_Report::META_KEY_REPORT_DATA, '', true );
		delete_metadata( 'post', 0, Event_Report::META_KEY_MIGRATION_LOCK_HASH, '', true );
	}
}