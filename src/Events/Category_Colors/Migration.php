<?php

namespace TEC\Events\Category_Colors;

use Tribe__Events__Main;

class Migration {

	/**
	 * Maps old category field names to new ones.
	 *
	 * @var array<string, string|null>
	 */
	protected array $category_field_mapping = [
		'border'          => 'border_color',
		'background'      => 'background_color',
		'text'            => 'text_color',
		'border_none'     => null,
		'background_none' => null,
		'hide'            => null,
	];

	/**
	 * Maps global settings keys.
	 *
	 * @var array<string, string>
	 */
	protected array $settings_mapping = [
		'add_legend'               => 'add_legend',
		'show_ignored_cats_legend' => 'show_ignored_cats_legend',
		'custom_legend_css'        => 'custom_legend_css',
		'reset_show'               => 'reset_show',
		'legend_superpowers'       => 'legend_superpowers',
	];

	/**
	 * Stores logs for debugging.
	 *
	 * @var array<string, mixed>
	 */
	protected array $log_data = [
		'raw_category_keys'      => [], // All category keys processed.
		'extracted_settings'     => [], // Global settings extracted.
		'processed_category_ids' => [], // Extracted category IDs.
		'removed_invalid_ids'    => [], // IDs removed due to invalid taxonomy.
		'duplicate_fields'       => [], // Duplicate fields in categories.
		'missing_categories'     => [], // Missing categories in the taxonomy.
		'validation_errors'      => [], // General validation issues.
	];

	/**
	 * Runs the migration process.
	 *
	 * @return array<string, mixed> Structured migration data.
	 */
	public function run(): array {
		$original_settings = get_option( 'teccc_options', [] );

		if ( empty( $original_settings ) ) {
			$this->log_data['validation_errors'][] = 'No data found in teccc_options.';

			return [];
		}

		// Extract settings.
		$settings = $this->extract_global_settings( $original_settings );

		// Extract and process category colors.
		$category_colors = $this->extract_category_colors( $original_settings );

		// Validate category IDs.
		$validated_category_colors = $this->filter_valid_categories( $category_colors );

		// Log removed invalid categories.
		$this->log_removed_invalid_ids( $category_colors, $validated_category_colors );

		$migration_data = [
			'settings'        => $settings,
			'category_colors' => $validated_category_colors,
		];

		// Debug output.
		printr( $migration_data, 'Final Migration Data' );
		printr( $this->log_data, 'Log Data' );

		return $migration_data;
	}

	/**
	 * Extracts and returns global settings while removing them from the original settings array.
	 *
	 * @param array<string, mixed> &$original_settings The original settings reference.
	 *
	 * @return array<string, mixed> Extracted settings.
	 */
	protected function extract_global_settings( array &$original_settings ): array {
		$settings = [];

		foreach ( $this->settings_mapping as $old_key => $new_key ) {
			if ( isset( $original_settings[ $old_key ] ) ) {
				$settings[ $new_key ] = $original_settings[ $old_key ];
				unset( $original_settings[ $old_key ] ); // Remove from original settings.
			}
		}

		// Log extracted settings.
		$this->log_data['extracted_settings'] = $settings;

		return $settings;
	}

	/**
	 * Extracts and maps category colors from the original settings.
	 *
	 * @param array<string, mixed> $original_settings The remaining settings.
	 *
	 * @return array<int, array<string, mixed>> Processed category colors.
	 */
	protected function extract_category_colors( array $original_settings ): array {
		// Get all keys that match the category pattern.
		$category_keys = array_filter(
			array_keys( $original_settings ),
			function ( $key ) {
				return preg_match( '/^category-(\d+)-(.+)$/', $key );
			}
		);

		// Extract only the matching category settings.
		$filtered_categories = array_intersect_key( $original_settings, array_flip( $category_keys ) );

		$category_colors = [];

		foreach ( $filtered_categories as $key => $value ) {
			preg_match( '/^category-(\d+)-(.+)$/', $key, $matches );
			$category_id = (int) $matches[1];
			$field_name  = $matches[2];

			// Log all raw category keys.
			$this->log_data['raw_category_keys'][] = $key;

			// Skip ignored fields (mapped to null).
			if ( isset( $this->category_field_mapping[ $field_name ] ) && $this->category_field_mapping[ $field_name ] === null ) {
				continue;
			}

			// Process only mapped fields.
			if ( isset( $this->category_field_mapping[ $field_name ] ) ) {
				$new_field_name                                     = $this->category_field_mapping[ $field_name ];
				$category_colors[ $category_id ][ $new_field_name ] = $value;
			}
		}

		return $category_colors;
	}

	/**
	 * Filters valid categories by checking if they exist in the taxonomy.
	 *
	 * @param array<int, array<string, mixed>> $category_colors The processed category colors.
	 *
	 * @return array<int, array<string, mixed>> Validated category colors.
	 */
	protected function filter_valid_categories( array $category_colors ): array {
		$taxonomy         = Tribe__Events__Main::TAXONOMY;
		$valid_categories = [];

		foreach ( $category_colors as $category_id => $fields ) {
			if ( term_exists( $category_id, $taxonomy ) ) {
				$valid_categories[ $category_id ] = $fields;
			} else {
				$this->log_data['missing_categories'][] = $category_id; // Log missing categories.
			}
		}

		return $valid_categories;
	}

	/**
	 * Logs category IDs that were removed due to being invalid.
	 *
	 * @param array<int, array<string, mixed>> $category_colors           Original processed category colors.
	 * @param array<int, array<string, mixed>> $validated_category_colors Validated category colors.
	 *
	 * @return void
	 */
	protected function log_removed_invalid_ids( array $category_colors, array $validated_category_colors ): void {
		$removed_ids                           = array_diff( array_keys( $category_colors ), array_keys( $validated_category_colors ) );
		$this->log_data['removed_invalid_ids'] = array_values( $removed_ids );
	}

	/**
	 * Gets the logged data for debugging.
	 *
	 * @return array<string, mixed> The log data.
	 */
	public function get_log_data(): array {
		return $this->log_data;
	}
}
