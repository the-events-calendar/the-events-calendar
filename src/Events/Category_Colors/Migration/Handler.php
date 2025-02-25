<?php
/**
 * Handles the category color migration process.
 *
 * This class manages the full lifecycle of the category color migration process,
 * including preprocessing, validation, execution, and post-processing.
 * It ensures that the migration runs sequentially, with proper logging and status checks.
 *
 * @since   TBD
 *
 * @package TEC\Events\Category_Colors\Migration
 */

namespace TEC\Events\Category_Colors\Migration;

use Tribe__Events__Main;

/**
 * Class Migration_Process
 *
 * Orchestrates the category color migration process, ensuring that each step
 * is executed in order and that the migration status is properly maintained.
 * The migration consists of four main steps:
 *
 * 1. **Preprocessing** - Prepares the migration data.
 * 2. **Validation** - Ensures the data is structured correctly.
 * 3. **Execution** - Applies category colors to the database.
 * 4. **Post-processing** - Verifies that the migration was successful.
 *
 * The process prevents duplicate migrations from running and handles failures
 * gracefully by logging issues and stopping execution if errors are detected.
 *
 * @since   TBD
 *
 * @package TEC\Events\Category_Colors\Migration
 */
class Handler extends Abstract_Migration_Step {

	/**
	 * The taxonomy used for event categories.
	 *
	 * @since TBD
	 * @var string
	 */
	public static string $taxonomy = Tribe__Events__Main::TAXONOMY;

	/**
	 * Whether this is a dry run.
	 *
	 * A dry run allows testing the migration process without making actual changes.
	 *
	 * @since TBD
	 *
	 * @var bool
	 */
	protected bool $dry_run = false;

	/**
	 * Determines whether the migration step is in a valid state to run.
	 *
	 * @since TBD
	 *
	 * @return bool True if the migration step can run, false otherwise.
	 */
	public function is_runnable(): bool {
		return true;
	}


	/**
	 * Runs the migration process in sequential steps.
	 *
	 * Each step is executed in order, stopping if an error occurs.
	 * This ensures that the migration does not continue if a previous step fails.
	 *
	 * @since TBD
	 *
	 * @param bool $dry_run Optional. If true, the migration runs in dry-run mode.
	 *
	 * @return bool
	 */
	public function process( bool $dry_run = false ): bool {
		Errors::clear_errors();
		$this->dry_run = $dry_run;
		$start_time    = microtime( true );

		if ( Status::$postprocess_completed === static::get_migration_status()['status'] ) {
			$this->log_message( 'info', 'Migration has already been completed.' );
			$this->log_elapsed_time( 'Migration Process', $start_time );

			return true;
		}

		// Prevent running if migration is already in progress.
		if ( Status::$execution_in_progress === static::get_migration_status()['status'] ) {
			$this->log_message( 'info', 'Migration is already in progress.' );
			$this->log_elapsed_time( 'Migration Process', $start_time );

			return false;
		}

		$this->log_message( 'info', 'Migration starting. Current status: ' . static::get_migration_status()['status'] );

		// Define migration steps.
		$migration_steps = [
			'Preprocessing'   => fn() => tribe( Pre_Processor::class )->process(),
			'Validation'      => fn() => tribe( Validator::class )->process(),
			'Execution'       => fn() => tribe( Worker::class )->process(),
			'Post-processing' => fn() => tribe( Post_processor::class )->process(),
		];

		// Execute each step in sequence and stop if a failure occurs.
		foreach ( $migration_steps as $step_name => $step ) {
			if ( ! $this->run_migration_step( $step, $step_name ) ) {
				return false;
			}
		}
		$this->log_elapsed_time( 'Migration Process', $start_time );

		return true;
	}

	/**
	 * Runs a single migration step and stops further execution if errors are logged.
	 *
	 * @since TBD
	 *
	 * @param callable $migration_step The function representing a migration step.
	 * @param string   $step_name      The name of the migration step.
	 *
	 * @return bool True if the step succeeded, false if an error was logged.
	 */
	protected function run_migration_step( callable $migration_step, string $step_name ): bool {
		$migration_step();

		if ( Errors::has_errors() ) {
			$this->log_message( 'error', "Migration failed at step: {$step_name}. Stopping further processing." );
			return false;
		}

		return true;
	}
}
