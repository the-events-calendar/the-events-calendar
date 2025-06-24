<?php
/**
 * Configuration class for Category Colors migration.
 *
 * This class centralizes option names, expected structures, and key mappings
 * used during the migration process. It ensures consistency and reduces
 * hardcoded values across multiple migration steps.
 *
 * @since   TBD
 *
 * @package TEC\Events\Category_Colors\Migration
 */

namespace TEC\Events\Category_Colors\Migration;

/**
 * Class Config
 *
 * Provides configuration settings and mappings for the Category Colors migration.
 * This includes option names for storing migration data, meta key mappings, and
 * settings migration rules.
 *
 * @since   TBD
 *
 * @package TEC\Events\Category_Colors\Migration
 */
class Config {

	/**
	 * Option name for storing original settings.
	 *
	 * @since TBD
	 * @var string
	 */
	public const ORIGINAL_SETTINGS_OPTION = 'teccc_options';

	/**
	 * Option name for storing migration data.
	 *
	 * @since TBD
	 * @var string
	 */
	public const MIGRATION_DATA_OPTION = 'tec_category_colors_migration_data';

	/**
	 * Option name for storing migration processing data.
	 * This is a working copy of the migration data that gets modified as items are processed.
	 *
	 * @since TBD
	 * @var string
	 */
	public const MIGRATION_PROCESSING_OPTION = 'tec_category_colors_migration_processing';

	/**
	 * Option name for tracking the migration status.
	 *
	 * @since TBD
	 * @var string
	 */
	public const MIGRATION_STATUS_OPTION = 'tec_events_category_colors_migration_status';

	/**
	 * Expected structure for the migration data.
	 *
	 * @since TBD
	 * @var array<string, mixed>
	 */
	public const EXPECTED_STRUCTURE = [
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
	public const SETTINGS_MAPPING = [
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
			'mapped_key' => 'category-color-custom-css',
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
	public const META_KEY_PREFIX = 'tec-events-cat-colors-';

	/**
	 * Mapping of old meta keys to new ones.
	 * Keys represent the old names, and values represent the new names.
	 * Any key not in this list will be ignored.
	 *
	 * @since TBD
	 * @var array<string, string>
	 */
	public const META_KEY_MAP = [
		'border'     => 'primary',
		'background' => 'secondary',
		'text'       => 'text',
		'hide'       => 'hidden',
	];

	/**
	 * The action group name for migration actions.
	 *
	 * @since TBD
	 * @var string
	 */
	public const MIGRATION_ACTION_GROUP = 'tec_events_category_colors_migration';

	/**
	 * Option name for tracking the current migration batch.
	 *
	 * @since TBD
	 * @var string
	 */
	public const MIGRATION_BATCH_OPTION = 'tec_events_category_colors_migration_batch';
}
