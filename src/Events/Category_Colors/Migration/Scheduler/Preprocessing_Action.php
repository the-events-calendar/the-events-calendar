<?php
/**
 * Handles the preprocessing phase of the migration.
 * Prepares and formats data for migration.
 *
 * @since   TBD
 *
 * @package TEC\Events\Category_Colors\Migration\Scheduler
 */

namespace TEC\Events\Category_Colors\Migration\Scheduler;

use TEC\Events\Category_Colors\Migration\Config;
use TEC\Events\Category_Colors\Migration\Processors\Pre_Processor;
use TEC\Events\Category_Colors\Migration\Status;
use TEC\Events\Category_Colors\Migration\Processors\Validator;

/**
 * Handles the preprocessing phase of the migration.
 *
 * @since   TBD
 *
 * @package TEC\Events\Category_Colors\Migration\Scheduler
 */
class Preprocessing_Action extends Abstract_Action {

	/**
	 * The hook name for this action.
	 *
	 * @since TBD
	 * @var string
	 */
	protected const HOOK = 'tec_events_category_colors_migration_preprocess';

	/**
	 * Constructor.
	 *
	 * @since TBD
	 */
	public function __construct() {
		// Hook registration is now handled in the Controller
	}

	/**
	 * Get the hook name for this action.
	 *
	 * @since TBD
	 *
	 * @return string The hook name.
	 */
	public function get_hook(): string {
		return self::HOOK;
	}

	/**
	 * Get the status to set when this action is scheduled.
	 *
	 * @since TBD
	 *
	 * @return string The scheduled status.
	 */
	public function get_scheduled_status(): string {
		return Status::$preprocessing_scheduled;
	}

	/**
	 * Get the status to set when this action is running.
	 *
	 * @since TBD
	 *
	 * @return string The in-progress status.
	 */
	public function get_in_progress_status(): string {
		return Status::$preprocessing_in_progress;
	}

	/**
	 * Get the status to set when this action completes successfully.
	 *
	 * @since TBD
	 *
	 * @return string The completed status.
	 */
	public function get_completed_status(): string {
		return Status::$preprocessing_completed;
	}

	/**
	 * Get the status to set when this action fails.
	 *
	 * @since TBD
	 *
	 * @return string The failed status.
	 */
	public function get_failed_status(): string {
		return Status::$preprocessing_failed;
	}

	/**
	 * Whether this action can be scheduled.
	 *
	 * @since TBD
	 *
	 * @return bool True if the action can be scheduled.
	 */
	public function can_schedule(): bool {
		// Check if we're in a valid state to schedule preprocessing
		$current_status = Status::get_migration_status()['status'];
		return in_array( $current_status, [ Status::$not_started, Status::$preprocessing_failed ], true );
	}

	/**
	 * Process the preprocessing step.
	 *
	 * @since TBD
	 *
	 * @return bool|WP_Error True on success, WP_Error on failure.
	 */
	public function process() {
		$start_time = microtime( true );
		error_log( 'Preprocessing started at: ' . date( 'Y-m-d H:i:s' ) );

		$preprocessor = tribe( Pre_Processor::class );
		$result       = $preprocessor->process();

		$end_time = microtime( true );
		$duration = round( $end_time - $start_time, 2 );
		error_log( 'Preprocessing ended at: ' . date( 'Y-m-d H:i:s' ) . ' (Duration: ' . $duration . ' seconds)' );

		// Check if the status is preprocessing_skipped (valid state)
		$current_status = Status::get_migration_status()['status'];
		if ( Status::$preprocessing_skipped === $current_status ) {
			return true;
		}

		// Handle actual failures
		if ( is_wp_error( $result ) || false === $result ) {
			$error_message = is_wp_error( $result ) ? $result->get_error_message() : 'Preprocessing failed';
			Status::update_migration_status( Status::$preprocessing_failed, $error_message );

			return is_wp_error( $result ) ? $result : new WP_Error( 'preprocessing_failed', $error_message );
		}

		Status::update_migration_status( Status::$preprocessing_completed );

		return true;
	}

	/**
	 * Schedule the next action in the sequence.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	protected function schedule_next_action(): void {
		$validator = tribe( Validation_Action::class );
		$validator->schedule();
	}

	/**
	 * Whether this action is in a valid state to run.
	 *
	 * @since TBD
	 *
	 * @return bool True if the action can run, false otherwise.
	 */
	public function is_runnable(): bool {
		$current_status = Status::get_migration_status()['status'];
		return in_array( $current_status, [ Status::$preprocessing_scheduled, Status::$preprocessing_in_progress ], true );
	}
}
