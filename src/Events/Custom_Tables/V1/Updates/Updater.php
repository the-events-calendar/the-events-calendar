<?php
/**
 * ${CARET}
 *
 * @since   TBD
 *
 * @package TEC\Events_Pro\Updates
 */

namespace TEC\Events_Pro\Updates;

use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use Tribe__Events__Main as TEC;
use WP_Post;
use WP_REST_Request;

class Updater {
	private $booked_ids = [];

	private $tracked_meta_keys = [
		'_EventStartDate',
		'_EventStartDateUTC',
		'_EventEndDate',
		'_EventEndDateUTC',
		'_EventDuration',
		'_EventTimezone',
	];

	public function mark_for_update( $object_id, $meta_key ) {
		$tracked_meta_keys = $this->getTracked_meta_keys();

		if ( ! in_array( $meta_key, $tracked_meta_keys, true ) ) {
			return;
		}

		if ( in_array( (int) $object_id, $this->booked_ids, true ) ) {
			return;
		}

		$post_id = Occurrence::normalize_id( $object_id );

		if ( TEC::POSTTYPE !== get_post_type( $post_id ) ) {
			return;
		}

		$this->booked_ids[] = (int) $object_id;
	}

	/**
	 * Returns the filtered set of meta keys that should be tracked to detect
	 * whether an Event post custom tables data might require update or not.
	 *
	 * @since TBD
	 *
	 * @return array<string> The filtered set of tracked meta keys.
	 */
	private function getTracked_meta_keys() {
		if ( did_action( 'init' ) ) {
			return $this->tracked_meta_keys;
		}

		/**
		 * Allows filtering the list of meta keys that, when modified, should trigger
		 * an update to the custom tables data.
		 *
		 * @since TBD
		 *
		 * @param array<string> The list of tracked meta keys.
		 */
		$this->tracked_meta_keys = apply_filters(
			'tec_events_custom_tables_v1_tracked_meta_keys',
			$this->tracked_meta_keys
		);

		return $this->tracked_meta_keys;
	}

	public function commit_updates() {
		if ( empty( $this->booked_ids ) ) {
			return;
		}

		foreach ( $this->booked_ids as $booked_id ) {
			$this->commit_post_updates( $booked_id );
		}
	}

	public function commit_post_updates( $post_id ) {
		$updated = apply_filters( 'tec_events_custom_tables_v1_commit_post_updates', null );

		if ( null === $updated ) {
			// @todo default TEC logic here.
		}

		$this->booked_ids = array_diff( $this->booked_ids, $post_id );
	}

	public function commit_post_rest_update( WP_Post $post, WP_REST_Request $request ) {
		if ( ! in_array( $post->ID, $this->booked_ids, true ) ) {
			return false;
		}

		$this->commit_post_updates( $post->ID );
	}
}
