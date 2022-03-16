<?php
/**
 * ${CARET}
 *
 * @since   TBD
 * @package TEC\Events\Custom_Tables\V1\Migration;
 */

namespace TEC\Events\Custom_Tables\V1\Migration;

use TEC\Events\Custom_Tables\V1\Migration\Reports\Event_Report;
use Tribe__Events__Main as TEC;

/**
 * Class Events.
 *
 * @since   TBD
 * @package TEC\Events\Custom_Tables\V1\Migration;
 */
class Events {
	/**
	 * A place to store various data sets to avoid repeating expensive queries.
	 *
	 * @var array<string, mixed>
	 */
	protected $_cache = [];

	/**
	 * Returns an Event post ID, claimed and locked to process.
	 *
	 * @since TBD
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
	 * @since TBD
	 *
	 * @param int $limit The max number of Event post IDs to return.
	 *
	 * @return array<numeric> An array of claimed and locked Event post IDs.
	 */
	public function get_ids_to_process( $limit ) {
		global $wpdb;

		// Batch locking
		$batch_uid = uniqid( 'tec_ct1_action', true ); // Should be pretty unique.

		// Atomic query.
		// Fetch only those that were NOT previously touched.
		$lock_query = "INSERT INTO {$wpdb->postmeta} (post_id, meta_key, meta_value)
	    SELECT p.ID, %s,%s
	    FROM {$wpdb->posts} p
			LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key IN(%s, %s)
	    WHERE p.post_type = %s AND pm.meta_value IS NULL
	    LIMIT %d";
		$lock_query = $wpdb->prepare( $lock_query,
			Event_Report::META_KEY_MIGRATION_LOCK_HASH,
			$batch_uid,
			Event_Report::META_KEY_MIGRATION_LOCK_HASH,
			Event_Report::META_KEY_MIGRATION_PHASE,
			TEC::POSTTYPE,
			$limit
		);

		$wpdb->query( $lock_query );

		if ( ! $wpdb->rows_affected ) {
			return [];
		}

		// Let’s claim the prize.
		$fetch_query = "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value = %s";
		$fetch_query = $wpdb->prepare( $fetch_query, Event_Report::META_KEY_MIGRATION_LOCK_HASH, $batch_uid );

		return $wpdb->get_col( $fetch_query );
	}

	/**
	 * Calculate how many events are remaining in migration.
	 *
	 * @since TBD
	 *
	 * @return int
	 */
	public function get_total_events_remaining() {
		return $this->get_total_events() - $this->get_total_events_migrated();
	}

	/**
	 * Fetches all the post IDs of Events that have been migrated.
	 *
	 * @since TBD
	 *
	 * @param $page  int Page in a pagination retrieval.
	 * @param $count int How many to retrieve.
	 *
	 * @return array<numeric>
	 */
	public function get_events_migrated( $page, $count ) {
		global $wpdb; $total_events_migrated = $this->get_total_events_migrated();
		// @todo do we want to query for "locked" and "event reported" events?
		// Get in progress / complete events
		if ( $page === - 1 || $total_events_migrated == 0 || $count > $total_events_migrated ) {
			$query = $wpdb->prepare(
				"SELECT DISTINCT ID
				FROM {$wpdb->posts} p
				INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key IN( %s )
				WHERE p.post_type = %s",
				Event_Report::META_KEY_REPORT_DATA,
				TEC::POSTTYPE
			);
		} else {
			$total_pages = $total_events_migrated / $count;
			if ( $page > $total_pages ) {
				$page = $total_pages;
			}
			$start = ( $page - 1 ) * $count;

			$query = $wpdb->prepare(
				"SELECT DISTINCT ID
				FROM {$wpdb->posts} p
				INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key IN( %s)
				WHERE p.post_type = %s ORDER BY ID ASC LIMIT %d, %d",
				Event_Report::META_KEY_REPORT_DATA,
				TEC::POSTTYPE,
				$start,
				$count
			);
		}

		return $wpdb->get_col( $query );
	}

	/**
	 * Total number of events that are flagged as locked for processing.
	 *
	 * @since TBD
	 *
	 * @return int
	 */
	public function get_total_events_in_progress() {
		$cache       = tribe( 'cache' );
		$in_progress = $cache[ __METHOD__ ];

		if ( false === $in_progress ) {
			global $wpdb;
			$total_in_progress_query = $wpdb->prepare(
				"SELECT COUNT(DISTINCT `ID`)
			FROM {$wpdb->posts} p
			INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = %s
			WHERE p.post_type = %s",
				Event_Report::META_KEY_MIGRATION_LOCK_HASH,
				TEC::POSTTYPE
			);
			$in_progress             = (int) $wpdb->get_var( $total_in_progress_query );
			$cache[ __METHOD__ ]     = $in_progress;
		}

		return $in_progress;
	}

	/**
	 * How many events have been migrated (failure and success).
	 *
	 * @since TBD
	 *
	 * @return int
	 */
	public function get_total_events_migrated() {
		$cache    = tribe( 'cache' );
		$migrated = $cache[ __METHOD__ ];

		if ( false === $migrated ) {
			global $wpdb;
			$total_migrated_query = $wpdb->prepare(
				"SELECT COUNT(DISTINCT `ID`)
			FROM {$wpdb->posts} p
			INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = %s
			WHERE p.post_type = %s
			AND pm.meta_value IN(%s, %s)",
				Event_Report::META_KEY_MIGRATION_PHASE,
				TEC::POSTTYPE,
				Event_Report::META_VALUE_MIGRATION_PHASE_MIGRATION_SUCCESS,
				Event_Report::META_VALUE_MIGRATION_PHASE_MIGRATION_FAILURE
			);
			$migrated             = (int) $wpdb->get_var( $total_migrated_query );
			$cache[ __METHOD__ ]  = $migrated;
		}

		return $migrated;
	}

	/**
	 * The total number of TEC events.
	 *
	 * @since TBD
	 *
	 * @return int
	 */
	public function get_total_events() {
		$cache        = tribe( 'cache' );
		$total_events = $cache[ __METHOD__ ];

		if ( false === $total_events ) {
			global $wpdb;
			$total_events = (int) $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(ID) FROM {$wpdb->posts} p WHERE p.post_type = %s AND post_parent = 0",
					TEC::POSTTYPE
				)
			);
			$cache[ __METHOD__ ] = $total_events;
		}

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
