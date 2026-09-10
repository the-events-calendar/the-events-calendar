<?php
/**
 * Keeps the Occurrence rows of multi-Occurrence Events consistent across regenerations.
 *
 * Mirrors the matching and pruning behavior of the Events Calendar Pro update pipeline:
 * regenerated Occurrences are matched to existing rows by their dates, so Occurrence IDs
 * (and the provisional post IDs derived from them) stay stable, and rows left behind by
 * a previous sequence are pruned after a save.
 *
 * @since TBD
 *
 * @package TEC\Events\Recurrence
 */

declare( strict_types=1 );

namespace TEC\Events\Recurrence;

use TEC\Events\Custom_Tables\V1\Models\Event;
use TEC\Events\Custom_Tables\V1\Models\Extensions\Occurrence as Occurrence_Extension;
use TEC\Events\Custom_Tables\V1\Models\Occurrence;

/**
 * Class Occurrences_Maintenance.
 *
 * @since TBD
 *
 * @package TEC\Events\Recurrence
 */
class Occurrences_Maintenance {
	/**
	 * Filters the Occurrence match to return one matched by dates and post ID.
	 *
	 * @since TBD
	 *
	 * @param Occurrence|null $occurrence Either a reference to an existing, matching, Occurrence
	 *                                    or `null`.
	 * @param Occurrence      $result     A reference to the Occurrence model instance that will be
	 *                                    inserted if a matching Occurrence cannot be found.
	 * @param int             $post_id    The post ID of the Event the match is being searched for.
	 *
	 * @return Occurrence|null Either the reference to an existing Occurrence matching the one
	 *                         that should be inserted, or `null` to indicate none was found.
	 */
	public function get_occurrence_match( ?Occurrence $occurrence, Occurrence $result, int $post_id ): ?Occurrence {
		$event = Event::find( $post_id, 'post_id' );

		if ( ! $event instanceof Event || '' === trim( (string) $event->rset ) ) {
			// Not a multi-Occurrence Event: let the base logic apply.
			return $occurrence;
		}

		// Did we already build the set?
		$post_id_occurrences = wp_cache_get( $post_id, 'tec_occurrence_matches' );

		if ( false === $post_id_occurrences ) {
			$occurrences = Occurrence::where( 'post_id', '=', $post_id )
									->output( ARRAY_A )
									->all();

			// Extract the values from the Occurrences generator: the batched query logic will be applied.
			$post_id_occurrences = iterator_to_array( $occurrences );

			// Store the set to re-use it in the next run, will expire at the end of the current Request.
			wp_cache_set( $post_id, $post_id_occurrences, 'tec_occurrence_matches' );
		}

		// Look for a match in the set.
		$matches = wp_list_filter(
			$post_id_occurrences,
			[
				'start_date'     => $result->get_start_date_attribute(),
				'start_date_utc' => $result->get_start_date_utc_attribute(),
			]
		);

		if ( empty( $matches ) ) {
			if ( $occurrence instanceof Occurrence ) {
				/*
				 * If no Occurrence matches the new one, then the base logic should not try
				 * to reuse the first Occurrence.
				 */
				return null;
			}

			return $occurrence;
		}

		// Build the Occurrence model instance from the pre-fetched set row.
		return new Occurrence( reset( $matches ) );
	}

	/**
	 * Prunes the Occurrences of an Event left behind by a previous sequence.
	 *
	 * @since TBD
	 *
	 * @param int $post_id The ID of the Event post the Occurrences are being saved for.
	 *
	 * @return int|false The number of Occurrences deleted, or `false` if there is no sequence.
	 */
	public function prune_occurrences( int $post_id ) {
		/*
		 * The matching set was built from the pre-save rows: invalidate it so a further
		 * save in the same request (e.g. a second authoring commit) matches against the
		 * rows that exist now, keeping Occurrence IDs stable.
		 */
		wp_cache_delete( $post_id, 'tec_occurrence_matches' );

		$event = Event::find( $post_id, 'post_id' );

		if ( $event instanceof Event && '' === trim( (string) $event->rset ) ) {
			/*
			 * The Event collapsed back to a single Occurrence (e.g. its dates were removed):
			 * the base save logic reused the first row, so every other row is stale. The
			 * sequence-based pruning below cannot catch these, since stale rows can carry
			 * the highest sequence while the reused row was reset to a `NULL` one.
			 */
			$kept = Occurrence::where( 'post_id', '=', $post_id )->first();

			if ( ! $kept instanceof Occurrence ) {
				return false;
			}

			return Occurrence::where( 'post_id', $post_id )
							->where_raw( '`occurrence_id` != %d', (int) $kept->occurrence_id )
							->delete();
		}

		$current_sequence = Occurrence_Extension::get_sequence( $post_id );

		/*
		 * A `NULL` sequence value will result in an empty sequence, preserving single
		 * Occurrence Events that will set the `sequence` column to `NULL`.
		 */
		if ( empty( $current_sequence ) ) {
			return false;
		}

		return Occurrence::where( 'post_id', $post_id )
						->where_raw( '`sequence` IS NULL OR `sequence` < %d', $current_sequence )
						->delete();
	}
}
