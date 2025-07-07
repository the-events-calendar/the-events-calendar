<?php
/**
 * Handles the execution phase of the migration.
 * Processes categories in batches of 100.
 *
 * @since 6.14.0
 *
 * @package TEC\Events\Category_Colors\Migration\Scheduler
 */

namespace TEC\Events\Category_Colors\Migration\Scheduler;

use TEC\Events\Category_Colors\Migration\Status;
use TEC\Events\Category_Colors\Migration\Processors\Worker;
use WP_Error;

/**
 * Class Execution_Action
 * Handles the execution phase of the migration process.
 * Schedules and manages the processing of categories in batches.
 *
 * @since 6.14.0
 *
 * @package TEC\Events\Category_Colors\Migration\Scheduler
 */
class Execution_Action extends Abstract_Action {

	/**
	 * The Worker instance.
	 *
	 * @since 6.14.0
	 * @var Worker
	 */
	protected Worker $worker;

	/**
	 * The hook name for this action.
	 *
	 * @since 6.14.0
	 * @var string
	 */
	protected const HOOK = 'tec_events_category_colors_migration_execution';

	/**
	 * Constructor.
	 *
	 * @since 6.14.0
	 */
	public function __construct() {
		$this->worker = tribe( Worker::class );
	}

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
		return Status::$execution_scheduled;
	}

	/**
	 * Get the status to set when this action is running.
	 *
	 * @since 6.14.0
	 *
	 * @return string The in-progress status.
	 */
	public function get_in_progress_status(): string {
		return Status::$execution_in_progress;
	}

	/**
	 * Get the status to set when this action completes successfully.
	 *
	 * @since 6.14.0
	 *
	 * @return string The completed status.
	 */
	public function get_completed_status(): string {
		return Status::$execution_completed;
	}

	/**
	 * Get the status to set when this action fails.
	 *
	 * @since 6.14.0
	 *
	 * @return string The failed status.
	 */
	public function get_failed_status(): string {
		return Status::$execution_failed;
	}

	/**
	 * Determines if the action is in a valid state to run.
	 *
	 * @since 6.14.0
	 *
	 * @return bool True if the action can run, false otherwise.
	 */
	public function is_runnable(): bool {
		return Status::$validation_completed === Status::get_migration_status()['status'];
	}

	/**
	 * Executes the action.
	 *
	 * @since 6.14.0
	 *
	 * @return bool|WP_Error True on success, WP_Error on failure.
	 */
	public function execute() {
		// Let the Worker handle the processing.
		$result = $this->worker->process();
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		// Check if there are more categories to process.
		$remaining_categories = $this->worker->get_remaining_categories();
		if ( $remaining_categories > 0 ) {
			// Schedule the next batch.
			$this->schedule_next_batch();
		} else {
			// No more categories, mark execution as completed and schedule postprocessing.
			$this->update_migration_status( Status::$execution_completed );

			// Schedule the postprocessing action.
			$postprocessing = tribe( Postprocessing_Action::class );
			$postprocessing->schedule();
		}

		return true;
	}

	/**
	 * Schedules the next batch to be processed.
	 *
	 * @since 6.14.0
	 *
	 * @return void
	 */
	protected function schedule_next_batch(): void {
		$total_categories     = $this->worker->get_total_categories();
		$remaining_categories = $this->worker->get_remaining_categories();
		$processed_categories = $total_categories - $remaining_categories;

		$args = [
			'processed_categories' => $processed_categories,
			'remaining_categories' => $remaining_categories,
			'total_categories'     => $total_categories,
			'scheduled_at'         => time(),
		];

		as_enqueue_async_action(
			self::HOOK,
			$args,
			'tec_events_category_colors_migration'
		);
	}

	/**
	 * Whether this action can be scheduled.
	 *
	 * @since 6.14.0
	 *
	 * @return bool True if the action can be scheduled.
	 */
	public function can_schedule(): bool {
		return true;
	}

	/**
	 * Process a step. This method does nothing for the Execution_action class.
	 *
	 * @since 6.14.0
	 *
	 * @return bool|WP_Error True on success, WP_Error on failure.
	 */
	public function process() {
		return true;
	}

	/**
	 * Schedule the next action, this method does nothing for the Execution_action class.
	 *
	 * @since 6.14.0
	 *
	 * @return void
	 */
	protected function schedule_next_action(): void {
	}
}
