<?php

namespace TEC\Events\Category_Colors\Migration;

class Preprocessor {
	use Migration_Trait;

	/**
	 * Stores the original settings retrieved from the database (unchanged for reference).
	 *
	 * @var array<string, mixed>
	 */
	protected array $original_settings = [];

	/**
	 * A working copy of the settings, which gets modified during processing.
	 *
	 * @var array<string, mixed>
	 */
	protected array $processed_settings = [];

	/**
	 * Stores the extracted migration data.
	 *
	 * @var array<string, mixed>
	 */
	protected array $migration_data = [];

	/**
	 * List of original settings keys to remove after processing.
	 *
	 * @var array<string>
	 */
	protected array $original_keys_to_remove = [ 'terms', 'all_terms' ];

	/**
	 * Retrieves the original settings from the database.
	 * If already loaded, it returns the cached version.
	 *
	 * @return array<string, mixed> The original settings.
	 */
	public function get_original_settings(): array {
		if ( ! empty( $this->original_settings ) ) {
			return $this->original_settings;
		}

		// Load original settings and ensure it's an array.
		$this->original_settings = (array) get_option( 'teccc_options', [] );

		return $this->original_settings;
	}

	/**
	 * Processes category colors and settings for migration.
	 *
	 * @return array<string, mixed> Processed settings and valid category colors.
	 */
	public function process(): array {
		$this->original_settings  = $this->get_original_settings();
		$this->processed_settings = $this->original_settings;

		if ( empty( $this->processed_settings ) ) {
			$this->processed_settings = [];

			return $this->migration_data;
		}

		// Populate migration data.
		$this->migration_data                  = $this->expected_structure;
		$this->migration_data['categories']    = $this->get_category_values();
		$this->migration_data['legend']        = $this->get_legend_values();
		$this->migration_data['general']       = $this->get_general_settings();
		$this->migration_data['ignored_terms'] = $this->process_ignored_terms();

		return $this->migration_data;
	}

	/**
	 * Removes unnecessary keys from processed settings.
	 *
	 * @return void
	 */
	protected function remove_original_keys(): void {
		$this->processed_settings = array_diff_key(
			$this->processed_settings,
			array_flip( $this->original_keys_to_remove )
		);
	}

	/**
	 * Extracts category-related values and removes them from processed settings.
	 *
	 * @return array<int, array<string, mixed>> Processed category data structured by category ID.
	 */
	protected function get_category_values(): array {
		$category_keys = array_filter(
			array_keys( $this->processed_settings ),
			fn( $key ) => preg_match( $this->category_regex, $key )
		);

		$filtered_settings = array_intersect_key( $this->processed_settings, array_flip( $category_keys ) );

		foreach ( $filtered_settings as $key => $_ ) {
			unset( $this->processed_settings[ $key ] );
		}

		return array_reduce(
			array_keys( $filtered_settings ),
			function ( $result, $key ) use ( $filtered_settings ) {
				$category_id = $this->extract_category_id( $key );

				if ( null === $category_id ) {
					return $result;
				}

				preg_match( $this->category_regex, $key, $matches );
				$field_name = $matches[2] ?? '';

				// Ensure category key exists and add the taxonomy_id for reference.
				if ( ! isset( $result[ $category_id ] ) ) {
					$result[ $category_id ]['taxonomy_id'] = $category_id;
				}

				// Get mapped meta key.
				$mapped_key = $this->get_mapped_meta_key( $field_name );

				// Only store allowed meta keys.
				if ( null !== $mapped_key ) {
					$result[ $category_id ][ $this->meta_key_prefix . $mapped_key ] =
						( 'no_color' === $filtered_settings[ $key ] ) ? '' : $filtered_settings[ $key ];
				}

				return $result;
			},
			[]
		);
	}

	/**
	 * Extracts legend-related values and removes them from processed settings.
	 *
	 * @return array<string, mixed> Processed legend data.
	 */
	protected function get_legend_values(): array {
		$legend_data = array_intersect_key( $this->processed_settings, array_flip( $this->legend_keys ) );

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
	 * @return array<string, mixed> Processed ignored terms.
	 */
	protected function process_ignored_terms(): array {
		$ignored_terms = $this->processed_settings['ignored_terms'] ?? [];

		$ignored_terms = is_array( $ignored_terms ) ? $ignored_terms : [];

		unset( $this->processed_settings['ignored_terms'] );

		return $ignored_terms;
	}
}
