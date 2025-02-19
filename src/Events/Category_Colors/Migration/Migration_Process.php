<?php
/**
 * Handles the category color migration process.
 *
 * This class manages the full lifecycle of the category color migration process,
 * including preprocessing, validation, execution, and post-processing.
 * It ensures that the migration runs sequentially, with proper logging and status checks.
 *
 * @since   TBD
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
 * @since TBD
 */
class Migration_Process {
	use Migration_Trait;

	/** @var bool Whether this is a dry run */
	private bool $dry_run = false;

	/**
	 * Runs the migration process.
	 */
	public function migrate(): void {
		Logger::clear_logs();
		if ( $this->is_migration_complete() ) {
			Logger::log( 'info', 'Migration has already been completed.' );

			return;
		}

		// Prevent running if migration is already in progress.
		$current_status = $this->get_migration_status()['status'];

		if ( 'execution_in_progress' === $current_status ) {
			Logger::log( 'info', 'Migration is already in progress.' );

			return;
		}

		Logger::log( 'info', 'Migration starting. Current status: ' . $this->get_status() );

		$this->preprocess();
		$this->validate();
		$this->execute();
		$this->postprocess();
	}

	/**
	 * Checks if migration is complete.
	 */
	private function is_migration_complete(): bool {
		return $this->get_status() === 'migration_completed';
	}

	/**
	 * Retrieves the current migration status.
	 */
	private function get_status(): string {
		$migration_status = $this->get_migration_status();

		return $migration_status['status'] ?? 'not_started';
	}

	/**
	 * Preprocesses data before migration.
	 */
	private function preprocess(): void {
		if ( $this->get_status() === 'not_started' ) {
			tribe( Pre_Processor::class )->process();
		}
	}

	/**
	 * Validates the migration data.
	 */
	private function validate(): void {
		if ( in_array( $this->get_status(), [ 'preprocess_completed', 'validation_failed' ], true ) ) {
			if ( ! tribe( Validator::class )->validate() ) {
				return;
			}
		}
	}

	/**
	 * Executes the migration.
	 */
	private function execute(): void {
		if ( in_array( $this->get_status(), [ 'validation_completed', 'execution_failed' ], true ) ) {
			$executor = new Migration_Runner( $this->dry_run );
			$executor->execute();

			if ( ! empty( Logger::get_logs( 'error' ) ) ) {
				return;
			}
		}
	}

	/**
	 * Handles post-processing validation.
	 */
	private function postprocess(): void {
		if ( in_array( $this->get_status(), [ 'execution_completed', 'postprocessing_failed' ], true ) ) {
			$post_processor = new Post_Processor( $this->dry_run );
			$post_processor->verify_migration();
		}
	}
}
