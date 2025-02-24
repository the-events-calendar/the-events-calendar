<?php
/**
 * Handles validation of category color migration data.
 * This class ensures that the processed migration data is correctly formatted,
 * contains required fields, and aligns with expected data structures.
 *
 * @since   TBD
 *
 * @package TEC\Events\Category_Colors\Migration
 */

namespace TEC\Events\Category_Colors\Migration;

/**
 * Class Validator
 * Validates the migration data before execution to prevent incorrect or incomplete imports.
 * Checks data structure, required fields, existing categories, and unexpected meta keys.
 *
 * @since TBD
 *
 * @package TEC\Events\Category_Colors\Migration
 */
class Validator {
	use Utilities;

	/**
	 * Number of random keys to validate.
	 *
	 * @since TBD
	 * @var int
	 */
	protected int $validation_sample_size = 200;

	/**
	 * Runs the full validation process.
	 * Fires an action before and after validation.
	 * If validation fails, the end hook passes `false`.
	 * If validation passes, the end hook passes `true`.
	 *
	 * @since TBD
	 * @return bool True if validation passes, false otherwise.
	 */
	public function validate(): bool {
		Errors::clear_errors();
		$start_time = $this->start_timer();
		$this->update_migration_status( 'validation_in_progress' ); // Set migration status to validation started.

		/**
		 * Fires before the validation process begins.
		 * Allows external systems to hook in and modify data before validation runs.
		 *
		 * @since TBD
		 */
		do_action( 'tec_events_category_colors_migration_validator_start' );

		$migration_data = $this->get_migration_data();

		// Run validation steps in sequence, stopping if an error occurs.
		$validation_steps = [
			[ 'Structure Validation', fn() => $this->validate_structure( $migration_data ) ],
			[ 'Category Existence Validation', fn() => $this->validate_category_existence( $migration_data['categories'] ?? [] ) ],
			[ 'Random Keys Validation', fn() => $this->validate_random_keys() ],
			[ 'Unrecognized Keys Detection', fn() => $this->detect_unrecognized_keys( $migration_data ) ],
			[ 'Required Fields Check', fn() => $this->check_required_fields( $migration_data ) ],
			[ 'Meta Keys Validation', fn() => $this->validate_meta_keys( $migration_data['categories'] ?? [] ) ],
		];

		foreach ( $validation_steps as [$step_name, $validation_step] ) {
			if ( ! $this->run_validation_step( $validation_step, $step_name ) ) {
				$this->log_elapsed_time( 'Validation', $start_time );
				return false; // Stop execution if any validation step fails.
			}
		}

		$this->update_migration_status( Status::$validation_completed ); // Mark validation as completed.

		/**
		 * Fires after the validation process completes.
		 *
		 * @since TBD
		 *
		 * @param bool $success True if validation passed, false otherwise.
		 */
		do_action( 'tec_events_category_colors_migration_validator_end', true );
		$this->log_elapsed_time( 'Validation', $start_time );
		return true;
	}

	/**
	 * Runs a validation step and stops further execution if errors are logged.
	 *
	 * @since TBD
	 *
	 * @param callable $validation_step A function representing a validation step.
	 * @param string   $step_name       (Optional) The name of the validation step.
	 *
	 * @return bool True if no errors, false if errors were logged.
	 */
	protected function run_validation_step( callable $validation_step, string $step_name = 'Unknown Step' ): bool {
		$validation_step();

		if ( Errors::has_errors() ) {
			$this->log_message( 'error', "Validation failed at step: {$step_name}. Stopping further processing.", [], 'Validator' );
			$this->update_migration_status( 'validation_failed' );

			return false;
		}

		return true;
	}

	/**
	 * Validates that the migration data structure matches expectations.
	 *
	 * @since TBD
	 *
	 * @param array<string, mixed> $migration_data The migration data to check.
	 *
	 * @return void
	 */
	protected function validate_structure( array $migration_data ): void {
		if ( empty( $migration_data ) ) {
			$this->log_message( 'error', 'Migration contains no data.', $migration_data, 'Validator' );
			return;
		}
		if ( ! [ $migration_data['categories'] ] ) {
			$this->log_message( 'error', 'Migration Categories should be an array, found ' . gettype( $migration_data['categories'] ) . '.', [], 'Validator' );
			return;
		}
		foreach ( $this->expected_structure as $key => $_ ) {
			if ( ! isset( $migration_data[ $key ] ) || ! is_array( $migration_data[ $key ] ) ) {
				$this->log_message( 'error', "Invalid or missing key: '{$key}' in migration data.", [], 'Validator' );
				return;
			}
		}

		if ( isset( $migration_data['ignored_terms'] ) && ! is_array( $migration_data['ignored_terms'] ) ) {
			$this->log_message( 'error', "'ignored_terms' should be an array, found " . gettype( $migration_data['ignored_terms'] ) . '.', [], 'Validator' );
		}
	}

	/**
	 * Validates that all categories in migration data exist.
	 *
	 * @since TBD
	 *
	 * @param array<int, array<string, mixed>> $categories List of categories from migration data.
	 *
	 * @return void
	 */
	protected function validate_category_existence( array $categories ): void {
		$valid_categories = get_terms(
			[
				'taxonomy'   => $this->taxonomy,
				'hide_empty' => false,
				'fields'     => 'ids',
			]
		);

		if ( is_wp_error( $valid_categories ) ) {
			$this->log_message( 'error', 'Error fetching existing categories: ' . $valid_categories->get_error_message(), [], 'Validator' );

			return;
		}

		$valid_categories = array_map( 'intval', $valid_categories );

		foreach ( $categories as $category_id => $_ ) {
			if ( ! in_array( $category_id, $valid_categories, true ) ) {
				$this->log_message( 'warning', "Category with ID {$category_id} does not exist in the taxonomy. Skipping.", [], 'Validator' );
			}
		}
	}

	/**
	 * Validates a random sample of settings to ensure proper migration.
	 *
	 * @since TBD
	 * @return void
	 */
	protected function validate_random_keys(): void {
		$original_settings = $this->get_original_settings();

		if ( empty( $original_settings ) ) {
			$this->log_message( 'error', 'Original settings are empty, cannot validate migration.', [], 'Validator' );
			return;
		}

		$random_keys = array_rand( $original_settings, min( $this->validation_sample_size, count( $original_settings ) ) );
		$random_keys = is_array( $random_keys ) ? $random_keys : [ $random_keys ];

		$migration_data = $this->get_migration_data();

		foreach ( $random_keys as $key ) {
			$category_id = $this->extract_category_id( $key );

			if ( null !== $category_id && ! isset( $migration_data['categories'][ $category_id ] ) ) {
				$this->log_message( 'error', "Category '{$category_id}' is missing in migration data.", [], 'Validator' );
			}
		}
	}

	/**
	 * Validates that meta keys in category data match the expected mapping.
	 *
	 * @since TBD
	 *
	 * @param array<int, array<string, mixed>> $categories The categories array.
	 *
	 * @return void
	 */
	protected function validate_meta_keys( array $categories ): void {
		$expected_meta_keys = array_map(
			fn( $mapped ) => $this->meta_key_prefix . $mapped,
			$this->meta_key_map
		);

		foreach ( $categories as $category_id => $data ) {
			foreach ( $data as $key => $_ ) {
				if ( 'taxonomy_id' === $key ) {
					continue;
				}

				if ( ! in_array( $key, $expected_meta_keys, true ) ) {
					$this->log_message( 'error', "Invalid meta key '{$key}' found in category '{$category_id}' migration data.", [], 'Validator' );
				}
			}
		}
	}

	/**
	 * Detects unrecognized keys that should not be in migration data.
	 *
	 * @since TBD
	 *
	 * @param array<string, mixed> $migration_data The migration data to check.
	 *
	 * @return void
	 */
	protected function detect_unrecognized_keys( array $migration_data ): void {
		foreach ( $migration_data as $section => $values ) {
			if ( ! isset( $this->expected_structure[ $section ] ) ) {
				$this->log_message( 'error', "Unexpected section found: '{$section}' in migration data.", [], 'Validator' );
				continue;
			}
			if ( ! is_array( $values ) ) {
				$this->log_message( 'error', "Invalid structure for section '{$section}'. Expected array, got " . gettype( $values ) . '.', [], 'Validator' );
			}
		}
	}

	/**
	 * Checks that expected settings fields are present in the migration data.
	 *
	 * Logs a warning if any expected setting is missing, but does not fail validation.
	 *
	 * @since TBD
	 *
	 * @param array<string, mixed> $migration_data The migration data to check.
	 *
	 * @return void
	 */
	protected function check_required_fields( array $migration_data ): void {
		foreach ( $this->settings_mapping as $original_key => $mapped_data ) {
			$mapped_key = $mapped_data['mapped_key'] ?? null;

			if ( ! $mapped_key ) {
				continue; // Skip if no mapped key is defined.
			}

			// Check if the mapped key exists in any part of the migration data.
			$exists = false;
			foreach ( $migration_data as $section_data ) {
				if ( is_array( $section_data ) && array_key_exists( $mapped_key, $section_data ) ) {
					$exists = true;
					break;
				}
			}

			if ( ! $exists ) {
				$this->log_message( 'warning', "Expected setting '{$mapped_key}' is missing in migration data.", [], 'Validator' );
			}
		}
	}
}
