<?php

use Tribe__Cache_Listener as Cache_Listener;
use Tribe__Events__Main as TEC;

/**
 * Controls getting a previous or next event from the context of a single event being viewed.
 */
class Tribe__Events__Adjacent_Events {

	/**
	 * @var int
	 */
	protected $current_event_id = 0;

	/**
	 * @var int
	 */
	public $previous_event_id;

	/**
	 * @var int
	 */
	public $next_event_id;

	/**
	 * @var string
	 */
	public $previous_event_link = '';

	/**
	 * @var string
	 */
	public $next_event_link = '';

	/**
	 * Set the "center" event ID to look on either side of in prev/next methods.
	 *
	 * @since 4.6.12
	 *
	 * @param int $event_id The event ID to look on either side of in prev/next methods.
	 */
	public function set_current_event_id( $event_id ) {
		$this->current_event_id = $event_id;
	}

	/**
	 * Get the "center" event ID to look on either side of in prev/next methods.
	 *
	 * @since 4.6.12
	 *
	 * @param int $event_id The event ID to look on either side of in prev/next methods.
	 */
	public function get_current_event_id() {
		return $this->current_event_id;
	}

	/**
	 * Get link to the previous event.
	 *
	 * @since 4.6.12
	 *
	 * @param boolean $anchor
	 * @return string
	 */
	public function get_prev_event_link( $anchor ) {

		if ( empty( $this->previous_event_link ) ) {
			$this->previous_event_link = $this->get_event_link( 'previous', $anchor );
		}

		return $this->previous_event_link;
	}

	/**
	 * Get link to the next event.
	 *
	 * @since 4.6.12
	 *
	 * @param boolean $anchor
	 * @return string
	 */
	public function get_next_event_link( $anchor ) {

		if ( empty( $this->next_event_link ) ) {
			$this->next_event_link = $this->get_event_link( 'next', $anchor );
		}

		return $this->next_event_link;
	}

	/**
	 * Modify the WHERE clause of query when fetching next/prev posts so events with identical times are not excluded
	 *
	 * This method ensures that when viewing single events that occur at a given time, other events
	 * that occur at the exact same time are are not excluded from the prev/next links
	 *
	 * @since 4.0.2
	 * @since 4.6.12 Moved to new Tribe__Events__Adjacent_Events class.
	 *
	 * @param string $where_sql WHERE SQL statement
	 * @param WP_Query $query WP_Query object
	 *
	 * @return string
	 */
	public function get_closest_event_where( $where_sql ) {
		// if we are in this method, we KNOW there is a section of the SQL that looks like this:
		//     ( table.meta_key = '_EventStartDate' AND CAST( table.meta_value AS DATETIME ) [<|>] '2015-01-01 00:00:00' )
		// What we want to do is to extract all the portions of the WHERE BEFORE that section, all the
		// portions AFTER that section, and then rebuild that section to be flexible enough to include
		// events that have the SAME datetime as the event we're comparing against.  Sadly, this requires
		// some regex-fu.
		//
		// The end-game is to change the known SQL line (from above) into the following:
		//
		//  (
		//    ( table.meta_key = '_EventStartDate' AND CAST( table.meta_value AS DATETIME ) [<|>] '2015-01-01 00:00:00' )
		//    OR (
		//      ( table.meta_key = '_EventStartDate' AND CAST( table.meta_value AS DATETIME ) = '2015-01-01 00:00:00' )
		//      AND
		//      table.post_id [<|>] POST_ID
		//    )
		//  )
		//

		// Here's the regex portion that matches the part that we know. From that line, we want to
		// have a few capture groups.
		//     1) We need the whole thing
		//     2) We need the meta table alias
		//     3) We need the < or > sign

		// Here's the regex for getting the meta table alias
		$meta_table_regex = '([^\.]+)\.meta_key\s*=\s*';

		// Here's the regex for the middle section of the know line
		$middle_regex = '[\'"]_EventStartDate[\'"]\s+AND\s+CAST[^\)]+AS DATETIME\s*\)\s*';

		// Here's the regex for the < and > sign
		$gt_lt_regex = '(\<|\>)';

		// Let's put that line together, making sure we are including the wrapping parens and the
		// characters that make up the rest of the line - spacing in front, non paren characters at
		// the end
		$known_sql_regex = "\(\s*{$meta_table_regex}{$middle_regex}{$gt_lt_regex}[^\)]+\)";

		// The known SQL line will undoubtedly be included amongst other WHERE statements. We need
		// to generically grab the SQL before and after the known line so we can rebuild our nice new
		// where statement. Here's the regex that brings it all together.
		//   Note: We are using the 'm' modifier so that the regex looks over multiple lines as well
		//         as the 's' modifier so that '.' includes linebreaks
		$full_regex = "/(.*)($known_sql_regex)(.*)/ms";

		// here's a regex to grab the post ID from a portion of the WHERE statement
		$post_id_regex = '/NOT IN\s*\(([0-9]+)\)/';

		if ( preg_match( $full_regex, $where_sql, $matches ) ) {
			// place capture groups into vars that are easier to read
			$before = $matches[1];
			$known  = $matches[2];
			$alias  = $matches[3];
			$gt_lt  = $matches[4];
			$after  = $matches[5];

			// copy the known line but replace the < or > symbol with an =
			$equal = preg_replace( '/(\<|\>)/', '=', $known );

			// extract the post ID from the extra "before" or "after" WHERE
			if (
				preg_match( $post_id_regex, $before, $post_id )
				|| preg_match( $post_id_regex, $after, $post_id )
			) {
				$post_id = absint( $post_id[1] );
			} else {
				// if we can't find the post ID, then let's bail
				return $where_sql;
			}

			// rebuild the WHERE clause
			$where_sql = "{$before} (
				{$known}
				OR (
					{$equal}
					AND {$alias}.post_id {$gt_lt} {$post_id}
				)
			) {$after} ";
		}

		return $where_sql;
	}

	/**
	 * Get the prev/next post for a given event. Ordered by start date instead of ID.
	 *
	 * @since 4.6.12
	 * @since 6.0.7 Cache the query results.
	 *
	 * @param string  $mode Either 'next' or 'previous'.
	 *
	 * @return null|WP_Post The closest Event post object, or `null` if no post was found.
	 */
	public function get_closest_event( $mode = 'next' ) {
		if ( empty( $this->current_event_id ) ) {
			return null;
		}

		$cache     = tribe_cache();
		$cache_key = 'tec_events_closest_event_' . $this->current_event_id . '_' . $mode;
		// The cached value will be the post ID, or `null`, to avoid pre-fetch issues.
		$cached = $cache->get( $cache_key, Cache_Listener::TRIGGER_SAVE_POST, false );
		$event = $cached;
		if ( ! empty( $cached ) ) {
			// If not empty, it should be a valid event post ID.
			$event = get_post( $cached );
			if ( ! ( $event instanceof WP_Post && $event->post_type === TEC::POSTTYPE ) ) {
				$event = false;
			}
		} elseif ( $cached !== null ) {
			// If not a post ID, then it should be `null`.
			$event = false;
		}

		$post_obj = get_post( $this->current_event_id );

		if ( $event === false ) {
			if ( 'previous' === $mode ) {
				$order     = 'DESC';
				$direction = '<';
			} else {
				$order     = 'ASC';
				$direction = '>';
				$mode      = 'next';
			}
			$args       = [
				'posts_per_page' => 1,
				'post__not_in'   => [ $this->current_event_id ],
				'meta_query'     => [
					[
						'key'     => '_EventStartDate',
						'value'   => $post_obj->_EventStartDate,
						'type'    => 'DATETIME',
						'compare' => $direction,
					],
					[
						'key'     => '_EventHideFromUpcoming',
						'compare' => 'NOT EXISTS',
					],
					'relation' => 'AND',
				],
			];
			$events_orm = tribe_events();
			/**
			 * Allows the query arguments used when retrieving the next/previous event link
			 * to be modified.
			 *
			 * @since 4.6.12
			 *
			 * @param array   $args
			 * @param WP_Post $post_obj
			 */
			$args = (array) apply_filters( "tribe_events_get_{$mode}_event_link", $args, $post_obj );
			$events_orm->order_by( 'event_date', $order );
			$events_orm->by_args( $args );
			$query = $events_orm->get_query();// Make sure we are not including same datetime events
			add_filter( 'posts_where', [ $this, 'get_closest_event_where' ] );// Fetch the posts
			$query->get_posts();// Remove this filter right after fetching the events
			remove_filter( 'posts_where', [ $this, 'get_closest_event_where' ] );
			$results = $query->posts;
			$event = null;

			// If we successfully located the next/prev event, we should have precisely one element in $results
			if ( 1 === count( $results ) ) {
				$event = reset( $results );
			}

			$value = $event instanceof WP_Post ? $event->ID : $event;
			// Cache until an Event is updated; just the ID to avoid pre-fetching issues, or `null`.
			$cache->set( $cache_key, $value, WEEK_IN_SECONDS, Cache_Listener::TRIGGER_SAVE_POST );
		}

		/**
		 * Affords an opportunity to modify the event used to generate the event link (typically for
		 * the next or previous event in relation to $post).
		 *
		 * @since 4.6.12
		 *
		 * @param WP_Post $post_obj
		 * @param string  $mode (typically "previous" or "next")
		 */
		return apply_filters( 'tribe_events_get_closest_event', $event, $post_obj, $mode );
	}

	/**
	 * Get a "previous/next post" link for events. Ordered by start date instead of ID.
	 *
	 * @since 4.6.12
	 *
	 * @param string  $mode Either 'next' or 'previous'.
	 * @param mixed   $anchor
	 *
	 * @return string The link (with <a> tags).
	 */
	public function get_event_link( $mode = 'next', $anchor = false ) {
		$link  = null;
		$event = $this->get_closest_event( $mode );

		// If we successfully located the next/prev event, we should have precisely one element in $results
		if ( $event ) {
			if ( ! $anchor ) {
				$anchor = apply_filters( 'the_title', $event->post_title, $event->ID );
			} elseif ( strpos( $anchor, '%title%' ) !== false ) {
				// get the nicely filtered post title
				$title = apply_filters( 'the_title', $event->post_title, $event->ID );

				// escape special characters used in the second parameter of preg_replace
				$title = str_replace(
					[
						'\\',
						'$',
					],
					[
						'\\\\',
						'\$',
					],
					$title
				);

				$anchor = preg_replace( '|%title%|', $title, $anchor );
			}

			$link = '<a href="' . esc_url( tribe_get_event_link( $event ) ) . '">' . $anchor . '</a>';
		}

		/**
		 * Affords an opportunity to modify the event link (typically for the next or previous
		 * event in relation to $post).
		 *
		 * @since 4.6.12
		 *
		 * @param string  $link
		 * @param int     $current_event_id
		 * @param WP_Post $event
		 * @param string  $mode (typically "previous" or "next")
		 * @param string  $anchor
		 */
		return apply_filters( 'tribe_events_get_event_link', $link, $this->current_event_id, $event, $mode, $anchor );
	}
}
