<?php
/**
 * Manages the table creation and update.
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Tables
 */

namespace TEC\Events\Custom_Tables\V1\Tables;

use TEC\Events\Custom_Tables\V1\Schema_Builder\Abstract_Schema_Provider;

/**
 * Class Provider
 *
 * @since   6.0.0
 * @package TEC\Events\Custom_Tables\V1\Tables
 */
class Provider extends Abstract_Schema_Provider {
	/**
	 * @inheritDoc
	 */
	public static function get_table_schemas() {
		return [
			tribe( Events::class ),
			tribe( Occurrences::class ),
		];
	}

	/**
	 * {@inheritdoc}
	 *
	 * The recurrence field schemas are registered unconditionally: the columns are inert
	 * defaults when the Recurrence feature is not active, and unconditional registration
	 * keeps the registered schemas version hash stable whether the feature, or Events
	 * Calendar Pro, is active or not. Events Calendar Pro versions registering the same
	 * field schemas are deduplicated by the Schema_Builder on the schema version option.
	 *
	 * @since TBD
	 */
	public static function get_field_schemas() {
		return [
			tribe( Fields\Events::class ),
			tribe( Fields\Occurrences::class ),
		];
	}
}
