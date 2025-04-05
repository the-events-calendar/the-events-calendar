<?php

namespace TEC\Events\Custom_Tables\V1\Events\Event_Cleaner;

use TEC\Events\Custom_Tables\V1\Tables\Occurrences;

/**
 * Class Provider
 *
 * This is the service for our "Old" Event Cleaner system.
 *
 * @since   6.0.13
 *
 * @package TEC\Events\Custom_Tables\V1\Events\Event_Cleaner
 */
class Event_Cleaner {

	/**
	 * Hooks into our automated event cleaner service, and modifies the expired events query to handle occurrences for
	 * recurring events.
	 *
	 * @since 6.0.13
	 * @since 6.9.0 Adjust SQL to ignore trashed imported events during cleanup.
	 *
	 * @param string $sql The original query to retrieve expired events.
	 *
	 * @return string The modified CT1 query to retrieve expired events.
	 */
	public function redirect_old_events_sql( string $sql ): string {
		global $wpdb;
		$occurrence_table = Occurrences::table_name();

		// Order events by start date, so we can delete the "first" event chronologically.
		// Also restricts to events that only have one occurrence (in case of ECP deactivation).
		return "SELECT {$occurrence_table}.post_id
				FROM {$wpdb->posts}
			    	INNER JOIN {$occurrence_table} ON {$wpdb->posts}.ID = {$occurrence_table}.post_id
				WHERE {$wpdb->posts}.post_type = " . '"%1$s"' . "
					AND {$occurrence_table}.end_date_utc <= DATE_SUB( CURRENT_TIMESTAMP(), INTERVAL " . '%2$d %4$s' . " )
					AND {$wpdb->posts}.post_status NOT IN ( 'trash', 'tribe-ignored' )
				GROUP BY {$occurrence_table}.post_id
				HAVING COUNT(*) = 1
				ORDER BY {$occurrence_table}.start_date_utc ASC, {$occurrence_table}.end_date_utc ASC
				LIMIT " . '%3$d';
	}
}
