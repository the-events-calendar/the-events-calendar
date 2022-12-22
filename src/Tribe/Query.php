<?php
/**
 * Controls the main event query.  Allows for recurring events.
 */

use Tribe\Events\Views\V2\Views\Month_View;
use Tribe\Events\Views\V2\Views\List_View;
use Tribe__Utils__Array as Arr;
use Tribe__Date_Utils as Dates;
use Tribe__Events__Main as TEC;
use Tribe__Events__Venue as Venue;
use Tribe__Events__Organizer as Organizer;
use Tribe__Admin__Helpers as Admin_Helpers;

class Tribe__Events__Query {
	use Tribe__Events__Query_Deprecated;

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
		// Do not act on wrong object types or in the context of an Admin request.
		if ( ! $query instanceof WP_Query || is_admin() ) {
			return;
		}

		// If this is set then the class will bail out of any filtering.
		if ( $query->get( 'tribe_suppress_query_filters', false ) ) {
			return;
		}

		// Work out if this is an Event query or not, do not set the flag yet.
		$is_event_query = (array) $query->get( 'post_type' ) === [ TEC::POSTTYPE ];
		$any_post_type  = (array) $query->get( 'post_type' ) === [ 'any' ];
		$is_main_query  = $query->is_main_query();
		$query_post_types = self::get_query_post_types( $query );

		$tec_post_types   = [ TEC::POSTTYPE, Venue::POSTTYPE, Organizer::POSTTYPE ];

		if ( $is_main_query ) {
			if ( $query->is_single() && count( array_intersect( $query_post_types, $tec_post_types ) ) === 0 ) {
				// Do not modify a single query for a non-TEC post type.
				return;
			}

			// Commute the `tribe_paged` query var to the `paged` one, if set.
			$paged = tribe_get_request_var( 'tribe_paged' );
			if ( $paged !== null ) {
				$query->set( 'paged', (int) $paged );
			}

			if ( $query->is_home() ) {
				/**
				 * The following filter will remove the virtual page from the option page and return a 0 as it's not
				 * set when the SQL query is constructed to avoid having a is_page() instead of a is_home().
				 */
				add_filter( 'option_page_on_front', [ __CLASS__, 'default_page_on_front' ] );

				// Include Events in the main loop if the option is checked.
				if ( ! $any_post_type && tribe_get_option( 'showEventsInMainLoop', false ) ) {
					self::add_post_type_to_query( $query, TEC::POSTTYPE );
				}
			} else if ( $is_event_query ) {
				// Not the main query, but it's an event query: check back later to filter and order by date.
				add_filter( 'parse_query', [ __CLASS__, 'filter_and_order_by_date' ], 1000 );
			}
		}

		// Refresh the value of the flag: it might have changed in the previous block.
		$is_event_query = (array) $query->get( 'post_type' ) === [ TEC::POSTTYPE ];
		// Refresh the query post types: they might have been modified.
		$query_post_types = (array) $query->get( 'post_type' );

		// Add Events to tag archives when not looking at the admin screen for posts.
		if (
			! $any_post_type
			&& $query->is_tag
			&& ! $is_event_query
			&& ! Admin_Helpers::instance()->is_post_type_screen( 'post' )
		) {
			self::add_post_type_to_query( $query, TEC::POSTTYPE );
		}

		// Flag the query as one to fetch Events.
		$query->tribe_is_event = $is_event_query;

		// This query will fetch the Event post type, and others.
		$query->tribe_is_multi_posttype = $any_post_type
		                                  || (
			                                  count( $query_post_types ) > 1
			                                  && in_array( TEC::POSTTYPE, $query_post_types, true )
		                                  );

		// check if any possibility of this being an event category
		$query->tribe_is_event_category = $query->is_tax( TEC::TAXONOMY );

		$query->tribe_is_event_venue = $query_post_types === [ Venue::POSTTYPE ];

		$query->tribe_is_event_organizer = $query_post_types === [ Organizer::POSTTYPE ];

		$query->tribe_is_event_query = $query->tribe_is_event
		                               || $query->tribe_is_event_category
		                               || $query->tribe_is_event_venue
		                               || $query->tribe_is_event_organizer;

		$event_display = $query->get( 'eventDisplay' );

		$query->tribe_is_past = ( $is_main_query && 'past' === tribe_context()->get( 'event_display' ) )
		                        || $event_display === 'past';

		// Never allow 404 on month view.
		if (
			! $query->tribe_is_event_category
			&& $is_main_query
			&& $event_display === Month_View::get_view_slug()
			&& ! $query->is_tax()
		) {
			$query->is_post_type_archive = true;
			$query->queried_object       = get_post_type_object( TEC::POSTTYPE );
			$query->queried_object_id    = 0;
		}

		if ( tribe_is_events_front_page() ) {
			$query->is_home = true;
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
				&& in_array(
						$display,
						[
							'upcoming',
							List_View::get_view_slug()
						]
					)
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
	 * @since 6.0.2
	 *
	 * @param WP_Query $query The query object to modify.
	 *
	 * @return void The query object is modified by reference.
	 */
	public static function filter_and_order_by_date( $query ) {
		if ( ! $query instanceof WP_Query ) {
			return;
		}

		if ( (array) $query->get( 'post_type' ) !== [ TEC::POSTTYPE ] ) {
			// Not an Event only query.
			return;
		}

		if ( $query->get( 'tribe_suppress_query_filters', false ) ) {
			// Filters were suppressed by others, bail.
			return;
		}

		// If this is a query for a single event, we don't need to order it.
		if ( $query->is_single ) {
			return;
		}

		// Work done: stop filtering.
		remove_filter( current_action(), [ __CLASS__, 'filter_and_order_by_date' ] );

		$query_vars = $query->query_vars ?? [];

		// If a clause on the '_Event(Start|End)Date(UTC)' meta key is present in any query variable, bail.
		if ( ! empty( $query_vars ) && preg_match( '/_Event(Start|End)Date(UTC)?/', serialize( $query_vars ) ) ) {
			return;
		}

		// If the query order is `none` or `rand` we don't need to order it.
		if ( in_array( $query->get( 'orderby' ), [ 'none', 'rand' ], true ) ) {
			return;
		}

		/**
		 * Filters the value that will be used to indicate the current moment in an
		 * Event query. The query will return Events ending after the current moment.
		 *
		 * @since 6.0.2
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

	/**
	 * Returns the query post type(s) in array format.
	 *
	 * @since 6.0.6
	 *
	 * @param WP_Query $query The query object to read the post type entry from.
	 *
	 * @return array<string> The post type(s) read from the query.
	 */
	protected static function get_query_post_types( WP_Query $query ): array {
		$query_post_types = (array) $query->get( 'post_type' );

		// A query for the main posts page will not have a `post_type` set: let's correct that now.
		if ( $query_post_types === [ '' ] ) {
			$query_post_types = [ 'post' ];
		}

		return $query_post_types;
	}

	/**
	 * Updates the query post count to include the specified ones.
	 *
	 * @since 6.0.6
	 *
	 * @param WP_Query $query         The query object to modify.
	 * @param string   ...$post_types The post types to add to the query `post_types` entry.
	 *
	 * @return void The query object is modified by reference.
	 */
	protected static function add_post_type_to_query( WP_Query $query, string ...$post_types ): void {
		$query_post_types = self::get_query_post_types( $query );

		$updated_post_types = array_unique( array_merge( $post_types, $query_post_types ) );

		$query->set( 'post_type', $updated_post_types );
		$query->query['post_type'] = $updated_post_types;
	}
}
