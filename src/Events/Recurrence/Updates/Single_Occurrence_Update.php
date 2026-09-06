<?php
/**
 * Moves a single Occurrence of a multi-date Event when its edit screen is saved.
 *
 * An Occurrence edit screen is a provisional post: every meta write against it is
 * retargeted to the real Event by the provisional meta filters, so a plain save would
 * move the EVENT's own date, not the Occurrence's. This service buffers the date meta
 * posted against a provisional ID before that retargeting runs, and applies them as an
 * update of that one Occurrence once the save completes:
 *
 * - on a dates-only Event, the matching authored date is replaced (the Event's own date
 *   when the Occurrence is the first one) and the Occurrences regenerate;
 * - on a rule-based Event whose rules are frozen (Events Calendar Pro data, Pro absent),
 *   the move is refused: the Occurrences follow the Pro rules, which only Pro or the
 *   conversion to individual dates may change. The refusal is recorded with the
 *   Freeze_Guard, which leaves the user the same warning a refused Event date write does.
 *
 * The Occurrence row is moved BEFORE any regeneration so the start-date based matching
 * recycles the same Occurrence ID: the editor stays on the same provisional post.
 *
 * @since TBD
 *
 * @package TEC\Events\Recurrence\Updates
 */

declare( strict_types=1 );

namespace TEC\Events\Recurrence\Updates;

use DateTimeImmutable;
use DateTimeZone;
use Exception;
use TEC\Events\Custom_Tables\V1\Models\Event;
use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use TEC\Events\Custom_Tables\V1\Models\Provisional_Post;
use TEC\Events\Recurrence\Authoring_Guard;
use TEC\Events\Recurrence\Dates_Service;
use TEC\Events\Recurrence\Occurrences_List;
use Tribe__Events__Main as TEC;
use WP_Post;

/**
 * Class Single_Occurrence_Update.
 *
 * @since TBD
 *
 * @package TEC\Events\Recurrence\Updates
 */
class Single_Occurrence_Update {
	/**
	 * The date meta keys an Occurrence edit screen posts.
	 *
	 * @since TBD
	 *
	 * @var array<int,string>
	 */
	public const DATE_META_KEYS = [
		'_EventStartDate',
		'_EventEndDate',
		'_EventStartDateUTC',
		'_EventEndDateUTC',
		'_EventDuration',
		'_EventAllDay',
	];

	/**
	 * The transient prefix of the per-user admin notice.
	 *
	 * @since TBD
	 *
	 * @see Admin_Notice::TRANSIENT The notice is rendered by the shared notice.
	 */
	public const NOTICE_TRANSIENT = Admin_Notice::TRANSIENT;

	/**
	 * The date meta buffered per provisional post ID during the request.
	 *
	 * @since TBD
	 *
	 * @var array<int,array<string,mixed>>
	 */
	private array $pending = [];

	/**
	 * Registers the meta interception and the save hooks.
	 *
	 * The interception runs before the provisional meta filters (priority 0) that would
	 * retarget the writes to the real Event.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function register(): void {
		if ( ! has_filter( 'update_post_metadata', [ $this, 'buffer_update' ] ) ) {
			add_filter( 'update_post_metadata', [ $this, 'buffer_update' ], -10, 4 );
		}

		if ( ! has_filter( 'add_post_metadata', [ $this, 'buffer_add' ] ) ) {
			add_filter( 'add_post_metadata', [ $this, 'buffer_add' ], -10, 4 );
		}

		if ( ! has_filter( 'delete_post_metadata', [ $this, 'buffer_delete' ] ) ) {
			add_filter( 'delete_post_metadata', [ $this, 'buffer_delete' ], -10, 3 );
		}

		if ( ! has_action( 'tribe_events_update_meta', [ $this, 'on_classic_save' ] ) ) {
			// After the Event API wrote (and this service buffered) the date meta.
			add_action( 'tribe_events_update_meta', [ $this, 'on_classic_save' ], 20 );
		}

		if ( ! has_action( 'rest_after_insert_' . TEC::POSTTYPE, [ $this, 'on_rest_save' ] ) ) {
			// After the attribute-bound meta and the UTC dates were written, before the Custom Tables commit at 100.
			add_action( 'rest_after_insert_' . TEC::POSTTYPE, [ $this, 'on_rest_save' ], 50 );
		}
	}

	/**
	 * Removes the hooks added by the service.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function unregister(): void {
		remove_filter( 'update_post_metadata', [ $this, 'buffer_update' ], -10 );
		remove_filter( 'add_post_metadata', [ $this, 'buffer_add' ], -10 );
		remove_filter( 'delete_post_metadata', [ $this, 'buffer_delete' ], -10 );
		remove_action( 'tribe_events_update_meta', [ $this, 'on_classic_save' ], 20 );
		remove_action( 'rest_after_insert_' . TEC::POSTTYPE, [ $this, 'on_rest_save' ], 50 );
		$this->pending = [];
	}

	/**
	 * Buffers a date meta update posted against a provisional post ID.
	 *
	 * @since TBD
	 *
	 * @param null|bool|mixed $check      Whether to short-circuit the update or not.
	 * @param int|mixed       $object_id  The object ID the meta is written for.
	 * @param string|mixed    $meta_key   The meta key.
	 * @param mixed           $meta_value The meta value.
	 *
	 * @return null|bool|mixed `true` when the write was buffered, the input otherwise.
	 */
	public function buffer_update( $check, $object_id, $meta_key, $meta_value ) {
		if ( null !== $check || ! $this->is_buffered_write( $object_id, $meta_key ) ) {
			return $check;
		}

		$this->pending[ (int) $object_id ][ (string) $meta_key ] = $meta_value;

		return true;
	}

	/**
	 * Buffers a date meta addition posted against a provisional post ID.
	 *
	 * @since TBD
	 *
	 * @param null|bool|mixed $check      Whether to short-circuit the addition or not.
	 * @param int|mixed       $object_id  The object ID the meta is written for.
	 * @param string|mixed    $meta_key   The meta key.
	 * @param mixed           $meta_value The meta value.
	 *
	 * @return null|bool|mixed `true` when the write was buffered, the input otherwise.
	 */
	public function buffer_add( $check, $object_id, $meta_key, $meta_value ) {
		return $this->buffer_update( $check, $object_id, $meta_key, $meta_value );
	}

	/**
	 * Buffers a date meta deletion posted against a provisional post ID.
	 *
	 * @since TBD
	 *
	 * @param null|bool|mixed $check     Whether to short-circuit the deletion or not.
	 * @param int|mixed       $object_id The object ID the meta is deleted for.
	 * @param string|mixed    $meta_key  The meta key.
	 *
	 * @return null|bool|mixed `true` when the deletion was buffered, the input otherwise.
	 */
	public function buffer_delete( $check, $object_id, $meta_key ) {
		if ( null !== $check || ! $this->is_buffered_write( $object_id, $meta_key ) ) {
			return $check;
		}

		$this->pending[ (int) $object_id ][ (string) $meta_key ] = null;

		return true;
	}

	/**
	 * Returns whether date meta was buffered for a provisional post ID during this request.
	 *
	 * @since TBD
	 *
	 * @param int $provisional_id The provisional post ID.
	 *
	 * @return bool Whether date meta is pending or not.
	 */
	public function has_pending( int $provisional_id ): bool {
		return ! empty( $this->pending[ $provisional_id ] );
	}

	/**
	 * Applies the buffered dates of a classic editor save.
	 *
	 * @since TBD
	 *
	 * @param int|mixed $event_id The saved post ID, a provisional one on Occurrence screens.
	 *
	 * @return void
	 */
	public function on_classic_save( $event_id ): void {
		$event_id = (int) $event_id;

		if ( $this->has_pending( $event_id ) ) {
			$this->apply_pending( $event_id );
		}
	}

	/**
	 * Applies the buffered dates of a Block Editor (REST) save.
	 *
	 * @since TBD
	 *
	 * @param WP_Post|mixed $post The saved post, a provisional one on Occurrence screens.
	 *
	 * @return void
	 */
	public function on_rest_save( $post ): void {
		if ( $post instanceof WP_Post && $this->has_pending( (int) $post->ID ) ) {
			$this->apply_pending( (int) $post->ID );
		}
	}

	/**
	 * Applies the date meta buffered for a provisional post ID as a move of that Occurrence.
	 *
	 * @since TBD
	 *
	 * @param int $provisional_id The provisional post ID.
	 *
	 * @return bool Whether the Occurrence was moved or not.
	 */
	public function apply_pending( int $provisional_id ): bool {
		$pending = $this->pending[ $provisional_id ] ?? [];
		unset( $this->pending[ $provisional_id ] );

		if ( ! isset( $pending['_EventStartDate'] ) && ! isset( $pending['_EventEndDate'] ) ) {
			// Nothing date-related was posted: nothing to move.
			return false;
		}

		$occurrence = $this->get_occurrence( $provisional_id );

		if ( ! $occurrence instanceof Occurrence ) {
			return false;
		}

		try {
			$timezone  = tribe( Occurrences_List::class )->get_event_timezone( (int) $occurrence->post_id );
			$old_start = new DateTimeImmutable( (string) $occurrence->start_date, $timezone );
			$old_end   = new DateTimeImmutable( (string) $occurrence->end_date, $timezone );
			$new_start = isset( $pending['_EventStartDate'] ) ? new DateTimeImmutable( (string) $pending['_EventStartDate'], $timezone ) : $old_start;

			if ( isset( $pending['_EventEndDate'] ) ) {
				$new_end = new DateTimeImmutable( (string) $pending['_EventEndDate'], $timezone );
			} else {
				// Only the start moved: keep the duration.
				$shift   = (int) $new_start->format( 'U' ) - (int) $old_start->format( 'U' );
				$new_end = $old_end->modify( sprintf( '%+d seconds', $shift ) );
			}
		} catch ( Exception $e ) {
			$this->set_notice( 'error', __( 'The occurrence dates could not be read: nothing was changed.', 'the-events-calendar' ) );

			return false;
		}

		return $this->apply( $provisional_id, $new_start, $new_end );
	}

	/**
	 * Moves one Occurrence of an Event to new dates, updating the Event recurrence data.
	 *
	 * @since TBD
	 *
	 * @param int               $provisional_id The provisional post ID of the Occurrence.
	 * @param DateTimeImmutable $new_start      The new start, in the Event timezone.
	 * @param DateTimeImmutable $new_end        The new end.
	 *
	 * @return bool Whether the Occurrence was moved or not.
	 */
	public function apply( int $provisional_id, DateTimeImmutable $new_start, DateTimeImmutable $new_end ): bool {
		if ( tribe( Authoring_Guard::class )->has_external_updates() ) {
			// The active recurrence editor owns the requested update scope.
			return false;
		}

		$occurrence = $this->get_occurrence( $provisional_id );

		if ( ! $occurrence instanceof Occurrence ) {
			return false;
		}

		$post_id = (int) $occurrence->post_id;
		$event   = Event::find( $post_id, 'post_id' );

		if ( ! $event instanceof Event ) {
			return false;
		}

		$timezone  = tribe( Occurrences_List::class )->get_event_timezone( $post_id );
		$new_start = $new_start->setTimezone( $timezone );
		$new_end   = $new_end->setTimezone( $timezone );

		if ( (int) $new_end->format( 'U' ) <= (int) $new_start->format( 'U' ) ) {
			$this->set_notice( 'error', __( 'The occurrence end must be after its start: nothing was changed.', 'the-events-calendar' ) );

			return false;
		}

		$old_start = new DateTimeImmutable( (string) $occurrence->start_date, $timezone );
		$old_end   = new DateTimeImmutable( (string) $occurrence->end_date, $timezone );
		$format    = 'Y-m-d H:i:s';

		if ( $old_start->format( $format ) === $new_start->format( $format ) && $old_end->format( $format ) === $new_end->format( $format ) ) {
			// Nothing moved.
			return true;
		}

		if ( tribe( Authoring_Guard::class )->is_rule_locked( $post_id ) ) {
			/*
			 * The Occurrences of a rule-based Event follow its Events Calendar Pro rules: a
			 * move has no valid target while the rules are frozen. The `tec_events_recurrence_freeze_meta_write`
			 * filter is not consulted: it governs raw meta writes, and letting the move through
			 * would author a dates-only set over the rules.
			 */
			tribe( Freeze_Guard::class )->record_refusal( $post_id, '_EventStartDate' );

			return false;
		}

		$collision = Occurrence::where( 'post_id', '=', $post_id )
							->where( 'start_date', '=', $new_start->format( $format ) )
							->where( 'occurrence_id', '!=', (int) $occurrence->occurrence_id )
							->count();

		if ( $collision > 0 ) {
			$this->set_notice( 'error', __( 'The event already has an occurrence starting on that date and time: nothing was changed.', 'the-events-calendar' ) );

			return false;
		}

		$is_first = (string) $occurrence->start_date === (string) $event->start_date;

		if ( ! $this->move_dates_only_occurrence( $occurrence, $event, $is_first, $new_start, $new_end, $timezone ) ) {
			return false;
		}

		$this->flush_caches( (int) $occurrence->occurrence_id, $post_id );

		/**
		 * Fires after a single Occurrence of an Event was moved to new dates.
		 *
		 * @since TBD
		 *
		 * @param int                                                $occurrence_id The Occurrence ID.
		 * @param int                                                $post_id       The Event post ID.
		 * @param array{start: DateTimeImmutable, end: DateTimeImmutable} $before   The dates before the move.
		 * @param array{start: DateTimeImmutable, end: DateTimeImmutable} $after    The dates after the move.
		 */
		do_action(
			'tec_events_recurrence_occurrence_updated',
			(int) $occurrence->occurrence_id,
			$post_id,
			[
				'start' => $old_start,
				'end'   => $old_end,
			],
			[
				'start' => $new_start,
				'end'   => $new_end,
			]
		);

		$this->set_notice(
			'success',
			sprintf(
				/* translators: %s: the new date and time of the occurrence. */
				__( 'This occurrence now takes place on %s. The other dates of the event were not changed.', 'the-events-calendar' ),
				wp_date( tribe_get_datetime_format( true ), (int) $new_start->format( 'U' ), $timezone )
			)
		);

		return true;
	}

	/**
	 * Moves an Occurrence of a dates-only Event, regenerating the set.
	 *
	 * @since TBD
	 *
	 * @param Occurrence        $occurrence The Occurrence to move.
	 * @param Event             $event      The Event.
	 * @param bool              $is_first   Whether the Occurrence is the Event's own (first) date.
	 * @param DateTimeImmutable $new_start  The new start.
	 * @param DateTimeImmutable $new_end    The new end.
	 * @param DateTimeZone      $timezone   The Event timezone.
	 *
	 * @return bool Whether the Occurrence was moved or not.
	 */
	private function move_dates_only_occurrence( Occurrence $occurrence, Event $event, bool $is_first, DateTimeImmutable $new_start, DateTimeImmutable $new_end, DateTimeZone $timezone ): bool {
		$post_id = (int) $event->post_id;
		$guard   = tribe( Authoring_Guard::class );
		$periods = $guard->get_authored_periods( $post_id );
		$format  = 'Y-m-d H:i';

		if ( $is_first ) {
			// The Event's own date moves; the authored dates are absolute and stay.
			$this->move_row( $occurrence, $new_start, $new_end );
			$this->write_event_dates( $post_id, $new_start, $new_end );
			Event::upsert( [ 'post_id' ], Event::data_from_post( $post_id ) );
		} else {
			$old_start = new DateTimeImmutable( (string) $occurrence->start_date, $timezone );
			$old_end   = new DateTimeImmutable( (string) $occurrence->end_date, $timezone );
			$replaced  = false;

			foreach ( $periods as $index => $period ) {
				if ( $period['start']->format( $format ) === $old_start->format( $format ) && $period['end']->format( $format ) === $old_end->format( $format ) ) {
					$periods[ $index ] = [
						'start' => $new_start,
						'end'   => $new_end,
					];
					$replaced          = true;
					break;
				}
			}

			if ( ! $replaced ) {
				$this->set_notice( 'error', __( 'This occurrence could not be matched to one of the event dates: nothing was changed.', 'the-events-calendar' ) );

				return false;
			}

			$this->move_row( $occurrence, $new_start, $new_end );
		}

		if ( ! count( $periods ) ) {
			// A single-date Event whose only date moved: nothing else to author.
			return true;
		}

		return tribe( Dates_Service::class )->set_dates( $post_id, $periods );
	}

	/**
	 * Moves an Occurrence row in place, keeping its ID.
	 *
	 * @since TBD
	 *
	 * @param Occurrence        $occurrence The Occurrence to move.
	 * @param DateTimeImmutable $start      The new start, in the Event timezone.
	 * @param DateTimeImmutable $end        The new end, in the Event timezone.
	 *
	 * @return void
	 */
	private function move_row( Occurrence $occurrence, DateTimeImmutable $start, DateTimeImmutable $end ): void {
		$utc    = new DateTimeZone( 'UTC' );
		$format = 'Y-m-d H:i:s';

		$occurrence->start_date     = $start->format( $format );
		$occurrence->end_date       = $end->format( $format );
		$occurrence->start_date_utc = $start->setTimezone( $utc )->format( $format );
		$occurrence->end_date_utc   = $end->setTimezone( $utc )->format( $format );
		$occurrence->duration       = (int) $end->format( 'U' ) - (int) $start->format( 'U' );

		$occurrence->update(
			[
				'start_date'     => $occurrence->start_date,
				'end_date'       => $occurrence->end_date,
				'start_date_utc' => $occurrence->start_date_utc,
				'end_date_utc'   => $occurrence->end_date_utc,
				'duration'       => $occurrence->duration,
				'hash'           => $occurrence->generate_hash(),
			]
		);
	}

	/**
	 * Writes the Event's own date meta, the way the Event API stores them.
	 *
	 * @since TBD
	 *
	 * @param int               $post_id The Event post ID.
	 * @param DateTimeImmutable $start   The new start, in the Event timezone.
	 * @param DateTimeImmutable $end     The new end, in the Event timezone.
	 *
	 * @return void
	 */
	private function write_event_dates( int $post_id, DateTimeImmutable $start, DateTimeImmutable $end ): void {
		$utc    = new DateTimeZone( 'UTC' );
		$format = 'Y-m-d H:i:s';

		update_post_meta( $post_id, '_EventStartDate', $start->format( $format ) );
		update_post_meta( $post_id, '_EventEndDate', $end->format( $format ) );
		update_post_meta( $post_id, '_EventStartDateUTC', $start->setTimezone( $utc )->format( $format ) );
		update_post_meta( $post_id, '_EventEndDateUTC', $end->setTimezone( $utc )->format( $format ) );
		update_post_meta( $post_id, '_EventDuration', (int) $end->format( 'U' ) - (int) $start->format( 'U' ) );
	}

	/**
	 * Flushes the caches carrying the Occurrence's previous dates.
	 *
	 * @since TBD
	 *
	 * @param int $occurrence_id The Occurrence ID.
	 * @param int $post_id       The Event post ID.
	 *
	 * @return void
	 */
	private function flush_caches( int $occurrence_id, int $post_id ): void {
		$provisional = tribe( Provisional_Post::class );
		$provisional->clear_occurrence_cache( $occurrence_id );
		$provisional->get_post_cache()->flush_occurrences( $post_id );
		wp_cache_delete( $post_id, 'tec_occurrence_matches' );
		clean_post_cache( $post_id );
	}

	/**
	 * Resolves the Occurrence behind a provisional post ID.
	 *
	 * @since TBD
	 *
	 * @param int $provisional_id The provisional post ID.
	 *
	 * @return Occurrence|null The Occurrence, or `null` if the ID is not a provisional one or the row is gone.
	 */
	private function get_occurrence( int $provisional_id ): ?Occurrence {
		$provisional = tribe( Provisional_Post::class );

		if ( ! $provisional->is_provisional_post_id( $provisional_id ) ) {
			return null;
		}

		$occurrence = Occurrence::find( $provisional->normalize_provisional_post_id( $provisional_id ), 'occurrence_id' );

		return $occurrence instanceof Occurrence ? $occurrence : null;
	}

	/**
	 * Returns whether a meta write should be buffered: a date meta key on a provisional post ID.
	 *
	 * @since TBD
	 *
	 * @param int|mixed    $object_id The object ID the meta is written for.
	 * @param string|mixed $meta_key  The meta key.
	 *
	 * @return bool Whether to buffer the write or not.
	 */
	private function is_buffered_write( $object_id, $meta_key ): bool {
		if ( tribe( Authoring_Guard::class )->has_external_updates() ) {
			return false;
		}

		if ( ! is_string( $meta_key ) || ! in_array( $meta_key, self::DATE_META_KEYS, true ) ) {
			return false;
		}

		if ( ! is_numeric( $object_id ) || (int) $object_id <= 0 ) {
			return false;
		}

		return tribe( Provisional_Post::class )->is_provisional_post_id( (int) $object_id );
	}

	/**
	 * Stores the notice the next admin screen of the current user renders.
	 *
	 * @since TBD
	 *
	 * @param string $type    The notice type, `success` or `error`.
	 * @param string $message The notice message.
	 *
	 * @return void
	 */
	private function set_notice( string $type, string $message ): void {
		tribe( Admin_Notice::class )->set( $type, esc_html( $message ) );
	}
}
