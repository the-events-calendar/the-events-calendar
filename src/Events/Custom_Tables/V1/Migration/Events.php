<?php
/**
 * ${CARET}
 *
 * @since   TBD
 *
 * @package TEC\Events\Custom_Tables\V1\Migration;
 */

namespace TEC\Events\Custom_Tables\V1\Migration;

/**
 * Class Events.
 *
 * @since   TBD
 *
 * @package TEC\Events\Custom_Tables\V1\Migration;
 */
class Events {

	/**
	 * Returns an Event post ID, claimed and locked to process.
	 *
	 * @since TBD
	 *
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
	 * @param int $limit The max number of Event post IDs to return
	 *
	 * @return array<int> An array of claimed and locked Event post IDs.
	 */
	public function get_ids_to_process( $limit ) {
		global $wpdb;
		// @todo query from doc, LIMIT $limit
		$query  = '';
		$locked = $wpdb->query( $query );

		return $locked;
	}

	public function get_ids_to_cancel( $int ) {
		// @todo
	}

	public function get_ids_to_undo( $int ) {
		// @todo
	}

	public function get_id_to_undo() {
		// @todo
	}
}