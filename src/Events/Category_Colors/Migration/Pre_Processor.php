<?php
/**
 * Handles the preprocessing of category color migration data.
 * This class extracts, formats, and stores category-related settings
 * before they are validated and executed in the migration process.
 *
 * @since   TBD
 *
 * @package TEC\Events\Category_Colors\Migration
 */

namespace TEC\Events\Category_Colors\Migration;

/**
 * Class Pre_Processor
 * Prepares the migration data by extracting and formatting category settings.
 * This class ensures the settings are structured correctly before validation.
 *
 * @since TBD
 *
 * @package TEC\Events\Category_Colors\Migration
 */
class Pre_Processor {
	use Migration_Trait;

	/**
	 * A working copy of the settings, which gets modified during processing.
	 *
	 * @since TBD
	 * @var array<string, mixed>
	 */
	protected array $processed_settings = [];

	/**
	 * Processes category colors and settings for migration.
	 * Fires an action before and after processing.
	 * If the process completes successfully, the end hook passes `true`.
	 * If processing is skipped due to empty settings, the end hook passes `false`.
	 *
	 * @since TBD
	 * @return array<string, mixed> Processed settings and valid category colors.
	 */
	public function process(): array {
		$start_time = $this->start_timer();
		$this->update_migration_status( Migration_Status::$in_progress ); // Set migration to in_progress.

		/**
		 * Fires before the preprocessor starts processing category color data.
		 * Allows logging or hooking into the process before any changes are made.
		 *
		 * @since TBD
		 */
		do_action( 'tec_events_category_colors_migration_preprocessor_start' );

		// Load the original settings.
		$this->processed_settings = $this->get_original_settings();

		if ( empty( $this->processed_settings ) ) {
			$this->update_migration_data( $this->expected_structure );
			$this->update_migration_status( 'preprocess_skipped' ); // Migration skipped due to no data.

			/**
			 * Fires after the preprocessor completes.
			 *
			 * @since TBD
			 *
			 * @param array<string, mixed> $migration_data The processed migration data.
			 * @param bool                 $success        Whether processing was successful.
			 */
			do_action( 'tec_events_category_colors_migration_preprocessor_end', $this->expected_structure, false );
			$this->log_elapsed_time( 'Preprocessing', $start_time );
			return $this->expected_structure;
		}

		// Populate migration data.
		$migration_data = [
			'categories'    => $this->get_category_values(),
			'settings'      => $this->get_settings_values(),
			'ignored_terms' => $this->process_ignored_terms(),
		];

		// Store processed data in the database.
		$this->update_migration_data( $migration_data );

		$this->update_migration_status( Migration_Status::$preprocess_completed ); // Mark as completed.

		/**
		 * Fires after the preprocessor completes.
		 *
		 * @since TBD
		 *
		 * @param array<string, mixed> $migration_data The processed migration data.
		 * @param bool                 $success        Whether processing was successful.
		 */
		do_action( 'tec_events_category_colors_migration_preprocessor_end', $migration_data, true );

		Logger::log( 'info', 'Preprocessing complete. Migration data prepared.' );

		$this->log_elapsed_time( 'Preprocessing', $start_time );

		return $migration_data;
	}

	/**
	 * Extracts category-related values from processed settings and removes them.
	 *
	 * @since TBD
	 * @return array<int, array<string, mixed>> Processed category data structured by category ID.
	 */
	protected function get_category_values(): array {
		$categories        = [];
		$filtered_settings = $this->processed_settings; // Create a copy to modify.

		foreach ( $this->processed_settings['terms'] ?? [] as $term_id => [$slug, $name] ) {
			foreach ( array_keys( $filtered_settings ) as $key ) {
				if ( strpos( $key, $slug . '-' ) === 0 || strpos( $key, $slug . '_' ) === 0 ) {
					$field_name = str_replace( [ $slug . '-', $slug . '_' ], '', $key );
					$mapped_key = $this->get_mapped_meta_key( $field_name );

					if ( null !== $mapped_key ) {
						$meta_key = $this->meta_key_prefix . $mapped_key;
						$value    = $filtered_settings[ $key ];
						$value    = ( 'no_color' === $value ) ? '' : $value;
						// Store processed setting under category.
						$categories[ $term_id ][ $meta_key ] = $value;

						// Remove from copied array.
						unset( $filtered_settings[ $key ] );
					}
				}
			}

			// Store the term_id reference itself.
			$categories[ $term_id ]['taxonomy_id'] = $term_id;
		}

		// Replace the original array with the filtered one.
		$this->processed_settings = $filtered_settings;

		return $categories;
	}

	/**
	 * Extracts and maps settings while applying validation rules.
	 *
	 * @since TBD
	 * @return array<string, mixed> Processed settings with proper validation.
	 */
	protected function get_settings_values(): array {
		$mapped_settings = [];

		foreach ( $this->settings_mapping as $old_key => $mapping ) {
			if ( ! $mapping['import'] ) {
				continue;
			}
			// Skip if the key doesn't exist in the processed settings.
			if ( ! array_key_exists( $old_key, $this->processed_settings ) ) {
				continue;
			}

			$value   = $this->processed_settings[ $old_key ];
			$new_key = $mapping['mapped_key'];

			// Apply validation rules.
			switch ( $mapping['validation'] ) {
				case 'boolean':
					$value = filter_var( $value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE );
					if ( is_null( $value ) ) {
						$value = false;
					}
					break;
				case 'array':
					$value = is_array( $value ) ? $value : [];
					break;
				default:
					$value = is_scalar( $value ) ? $value : ''; // Default to empty if invalid.
			}

			if ( '' !== $value && [] !== $value ) { // Ignore empty values.
				$mapped_settings[ $new_key ] = $value;
			}

			// Remove the key from the processed settings after mapping.
			unset( $this->processed_settings[ $old_key ] );
		}

		return $mapped_settings;
	}

	/**
	 * Processes ignored terms data.
	 *
	 * @since TBD
	 * @return array<string, mixed> Processed ignored terms.
	 */
	protected function process_ignored_terms(): array {
		$ignored_terms = $this->processed_settings['ignored_terms'] ?? [];

		$ignored_terms = is_array( $ignored_terms ) ? $ignored_terms : [];

		unset( $this->processed_settings['ignored_terms'] );

		return $ignored_terms;
	}
}
