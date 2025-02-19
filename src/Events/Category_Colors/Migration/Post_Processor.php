<?php
/**
 * Handles post-processing validation for category color migration.
 * Ensures that all expected meta values exist and logs any inconsistencies.
 * This step is crucial to verify the success of the migration process.
 *
 * @since   TBD
 * @package TEC\Events\Category_Colors\Migration
 */

namespace TEC\Events\Category_Colors\Migration;

use TEC\Events\Category_Colors\Event_Category_Meta;

/**
 * Class Post_Processor
 * Validates migration execution results by checking if expected metadata
 * was correctly stored in the database. Logs missing or mismatched data.
 *
 * @since TBD
 */
class Post_Processor {

	use Migration_Trait;

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
	 * Whether to perform post-processing validation.
	 *
	 * @since TBD
	 * @var bool
	 */
	protected bool $dry_run = false;

	/**
	 * Constructor.
	 *
	 * @since TBD
	 *
	 * @param bool $dry_run Whether to skip post-processing in dry run mode.
	 */
	public function __construct( bool $dry_run = false ) {
		$this->dry_run = $dry_run;
	}

	/**
	 * Runs validation checks on migrated category meta data.
	 *
	 * @since TBD
	 * @return void
	 */
	public function verify_migration(): void {
		if ( $this->dry_run ) {
			Logger::log( 'info', 'Dry run mode active. Skipping post-processing validation.' );
			$this->update_migration_status( 'migration_completed' );

			return;
		}

		$migration_data = $this->get_migration_data();

		if ( empty( $migration_data['categories'] ) ) {
			Logger::log( 'warning', 'No migration data found. Cannot validate migration results.' );
			$this->update_migration_status( 'migration_failed' );

			return;
		}

		$errors_found = false;

		// Validate each category against expected migration data.
		foreach ( $migration_data['categories'] as $category_id => $meta_data ) {
			$category_meta = new Event_Category_Meta( $category_id );
			$actual_meta   = $category_meta->get(); // Fetch stored metadata.

			foreach ( $meta_data as $meta_key => $expected_value ) {
				// Skip validation for excluded meta keys.
				if ( in_array( $meta_key, $this->skip_meta_keys, true ) ) {
					continue;
				}

				$actual_value = $actual_meta[ $meta_key ] ?? null;

				if ( is_null( $actual_value ) ) {
					Logger::log( 'warning', "Missing meta key '{$meta_key}' for category ID {$category_id}." );
					$errors_found = true;
				} elseif ( $actual_value !== $expected_value ) {
					Logger::log( 'warning', "Mismatched value for '{$meta_key}' on category {$category_id}. Expected: " . wp_json_encode( $expected_value, JSON_PRETTY_PRINT ) . ' | Found: ' . wp_json_encode( $actual_value, JSON_PRETTY_PRINT ) );
					$errors_found = true;
				}
			}
		}

		if ( $errors_found ) {
			$this->update_migration_status( 'migration_failed' );
		} else {
			Logger::log( 'info', 'Migration verification successful. Marking migration as completed.' );
			$this->update_migration_status( 'migration_completed' );
		}
	}
}
