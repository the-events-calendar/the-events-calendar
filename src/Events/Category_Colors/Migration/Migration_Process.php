<?php

namespace TEC\Events\Category_Colors\Migration;

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

		Logger::log( 'info', "Migration starting. Current status: " . $this->get_status() );

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
