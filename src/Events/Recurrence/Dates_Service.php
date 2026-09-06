<?php
/**
 * The public service to author the explicit dates of an Event.
 *
 * The canonical authored format is the legacy `_EventRecurrence` meta (as date rules);
 * the Event `rset` is derived from it, and the Occurrence rows are regenerated from the
 * RSET. Events Calendar Pro reads and writes the same formats, so dates authored here
 * can be extended with recurrence rules there, and vice versa.
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

/**
 * Class Dates_Service.
 *
 * @since TBD
 *
 * @package TEC\Events\Recurrence
 */
class Dates_Service {
	/**
	 * Sets the additional dates of an Event, regenerating its Occurrences.
	 *
	 * @since TBD
	 *
	 * @param int                                                                              $post_id The Event post ID (a provisional ID is accepted).
	 * @param array<int,array{start: DateTimeImmutable|string, end: DateTimeImmutable|string}> $dates The additional dates; strings are parsed in the
	 *                                                                                                Event timezone.
	 *
	 * @return bool Whether the dates were set and the Occurrences regenerated or not.
	 */
	public function set_dates( int $post_id, array $dates ): bool {
		$post_id = Occurrence::normalize_id( $post_id );
		$event   = $this->get_or_create_event( $post_id );

		if ( ! $event instanceof Event ) {
			return false;
		}

		if ( tribe( Authoring_Guard::class )->is_rule_locked( $post_id ) ) {
			// Rule-based recurrence is not authored here: writing dates would destroy the rules.
			return false;
		}

		try {
			$timezone = new DateTimeZone( (string) $event->timezone );

			$periods = [];
			foreach ( $dates as $date ) {
				if ( ! is_array( $date ) || ! isset( $date['start'], $date['end'] ) ) {
					// A malformed entry would silently author an Occurrence at the current time.
					return false;
				}

				foreach ( [ 'start', 'end' ] as $bound ) {
					if ( ! $date[ $bound ] instanceof DateTimeImmutable && '' === trim( (string) $date[ $bound ] ) ) {
						// An empty string passes `isset()` and would resolve to the current time.
						return false;
					}
				}

				$start = $date['start'] instanceof DateTimeImmutable ? $date['start'] : new DateTimeImmutable( (string) $date['start'], $timezone );
				$end   = $date['end'] instanceof DateTimeImmutable ? $date['end'] : new DateTimeImmutable( (string) $date['end'], $timezone );

				$periods[] = [
					'start' => $start,
					'end'   => $end,
				];
			}
		} catch ( Exception $e ) {
			return false;
		}

		if ( ! count( $periods ) ) {
			return $this->remove_dates( $post_id );
		}

		return $this->write_dates( $post_id, $periods );
	}

	/**
	 * Writes the additional dates of an Event, regenerating its Occurrences.
	 *
	 * No lock check: the caller owns the lock decision. `set_dates()` refuses rule-based
	 * Events before calling this; the conversion of a rule-based Event to individual
	 * dates calls it on purpose.
	 *
	 * @since TBD
	 *
	 * @param int                                                                $post_id The Event post ID.
	 * @param array<int,array{start: DateTimeImmutable, end: DateTimeImmutable}> $periods The additional dates, in the Event timezone.
	 *
	 * @return bool Whether the dates were written and the Occurrences regenerated or not.
	 */
	public function write_dates( int $post_id, array $periods ): bool {
		$post_id = Occurrence::normalize_id( $post_id );
		$event   = Event::find( $post_id, 'post_id' );

		if ( ! $event instanceof Event || ! count( $periods ) ) {
			return false;
		}

		try {
			$timezone    = new DateTimeZone( (string) $event->timezone );
			$event_start = new DateTimeImmutable( (string) $event->start_date, $timezone );
			$event_end   = new DateTimeImmutable( (string) $event->end_date, $timezone );
		} catch ( Exception $e ) {
			return false;
		}

		// The legacy meta is the canonical authored format.
		$meta = Date_Rules::to_meta( $periods, $event_start, $event_end );
		update_post_meta( $post_id, '_EventRecurrence', $meta );
		if ( get_post_meta( $post_id, '_EventRecurrence', true ) !== $meta ) {
			return false;
		}

		// The RSET is derived from it.
		$rset = Dates::serialize( $event_start, $event_end, $periods );
		if ( false === $event->update( [ 'rset' => $rset ] ) ) {
			return false;
		}

		$this->regenerate( $post_id );

		return true;
	}

	/**
	 * Collapses an Event to its own single date, regenerating its Occurrences.
	 *
	 * No lock check: the caller owns the lock decision, see `write_dates()`.
	 *
	 * @since TBD
	 *
	 * @param int $post_id The Event post ID.
	 *
	 * @return bool Whether the Event was collapsed or not.
	 */
	public function write_single( int $post_id ): bool {
		$post_id = Occurrence::normalize_id( $post_id );
		$event   = Event::find( $post_id, 'post_id' );

		if ( ! $event instanceof Event ) {
			return false;
		}

		delete_post_meta( $post_id, '_EventRecurrence' );
		$event->update( [ 'rset' => '' ] );

		$this->regenerate( $post_id );

		return true;
	}

	/**
	 * Removes all the additional dates of an Event, collapsing it to a single Occurrence.
	 *
	 * @since TBD
	 *
	 * @param int $post_id The Event post ID (a provisional ID is accepted).
	 *
	 * @return bool Whether the dates were removed or not.
	 */
	public function remove_dates( int $post_id ): bool {
		$post_id = Occurrence::normalize_id( $post_id );
		$event   = $this->get_or_create_event( $post_id );

		if ( ! $event instanceof Event ) {
			return false;
		}

		$recurrence_meta = get_post_meta( $post_id, '_EventRecurrence', true );

		if ( ! empty( $recurrence_meta ) && ! Date_Rules::is_dates_only_meta( $recurrence_meta ) ) {
			// Rule-based recurrence is not authored here.
			return false;
		}

		return $this->write_single( $post_id );
	}

	/**
	 * Returns the dates of all the Occurrences of an Event, from the Occurrences table.
	 *
	 * @since TBD
	 *
	 * @param int $post_id The Event post ID (a provisional ID is accepted).
	 *
	 * @return array<int,array{start: string, end: string, occurrence_id: int, provisional_id: ?int}> The Occurrence dates, ascending.
	 */
	public function get_dates( int $post_id ): array {
		$post_id = Occurrence::normalize_id( $post_id );

		$dates = [];

		foreach ( Occurrence::where( 'post_id', '=', $post_id )->order_by( 'start_date', 'ASC' )->all() as $occurrence ) {
			$dates[] = [
				'start'          => $occurrence->start_date,
				'end'            => $occurrence->end_date,
				'occurrence_id'  => (int) $occurrence->occurrence_id,
				'provisional_id' => $occurrence->provisional_id,
			];
		}

		return $dates;
	}

	/**
	 * Creates the Event row on first editor save, after WordPress saved date meta.
	 *
	 * Editor hooks run before the deferred CT1 commit. Requiring an existing row
	 * loses additional dates on the first publish of a genuine auto-draft.
	 *
	 * @since TBD
	 * @param int $post_id The durable Event post ID.
	 * @return Event|null The persisted Event, or null if its dates are not ready.
	 */
	private function get_or_create_event( int $post_id ): ?Event {
		$event = Event::find( $post_id, 'post_id' );
		if ( $event instanceof Event ) {
			return $event;
		}
		$data = Event::data_from_post( $post_id );
		if ( ! $data || ! $data['start_date'] || ! $data['end_date'] || ! $data['timezone'] || wp_is_post_revision( $post_id ) ) {
			return null;
		}
		if ( false === Event::upsert( [ 'post_id' ], $data ) ) {
			return null;
		}
		$event = Event::find( $post_id, 'post_id' );
		return $event instanceof Event ? $event : null;
	}

	/**
	 * Regenerates the Occurrences of an Event from its current RSET.
	 *
	 * @since TBD
	 *
	 * @param int $post_id The Event post ID.
	 *
	 * @return void
	 */
	private function regenerate( int $post_id ): void {
		// Clear the per-request match cache: the set is being rebuilt.
		wp_cache_delete( $post_id, 'tec_occurrence_matches' );

		$event = Event::find( $post_id, 'post_id' );

		if ( $event instanceof Event ) {
			$event->occurrences()->save_occurrences();
		}
	}
}
