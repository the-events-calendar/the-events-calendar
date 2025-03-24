<?php
/**
 * Abstract base class for all migration scheduler actions.
 * Provides common functionality and interfaces for all migration steps.
 *
 * @since   TBD
 *
 * @package TEC\Events\Category_Colors\Migration\Scheduler
 */

namespace TEC\Events\Category_Colors\Migration\Scheduler;

use ActionScheduler_Action;
use TEC\Events\Category_Colors\Migration\Abstract_Migration_Step;
use TEC\Events\Category_Colors\Migration\Config;
use TEC\Events\Category_Colors\Migration\Handler;
use TEC\Events\Category_Colors\Migration\Status;
use WP_Error;

/**
 * Abstract base class for all migration scheduler actions.
 *
 * @since   TBD
 *
 * @package TEC\Events\Category_Colors\Migration\Scheduler
 */
abstract class Abstract_Action extends Abstract_Migration_Step implements Action_Interface {

	/**
	 * The Action Scheduler action ID.
	 *
	 * @since TBD
	 * @var int|null
	 */
	protected ?int $action_id = null;

	/**
	 * The hook name for this action.
	 *
	 * @since TBD
	 * @var string
	 */
	abstract public function get_hook(): string;

	/**
	 * The status to set when this action is scheduled.
	 *
	 * @since TBD
	 * @var string
	 */
	abstract public function get_scheduled_status(): string;

	/**
	 * The status to set when this action is running.
	 *
	 * @since TBD
	 * @var string
	 */
	abstract public function get_in_progress_status(): string;

	/**
	 * The status to set when this action completes successfully.
	 *
	 * @since TBD
	 * @var string
	 */
	abstract public function get_completed_status(): string;

	/**
	 * The status to set when this action fails.
	 *
	 * @since TBD
	 * @var string
	 */
	abstract public function get_failed_status(): string;

	/**
	 * Whether this action can be scheduled.
	 *
	 * @since TBD
	 *
	 * @return bool True if the action can be scheduled.
	 */
	abstract public function can_schedule(): bool;

	/**
	 * Schedule this action to run.
	 *
	 * @since TBD
	 *
	 * @return int|WP_Error The action ID on success, WP_Error on failure.
	 */
	public function schedule() {
		if ( ! $this->can_schedule() ) {
			return new WP_Error(
				'tec_events_category_colors_migration_cannot_schedule',
				'Action cannot be scheduled at this time.'
			);
		}

		/**
		 * Fires before scheduling a migration action.
		 *
		 * @since TBD
		 *
		 * @param Abstract_Action $action The action being scheduled.
		 * @return bool|WP_Error True to allow scheduling, WP_Error to prevent it.
		 */
		$pre_schedule = apply_filters( 'tec_events_category_colors_migration_pre_schedule_action', true, $this );
		if ( is_wp_error( $pre_schedule ) ) {
			return $pre_schedule;
		}

		$action_id = as_enqueue_async_action(
			$this->get_hook(),
			[],
			Config::$migration_action_group
		);

		if ( is_wp_error( $action_id ) ) {
			return $action_id;
		}

		$this->action_id = $action_id;
		$this->update_migration_status( $this->get_scheduled_status() );

		/**
		 * Fires after scheduling a migration action.
		 *
		 * @since TBD
		 *
		 * @param Abstract_Action $action The action that was scheduled.
		 * @param int $action_id The ID of the scheduled action.
		 */
		do_action( 'tec_events_category_colors_migration_post_schedule_action', $this, $action_id );

		return $action_id;
	}

	/**
	 * Execute the action.
	 *
	 * @since TBD
	 *
	 * @return bool|WP_Error True on success, WP_Error on failure.
	 */
	public function execute() {
		$pre_execute = apply_filters( 'tec_events_category_colors_migration_pre_execute_action', true );
		if ( is_wp_error( $pre_execute ) ) {
			Status::update_migration_status( $this->get_failed_status(), $pre_execute->get_error_message() );
			return $pre_execute;
		}

		$pre_execute = apply_filters( 'tec_events_category_colors_migration_' . $this->get_hook() . '_pre_execute', true );
		if ( is_wp_error( $pre_execute ) ) {
			Status::update_migration_status( $this->get_failed_status(), $pre_execute->get_error_message() );
			return $pre_execute;
		}

		$this->update_migration_status( $this->get_in_progress_status() );

		$result = $this->process();

		if ( is_wp_error( $result ) ) {
			$this->update_migration_status( $this->get_failed_status(), $result->get_error_message() );
			return $result;
		}

		// Only set completed status if there are no more batches
		if ( ! get_option( Config::$migration_batch_option, 0 ) ) {
			$this->update_migration_status( $this->get_completed_status() );
		} else {
			$this->update_migration_status( $this->get_scheduled_status() );
		}

		$this->schedule_next_action();

		return true;
	}

	/**
	 * Cancel this action if it's scheduled.
	 *
	 * @since TBD
	 *
	 * @return bool True if the action was cancelled, false otherwise.
	 */
	public function cancel(): bool {
		if ( ! $this->action_id ) {
			return false;
		}

		/**
		 * Fires before cancelling a migration action.
		 *
		 * @since TBD
		 *
		 * @param Abstract_Action $action The action being cancelled.
		 * @return bool True to allow cancellation, false to prevent it.
		 */
		$pre_cancel = apply_filters( 'tec_events_category_colors_migration_pre_cancel_action', true, $this );
		if ( ! $pre_cancel ) {
			return false;
		}

		$cancelled = as_unschedule_action( $this->get_hook(), [], Config::$migration_action_group );
		$this->action_id = null;

		/**
		 * Fires after cancelling a migration action.
		 *
		 * @since TBD
		 *
		 * @param Abstract_Action $action The action that was cancelled.
		 * @param bool $cancelled Whether the action was successfully cancelled.
		 */
		do_action( 'tec_events_category_colors_migration_post_cancel_action', $this, $cancelled );

		return $cancelled;
	}

	/**
	 * Get the current action ID.
	 *
	 * @since TBD
	 *
	 * @return int|null The action ID if scheduled, null otherwise.
	 */
	public function get_action_id(): ?int {
		return $this->action_id;
	}

	/**
	 * Check if this action is currently scheduled.
	 *
	 * @since TBD
	 *
	 * @return bool True if the action is scheduled, false otherwise.
	 */
	public function is_scheduled(): bool {
		return $this->action_id !== null;
	}

	/**
	 * Determines if the migration step should run.
	 *
	 * @since TBD
	 *
	 * @return bool True if the step is ready to run, false otherwise.
	 */
	public function is_runnable(): bool {
		$current_status = static::get_migration_status()['status'];
		return ! in_array( $current_status, [
			Status::$preprocessing_in_progress,
			Status::$validation_in_progress,
			Status::$execution_in_progress,
			Status::$postprocessing_in_progress,
		], true );
	}

	/**
	 * Get the next scheduled time for this action.
	 *
	 * @since TBD
	 *
	 * @return int|false The timestamp of the next scheduled run, or false if not scheduled.
	 */
	public function get_next_scheduled_time() {
		if ( ! $this->action_id ) {
			return false;
		}

		return as_next_scheduled_action( $this->get_hook(), [], Config::$migration_action_group );
	}

	/**
	 * Get the migration status.
	 *
	 * @since TBD
	 *
	 * @return array<string, mixed> The current migration status.
	 */
	public static function get_migration_status(): array {
		return Handler::get_migration_status();
	}

	/**
	 * Update the migration status.
	 *
	 * @since TBD
	 *
	 * @param string $status The new status.
	 *
	 * @return void
	 */
	public function update_migration_status( string $status ): void {
		parent::update_migration_status( $status );
	}
}
