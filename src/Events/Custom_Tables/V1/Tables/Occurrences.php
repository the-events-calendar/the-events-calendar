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
	const SCHEMA_VERSION = '1.0.3';

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
			`start_date` datetime NOT NULL,
			`start_date_utc` datetime NOT NULL,
			`end_date` datetime NOT NULL,
			`end_date_utc` datetime NOT NULL,
			`duration` mediumint(30) DEFAULT 7200,
			`hash` varchar(40) NOT NULL,
			`updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY  (`occurrence_id`)
			) {$charset_collate};";
	}

	/**
	 * Overrides the base method to add `event_id`, `post_id` as kesys.
	 *
	 * @since 6.0.6 Will now create an `event_id` index, removes the foreign key from the previous version.
	 * @since 6.11.1   Will now create an `post_id, start_date, end_date` and `post_id, start_date_utc, end_date_utc` indexes.
	 *
	 * {@inheritdoc}
	 */
	protected function after_update( array $results ) {
		global $wpdb;
		$this_table        = self::table_name( true );
		$updated           = false;
		if (
			$this->exists()
		) {
			$updated_index_queries = true;
			$index_queries         = [
				'event_id'                                 => "ALTER TABLE {$this_table} ADD INDEX (event_id)",

				/*
				 * The post_id is always part of the queries.
				 * The order matters in indexes; end_date is used alone in some queries, while the start_date is mostly used
				 * together with the end_date.
				 * Finally, UTC and non-UTC version are always used together.
				 */
				'idx_wp_tec_occurrences_post_id_dates'     => "ALTER TABLE {$this_table} ADD INDEX idx_wp_tec_occurrences_post_id_dates (post_id, end_date, start_date)",
				'idx_wp_tec_occurrences_post_id_dates_utc' => "ALTER TABLE {$this_table} ADD INDEX idx_wp_tec_occurrences_post_id_dates_utc (post_id, end_date_utc, start_date_utc)",
			];

			foreach ( $index_queries as $column => $index_query ) {
				if ( $this->has_index( $column, $this_table ) ) {
					continue;
				}

				$updated_index_queries &= $wpdb->query( $index_query );
			}

			$updated = $updated_index_queries;
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
			? "Added indexes in {$this_table}"
			: "Failed to add indexes in {$this_table}";

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
