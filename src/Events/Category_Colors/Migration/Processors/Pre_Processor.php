<?php
/**
 * Handles the preprocessing of category color migration data.
 * This class extracts, formats, and stores category-related settings
 * before they are validated and executed in the migration process.
 *
 * @since 6.14.0
 *
 * @package TEC\Events\Category_Colors\Migration\Processors
 */

namespace TEC\Events\Category_Colors\Migration\Processors;

use TEC\Events\Category_Colors\Migration\Config;
use TEC\Events\Category_Colors\Migration\Status;

/**
 * Class Pre_Processor
 * Prepares the migration data by extracting and formatting category settings.
 * This class ensures the settings are structured correctly before validation.
 *
 * @since 6.14.0
 *
 * @package TEC\Events\Category_Colors\Migration\Processors
 */
class Pre_Processor extends Abstract_Migration_Step {

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
		return Status::$not_started === Status::get_migration_status()['status'];
	}

	/**
	 * A working copy of the settings, which gets modified during processing.
	 *
	 * @since 6.14.0
	 * @var array<string, mixed>
	 */
	protected array $processed_settings = [];

	/**
	 * Processes category colors and settings for migration.
	 * Fires an action before and after processing.
	 * If the process completes successfully, the end hook passes `true`.
	 * If processing is skipped due to empty settings, the end hook passes `false`.
	 *
	 * @since 6.14.0
	 * @return bool
	 */
	public function process(): bool {
		$start_time = microtime( true );
		$this->update_migration_status( Status::$in_progress );

		/**
		 * Fires before the preprocessor starts processing category color data.
		 * Allows logging or hooking into the process before any changes are made.
		 *
		 * @since 6.14.0
		 */
		do_action( 'tec_events_category_colors_migration_preprocessor_start' );

		// Load the original settings.
		$this->processed_settings = $this->get_original_settings();

		if ( empty( $this->processed_settings ) ) {
			$this->update_migration_data( Config::EXPECTED_STRUCTURE );
			$this->update_migration_status( Status::$preprocessing_skipped );

			/**
			 * Fires after the preprocessor completes.
			 *
			 * @since 6.14.0
			 *
			 * @param array<string, mixed> $migration_data The processed migration data.
			 * @param bool                 $success        Whether processing was successful.
			 */
			do_action( 'tec_events_category_colors_migration_preprocessor_end', Config::EXPECTED_STRUCTURE, false );
			$this->log_elapsed_time( 'Preprocessing', $start_time );

			return false;
		}

		// Validate terms data structure.
		if ( ! $this->validate_terms_structure() ) {
			$this->update_migration_data( Config::EXPECTED_STRUCTURE );
			$this->update_migration_status( Status::$preprocessing_skipped );
			do_action( 'tec_events_category_colors_migration_preprocessor_end', Config::EXPECTED_STRUCTURE, false );
			$this->log_elapsed_time( 'Preprocessing', $start_time );

			return false;
		}

		// Populate migration data.
		$migration_data = [
			'categories'    => $this->get_category_values(),
			'settings'      => $this->get_settings_values(),
			'ignored_terms' => $this->process_ignored_terms(),
		];

		// Store processed data in the database.
		$this->update_migration_data( $migration_data );

		// Initialize the processing data as a copy of the migration data.
		update_option( Config::MIGRATION_PROCESSING_OPTION, $migration_data );

		$this->update_migration_status( Status::$preprocessing_completed );

		/**
		 * Fires after the preprocessor completes.
		 *
		 * @since 6.14.0
		 *
		 * @param array<string, mixed> $migration_data The processed migration data.
		 * @param bool                 $success        Whether processing was successful.
		 */
		do_action( 'tec_events_category_colors_migration_preprocessor_end', $migration_data, true );

		$this->log_message( 'info', 'Preprocessing complete. Migration data prepared.', $migration_data, 'Pre_Processor' );

		$this->log_elapsed_time( 'Preprocessing', $start_time );

		return true;
	}

	/**
	 * Validates the structure of terms data.
	 *
	 * @since 6.14.0
	 * @return bool True if the terms data structure is valid, false otherwise.
	 */
	protected function validate_terms_structure(): bool {
		$terms = $this->processed_settings['all_terms'] ?? [];

		if ( ! is_array( $terms ) ) {
			$this->log_message( 'error', 'Terms data is not an array.', [], 'Pre_Processor' );

			return false;
		}

		$seen_term_ids = [];
		$seen_slugs    = [];
		foreach ( $terms as $term_id => $term_data ) {
			if ( ! is_array( $term_data ) || count( $term_data ) < 2 ) {
				$this->log_message( 'error', 'Invalid term data structure for term ID: ' . $term_id, $term_data, 'Pre_Processor' );

				return false;
			}

			// Check for duplicate term IDs.
			if ( isset( $seen_term_ids[ $term_id ] ) ) {
				$this->log_message( 'error', 'Duplicate term ID found: ' . $term_id, [], 'Pre_Processor' );

				return false;
			}
			$seen_term_ids[ $term_id ] = true;

			// Check for duplicate slugs.
			$slug = $term_data[0];
			if ( isset( $seen_slugs[ $slug ] ) ) {
				$this->log_message( 'error', 'Duplicate term slug found: ' . $slug, [], 'Pre_Processor' );

				return false;
			}
			$seen_slugs[ $slug ] = true;
		}

		return true;
	}

	/**
	 * Extracts category-related values from processed settings and removes them.
	 *
	 * @since 6.14.0
	 * @return array<int, array<string, mixed>> Processed category data structured by category ID.
	 */
	protected function get_category_values(): array {
		$categories        = [];
		$filtered_settings = $this->processed_settings;

		foreach ( $this->processed_settings['all_terms'] ?? [] as $term_id => [$slug, $name] ) {
			foreach ( array_keys( $filtered_settings ) as $key ) {
				if ( strpos( $key, $slug . '-' ) !== 0 && strpos( $key, $slug . '_' ) !== 0 ) {
					continue;
				}

				$field_name = str_replace( [ $slug . '-', $slug . '_' ], '', $key );
				$mapped_key = $this->get_mapped_meta_key( $field_name );

				if ( null === $mapped_key ) {
					continue;
				}

				$meta_key = Config::META_KEY_PREFIX . $mapped_key;
				$value    = $filtered_settings[ $key ];
				$value    = ( 'no_color' === $value ) ? '' : $value;

				// Store processed setting under category.
				$categories[ $term_id ][ $meta_key ] = $value;

				// Remove from copied array.
				unset( $filtered_settings[ $key ] );
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
	 * @since 6.14.0
	 * @return array<string, mixed> Processed settings with proper validation.
	 */
	protected function get_settings_values(): array {
		$mapped_settings = [];

		foreach ( Config::SETTINGS_MAPPING as $old_key => $mapping ) {
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
					if ( null === $value ) {
						$value = false;
					}
					// Convert boolean to string '1' or ''.
					$value                       = $value ? '1' : '';
					$mapped_settings[ $new_key ] = $value;
					break;
				case 'array':
					$value = is_array( $value ) ? $value : [];
					if ( [] !== $value ) {
						$mapped_settings[ $new_key ] = $value;
					}
					break;
				default:
					$value = is_scalar( $value ) ? $value : '';
					if ( '' !== $value ) {
						$mapped_settings[ $new_key ] = $value;
					}
			}

			// Remove the key from the processed settings after mapping.
			unset( $this->processed_settings[ $old_key ] );
		}

		return $mapped_settings;
	}

	/**
	 * Processes ignored terms data.
	 *
	 * @since 6.14.0
	 * @return array<string, mixed> Processed ignored terms.
	 */
	protected function process_ignored_terms(): array {
		$ignored_terms = $this->processed_settings['ignored_terms'] ?? [];

		$ignored_terms = is_array( $ignored_terms ) ? $ignored_terms : [];

		unset( $this->processed_settings['ignored_terms'] );

		return $ignored_terms;
	}
}
