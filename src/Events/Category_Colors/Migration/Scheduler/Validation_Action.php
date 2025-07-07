<?php
/**
 * Handles the validation phase of the migration.
 * Validates preprocessed data before execution.
 *
 * @since 6.14.0
 *
 * @package TEC\Events\Category_Colors\Migration\Scheduler
 */

namespace TEC\Events\Category_Colors\Migration\Scheduler;

use TEC\Events\Category_Colors\Migration\Status;
use TEC\Events\Category_Colors\Migration\Processors\Validator;
use WP_Error;

/**
 * Handles the validation phase of the migration.
 *
 * @since 6.14.0
 *
 * @package TEC\Events\Category_Colors\Migration\Scheduler
 */
class Validation_Action extends Abstract_Action {

	/**
	 * The hook name for this action.
	 *
	 * @since 6.14.0
	 * @var string
	 */
	protected const HOOK = 'tec_events_category_colors_migration_validate';

	/**
	 * Get the hook name for this action.
	 *
	 * @since 6.14.0
	 *
	 * @return string The hook name.
	 */
	public function get_hook(): string {
		return self::HOOK;
	}

	/**
	 * Get the status to set when this action is scheduled.
	 *
	 * @since 6.14.0
	 *
	 * @return string The scheduled status.
	 */
	public function get_scheduled_status(): string {
		return Status::$validation_scheduled;
	}

	/**
	 * Get the status to set when this action is running.
	 *
	 * @since 6.14.0
	 *
	 * @return string The in-progress status.
	 */
	public function get_in_progress_status(): string {
		return Status::$validation_in_progress;
	}

	/**
	 * Get the status to set when this action completes successfully.
	 *
	 * @since 6.14.0
	 *
	 * @return string The completed status.
	 */
	public function get_completed_status(): string {
		return Status::$validation_completed;
	}

	/**
	 * Get the status to set when this action fails.
	 *
	 * @since 6.14.0
	 *
	 * @return string The failed status.
	 */
	public function get_failed_status(): string {
		return Status::$validation_failed;
	}

	/**
	 * Whether this action can be scheduled.
	 *
	 * @since 6.14.0
	 *
	 * @return bool True if the action can be scheduled.
	 */
	public function can_schedule(): bool {
		$current_status = Status::get_migration_status()['status'];

		return in_array( $current_status, [ Status::$preprocessing_completed, Status::$validation_failed ], true );
	}

	/**
	 * Process the validation step.
	 *
	 * @since 6.14.0
	 *
	 * @return bool|WP_Error True on success, WP_Error on failure.
	 */
	public function process() {
		$validator = tribe( Validator::class );
		$result    = $validator->process();

		if ( is_wp_error( $result ) || false === $result ) {
			$error_message = is_wp_error( $result ) ? $result->get_error_message() : 'Validation failed';
			Status::update_migration_status( Status::$validation_failed, $error_message );

			return is_wp_error( $result ) ? $result : new WP_Error( 'validation_failed', $error_message );
		}

		Status::update_migration_status( Status::$validation_completed );

		return true;
	}

	/**
	 * Schedule the next action in the sequence.
	 *
	 * @since 6.14.0
	 *
	 * @return void
	 */
	protected function schedule_next_action(): void {
		$execution = tribe( Execution_Action::class );
		$execution->schedule();
	}
}
