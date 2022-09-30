<?php
/**
 * Generates occurrences for an Event.
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Events
 */

namespace TEC\Events\Custom_Tables\V1\Events\Occurrences;

use DateTime;
use DateTimeZone;
use Generator;
use TEC\Events\Custom_Tables\V1\Models\Event;
use TEC\Events\Custom_Tables\V1\Models\Occurrence;

/**
 * Class Occurrences
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Events
 */
class Occurrences_Generator {
	/**
	 * Generate all the occurrences for this event, without using large chunks of memory in the process.
	 *
	 * @since 6.0.0
	 *
	 * @param Event $event The Event model instance.
	 *
	 * @return Generator<Occurrence>|void Either the next row generated for the Event or void to indicate the Event is
	 *                                    not in a state where its Occurrences can be generated.
	 */
	public function generate_from_event( Event $event ) {
		if ( empty( $event->event_id ) ) {
			// This one has not been saved yet.
			return;
		}

		yield $this->get_single_event_row( $event );
	}

	/**
	 * Builds and returns the entry for a Single Occurrence.
	 *
	 * @since 6.0.0
	 *
	 * @param Event $event The Event model instance to generate the Occurrence entry for.
	 *
	 * @return Occurrence The Single Event Occurrence instance.
	 */
	public function get_single_event_row( Event $event ) {
		$occurrence          = new Occurrence( [
			'event_id'       => $event->event_id,
			'post_id'        => $event->post_id,
			'start_date'     => $event->start_date,
			'end_date'       => $event->end_date,
			'start_date_utc' => $event->start_date_utc,
			'end_date_utc'   => $event->end_date_utc,
			'duration'       => $event->duration,
			'updated_at'     => new DateTime( 'now', new DateTimeZone( 'utc' ) ),
		] );

		$occurrence->hash = $occurrence->generate_hash();

		return $occurrence;
	}
}
