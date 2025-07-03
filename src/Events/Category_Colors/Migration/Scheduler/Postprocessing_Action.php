<?php
/**
 * Handles the postprocessing phase of the migration.
 * Performs final cleanup and verification.
 *
 * @since 6.14.0
 *
 * @package TEC\Events\Category_Colors\Migration\Scheduler
 */

namespace TEC\Events\Category_Colors\Migration\Scheduler;

use TEC\Events\Category_Colors\Migration\Processors\Post_Processor;
use TEC\Events\Category_Colors\Migration\Status;
use WP_Error;

/**
 * Handles the postprocessing phase of the migration.
 *
 * @since 6.14.0
 *
 * @package TEC\Events\Category_Colors\Migration\Scheduler
 */
class Postprocessing_Action extends Abstract_Action {

	/**
	 * The hook name for this action.
	 *
	 * @since 6.14.0
	 * @var string
	 */
	protected const HOOK = 'tec_events_category_colors_migration_postprocess';

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
		return Status::$postprocessing_scheduled;
	}

	/**
	 * Get the status to set when this action is running.
	 *
	 * @since 6.14.0
	 *
	 * @return string The in-progress status.
	 */
	public function get_in_progress_status(): string {
		return Status::$postprocessing_in_progress;
	}

	/**
	 * Get the status to set when this action completes successfully.
	 *
	 * @since 6.14.0
	 *
	 * @return string The completed status.
	 */
	public function get_completed_status(): string {
		return Status::$postprocessing_completed;
	}

	/**
	 * Get the status to set when this action fails.
	 *
	 * @since 6.14.0
	 *
	 * @return string The failed status.
	 */
	public function get_failed_status(): string {
		return Status::$postprocessing_failed;
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

		return in_array( $current_status, [ Status::$execution_completed, Status::$postprocessing_failed ], true );
	}

	/**
	 * Process the postprocessing step.
	 *
	 * @since 6.14.0
	 *
	 * @return bool|WP_Error True on success, WP_Error on failure.
	 */
	public function process() {
		$postprocessor = tribe( Post_Processor::class );
		$result        = $postprocessor->process();

		if ( is_wp_error( $result ) || false === $result ) {
			$error_message = is_wp_error( $result ) ? $result->get_error_message() : 'Postprocessing failed';
			Status::update_migration_status( Status::$postprocessing_failed, $error_message );

			return is_wp_error( $result ) ? $result : new WP_Error( 'postprocessing_failed', $error_message );
		}

		Status::update_migration_status( Status::$postprocessing_completed );

		return true;
	}

	/**
	 * Schedule the next action in the sequence.
	 * This is the final action, so it doesn't schedule anything.
	 *
	 * @since 6.14.0
	 *
	 * @return void
	 */
	protected function schedule_next_action(): void {
		// No next action to schedule.
	}
}
