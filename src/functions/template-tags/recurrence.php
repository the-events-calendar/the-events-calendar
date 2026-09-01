<?php
/**
 * Recurrence-related template tags.
 *
 * The functions were historically defined by Events Calendar Pro; every Pro version
 * wraps its own definitions in `function_exists` checks, so the definitions here take
 * precedence when both plugins are active. The implementations are data driven and
 * behave identically for Pro-authored recurring Events, including legacy (pre-6.0)
 * child-post ones and sites where the Custom Tables are not in use.
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

		if ( ! $recurring && tribe()->getVar( 'ct1_fully_activated', false ) ) {
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
	 *            read from the Occurrences custom table when the Custom Tables are in
	 *            use, from the legacy child-post structure otherwise.
	 *
	 * @param int|WP_Post|null $post_id The Event post ID, or object, or `null` to use the current post.
	 *
	 * @return array<int,string> The Occurrence start dates, in the `Y-m-d H:i:s` format.
	 */
	function tribe_get_recurrence_start_dates( $post_id = null ) {
		$post_id = Tribe__Events__Main::postIdHelper( $post_id );

		if ( empty( $post_id ) ) {
			return [];
		}

		// Pre-6.0 recurring Events are parent/child posts: resolve the parent first.
		$ancestors = get_post_ancestors( $post_id );
		$parent_id = empty( $ancestors ) ? (int) $post_id : (int) end( $ancestors );

		global $wpdb;

		$start_dates = (array) $wpdb->get_col(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table names cannot be placeholders.
				"SELECT m.meta_value FROM {$wpdb->postmeta} m
				JOIN {$wpdb->posts} p ON p.ID = m.post_id
				WHERE m.meta_key = '_EventStartDate'
				AND ( p.ID = %d OR p.post_parent = %d )
				AND p.post_type = %s
				ORDER BY m.meta_value ASC",
				$parent_id,
				$parent_id,
				Tribe__Events__Main::POSTTYPE
			)
		);

		if ( count( $start_dates ) > 1 ) {
			// A legacy child-post recurring Event: the meta values are the dates.
			return $start_dates;
		}

		if ( tribe()->getVar( 'ct1_fully_activated', false ) ) {
			$normalized_id    = Occurrence::normalize_id( $parent_id );
			$occurrence_dates = [];

			foreach ( Occurrence::where( 'post_id', '=', $normalized_id )->order_by( 'start_date', 'ASC' )->all() as $occurrence ) {
				$occurrence_dates[] = $occurrence->start_date;
			}

			if ( count( $occurrence_dates ) ) {
				return $occurrence_dates;
			}
		}

		return $start_dates;
	}
}

if ( ! function_exists( 'tribe_all_occurrences_link' ) ) {
	/**
	 * Returns, and optionally echoes, the link to the archive of all the Occurrences of
	 * an Event, e.g. `/event/some-event/all/`.
	 *
	 * @since TBD Moved to The Events Calendar from Events Calendar Pro; provisional
	 *            Occurrence post IDs are normalized to the Event post ID.
	 *
	 * @param int|WP_Post|null $post_id The Event post ID, or object, or `null` to use the current post.
	 * @param bool             $echo    Whether to echo the link too or not.
	 *
	 * @return string The link to the archive of all the Occurrences of the Event.
	 */
	function tribe_all_occurrences_link( $post_id = null, $echo = true ) {
		$cache_key_links = __FUNCTION__ . ':links';
		$cache_links     = tribe_get_var( $cache_key_links, [] );

		$post_id = Tribe__Events__Main::postIdHelper( $post_id );

		if ( empty( $post_id ) ) {
			return '';
		}

		// Pre-6.0 recurring Events are parent/child posts; Occurrences are provisional IDs.
		$parent_id = wp_get_post_parent_id( $post_id );
		$cache_id  = $parent_id ? (int) $parent_id : Occurrence::normalize_id( (int) $post_id );

		if ( ! isset( $cache_links[ $cache_id ] ) ) {
			/**
			 * Filters the link to the archive of all the Occurrences of an Event.
			 *
			 * @since TBD Moved to The Events Calendar from Events Calendar Pro.
			 *
			 * @param string $link The link to the archive of all the Occurrences of the Event.
			 */
			$cache_links[ $cache_id ] = apply_filters(
				'tribe_all_occurrences_link',
				Tribe__Events__Main::instance()->getLink( 'all', $cache_id )
			);
			tribe_set_var( $cache_key_links, $cache_links );
		}

		if ( $echo ) {
			echo esc_url( $cache_links[ $cache_id ] );
		}

		return $cache_links[ $cache_id ];
	}
}
