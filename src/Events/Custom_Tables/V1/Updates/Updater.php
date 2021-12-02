<?php
/**
 * Handles the update of the Events custom tables information.
 *
 * @since   TBD
 *
 * @package TEC\Events_Pro\Updates
 */

namespace TEC\Events\Custom_Tables\V1\Updates;

use TEC\Events\Custom_Tables\V1\Models\Event;
use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use Tribe__Events__Main as TEC;
use WP_Post;
use WP_REST_Request;

/**
 * Class Updater
 *
 * @since   TBD
 *
 * @package TEC\Events\Custom_Tables\V1\Updates
 */
class Updater {
	/**
	 * A stack of Event post IDs that should be updated in this request.
	 *
	 * @since TBD
	 *
	 * @var array<int>
	 */
	private $booked_ids = [];

	/**
	 * A list of meta keys that are integral to the modeling of an Event, tracked
	 * for changes to make sure the custom tables are updated if one or more of
	 * these fields is updated.
	 *
	 * @since TBD
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
	];

	/**
	 * If the addition, update or deletion is for a meta field used to model
	 * an Event, then mark the Event as requiring an update to its custom tables
	 * information.
	 *
	 * @since TBD
	 *
	 * @param int    $object_id The ID  of the object (might be other than an Event post!)
	 *                          whose meta is being updated.
	 * @param string $meta_key  The meta key that is being updated.
	 */
	public function mark_for_update( $object_id, $meta_key ) {
		if ( in_array( (int) $object_id, $this->booked_ids, true ) ) {
			// Already tracked.
			return;
		}

		$tracked_meta_keys = $this->get_tracked_meta_keys();

		if ( ! in_array( $meta_key, $tracked_meta_keys, true ) ) {
			// Not a meta key we track.
			return;
		}

		$post_id = Occurrence::normalize_id( $object_id );

		if ( TEC::POSTTYPE !== get_post_type( $post_id ) ) {
			// Not an Event.
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
	private function get_tracked_meta_keys() {
		if ( did_action( 'init' ) ) {
			/*
			 * Filtering of this value should happen before, or during, the `init` action.
			 * After that let's avoid running the Filter API on each call.
			 */
			return $this->tracked_meta_keys;
		}

		/**
		 * Allows filtering the list of meta keys that, when modified, should trigger
		 * an update to the custom tables' data.
		 *
		 * Note: this filter will only run before, or on, the `init` action; after `init`
		 * the filtered value will be cached for the rest of the request.
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

	/**
	 * Updates the custom tables' information for each Event post whose important
	 * meta was updated during the request.
	 *
	 * @since TBD
	 */
	public function commit_updates() {
		if ( empty( $this->booked_ids ) ) {
			return;
		}

		$request = Requests::from_http_request();

		foreach ( $this->booked_ids as $booked_id ) {
			$this->commit_post_updates( $booked_id, $request );
		}
	}

	/**
	 * Updates the custom tables' information for an Event post whose important
	 * meta was updated.
	 *
	 * After a first update, the post ID is removed from the marked-for-update stack
	 * and will not be automatically updated again during the request.
	 *
	 * @since TBD
	 *
	 * @param WP_REST_Request $request      A reference to the object modeling the current request,
	 *                                      if any. Mind the WP_REST_Request class can be used to
	 *                                      model a non-REST API request too!
	 *
	 * @param int             $post_id      The post ID, not guaranteed to be an Event post ID if this
	 *                                      method is not called from this class!
	 */
	public function commit_post_updates( $post_id, WP_REST_Request $request ) {
		if ( ! in_array( (int) $post_id, $this->booked_ids ) ) {
			// The post relevant meta was not changed, do nothing.
			return false;
		}

		/**
		 * Fires before the default The Events Calendar logic to update an Event custom tables
		 * information is applied.
		 * Returning a non `null` value from this filter will prevent the default logic from running.
		 *
		 * @since TBD
		 *
		 * @param mixed|null      $updated      Whether the post custom tables information was updated by any
		 *                                      filtering function or not. If a non `null` value is returned
		 *                                      from this filter, then the default logic will not be applied.
		 * @param int             $post_id      The post ID of the Event whose custom tables information should be
		 *                                      updated.
		 * @param WP_REST_Request $request      A reference to the object modeling the current request,
		 *                                      if any. Mind the WP_REST_Request class can be used to
		 *                                      model a non-REST API request too!
		 *
		 * @return bool Whether the custom tables' updates were correctly applied or not.
		 */
		$updated = apply_filters( 'tec_events_custom_tables_v1_commit_post_updates', null, $post_id, $request );

		if ( null === $updated ) {
			$updated = $this->update_custom_tables( $post_id, $updated );
		}

		if ( $updated ) {
			// Remove the post ID from the list of post IDs still to update.
			$this->booked_ids = array_diff( $this->booked_ids, [ $post_id ] );
		}

		return true;
	}

	/**
	 * Updates the custom tables' information for an Event post whose important meta
	 * was updated in the context of a REST request.
	 *
	 * After a first update, the post ID is removed from the marked-for-update stack
	 * and will not be automatically updated again during the request.
	 *
	 * @since TBD
	 *
	 * @param WP_Post         $post    A reference to the post object representing the Event
	 *                                 post.
	 * @param WP_REST_Request $request A reference to the REST API request object that is,
	 *                                 currently, being processed.
	 *
	 * @return bool Whether the custom tables' updates were correctly applied or not.
	 */
	public function commit_post_rest_update( WP_Post $post, WP_REST_Request $request ) {
		if ( ! in_array( $post->ID, $this->booked_ids, true ) ) {
			return false;
		}

		$this->commit_post_updates( $post->ID, $request );
	}

	/**
	 * Updates the custom tables with the data for an Event post.
	 *
	 * @since TBD
	 *
	 * @param int $post_id The Even post ID.
	 *
	 * @return bool Whether the update was successful or not.
	 */
	private function update_custom_tables( int $post_id ): bool {
		$event_data = Event::data_from_post( $post_id );
		$upserted   = Event::upsert( [ 'post_id' ], $event_data );

		if ( ! $upserted ) {
			// At this stage the data might just be missing: it's fine.
			return false;
		}

		$event = Event::find( $post_id, 'post_id' );

		if ( ! $event instanceof Event ) {
			do_action( 'tribe_log', 'error', __CLASS__, [
				'message' => 'Event fetching after insertion failed.',
				'post_id' => $post_id,
			] );

			return false;
		}

		try {
			$event->occurrences()->save_occurrences();
		} catch ( \Exception $e ) {
			do_action( 'tribe_log', 'error', __CLASS__, [
				'message' => 'Event Occurrence update failed.',
				'post_id' => $post_id,
				'error'   => $e->getMessage(),
			] );

			return false;
		}

		return true;
	}

	/**
	 * Deletes an Event custom tables information.
	 *
	 * @since TBD
	 *
	 * @param int $post_id The deleted Event post ID.
	 *
	 * @return int|false Either the number of affected rows, or `false` on failure.
	 */
	public function delete_custom_tables_data( $post_id, WP_Post $post ) {
		if ( TEC::POSTTYPE !== get_post_type( $post_id ) ) {
			// Not an Event post.
			return false;
		}

		$affected = (int) Event::where( 'post_id', (int) $post_id )->delete();
		$affected += (int) Occurrence::where( 'post_id', $post_id )->delete();

		/**
		 * Fires after the Event custom tables data has been removed from the database.
		 *
		 * By the time this action fires, the Event post has not yet been removed from
		 * the posts tables.
		 *
		 * @since TBD
		 *
		 * @param int     $affected The number of affected rows, across all custom tables.
		 *                          Keep in mind db-level deletions will not be counted in
		 *                          this value!
		 * @param int     $post_id  The Event post ID.
		 * @param WP_Post $post     A reference to the deleted Event post.
		 *
		 */
		$affected = apply_filters( 'tec_events_custom_tables_v1_delete_post', $affected, $post_id, $post );

		return $affected;
	}
}
