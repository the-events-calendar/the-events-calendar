<?php
/**
 * Models the Occurrence custom table.
 *
 * @since   TBD
 *
 * @package TEC\Events\Custom_Tables\V1\Tables
 */

namespace TEC\Events\Custom_Tables\V1\Tables;

use TEC\Events\Custom_Tables\V1\Schema_Builder\Abstract_Custom_Table;

/**
 * Class Occurrences
 *
 * @since   TBD
 *
 * @package TEC\Events\Custom_Tables\V1\Tables
 */
class Occurrences extends Abstract_Custom_Table {
	const SCHEMA_VERSION_OPTION = 'tec_ct1_occurrences_table_schema_version';
	const SCHEMA_VERSION = '1.0.0';

	/**
	 * The table name, without prefix.
	 *
	 * @since    TBD
	 *
	 * @internal Use the `table_name` method to get the table name.
	 */
	const TABLE_NAME = 'tec_occurrences';

	/**
	 * @inheritDoc
	 */
	public static function base_table_name() {
		return 'tec_occurrences';
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

		return "CREATE TABLE `{$table_name}` (
			`occurrence_id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			`event_id` BIGINT(20) UNSIGNED NOT NULL,
			`post_id` BIGINT(20) UNSIGNED NOT NULL,
			`start_date` DATETIME NOT NULL,
			`start_date_utc` DATETIME NOT NULL,
			`end_date` DATETIME NOT NULL,
			`end_date_utc` DATETIME NOT NULL,
			`duration` MEDIUMINT(30) DEFAULT 7200,
			`hash` VARCHAR(40) NOT NULL,
			`updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY  (`occurrence_id`)
			) {$charset_collate};";
	}

	/**
	 * Overrides the base method to add `event_id` as foreign key on the Events
	 * custom table.
	 *
	 * {@inheritdoc}
	 */
	protected function after_update( array $results ) {
		$this_table   = self::table_name( true );
		$events_table = Events::table_name( true );

		$updated = false;
		if (
			$this->exists()
			&& $this->exists( Events::table_name( true ) )
			&& ! $this->has_index( 'event_id', $events_table )
		) {
			global $wpdb;
			$SQL = "ALTER TABLE {$this_table}
				ADD FOREIGN KEY (event_id) REFERENCES {$events_table} (event_id)
				ON DELETE CASCADE";

			$updated = $wpdb->query( $SQL );
		}

		$message = $updated
			? "Added event_id as foreign key from {$events_table}"
			: "Failed to add event_id as foreign key from {$events_table}";

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
