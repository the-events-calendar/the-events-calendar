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
	 * Returns an Event post ID, claimed and locked to process.
	 *
	 * @since TBD
	 * @return int|false Either an Event post ID, or `false` if no
	 *                   Event post ID could be claimed and locked.
	 */
	public function get_id_to_process() {
		$locked = $this->get_ids_to_process( 1 );

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

		$extra_where = '';
		$extra_join  = '';
		// Fetch only those that were previously touched?
		if ( $has_been_migrated ) {
			$extra_join  = "INNER JOIN {$wpdb->postmeta} pm_exists ON p.ID = pm_exists.post_id";
			$extra_where = sprintf( "AND pm_exists.meta_key ='%s'", Event_Report::META_KEY_MIGRATION_PHASE );
		}

		// Atomic query. Grab those that are not locked.
		$lock_query = "INSERT INTO wp_postmeta (post_id, meta_key, meta_value)
	    SELECT p.ID, '%s','%s'
	    FROM {$wpdb->posts} p
	    LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = '%s'
		{$extra_join}
	    WHERE p.post_type = '%s' AND pm.meta_value IS NULL
	    {$extra_where}
	    LIMIT %d";
		$lock_query = sprintf( $lock_query,
			Event_Report::META_KEY_MIGRATION_LOCK_HASH,
			$batch_uid,
			Event_Report::META_KEY_MIGRATION_LOCK_HASH,
			TEC::POSTTYPE,
			$limit
		);

		$wpdb->query( $lock_query );

		if ( ! $wpdb->rows_affected ) {
			return [];
		}

		// Let’s claim the prize.
		$fetch_query = "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '%s' AND meta_value = '%s'";
		$fetch_query = sprintf( $fetch_query, Event_Report::META_KEY_MIGRATION_LOCK_HASH, $batch_uid );

		return $wpdb->get_col( $fetch_query );
	}


	public function get_id_to_undo() {
		$ids = $this->get_ids_to_undo( 1 );

		return count( $ids ) ? reset( $ids ) : false;
	}
}