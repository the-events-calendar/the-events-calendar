<?php
/**
 * Executes the full category color migration process.
 * Orchestrates preprocessing, validation, execution, and post-processing steps.
 *
 * @since 6.14.0
 *
 * @package TEC\Events\Category_Colors\Migration\Processors
 */

namespace TEC\Events\Category_Colors\Migration\Processors;

use TEC\Events\Category_Colors\Event_Category_Meta;
use TEC\Events\Category_Colors\Migration\Config;
use TEC\Events\Category_Colors\Migration\Status;
use Tribe__Events__Main;
use WP_Error;

/**
 * Class Worker
 * Controls the entire migration lifecycle for category colors.
 * Runs preprocessing, validation, execution, and post-processing in sequence.
 * Ensures data integrity and logs potential issues during migration.
 *
 * @since 6.14.0
 *
 * @package TEC\Events\Category_Colors\Migration\Processors
 */
class Worker extends Abstract_Migration_Step {

	/**
	 * Default batch size for processing categories.
	 *
	 * @since 6.14.0
	 * @var int
	 */
	const BATCH_SIZE = 50;

	/**
	 * List of meta keys that should be skipped (not inserted as term meta).
	 *
	 * @since 6.14.0
	 * @var array<string>
	 */
	protected const SKIP_META_KEYS = [
		'taxonomy_id',
	];

	/**
	 * Determines if the migration step should run.
	 *
	 * @since 6.14.0
	 *
	 * @return bool True if the step is ready to run, false otherwise.
	 */
	public function is_runnable(): bool {
		$status = Status::get_migration_status();

		return Status::$validation_completed === $status['status'];
	}

	/**
	 * Process the execution step.
	 *
	 * @since 6.14.0
	 *
	 * @return bool|WP_Error True on success, WP_Error on failure.
	 */
	public function process() {
		try {
			$start_time = microtime( true );

			// Ensure we're in a valid state to run.
			if ( ! $this->validate_execution_state() ) {
				return false;
			}

			$this->update_migration_status( Status::$execution_in_progress );
			do_action( 'tec_events_category_colors_migration_runner_start', $this->dry_run );

			// Get and validate processing data.
			$processing_data = $this->get_processing_data();
			if ( ! $this->validate_processing_data( $processing_data ) ) {
				return false;
			}

			// Process settings if they haven't been processed yet.
			if ( ! empty( $processing_data['settings'] ) ) {
				$result = $this->process_settings( $processing_data['settings'] );
				if ( is_wp_error( $result ) ) {
					return $result;
				}
				// Remove settings from processing data after successful processing.
				unset( $processing_data['settings'] );
				update_option( Config::MIGRATION_PROCESSING_OPTION, $processing_data );
			}

			// Process categories if any remain.
			if ( ! empty( $processing_data['categories'] ) ) {
				$result = $this->process_categories( $processing_data );
				if ( is_wp_error( $result ) ) {
					return $result;
				}
			}

			$this->update_migration_status( Status::$execution_completed );
			do_action( 'tec_events_category_colors_migration_runner_end', true );

			$this->log_elapsed_time( 'Execution', $start_time );

			return true;
		} catch ( \Exception $e ) {
			$this->log_message( 'error', 'Unexpected error during migration: ' . $e->getMessage(), [], 'Worker' );
			$this->update_migration_status( Status::$execution_failed );
			do_action( 'tec_events_category_colors_migration_runner_end', false );

			return new WP_Error( 'migration_error', $e->getMessage() );
		}
	}

	/**
	 * Validates the current execution state.
	 *
	 * @since 6.14.0
	 *
	 * @return bool True if valid, false otherwise.
	 */
	protected function validate_execution_state(): bool {
		if ( Status::$validation_completed !== Status::get_migration_status()['status'] ) {
			$this->log_message( 'info', 'Validation not completed. Running validation before execution.', [], 'Worker' );

			$error = tribe( Validator::class )->process();
			if ( is_wp_error( $error ) ) {
				$this->log_message( 'error', 'Validation failed. Migration execution stopped.', [], 'Worker' );
				$this->update_migration_status( Status::$execution_failed );

				return false;
			}

			$this->log_message( 'info', 'Validation completed successfully. Proceeding to execution.', [], 'Worker' );
		}

		return true;
	}

	/**
	 * Validates the processing data.
	 *
	 * @since 6.14.0
	 *
	 * @param array<string, mixed> $processing_data The processing data to validate.
	 *
	 * @return bool True if valid, false otherwise.
	 */
	protected function validate_processing_data( array $processing_data ): bool {
		if ( empty( $processing_data['categories'] ) && empty( $processing_data['settings'] ) ) {
			$this->log_message( 'error', 'No data found for migration.', [], 'Worker' );
			$this->update_migration_status( Status::$execution_skipped );

			return false;
		}

		return true;
	}

	/**
	 * Processes categories in batches.
	 *
	 * @since 6.14.0
	 *
	 * @param array<string, mixed> $processing_data The processing data.
	 *
	 * @return bool|WP_Error True on success, WP_Error on failure.
	 */
	protected function process_categories( array &$processing_data ) {
		// Get the batch size from the class method.
		$batch_size = $this->get_batch_size();

		// Ensure batch size is a positive integer.
		$batch_size = max( 1, absint( $batch_size ) );

		// Take a batch of categories.
		$batch = array_slice( $processing_data['categories'], 0, $batch_size, true );

		// Process this batch.
		$error = $this->process_batch( $batch );
		if ( is_wp_error( $error ) ) {
			$this->log_message( 'error', 'Failed to process category batch.', [], 'Worker' );
			$this->update_migration_status( Status::$execution_failed );

			return $error;
		}

		// Remove processed categories from processing data.
		foreach ( $batch as $category_id => $_ ) {
			unset( $processing_data['categories'][ $category_id ] );
		}

		// Update the processing data.
		update_option( Config::MIGRATION_PROCESSING_OPTION, $processing_data );

		return true;
	}

	/**
	 * Process a batch of categories.
	 *
	 * @since 6.14.0
	 *
	 * @param array<int, array<string, mixed>> $categories The categories to process.
	 *
	 * @return bool|WP_Error True on success, WP_Error on failure.
	 */
	protected function process_batch( array $categories ) {
		// Get all terms at once for better performance.
		$term_ids = array_keys( $categories );
		$terms    = get_terms(
			[
				'taxonomy'   => 'tribe_events_cat',
				'include'    => $term_ids,
				'hide_empty' => false,
				'fields'     => 'ids',
			]
		);

		if ( is_wp_error( $terms ) ) {
			return $this->log_message( 'error', 'Failed to fetch categories: ' . $terms->get_error_message(), [], 'Worker' );
		}

		// Create a lookup array for faster validation.
		$valid_terms = array_flip( $terms );

		$migrated_category_meta_count = 0;
		$migrated_category_count      = count( $categories );

		$dry_run_summary = [
			'categories' => 0,
			'meta_keys'  => 0,
			'changes'    => [],
		];

		foreach ( $categories as $category_id => $meta_data ) {
			// Validate category exists using our lookup array.
			if ( ! isset( $valid_terms[ $category_id ] ) ) {
				$this->log_message( 'warning', "Category {$category_id} no longer exists. Skipping.", [], 'Worker' );
				continue;
			}

			// Validate meta_data is an array.
			if ( ! is_array( $meta_data ) ) {
				$this->log_message( 'warning', "Invalid meta data for category {$category_id}. Skipping.", [], 'Worker' );
				continue;
			}

			try {
				$category_meta = tribe( Event_Category_Meta::class )->set_term( $category_id );

				$meta_keys_to_process = array_filter(
					array_keys( $meta_data ),
					fn( $key ) => ! in_array( $key, self::SKIP_META_KEYS, true )
				);

				foreach ( $meta_keys_to_process as $meta_key ) {
					$existing_value = $category_meta->get( $meta_key );

					if ( ! empty( $existing_value ) ) {
						continue;
					}
					++$migrated_category_meta_count;

					if ( $this->dry_run ) {
						++$dry_run_summary['categories'];
						$dry_run_summary['meta_keys'] += $migrated_category_meta_count;
						$dry_run_summary['changes'][]  = [
							'category_id' => $category_id,
							'meta_keys'   => array_keys( $meta_data ),
						];
						$this->log_message( 'info', "[DRY RUN] Would insert meta key '{$meta_key}' for category {$category_id} with value: " . wp_json_encode( $meta_data[ $meta_key ], JSON_PRETTY_PRINT ), [], 'Worker' );
					} else {
						$result = $category_meta->set( $meta_key, $meta_data[ $meta_key ] );
						if ( is_wp_error( $result ) ) {
							return $this->log_message( 'error', "Failed to insert meta '{$meta_key}' for category {$category_id}.", [], 'Worker' );
						}
					}
				}

				if ( ! $this->dry_run ) {
					$category_meta->save();
				}
			} catch ( \InvalidArgumentException $e ) {
				return $this->log_message( 'error', "Failed to process category {$category_id}: {$e->getMessage()}", [], 'Worker' );
			}
		}

		$this->log_message( 'info', "Migrated {$migrated_category_meta_count} category meta values across {$migrated_category_count} categories.", [], 'Worker' );

		if ( $this->dry_run ) {
			$this->log_dry_run_summary( $dry_run_summary );
		}

		return true;
	}

	/**
	 * Processes settings.
	 *
	 * @since 6.14.0
	 *
	 * @param array<string, mixed> $settings The settings to process.
	 *
	 * @return bool|WP_Error True on success, WP_Error on failure.
	 */
	protected function process_settings( array $settings ) {
		if ( empty( $settings ) ) {
			$this->log_message( 'warning', 'No general settings found to migrate. Skipping settings update.', [], 'Worker' );

			return true;
		}

		$existing_settings = get_option( Tribe__Events__Main::OPTIONNAME, [] );

		if ( ! is_array( $existing_settings ) ) {
			return $this->log_message( 'error', 'Existing settings are not an array. Skipping migration to prevent corruption.', [], 'Worker' );
		}

		$new_settings     = [];
		$skipped_settings = [];

		foreach ( $settings as $key => $value ) {
			if ( array_key_exists( $key, $existing_settings ) ) {
				$skipped_settings[ $key ] = $existing_settings[ $key ];
				continue;
			}

			$new_settings[ $key ] = $value;
		}

		if ( empty( $new_settings ) ) {
			$this->log_message( 'info', 'No new settings needed migration. All settings already exist.', [], 'Worker' );

			return true;
		}

		if ( $this->dry_run ) {
			foreach ( $new_settings as $key => $value ) {
				$this->log_message( 'info', "[DRY RUN] Would update `tribe_events_calendar_options`: Setting '{$key}' => " . wp_json_encode( $value, JSON_PRETTY_PRINT ), [], 'Worker' );
			}
		} else {
			$result = update_option( Tribe__Events__Main::OPTIONNAME, array_merge( $existing_settings, $new_settings ) );

			if ( ! $result ) {
				return $this->log_message( 'error', 'Failed to update `tribe_events_calendar_options`.', [], 'Worker' );
			}

			foreach ( $new_settings as $key => $value ) {
				$this->log_message( 'info', "Updated `tribe_events_calendar_options`: Setting '{$key}' => " . wp_json_encode( $value, JSON_PRETTY_PRINT ), [], 'Worker' );
			}
		}

		if ( ! empty( $skipped_settings ) ) {
			foreach ( $skipped_settings as $key => $existing_value ) {
				$this->log_message( 'info', "Skipped updating setting '{$key}' (already exists) => " . wp_json_encode( $existing_value, JSON_PRETTY_PRINT ), [], 'Worker' );
			}
		}

		return true;
	}

	/**
	 * Get the processing data.
	 *
	 * @since 6.14.0
	 *
	 * @return array<string, mixed> The processing data.
	 */
	public function get_processing_data(): array {
		return get_option( Config::MIGRATION_PROCESSING_OPTION, [] );
	}

	/**
	 * Logs dry-run statistics and existing meta data.
	 *
	 * @since 6.14.0
	 *
	 * @param array<string, mixed> $dry_run_summary The dry run summary data.
	 *
	 * @return void
	 */
	protected function log_dry_run_summary( array $dry_run_summary ): void {
		if ( ! $this->dry_run ) {
			return;
		}

		$migration_data = $this->get_migration_data();
		$category_count = isset( $migration_data['categories'] ) ? count( $migration_data['categories'] ) : 0;

		$this->log_message( 'info', 'Dry Run Mode Active: No actual database modifications will be made.', [], 'Worker' );
		$this->log_message( 'info', "Total Categories to Process: {$category_count}", [], 'Worker' );
		$this->log_message( 'info', 'Skipped Meta Keys: ' . wp_json_encode( self::SKIP_META_KEYS ), [], 'Worker' );

		if ( ! empty( $dry_run_summary['changes'] ) ) {
			$this->log_message( 'info', 'Categories that would be modified:', [], 'Worker' );
			foreach ( $dry_run_summary['changes'] as $change ) {
				$this->log_message( 'info', "Category {$change['category_id']}: " . implode( ', ', $change['meta_keys'] ), [], 'Worker' );
			}
		}
	}

	/**
	 * Get the batch size for processing categories.
	 *
	 * @since 6.14.0
	 *
	 * @return int The number of categories to process in each batch.
	 */
	public function get_batch_size(): int {
		/**
		 * Determines the number of categories to process in each batch.
		 *
		 * @since 6.14.0
		 *
		 * @param int $batch_size The number of categories to process at once.
		 *
		 * @return int The validated batch size.
		 */
		return apply_filters( 'tec_events_category_colors_migration_batch_size', self::BATCH_SIZE );
	}

	/**
	 * Get the total number of categories to process.
	 *
	 * @since 6.14.0
	 *
	 * @return int The total number of categories.
	 */
	public function get_total_categories(): int {
		$processing_data = $this->get_migration_data();

		return isset( $processing_data['categories'] ) ? count( $processing_data['categories'] ) : 0;
	}

	/**
	 * Get the number of remaining categories to process.
	 *
	 * @since 6.14.0
	 *
	 * @return int The number of remaining categories.
	 */
	public function get_remaining_categories(): int {
		$processing_data = $this->get_processing_data();

		return isset( $processing_data['categories'] ) ? count( $processing_data['categories'] ) : 0;
	}
}
