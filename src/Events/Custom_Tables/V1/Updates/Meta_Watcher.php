<?php
/**
 * Watches any post meta updates, insertions or deletions for keys relevant
 * to the modeling of an Event.
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Updates
 */

namespace TEC\Events\Custom_Tables\V1\Updates;

use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use Tribe__Events__Main as TEC;

/**
 * Class Meta_Watcher
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Updates
 */
class Meta_Watcher {

	/**
	 * A stack of Event post IDs that should be updated in this request.
	 *
	 * @since 6.0.0
	 *
	 * @var array<int>
	 */
	private $marked_ids = [];

	/**
	 * A list of meta keys that are integral to the modeling of an Event, tracked
	 * for changes to make sure the custom tables are updated if one or more of
	 * these fields is updated.
	 *
	 * @since 6.0.0
	 *
	 * @var array<string>
	 */
	private $tracked_meta_keys = [
		'_EventStartDate',
		'_EventStartDateUTC',
		'_EventEndDate',
		'_EventEndDateUTC',
		'_EventDuration',
		'_EventTimezone',
		'_EventAllDay',
	];

	/**
	 * Returns the filtered set of meta keys that should be tracked to detect
	 * whether an Event post custom tables data might require update or not.
	 *
	 * @since 6.0.0
	 *
	 * @param int $id  The ID of the object (could not be an Event post!)
	 *                 the filters are being applied for.
	 *
	 * @return array<string> The filtered set of tracked meta keys.
	 */
	public function get_tracked_meta_keys( $id = null ) {
		/**
		 * Allows filtering the list of meta keys that, when modified, should trigger
		 * an update to the custom tables' data.
		 *
		 * Note: this filter will only run before, or on, the `init` action; after `init`
		 * the filtered value will be cached for the rest of the request.
		 *
		 * @since 6.0.0
		 *
		 * @param array<string> The list of tracked meta keys.
		 * @param int|null $id  The ID of the object (could not be an Event post!)
		 *                      the filters are being applied for, or `null` if the tracked keys
		 *                      should not be specific to an Event.
		 */
		return apply_filters(
			'tec_events_custom_tables_v1_tracked_meta_keys',
			$this->tracked_meta_keys,
			$id
		);
	}

	/**
	 * If the addition, update or deletion is for a meta field used to model
	 * an Event, then mark the Event as requiring an update to its custom tables
	 * information.
	 *
	 * @since 6.0.0
	 *
	 * @param int    $object_id The ID  of the object (might be other than an Event post!)
	 *                          whose meta is being updated.
	 * @param string $meta_key  The meta key that is being updated.
	 */
	public function mark_for_update( $object_id, $meta_key ) {
		if ( in_array( (int) $object_id, $this->marked_ids, true ) ) {
			// Already tracked.
			return;
		}

		$tracked_meta_keys = $this->get_tracked_meta_keys( $object_id );

		if ( ! in_array( $meta_key, $tracked_meta_keys, true ) ) {
			// Not a meta key we track.
			return;
		}

		if ( ! Occurrence::is_valid_occurrence_id( $object_id ) ) {
			return;
		}

		$this->marked_ids[] = (int) $object_id;
	}

	/**
	 * Returns the current list of IDs marked for update.
	 *
	 * @since 6.0.0
	 *
	 * @return array<int> The current list of IDs marked for update.
	 */
	public function get_marked_ids() {
		return $this->marked_ids;
	}

	/**
	 * Returns the first element of the marked IDs.
	 *
	 * Note: the order in which elements are popped is the inverse
	 * of the order in which they are pushed: pop from the bottom, push
	 * to the top.
	 *
	 * @since 6.0.0
	 *
	 * @return int|null Either the next oldest tracked ID, or `null` if not found.
	 */
	public function pop() {
		return array_shift( $this->marked_ids );
	}

	/**
	 * Adds an ID back into the last position of the FIFO queue.
	 *
	 * Note: the order in which elements are pushed is the inverse
	 * of the order in which they are popped: pop from the bottom, push
	 * to the top.
	 * Only Event IDs are allowed and are guaranteed to be added at most once.
	 *
	 * @since 6.0.0
	 *
	 * @param int $id The post ID to add in the last position of the FIFO queue.
	 */
	public function push( $id ) {
		if ( ! Occurrence::is_valid_occurrence_id( $id ) || in_array( (int) $id, $this->marked_ids, true ) ) {
			return;
		}
		$this->marked_ids[] = (int) $id;
	}

	/**
	 * Returns whether an Event post ID is currently tracked by the meta watcher or not.
	 *
	 * @since 6.0.0
	 *
	 * @param int $post_id The Event post ID to check.
	 */
	public function is_tracked( $post_id ) {
		return in_array( (int) $post_id, $this->marked_ids, true );
	}

	/**
	 * Removes an ID from the marked IDs.
	 *
	 * @since 6.0.0
	 *
	 * @param int ...$post_ids The post ID(s) to remove from the marked IDs.
	 */
	public function remove( int ...$post_ids ): void {
		foreach ( $post_ids as $post_id ) {
			$key = array_search( (int) $post_id, $this->marked_ids, true );
			if ( false === $key ) {
				return;
			}
			unset( $this->marked_ids[ $key ] );
		}
		// Consolidate keys.
		$this->marked_ids = array_values( $this->marked_ids );
	}
}