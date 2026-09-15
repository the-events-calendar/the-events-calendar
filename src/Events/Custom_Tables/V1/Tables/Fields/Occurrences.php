<?php
/**
 * Models the recurrence fields of the Occurrences custom table.
 *
 * The field schema was previously registered by Events Calendar Pro
 * (`TEC\Events_Pro\Custom_Tables\V1\Tables\Occurrences`); it keeps the same column
 * definitions, schema version and schema version option so ownership of the fields can
 * move between the two plugins without triggering schema updates.
 *
 * @since TBD
 *
 * @package TEC\Events\Custom_Tables\V1\Tables\Fields
 */

namespace TEC\Events\Custom_Tables\V1\Tables\Fields;

use TEC\Events\Custom_Tables\V1\Schema_Builder\Abstract_Custom_Field;
use TEC\Events\Custom_Tables\V1\Tables\Occurrences as OccurrencesSchema;

/**
 * Class Occurrences
 *
 * @since TBD
 *
 * @package TEC\Events\Custom_Tables\V1\Tables\Fields
 */
class Occurrences extends Abstract_Custom_Field {
	/**
	 * {@inheritdoc}
	 */
	const SCHEMA_VERSION_OPTION = 'tec_ct1_occurrences_field_schema_version';

	/**
	 * {@inheritdoc}
	 */
	const SCHEMA_VERSION = '1.0.1';

	/**
	 * @inheritDoc
	 */
	public function fields() {
		return [
			'has_recurrence',
			'sequence',
			'is_rdate',
		];
	}

	/**
	 * {@inheritdoc}
	 *
	 * This table SQL is differential in respect to the one defined in
	 * the base version of the table.
	 *
	 * @see OccurrencesSchema::get_update_sql()
	 */
	protected function get_update_sql() {
		global $wpdb;
		$table_name      = $this->table_schema()::table_name( true );
		$charset_collate = $wpdb->get_charset_collate();

		return "CREATE TABLE `{$table_name}` (
			`has_recurrence` boolean DEFAULT FALSE,
            `sequence` bigint(20) unsigned DEFAULT 0,
            `is_rdate` boolean DEFAULT FALSE
			) {$charset_collate};";
	}

	/**
	 * @inheritDoc
	 */
	public function table_schema() {
		return tribe( OccurrencesSchema::class );
	}
}
