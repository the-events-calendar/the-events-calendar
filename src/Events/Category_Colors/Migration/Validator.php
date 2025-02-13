<?php
/**
 * Handles validation of category color migration data.
 * This class ensures that the processed migration data is correctly formatted,
 * contains required fields, and aligns with expected data structures.
 *
 * @since   TBD
 * @package TEC\Events\Category_Colors\Migration
 */

namespace TEC\Events\Category_Colors\Migration;

/**
 * Class Validator
 * Validates the migration data before execution to prevent incorrect or incomplete imports.
 * Checks data structure, required fields, existing categories, and unexpected meta keys.
 *
 * @since TBD
 */
class Validator {
	use Migration_Trait;

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
		$this->update_migration_status( 'validation_in_progress' ); // Set migration status to validation started.

		/**
		 * Fires before the validation process begins.
		 * Allows external systems to hook in and modify data before validation runs.
		 *
		 * @since TBD
		 */
		do_action( 'tec_events_category_colors_migration_validator_start' );

		$migration_data = $this->get_migration_data();

		$this->validate_structure( $migration_data );
		$this->validate_category_existence( $migration_data['categories'] ?? [] );
		$this->validate_random_keys();
		$this->detect_unrecognized_keys( $migration_data );
		$this->check_required_fields( $migration_data );
		$this->validate_meta_keys( $migration_data['categories'] ?? [] );

		$validation_passed = empty( Logger::get_logs( 'error' ) );

		if ( ! $validation_passed ) {
			Logger::log( 'error', 'Validation encountered errors. Stopping further processing.' );
			$this->update_migration_status( 'validation_failed' ); // Mark validation as failed.
			return false;
		}

		$this->update_migration_status( 'validation_completed' ); // Mark validation as completed.

		/**
		 * Fires after the validation process completes.
		 *
		 * @since TBD
		 *
		 * @param bool $success True if validation passed, false otherwise.
		 */
		do_action( 'tec_events_category_colors_migration_validator_end', true );

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
		foreach ( $this->expected_structure as $key => $_ ) {
			if ( ! isset( $migration_data[ $key ] ) || ! is_array( $migration_data[ $key ] ) ) {
				Logger::log( 'error', "Invalid or missing key: '{$key}' in migration data." );
			}
		}

		if ( isset( $migration_data['ignored_terms'] ) && ! is_array( $migration_data['ignored_terms'] ) ) {
			Logger::log( 'error', "'ignored_terms' should be an array, found " . gettype( $migration_data['ignored_terms'] ) . '.' );
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
			Logger::log( 'error', 'Error fetching existing categories: ' . $valid_categories->get_error_message() );

			return;
		}

		$valid_categories = array_map( 'intval', $valid_categories );

		foreach ( $categories as $category_id => $_ ) {
			if ( ! in_array( (int) $category_id, $valid_categories, true ) ) {
				Logger::log( 'warning', "Category with ID {$category_id} does not exist in the taxonomy. Skipping." );
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
			Logger::log( 'error', 'Original settings are empty, cannot validate migration.' );

			return;
		}

		$random_keys = array_rand( $original_settings, min( $this->validation_sample_size, count( $original_settings ) ) );
		$random_keys = is_array( $random_keys ) ? $random_keys : [ $random_keys ];

		$migration_data = $this->get_migration_data();

		foreach ( $random_keys as $key ) {
			$category_id = $this->extract_category_id( $key );

			if ( null !== $category_id && ! isset( $migration_data['categories'][ $category_id ] ) ) {
				Logger::log( 'error', "Category '{$category_id}' is missing in migration data." );
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
					Logger::log( 'error', "Invalid meta key '{$key}' found in category '{$category_id}' migration data." );
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
				Logger::log( 'error', "Unexpected section found: '{$section}' in migration data." );
				continue;
			}
			if ( ! is_array( $values ) ) {
				Logger::log( 'error', "Invalid structure for section '{$section}'. Expected array, got " . gettype( $values ) . '.' );
			}
		}
	}

	/**
	 * Checks that required fields are present before importing.
	 *
	 * @since TBD
	 *
	 * @param array<string, mixed> $migration_data The migration data to check.
	 *
	 * @return void
	 */
	protected function check_required_fields( array $migration_data ): void {
		$required_keys = [
			'add_legend'  => 'legend',
			'reset_show'  => 'general',
			'font_weight' => 'general',
		];

		foreach ( $required_keys as $key => $section ) {
			if ( ! array_key_exists( $key, $migration_data[ $section ] ) ) {
				Logger::log( 'error', "Required setting '{$key}' is missing in migration data." );
			}
		}
	}
}
