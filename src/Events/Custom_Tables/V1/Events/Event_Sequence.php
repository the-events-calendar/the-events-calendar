<?php

namespace TEC\Events\Custom_Tables\V1\Events;

use TEC\Events\Custom_Tables\V1\Models\Occurrence;
use TEC\Events\Custom_Tables\V1\Models\Provisional_Post;
use Tribe__Date_Utils;

/**
 * Class Event_Sequence.
 *
 * @since   7.4.5
 * @since TBD Migrated to The Events Calendar from Events Calendar Pro.
 *
 * @package TEC\Events\Custom_Tables\V1\Events;
 */
class Event_Sequence {
	/**
	 * @since 6.1.1
	 *
	 * @var numeric The post ID for this event sequence.
	 */
	protected $post_id;
	/**
	 * @since 6.1.1
	 *
	 * @var string The date for this sequence series.
	 */
	protected $start_date;
	/**
	 * @since 6.1.1
	 *
	 * @var array|mixed The post meta hash array to save / loaded from the database.
	 */
	protected array $post_meta = [];

	/**
	 * An instance of an event sequence hash table. This instance will have access
	 * to several occurrence dates with the sequence number.
	 *
	 * @since 6.1.1
	 *
	 * @param numeric $post_id    This event's post ID.
	 * @param string  $start_date This event sequence series start date.
	 */
	public function __construct( $post_id, $start_date ) {
		$this->post_id    = tribe( Provisional_Post::class )->normalize_provisional_post_id( $post_id );
		$this->start_date = $start_date;
		$meta             = get_post_meta( $this->post_id, static::meta_key( $start_date ), true );
		if ( ! empty( $meta ) && is_array( $meta ) ) {
			$this->post_meta = $meta;
		}
	}

	/**
	 * Will add this occurrence if it doesn't already exist.
	 *
	 * @since 6.1.1
	 *
	 * @param Occurrence $occurrence The occurrence to add to this meta value.
	 *
	 * @return bool Whether this is occurrence was added or not.
	 */
	public function add_occurrence( Occurrence $occurrence ): bool {
		foreach ( $this->post_meta as $dates ) {
			// Do we have this occurrence already? If so, we are done.
			if ( $dates['start_date'] === $occurrence->start_date && $dates['end_date'] === $occurrence->end_date ) {

				return false;
			}
		}
		// Next sequence number.
		$sequence = count( $this->post_meta ) + 1;
		// New occurrence, now add.
		$this->post_meta[ $sequence ] = [
			'start_date' => $occurrence->start_date,
			'end_date'   => $occurrence->end_date,
		];

		return true;
	}

	/**
	 * Save to the database.
	 *
	 * @since 6.1.1
	 *
	 * @return $this
	 */
	public function save(): self {
		update_post_meta( $this->post_id, static::meta_key( $this->start_date ), $this->post_meta );

		return $this;
	}

	/**
	 * Generates the key for a particular sequence.
	 *
	 * @since 6.1.1
	 *
	 * @param string $date The date this sequence is on.
	 *
	 * @return string The meta key for this date.
	 */
	public static function meta_key( $date ): string {
		return '_EventSequence_' . ( Tribe__Date_Utils::build_date_object( $date ) )->format( 'Y-m-d' );
	}

	/**
	 * Finds an occurrence by the sequence number.
	 *
	 * @since 6.1.1
	 *
	 * @param numeric $post_id         The occurrences post ID.
	 * @param numeric $sequence_number This sequence instance number, starting at 1.
	 * @param string  $start_date      The date these sequences reside on.
	 *
	 * @return Occurrence|null The occurrence model if one is found.
	 */
	public static function find_occurrence_by_sequence( $post_id, $sequence_number, $start_date ): ?Occurrence {
		$sequence_hash_table = get_post_meta( $post_id, static::meta_key( $start_date ), true );
		if ( ! isset( $sequence_hash_table[ $sequence_number ] ) ) {
			return null;
		}

		return Occurrence::where( 'post_id', $post_id )
						->where( 'start_date', $sequence_hash_table[ $sequence_number ]['start_date'] )
						->where( 'end_date', $sequence_hash_table[ $sequence_number ]['end_date'] )
						->first();
	}

	/**
	 * Scans the hash array for the sequence number of a particular occurrence by start / end dates.
	 *
	 * @since 6.1.1
	 *
	 * @param array  $sequence_hash The sequence hash.
	 * @param string $start_date    The occurrence start date.
	 * @param string $end_date      The occurrence end date.
	 *
	 * @return int|null The sequence number if one is found.
	 */
	public static function find_sequence_for_dates( array $sequence_hash, string $start_date, string $end_date ): ?int {
		foreach ( $sequence_hash as $sequence => $dates ) {
			if ( $dates['start_date'] === $start_date && $dates['end_date'] === $end_date ) {
				return (int) $sequence;
			}
		}

		return null;
	}

	/**
	 * Finds the sequence number if one exists for a particular occurrence.
	 *
	 * @since 6.1.1
	 *
	 * @param Occurrence $occurrence The occurrence to find the sequence for.
	 *
	 * @return int|null The sequence ID if one is found.
	 */
	public static function find_sequence_for_occurrence( Occurrence $occurrence ): ?int {
		$start_date          = $occurrence->start_date;
		$key                 = static::meta_key( $start_date );
		$post_id             = $occurrence->post_id;
		$sequence_hash_table = get_post_meta( $post_id, $key, true );
		// Anything found?
		if ( empty( $sequence_hash_table ) || ! is_array( $sequence_hash_table ) ) {
			return null;
		}

		return static::find_sequence_for_dates( $sequence_hash_table, (string) $occurrence->start_date, (string) $occurrence->end_date );
	}

	/**
	 * Checks if this occurrence has other occurrences that would be eligible for a sequence number.
	 *
	 * @since 6.1.1
	 * @since 7.4.4   Improve logic to cache results and avoid duplicated queries.
	 *
	 * @param Occurrence $occurrence The occurrence that may have other occurrences that fall on the same start date as.
	 *
	 * @return bool Whether there is another occurrence on the same date.
	 */
	public static function has_occurrence_on_same_day( Occurrence $occurrence ): bool {
		// We can trust the format of the Occurrence `start_date` field to be `Y-m-d H:i:s`.
		[ $day_y_m_d ] = explode( ' ', $occurrence->start_date, 2 );

		$post_id   = $occurrence->post_id;
		$cache_key = __METHOD__ . '_' . $day_y_m_d . '_' . $post_id;
		$cache     = tribe_cache();
		$count     = $cache[ $cache_key ];

		if ( $count === false ) {
			$count = Occurrence::where( 'post_id', $occurrence->post_id )
								->where( 'start_date', '>=', $day_y_m_d . ' 00:00:00' )
								->where( 'start_date', '<=', $day_y_m_d . ' 23:59:59' )
								->count();

			$cache[ $cache_key ] = $count;
		}

		return $count > 1;
	}

	/**
	 * Will find an occurrence on the same date as the specified post, with
	 * consideration for event sequence date ranges.
	 *
	 * @since 6.1.1
	 * @since 7.4.4   Improve logic to cache results and avoid duplicated queries.
	 *
	 * @param numeric $post_id The post ID.
	 * @param string  $date    The date to search on for an occurrence.
	 *
	 * @return Occurrence|null The occurrence if one exists.
	 */
	public static function get_occurrence_on_same_day( $post_id, $date ): ?Occurrence {
		$start      = Tribe__Date_Utils::build_date_object( $date );
		$day_y_m_d  = $start->format( 'Y-m-d' );
		$cache_key  = __METHOD__ . '_' . $post_id . '_' . $day_y_m_d;
		$cache      = tribe_cache();
		$occurrence = $cache[ $cache_key ];

		if ( $occurrence === false ) {
			$occurrence = Occurrence::where( 'post_id', $post_id )
									->where( 'start_date', '>=', $start->format( 'Y-m-d 00:00:00' ) )
									->where( 'start_date', '<=', $start->format( 'Y-m-d 23:59:59' ) )
									->first();

			$cache[ $cache_key ] = $occurrence;
		}

		return $occurrence;
	}

	/**
	 * This will generate Event Sequence meta keys for the current set of occurrences we have.
	 * Occurrences will sometimes be generated or dates adjusted after the initial sync, so this
	 * will add incrementally as needed.
	 *
	 * @since 6.1.1
	 * @since 7.4.4   Improve logic to cache results and avoid duplicated queries.
	 *
	 * @param Occurrence $occurrence The occurrence to sync other sequences for.
	 *
	 * @return int How many occurrences were added.
	 */
	public static function sync_sequences_for( Occurrence $occurrence ): int {
		$count_added = 0;

		// Find all occurrences that land on the same day in our same event.
		$post_id = $occurrence->post_id;

		// We can trust the format of the Occurrence `start_date` field to be `Y-m-d H:i:s`.
		[ $day_y_m_d ] = explode( ' ', $occurrence->start_date, 2 );

		$cache_key   = __METHOD__ . '_' . $post_id . '_' . $day_y_m_d;
		$cache       = tribe_cache();
		$occurrences = $cache[ $cache_key ];

		if ( $occurrences === false ) {
			$occurrences = Occurrence::where( 'start_date', '>=', $day_y_m_d . ' 00:00:00' )
									->where( 'start_date', '<=', $day_y_m_d . ' 23:59:59' )
									->where( 'post_id', '=', $post_id )
									->order_by( 'start_date', 'ASC' )
									->get();

			$cache[ $cache_key ] = $occurrences;
		}

		// Only one so let's bail.
		if ( count( $occurrences ) <= 1 ) {
			return $count_added;
		}
		// We have at least two, let's build/fetch a sequence hash array.
		$event_sequence_object = new static( $occurrence->post_id, $occurrence->start_date );
		// Find which occurrence in this day this is.
		foreach ( $occurrences as $sameday_occurrence ) {
			if ( $event_sequence_object->add_occurrence( $sameday_occurrence ) ) {
				++$count_added;
			}
		}

		// Any changes to save?
		if ( $count_added ) {
			$event_sequence_object->save();
		}

		return $count_added;
	}
}
