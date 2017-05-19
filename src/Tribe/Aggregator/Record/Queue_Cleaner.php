<?php


class Tribe__Events__Aggregator__Record__Queue_Cleaner {

	/**
	 * Default is 12hrs.
	 *
	 * @var int The time a record is allowed to stall before havint its status set to to failed since its creation in
	 *          seconds.
	 */
	protected $time_to_live = 43200; // For pre-PHP 5.6 compat, we do not define as 12 * HOUR_IN_SECONDS

	/**
	 * @var int The time a record is allowed to stall before having
	 *          its status set to failed in seconds.
	 */
	protected $stall_limit = HOUR_IN_SECONDS;

	/**
	 * Removes duplicate records for the same import ID.
	 *
	 * While it makes sense to keep track of past import records it does not make sense
	 * to keep more than one pending record for the same import ID.
	 *
	 * @param Tribe__Events__Aggregator__Record__Abstract $record A record object or a record post ID.
	 *
	 * @return int[] An array containing the deleted posts IDs.
	 */
	public function remove_duplicate_pending_records_for( Tribe__Events__Aggregator__Record__Abstract $record ) {
		if ( empty( $record->meta['import_id'] ) ) {
			return array();
		}

		$import_id = $record->meta['import_id'];

		/** @var \wpdb $wpdb */
		global $wpdb;

		$pending_status = Tribe__Events__Aggregator__Records::$status->pending;

		$query = $wpdb->prepare( "SELECT ID
			FROM {$wpdb->postmeta} pm
			JOIN {$wpdb->posts} p
			ON pm.post_id = p.ID
			WHERE p.post_type = %s
			AND p.post_status = %s
			AND pm.meta_key = '_tribe_aggregator_import_id'
			AND pm.meta_value = %s
			ORDER BY p.post_modified_gmt DESC", Tribe__Events__Aggregator__Records::$post_type, $pending_status, $import_id );

		/**
		 * Filters the query to find duplicate pending import records in respect to an
		 * import id.
		 *
		 * If the filter returns an empty value then the delete operation will be voided.
		 * This is a maintenance query that should really run so take care while modifying
		 * or voiding it!
		 *
		 * @param string $query The SQL query used to find duplicate pending import records
		 *                      in respect to an import id.
		 */
		$query = apply_filters( 'tribe_aggregator_import_queue_cleaner_query', $query );

		if ( empty( $query ) ) {
			return array();
		}

		$records = $wpdb->get_col( $query );
		array_shift( $records );

		$deleted = array();
		foreach ( $records as $to_delete ) {
			$post = wp_delete_post( $to_delete, true );
			if ( ! empty( $post ) ) {
				$deleted[] = $post->ID;
			}
		}

		return $deleted;
	}

	/**
	 * Depending from how long a record has been pending and the allowed lifespan
	 * update the record status to failed.
	 *
	 * @param Tribe__Events__Aggregator__Record__Abstract $record
	 *
	 * @return bool If the record status has been set to failed or not.
	 */
	public function maybe_fail_stalled_record( Tribe__Events__Aggregator__Record__Abstract $record ) {
		$pending = Tribe__Events__Aggregator__Records::$status->pending;
		$failed = Tribe__Events__Aggregator__Records::$status->failed;

		$post_status = $record->post->post_status;

		if ( ! in_array( $post_status, array( $pending, $failed ) ) ) {
			return false;
		}

		$id = $record->post->ID;

		if ( $post_status === $failed ) {
			delete_post_meta( $id, '_tribe_aggregator_queue' );
			Tribe__Post_Transient::instance()->delete( $id, '_tribe_aggregator_queue' );

			return true;
		}

		$created = strtotime( $record->post->post_date );
		$last_updated = strtotime( $record->post->post_modified_gmt );
		$now = time();
		$since_creation = $now - $created;
		$pending_for = $now - $last_updated;

		if ( $pending_for > $this->stall_limit || $since_creation > $this->time_to_live ) {
			$failed = Tribe__Events__Aggregator__Records::$status->failed;
			wp_update_post( array( 'ID' => $id, 'post_status' => $failed ) );
			delete_post_meta( $id, '_tribe_aggregator_queue' );
			Tribe__Post_Transient::instance()->delete( $id, '_tribe_aggregator_queue' );

			return true;
		}

		return false;
	}

	/**
	 * Gets the time, in seconds, after which a pending record is considered stalling.
	 *
	 * @return int
	 */
	public function get_stall_limit() {
		return $this->stall_limit;
	}

	/**
	 * Sets the time, in seconds, after which a pending record is considered stalling.
	 *
	 * @param int $stall_limit
	 */
	public function set_stall_limit( $stall_limit ) {
		$this->stall_limit = $stall_limit;
	}
}
