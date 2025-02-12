<?php

namespace TEC\Events\Category_Colors\Migration;

use Tribe__Events__Main;

trait Migration_Trait {

	/**
	 * The taxonomy used for event categories.
	 *
	 * @var string
	 */
	public string $taxonomy = Tribe__Events__Main::TAXONOMY;

	/**
	 * Expected structure for the migration data.
	 *
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
	 * Regular expression for matching category-related keys.
	 *
	 * @var string
	 */
	protected string $category_regex = '/^category-(\d+)[-_](.+)$/';

	/**
	 * Prefix for storing category meta values.
	 *
	 * @var string
	 */
	protected string $meta_key_prefix = 'tec-events-cat-colors-';

	/**
	 * Mapping of old meta keys to new ones.
	 * If additional items need to be extracted, add them here.
	 * Keys represent the old names, and values represent the new names.
	 * Any key not in this list will be ignored.
	 * Example:
	 * - 'border'     → 'primary'
	 * - 'background' → 'secondary'
	 * - 'text'       → 'text'
	 *
	 * @var array<string, string>
	 */
	protected array $meta_key_map = [
		'border'     => 'primary',
		'background' => 'secondary',
		'text'       => 'text',
	];

	/**
	 * Extracts the category ID from a category-related setting key.
	 *
	 * @param string $key The category setting key.
	 *
	 * @return int|null The extracted category ID or null if not found.
	 */
	protected function extract_category_id( string $key ): ?int {
		if ( preg_match( $this->category_regex, $key, $matches ) ) {
			return (int) $matches[1];
		}

		return null;
	}

	/**
	 * Retrieves the processed categories.
	 *
	 * @return array<int, array<string, mixed>> The list of processed categories.
	 */
	public function get_categories(): array {
		return $this->migration_data['categories'] ?? [];
	}

	/**
	 * Retrieves a specific meta value from a category.
	 *
	 * @param int    $category_id The category ID.
	 * @param string $key         The key to retrieve.
	 * @param mixed  $default     Default value if the key is not found.
	 *
	 * @return mixed The retrieved value or the default.
	 */
	public function get_meta( int $category_id, string $key, $default = '' ) {
		if ( ! isset( $this->migration_data['categories'][ $category_id ] ) ) {
			return $default;
		}

		return $this->migration_data['categories'][ $category_id ][ $key ] ?? $default;
	}

	/**
	 * Retrieves the mapped meta key, or null if it is not recognized.
	 *
	 * @param string $key The original meta key.
	 *
	 * @return string|null The mapped meta key, or null if it should be ignored.
	 */
	protected function get_mapped_meta_key( string $key ): ?string {
		return $this->meta_key_map[ $key ] ?? null;
	}
}
