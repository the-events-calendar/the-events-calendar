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
	 * @param int  $limit             The max number of Event post IDs to return.
	 * @param bool $has_been_migrated Whether to limit results to only those that have been previously touched by
	 *                                migration.
	 *
	 * @return array<numeric> An array of claimed and locked Event post IDs.
	 */
	public function get_ids_to_process( $limit, $has_been_migrated = false ) {
		global $wpdb;

		// Batch locking
		$batch_uid = uniqid( 'tec_ct1_action', true ); // Should be pretty unique.

		// Atomic query.
		if ( $has_been_migrated ) {
			// Fetch only those that were previously touched
			$lock_query = "INSERT INTO wp_postmeta (post_id, meta_key, meta_value)
	    SELECT p.ID, %s, %s
	    FROM {$wpdb->posts} p
			LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = %s
			INNER JOIN {$wpdb->postmeta} pm_exists ON p.ID = pm_exists.post_id
	    WHERE p.post_type = %s AND pm.meta_value IS NULL
	    	AND pm_exists.meta_key =%s
	    	AND pm_exists.meta_value IN (%s, %s)
	    LIMIT %d";
			$lock_query = $wpdb->prepare( $lock_query,
				Event_Report::META_KEY_MIGRATION_LOCK_HASH,
				$batch_uid,
				Event_Report::META_KEY_MIGRATION_LOCK_HASH,
				TEC::POSTTYPE,
				Event_Report::META_KEY_MIGRATION_PHASE,
				Event_Report::META_VALUE_MIGRATION_PHASE_MIGRATION_SUCCESS,
				Event_Report::META_VALUE_MIGRATION_PHASE_MIGRATION_FAILURE,
				$limit
			);
		} else {
			//  Fetch only those that were NOT previously touched.
			$lock_query = "INSERT INTO wp_postmeta (post_id, meta_key, meta_value)
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
		}

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
		global $wpdb;
		$total_events_migrated = $this->get_total_events_migrated();
		// @todo do we want to query for "locked" and "event reported" events?
		// Get in progress / complete events
		if ( $page === - 1 || $total_events_migrated == 0 || $count > $total_events_migrated ) {
			$query = $wpdb->prepare(
				"SELECT DISTINCT ID
				FROM {$wpdb->posts} p
				INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key IN( %s, %s)
				WHERE p.post_type = %s",
				Event_Report::META_KEY_REPORT_DATA,
				Event_Report::META_KEY_MIGRATION_LOCK_HASH,
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
				INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key IN( %s, %s)
				WHERE p.post_type = %s ORDER BY ID ASC LIMIT %d, %d",
				Event_Report::META_KEY_REPORT_DATA,
				Event_Report::META_KEY_MIGRATION_LOCK_HASH,
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
		global $wpdb;
		if ( ! isset( $this->_cache[ __FUNCTION__ ] ) ) {
			$total_in_progress_query = $wpdb->prepare(
				"SELECT COUNT(DISTINCT `ID`)
			FROM {$wpdb->posts} p
			INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = %s
			WHERE p.post_type = %s",
				Event_Report::META_KEY_MIGRATION_LOCK_HASH,
				TEC::POSTTYPE
			);

			$this->_cache[ __FUNCTION__ ] = $wpdb->get_var( $total_in_progress_query );
		}

		return $this->_cache[ __FUNCTION__ ];
	}

	/**
	 * How many events have been migrated (failure and success).
	 *
	 * @since TBD
	 *
	 * @return int
	 */
	public function get_total_events_migrated() {
		global $wpdb;
		if ( ! isset( $this->_cache[ __FUNCTION__ ] ) ) {
			$total_migrated_query         = $wpdb->prepare(
				"SELECT COUNT(DISTINCT `ID`)
			FROM {$wpdb->posts} p
			INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = %s
			WHERE p.post_type = %s
			AND pm.meta_value IN(%s, %s)",
				Event_Report::META_KEY_MIGRATION_PHASE,
				TEC::POSTTYPE,
				Event_Report::META_VALUE_MIGRATION_PHASE_MIGRATION_SUCCESS,
				Event_Report::META_VALUE_MIGRATION_PHASE_MIGRATION_FAILURE,
			);
			$this->_cache[ __FUNCTION__ ] = $wpdb->get_var( $total_migrated_query );
		}

		return $this->_cache[ __FUNCTION__ ];
	}

	/**
	 * The total number of TEC events.
	 *
	 * @since TBD
	 *
	 * @return int
	 */
	public function get_total_events() {
		global $wpdb;
		if ( ! isset( $this->_cache[ __FUNCTION__ ] ) ) {
			$total_cnt_query              = $wpdb->prepare(
				"SELECT COUNT(*)
			FROM {$wpdb->posts} p
			WHERE p.post_type = %s",
				TEC::POSTTYPE
			);
			$this->_cache[ __FUNCTION__ ] = $wpdb->get_var( $total_cnt_query );
		}

		return $this->_cache[ __FUNCTION__ ];
	}

	/**
	 * Formulate an estimate on time to complete migration.
	 *
	 * @return float|int
	 */
	public function calculate_time_to_completion() {
		$total_events_remaining = $this->get_total_events_remaining();

		// @todo Move this and determine how we want to calculate this - potential algorithm here
		// Half a second per event? Async queue, batch lock queries, and worker operations to be considered.
		$time_per_event            = 0.5;
		$estimated_time_in_seconds = $total_events_remaining * $time_per_event;

		return $estimated_time_in_seconds;
	}
}