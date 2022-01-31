<?php
/**
 * Class responsible for top level database transactions, regarding changes
 * to Events and their related database entries/tables.
 *
 * @since   TBD
 *
 * @package TEC\Events\Custom_Tables\V1\Updates
 */

namespace TEC\Events\Custom_Tables\V1\Updates;

use Exception;
use TEC\Events\Custom_Tables\V1\Models\Event;
use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use Tribe__Events__Main as TEC;

/**
 * Class Events
 *
 * @since   TBD
 *
 * @package TEC\Events\Custom_Tables\V1\Updates
 */
class Events {

	/**
	 * Updates an Event by post ID.
	 *
	 * @since TBD
	 *
	 * @param int $post_id The Event post ID.
	 *
	 * @return bool Whether the update was correctly performed or not.
	 */
	public function update( $post_id ) {
		// Make sure to update the real thing.
		$post_id = Occurrence::normalize_id( $post_id );

		if ( TEC::POSTTYPE !== get_post_type( $post_id ) ) {
			return false;
		}

		$event_data = Event::data_from_post( $post_id );
		$upsert     = Event::upsert( [ 'post_id' ], $event_data );

		if ( ! $upsert ) {
			// At this stage the data might just be missing: it's fine.
			return false;
		}

		$event = Event::find( $post_id, 'post_id' );

		if ( ! $event instanceof Event ) {
			do_action( 'tribe_log', 'error', 'Event fetching after insertion failed.', [
				'source'  => __CLASS__,
				'slug'    => 'fetch-after-upsert',
				'post_id' => $post_id,
			] );

			return false;
		}

		try {
			$occurrences = $event->occurrences();
			$occurrences->save_occurrences();
		} catch ( Exception $e ) {
			do_action( 'tribe_log', 'error', 'Event Occurrence update failed.', [
				'source'  => __CLASS__,
				'slug'    => 'update-occurrences',
				'post_id' => $post_id,
				'error'   => $e->getMessage(),
			] );

			return false;
		}

		return true;
	}

	/**
	 * Deletes an Event and related data from the custom tables.
	 *
	 * @since TBD
	 *
	 * @param int $post_id The Event post ID.
	 *
	 * @return int|false Either the number of affected rows, or `false` to
	 *                   indicate a failure.
	 */
	public function delete( $post_id ) {
		if ( TEC::POSTTYPE !== get_post_type( $post_id ) ) {
			// Not an Event post.
			return false;
		}

		$affected = Event::where( 'post_id', (int) $post_id )->delete();
		$affected += Occurrence::where( 'post_id', $post_id )->delete();

		return $affected;
	}

	/**
	 * Rebuilds the known Events dates range setting the values of the options
	 * used to track the earliest Event start date and the latest Event end date.
	 *
	 * @since TBD
	 *
	 * @return bool Whether the values were updated or not. They will not be updated
	 *              if no Occurrences are found in the database.
	 */
	public function rebuild_known_range(){
		$first = Occurrence::order_by( 'start_date_utc', 'ASC' )->first();
		$last  = Occurrence::order_by( 'end_date_utc', 'DESC' )->first();

		if ( ! ( $first instanceof Occurrence && $last instanceof Occurrence ) ) {
			return false;
		}

		tribe_update_option( 'earliest_date', $first->start_date );
		tribe_update_option( 'latest_date', $last->end_date );

		return true;
	}
}