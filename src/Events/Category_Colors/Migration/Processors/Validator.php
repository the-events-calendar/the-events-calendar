<?php
/**
 * Handles validation of category color migration data.
 * This class ensures that the processed migration data is correctly formatted,
 * contains required fields, and aligns with expected data structures.
 *
 * @since 6.14.0
 *
 * @package TEC\Events\Category_Colors\Migration\Processors
 */

namespace TEC\Events\Category_Colors\Migration\Processors;

use TEC\Events\Category_Colors\Migration\Config;
use TEC\Events\Category_Colors\Migration\Status;
use WP_Error;
use Tribe__Events__Main;

/**
 * Class Validator
 * Validates the migration data before execution to prevent incorrect or incomplete imports.
 * Checks data structure, required fields, existing categories, and unexpected meta keys.
 *
 * @since 6.14.0
 *
 * @package TEC\Events\Category_Colors\Migration\Processors
 */
class Validator extends Abstract_Migration_Step {

	/**
	 * Number of random keys to validate.
	 *
	 * @since 6.14.0
	 * @var int
	 */
	protected const VALIDATION_SAMPLE_SIZE = 200;

	/**
	 * Determines whether the migration step is in a valid state to run.
	 *
	 * This method checks the current migration status and ensures the step
	 * should only execute if the migration has not already started.
	 *
	 * @since 6.14.0
	 *
	 * @return bool True if the migration step can run, false otherwise.
	 */
	public function is_runnable(): bool {
		return in_array( Status::get_migration_status()['status'], [ Status::$preprocessing_completed, Status::$validation_failed ], true );
	}

	/**
	 * Runs the full validation process.
	 * Fires an action before and after validation.
	 * If validation fails, the end hook passes `false`.
	 * If validation passes, the end hook passes `true`.
	 *
	 * @since 6.14.0
	 * @return bool True if validation passes, false otherwise.
	 */
	public function process(): bool {
		$start_time = microtime( true );
		$this->update_migration_status( Status::$validation_in_progress );

		/**
		 * Fires before the validation process begins.
		 *
		 * @since 6.14.0
		 */
		do_action( 'tec_events_category_colors_migration_validator_start' );

		$migration_data = $this->get_migration_data();

		// Run validation steps sequentially, stopping if an error occurs.
		$validation_steps = [
			[ 'Structure Validation', fn() => $this->validate_structure( $migration_data ) ],
			[ 'Category Existence Validation', fn() => $this->validate_category_existence( $migration_data['categories'] ?? [] ) ],
			[ 'Random Keys Validation', fn() => $this->validate_random_keys() ],
			[ 'Unrecognized Keys Detection', fn() => $this->detect_unrecognized_keys( $migration_data ) ],
			[ 'Required Fields Check', fn() => $this->check_required_fields( $migration_data ) ],
			[ 'Meta Keys Validation', fn() => $this->validate_meta_keys( $migration_data['categories'] ?? [] ) ],
			[ 'Settings Values Validation', fn() => $this->validate_settings_values( $migration_data['settings'] ?? [] ) ],
		];

		foreach ( $validation_steps as [$step_name, $validation_step] ) {
			$error = $this->run_validation_step( $validation_step, $step_name );
			if ( is_wp_error( $error ) ) {
				$this->log_elapsed_time( 'Validation', $start_time );
				$this->update_migration_status( Status::$validation_failed );

				return false;
			}
		}

		$this->update_migration_status( Status::$validation_completed );

		/**
		 * Fires after the validation process completes.
		 *
		 * @since 6.14.0
		 *
		 * @param bool $success True if validation passed, false otherwise.
		 */
		do_action( 'tec_events_category_colors_migration_validator_end', true );
		$this->log_elapsed_time( 'Validation', $start_time );

		return true;
	}

	/**
	 * Runs a validation step and stops further execution if an error occurs.
	 *
	 * @since 6.14.0
	 *
	 * @param callable $validation_step A function representing a validation step.
	 * @param string   $step_name       (Optional) The name of the validation step.
	 *
	 * @return bool|WP_Error True if successful, WP_Error if validation fails.
	 */
	protected function run_validation_step( callable $validation_step, string $step_name = 'Unknown Step' ) {
		$error = $validation_step();

		if ( is_wp_error( $error ) ) {
			$this->log_message( 'error', "Validation failed at step: {$step_name}. Stopping further processing.", [], 'Validator' );

			return $error;
		}

		return true;
	}

	/**
	 * Validates that the migration data structure matches expectations.
	 *
	 * @since 6.14.0
	 *
	 * @param array<string, mixed> $migration_data The migration data to check.
	 *
	 * @return true|WP_Error Returns WP_Error if validation fails.
	 */
	protected function validate_structure( array $migration_data ) {
		if ( empty( $migration_data ) ) {
			return $this->log_message( 'error', 'Migration contains no data.', $migration_data, 'Validator' );
		}

		foreach ( Config::EXPECTED_STRUCTURE as $key => $_ ) {
			if ( ! isset( $migration_data[ $key ] ) || ! is_array( $migration_data[ $key ] ) ) {
				return $this->log_message( 'error', "Invalid or missing key: '{$key}' in migration data.", [ $migration_data ], 'Validator' );
			}
		}

		return true;
	}

	/**
	 * Validates that all categories in migration data exist.
	 *
	 * @since 6.14.0
	 *
	 * @param array<int, array<string, mixed>> $categories List of categories from migration data.
	 *
	 * @return true|WP_Error Returns WP_Error if validation fails.
	 */
	protected function validate_category_existence( array $categories ) {
		$valid_categories = get_terms(
			[
				'taxonomy'   => Tribe__Events__Main::TAXONOMY,
				'hide_empty' => false,
				'fields'     => 'ids',
				'include'    => array_keys( $categories ),
			]
		);

		if ( is_wp_error( $valid_categories ) ) {
			return $this->log_message( 'error', 'Error fetching existing categories: ' . $valid_categories->get_error_message(), [], 'Validator' );
		}

		foreach ( $categories as $category_id => $_ ) {
			if ( ! in_array( $category_id, $valid_categories, true ) ) {
				$this->log_message( 'warning', "Category with ID {$category_id} does not exist in the taxonomy. Skipping.", [], 'Validator' );
			}
		}

		return true;
	}

	/**
	 * Validates a random sample of settings to ensure proper migration.
	 *
	 * If there are no migrated settings, the validation passes automatically.
	 *
	 * @since 6.14.0
	 * @return true|WP_Error Returns WP_Error if validation fails.
	 */
	protected function validate_random_keys() {
		$original_settings = $this->get_original_settings();
		$migrated_settings = $this->get_migration_data()['settings'] ?? [];

		// If there are no migrated settings, validation is not needed.
		if ( empty( $migrated_settings ) ) {
			$this->log_message( 'info', 'No migrated settings found. Skipping settings validation.', [], 'Validator' );
			return true;
		}

		// Ensure we have original settings to compare.
		if ( empty( $original_settings ) ) {
			$this->log_message( 'error', 'Original settings are empty, cannot validate migration. No need for migration.', [], 'Validator' );
			return false;
		}

		// Get a list of all setting keys and shuffle them to randomize selection.
		$sample_size = min( self::VALIDATION_SAMPLE_SIZE, count( $migrated_settings ) );
		$sample_keys = array_rand( $original_settings, $sample_size );

		foreach ( $sample_keys as $key ) {
			// If the key doesn't exist in migrated settings, it's fine—skip validation.
			if ( ! array_key_exists( $key, $migrated_settings ) ) {
				continue;
			}

			$original_value = $original_settings[ $key ] ?? null;
			$migrated_value = $migrated_settings[ $key ] ?? null;

			// Compare original and migrated values.
			if ( $original_value !== $migrated_value ) {
				$this->log_message( 'warning', "Mismatch for '{$key}'. Expected: " . wp_json_encode( $original_value ) . ' | Found: ' . wp_json_encode( $migrated_value ), [], 'Validator' );
				$this->log_message( 'error', 'Settings validation failed.', [], 'Validator' );
				return false;
			}
		}

		$this->log_message( 'info', 'Settings validation passed.', [], 'Validator' );
		return true;
	}

	/**
	 * Validates that meta keys in category data match the expected mapping.
	 *
	 * @since 6.14.0
	 *
	 * @param array<int, array<string, mixed>> $categories The categories array.
	 *
	 * @return true|WP_Error Returns WP_Error if validation fails.
	 */
	protected function validate_meta_keys( array $categories ) {
		if ( empty( $categories ) || ! is_array( $categories ) ) {
			return $this->log_message( 'error', 'Invalid or empty categories array provided for validation.', [], 'Validator' );
		}

		$expected_meta_keys = array_map(
			fn( $mapped ) => Config::META_KEY_PREFIX . $mapped,
			Config::META_KEY_MAP
		);

		foreach ( $categories as $category_id => $data ) {
			if ( empty( $data ) || ! is_array( $data ) ) {
				return $this->log_message( 'error', "Invalid data structure for category '{$category_id}'.", [], 'Validator' );
			}

			foreach ( $data as $key => $_ ) {
				if ( 'taxonomy_id' === $key ) {
					continue;
				}

				if ( ! in_array( $key, $expected_meta_keys, true ) ) {
					return $this->log_message( 'error', "Invalid meta key '{$key}' found in category '{$category_id}' migration data.", [], 'Validator' );
				}
			}
		}

		return true;
	}

	/**
	 * Detects unrecognized keys that should not be in migration data.
	 *
	 * @since 6.14.0
	 *
	 * @param array<string, mixed> $migration_data The migration data to check.
	 *
	 * @return true|WP_Error Returns WP_Error if unexpected keys are found.
	 */
	protected function detect_unrecognized_keys( array $migration_data ) {
		foreach ( $migration_data as $section => $values ) {
			if ( ! isset( Config::EXPECTED_STRUCTURE[ $section ] ) ) {
				return $this->log_message( 'error', "Unexpected section found: '{$section}' in migration data.", [], 'Validator' );
			}
			if ( ! is_array( $values ) ) {
				return $this->log_message( 'error', "Invalid structure for section '{$section}'. Expected array, got " . gettype( $values ) . '.', [], 'Validator' );
			}
		}

		return true;
	}

	/**
	 * Checks that expected settings fields are present in the migration data.
	 *
	 * Logs a warning if any expected setting is missing, but does not fail validation.
	 *
	 * @since 6.14.0
	 *
	 * @param array<string, mixed> $migration_data The migration data to check.
	 *
	 * @return true|WP_Error Returns WP_Error if critical fields are missing.
	 */
	protected function check_required_fields( array $migration_data ) {
		foreach ( Config::SETTINGS_MAPPING as $original_key => $mapped_data ) {
			$mapped_key = $mapped_data['mapped_key'] ?? null;

			if ( ! $mapped_key ) {
				continue;
			}

			// Check if the mapped key exists in any part of the migration data.
			$exists = array_reduce(
				$migration_data,
				fn( $exists, $section ) => $exists || ( is_array( $section ) && array_key_exists( $mapped_key, $section ) ),
				false
			);

			if ( ! $exists ) {
				return $this->log_message( 'warning', "Expected setting '{$mapped_key}' is missing in migration data.", [], 'Validator' );
			}
		}

		return true;
	}

	/**
	 * Validates that settings values match their expected types and formats.
	 *
	 * @since 6.14.0
	 *
	 * @param array<string, mixed> $settings The settings to validate.
	 *
	 * @return true|WP_Error Returns WP_Error if validation fails.
	 */
	protected function validate_settings_values( array $settings ) {
		foreach ( Config::SETTINGS_MAPPING as $original_key => $mapped_data ) {
			$mapped_key = $mapped_data['mapped_key'] ?? null;
			$validation = $mapped_data['validation'] ?? '';
			$import     = $mapped_data['import'] ?? false;

			// Skip if no mapped key or validation rule, or if not meant to be imported.
			if ( ! $mapped_key || ! $validation || ! $import ) {
				continue;
			}

			// Skip if the key doesn't exist in settings.
			if ( ! isset( $settings[ $mapped_key ] ) ) {
				continue;
			}

			$value = $settings[ $mapped_key ];

			switch ( $validation ) {
				case 'array':
					if ( ! is_array( $value ) ) {
						return $this->log_message( 'error', "Setting '{$mapped_key}' should be an array, got " . gettype( $value ) . '.', [], 'Validator' );
					}
					break;

				case 'boolean':
					if ( ! in_array( $value, [ '1', '' ], true ) ) {
						return $this->log_message( 'error', "Setting '{$mapped_key}' should be a boolean value ('1' or ''), got '{$value}'.", [], 'Validator' );
					}
					break;

				default:
					// Unknown validation rule, skip.
					break;
			}
		}

		return true;
	}
}
