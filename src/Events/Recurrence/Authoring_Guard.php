<?php
/**
 * The single oracle deciding whether, and what, the dates authoring UI can edit.
 *
 * Mirrors the Events Calendar Pro authoring model: UI rows map to the AUTHORED
 * `_EventRecurrence` date rules, never to generated Occurrence rows; rule-based
 * recurrence (a Pro feature) locks the free UI; single Occurrences (provisional
 * post IDs) are not editable entry points for the whole set of dates.
 *
 * @since TBD
 *
 * @package TEC\Events\Recurrence
 */

declare( strict_types=1 );

namespace TEC\Events\Recurrence;

use DateTimeImmutable;
use DateTimeZone;
use Exception;
use TEC\Events\Custom_Tables\V1\Models\Event;
use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use TEC\Events\Custom_Tables\V1\Models\Provisional_Post;

/**
 * Class Authoring_Guard.
 *
 * @since TBD
 *
 * @package TEC\Events\Recurrence
 */
class Authoring_Guard {
	/**
	 * Returns whether the Event recurrence is rule-based, locking the dates authoring.
	 *
	 * Rule-based data is authored by Events Calendar Pro: the free UI must neither
	 * present nor overwrite it. Both the canonical meta and the derived RSET are
	 * consulted: an RSET containing rules with no authored meta (e.g. an Event
	 * created straight through the ORM) is just as locked.
	 *
	 * @since TBD
	 *
	 * @param int $post_id The Event post ID (a provisional ID is accepted).
	 *
	 * @return bool Whether the Event dates are rule-based and locked for authoring.
	 */
	public function is_rule_locked( int $post_id ): bool {
		$post_id         = Occurrence::normalize_id( $post_id );
		$recurrence_meta = get_post_meta( $post_id, '_EventRecurrence', true );

		if ( ! empty( $recurrence_meta ) ) {
			return ! Date_Rules::is_dates_only_meta( $recurrence_meta );
		}

		$event = Event::find( $post_id, 'post_id' );

		if ( ! $event instanceof Event ) {
			return false;
		}

		$rset = trim( (string) $event->rset );

		return '' !== $rset && ! Dates::is_dates_only( $rset );
	}

	/**
	 * Returns whether the post ID is a provisional Occurrence one.
	 *
	 * An Occurrence edit screen edits one Occurrence: presenting the whole set of
	 * dates for authoring there is wrong; the recurring Event is the entry point.
	 *
	 * @since TBD
	 *
	 * @param int $post_id The post ID being edited.
	 *
	 * @return bool Whether the post ID is a provisional Occurrence post ID.
	 */
	public function is_occurrence_edit( int $post_id ): bool {
		return tribe( Provisional_Post::class )->is_provisional_post_id( $post_id );
	}

	/**
	 * Returns the authored additional dates of an Event, the editable UI rows.
	 *
	 * The rows come from the authored `_EventRecurrence` date rules; when an Event
	 * has a dates-only RSET but no authored meta (e.g. created through the ORM),
	 * the rows are derived from the RSET instead — saving them writes the canonical
	 * meta back (repair on save). The Event's own date is never among the rows.
	 *
	 * @since TBD
	 *
	 * @param int $post_id The Event post ID (a provisional ID is accepted).
	 *
	 * @return array<int,array{start: DateTimeImmutable, end: DateTimeImmutable}> The authored periods.
	 */
	public function get_authored_periods( int $post_id ): array {
		$post_id = Occurrence::normalize_id( $post_id );
		$event   = Event::find( $post_id, 'post_id' );

		if ( ! $event instanceof Event ) {
			return [];
		}

		$recurrence_meta = get_post_meta( $post_id, '_EventRecurrence', true );

		if ( ! empty( $recurrence_meta ) ) {
			if ( ! Date_Rules::is_dates_only_meta( $recurrence_meta ) ) {
				return [];
			}

			try {
				$timezone    = new DateTimeZone( (string) $event->timezone );
				$event_start = new DateTimeImmutable( (string) $event->start_date, $timezone );
				$event_end   = new DateTimeImmutable( (string) $event->end_date, $timezone );
			} catch ( Exception $e ) {
				return [];
			}

			return (array) Date_Rules::to_periods( $recurrence_meta, $event_start, $event_end, $timezone );
		}

		$rset = trim( (string) $event->rset );

		if ( '' === $rset || ! Dates::is_dates_only( $rset ) ) {
			return [];
		}

		$parsed = Dates::parse( $rset, (int) ( $event->duration ?: 7200 ) );

		if ( null === $parsed ) {
			return [];
		}

		$dtstart_key = $parsed['dtstart'] instanceof DateTimeImmutable
			? (int) $parsed['dtstart']->format( 'U' )
			: null;

		return array_values(
			array_filter(
				$parsed['periods'],
				static function ( array $period ) use ( $dtstart_key ): bool {
					// The DTSTART period is the Event's own date, not an additional one.
					return (int) $period['start']->format( 'U' ) !== $dtstart_key;
				}
			)
		);
	}
}
