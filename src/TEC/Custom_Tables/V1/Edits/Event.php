<?php
/**
 * Manages the insertion, update and deletion of Events in the context of
 * the Custom Tables v1 implementation.
 *
 * @since   TBD
 *
 * @package TEC\Custom_Tables\V1\Edits
 */

namespace TEC\Custom_Tables\V1\Edits;

use TEC\Custom_Tables\V1\Models\Event as Event_Model;
use TEC\Custom_Tables\V1\Models\Occurrence;

/**
 * Class Event
 *
 * @since   TBD
 *
 * @package TEC\Custom_Tables\V1\Edits
 */
class Event {
	/**
	 * Updates or inserts a Single Event information in the custom tables.
	 *
	 * Failures during the method operations will be logged.
	 *
	 * @since TBD
	 *
	 * @param int $post_id The Event post ID.
	 *
	 * @return bool Whether the Event update or insertion was successful or not.
	 */
	public function upsert( $post_id ) {
		$data = Event_Model::data_from_post( $post_id );

		if ( empty( $data['start_date'] ) || empty( $data['end_date'] ) ) {
			// The data is not there yet: this is not incorrect.
			return false;
		}

		$upsert = Event_Model::upsert( [ 'post_id' ], $data );

		if ( ! $upsert ) {
			do_action( 'tribe_log', 'error', __CLASS__, [
				'message' => 'Event upsert failed.',
				'data'    => $data,
			] );

			return false;
		}

		$event = Event_Model::find( (int) $post_id, 'post_id' );

		if ( ! $event instanceof Event_Model ) {
			do_action( 'tribe_log', 'error', __CLASS__, [
				'message' => 'Cannot fetch Event model.',
				'post_id' => $post_id,
			] );

			return false;
		}

		try {
			$event->occurrences()->save_occurrences();

			if ( 1 !== Occurrence::where( 'post_id', '=', (int) $post_id )->count() ) {
				do_action( 'tribe_log', 'error', __CLASS__, [
					'message' => 'Event Occurrences insertion failed.',
					'post_id' => $post_id,
				] );

				return false;
			}
		} catch ( \Exception $e ) {
			do_action( 'tribe_log', 'error', __CLASS__, [
				'message' => 'Event Occurrences save operation failed.',
				'post_id' => $post_id,
			] );

			return false;
		}

		return true;
	}

	/**
	 * Deletes an Event rows from the events and occurrences tables.
	 *
	 * @since TBD
	 *
	 * @param int $post_id The Event post ID.
	 *
	 * @return bool Whether the Event rows deletion was successful or not.
	 */
	public function delete( $post_id ) {
		$event_rows      = Event_Model::where( 'post_id', '=', $post_id );
		$existing_events = $event_rows->count();
		$deleted_events  = $event_rows->delete();

		if ( $deleted_events !== $existing_events ) {
			do_action( 'tribe_log', 'error', __CLASS__, [
				'message' => 'Event row could not be deleted from the database.',
				'post_id' => $post_id,
			] );

			return false;
		}

		// Occurrences should be removed as part of the DB structure, but let's make sure.
		$occurrence_rows      = Occurrence::where( 'post_id', '=', $post_id );
		$existing_occurrences = $occurrence_rows->count();
		$deleted_occurrences  = $occurrence_rows->delete();
		if ( $deleted_occurrences !== $existing_occurrences ) {
			do_action( 'tribe_log', 'error', __CLASS__, [
				'message' => 'Event Occurrences rows could not be deleted from the database.',
				'post_id' => $post_id,
			] );

			return false;
		}

		return true;
	}
}
