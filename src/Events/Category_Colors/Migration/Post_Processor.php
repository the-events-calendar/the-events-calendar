<?php

namespace TEC\Events\Category_Colors\Migration;

/**
 * Handles post-processing checks after migration execution.
 * Validates that all expected meta values exist and logs any inconsistencies.
 *
 * @since TBD
 */
class Post_Processor {

	use Migration_Trait;

	/**
	 * List of meta keys that should be skipped (not inserted as term meta).
	 *
	 * @var array<string>
	 */
	protected array $skip_meta_keys = [
		'taxonomy_id',
	];

	/**
	 * Whether to perform post-processing validation.
	 *
	 * @var bool
	 */
	protected bool $dry_run = false;

	/**
	 * Constructor.
	 *
	 * @param bool $dry_run Whether to skip post-processing in dry run mode.
	 */
	public function __construct( bool $dry_run = false ) {
		$this->dry_run = $dry_run;
	}

	/**
	 * Runs validation checks on migrated category meta data in batches.
	 * This improves performance by retrieving all term meta at once
	 * instead of checking each category one by one.
	 *
	 * @return void
	 */
	public function verify_migration(): void {
		if ( $this->dry_run ) {
			Logger::log( 'info', 'Dry run mode active. Skipping post-processing validation.' );

			return;
		}

		$migration_data = $this->get_migration_data();

		if ( empty( $migration_data['categories'] ) ) {
			Logger::log( 'warning', 'No migration data found. Cannot validate migration results.' );

			return;
		}

		$category_ids = array_keys( $migration_data['categories'] );

		if ( empty( $category_ids ) ) {
			Logger::log( 'warning', 'No category IDs found for validation.' );

			return;
		}

		// Fetch all term meta for these categories in one query.
		global $wpdb;
		$placeholders = implode( ',', array_fill( 0, count( $category_ids ), '%d' ) );

		$query = $wpdb->prepare(
			"SELECT term_id, meta_key, meta_value FROM {$wpdb->termmeta} WHERE term_id IN ($placeholders)",
			...$category_ids
		);

		$results = $wpdb->get_results( $query, ARRAY_A );

		// Reformat results into an associative array for faster lookups.
		$term_meta_map = [];

		foreach ( $results as $row ) {
			$term_meta_map[ $row['term_id'] ][ $row['meta_key'] ] = maybe_unserialize( $row['meta_value'] );
		}

		// Keep track of logged meta keys to prevent duplicate logging.
		$logged_skipped_meta_keys = [];

		// Validate each category against expected migration data.
		foreach ( $migration_data['categories'] as $category_id => $meta_data ) {
			foreach ( $meta_data as $meta_key => $expected_value ) {
				// Skip validation for excluded meta keys, but log only once.
				if ( in_array( $meta_key, $this->skip_meta_keys, true ) ) {
					if ( ! isset( $logged_skipped_meta_keys[ $meta_key ] ) ) {
						Logger::log( 'info', "PostProcessing: Skipping validation for meta key '{$meta_key}'." );
						$logged_skipped_meta_keys[ $meta_key ] = true;
					}
					continue;
				}

				$actual_value = $term_meta_map[ $category_id ][ $meta_key ] ?? null;

				if ( is_null( $actual_value ) ) {
					Logger::log( 'warning', "Missing meta key '{$meta_key}' for category ID {$category_id}." );
				} elseif ( $actual_value !== $expected_value ) {
					Logger::log( 'warning', "Mismatched value for '{$meta_key}' on category {$category_id}. Expected: " . wp_json_encode( $expected_value, JSON_PRETTY_PRINT ) . ' | Found: ' . wp_json_encode( $actual_value, JSON_PRETTY_PRINT ) );
				}
			}
		}
	}

}
