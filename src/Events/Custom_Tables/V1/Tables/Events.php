<?php
/**
 * Models the Event custom table.
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Tables
 */

namespace TEC\Events\Custom_Tables\V1\Tables;

use TEC\Events\Custom_Tables\V1\Schema_Builder\Abstract_Custom_Table;

/**
 * Class Events
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Tables
 */
class Events extends Abstract_Custom_Table {
	/**
	 * @inheritDoc
	 */
	const SCHEMA_VERSION_OPTION = 'tec_ct1_events_table_schema_version';

	/**
	 * @inheritDoc
	 */
	const SCHEMA_VERSION = '1.0.0';

	/**
	 * {@inheritdoc}
	 */
	public static function uid_column() {
		return 'event_id';
	}

	/**
	 * @inheritDoc
	 */
	public static function base_table_name() {
		return 'tec_events';
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
	protected function get_update_sql() {
		global $wpdb;
		$table_name = self::table_name(true);
		$charset_collate = $wpdb->get_charset_collate();

		return "CREATE TABLE `{$table_name}` (
			`event_id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			`post_id` BIGINT(20) UNSIGNED NOT NULL,
			`start_date` DATETIME NOT NULL,
			`end_date` DATETIME DEFAULT NULL,
			`timezone` VARCHAR(30) NOT NULL DEFAULT 'UTC',
			`start_date_utc` DATETIME NOT NULL,
			`end_date_utc` DATETIME DEFAULT NULL,
			`duration` MEDIUMINT(30) DEFAULT 7200,
			`updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			`hash` varchar(40) NOT NULL,
			PRIMARY KEY  (`event_id`)
			) {$charset_collate};";
	}

	/**
	 * Overrides the base method to add `post_id` as index.
	 *
	 * {@inheritdoc}
	 */
	protected function after_update( array $results ) {
		if ( ! count( $results ) ) {
			return $results;
		}
		// @todo why here and not in create?
		global $wpdb;
		$table_name = self::table_name( true );

		$updated = false;

		if ( $this->exists() && ! $this->has_index( 'post_id' ) ) {
			$updated = $wpdb->query( "ALTER TABLE `{$table_name}`ADD UNIQUE( `post_id` )" );
		}

		$message = $updated
			? "Added UNIQUE constraint to the events table on post_id."
			: "Failed to add a unique constraint to the events table";

		$results[ $table_name . '.event_id' ] = $message;

		return $results;
	}

}
