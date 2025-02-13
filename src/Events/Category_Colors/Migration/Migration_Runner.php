<?php

namespace TEC\Events\Category_Colors\Migration;

class Migration_Runner {

	use Migration_Trait;

	/**
	 * Stores any errors encountered during execution.
	 *
	 * @var array<string>
	 */
	protected array $errors = [];

	/**
	 * Stores warnings (non-critical issues that don't stop execution).
	 *
	 * @var array<string>
	 */
	protected array $warnings = [];

	/**
	 * Whether to perform a dry run (no actual DB modifications).
	 *
	 * @var bool
	 */
	protected bool $dry_run = false;

	/**
	 * List of meta keys that should be skipped (not inserted as term meta).
	 *
	 * @var array<string>
	 */
	protected array $skip_meta_keys = [
		'taxonomy_id',
	];

	/**
	 * Executor constructor.
	 * This ensures the migration data is validated before execution,
	 * preventing accidental execution with invalid data.
	 *
	 * @param bool $dry_run Whether to run in dry-run mode.
	 */
	public function __construct( bool $dry_run = false ) {
		$this->dry_run = $dry_run;
	}

	/**
	 * Executes the migration process for categories.
	 *
	 * This method retrieves migration data, validates it, and inserts meta values for categories.
	 * If dry-run mode is enabled, it logs execution details without modifying the database.
	 *
	 * Steps:
	 * 1. Logs dry-run statistics (if applicable).
	 * 2. Retrieves category migration data.
	 * 3. Validates the data before execution.
	 * 4. Logs existing meta data for reference.
	 * 5. Inserts meta values for categories.
	 *
	 * @return void
	 */
	public function execute(): void {
		$this->dry_run_statistics(); // Log dry-run details before execution starts.

		$migration_data = $this->get_migration_data();

		if ( empty( $migration_data['categories'] ) ) {
			Logger::log( 'error', 'No categories found for migration.' );

			return;
		}

		// Step 1: Validate the data before running the execution.
		$validator = new Validator();

		if ( ! $validator->validate() ) {
			$this->errors   = array_merge( $this->errors, $validator->get_errors() );
			$this->warnings = array_merge( $this->warnings, $validator->get_warnings() );

			Logger::log( 'error', 'Validation failed. Migration execution stopped.' );

			return;
		}

		$this->log_existing_meta( $migration_data['categories'] ); // Log existing category meta.

		// Step 2: Insert meta values for valid categories (already validated).
		$this->insert_categories( $migration_data['categories'] );
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
	protected function insert_categories( array $categories ): void {
		foreach ( $categories as $category_id => $meta_data ) {
			foreach ( $meta_data as $meta_key => $meta_value ) {
				// Skip any keys that should not be inserted.
				if ( in_array( $meta_key, $this->skip_meta_keys, true ) ) {
					continue;
				}

				// Check existing meta.
				$existing_value = get_term_meta( $category_id, $meta_key, true );

				if ( '' !== $existing_value ) {
					continue; // Skip if already exists.
				}

				if ( $this->dry_run ) {
					$this->log_dry_run( $category_id, $meta_key, $meta_value );
				} else {
					add_term_meta( $category_id, $meta_key, $meta_value, true );
				}
			}
		}
	}

	/**
	 * Logs what would be inserted or updated in dry-run mode.
	 *
	 * @param int    $category_id The category ID.
	 * @param string $meta_key    The meta key.
	 * @param mixed  $value       The value to be inserted.
	 *
	 * @return void
	 */
	protected function log_dry_run( int $category_id, string $meta_key, $value ): void {
		$this->warnings[] = "[DRY RUN] Would insert meta key '{$meta_key}' for category {$category_id} with value: " . wp_json_encode( $value, JSON_PRETTY_PRINT );
	}

	/**
	 * Logs dry-run statistics, summarizing the execution plan.
	 * If dry-run mode is enabled, this function outputs key execution details,
	 * such as category counts and meta keys being skipped.
	 *
	 * @return void
	 */
	protected function dry_run_statistics(): void {
		if ( ! $this->dry_run ) {
			return;
		}

		$migration_data = $this->get_migration_data();

		$category_count = isset( $migration_data['categories'] ) ? count( $migration_data['categories'] ) : 0;
		Logger::log( 'info', 'Dry Run Mode Active: No actual database modifications will be made.' );
		Logger::log( 'info', "Total Categories to Process: {$category_count}" );
		Logger::log( 'info', 'Skipped Meta Keys: ' . wp_json_encode( $this->skip_meta_keys ) );
		// Output more details if necessary.
	}

	/**
	 * Logs categories that already have the specified meta keys.
	 *
	 * @param array<int, array<string, mixed>> $categories The categories and their meta data.
	 *
	 * @return void
	 */
	protected function log_existing_meta( array $categories ): void {
		foreach ( $categories as $category_id => $meta_data ) {
			foreach ( $meta_data as $meta_key => $meta_value ) {
				$existing_value = get_term_meta( $category_id, $meta_key, true );

				if ( '' !== $existing_value ) {
					Logger::log( 'warning', "Category {$category_id} already has meta key '{$meta_key}' with value: " . wp_json_encode( $existing_value, JSON_PRETTY_PRINT ) );
				}
			}
		}
	}
}
