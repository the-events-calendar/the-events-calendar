<?php
/**
 * ${CARET}
 *
 * @since   TBD
 *
 * @package TEC\Events\Custom_Tables\V1\Updates
 */

namespace TEC\Events\Custom_Tables\V1\Updates;

use TEC\Events\Custom_Tables\V1\Models\Event;
use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use Tribe__Events__Main as TEC;

class Models {

	public function update( $post_id ) {
		$event_data = Event::data_from_post( $post_id );
		$upsert     = Event::upsert( [ 'post_id' ], $event_data );

		if ( ! $upsert ) {
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
			$occurrences = $event->occurrences();
			$occurrences->save_occurrences();
		} catch ( Exception $e ) {
			do_action( 'tribe_log', 'error', __CLASS__, [
				'message' => 'Event Occurrence update failed.',
				'post_id' => $post_id,
				'error'   => $e->getMessage(),
			] );

			return false;
		}

		return true;
	}

	public function delete( $post_id ) {
		if ( TEC::POSTTYPE !== get_post_type( $post_id ) ) {
			// Not an Event post.
			return false;
		}

		$affected = Event::where( 'post_id', (int) $post_id )->delete();
		$affected += Occurrence::where( 'post_id', $post_id )->delete();
	}
}