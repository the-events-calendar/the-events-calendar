<?php

namespace TEC\Events\Category_Colors\Migration;

class Config {

	/**
	 * Option name for storing original settings.
	 *
	 * @since TBD
	 * @var string
	 */
	public static string $original_settings_option = 'teccc_options';

	/**
	 * Option name for storing migration data.
	 *
	 * @since TBD
	 * @var string
	 */
	public static string $migration_data_option = 'tec_category_colors_migration_data';

	/**
	 * Option name for tracking the migration status.
	 *
	 * @since TBD
	 * @var string
	 */
	public static string $migration_status_option = 'tec_events_category_colors_migration_status';

	/**
	 * Expected structure for the migration data.
	 *
	 * @since TBD
	 * @var array<string, mixed>
	 */
	public static array $expected_structure = [
		'categories'    => [],
		'settings'      => [],
		'ignored_terms' => [],
	];

	/**
	 * Mapping of old settings keys to new migrated keys, along with validation rules.
	 *
	 * @since TBD
	 * @var array<string, array<string, string>>
	 */
	public static array $settings_mapping = [
		'add_legend'               => [
			'mapped_key' => 'category-color-legend-show',
			'validation' => 'array',
			'import'     => true,
		],
		'chk_default_options_db'   => [
			'mapped_key' => 'chk_default_options_db',
			'validation' => '',
			'import'     => false,
		],
		'custom_legend_css'        => [
			'mapped_key' => 'category-color-custom-CSS',
			'validation' => 'boolean',
			'import'     => true,
		],
		'font_weight'              => [
			'mapped_key' => 'font_weight',
			'validation' => '',
			'import'     => false,
		],
		'legend_superpowers'       => [
			'mapped_key' => 'category-color-legend-superpowers',
			'validation' => 'boolean',
			'import'     => true,
		],
		'reset_show'               => [
			'mapped_key' => 'category-color-reset-button',
			'validation' => 'boolean',
			'import'     => true,
		],
		'show_ignored_cats_legend' => [
			'mapped_key' => 'category-color-show-hidden-categories',
			'validation' => 'boolean',
			'import'     => true,
		],
	];

	/**
	 * Prefix for storing category meta values.
	 *
	 * @since TBD
	 * @var string
	 */
	public static string $meta_key_prefix = 'tec-events-cat-colors-';

	/**
	 * Mapping of old meta keys to new ones.
	 * Keys represent the old names, and values represent the new names.
	 * Any key not in this list will be ignored.
	 *
	 * @since TBD
	 * @var array<string, string>
	 */
	public static array $meta_key_map = [
		'border'     => 'primary',
		'background' => 'secondary',
		'text'       => 'text',
	];
}
