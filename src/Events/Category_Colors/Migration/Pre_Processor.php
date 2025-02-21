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

use TEC\Events\Category_Colors\Event_Category_Meta;

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
		$this->update_migration_status( 'in_progress' ); // Set migration to in_progress.

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
			'legend'        => $this->get_legend_values(),
			'general'       => $this->get_general_settings(),
			'ignored_terms' => $this->process_ignored_terms(),
		];

		// Store processed data in the database.
		$this->update_migration_data( $migration_data );

		$this->update_migration_status( 'preprocess_completed' ); // Mark as completed.

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
	 * Extracts legend-related values and removes them from processed settings.
	 *
	 * @since TBD
	 * @return array<string, mixed> Processed legend data.
	 */
	protected function get_legend_values(): array {
		$legend_data = array_intersect_key( $this->processed_settings, array_flip( $this->legend_keys ) );

		// Ensure valid values only.
		foreach ( $legend_data as $key => $value ) {
			if ( ! is_scalar( $value ) && ! is_array( $value ) ) {
				unset( $legend_data[ $key ] );
			}
		}

		$this->processed_settings = array_diff_key( $this->processed_settings, $legend_data );

		return $legend_data;
	}

	/**
	 * Extracts general settings and removes them from processed settings.
	 *
	 * @since TBD
	 * @return array<string, mixed> Extracted general settings.
	 */
	protected function get_general_settings(): array {
		$general_data = array_intersect_key( $this->processed_settings, array_flip( $this->general_settings_keys ) );

		foreach ( $this->general_settings_keys as $key ) {
			if ( ! array_key_exists( $key, $general_data ) ) {
				$general_data[ $key ] = '';
			}
		}

		foreach ( $general_data as $key => $value ) {
			if ( ! is_scalar( $value ) && ! is_array( $value ) ) {
				unset( $general_data[ $key ] );
			}
		}

		$this->processed_settings = array_diff_key( $this->processed_settings, $general_data );

		return $general_data;
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
