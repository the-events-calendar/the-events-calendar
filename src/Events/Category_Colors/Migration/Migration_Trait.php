<?php
/**
 * Provides shared functionality for category color migration.
 * This trait includes reusable methods for retrieving and managing migration data,
 * ensuring consistency across all migration-related classes.
 *
 * @since   TBD
 * @package TEC\Events\Category_Colors\Migration
 */

namespace TEC\Events\Category_Colors\Migration;

use Tribe__Events__Main;

/**
 * Trait Migration_Trait
 * Contains shared methods for handling migration data, taxonomy lookups,
 * and utility functions used across multiple migration classes.
 *
 * @since TBD
 */
trait Migration_Trait {

	/**
	 * The taxonomy used for event categories.
	 *
	 * @since TBD
	 * @var string
	 */
	public string $taxonomy = Tribe__Events__Main::TAXONOMY;

	/**
	 * Option name for storing original settings.
	 *
	 * @since TBD
	 * @var string
	 */
	protected string $original_settings_option = 'teccc_options';

	/**
	 * Option name for storing migration data.
	 *
	 * @since TBD
	 * @var string
	 */
	protected string $migration_data_option = 'tec_category_colors_migration_data';

	/**
	 * Option name for tracking the migration status.
	 *
	 * @since TBD
	 * @var string
	 */
	protected string $migration_status_option = 'tec_events_category_colors_migration_status';

	/**
	 * Expected structure for the migration data.
	 *
	 * @since TBD
	 * @var array<string, mixed>
	 */
	protected array $expected_structure = [
		'categories'    => [],
		'legend'        => [],
		'general'       => [],
		'ignored_terms' => [],
	];

	/**
	 * List of legend-related settings to extract.
	 *
	 * @since TBD
	 * @var array<string>
	 */
	protected array $legend_keys = [
		'custom_legend_css',
		'add_legend',
		'show_ignored_cats_legend',
		'legend_superpowers',
	];

	/**
	 * List of general settings keys to extract.
	 *
	 * @since TBD
	 * @var array<string>
	 */
	protected array $general_settings_keys = [
		'reset_show',
		'reset_label',
		'reset_url',
		'chk_default_options_db',
		'font_weight',
		'featured-event',
	];

	/**
	 * Prefix for storing category meta values.
	 *
	 * @since TBD
	 * @var string
	 */
	protected string $meta_key_prefix = 'tec-events-cat-colors-';

	/**
	 * Mapping of old meta keys to new ones.
	 * Keys represent the old names, and values represent the new names.
	 * Any key not in this list will be ignored.
	 *
	 * @since TBD
	 * @var array<string, string>
	 */
	protected array $meta_key_map = [
		'border'     => 'primary',
		'background' => 'secondary',
		'text'       => 'text',
	];

	/**
	 * Retrieves the original settings from the database.
	 *
	 * @since TBD
	 * @return array<string, mixed> The original settings.
	 */
	public function get_original_settings(): array {
		return get_option( $this->original_settings_option, [] );
	}

	/**
	 * Retrieves the migration data from the database.
	 *
	 * @since TBD
	 * @return array<string, mixed> The migration data.
	 */
	public function get_migration_data(): array {
		return get_option( $this->migration_data_option, [] );
	}

	/**
	 * Stores the migration data in the database.
	 *
	 * @since TBD
	 *
	 * @param array<string, mixed> $data The processed migration data to store.
	 *
	 * @return void
	 */
	public function update_migration_data( array $data ): void {
		update_option( $this->migration_data_option, $data, false );
	}

	/**
	 * Extracts the category ID from a category-related setting key.
	 *
	 * @since TBD
	 *
	 * @param string $key The category setting key.
	 *
	 * @return int|null The extracted category ID or null if not found.
	 */
	/**
	 * Extracts the category ID from a category-related setting key.
	 */
	protected function extract_category_id( string $key ): ?int {
		$original_settings = $this->get_original_settings();

		// Check if the key exists in the terms or all_terms mapping.
		foreach ( [ 'terms', 'all_terms' ] as $term_group ) {
			if ( isset( $original_settings[ $term_group ] ) ) {
				foreach ( $original_settings[ $term_group ] as $term_id => [$slug, $name] ) {
					if ( strpos( $key, $slug ) === 0 ) {
						return (int) $term_id;
					}
				}
			}
		}

		return null;
	}

	/**
	 * Retrieves the processed categories.
	 *
	 * @since TBD
	 * @return array<int, array<string, mixed>> The list of processed categories.
	 */
	public function get_categories(): array {
		$migration_data = $this->get_migration_data();

		return $migration_data['categories'] ?? [];
	}

	/**
	 * Retrieves a specific meta value from a category.
	 *
	 * @since TBD
	 *
	 * @param int    $category_id   The category ID.
	 * @param string $key           The key to retrieve.
	 * @param mixed  $default_value Default value if the key is not found.
	 *
	 * @return mixed The retrieved value or the default.
	 */
	public function get_meta( int $category_id, string $key, $default_value = '' ) {
		$migration_data = $this->get_migration_data();

		if ( ! isset( $migration_data['categories'][ $category_id ] ) ) {
			return $default_value;
		}

		return $migration_data['categories'][ $category_id ][ $key ] ?? $default_value;
	}

	/**
	 * Retrieves the mapped meta key, or null if it is not recognized.
	 *
	 * @since TBD
	 *
	 * @param string $key The original meta key.
	 *
	 * @return string|null The mapped meta key, or null if it should be ignored.
	 */
	protected function get_mapped_meta_key( string $key ): ?string {
		return $this->meta_key_map[ $key ] ?? null;
	}

	/**
	 * Gets the current migration status.
	 *
	 * @since TBD
	 * @return array<string, mixed> The current migration status with timestamp.
	 */
	protected function get_migration_status(): array {
		return get_option(
			$this->migration_status_option,
			[
				'status'    => 'not_started',
				'timestamp' => current_time( 'mysql' ),
			]
		);
	}

	/**
	 * Updates the migration status and triggers an action.
	 *
	 * @since TBD
	 *
	 * @param string $status The new migration status.
	 *
	 * @return void
	 */
	protected function update_migration_status( string $status ): void {
		update_option(
			$this->migration_status_option,
			[
				'status'    => $status,
				'timestamp' => current_time( 'mysql' ),
			]
		);

		Logger::log( 'info', "Migration status updated to: {$status} at " . current_time( 'mysql' ) );

		/**
		 * Fires when the migration status is updated.
		 *
		 * @since TBD
		 *
		 * @param string $status The new migration status.
		 */
		do_action( 'tec_events_category_colors_migration_status_updated', $status );
	}

	/**
	 * Resets the migration process, clearing stored migration data and resetting the status.
	 *
	 * @since TBD
	 * @return void
	 */
	public function reset_migration(): void {
		// Delete migration data and reset status
		delete_option( $this->migration_data_option );
		$this->update_migration_status( 'not_started' );

		Logger::log( 'info', 'Migration has been reset to the initial state.' );

		/**
		 * Fires when the migration is reset.
		 *
		 * @since TBD
		 */
		do_action( 'tec_events_category_colors_migration_reset' );
	}

}
