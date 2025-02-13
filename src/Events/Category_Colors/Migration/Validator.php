<?php

namespace TEC\Events\Category_Colors\Migration;

class Validator {

	use Migration_Trait;

	/**
	 * Number of random keys to validate.
	 *
	 * @var int
	 */
	protected int $validation_sample_size = 200;

	/**
	 * Runs the full validation process.
	 *
	 * @return bool True if validation passes, false otherwise.
	 */
	public function validate(): bool {
		$migration_data = $this->get_migration_data();

		$this->validate_structure( $migration_data );
		$this->validate_category_existence( $migration_data['categories'] ?? [] );
		$this->validate_random_keys();
		$this->detect_unrecognized_keys( $migration_data );
		$this->check_required_fields( $migration_data );
		$this->validate_meta_keys( $migration_data['categories'] ?? [] );

		return empty( $this->errors );
	}

	/**
	 * Validates that the migration data structure matches expectations.
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
