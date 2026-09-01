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

	/**
	 * Returns a read-only summary of the Event's scheduled dates.
	 *
	 * The locked authoring UI uses the summary to still show WHAT is scheduled — the
	 * generated Occurrence rows — while the rules that generated them stay Events
	 * Calendar Pro territory. Reading the Occurrence rows is display-only here, never
	 * an authoring source.
	 *
	 * @since TBD
	 *
	 * @param int $post_id The Event post ID (a provisional ID is accepted).
	 * @param int $limit   The maximum number of dates to include in the summary.
	 *
	 * @return array{count: int, next_dates: array<int,DateTimeImmutable>} The total scheduled dates
	 *                                                                     and the next upcoming ones.
	 */
	public function get_dates_summary( int $post_id, int $limit = 3 ): array {
		$summary = [
			'count'      => 0,
			'next_dates' => [],
		];

		if ( ! tribe()->getVar( 'ct1_fully_activated', false ) ) {
			// The Occurrences table might not exist at all.
			return $summary;
		}

		$post_id = Occurrence::normalize_id( $post_id );
		$count   = (int) Occurrence::where( 'post_id', '=', $post_id )->count();

		if ( 0 === $count ) {
			return $summary;
		}

		$summary['count'] = $count;

		$occurrences = Occurrence::where( 'post_id', '=', $post_id )
								->where( 'start_date', '>=', current_time( 'mysql' ) )
								->order_by( 'start_date', 'ASC' )
								->limit( $limit )
								->all();
		$occurrences = iterator_to_array( $occurrences, false );

		if ( ! count( $occurrences ) ) {
			// All the dates are in the past: show the final ones instead.
			$occurrences = iterator_to_array(
				Occurrence::where( 'post_id', '=', $post_id )
						->order_by( 'start_date', 'DESC' )
						->limit( $limit )
						->all(),
				false
			);
			$occurrences = array_reverse( $occurrences );
		}

		foreach ( $occurrences as $occurrence ) {
			try {
				$summary['next_dates'][] = new DateTimeImmutable( (string) $occurrence->start_date );
			} catch ( Exception $e ) {
				continue;
			}
		}

		return $summary;
	}
}
