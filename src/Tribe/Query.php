<?php
/**
 * Controls the main event query.  Allows for recurring events.
 */

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( ! class_exists( 'Tribe__Events__Query' ) ) {
	class Tribe__Events__Query {

		/**
		 * Initialize The Events Calendar query filters and post processing.
		 */
		public static function init() {

			// if tribe event query add filters
			add_action( 'parse_query', array( __CLASS__, 'parse_query' ), 50 );
			add_action( 'pre_get_posts', array( __CLASS__, 'pre_get_posts' ), 50 );
			// Remove the filter to reset the value of the virtual page if is setup.
			add_filter( 'posts_results', array( __CLASS__, 'posts_results' ) );

			if ( is_admin() ) {
				$cleanup = new Tribe__Events__Recurring_Event_Cleanup();
				$cleanup->toggle_recurring_events();
				unset( $cleanup );
			}
		}

		/**
		 * determines whether a date field can be injected into various parts of a query
		 *
		 * @param $query WP_Query Query object
		 *
		 * @return boolean
		 */
		public static function can_inject_date_field( $query ) {
			$can_inject = true;

			if ( 'ids' === $query->query_vars['fields'] ) {
				$can_inject = false;
			}

			if ( 'id=>parent' === $query->query_vars['fields'] ) {
				$can_inject = false;
			}

			if ( isset( $query->query_vars['do_not_inject_date'] ) && $query->query_vars['do_not_inject_date'] ) {
				$can_inject = false;
			}

			/**
			 * Determine whether a date field can be injected into various parts of a query.
			 *
			 * @param boolean  $can_inject Whether the date field can be injected
			 * @param WP_Query $query      Query object
			 *
			 * @since 4.5.8
			 */
			return apply_filters( 'tribe_query_can_inject_date_field', $can_inject, $query );
		}

		/**
		 * Set any query flags
		 *
		 * @param WP_Query $query
		 */
		public static function parse_query( $query ) {
			$helper = Tribe__Admin__Helpers::instance();

			// set paged
			if ( $query->is_main_query() && isset( $_GET['tribe_paged'] ) ) {
				$query->set( 'paged', $_REQUEST['tribe_paged'] );
			}

			// Return early as we don't want to change a post that is not part of the valid group of event post types.
			$valid_post_types = array(
				Tribe__Events__Main::POSTTYPE,
				Tribe__Events__Venue::POSTTYPE,
				Tribe__Events__Organizer::POSTTYPE,
			);
			if (
				$query->is_main_query()
				&& $query->is_single()
				// Make sure we are not in a Post Type declared by the plugin.
				&& false === in_array( $query->get( 'post_type' ), $valid_post_types )
			)  {
				return $query;
			}

			// Don't change query on pages as we might be ina shortcode.
			if ( $query->is_main_query() && $query->is_page() ) {
				return $query;
			}

			// Add tribe events post type to tag queries only in tag archives
			if (
				$query->is_tag
				&& (array) $query->get( 'post_type' ) != array( Tribe__Events__Main::POSTTYPE )
				&& ! $helper->is_post_type_screen( 'post' )
			) {
				$types = $query->get( 'post_type' );

				if ( empty( $types ) ) {
					$types = array( 'post' );
				}

				if ( is_array( $types ) && $query->is_main_query() ) {
					$types[] = Tribe__Events__Main::POSTTYPE;
				} elseif ( $query->is_main_query() ) {
					if ( is_string( $types ) ) {
						$types = array( $types, Tribe__Events__Main::POSTTYPE );
					} else {
						if ( $types != 'any' ) {
							$types = array( 'post', Tribe__Events__Main::POSTTYPE );
						}
					}
				}

				$query->set( 'post_type', $types );
			}

			$types = ( ! empty( $query->query_vars['post_type'] ) ? (array) $query->query_vars['post_type'] : array() );

			// check if any possiblity of this being an event query
			$query->tribe_is_event = ( in_array( Tribe__Events__Main::POSTTYPE, $types ) && count( $types ) < 2 )
				? true // it was an event query
				: false;

			$query->tribe_is_multi_posttype = ( in_array( Tribe__Events__Main::POSTTYPE, $types ) && count( $types ) >= 2 || in_array( 'any', $types ) )
				? true // it's a query for multiple post types, events post type included
				: false;

			if ( 'default' === $query->get( 'eventDisplay' ) ) {
				$query->set( 'eventDisplay', Tribe__Events__Main::instance()->default_view() );
			}

			// check if any possiblity of this being an event category
			$query->tribe_is_event_category = ! empty ( $query->query_vars[ Tribe__Events__Main::TAXONOMY ] )
				? true // it was an event category
				: false;

			$query->tribe_is_event_venue = ( in_array( Tribe__Events__Main::VENUE_POST_TYPE, $types ) )
				? true // it was an event venue
				: false;

			$query->tribe_is_event_organizer = ( in_array( Tribe__Events__Main::ORGANIZER_POST_TYPE, $types ) )
				? true // it was an event organizer
				: false;

			$query->tribe_is_event_query = ( $query->tribe_is_event
			                                 || $query->tribe_is_event_category
			                                 || $query->tribe_is_event_venue
			                                 || $query->tribe_is_event_organizer )
				? true // this is an event query of some type
				: false; // move along, this is not the query you are looking for

			// is the query pulling posts from the past?
			$past_requested = ! empty( $_REQUEST['tribe_event_display'] ) && 'past' === $_REQUEST['tribe_event_display'];
			$display_past   = 'past' === $query->get( 'eventDisplay' );

			if ( $query->is_main_query() && $past_requested ) {
				$query->tribe_is_past = true;
			} elseif ( tribe_is_ajax_view_request() && ( $display_past || $past_requested ) ) {
				$query->tribe_is_past = true;
			} elseif ( $query->get( 'tribe_is_past' ) ) {
				$query->tribe_is_past = true;
			} else {
				$query->tribe_is_past = isset( $query->tribe_is_past ) ? $query->tribe_is_past : false;
			}

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
			} elseif ( $query->tribe_is_event_query ) {
				// fixing is_home param
				$query->is_home = empty( $query->query_vars['is_home'] ) ? false : $query->query_vars['is_home'];
				do_action( 'tribe_events_parse_query', $query );
			}
		}

		/**
		 * Is hooked by init() filter to parse the WP_Query arguments for main and alt queries.
		 *
		 * @param object $query WP_Query object args supplied or default
		 *
		 * @return object $query (modified)
		 */
		public static function pre_get_posts( $query ) {
			$admin_helpers = Tribe__Admin__Helpers::instance();

			if ( $query->is_main_query() && is_home() ) {
				/**
				 * The following filter will remove the virtual page from the option page and return a 0 as it's not
				 * set when the SQL query is constructed to avoid having a is_page() instead of a is_home().
				 */
				add_filter( 'option_page_on_front', array( __CLASS__, 'default_page_on_front' ) );
				// check option for including events in the main wordpress loop, if true, add events post type
				if ( tribe_get_option( 'showEventsInMainLoop', false ) ) {
					$query->query_vars['post_type']   = isset( $query->query_vars['post_type'] )
						? ( array ) $query->query_vars['post_type']
						: array( 'post' );

					if ( ! in_array( Tribe__Events__Main::POSTTYPE, $query->query_vars['post_type'] ) ) {
						$query->query_vars['post_type'][] = Tribe__Events__Main::POSTTYPE;
					}
					$query->tribe_is_multi_posttype   = true;
				}
			}

			if ( $query->tribe_is_multi_posttype ) {
				do_action( 'log', 'multi_posttype', 'default', $query->tribe_is_multi_posttype );
				add_filter( 'posts_fields', array( __CLASS__, 'multi_type_posts_fields' ), 10, 2 );
				add_filter( 'posts_join', array( __CLASS__, 'posts_join' ), 10, 2 );
				add_filter( 'posts_join', array( __CLASS__, 'posts_join_venue_organizer' ), 10, 2 );
				add_filter( 'posts_distinct', array( __CLASS__, 'posts_distinct' ) );
				add_filter( 'posts_orderby', array( __CLASS__, 'posts_orderby' ), 10, 2 );
				do_action( 'tribe_events_pre_get_posts', $query );

				return;
			}

			if ( $query->tribe_is_event || $query->tribe_is_event_category ) {

				if ( ! ( $query->is_main_query() && 'month' === $query->get( 'eventDisplay' ) ) ) {
					add_filter( 'option_page_on_front', array( __CLASS__, 'default_page_on_front' ) );
					add_filter( 'posts_fields', array( __CLASS__, 'posts_fields' ), 10, 2 );
					add_filter( 'posts_join', array( __CLASS__, 'posts_join' ), 10, 2 );
					add_filter( 'posts_join', array( __CLASS__, 'posts_join_venue_organizer' ), 10, 2 );
					add_filter( 'posts_where', array( __CLASS__, 'posts_where' ), 10, 2 );
					add_filter( 'posts_distinct', array( __CLASS__, 'posts_distinct' ) );
				} else {

					// reduce number of queries triggered by main WP_Query on month view
					$query->set( 'posts_per_page', 1 );
					$query->set( 'no_found_rows', true );
					$query->set( 'cache_results', false );
					$query->set( 'update_post_meta_cache', false );
					$query->set( 'update_post_term_cache', false );
					do_action( 'tribe_events_pre_get_posts', $query );

					return $query;
				}

				// if a user selects a date in the event bar we want it to persist as long as possible
				if ( ! empty( $_REQUEST['tribe-bar-date'] ) ) {
					$query->set( 'eventDate', $_REQUEST['tribe-bar-date'] );
					do_action( 'log', 'changed eventDate to tribe-bar-date', 'tribe-events-query', $_REQUEST['tribe-bar-date'] );
				}

				// if a user provides a search term we want to use that in the search params
				if ( ! empty( $_REQUEST['tribe-bar-search'] ) ) {
					$query->query_vars['s'] = $_REQUEST['tribe-bar-search'];
				}

				$query->set( 'eventDisplay', $query->get( 'eventDisplay', Tribe__Events__Main::instance()->displaying ) );

				// By default we'll hide events marked as "hidden from event listings" unless
				// the query explicity requests they be exposed
				$maybe_hide_events = (bool) $query->get( 'hide_upcoming', true );

				//@todo stop calling EOD cutoff transformations all over the place

				if ( ! empty( $query->query_vars['eventDisplay'] ) ) {
					switch ( $query->query_vars['eventDisplay'] ) {
						case 'custom':
							// if the eventDisplay is 'custom', all we're gonna do is make sure the start and end dates are formatted
							$start_date = $query->get( 'start_date' );

							if ( $start_date ) {
								$start_date_string = $start_date instanceof DateTime
									? $start_date->format( Tribe__Date_Utils::DBDATETIMEFORMAT )
									: $start_date;

								$query->set( 'start_date', date_i18n( Tribe__Date_Utils::DBDATETIMEFORMAT, strtotime( $start_date_string ) ) );
							}

							$end_date = $query->get( 'end_date' );

							if ( $end_date ) {
								$end_date_string = $end_date instanceof DateTime
									? $end_date->format( Tribe__Date_Utils::DBDATETIMEFORMAT )
									: $end_date;

								$query->set( 'end_date', date_i18n( Tribe__Date_Utils::DBDATETIMEFORMAT, strtotime( $end_date_string ) ) );
							}
							break;
						case 'month':

							// make sure start and end date are set
							if ( $query->get( 'start_date' ) == '' ) {
								$event_date = ( $query->get( 'eventDate' ) != '' )
									? $query->get( 'eventDate' )
									: date_i18n( Tribe__Date_Utils::DBDATETIMEFORMAT );
								$query->set( 'start_date', tribe_beginning_of_day( $event_date ) );
							}

							if ( $query->get( 'end_date' == '' ) ) {
								$query->set( 'end_date', tribe_end_of_day( $query->get( 'start_date' ) ) );
							}
							$query->set( 'hide_upcoming', $maybe_hide_events );

							break;
						case 'day':
							$event_date = $query->get( 'eventDate' ) != '' ? $query->get( 'eventDate' ) : date( 'Y-m-d', current_time( 'timestamp' ) );
							$query->set( 'eventDate', $event_date );
							$beginning_of_day = strtotime( tribe_beginning_of_day( $event_date ) );
							$query->set( 'start_date', date_i18n( Tribe__Date_Utils::DBDATETIMEFORMAT, $beginning_of_day ) );
							$query->set( 'end_date', tribe_end_of_day( $event_date ) );
							$query->set( 'posts_per_page', - 1 ); // show ALL day posts
							$query->set( 'hide_upcoming', $maybe_hide_events );
							$query->set( 'order', self::set_order( 'ASC', $query ) );
							break;
						case 'single-event':
							if ( $query->get( 'eventDate' ) != '' ) {
								$query->set( 'start_date', $query->get( 'eventDate' ) );
								$query->set( 'eventDate', $query->get( 'eventDate' ) );
							}
							break;
						case 'future':
							$event_date = ( '' !== $query->get( 'eventDate' ) )
								? $query->get( 'eventDate' )
								: date_i18n( Tribe__Date_Utils::DBDATETIMEFORMAT );
							$query->set( 'start_date', ( '' != $query->get( 'eventDate' ) ? tribe_beginning_of_day( $event_date ) : tribe_format_date( current_time( 'timestamp' ), true, 'Y-m-d H:i:00' ) ) );
							$query->set( 'order', self::set_order( 'ASC', $query ) );
							$query->set( 'orderby', self::set_orderby( null, $query ) );
							$query->set( 'hide_upcoming', $maybe_hide_events );
							break;
						case 'all':
						case 'list':
						default: // default display query
							$event_date = ( $query->get( 'eventDate' ) != '' )
								? $query->get( 'eventDate' )
								: date_i18n( Tribe__Date_Utils::DBDATETIMEFORMAT );
							if ( ! $query->tribe_is_past ) {
								$query->set( 'start_date', ( '' != $query->get( 'eventDate' ) ? tribe_beginning_of_day( $event_date ) : tribe_format_date( current_time( 'timestamp' ), true, 'Y-m-d H:i:00' ) ) );
								$query->set( 'end_date', '' );
								$query->set( 'order', self::set_order( 'ASC', $query ) );
							} else {
								// on past view, set the passed date as the end date
								$query->set( 'start_date', '' );
								$query->set( 'end_date', $event_date );
								$query->set( 'order', self::set_order( 'DESC', $query ) );
							}
							$query->set( 'orderby', self::set_orderby( null, $query ) );
							$query->set( 'hide_upcoming', $maybe_hide_events );
							break;
					}
				} else {
					$query->set( 'hide_upcoming', $maybe_hide_events );
					$query->set( 'start_date', date_i18n( Tribe__Date_Utils::DBDATETIMEFORMAT ) );
					$query->set( 'orderby', self::set_orderby( null, $query ) );
					$query->set( 'order', self::set_order( null, $query ) );
				}

				// eventCat becomes a standard taxonomy query - will need to deprecate and update views eventually
				if ( ! in_array( $query->get( Tribe__Events__Main::TAXONOMY ), array( '', '-1' ) ) ) {
					$tax_query[] = array(
						'taxonomy'         => Tribe__Events__Main::TAXONOMY,
						'field'            => 'slug',
						'terms'            => $query->get( Tribe__Events__Main::TAXONOMY ),
						'include_children' => apply_filters( 'tribe_events_query_include_children', true ),
					);
				}

				// Only add the postmeta hack if it's not the main admin events list
				// Because this method filters out drafts without EventStartDate.
				// For this screen we're doing the JOIN manually in Tribe__Events__Admin_List

				if ( ! Tribe__Admin__Helpers::instance()->is_screen( 'edit-tribe_events' ) ) {
					$event_start_key = Tribe__Events__Timezones::is_mode( 'site' )
						? '_EventStartDateUTC'
						: '_EventStartDate';

					$meta_query[] = array(
						'key'  => $event_start_key,
						'type' => 'DATETIME',
					);
				}
			}

			// filter by Venue ID
			if ( $query->tribe_is_event_query && $query->get( 'venue' ) != '' ) {
				$meta_query[] = array(
					'key'   => '_EventVenueID',
					'value' => $query->get( 'venue' ),
				);
			}

			// filter by Organizer ID
			if ( $query->tribe_is_event_query && $query->get( 'organizer' ) != '' ) {
				$meta_query[] = array(
					'key'   => '_EventOrganizerID',
					'value' => $query->get( 'organizer' ),
				);
			}

			// enable pagination setup
			if ( $query->tribe_is_event_query && $query->get( 'posts_per_page' ) == '' ) {
				$query->set( 'posts_per_page', (int) tribe_get_option( 'postsPerPage', 10 ) );
			}

			// hide upcoming events from query (only not in admin)
			if ( $query->tribe_is_event_query && $query->get( 'hide_upcoming' ) && ! $query->get( 'suppress_filters' ) ) {
				$hide_upcoming_ids = self::getHideFromUpcomingEvents();
				if ( ! empty( $hide_upcoming_ids ) ) {
					// Merge if there is any items and remove empty items
					$hide_upcoming_ids = array_filter( array_merge( $hide_upcoming_ids, (array) $query->get( 'post__not_in' ) ) );

					$query->set( 'post__not_in', $hide_upcoming_ids );
				}
			}

			if ( $query->tribe_is_event_query && ! empty( $meta_query ) ) {
				// setup default relation for meta queries
				$meta_query['relation'] = 'AND';
				$meta_query_combined    = array_merge( (array) $meta_query, (array) $query->get( 'meta_query' ) );
				$query->set( 'meta_query', $meta_query_combined );
			}

			if ( $query->tribe_is_event_query && ! empty( $tax_query ) ) {
				// setup default relation for tax queries
				$tax_query_combined = array_merge( (array) $tax_query, (array) $query->get( 'tax_query' ) );
				$query->set( 'tax_query', $tax_query_combined );
			}

			if ( $query->tribe_is_event_query ) {
				add_filter( 'posts_orderby', array( __CLASS__, 'posts_orderby' ), 10, 2 );
			}

			if ( $query->tribe_is_event_query ) {
				do_action( 'tribe_events_pre_get_posts', $query );
			}

			/**
			 * If is in the admin remove the event date & upcoming filters, unless is an ajax call
			 * It's important to note that `tribe_remove_date_filters` needs to be set before calling
			 * self::should_remove_date_filters() to allow the date_filters to be actually removed
			 */
			if ( self::should_remove_date_filters( $query ) ) {
				remove_filter( 'option_page_on_front', array( __CLASS__, 'default_page_on_front' ) );
				remove_filter( 'posts_where', array( __CLASS__, 'posts_where' ), 10, 2 );
				remove_filter( 'posts_fields', array( __CLASS__, 'posts_fields' ) );
				remove_filter( 'posts_orderby', array( __CLASS__, 'posts_orderby' ), 10, 2 );
				$query->set( 'post__not_in', '' );

				// set the default order for posts within admin lists
				if ( ! isset( $query->query['order'] ) ) {
					$query->set( 'order', 'DESC' );
				} else {
					// making sure we preserve the order supplied by the query string even if it is overwritten above
					$query->set( 'order', $query->query['order'] );
				}
			}

			return $query;
		}

		/**
		 * Return a false ID when the SQL query is being constructed to avoid create a false positive with a page and
		 * add the virtual ID to the SQL query. Once this one has been added we need to remove the filter as is no
		 * longer required or used.
		 *
		 * This is done after we have results of the posts so the filter can be safely removed at this stage.
		 *
		 * @since 4.6.15
		 *
		 * @param $posts
		 *
		 * @return mixed
		 */
		public static function posts_results( $posts ) {
			if ( tribe( 'tec.front-page-view' )->is_page_on_front() ) {
				remove_filter( 'option_page_on_front', array( __CLASS__, 'default_page_on_front' ) );
			}
			return $posts;
		}

		/**
		 * Returns whether or not the event date & upcoming filters should be removed from the query
		 *
		 * @since 4.0
		 * @param WP_Query $query WP_Query object
		 * @return boolean
		 */
		public static function should_remove_date_filters( $query ) {
			// if the query flag to remove date filters is explicitly set then remove them
			if ( true === $query->get( 'tribe_remove_date_filters', false ) ) {
				return true;
			}

			// if we're doing ajax, let's keep the date filters
			if ( tribe( 'context' )->doing_ajax() ) {
				return false;
			}

			// otherwise, let's remove the date filters if we're in the admin dashboard and the query is
			// an event query on the tribe_events edit page
			return is_admin()
				&& $query->tribe_is_event_query
				&& Tribe__Admin__Helpers::instance()->is_screen( 'edit-' . Tribe__Events__Main::POSTTYPE );
		}

		/**
		 * Adds DISTINCT to the query.
		 *
		 * @param string $distinct The current DISTINCT statement.
		 *
		 * @return string The modified DISTINCT statement.
		 */
		public static function posts_distinct( $distinct ) {
			return 'DISTINCT';
		}

		/**
		 * Adds the proper fields to the FIELDS statement in the query.
		 *
		 * @param string   $field_sql The current/original FIELDS statement.
		 * @param WP_Query $query     The current query object.
		 *
		 * @return string The modified FIELDS statement.
		 */
		public static function posts_fields( $field_sql, $query ) {
			if (
				(
					! empty( $query->tribe_is_event )
					|| ! empty( $query->tribe_is_event_category )
				)
				&& self::can_inject_date_field( $query )
			) {
				$postmeta_table             = self::postmeta_table( $query );
				$fields                     = array();
				$fields['event_start_date'] = "MIN({$postmeta_table}.meta_value) as EventStartDate";
				$fields['event_end_date']   = 'MIN(tribe_event_end_date.meta_value) as EventEndDate';
				$fields                     = apply_filters( 'tribe_events_query_posts_fields', $fields, $query );

				return $field_sql . ', ' . implode( ', ', $fields );
			} else {
				return $field_sql;
			}
		}

		/**
		 * Adds the proper fields to the FIELDS statement in the query.
		 *
		 * @param string   $field_sql The current/original FIELDS statement.
		 * @param WP_Query $query     The current query object.
		 *
		 * @return string The modified FIELDS statement.
		 */
		public static function multi_type_posts_fields( $field_sql, $query ) {
			if (
				! empty( $query->tribe_is_multi_posttype )
				&& self::can_inject_date_field( $query )
			) {
				global $wpdb;
				$postmeta_table = self::postmeta_table( $query );
				$fields         = array();
				$fields[]       = "IF ({$wpdb->posts}.post_type = 'tribe_events', $postmeta_table.meta_value, {$wpdb->posts}.post_date) AS post_date";
				$fields         = apply_filters( 'tribe_events_query_posts_fields', $fields, $query );

				return $field_sql . ', ' . implode( ', ', $fields );
			} else {
				return $field_sql;
			}
		}

		/**
		 * Custom SQL join for event end date
		 *
		 * @param string   $join_sql
		 * @param wp_query $query
		 *
		 * @return string
		 */
		public static function posts_join( $join_sql, $query ) {
			global $wpdb;
			$joins = array();

			$postmeta_table = self::postmeta_table( $query );

			$event_start_key = '_EventStartDate';
			$event_end_key   = '_EventEndDate';

			/**
			 * When the "Use site timezone everywhere" option is checked in events settings,
			 * the UTC time for event start and end times will be used. This filter allows the
			 * disabling of that in certain contexts, so that local (not UTC) event times are used.
			 *
			 * @since 4.6.10
			 *
			 * @param boolean $force_local_tz Whether to force the local TZ.
			 */
			$force_local_tz = apply_filters( 'tribe_events_query_force_local_tz', false );

			if ( Tribe__Events__Timezones::is_mode( 'site' ) && ! $force_local_tz ) {
				$event_start_key .= 'UTC';
				$event_end_key   .= 'UTC';
			}

			// if it's a true event query then we want create a join for where conditions
			if ( $query->tribe_is_event || $query->tribe_is_event_category || $query->tribe_is_multi_posttype ) {
				if ( $query->tribe_is_multi_posttype ) {
					// if we're getting multiple post types, we don't need the end date, just get the start date
					// for events-only post type queries, the start date postmeta join is already added by the main query args
					$joins['event_start_date'] = " LEFT JOIN {$wpdb->postmeta} as {$postmeta_table} on {$wpdb->posts}.ID = {$postmeta_table}.post_id AND {$postmeta_table}.meta_key = '$event_start_key'";
				} else {
					// for events-only post type queries, we should also get the end date for display
					$joins['event_end_date'] = " LEFT JOIN {$wpdb->postmeta} as tribe_event_end_date ON ( {$wpdb->posts}.ID = tribe_event_end_date.post_id AND tribe_event_end_date.meta_key = '$event_end_key' ) ";
				}
				$joins = apply_filters( 'tribe_events_query_posts_joins', $joins, $query );

				return $join_sql . implode( '', $joins );
			}

			return $join_sql;
		}

		/**
		 * Custom SQL conditional for event duration meta field
		 *
		 * @param string   $where_sql
		 * @param WP_Query $query
		 *
		 * @return string
		 */
		public static function posts_where( $where_sql, $query ) {
			global $wpdb;

			// if it's a true event query then we to setup where conditions
			// but only if we aren't grabbing a specific post
			if (
				(
					$query->tribe_is_event
					|| $query->tribe_is_event_category
				)
				&& empty( $query->query_vars['name'] )
				&& empty( $query->query_vars['p'] )
			) {

				$postmeta_table = self::postmeta_table( $query );

				$start_date = $query->get( 'start_date' );
				$end_date   = $query->get( 'end_date' );
				$use_utc    = Tribe__Events__Timezones::is_mode( 'site' );
				$site_tz    = $use_utc ? Tribe__Events__Timezones::wp_timezone_string() : null;

				// Sitewide timezone mode: convert the start date - if set - to UTC
				if ( $use_utc && ! empty( $start_date ) ) {
					$start_date = Tribe__Events__Timezones::to_utc( $start_date, $site_tz );
				}

				// Sitewide timezone mode: convert the end date - if set - to UTC
				if ( $use_utc && ! empty( $end_date ) ) {
					$end_date = Tribe__Events__Timezones::to_utc( $end_date, $site_tz );
				}

				// we can't store end date directly because it messes up the distinct clause
				$event_end_date = apply_filters( 'tribe_events_query_end_date_column', 'tribe_event_end_date.meta_value' );

				// event start date
				$event_start_date = "{$postmeta_table}.meta_value";

				// build where conditionals for events if date range params are set
				if ( $start_date != '' && $end_date != '' ) {
					$start_clause  = $wpdb->prepare( "($event_start_date >= %s AND $event_start_date <= %s)", $start_date, $end_date );
					$end_clause    = $wpdb->prepare( "($event_end_date >= %s AND $event_start_date <= %s )", $start_date, $end_date );
					$within_clause = $wpdb->prepare( "($event_start_date < %s AND $event_end_date >= %s )", $start_date, $end_date );
					$where_sql .= " AND ($start_clause OR $end_clause OR $within_clause)";
				} elseif ( 'future' === $query->get( 'eventDisplay' ) && '' !== $start_date ) {
					$start_clause = $wpdb->prepare( "{$postmeta_table}.meta_value >= %s", $start_date );
					$where_sql   .= " AND ($start_clause)";
				} else {
					if ( $start_date != '' ) {
						$start_clause  = $wpdb->prepare( "{$postmeta_table}.meta_value >= %s", $start_date );
						$within_clause = $wpdb->prepare( "({$postmeta_table}.meta_value <= %s AND $event_end_date >= %s )", $start_date, $start_date );
						$where_sql .= " AND ($start_clause OR $within_clause)";
						if ( $query->is_singular() && $query->get( 'eventDate' ) ) {
							$tomorrow        = date( 'Y-m-d', strtotime( $query->get( 'eventDate' ) . ' +1 day' ) );
							$tomorrow_clause = $wpdb->prepare( "{$postmeta_table}.meta_value < %s", $tomorrow );
							$where_sql .= " AND $tomorrow_clause";
						}
					} else {
						if ( $end_date != '' ) {
							$where_sql .= ' AND ' . $wpdb->prepare( "$event_end_date < %s", $end_date );
						}
					}
				}
			}

			remove_filter( 'option_page_on_front', array( __CLASS__, 'default_page_on_front' ) );

			return $where_sql;
		}

		/**
		 * Internal method for properly setting a curated orderby value to $wp_query
		 * Internal method for properly setting a currated orderby value to $wp_query.
		 *
		 * If optional param $default is not provided it will default to 'event_date' - unless a custom
		 * orderby param was specified (via tribe_get_events() for example) - in which case that value
		 * will be used.
		 *
		 * @param string   $default
		 * @param WP_Query $query
		 *
		 * @return string
		 */
		public static function set_orderby( $default = null, $query = null ) {
			// What should $default be?
			if ( null === $default && isset( $query->query['orderby'] ) ) {
				$default = $query->query['orderby'];
			} elseif ( null === $default ) {
				$default = 'event_date';
			}

			$url_param = ! empty( $_GET['orderby'] ) ? $_GET['orderby'] : null;
			$url_param = ! empty( $_GET['tribe-orderby'] ) ? $_GET['tribe-orderby'] : $url_param;
			$url_param = strtolower( $url_param );

			switch ( $url_param ) {
				case 'tribe_sort_ecp_venue_filter':
					$orderby = 'venue';
					break;
				case 'tribe_sort_ecp_organizer_filter':
					$orderby = 'organizer';
					break;
				case 'title':
					$orderby = $url_param;
					break;
				default:
					$orderby = $default;
					break;
			}

			return $orderby;
		}

		/**
		 * Internal method for properly setting a currated order value to $wp_query.
		 *
		 * If optional param $default is not provided it will default to 'ASC' - unless a custom order
		 * was specified (via tribe_get_events() for example) - in which case that value will be used.
		 *
		 * @param string   $default
		 * @param WP_Query $query
		 *
		 * @return string
		 */
		public static function set_order( $default = null, $query = null ) {
			// As of WordPress 4.2 'order' can be set via the 'orderby' as an associative array
			if ( ! empty( $query->query['orderby'] ) && is_array( $query->query['orderby'] ) && ! empty( $query->query['orderby']['meta_value'] ) ) {
				$default = $query->query['orderby']['meta_value'];
			} elseif ( ! empty( $query->query['order'] ) ) {
				$default = $query->query['order'];
			} elseif ( null === $default ) {
				$default = 'ASC';
			}

			$url_param = ! empty( $_GET['order'] ) ? $_GET['order'] : null;
			$url_param = ! empty( $_GET['tribe-order'] ) ? $_GET['tribe-order'] : $url_param;
			$url_param = strtoupper( $url_param );

			$order = in_array( $url_param, array( 'ASC', 'DESC' ) ) ? $url_param : $default;

			return $order;
		}

		/**
		 * Custom SQL order by statement for Event Start Date result order.
		 *
		 * @param string   $order_sql
		 * @param wp_query $query
		 *
		 * @return string
		 */
		public static function posts_orderby( $order_sql, $query ) {
			global $wpdb;

			if ( $query->tribe_is_event || $query->tribe_is_event_category ) {
				$order   = ( isset( $query->query_vars['order'] ) && ! empty( $query->query_vars['order'] ) ) ? $query->query_vars['order'] : $query->get( 'order' );
				$orderby = ( isset( $query->query_vars['orderby'] ) && ! empty( $query->query_vars['orderby'] ) ) ? $query->query_vars['orderby'] : $query->get( 'orderby' );

				if ( self::can_inject_date_field( $query ) && ! $query->tribe_is_multi_posttype ) {
					$old_orderby = $order_sql;
					$order_sql = "EventStartDate {$order}";

					if ( $old_orderby ) {
						$order_sql .= ", {$old_orderby}";
					}
				}

				do_action( 'log', 'orderby', 'default', $orderby );

				switch ( $orderby ) {
					case 'title':
						$order_sql = "{$wpdb->posts}.post_title {$order}, " . $order_sql;
						break;
					case 'menu_order':
						$order_sql = "{$wpdb->posts}.menu_order ASC, " . $order_sql;
						break;
					case 'event_date':
						// we've already setup $order_sql
						break;
					case 'rand':
						$order_sql = 'RAND()';
						break;
				}

				// trim trailing characters
				$order_sql = trim( $order_sql, ", \t\n\r\0\x0B" );
			} else {
				if ( $query->tribe_is_multi_posttype && self::can_inject_date_field( $query ) ) {
					if ( $query->get( 'orderby' ) == 'date' || $query->get( 'orderby' ) == '' ) {
						$order_sql = str_replace( "$wpdb->posts.post_date", 'post_date', $order_sql );
					}
				}
			}

			$order_sql = apply_filters( 'tribe_events_query_posts_orderby', $order_sql, $query );

			return $order_sql;
		}

		/**
		 * Adds a custom SQL join when ordering by venue or organizer is desired.
		 *
		 * @param string   $join_sql
		 * @param wp_query $query
		 *
		 * @return string
		 */
		public static function posts_join_venue_organizer( $join_sql, $query ) {
			// bail if this is not a query for event post type
			if ( $query->get( 'post_type' ) !== Tribe__Events__Main::POSTTYPE ) {
				return $join_sql;
			}

			global $wpdb;

			switch ( $query->get( 'orderby' ) ) {
				case 'venue':
					$join_sql .= " LEFT JOIN {$wpdb->postmeta} tribe_order_by_venue_meta ON {$wpdb->posts}.ID = tribe_order_by_venue_meta.post_id AND tribe_order_by_venue_meta.meta_key='_EventVenueID' LEFT JOIN {$wpdb->posts} tribe_order_by_venue ON tribe_order_by_venue_meta.meta_value = tribe_order_by_venue.ID ";
					break;
				case 'organizer':
					$join_sql .= " LEFT JOIN {$wpdb->postmeta} tribe_order_by_organizer_meta ON {$wpdb->posts}.ID = tribe_order_by_organizer_meta.post_id AND tribe_order_by_organizer_meta.meta_key='_EventOrganizerID' LEFT JOIN {$wpdb->posts} tribe_order_by_organizer ON tribe_order_by_organizer_meta.meta_value = tribe_order_by_organizer.ID ";
					break;
				default:
					return $join_sql;
					break;
			}

			/**
			 * Ensures we add the matching corresponding code to the order clause.
			 *
			 * We use posts_clauses (as opposed to posts_orderby) in this case to avoid
			 * it being overwritten by Tribe__Events__Admin_List methods.
			 *
			 * @see Tribe__Events__Admin_List
			 */
			add_filter( 'posts_clauses', array( __CLASS__, 'posts_orderby_venue_organizer' ), 100, 2 );

			if ( has_filter( 'tribe_events_query_posts_join_orderby' ) ) {
				/**
				 * Historically this filter has only been useful to modify the joins setup
				 * in relation to organizers and venues. In future it may have a more general
				 * application or may be removed.
				 *
				 * @deprecated since 4.0.2
				 *
				 * @var string $join_sql
				 */
				$join_sql = apply_filters( 'tribe_events_query_posts_join_orderby', $join_sql );

				_doing_it_wrong(
					'tribe_events_query_posts_join_orderby (filter)',
					'To modify joins in relation to venues and organizers specifically you are encouraged to use the tribe_events_query_posts_join_venue_organizer hook.',
					'4.0.2'
				);
			}

			/**
			 * Provides an opportunity to modify the join condition added to the query when
			 * ordering by venue or organizer.
			 *
			 * @var string $join_sql
			 */
			return apply_filters( 'tribe_events_query_posts_join_venue_organizer', $join_sql );
		}

		/**
		 * Appends the necessary conditions to the order clause to sort by either venue or
		 * organizer.
		 *
		 * @param  array    $clauses
		 * @param  WP_Query $query
		 * @return string
		 */
		public static function posts_orderby_venue_organizer( array $clauses, $query ) {
			// This filter has to be added for every individual query that requires it
			remove_filter( 'posts_clauses', array( __CLASS__, 'posts_orderby_venue_organizer' ), 100 );

			$order   = ( isset( $query->order ) && ! empty( $query->order ) ) ? $query->order : $query->get( 'order' );
			$orderby = ( isset( $query->orderby ) && ! empty( $query->orderby ) ) ? $query->orderby : $query->get( 'orderby' );

			switch ( $orderby ) {
				case 'venue':
					$clauses['orderby'] = "tribe_order_by_venue.post_title {$order}, " . $clauses['orderby'];
					break;
				case 'organizer':
					$clauses['orderby'] = "tribe_order_by_organizer.post_title {$order}, " . $clauses['orderby'];
					break;
				default:
					return $clauses;
					break;
			}

			// Trim trailing characters
			$clauses['orderby'] = trim( $clauses['orderby'], ", \t\n\r\0\x0B" );

			return $clauses;
		}

		/**
		 * Custom SQL to retrieve post_id list of events marked to be hidden from upcoming lists.
		 *
		 * @return array
		 */
		public static function getHideFromUpcomingEvents() {
			global $wpdb;

			/** @var Tribe__Cache $cache */
			$cache     = tribe( 'cache' );
			$cache_key = 'tribe-hide-from-upcoming-events';
			$found     = $cache->get( $cache_key, 'save_post' );
			if ( is_array( $found ) ) {
				return $found;
			}

			// custom sql to get ids of posts that hide_upcoming_ids
			$hide_upcoming_ids = $wpdb->get_col( "SELECT {$wpdb->postmeta}.post_id FROM {$wpdb->postmeta} WHERE {$wpdb->postmeta}.meta_key = '_EventHideFromUpcoming' AND {$wpdb->postmeta}.meta_value = 'yes'" );
			$hide_upcoming_ids = apply_filters( 'tribe_events_hide_from_upcoming_ids', $hide_upcoming_ids );
			$cache->set( $cache_key, $hide_upcoming_ids, HOUR_IN_SECONDS, 'save_post' );

			return $hide_upcoming_ids;
		}

		/**
		 * Gets the event counts for individual days.
		 *
		 * @param array $args
		 *
		 * @return array The counts array.
		 */
		public static function getEventCounts( $args = array() ) {
			_deprecated_function( __METHOD__, '3.10.1' );
			global $wpdb;
			$date     = date( 'Y-m-d' );
			$defaults = array(
				'post_type'         => Tribe__Events__Main::POSTTYPE,
				'start_date'        => tribe_beginning_of_day( $date ),
				'end_date'          => tribe_end_of_day( $date ),
				'display_type'      => 'daily',
				'hide_upcoming_ids' => null,
			);
			$args     = wp_parse_args( $args, $defaults );

			$args['posts_per_page'] = - 1;
			$args['fields']         = 'ids';

			// remove empty args and sort by key, this increases chance of a cache hit
			$args = array_filter( $args, array( __CLASS__, 'filter_args' ) );
			ksort( $args );

			/** @var Tribe__Cache $cache */
			$cache     = tribe( 'cache' );
			$cache_key = 'daily_counts_and_ids_' . serialize( $args );
			$found     = $cache->get( $cache_key, 'save_post' );
			if ( $found ) {

				return $found;
			}

			$cache_key = 'month_post_ids_' . serialize( $args );
			$found     = $cache->get( $cache_key, 'save_post' );
			if ( $found && is_array( $found ) ) {
				$post_ids = $found;
			} else {
				$post_id_query = new WP_Query();
				$post_ids      = $post_id_query->query( $args );
				$cache->set( $cache_key, $post_ids, Tribe__Cache::NON_PERSISTENT, 'save_post' );
			}

			$counts    = array();
			$event_ids = array();
			if ( ! empty( $post_ids ) ) {
				switch ( $args['display_type'] ) {
					case 'daily':
					default :
						$wp_query = tribe_get_global_query_object();

						$output_date_format = '%Y-%m-%d %H:%i:%s';
						$start_date_sql = esc_sql( $post_id_query->query_vars['start_date'] );
						$end_date_sql = esc_sql( $post_id_query->query_vars['end_date'] );
						$sql_ids = esc_sql( implode( ',', array_map( 'intval', $post_ids ) ) );

						$raw_counts = $wpdb->get_results( "
							SELECT 	tribe_event_start.post_id as ID,
									tribe_event_start.meta_value as EventStartDate,
									DATE_FORMAT( tribe_event_end_date.meta_value, '{$output_date_format}') as EventEndDate,
									{$wpdb->posts}.menu_order as menu_order
							FROM $wpdb->postmeta AS tribe_event_start
									LEFT JOIN $wpdb->posts ON (tribe_event_start.post_id = {$wpdb->posts}.ID)
							LEFT JOIN $wpdb->postmeta as tribe_event_end_date ON ( tribe_event_start.post_id = tribe_event_end_date.post_id AND tribe_event_end_date.meta_key = '_EventEndDate' )
							WHERE tribe_event_start.meta_key = '_EventStartDate'
							AND tribe_event_start.post_id IN ( {$sql_ids} )
							AND (
								(
									tribe_event_start.meta_value >= '{$start_date_sql}'
									AND tribe_event_start.meta_value <= '{$end_date_sql}'
								)
								OR (
									tribe_event_end_date.meta_value >= '{$start_date_sql}'
									AND tribe_event_end_date.meta_value <= '{$end_date_sql}'
								)
								OR (
									tribe_event_start.meta_value < '{$start_date_sql}'
									AND tribe_event_end_date.meta_value > '{$end_date_sql}'
								)
							)
							ORDER BY menu_order ASC, DATE(tribe_event_start.meta_value) ASC, TIME(tribe_event_start.meta_value) ASC;"
						);
						$start_date = new DateTime( $post_id_query->query_vars['start_date'] );
						$end_date   = new DateTime( $post_id_query->query_vars['end_date'] );
						$days       = Tribe__Date_Utils::date_diff( $start_date->format( 'Y-m-d' ), $end_date->format( 'Y-m-d' ) );
						$term_id    = isset( $wp_query->query_vars[ Tribe__Events__Main::TAXONOMY ] ) ? $wp_query->query_vars[ Tribe__Events__Main::TAXONOMY ] : null;
						$terms = array();
						if ( is_int( $term_id ) ) {
							$terms[0] = $term_id;
						} elseif ( is_string( $term_id ) ) {
							$term = get_term_by( 'slug', $term_id, Tribe__Events__Main::TAXONOMY );
							if ( $term ) {
								$terms[0] = $term->term_id;
							}
						}
						if ( ! empty( $terms ) && is_tax( Tribe__Events__Main::TAXONOMY ) ) {
							$terms = array_merge( $terms, get_term_children( $terms[0], Tribe__Events__Main::TAXONOMY ) );
						}
						for ( $i = 0, $date = $start_date; $i <= $days; $i ++, $date->modify( '+1 day' ) ) {
							$formatted_date = $date->format( 'Y-m-d' );
							$count          = 0;
							$_day_event_ids = array();
							foreach ( $raw_counts as $record ) {

								$event = new stdClass;
								$event->EventStartDate = $record->EventStartDate;
								$event->EventEndDate = $record->EventEndDate;

								$per_day_limit = apply_filters( 'tribe_events_month_day_limit', tribe_get_option( 'monthEventAmount', '3' ) );

								if ( tribe_event_is_on_date( $formatted_date, $event ) ) {
									if ( ! empty ( $terms ) && ! has_term( $terms, Tribe__Events__Main::TAXONOMY, $record->ID ) ) {
										continue;
									}
									if ( count( $_day_event_ids ) < $per_day_limit ) {
										$_day_event_ids[] = $record->ID;
									}
									$count ++;
								}
							}
							$event_ids[ $formatted_date ] = $_day_event_ids;
							$counts[ $formatted_date ]    = $count;
						}
						break;
				}

				// get a unique list of the event IDs that will be displayed, and update all their postmeta and term caches at once
				$final_event_ids = call_user_func_array( 'array_merge', $event_ids );
				$final_event_ids = array_unique( $final_event_ids );
				update_object_term_cache( $final_event_ids, Tribe__Events__Main::POSTTYPE );
				update_postmeta_cache( $final_event_ids );
			}

			// return IDs per day and total counts per day
			$return    = array( 'counts' => $counts, 'event_ids' => $event_ids );

			/** @var Tribe__Cache $cache */
			$cache     = tribe( 'cache' );
			$cache_key = 'daily_counts_and_ids_' . serialize( $args );
			$cache->set( $cache_key, $return, Tribe__Cache::NON_PERSISTENT, 'save_post' );

			return $return;
		}

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
		public static function getEvents( $args = array(), $full = false ) {
			$defaults = array(
				'post_type'            => Tribe__Events__Main::POSTTYPE,
				'orderby'              => 'event_date',
				'order'                => 'ASC',
				'posts_per_page'       => tribe_get_option( 'postsPerPage', 10 ),
				'tribe_render_context' => 'default',
			);

			$args = wp_parse_args( $args, $defaults );

			$return_found_posts = ! empty( $args['found_posts'] );

			if ( $return_found_posts ) {
				$args['posts_per_page'] = 1;
				$args['paged']          = 1;
			}

			// remove empty args and sort by key, this increases chance of a cache hit
			$args = array_filter( $args, array( __CLASS__, 'filter_args' ) );
			ksort( $args );

			/** @var Tribe__Cache $cache */
			$cache     = tribe( 'cache' );
			$cache_key = 'get_events_' . get_current_user_id() . serialize( $args );

			$result = $cache->get( $cache_key, 'save_post' );
			if ( $result && $result instanceof WP_Query ) {
				do_action( 'log', 'cache hit', 'tribe-events-cache', $args );
			} else {
				do_action( 'log', 'no cache hit', 'tribe-events-cache', $args );
				$result = new WP_Query( $args );

				if ( $return_found_posts ) {
					$result = $result->found_posts;
				}

				$cache->set( $cache_key, $result, Tribe__Cache::NON_PERSISTENT, 'save_post' );
			}

			if ( $return_found_posts ) {
				return $result;
			}

			if ( ! empty( $result->posts ) ) {
				if ( $full ) {
					return $result;
				} else {
					$posts = $result->posts;

					return $posts;
				}
			} else {
				if ( $full ) {
					return $result;
				} else {
					return array();
				}
			}
		}

		/**
		 * Determine what postmeta table should be used,
		 * to avoid conflicts with previous postmeta joins
		 *
		 * @return string
		 **/
		private static function postmeta_table( $query ) {
			/** @var \wpdb $wpdb */
			global $wpdb;
			$postmeta_table = $wpdb->postmeta;

			if ( ! $query->tribe_is_multi_posttype ) {
				return $postmeta_table;
			}

			$qv = $query->query_vars;

			// check if are any meta queries
			if ( ! empty( $qv['meta_key'] ) ) {
				$postmeta_table = 'tribe_event_postmeta';
			} else {
				if ( isset( $qv['meta_query'] ) ) {
					if (
						( is_array( $qv['meta_query'] ) && ! empty( $qv['meta_query'] ) ) ||
						( $qv['meta_query'] instanceof WP_Meta_Query && ! empty( $qv['meta_query']->queries ) )
					) {
						$postmeta_table = 'tribe_event_postmeta';
					}
				} else {
					$postmeta_table = $wpdb->postmeta;
				}
			}

			return $postmeta_table;
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
		 * This filter is removed when this funtions has finished the execution
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


	}

}
