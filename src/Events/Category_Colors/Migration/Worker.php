<?php
/**
 * Executes the full category color migration process.
 * Orchestrates preprocessing, validation, execution, and post-processing steps.
 *
 * @since   TBD
 *
 * @package TEC\Events\Category_Colors\Migration
 */

namespace TEC\Events\Category_Colors\Migration;

use TEC\Events\Category_Colors\Event_Category_Meta;
use Tribe__Events__Main;

/**
 * Class Migration_Runner
 * Controls the entire migration lifecycle for category colors.
 * Runs preprocessing, validation, execution, and post-processing in sequence.
 * Ensures data integrity and logs potential issues during migration.
 *
 * @since   TBD
 *
 * @package TEC\Events\Category_Colors\Migration
 */
class Worker extends Abstract_Migration_Step {

	/**
	 * Whether to perform a dry run (no actual DB modifications).
	 *
	 * @since TBD
	 * @var bool
	 */
	protected bool $dry_run = false;

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
		return in_array( static::get_migration_status()['status'], [ Status::$validation_completed, Status::$execution_failed ], true );
	}

	/**
	 * Allows for enabling Dry run mode.
	 *
	 * @param bool $dry_run Whether the worker should be in Dry Run mode.
	 *
	 * @return self
	 */
	public function set_dry_run( bool $dry_run = false ): self {
		$this->dry_run = $dry_run;

		return $this;
	}

	/**
	 * Executes the category color migration process.
	 *
	 * This method orchestrates the entire migration process, ensuring:
	 * 1. Validation is skipped if already completed.
	 * 2. Execution only proceeds if validation passes.
	 * 3. Proper logging and error handling.
	 *
	 * @since TBD
	 *
	 * @return bool
	 */
	public function process(): bool {
		$start_time = microtime( true );
		if ( Status::$validation_completed !== static::get_migration_status()['status'] ) {
			$this->log_message( 'info', 'Validation not completed. Running validation before execution.', [], 'Worker' );

			if ( ! tribe( Validator::class )->validate() ) {
				$this->log_message( 'error', 'Validation failed. Migration execution stopped.', [], 'Worker' );
				$this->update_migration_status( Status::$execution_failed ); // Mark execution as failed.

				do_action( 'tec_events_category_colors_migration_runner_end', false );
				$this->log_elapsed_time( 'Execution', $start_time );

				return false;
			}

			$this->log_message( 'info', 'Validation completed successfully. Proceeding to execution.', [], 'Worker' );
		} else {
			$this->log_message( 'info', 'Skipping validation step as it was already completed.', [], 'Worker' );
		}

		$this->update_migration_status( Status::$execution_in_progress );

		/**
		 * Fires before the migration execution begins.
		 *
		 * @since TBD
		 *
		 * @param bool $dry_run Whether the migration is running in dry-run mode.
		 */
		do_action( 'tec_events_category_colors_migration_runner_start', $this->dry_run );

		$this->dry_run_statistics(); // Log dry-run details before execution starts.

		$migration_data = $this->get_migration_data();

		if ( empty( $migration_data['categories'] ) || ! is_array( $migration_data['categories'] ) ) {
			$this->log_message( 'error', 'No categories found for migration.', [], 'Worker' );
			$this->update_migration_status( 'execution_skipped' ); // Mark execution as skipped.

			/**
			 * Fires when migration execution is stopped due to no data.
			 *
			 * @since TBD
			 *
			 * @param bool $success False, indicating failure.
			 */
			do_action( 'tec_events_category_colors_migration_runner_end', false );
			$this->log_elapsed_time( 'Execution', $start_time );
			return true;
		}

		$this->log_existing_meta( $migration_data['categories'] ); // Log existing category meta.

		$this->insert_category_meta( $migration_data['categories'] );
		if ( ! empty( $migration_data['settings'] ) ) {
			$this->insert_settings( $migration_data['settings'] );
		}

		$this->update_migration_status( ! Errors::has_errors() ? Status::$execution_completed : Status::$execution_failed ); // Update final status.

		/**
		 * Fires after the migration execution completes.
		 *
		 * @since TBD
		 *
		 * @param bool $success True if execution was successful, false otherwise.
		 */
		do_action( 'tec_events_category_colors_migration_runner_end', ! Errors::has_errors() );
		$this->log_elapsed_time( 'Execution', $start_time );
		return true;
	}

	/**
	 * Inserts meta values for the given categories.
	 * This method loops through each category and its associated meta data,
	 * inserting meta values into the database unless they already exist
	 * or are explicitly skipped.
	 * If dry-run mode is enabled, no actual database modifications occur.
	 * Instead, the potential insertions are logged for review.
	 *
	 * @since TBD
	 *
	 * @param array<int, array<string, mixed>> $categories An associative array where
	 *                                                     the key is the category ID and the value is an array of meta keys and values.
	 *
	 * @return void
	 */
	protected function insert_category_meta( array $categories ): void {
		$migrated_category_meta_count = 0;
		$migrated_category_count      = count( $categories );

		foreach ( $categories as $category_id => $meta_data ) {
			$category_meta = tribe( Event_Category_Meta::class )->set_term( $category_id );

			foreach ( $meta_data as $meta_key => $meta_value ) {
				if ( in_array( $meta_key, $this->skip_meta_keys, true ) ) {
					continue;
				}

				$existing_value = $category_meta->get( $meta_key );

				if ( empty( ! $existing_value ) ) {
					continue; // Skip if already exists.
				}
				++$migrated_category_meta_count;

				if ( $this->dry_run ) {
					$this->log_dry_run( $category_id, $meta_key, $meta_value );
				} else {
					$category_meta->set( $meta_key, $meta_value );
				}
			}

			if ( ! $this->dry_run ) {
				$category_meta->save(); // Batch save updates.
			}
		}
		$this->log_message( 'info', "Migrated {$migrated_category_meta_count} category meta values across {$migrated_category_count} categories.", [], 'Worker' );
	}

	/**
	 * Logs what would be inserted or updated in dry-run mode.
	 *
	 * @since TBD
	 *
	 * @param int    $category_id The category ID.
	 * @param string $meta_key    The meta key.
	 * @param mixed  $value       The value to be inserted.
	 *
	 * @return void
	 */
	protected function log_dry_run( int $category_id, string $meta_key, $value ): void {
		$this->log_message( 'info', "[DRY RUN] Would insert meta key '{$meta_key}' for category {$category_id} with value: " . wp_json_encode( $value, JSON_PRETTY_PRINT ), [], 'Worker' );
	}

	/**
	 * Logs dry-run statistics, summarizing the execution plan.
	 * If dry-run mode is enabled, this function outputs key execution details,
	 * such as category counts and meta keys being skipped.
	 *
	 * @since TBD
	 * @return void
	 */
	protected function dry_run_statistics(): void {
		if ( ! $this->dry_run ) {
			return;
		}

		$migration_data = $this->get_migration_data();

		$category_count = isset( $migration_data['categories'] ) ? count( $migration_data['categories'] ) : 0;
		$this->log_message( 'info', 'Dry Run Mode Active: No actual database modifications will be made.', [], 'Worker' );
		$this->log_message( 'info', "Total Categories to Process: {$category_count}", [], 'Worker' );
		$this->log_message( 'info', 'Skipped Meta Keys: ' . wp_json_encode( $this->skip_meta_keys ), [], 'Worker' );
	}

	/**
	 * Logs categories that already have the specified meta keys.
	 *
	 * @since TBD
	 *
	 * @param array<int, array<string, mixed>> $categories The categories and their meta data.
	 *
	 * @return void
	 */
	protected function log_existing_meta( array $categories ): void {
		foreach ( $categories as $category_id => $meta_data ) {
			if ( ! is_array( $meta_data ) ) {
				continue;
			}

			foreach ( $meta_data as $meta_key => $meta_value ) {
				$existing_value = get_term_meta( $category_id, $meta_key, true );

				if ( '' !== $existing_value ) {
					$this->log_message( 'warning', "Category {$category_id} already has meta key '{$meta_key}' with value: " . wp_json_encode( $existing_value, JSON_PRETTY_PRINT ), [], 'Worker' );
				}
			}
		}
	}

	/**
	 * Inserts the migrated settings into the `tribe_events_calendar_options` option.
	 * Ensures existing settings are not overwritten and logs changes.
	 * Supports dry-run mode for safe testing.
	 *
	 * @since TBD
	 *
	 * @param array<string, mixed> $settings The settings to insert.
	 *
	 * @return void
	 */
	protected function insert_settings( array $settings ): void {
		if ( empty( $settings ) ) {
			$this->log_message( 'warning', 'No general settings found to migrate. Skipping settings update.', [], 'Worker' );
			return;
		}

		// Fetch existing settings.
		$existing_settings = get_option( Tribe__Events__Main::OPTIONNAME, [] );

		if ( ! is_array( $existing_settings ) ) {
			$this->log_message( 'error', 'Existing settings are not an array. Skipping migration to prevent corruption.', [], 'Worker' );
			return;
		}

		$new_settings     = [];
		$skipped_settings = [];

		foreach ( $settings as $key => $value ) {
			// Skip updating if the setting already exists.
			if ( array_key_exists( $key, $existing_settings ) ) {
				$skipped_settings[ $key ] = $existing_settings[ $key ];
				continue;
			}

			// Store new setting for update.
			$new_settings[ $key ] = $value;
		}

		if ( empty( $new_settings ) ) {
			$this->log_message( 'info', 'No new settings needed migration. All settings already exist.', [], 'Worker' );
			return;
		}

		if ( $this->dry_run ) {
			foreach ( $new_settings as $key => $value ) {
				$this->log_message( 'info', "[DRY RUN] Would update `tribe_events_calendar_options`: Setting '{$key}' => " . wp_json_encode( $value, JSON_PRETTY_PRINT ), [], 'Worker' );
			}
		} else {
			update_option( Tribe__Events__Main::OPTIONNAME, array_merge( $existing_settings, $new_settings ) );

			foreach ( $new_settings as $key => $value ) {
				$this->log_message( 'info', "Updated `tribe_events_calendar_options`: Setting '{$key}' => " . wp_json_encode( $value, JSON_PRETTY_PRINT ), [], 'Worker' );
			}
		}

		if ( ! empty( $skipped_settings ) ) {
			foreach ( $skipped_settings as $key => $existing_value ) {
				$this->log_message( 'info', "Skipped updating setting '{$key}' (already exists) => " . wp_json_encode( $existing_value, JSON_PRETTY_PRINT ), [], 'Worker' );
			}
		}
	}
}
