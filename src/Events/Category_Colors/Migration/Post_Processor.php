<?php
/**
 * Handles post-processing validation for category color migration.
 * Ensures that all expected meta values exist and logs any inconsistencies.
 * This step is crucial to verify the success of the migration process.
 *
 * @since   TBD
 *
 * @package TEC\Events\Category_Colors\Migration
 */

namespace TEC\Events\Category_Colors\Migration;

use TEC\Events\Category_Colors\Event_Category_Meta;
use Tribe__Events__Main;

/**
 * Class Post_Processor
 * Validates migration execution results by checking if expected metadata
 * was correctly stored in the database. Logs missing or mismatched data.
 *
 * @since TBD
 *
 * @package TEC\Events\Category_Colors\Migration
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
		$start_time = $this->start_timer();
		if ( $this->dry_run ) {
			Logger::log( 'info', 'Dry run mode active. Skipping post-processing validation.' );
			$this->update_migration_status( Migration_Status::$postprocess_completed );
			$this->log_elapsed_time( 'Post Processor', $start_time );
			return;
		}

		$migration_data = $this->get_migration_data();

		if ( empty( $migration_data['categories'] ) ) {
			Logger::log( 'warning', 'No migration data found. Cannot validate migration results.' );
			$this->update_migration_status( 'migration_failed' );
			$this->log_elapsed_time( 'Post Processor', $start_time );
			return;
		}

		$errors_found = false;

		// Validate each category against expected migration data.
		foreach ( $migration_data['categories'] as $category_id => $meta_data ) {
			$category_meta = tribe( Event_Category_Meta::class )->set_term( $category_id );
			$actual_meta   = $category_meta->get(); // Fetch stored metadata.

			foreach ( $meta_data as $meta_key => $expected_value ) {
				// Skip validation for excluded meta keys.
				if ( in_array( $meta_key, $this->skip_meta_keys, true ) ) {
					continue;
				}

				$actual_value = $actual_meta[ $meta_key ] ?? null;

				if ( is_null( $actual_value ) ) {
					Logger::log( 'error', "Missing meta key '{$meta_key}' for category ID {$category_id}." );
					$errors_found = true;
				} elseif ( $actual_value !== $expected_value ) {
					Logger::log( 'error', "Mismatched value for '{$meta_key}' on category {$category_id}. Expected: " . wp_json_encode( $expected_value, JSON_PRETTY_PRINT ) . ' | Found: ' . wp_json_encode( $actual_value, JSON_PRETTY_PRINT ) );
					$errors_found = true;
				}
			}
		}

		if ( $errors_found ) {
			$this->update_migration_status( 'migration_failed' );
		} else {
			Logger::log( 'info', 'Migration verification successful. Marking migration as completed.' );
			$this->update_migration_status( Migration_Status::$postprocess_completed );
		}
		$this->log_elapsed_time( 'Post Processor', $start_time );
	}

	/**
	 * Runs validation checks on migrated settings in tribe_events_calendar_options.
	 *
	 * @since TBD
	 * @return void
	 */
	protected function verify_settings(): void {
		$start_time = $this->start_timer();

		if ( $this->dry_run ) {
			Logger::log( 'info', 'Dry run mode active. Skipping settings validation.' );

			return;
		}

		$existing_settings  = get_option( Tribe__Events__Main::OPTIONNAME, [] );
		$original_settings  = $this->get_original_settings();
		$migration_settings = $this->get_migration_data()['settings'] ?? [];

		if ( empty( $migration_settings ) ) {
			Logger::log( 'warning', 'No migrated settings found. Cannot validate settings.' );

			return;
		}

		$errors_found = false;

		foreach ( $this->settings_mapping as $old_key => $mapping ) {
			if ( ! $mapping['import'] ) {
				continue; // Skip non-imported keys.
			}

			$expected_key   = $mapping['mapped_key'];
			$expected_value = $migration_settings[ $expected_key ] ?? null;
			$actual_value   = $existing_settings[ $expected_key ] ?? null;
			$original_value = $original_settings[ $old_key ] ?? null;

			// 1️⃣ Check if the setting exists at all.
			if ( ! array_key_exists( $expected_key, $existing_settings ) ) {
				Logger::log( 'error', "Missing expected setting '{$expected_key}' in tribe_events_calendar_options." );
				$errors_found = true;
				continue;
			}

			// 2️⃣ Compare actual vs. migrated value.
			if ( $actual_value !== $expected_value ) {
				if ( $original_value === $actual_value ) {
					// It was already different before migration—log as info.
					Logger::log( 'info', "Setting '{$expected_key}' has a pre-existing value. Migration did not change it. Expected: " . wp_json_encode( $expected_value ) . ' | Found: ' . wp_json_encode( $actual_value ) );
				} else {
					// Migration changed it—log as a warning.
					Logger::log( 'warning', "Mismatch for '{$expected_key}'. Expected: " . wp_json_encode( $expected_value ) . ' | Found: ' . wp_json_encode( $actual_value ) );
				}
			}
		}

		if ( $errors_found ) {
			Logger::log( 'error', 'Migration settings validation failed.' );
			$this->update_migration_status( 'migration_failed' );
		} else {
			Logger::log( 'info', 'Migration settings successfully verified.' );
		}

		$this->log_elapsed_time( 'Settings Verification', $start_time );
	}
}
