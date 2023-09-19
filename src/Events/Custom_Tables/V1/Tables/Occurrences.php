<?php
/**
 * Models the Occurrence custom table.
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Tables
 */

namespace TEC\Events\Custom_Tables\V1\Tables;

use TEC\Events\Custom_Tables\V1\Schema_Builder\Abstract_Custom_Table;

/**
 * Class Occurrences
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Tables
 */
class Occurrences extends Abstract_Custom_Table {
	/**
	 * @inheritDoc
	 */
	const SCHEMA_VERSION_OPTION = 'tec_ct1_occurrences_table_schema_version';

	/**
	 * @since 6.0.6 Will now simply create an `event_id` index, removes the foreign key from the previous version.
	 *
	 * @inheritDoc
	 */
	const SCHEMA_VERSION = '1.0.2';

	/**
	 * @inheritDoc
	 */
	public static function base_table_name() {
		return 'tec_occurrences';
	}

	/**
	 * {@inheritDoc}
	 */
	public static function group_name() {
		return 'tec';
	}

	/**
	 * {@inheritdoc}
	 */
	public static function uid_column() {
		return 'occurrence_id';
	}

	/**
	 * {@inheritdoc}
	 */
	protected function get_update_sql() {
		global $wpdb;
		$table_name      = self::table_name( true );
		$charset_collate = $wpdb->get_charset_collate();

		// VARCHAR(19) to store YYYY-MM-DD HH:MM:SS values as strings and allow partial compare.
		return "CREATE TABLE `{$table_name}` (
			`occurrence_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			`event_id` bigint(20) unsigned NOT NULL,
			`post_id` bigint(20) unsigned NOT NULL,
			`start_date` varchar(19) NOT NULL,
			`start_date_utc` varchar(19) NOT NULL,
			`end_date` varchar(19) NOT NULL,
			`end_date_utc` varchar(19) NOT NULL,
			`duration` mediumint(30) DEFAULT 7200,
			`hash` varchar(40) NOT NULL,
			`updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY  (`occurrence_id`)
			) {$charset_collate};";
	}

	/**
	 * Overrides the base method to add `event_id` as key.
	 *
	 * @since 6.0.6 Will now create an `event_id` index, removes the foreign key from the previous version.
	 *
	 * {@inheritdoc}
	 */
	protected function after_update( array $results ) {
		global $wpdb;
		$this_table        = self::table_name( true );
		$updated           = false;
		if (
			$this->exists()
			&& ! $this->has_index( 'event_id', $this_table )
		) {
			$SQL     = "ALTER TABLE {$this_table} ADD INDEX (event_id)";
			$updated = $wpdb->query( $SQL );
		} else if ( $this->exists()
		            && $this->has_constraint( 'event_id', $this_table ) ) {
			// We are moving away from foreign key constraints. If this is our old schema, find the FK name and drop it.
			$constraint      = $this->get_schema_constraint( 'event_id', $this_table );
			$foreign_key_name = $constraint->CONSTRAINT_NAME ?? null;
			if ( $foreign_key_name ) {
				$updated = $wpdb->query( "ALTER TABLE {$this_table} DROP FOREIGN KEY {$foreign_key_name}" );
			}
		}

		$message = $updated
			? "Added event_id as key in {$this_table}"
			: "Failed to add event_id as key in {$this_table}";

		$results[ $this_table . '.event_id' ] = $message;

		if ( $this->exists() && ! $this->has_index( 'hash' ) ) {
			$updated = $wpdb->query( "ALTER TABLE `{$this_table}`ADD UNIQUE( `hash` )" );

			$message = $updated
				? "Added UNIQUE constraint to the table {$this_table} on hash."
				: "Failed to add a unique constraint to the events table";

			$results[ $this_table . '.hash' ] = $message;
		}

		return $results;
	}
}
