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
class Handler {
	use Utilities;

	/**
	 * Whether this is a dry run.
	 *
	 * A dry run allows testing the migration process without making actual changes.
	 *
	 * @since TBD
	 *
	 * @var bool
	 */
	private bool $dry_run = false;

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
	 * @return void
	 */
	public function migrate( bool $dry_run = false ): void {
		Errors::clear_errors();
		$this->dry_run = $dry_run;
		$start_time    = $this->start_timer();

		if ( $this->is_migration_complete() ) {
			$this->log_message( 'info', 'Migration has already been completed.' );
			$this->log_elapsed_time( 'Migration Process', $start_time );
			return;
		}

		// Prevent running if migration is already in progress.
		if ( 'execution_in_progress' === $this->get_status() ) {
			$this->log_message( 'info', 'Migration is already in progress.' );
			$this->log_elapsed_time( 'Migration Process', $start_time );
			return;
		}

		$this->log_message( 'info', 'Migration starting. Current status: ' . $this->get_status() );

		// Define migration steps.
		$migration_steps = [
			'Preprocessing'   => fn() => $this->preprocess(),
			'Validation'      => fn() => $this->validate(),
			'Execution'       => fn() => $this->execute(),
			'Post-processing' => fn() => $this->postprocess(),
		];

		// Execute each step in sequence and stop if a failure occurs.
		foreach ( $migration_steps as $step_name => $step ) {
			if ( ! $this->run_migration_step( $step, $step_name ) ) {
				return;
			}
		}
		$this->log_elapsed_time( 'Migration Process', $start_time );
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

	/**
	 * Checks if the migration process has already been completed.
	 *
	 * This ensures that the migration does not run again once it has finished successfully.
	 *
	 * @since TBD
	 *
	 * @return bool True if the migration is already completed, false otherwise.
	 */
	private function is_migration_complete(): bool {
		return $this->get_status() === Status::$postprocess_completed;
	}

	/**
	 * Retrieves the current migration status.
	 *
	 * The migration status indicates which phase the process is currently in.
	 * Possible values:
	 * - `not_started`
	 * - `preprocess_completed`
	 * - `validation_completed`
	 * - `execution_completed`
	 * - `migration_completed`
	 * - `execution_failed`
	 * - `postprocessing_failed`
	 *
	 * @since TBD
	 *
	 * @return string The current migration status.
	 */
	private function get_status(): string {
		$migration_status = $this->get_migration_status();

		return $migration_status['status'] ?? 'not_started';
	}

	/**
	 * Handles the preprocessing phase of the migration.
	 *
	 * The preprocessing phase prepares the migration data and ensures that the necessary
	 * structures are in place before validation begins.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	private function preprocess(): void {
		if ( $this->get_status() === 'not_started' ) {
			tribe( Pre_Processor::class )->process();
		}
	}

	/**
	 * Runs validation checks on the migration data.
	 *
	 * Validation ensures that the data being migrated is correctly structured,
	 * contains all required fields, and does not introduce inconsistencies.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	private function validate(): void {
		if ( in_array( $this->get_status(), [ Status::$preprocess_completed, Status::$validation_failed ], true ) ) {
			tribe( Validator::class )->validate();
		}
	}

	/**
	 * Executes the migration process.
	 *
	 * This step applies the validated category colors data to the database.
	 * If execution fails due to an error (e.g., database failure), it will be logged.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	private function execute(): void {
		if ( in_array( $this->get_status(), [ Status::$validation_completed, Status::$execution_failed ], true ) ) {
			$executor = new Worker( $this->dry_run );
			$executor->execute();
		}
	}

	/**
	 * Handles post-processing validation.
	 *
	 * Post-processing ensures that the migration completed successfully by verifying
	 * the applied data. If post-processing fails, it prevents the migration from
	 * being marked as complete.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	private function postprocess(): void {
		if ( in_array( $this->get_status(), [ Status::$execution_completed, Status::$postprocess_failed ], true ) ) {
			$post_processor = new Post_Processor( $this->dry_run );
			$post_processor->verify_migration();
		}
	}
}
