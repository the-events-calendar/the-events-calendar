<?php
/**
 * Provides methods to query the Events posts and postmeta tables in the context of the migration process.
 *
 * @since   6.0.0
 * @package TEC\Events\Custom_Tables\V1\Migration;
 */

namespace TEC\Events\Custom_Tables\V1\Migration;

use TEC\Events\Custom_Tables\V1\Migration\Reports\Event_Report;
use Tribe__Events__Main as TEC;

/**
 * Class Events.
 *
 * @since   6.0.0
 * @package TEC\Events\Custom_Tables\V1\Migration;
 */
class Events {
	/**
	 * Returns an Event post ID, claimed and locked to process.
	 *
	 * @since 6.0.0
	 *
	 * @param bool $has_been_migrated Whether to limit results to only those that have been previously touched by
	 *                                migration.
	 *
	 * @return int|false Either an Event post ID, or `false` if no
	 *                   Event post ID could be claimed and locked.
	 */
	public function get_id_to_process( $has_been_migrated = false ) {
		$locked = $this->get_ids_to_process( 1, $has_been_migrated );

		return count( $locked ) ? reset( $locked ) : false;
	}

	/**
	 * Returns a set of Event post IDs that have been locked and claimed
	 * for operation.
	 *
	 * @since 6.0.0
	 *
	 * @param int $limit The max number of Event post IDs to return.
	 *
	 * @return array<numeric> An array of claimed and locked Event post IDs.
	 */
	public function get_ids_to_process( $limit ) {
		global $wpdb;

		// Batch locking
		$batch_uid = uniqid( 'tec_ct1_action', true ); // Should be pretty unique.

		// Let's avoid table locks in the following query, this could have Deadlock side effects.
		// @see https://dev.mysql.com/doc/refman/5.7/en/set-transaction.html to know what this is doing.
		$wpdb->query("SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED");

		// Atomic query.
		// Fetch only those that were NOT previously touched.
		$lock_query = "INSERT INTO {$wpdb->postmeta} (post_id, meta_key, meta_value)
	    SELECT DISTINCT p.ID, %s,%s
	    FROM {$wpdb->posts} p
			LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key IN (%s, %s)
			LEFT JOIN {$wpdb->postmeta} created_by_migration ON p.ID = created_by_migration.post_id
				AND created_by_migration.meta_key = %s
			LEFT JOIN {$wpdb->postmeta} pm1 ON p.ID = pm1.post_id AND pm1.meta_key = '_EventStartDate'
			LEFT JOIN {$wpdb->postmeta} pm2 ON p.ID = pm2.post_id AND pm2.meta_key = '_EventStartDateUTC'
			LEFT JOIN {$wpdb->postmeta} pm3 ON p.ID = pm3.post_id AND pm3.meta_key = '_EventEndDate'
			LEFT JOIN {$wpdb->postmeta} pm4 ON p.ID = pm4.post_id AND pm4.meta_key = '_EventEndDateUTC'
	    WHERE p.post_type = %s
	    	AND pm.meta_value IS NULL
	    	AND p.post_status != 'auto-draft'
	    	AND p.post_parent = 0
	    	AND created_by_migration.meta_value IS NULL
			AND ((pm1.meta_value IS NOT NULL AND pm1.meta_value != '') OR (pm2.meta_value IS NOT NULL AND pm2.meta_value != ''))
			AND ((pm3.meta_value IS NOT NULL AND pm3.meta_value != '') OR (pm4.meta_value IS NOT NULL AND pm4.meta_value != ''))
	    LIMIT %d";
		$lock_query = $wpdb->prepare( $lock_query,
			Event_Report::META_KEY_MIGRATION_LOCK_HASH,
			$batch_uid,
			Event_Report::META_KEY_MIGRATION_LOCK_HASH,
			Event_Report::META_KEY_MIGRATION_PHASE,
			Process::EVENT_CREATED_BY_MIGRATION_META_KEY,
			TEC::POSTTYPE,
			$limit
		);

		// The lock operation could fail and that is ok. A deadlock message should not be reported in this case.
		$suppress_errors_backup = $wpdb->suppress_errors;
		$wpdb->suppress_errors  = true;
		$wpdb->query( $lock_query );
		$wpdb->suppress_errors = $suppress_errors_backup;

		// Get our db object so we can inspect. This isn't always an object, so some type checking is needed.
		$db = is_object( $wpdb->dbh ) ? $wpdb->dbh : null;

		// Deadlock error no.
		$deadlock_errno = 1213;
		if ( $db !== null && $db->errno === $deadlock_errno ) {
			// Deadlock, lets retry lock query.
			$wpdb->query( $lock_query );
		}

		if ( ! $wpdb->rows_affected ) {
			return [];
		}

		// Let’s claim the prize.
		$fetch_query = "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value = %s";
		$fetch_query = $wpdb->prepare( $fetch_query, Event_Report::META_KEY_MIGRATION_LOCK_HASH, $batch_uid );
		$results     = $wpdb->get_col( $fetch_query );

		if ( empty( $results ) && $db !== null && $db->errno === $deadlock_errno ) {
			// Deadlock, lets retry fetch query.
			$results = $wpdb->get_col( $fetch_query );
		}

		return $results;
	}

	/**
	 * Calculate how many events are remaining to migrate.
	 *
	 * @since 6.0.0
	 *
	 * @return int The total number of Events that are not migrated or migrating.
	 */
	public function get_total_events_remaining() {
		return $this->get_total_events() - $this->get_total_events_migrated();
	}

	/**
	 * Fetches all the post IDs of Events that have been migrated.
	 *
	 * @since 6.0.0
	 *
	 * @param int   $page   Page in a pagination retrieval.
	 * @param int   $count  How many to retrieve.
	 * @param array $filter Filter the events returned.
	 *
	 * @return array<numeric>
	 */
	public function get_events_migrated( $page, $count, $filter = [] ) {
		global $wpdb;

		// If the first page, start at 0. Else increment to the next page and start there.
		$start     = $page === 1 ? 0 : ( $page - 1 ) * $count;
		$params    = [];
		$q = "SELECT DISTINCT `ID`, pm_d.meta_value
				FROM {$wpdb->posts} p
				INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = %s
				LEFT JOIN {$wpdb->postmeta} pm_d ON p.ID = pm_d.post_id AND pm_d.meta_key = '_EventStartDate'
				LEFT JOIN {$wpdb->postmeta} created_by_migration ON p.ID = created_by_migration.post_ID
					AND created_by_migration.meta_key = %s";
		array_push( $params, Event_Report::META_KEY_REPORT_DATA, Process::EVENT_CREATED_BY_MIGRATION_META_KEY );

		// Add joins.
		if ( isset( $filter[ Event_Report::META_KEY_MIGRATION_PHASE ] ) ) {
			$q        .= " INNER JOIN {$wpdb->postmeta} pm_s ON p.ID = pm_s.post_id AND pm_s.meta_key = %s ";
			$params[] = Event_Report::META_KEY_MIGRATION_PHASE;
		}

		if ( isset( $filter[ Event_Report::META_KEY_MIGRATION_CATEGORY ] ) ) {
			$q        .= " INNER JOIN {$wpdb->postmeta} pm_c ON p.ID = pm_c.post_id AND pm_c.meta_key = %s ";
			$params[] = Event_Report::META_KEY_MIGRATION_CATEGORY;
		}

		// Add where statement.
		$q        .= " WHERE p.post_type = %s AND p.post_parent = 0 AND created_by_migration.meta_value IS NULL";
		$params[] = TEC::POSTTYPE;
		if ( isset( $filter[ Event_Report::META_KEY_MIGRATION_PHASE ] ) ) {
			$q        .= " AND pm_s.meta_value = %s ";
			$params[] = $filter[ Event_Report::META_KEY_MIGRATION_PHASE ];
		}
		if ( isset( $filter[ Event_Report::META_KEY_MIGRATION_CATEGORY ] ) ) {
			$q        .= " AND pm_c.meta_value = %s ";
			$params[] = $filter[ Event_Report::META_KEY_MIGRATION_CATEGORY ];
		}

		// Are we grabbing upcoming or past events?
		if ( isset( $filter['upcoming'] ) ) {
			$gtlt = $filter['upcoming'] ? '>=' : '<';

			$q        .= " AND  pm_d.meta_value $gtlt %s ";
			$now      = new \DateTime( 'now', wp_timezone() );
			$params[] = $now->format( 'Y-m-d H:i:s' );
		}

		// @todo Confirm ordering - look at list view?
		$q .= " ORDER BY pm_d.meta_value DESC ";
		if ( $page !== - 1 ) {
			$q         .= "  LIMIT %d, %d ";
			$params [] = $start;
			$params [] = $count;
		}

		$query = call_user_func_array( [ $wpdb, 'prepare' ], array_merge( [ $q ], $params ) );

		return $wpdb->get_col( $query, 0 );
	}

	/**
	 * Total number of events that are flagged as locked for processing.
	 *
	 * @since 6.0.0
	 *
	 * @return int
	 */
	public function get_total_events_in_progress() {
		global $wpdb;
		$total_in_progress_query = $wpdb->prepare(
			"SELECT COUNT(DISTINCT `ID`)
			FROM {$wpdb->posts} p
			INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = %s
			LEFT JOIN {$wpdb->postmeta} pm_s on pm_s.post_id = p.ID AND pm_s.meta_key = %s
			WHERE pm_s.meta_id is null AND p.post_type = %s AND p.post_parent = 0",
			Event_Report::META_KEY_MIGRATION_LOCK_HASH,
			Event_Report::META_KEY_MIGRATION_PHASE,
			TEC::POSTTYPE
		);
		$in_progress             = (int) $wpdb->get_var( $total_in_progress_query );

		return $in_progress;
	}

	/**
	 * Total number of events that are flagged with a failure.
	 *
	 * @since 6.0.0
	 *
	 * @return int
	 */
	public function get_total_events_with_failure() {
		global $wpdb;
		$query = $wpdb->prepare(
			"SELECT COUNT(DISTINCT `ID`)
			FROM {$wpdb->posts} p
			INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = %s
			WHERE p.post_type = %s
			AND pm.meta_value = %s",
			Event_Report::META_KEY_MIGRATION_PHASE,
			TEC::POSTTYPE,
			Event_Report::META_VALUE_MIGRATION_PHASE_MIGRATION_FAILURE
		);
		$total = (int) $wpdb->get_var( $query );

		return $total;
	}

	/**
	 * How many events have been migrated (failure and success).
	 *
	 * @since 6.0.0
	 *
	 * @return int
	 */
	public function get_total_events_migrated() {
		global $wpdb;
		$total_migrated_query = $wpdb->prepare(
			"SELECT COUNT(DISTINCT `ID`)
			FROM {$wpdb->posts} p
			INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = %s
			WHERE p.post_type = %s
				AND p.post_status != 'auto-draft'
			  	AND p.post_parent = 0
				AND pm.meta_value IN(%s, %s)",
			Event_Report::META_KEY_MIGRATION_PHASE,
			TEC::POSTTYPE,
			Event_Report::META_VALUE_MIGRATION_PHASE_MIGRATION_SUCCESS,
			Event_Report::META_VALUE_MIGRATION_PHASE_MIGRATION_FAILURE
		);
		$migrated             = (int) $wpdb->get_var( $total_migrated_query );

		return $migrated;
	}

	/**
	 * The total number of TEC events.
	 *
	 * @since 6.0.0
	 *
	 * @return int The total number of Events in the database, migrated or not.
	 */
	public function get_total_events() {
		global $wpdb;
		$total_events = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(DISTINCT `ID`) FROM {$wpdb->posts} p
						LEFT JOIN {$wpdb->postmeta} created_by_migration ON p.ID = created_by_migration.post_id
							AND created_by_migration.meta_key = %s
						LEFT JOIN {$wpdb->postmeta} pm1 ON p.ID = pm1.post_id AND pm1.meta_key = '_EventStartDate'
						LEFT JOIN {$wpdb->postmeta} pm2 ON p.ID = pm2.post_id AND pm2.meta_key = '_EventStartDateUTC'
						LEFT JOIN {$wpdb->postmeta} pm3 ON p.ID = pm3.post_id AND pm3.meta_key = '_EventEndDate'
						LEFT JOIN {$wpdb->postmeta} pm4 ON p.ID = pm4.post_id AND pm4.meta_key = '_EventEndDateUTC'
						WHERE p.post_type = %s
							AND post_parent = 0
							AND p.post_status != 'auto-draft'
							AND created_by_migration.meta_value IS NULL
							AND ((pm1.meta_value IS NOT NULL AND pm1.meta_value != '') OR (pm2.meta_value IS NOT NULL AND pm2.meta_value != ''))
							AND ((pm3.meta_value IS NOT NULL AND pm3.meta_value != '') OR (pm4.meta_value IS NOT NULL AND pm4.meta_value != ''))
						",
				Process::EVENT_CREATED_BY_MIGRATION_META_KEY,
				TEC::POSTTYPE
			)
		);

		return $total_events;
	}

	/**
	 * Formulate an estimate on time to complete migration.
	 *
	 * @return float|int
	 */
	public function calculate_time_to_completion() {
		// Half a second per event? Async queue, batch lock queries, and worker operations to be considered.
		$time_per_event = 0.5;
		$total_events   = $this->get_total_events();
		// So we can get an estimate based on real data.
		$post_ids = $this->get_events_migrated( 1, 50 );
		// We may not have data yet, if we do let's adjust our average time per event.
		if ( count( $post_ids ) ) {
			$total_time = 0;
			$count      = count( $post_ids );
			foreach ( $post_ids as $post_id ) {
				$event_report = new Event_Report( get_post( $post_id ) );
				// Did we get both times?
				if ( $event_report->start_timestamp && $event_report->end_timestamp ) {
					$duration   = $event_report->end_timestamp - $event_report->start_timestamp;
					$total_time += $duration;
				} else {
					// Remove from average.
					$count --;
				}
			}
			// Get average.
			if ( $count ) {
				$time_per_event = ( $total_time / $count );
			}
		}

		return $total_events * $time_per_event;
	}
}
