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
}