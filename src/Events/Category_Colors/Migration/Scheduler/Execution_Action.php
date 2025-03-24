<?php
/**
 * Handles the execution phase of the migration.
 * Processes categories in batches of 100.
 *
 * @since   TBD
 *
 * @package TEC\Events\Category_Colors\Migration\Scheduler
 */

namespace TEC\Events\Category_Colors\Migration\Scheduler;

use TEC\Events\Category_Colors\Migration\Config;
use TEC\Events\Category_Colors\Migration\Status;
use TEC\Events\Category_Colors\Migration\Worker;
use TEC\Events\Category_Colors\Migration\Executor;

/**
 * Handles the execution phase of the migration.
 *
 * @since   TBD
 *
 * @package TEC\Events\Category_Colors\Migration\Scheduler
 */
class Execution_Action extends Abstract_Action {

	/**
	 * The hook name for this action.
	 *
	 * @since TBD
	 * @var string
	 */
	protected const HOOK = 'tec_events_category_colors_migration_execute';

	/**
	 * The number of categories to process in each batch.
	 *
	 * @since TBD
	 * @var int
	 */
	protected const BATCH_SIZE = 100;

	/**
	 * Constructor.
	 *
	 * @since TBD
	 */
	public function __construct() {
		add_action( self::HOOK, [ $this, 'execute' ] );
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
		return Status::$execution_scheduled;
	}

	/**
	 * Get the status to set when this action is running.
	 *
	 * @since TBD
	 *
	 * @return string The in-progress status.
	 */
	public function get_in_progress_status(): string {
		return Status::$execution_in_progress;
	}

	/**
	 * Get the status to set when this action completes successfully.
	 *
	 * @since TBD
	 *
	 * @return string The completed status.
	 */
	public function get_completed_status(): string {
		return Status::$execution_completed;
	}

	/**
	 * Get the status to set when this action fails.
	 *
	 * @since TBD
	 *
	 * @return string The failed status.
	 */
	public function get_failed_status(): string {
		return Status::$execution_failed;
	}

	/**
	 * Whether this action can be scheduled.
	 *
	 * @since TBD
	 *
	 * @return bool True if the action can be scheduled.
	 */
	public function can_schedule(): bool {
		$current_status = $this->get_migration_status()['status'];
		return in_array( $current_status, [ Status::$validation_completed, Status::$execution_failed ], true );
	}

	/**
	 * Process the execution step.
	 *
	 * @since TBD
	 *
	 * @return bool|WP_Error True on success, WP_Error on failure.
	 */
	public function process() {
		$worker = tribe( Worker::class );
		$migration_data = $this->get_migration_data();
		
		if ( empty( $migration_data['categories'] ) || ! is_array( $migration_data['categories'] ) ) {
			$error = new \WP_Error(
				'tec_events_category_colors_migration_no_categories',
				'No categories found for migration.'
			);
			Status::update_migration_status( Status::$execution_failed, $error->get_error_message() );
			codecept_debug("Here 1");
			return $error;
		}

		$categories = array_chunk( $migration_data['categories'], self::BATCH_SIZE, true );
		$total_batches = count( $categories );
		$remaining_batches = get_option( Config::$migration_batch_option, $total_batches );

		codecept_debug("Categories: " .count($migration_data['categories']));
		codecept_debug("Total Batches: " . $total_batches);
		codecept_debug("Remaining Batches: " . $remaining_batches);

		if ( $remaining_batches <= 0 ) {
			// All batches processed
			delete_option( Config::$migration_batch_option );
			Status::update_migration_status( Status::$execution_completed );
			codecept_debug("Here 2");
			return true;
		}

		$current_batch_index = $total_batches - $remaining_batches;
		$result = $worker->process_batch( $categories[ $current_batch_index ] );

		if ( is_wp_error( $result ) || false === $result ) {
			$error_message = is_wp_error( $result ) ? $result->get_error_message() : 'Execution failed';
			Status::update_migration_status( Status::$execution_failed, $error_message );
			codecept_debug("Here 3");
			return is_wp_error( $result ) ? $result : new \WP_Error( 'execution_failed', $error_message );
		}

		// Only decrement batch counter and schedule next batch if processing was successful
		update_option( Config::$migration_batch_option, $remaining_batches - 1 );
		codecept_debug("Remaining Batches: " . ($remaining_batches - 1 ));
		
		// Schedule the next batch
		$this->schedule_next_action();
		
		codecept_debug("Here 4");
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
		// Only schedule postprocessing when all batches are done
		if ( get_option( Config::$migration_batch_option, 0 ) <= 0 ) {
			$post_processor = tribe( Postprocessing_Action::class );
			$post_processor->schedule();
		} else {
			// Schedule the next batch of execution
			$this->schedule();
		}
	}

	/**
	 * Get the migration data.
	 *
	 * @since TBD
	 *
	 * @return array<string, mixed> The migration data.
	 */
	public function get_migration_data(): array {
		return get_option( Config::$migration_data_option, [] );
	}
} 