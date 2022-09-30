<?php
/**
 * Controls the main event query.  Allows for recurring events.
 */

use Tribe__Utils__Array as Arr;
use Tribe__Date_Utils as Dates;
use Tribe__Events__Main as TEC;

class Tribe__Events__Query {
	/**
	 * @since 4.9.4
	 *
	 * @var array The WP_Query arguments used in the last `getEvents` method
	 *            query.
	 */
	protected static $last_result = [];

	/**
	 * Set any query flags
	 *
	 * @param WP_Query $query
	 */
	public static function parse_query( $query ) {
		if ( is_admin() ) {
			return $query;
		}

		// If this is set then the class will bail out of any filtering.
		if ( $query->get( 'tribe_suppress_query_filters', false ) ) {
			return $query;
		}

		$context = tribe_context();

		// These are only required for Main Query stuff.
		if ( ! ( $context->is( 'is_main_query' ) && $context->is( 'tec_post_type' ) ) ) {
			if ( ( (array) $query->get( 'post_type', [] ) ) === [ TEC::POSTTYPE ] ) {
				// Not the main query in Event context, but it's an event query: check back later.
				add_filter( 'parse_query', [ __CLASS__, 'filter_and_order_by_date' ], 1000 );
			}

			return $query;
		}

		// set paged
		if ( isset( $_GET['tribe_paged'] ) ) {
			$query->set( 'paged', absint( tribe_get_request_var( 'tribe_paged' ) ) );
		}

		// Add tribe events post type to tag queries only in tag archives
		if (
			$query->is_tag
			&& (array) $query->get( 'post_type' ) != [ Tribe__Events__Main::POSTTYPE ]
		) {
			$types = $query->get( 'post_type' );

			if ( empty( $types ) ) {
				$types = [ 'post' ];
			}

			if ( is_array( $types ) && $query->is_main_query() ) {
				$types[] = Tribe__Events__Main::POSTTYPE;
			} elseif ( $query->is_main_query() ) {
				if ( is_string( $types ) ) {
					$types = [ $types, Tribe__Events__Main::POSTTYPE ];
				} else {
					if ( $types != 'any' ) {
						$types = [ 'post', Tribe__Events__Main::POSTTYPE ];
					}
				}
			}

			$query->set( 'post_type', $types );
		}

		$types = (array) $context->get( 'post_type' );

		// check if any possibility of this being an event query
		$query->tribe_is_event = $context->is( 'event_post_type' );

		$query->tribe_is_multi_posttype = ( $query->tribe_is_event && count( $types ) >= 2 ) || in_array( 'any', $types );

		// check if any possibility of this being an event category
		$query->tribe_is_event_category = $context->is( 'event_category' );

		$query->tribe_is_event_venue = $context->is( 'venue_post_type' );

		$query->tribe_is_event_organizer = $context->is( 'organizer_post_type' );

		$query->tribe_is_event_query = $context->is( 'tec_post_type' );

		$query->tribe_is_past = 'past' === $context->get( 'event_display' );

		// never allow 404 on month view
		if (
			$query->is_main_query()
			&& 'month' === $query->get( 'eventDisplay' )
			&& ! $query->is_tax
			&& ! $query->tribe_is_event_category
		) {
			$query->is_post_type_archive = true;
			$query->queried_object       = get_post_type_object( Tribe__Events__Main::POSTTYPE );
			$query->queried_object_id    = 0;
		}

		if ( tribe_is_events_front_page() ) {
			$query->is_home = true;
		} else {
			$query->is_home = empty( $query->query_vars['is_home'] ) ? false : $query->query_vars['is_home'];
		}

		// Hook reasonably late on the action that will fire next to filter and order Events by date, if required.
		add_filter( 'tribe_events_parse_query', [ __CLASS__, 'filter_and_order_by_date' ], 1000 );

		/**
		 * Fires after the query has been parsed by The Events Calendar.
		 * If this action fires, then the query is for the Event post type, is the main
		 * query, and TEC filters are not suppressed.
		 *
		 * @since 3.5.1
		 *
		 * @param WP_Query $query The parsed WP_Query object.
		 */
		do_action( 'tribe_events_parse_query', $query );
	}

	/**
	 * Customized WP_Query wrapper to set up event queries with default arguments.
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

	/**
	 * If the user has the Main events page set on the reading options it should return 0 or the default value in
	 * order to avoid to set the:
	 * - p
	 * - page_id
	 *
	 * variables when using  pre_get_posts or posts_where
	 *
	 * This filter is removed when this functions has finished the execution
	 *
	 * @since 4.6.15
	 *
	 * @param $value
	 *
	 * @return int
	 */
	public static function default_page_on_front( $value ) {
		return tribe( 'tec.front-page-view' )->is_virtual_page_id( $value ) ? 0 : $value;
	}

	/**
	 * Provided a query for Events, the method will set the query variables up to filter
	 * and order Events by start and end date.
	 *
	 * @since TBD
	 *
	 * @param WP_Query $query The query object to modify.
	 *
	 * @return void The query object is modified by reference.
	 */
	public static function filter_and_order_by_date( $query ) {
		if ( ! $query instanceof WP_Query ) {
			return;
		}

		if ( $query->get( 'tribe_suppress_query_filters', false ) ) {
			// Filters were suppressed by others, bail.
			return;
		}

		// Work done: stop filtering.
		remove_filter( current_action(), [ __CLASS__, 'filter_and_order_by_date' ] );

		$query_vars = $query->query_vars ?? [];

		// If a clause on the '_Event(Start|End)Date(UTC)' meta key is present in any query variable, bail.
		if ( ! empty( $query_vars ) && preg_match( '/_Event(Start|End)Date(UTC)?/', serialize( $query_vars ) ) ) {
			return;
		}

		/**
		 * Filters the value that will be used to indicate the current moment in an
		 * Event query. The query will return Events ending after the current moment.
		 *
		 * @since TBD
		 *
		 * @param string|int|DateTimeInterface $current_moment The current moment, defaults to `now`.
		 * @param WP_Query                     $query          The query object being filtered.
		 */
		$current_moment = apply_filters( 'tec_events_query_current_moment', 'now', $query );

		// Only get Events ending after now altering the current meta query.
		$meta_query = $query_vars['meta_query'] ?? [];
		$meta_query['tec_event_start_date'] = [
			'key'     => '_EventStartDate',
			'compare' => 'EXISTS',
		];
		$meta_query['tec_event_end_date'] = [
			'key'     => '_EventEndDate',
			'value'   => Dates::immutable( $current_moment )->format( Dates::DBDATETIMEFORMAT ),
			'compare' => '>=',
			'type'    => 'DATETIME',
		];
		$query->query_vars['meta_query'] = $meta_query;

		// Order the resulting events by start date, then post date.
		$orderby = $query_vars['orderby'] ?? '';
		$order = $query_vars['order'] ?? null;
		$query->query_vars['orderby'] = tribe_normalize_orderby( $orderby, $order );
		$query->query_vars['orderby']['tec_event_start_date'] = 'ASC';
		$query->query_vars['orderby']['post_date'] = 'ASC';

		// Duplicate the values on the `query` property of the query.
		$query->query['meta_query'] = $query->query_vars['meta_query'];
		$query->query['orderby'] = $query->query_vars['orderby'];
	}
}
