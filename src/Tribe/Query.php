<?php
/**
 * Controls the main event query.  Allows for recurring events.
 */

use Tribe__Utils__Array as Arr;

class Tribe__Events__Query {
	/**
	 * @since 4.9.4
	 *
	 * @var array The WP_Query arguments used in the last `getEvents` method
	 *            query.
	 */
	protected static $last_result = [];

	/**
	 * Customized WP_Query wrapper to setup event queries with default arguments.
	 *
	 * @param array $args {
	 *		Optional. Array of Query parameters.
	 *
	 *      @type bool $found_posts Return the number of found events.
	 * }
	 * @param bool  $full Whether the full WP_Query object should returned (`true`) or just the
	 *                    found posts (`false`)
	 *
	 * @return array|WP_Query
	 */
	public static function getEvents( $args = [], $full = false ) {
		$defaults = [
			'orderby'              => 'event_date',
			'order'                => 'ASC',
			'posts_per_page'       => tribe_get_option( 'posts_per_page', tribe_get_option( 'postsPerPage', get_option( 'posts_per_page', 10 ) ) ),
			'tribe_render_context' => 'default',
		];

		$args = wp_parse_args( $args, $defaults );
		$event_display = tribe_get_request_var(
			'tribe_event_display',
			Tribe__Utils__Array::get( $args, 'eventDisplay', false )
		);

		$search = tribe_get_request_var( 'tribe-bar-search' );

		/**
		 * @todo Move this to each one of the views and their ajax requests
		 */
		// if a user provides a search term we want to use that in the search params
		if ( ! empty( $search ) ) {
			$args['s'] = $search;
		}

		$return_found_posts = ! empty( $args['found_posts'] );

		if ( $return_found_posts ) {
			unset( $args['found_posts'] );

			$args['posts_per_page'] = 1;
			$args['paged']          = 1;
		}

		// remove empty args and sort by key, this increases chance of a cache hit
		$args = array_filter( $args, [ __CLASS__, 'filter_args' ] );
		ksort( $args );

		/** @var Tribe__Cache $cache */
		$cache     = tribe( 'cache' );
		$cache_key = 'get_events_' . get_current_user_id() . serialize( $args );

		$result = $cache->get( $cache_key, 'save_post' );

		if (
			false !== $result
			&& (
				$result instanceof WP_Query
				|| (
					$return_found_posts
					&& is_int( $result )
				)
			)
		) {
			do_action( 'log', 'cache hit', 'tribe-events-cache', $args );
		} else {
			do_action( 'log', 'no cache hit', 'tribe-events-cache', $args );

			/** @var Tribe__Events__Repositories__Event $event_orm */
			$event_orm = tribe_events();

			$hidden = false;

			if ( isset( $args['tribe_render_context'] ) ) {
				$event_orm->set_render_context( $args['tribe_render_context'] );
			}

			if ( ! empty( $event_display ) ) {
				$event_orm->set_display_context( $event_display );
			}

			// Backcompat defaults.
			if ( isset( $args['hide_upcoming'] ) ) {
				// Negate the hide_upcoming for $hidden
				if ( true !== (boolean) $args['hide_upcoming'] ) {
					$hidden = null;
				}

				unset( $args['hide_upcoming'] );
			}

			$display = Arr::get( $args, 'eventDisplay' );
			$has_date_args = array_filter( [
				Arr::get( $args, 'start_date' ),
				Arr::get( $args, 'startDate' ),
				Arr::get( $args, 'starts_after' ),
				Arr::get( $args, 'starts_before' ),
				Arr::get( $args, 'end_date' ),
				Arr::get( $args, 'endDate' ),
				Arr::get( $args, 'ends_after' ),
				Arr::get( $args, 'ends_before' ),
			] );

			// Support for `eventDisplay = 'upcoming' || 'list'` for backwards compatibility
			if (
				! $has_date_args
				&& in_array( $display, [ 'upcoming', 'list' ] )
			) {
				if ( empty( $args['tribe_is_past'] ) ) {
					$args['start_date'] = 'now';
				}
				unset( $args['eventDisplay'] );
			}

			// Support for `eventDisplay = 'day'` for backwards compatibility
			if (
				! $has_date_args
				&& in_array( $display, [ 'day' ] )
			) {
				$args['start_date'] = 'today';
				unset( $args['eventDisplay'] );
			}

			// Support `tribeHideRecurrence` old param
			if ( isset( $args['tribeHideRecurrence'] ) ) {
				$args['hide_subsequent_recurrences'] = $args['tribeHideRecurrence'];
				unset( $args['tribeHideRecurrence'] );
			}

			if ( isset( $args['start_date'] ) && false === $args['start_date'] ) {
				unset( $args['start_date'] );
			}

			if ( isset( $args['end_date'] ) && false === $args['end_date'] ) {
				unset( $args['end_date'] );
			}

			if ( isset( $args['eventDate'] ) && ! isset( $args['start_date'], $args['end_date'] ) ) {
				$args['on_date'] = $args['eventDate'];
				unset( $args['eventDate'] );
			}

			if ( ! empty( $args['orderby'] ) ) {
				$event_orm->order_by( $args['orderby'] );

				unset( $args['orderby'] );
			}

			if ( 'all' === $event_display  ) {
				if ( empty( $args['post_parent'] ) ) {
					// Make sure the `post_parent` ID is set in /all requests.
					$parent_name = Tribe__Utils__Array::get(
						$args,
						'name',
						Tribe__Utils__Array::get( $args, 'tribe_events', false )
					);

					if ( ! empty( $parent_name ) ) {
						$post_parent         = tribe_events()->where( 'name', $parent_name )->fields( 'ids' )
						                                     ->first();
						$args['post_parent'] = $post_parent;
					}

					// Make sure these are unset to avoid 'post_name' comparisons.
					unset( $args['name'], $args['post_name'], $args['tribe_events'] );
				}

				if ( class_exists( 'Tribe__Events__Pro__Recurrence__Event_Query' ) ) {
					$recurrence_query = new Tribe__Events__Pro__Recurrence__Event_Query();
					$parent_post      = get_post( $args['post_parent'] );
					if ( $parent_post instanceof WP_Post ) {
						$recurrence_query->set_parent_event( $parent_post );
						add_filter( 'posts_where', [ $recurrence_query, 'include_parent_event' ], 100 );
					}
				}
			}

			$is_past = ! empty( $args['tribe_is_past'] ) || 'past' === $event_display;
			if ( $is_past ) {
				$args['order'] = 'DESC';
				/*
				 * If in the context of a "past" view let's try to use, as limit, the same
				 * end date limit passed, if any.
				 */
				$now = isset( $args['ends_before'] ) ? $args['ends_before'] : 'now';
				$pivot_date = tribe_get_request_var( 'tribe-bar-date', $now );
				$date       = Tribe__Date_Utils::build_date_object( $pivot_date );
				// Remove any existing date meta queries.
				if ( isset( $args['meta_query'] ) ) {
					$args['meta_query'] = tribe_filter_meta_query(
						$args['meta_query'],
						[ 'key' => '/_Event(Start|End)Date(UTC)/' ]
					);
				}

				/**
				 * We used to use the `tribe_beginning_of_day` for part of the query.
				 *
				 * Intentionally changed the behavior here to use "now" as part of the code
				 *
				 * @link https://central.tri.be/issues/123950
				 */
				$args['starts_before'] = $date->format( Tribe__Date_Utils::DBDATETIMEFORMAT );
			}

			if ( null !== $hidden ) {
				$event_orm->by( 'hidden', $hidden );
				if ( isset( $args['meta_query'] ) ) {
					$args['meta_query'] = tribe_filter_meta_query(
						$args['meta_query'],
						[ 'key' => '_EventHideFromUpcoming' ]
					);
				}
			}

			/**
			 * Some key arguments have been passed as arrays but will require unpacking.
			 * Due to the dynamic nature of the ORM implementation this is a curated list
			 * that should be updated here. Do not try to move this conditional unpacking logic
			 * in the ORM: this is an issue the proxy function should handle ad-hoc.
			 */
			$requiring_unpack = [ 'date_overlaps', 'runs_between' ];
			foreach ( array_intersect( array_keys( $args ), $requiring_unpack ) as $key ) {
				$event_orm->by( $key, ...$args[ $key ] );
				unset( $args[ $key ] );
			}

			$event_orm->by_args( $args );

			if ( $return_found_posts ) {
				$result = $event_orm->found();
			} else {
				$result = $event_orm->get_query();

				// Set the event display, if any, for back-compatibility purposes.
				if ( ! empty( $event_display ) ) {
					$result->set( 'eventDisplay', $event_display );
				}

				// Run the query.
				$result->get_posts();
				self::$last_result = empty( $result->posts ) ? [] : $result->posts;
			}

			$cache->set( $cache_key, $result, Tribe__Cache::NON_PERSISTENT, 'save_post' );
		}


		if ( $return_found_posts ) {
			return $result;
		}

		if ( ! empty( $result->posts ) ) {
			self::$last_result = empty( $result->posts ) ? [] : $result->posts;
			if ( $full ) {
				return $result;
			}
			return $result->posts;
		}

		if ( $full ) {
			self::$last_result = empty( $result->posts ) ? [] : $result->posts;
			return $result;
		}

		self::$last_result = [];
		return [];
	}

	/**
	 * Remove empty values from the query args
	 *
	 * @param mixed $arg
	 *
	 * @return bool
	 **/
	private static function filter_args( $arg ) {
		if ( empty( $arg ) && $arg !== false && 0 !== $arg ) {
			return false;
		}

		return true;
	}
}
