<?php

namespace TEC\Events\Category_Colors\Migration;

class Validator {

	use Migration_Trait;

	/**
	 * The original settings before migration.
	 *
	 * @var array<string, mixed>
	 */
	protected array $original_settings = [];

	/**
	 * The migrated data to validate.
	 *
	 * @var array<string, mixed>
	 */
	protected array $migration_data = [];

	/**
	 * Number of random keys to validate.
	 *
	 * @var int
	 */
	protected int $validation_sample_size = 200;

	/**
	 * Stores validation errors that should stop execution.
	 *
	 * @var array<string>
	 */
	protected array $errors = [];

	/**
	 * Stores validation warnings that should be logged but skipped.
	 *
	 * @var array<string>
	 */
	protected array $warnings = [];

	/**
	 * Validator constructor.
	 *
	 * @param array<string, mixed> $original_settings The original settings before migration.
	 * @param array<string, mixed> $migration_data    The migration data after processing.
	 */
	public function __construct( array $original_settings, array $migration_data ) {
		$this->original_settings = $original_settings;
		$this->migration_data    = $migration_data;
	}

	/**
	 * Runs the full validation process.
	 *
	 * @return bool True if validation passes, false otherwise.
	 */
	public function validate(): bool {
		$this->validate_structure();
		$this->validate_category_existence();
		$this->validate_random_keys();
		$this->detect_unrecognized_keys();
		$this->check_required_fields();
		$this->validate_meta_keys();

		return empty( $this->errors );
	}

	/**
	 * Validates that the migration data structure matches expectations.
	 *
	 * @return void
	 */
	protected function validate_structure(): void {
		foreach ( $this->expected_structure as $key => $_ ) {
			if ( ! isset( $this->migration_data[ $key ] ) || ! is_array( $this->migration_data[ $key ] ) ) {
				$this->log_error( "Invalid or missing key: '{$key}' in migration data." );
			}
		}

		// Ensure `ignored_terms` is an array.
		if ( isset( $this->migration_data['ignored_terms'] ) && ! is_array( $this->migration_data['ignored_terms'] ) ) {
			$this->log_error( "'ignored_terms' should be an array, found " . gettype( $this->migration_data['ignored_terms'] ) . '.' );
		}
	}

	/**
	 * Validates that all categories in migration data exist.
	 *
	 * @return void
	 */
	protected function validate_category_existence(): void {
		// Fetch all existing category IDs for 'tribe_events_cat'.
		$valid_categories = get_terms( [
			'taxonomy'   => 'tribe_events_cat',
			'hide_empty' => false,
			'fields'     => 'ids', // Get only term IDs.
		] );

		if ( is_wp_error( $valid_categories ) ) {
			$this->log_error( 'Error fetching existing categories: ' . $valid_categories->get_error_message() );
			return;
		}

		// Convert to an array of integers for easy lookup.
		$valid_categories = array_map( 'intval', $valid_categories );

		foreach ( $this->migration_data['categories'] as $category_id => $_ ) {
			if ( ! in_array( (int) $category_id, $valid_categories, true ) ) {
				$this->log_warning( "Category with ID {$category_id} does not exist in the taxonomy. Skipping." );
			}
		}
	}

	/**
	 * Validates a random sample of settings to ensure proper migration.
	 *
	 * @return void
	 */
	protected function validate_random_keys(): void {
		if ( empty( $this->original_settings ) ) {
			$this->log_error( 'Original settings are empty, cannot validate migration.' );
			return;
		}

		$random_keys = array_rand( $this->original_settings, min( $this->validation_sample_size, count( $this->original_settings ) ) );
		$random_keys = is_array( $random_keys ) ? $random_keys : [ $random_keys ];

		foreach ( $random_keys as $key ) {
			$category_id = $this->extract_category_id( $key );

			if ( null !== $category_id && ! isset( $this->migration_data['categories'][ $category_id ] ) ) {
				$this->log_error( "Category '{$category_id}' is missing in migration data." );
			}
		}
	}

	/**
	 * Validates that meta keys in category data match the expected mapping.
	 *
	 * @return void
	 */
	protected function validate_meta_keys(): void {
		foreach ( $this->migration_data['categories'] as $category_id => $data ) {
			foreach ( $data as $key => $_ ) {
				// Ignore non-meta fields.
				if ( 'taxonomy_id' === $key ) {
					continue;
				}

				// Ensure key exists in the mapping.
				$expected_meta_keys = array_map(
					fn( $mapped ) => $this->meta_key_prefix . $mapped,
					$this->meta_key_map
				);

				if ( ! in_array( $key, $expected_meta_keys, true ) ) {
					$this->log_error( "Invalid meta key '{$key}' found in category '{$category_id}' migration data." );
				}
			}
		}
	}

	/**
	 * Detects unrecognized keys that should not be in migration data.
	 *
	 * @return void
	 */
	protected function detect_unrecognized_keys(): void {
		foreach ( $this->migration_data as $section => $values ) {
			if ( ! isset( $this->expected_structure[ $section ] ) ) {
				$this->log_error( "Unexpected section found: '{$section}' in migration data." );
				continue;
			}
			if ( ! is_array( $values ) ) {
				$this->log_error( "Invalid structure for section '{$section}'. Expected array, got " . gettype( $values ) . '.' );
			}
		}
	}

	/**
	 * Checks that required fields are present before importing.
	 *
	 * @return void
	 */
	protected function check_required_fields(): void {
		$required_keys = [
			'add_legend'  => 'legend',
			'reset_show'  => 'general',
			'font_weight' => 'general',
		];

		foreach ( $required_keys as $key => $section ) {
			if ( ! array_key_exists( $key, $this->migration_data[ $section ] ) ) {
				$this->log_error( "Required setting '{$key}' is missing in migration data." );
			}
		}
	}

	/**
	 * Logs a validation error (stops execution).
	 *
	 * @param string $message The error message.
	 *
	 * @return void
	 */
	protected function log_error( string $message ): void {
		$this->errors[] = $message;
	}

	/**
	 * Logs a validation warning (skips but logs).
	 *
	 * @param string $message The warning message.
	 *
	 * @return void
	 */
	protected function log_warning( string $message ): void {
		$this->warnings[] = $message;
	}

	/**
	 * Retrieves validation errors (blocking).
	 *
	 * @return array<string> List of validation errors.
	 */
	public function get_errors(): array {
		return $this->errors;
	}

	/**
	 * Retrieves validation warnings (skippable).
	 *
	 * @return array<string> List of validation warnings.
	 */
	public function get_warnings(): array {
		return $this->warnings;
	}
}
