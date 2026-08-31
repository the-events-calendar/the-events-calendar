<?php
/**
 * Generates Occurrences for Events whose RSET is a list of explicit dates.
 *
 * @since TBD
 *
 * @package TEC\Events\Recurrence
 */

declare( strict_types=1 );

namespace TEC\Events\Recurrence;

use DateTime;
use DateTimeZone;
use Generator;
use TEC\Events\Custom_Tables\V1\Models\Event;
use TEC\Events\Custom_Tables\V1\Models\Extensions\Occurrence as Occurrence_Extension;
use TEC\Events\Custom_Tables\V1\Models\Occurrence;

/**
 * Class Dates_Generator.
 *
 * @since TBD
 *
 * @package TEC\Events\Recurrence
 */
class Dates_Generator {
	/**
	 * Returns the Generator producing the Occurrence rows for a dates-only Event, or
	 * `null` when the Event RSET is empty or contains recurrence rules.
	 *
	 * The row shape matches the one produced by the Events Calendar Pro RSET generator:
	 * every row is flagged as an RDATE, belongs to a new sequence, and carries the
	 * recurrence flag when the Event has more than one Occurrence.
	 *
	 * @since TBD
	 *
	 * @param Event $event A reference to the Event model to generate Occurrences for.
	 *
	 * @return Generator<Occurrence>|null The Occurrences generator, or `null` when this
	 *                                    generator does not apply to the Event.
	 */
	public function get_occurrences_generator( Event $event ): ?Generator {
		$rset = (string) $event->rset;

		if ( '' === trim( $rset ) ) {
			return null;
		}

		$parsed = Dates::parse( $rset, (int) ( $event->duration ?: 7200 ) );

		if ( null === $parsed ) {
			// Not a dates-only RSET: rule-based RSETs are handled by Events Calendar Pro.
			return null;
		}

		return $this->generate( $event, $parsed['periods'] );
	}

	/**
	 * Generates the Occurrence rows for the given periods.
	 *
	 * @since TBD
	 *
	 * @param Event                                                                $event   A reference to the Event model.
	 * @param array<int,array{start: \DateTimeImmutable, end: \DateTimeImmutable}> $periods The periods to generate rows for.
	 *
	 * @return Generator<Occurrence> The generated Occurrence models.
	 */
	private function generate( Event $event, array $periods ): Generator {
		$utc            = new DateTimeZone( 'UTC' );
		$new_sequence   = Occurrence_Extension::get_sequence( (int) $event->post_id ) + 1;
		$has_recurrence = count( $periods ) > 1;

		foreach ( $periods as $period ) {
			$start = $period['start'];
			$end   = $period['end'];

			$row = new Occurrence(
				[
					'event_id'       => $event->event_id,
					'post_id'        => $event->post_id,
					'start_date'     => $start,
					'start_date_utc' => $start->setTimezone( $utc ),
					'end_date'       => $end,
					'end_date_utc'   => $end->setTimezone( $utc ),
					// Durations via `format( 'U' )`: PHP < 8 `getTimestamp()` is wrong in the repeated DST hour.
					'duration'       => (int) $end->format( 'U' ) - (int) $start->format( 'U' ),
					'updated_at'     => new DateTime( 'now', $utc ),
					'has_recurrence' => $has_recurrence,
					'sequence'       => $new_sequence,
					'is_rdate'       => true,
				]
			);

			$row->hash = $row->generate_hash();

			yield $row;
		}
	}

	/**
	 * Returns a Generator that re-yields the existing Occurrence rows of an Event.
	 *
	 * Used to freeze the Occurrences of an Event whose RSET contains recurrence rules
	 * while no plugin providing a rule engine (Events Calendar Pro) is active: the rows
	 * are preserved as they are instead of being collapsed to a single Occurrence.
	 *
	 * @since TBD
	 *
	 * @param Event $event A reference to the Event model to freeze the Occurrences of.
	 *
	 * @return Generator<Occurrence> The existing Occurrence models.
	 */
	public function get_freeze_generator( Event $event ): Generator {
		foreach ( Occurrence::where( 'post_id', '=', (int) $event->post_id )->all() as $occurrence ) {
			yield $occurrence;
		}
	}
}
