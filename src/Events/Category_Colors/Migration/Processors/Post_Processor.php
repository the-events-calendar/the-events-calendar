<?php
/**
 * Handles post-processing validation for category color migration.
 * Ensures that all expected meta values exist and logs any inconsistencies.
 * This step is crucial to verify the success of the migration process.
 *
 * @since   TBD
 *
 * @package TEC\Events\Category_Colors\Migration\Processors
 */

namespace TEC\Events\Category_Colors\Migration\Processors;

use TEC\Events\Category_Colors\Event_Category_Meta;
use Tribe__Events__Main;
use TEC\Events\Category_Colors\Migration\Config;
use TEC\Events\Category_Colors\Migration\Status;

/**
 * Class Post_Processor
 * Validates migration execution results by checking if expected metadata
 * was correctly stored in the database. Logs missing or mismatched data.
 *
 * @since   TBD
 *
 * @package TEC\Events\Category_Colors\Migration\Processors
 */
class Post_Processor extends Abstract_Migration_Step {

	/**
	 * List of meta keys that should be skipped (not inserted as term meta).
	 *
	 * @since TBD
	 * @var array<string>
	 */
	protected array $skip_meta_keys = [
		'taxonomy_id',
	];

	/**
	 * Determines whether the migration step is in a valid state to run.
	 *
	 * This method checks the current migration status and ensures the step
	 * should only execute if the migration has not already started.
	 *
	 * @since TBD
	 *
	 * @return bool True if the migration step can run, false otherwise.
	 */
	public function is_runnable(): bool {
		$status          = Status::get_migration_status()['status'];
		$processing_data = get_option( Config::$migration_processing_option, [] );

		// First check if we're in a valid state to run post-processing.
		if ( ! in_array( $status, [ Status::$execution_completed, Status::$postprocessing_failed ], true ) ) {
			$this->log_message(
				'warning',
				"Cannot run post-processing. Current status: {$status}",
				[],
				'Post Processor'
			);

			return false;
		}

		// Check if we have any processing data at all.
		if ( empty( $processing_data ) ) {
			$this->log_message(
				'error',
				'No processing data found. Migration may be in an inconsistent state.',
				[],
				'Post Processor'
			);

			return false;
		}

		// Check if we have any categories still to process.
		if ( ! empty( $processing_data['categories'] ) ) {
			$this->log_message(
				'warning',
				'Categories still exist in processing data. Execution may not be complete.',
				[ 'remaining_categories' => count( $processing_data['categories'] ) ],
				'Post Processor'
			);

			return false;
		}

		// Verify we have migration data to validate against.
		$migration_data = $this->get_migration_data();
		if ( empty( $migration_data['categories'] ) ) {
			$this->log_message(
				'error',
				'No migration data found to validate against.',
				[],
				'Post Processor'
			);

			return false;
		}

		return true;
	}

	/**
	 * Runs validation checks on migrated category meta data.
	 *
	 * @since TBD
	 * @return bool
	 */
	public function process(): bool {
		$start_time = microtime( true );
		if ( $this->dry_run ) {
			$this->log_message( 'info', 'Dry run mode active. Skipping post-processing validation.', [], 'Post Processor' );
			$this->update_migration_status( Status::$postprocessing_completed );
			$this->log_elapsed_time( 'Post Processor', $start_time );

			return true;
		}

		$migration_data = $this->get_migration_data();

		if ( empty( $migration_data['categories'] ) ) {
			$this->log_message( 'warning', 'No migration data found. Cannot validate migration results.', [], 'Post Processor' );
			$this->update_migration_status( Status::$postprocessing_completed );
			$this->log_elapsed_time( 'Post Processor', $start_time );

			return false;
		}

		$errors_found = false;

		// Validate each category against expected migration data.
		foreach ( $migration_data['categories'] as $category_id => $meta_data ) {
			$category_meta = tribe( Event_Category_Meta::class )->set_term( $category_id );
			$actual_meta   = array_map(
				static fn( $value ) => is_array( $value ) && count( $value ) === 1 ? $value[0] : $value,
				$category_meta->get()
			);

			foreach ( $meta_data as $meta_key => $expected_value ) {
				// Skip validation for excluded meta keys.
				if ( in_array( $meta_key, $this->skip_meta_keys, true ) ) {
					continue;
				}

				$actual_value = $actual_meta[ $meta_key ] ?? null;

				if ( is_null( $actual_value ) ) {
					$this->log_message( 'error', "Missing meta key '{$meta_key}' for category ID {$category_id}.", [], 'Post Processor' );
					$errors_found = true;
				} elseif ( $actual_value !== $expected_value ) {
					// If the actual value exists but doesn't match expected, this is okay
					// because we intentionally don't overwrite existing values during migration.
					$this->log_message(
						'info',
						"Found different value for '{$meta_key}' on category {$category_id}. " .
						"This is expected as we don't overwrite existing values. " .
						'Migration value: ' . wp_json_encode( $expected_value, JSON_PRETTY_PRINT ) .
						' | Existing value: ' . wp_json_encode( $actual_value, JSON_PRETTY_PRINT ),
						[],
						'Post Processor'
					);
				}
			}
		}

		if ( $errors_found ) {
			$this->update_migration_status( Status::$postprocessing_failed );
			$this->log_elapsed_time( 'Post Processor', $start_time );

			return false;
		} else {
			$this->log_message( 'info', 'Migration verification successful. Marking migration as completed.', [], 'Post Processor' );
			$this->update_migration_status( Status::$postprocessing_completed );
		}
		$this->log_elapsed_time( 'Post Processor', $start_time );

		return true;
	}
}
