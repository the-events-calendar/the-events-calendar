<?php
/**
 * Recurrence-related template tags.
 *
 * The functions were historically defined by Events Calendar Pro; every Pro version
 * wraps its own definitions in `function_exists` checks, so the definitions here take
 * precedence when both plugins are active. The implementations are data driven and
 * behave identically for Pro-authored recurring Events.
 *
 * @since TBD
 */

use TEC\Events\Custom_Tables\V1\Models\Occurrence;

if ( ! function_exists( 'tribe_is_recurring_event' ) ) {
	/**
	 * Returns whether an Event is a recurring (multi-Occurrence) one or not.
	 *
	 * @since TBD Moved to The Events Calendar from Events Calendar Pro.
	 *
	 * @param int|WP_Post|null $post_id The Event post ID, or object, or `null` to use the current post.
	 *
	 * @return bool Whether the Event is a recurring one or not.
	 */
	function tribe_is_recurring_event( $post_id = null ) {
		$post_id = Tribe__Events__Main::postIdHelper( $post_id );

		if ( empty( $post_id ) ) {
			return false;
		}

		$post = get_post( $post_id );

		if ( empty( $post ) || Tribe__Events__Main::POSTTYPE !== $post->post_type ) {
			return false;
		}

		$recurring = false;

		if ( $post->post_parent > 0 ) {
			// Pre-6.0 recurring Event instances are child posts of the recurring Event.
			$recurring = true;
		} else {
			$recurrence_meta = get_post_meta( $post_id, '_EventRecurrence', true );

			if ( ! empty( $recurrence_meta['rules'] ) ) {
				foreach ( $recurrence_meta['rules'] as $recurrence ) {
					if ( isset( $recurrence['type'] ) && 'None' !== $recurrence['type'] ) {
						$recurring = true;
						break;
					}
				}
			} elseif ( ! empty( $recurrence_meta['type'] ) && 'None' !== $recurrence_meta['type'] ) {
				// Support the legacy, pre 3.12, recurrence meta format.
				$recurring = true;
			}
		}

		if ( ! $recurring && class_exists( Occurrence::class ) ) {
			// An Event with more than one Occurrence row is a recurring one.
			$normalized_id = Occurrence::normalize_id( (int) $post_id );
			$recurring     = Occurrence::where( 'post_id', '=', $normalized_id )->count() > 1;
		}

		/**
		 * Allows for filtering whether the specified event is recurring or not.
		 *
		 * @since TBD Moved to The Events Calendar from Events Calendar Pro.
		 *
		 * @param boolean $recurring Whether the specified event is recurring or not.
		 * @param int     $post_id   The post ID of the specified event.
		 */
		return apply_filters( 'tribe_is_recurring_event', $recurring, $post_id );
	}
}

if ( ! function_exists( 'tribe_get_recurrence_start_dates' ) ) {
	/**
	 * Returns the start dates of all the Occurrences of an Event, in ascending order.
	 *
	 * @since TBD Moved to The Events Calendar from Events Calendar Pro; the dates are
	 *            now read from the Occurrences custom table.
	 *
	 * @param int|WP_Post|null $post_id The Event post ID, or object, or `null` to use the current post.
	 *
	 * @return array<int,string> The Occurrence start dates, in the `Y-m-d H:i:s` format.
	 */
	function tribe_get_recurrence_start_dates( $post_id = null ) {
		$post_id = Tribe__Events__Main::postIdHelper( $post_id );

		if ( empty( $post_id ) || ! class_exists( Occurrence::class ) ) {
			return [];
		}

		$normalized_id = Occurrence::normalize_id( (int) $post_id );

		$start_dates = [];

		foreach ( Occurrence::where( 'post_id', '=', $normalized_id )->order_by( 'start_date', 'ASC' )->all() as $occurrence ) {
			$start_dates[] = $occurrence->start_date;
		}

		if ( ! count( $start_dates ) ) {
			// Fall back to the Event start date meta.
			$start_date = get_post_meta( $normalized_id, '_EventStartDate', true );

			if ( ! empty( $start_date ) ) {
				$start_dates[] = $start_date;
			}
		}

		return $start_dates;
	}
}
