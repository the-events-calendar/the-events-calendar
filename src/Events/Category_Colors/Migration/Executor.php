<?php

namespace TEC\Events\Category_Colors\Migration;

class Executor {

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
	 * The original settings before migration.
	 *
	 * @var array<string, mixed>
	 */
	protected array $original_settings = [];

	/**
	 * The migration data after processing.
	 *
	 * @var array<string, mixed>
	 */
	protected array $migration_data = [];

	/**
	 * Stores valid category IDs for quick lookup.
	 *
	 * @var array<int, bool>
	 */
	protected array $valid_categories = [];

	/**
	 * Executor constructor.
	 *
	 * This ensures the migration data is validated before execution,
	 * preventing accidental execution with invalid data.
	 *
	 * @param array<string, mixed> $original_settings The original settings before migration.
	 * @param array<string, mixed> $migration_data    The migration data after processing.
	 * @param bool                 $dry_run           Whether to run in dry-run mode.
	 */
	public function __construct( array $original_settings, array $migration_data, bool $dry_run = false ) {
		$this->original_settings = $original_settings;
		$this->migration_data    = $migration_data;
		$this->dry_run           = $dry_run;
	}

	/**
	 * Executes the migration process for categories.
	 *
	 * @return void
	 */
	public function execute(): void {
		if ( empty( $this->migration_data['categories'] ) ) {
			$this->log_error( 'No categories found for migration.' );
			return;
		}

		// Step 1: Validate the data before running the execution
		$validator = new Validator( $this->original_settings, $this->migration_data );

		if ( ! $validator->validate() ) {
			$this->errors = array_merge( $this->errors, $validator->get_errors() );
			$this->warnings = array_merge( $this->warnings, $validator->get_warnings() );

			$this->log_error( 'Validation failed. Migration execution stopped.' );
			return; // Do not proceed with execution if validation fails.
		}

		// Step 2: Validate existing categories before processing
		$this->validate_existing_categories();

		// Step 3: Insert meta values only for valid categories
		$this->insert_categories();
	}

	/**
	 * Validates if categories exist before processing.
	 *
	 * @return void
	 */
	protected function validate_existing_categories(): void {
		$existing_terms = get_terms(
			[
				'taxonomy'   => 'tribe_events_cat',
				'fields'     => 'ids',
				'hide_empty' => false,
			]
		);

		// Ensure we got a valid result
		if ( is_wp_error( $existing_terms ) ) {
			$this->log_error( 'Failed to retrieve existing terms: ' . $existing_terms->get_error_message() );
			return;
		}

		$this->valid_categories = array_flip( $existing_terms ); // Convert to quick lookup array

		foreach ( $this->migration_data['categories'] as $category_id => $_ ) {
			if ( ! isset( $this->valid_categories[ $category_id ] ) ) {
				$this->log_warning( "Category with ID {$category_id} does not exist in the taxonomy. Skipping." );
			}
		}
	}

	/**
	 * Inserts meta values for each valid category.
	 *
	 * @return void
	 */
	protected function insert_categories(): void {
		foreach ( $this->migration_data['categories'] as $category_id => $meta_data ) {
			// Skip if category is invalid
			if ( ! isset( $this->valid_categories[ $category_id ] ) ) {
				continue;
			}

			foreach ( $meta_data as $meta_key => $meta_value ) {
				$mapped_key = $this->get_meta_key( $meta_key );

				// Skip unmapped keys.
				if ( empty( $mapped_key ) ) {
					continue;
				}

				// Construct full meta key.
				$meta_key_prefixed = "tec-events-cat-colors-{$mapped_key}";

				// Check existing meta.
				$existing_value = get_term_meta( $category_id, $meta_key_prefixed, true );

				if ( $existing_value !== '' ) {
					continue; // Skip if already exists.
				}

				if ( $this->dry_run ) {
					$this->log_dry_run( $category_id, $meta_key_prefixed, $meta_value );
				} else {
					add_term_meta( $category_id, $meta_key_prefixed, $meta_value, true );
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
		$this->warnings[] = "[DRY RUN] Would insert meta key '{$meta_key}' for category {$category_id} with value: " . print_r( $value, true );
	}

	/**
	 * Retrieves the mapped meta key for a field name.
	 *
	 * @param string $field_name The field name (e.g., 'border').
	 *
	 * @return string The mapped meta key (e.g., 'primary').
	 */
	protected function get_meta_key( string $field_name ): string {
		return $this->meta_key_map[ $field_name ] ?? ''; // Return empty string for unmapped keys
	}

	/**
	 * Retrieves execution errors.
	 *
	 * @return array<string> List of errors and warnings.
	 */
	public function get_errors(): array {
		return $this->errors;
	}

	/**
	 * Retrieves execution warnings.
	 *
	 * @return array<string> List of warnings.
	 */
	public function get_warnings(): array {
		return $this->warnings;
	}

	/**
	 * Logs a validation error.
	 *
	 * @param string $message The error message.
	 *
	 * @return void
	 */
	protected function log_error( string $message ): void {
		$this->errors[] = $message;
	}

	/**
	 * Logs a non-critical warning.
	 *
	 * @param string $message The warning message.
	 *
	 * @return void
	 */
	protected function log_warning( string $message ): void {
		$this->warnings[] = $message;
	}
}
