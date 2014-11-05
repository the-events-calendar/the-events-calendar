<?php
/**
 * Controls the main event query.  Allows for recurring events.
 */

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( ! class_exists( 'TribeEventsQuery' ) ) {
	class TribeEventsQuery {

		/**
		 * Initialize The Events Calendar query filters and post processing.
		 *
		 * @return void
		 */
		public static function init() {

			// if tribe event query add filters
			add_action( 'parse_query', array( __CLASS__, 'parse_query' ), 50 );
			add_action( 'pre_get_posts', array( __CLASS__, 'pre_get_posts' ), 50 );

			if ( is_admin() ) {
				require_once 'tribe-recurring-event-cleanup.php';
				$cleanup = new TribeRecurringEventCleanup();
				$cleanup->toggle_recurring_events();
				unset( $cleanup );
			}
		}

		/**
		 * Set any query flags
		 *
		 * @param WP_Query $query
		 *
		 * @return void
		 **/
		public static function parse_query( $query ) {

			// set paged
			if ( $query->is_main_query() && isset( $_GET['tribe_paged'] ) ) {
				$query->set( 'paged', $_REQUEST['tribe_paged'] );
			}

			// Add tribe events post type to tag queries
			if ( $query->is_tag && (array) $query->get( 'post_type' ) != array( TribeEvents::POSTTYPE ) ) {
				$types = $query->get( 'post_type' );
				if ( empty( $types ) ) {
					$types = array( 'post' );
				}
				if ( is_array( $types ) ) {
					$types[] = TribeEvents::POSTTYPE;
				} else {
					if ( is_string( $types ) ) {
						$types = array( $types, TribeEvents::POSTTYPE );
					} else {
						if ( $types != 'any' ) {
							$types = array( 'post', TribeEvents::POSTTYPE );
						}
					}
				}
				$query->set( 'post_type', $types );
			}

			$types = ( ! empty( $query->query_vars['post_type'] ) ? (array) $query->query_vars['post_type'] : array() );

			// check if any possiblity of this being an event query
			$query->tribe_is_event = ( in_array( TribeEvents::POSTTYPE, $types ) && count( $types ) < 2 )
				? true // it was an event query
				: false;

			$query->tribe_is_multi_posttype = ( in_array( TribeEvents::POSTTYPE, $types ) && count( $types ) >= 2 || in_array( 'any', $types ) )
				? true // it's a query for multiple post types, events post type included
				: false;

			if ( 'default' === $query->get( 'eventDisplay' ) ) {
				$query->set( 'eventDisplay', TribeEvents::instance()->default_view() );
			}

			// check if any possiblity of this being an event category
			$query->tribe_is_event_category = ! empty ( $query->query_vars[ TribeEvents::TAXONOMY ] )
				? true // it was an event category
				: false;

			$query->tribe_is_event_venue = ( in_array( TribeEvents::VENUE_POST_TYPE, $types ) )
				? true // it was an event venue
				: false;

			$query->tribe_is_event_organizer = ( in_array( TribeEvents::ORGANIZER_POST_TYPE, $types ) )
				? true // it was an event organizer
				: false;

			$query->tribe_is_event_query = ( $query->tribe_is_event
			                                 || $query->tribe_is_event_category
			                                 || $query->tribe_is_event_venue
			                                 || $query->tribe_is_event_organizer )
				? true // this is an event query of some type
				: false; // move along, this is not the query you are looking for

			// is the query pulling posts from the past
			if ( $query->is_main_query() && ! empty( $_REQUEST['tribe_event_display'] ) && $_REQUEST['tribe_event_display'] == 'past' ) {
				$query->tribe_is_past = true;
			} elseif ( tribe_is_ajax_view_request() && $query->get( 'eventDisplay' ) == 'past' ) {
				$query->tribe_is_past = true;
			} else {
				$query->tribe_is_past = isset( $query->tribe_is_past ) ? $query->tribe_is_past : false;
			}

			// never allow 404 on month view
			if ( $query->is_main_query() && $query->get( 'eventDisplay' ) == 'month' && ! $query->is_tax && ! $query->tribe_is_event_category ) {
				$query->is_post_type_archive = true;
				$query->queried_object       = get_post_type_object( TribeEvents::POSTTYPE );
				$query->queried_object_id    = 0;
			}

			// check if is_event_query === true and hook filter
			if ( $query->tribe_is_event_query ) {
				// fixing is_home param
				$query->is_home = ! empty( $query->query_vars['is_home'] ) ? $query->query_vars['is_home'] : false;
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

			if ( $query->is_main_query() && is_home() ) {
				// check option for including events in the main wordpress loop, if true, add events post type
				if ( tribe_get_option( 'showEventsInMainLoop', false ) ) {
					$query->query_vars['post_type']   = isset( $query->query_vars['post_type'] ) ? ( array ) $query->query_vars['post_type'] : array( 'post' );
					$query->query_vars['post_type'][] = TribeEvents::POSTTYPE;
					$query->tribe_is_multi_posttype   = true;
				}
			}

			if ( $query->tribe_is_multi_posttype ) {
				do_action( 'log', 'multi_posttype', 'default', $query->tribe_is_multi_posttype );
				add_filter( 'posts_fields', array( __CLASS__, 'multi_type_posts_fields' ), 10, 2 );
				add_filter( 'posts_join', array( __CLASS__, 'posts_join' ), 10, 2 );
				add_filter( 'posts_join', array( __CLASS__, 'posts_join_orderby' ), 10, 2 );
				add_filter( 'posts_distinct', array( __CLASS__, 'posts_distinct' ) );
				add_filter( 'posts_groupby', array( __CLASS__, 'posts_groupby' ), 10, 2 );
				add_filter( 'posts_orderby', array( __CLASS__, 'posts_orderby' ), 10, 2 );
				do_action( 'tribe_events_pre_get_posts', $query );

				return;
			}

			if ( $query->tribe_is_event || $query->tribe_is_event_category ) {

				if ( ! ( $query->is_main_query() && $query->get( 'eventDisplay' ) == 'month' ) ) {
					add_filter( 'posts_fields', array( __CLASS__, 'posts_fields' ), 10, 2 );
					add_filter( 'posts_join', array( __CLASS__, 'posts_join' ), 10, 2 );
					add_filter( 'posts_join', array( __CLASS__, 'posts_join_orderby' ), 10, 2 );
					add_filter( 'posts_where', array( __CLASS__, 'posts_where' ), 10, 2 );
					add_filter( 'posts_distinct', array( __CLASS__, 'posts_distinct' ) );
					add_filter( 'posts_groupby', array( __CLASS__, 'posts_groupby' ), 10, 2 );
				} else {
					// reduce number of queries triggered by main WP_Query on month view
					$query->set( 'posts_per_page', 1 );
					$query->set( 'no_found_rows', true );
					$query->set( 'cache_results', false );
					$query->set( 'update_post_meta_cache', false );
					$query->set( 'update_post_term_cache', false );
					$query->set(
						'meta_query', array(
							array(
								'key'  => '_EventStartDate',
								'type' => 'DATETIME'
							)
						)
					);
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

				$query->query_vars['eventDisplay'] = ! empty( $query->query_vars['eventDisplay'] ) ? $query->query_vars['eventDisplay'] : TribeEvents::instance()->displaying;

				//@todo stop calling EOD cutoff transformations all over the place

				if ( ! empty( $query->query_vars['eventDisplay'] ) ) {
					switch ( $query->query_vars['eventDisplay'] ) {
						case 'custom':
							// if the eventDisplay is 'custom', all we're gonna do is make sure the start and end dates are formatted
							$start_date = $query->get( 'start_date' );
							if ( $start_date ) {
								$query->set( 'start_date', date_i18n( TribeDateUtils::DBDATETIMEFORMAT, strtotime( $start_date ) ) );
							}
							$end_date = $query->get( 'end_date' );
							if ( $end_date ) {
								$query->set( 'end_date', date_i18n( TribeDateUtils::DBDATETIMEFORMAT, strtotime( $end_date ) ) );
							}
							break;
						case 'month':
							// make sure start and end date are set
							if ( $query->get( 'start_date' ) == '' ) {
								$event_date = ( $query->get( 'eventDate' ) != '' )
									? $query->get( 'eventDate' )
									: date_i18n( TribeDateUtils::DBDATETIMEFORMAT );
								$query->set( 'start_date', tribe_event_beginning_of_day( $event_date ) );
							}

							if ( $query->get( 'end_date' == '' ) ) {
								$query->set( 'end_date', tribe_event_end_of_day( $query->get( 'start_date' ) ) );
							}
							$query->set( 'hide_upcoming', true );

							break;
						case 'day':
							$event_date = $query->get( 'eventDate' ) != '' ? $query->get( 'eventDate' ) : Date( 'Y-m-d', current_time( 'timestamp' ) );
							$query->set( 'eventDate', $event_date );
							$beginning_of_day = strtotime( tribe_event_beginning_of_day( $event_date ) ) + 1;
							$query->set( 'start_date', date_i18n( TribeDateUtils::DBDATETIMEFORMAT, $beginning_of_day ) );
							$query->set( 'end_date', tribe_event_end_of_day( $event_date ) );
							$query->set( 'posts_per_page', - 1 ); // show ALL day posts
							$query->set( 'hide_upcoming', false );
							$query->set( 'order', self::set_order( 'ASC', $query ) );
							break;
						case 'all':
							$query->set( 'orderby', self::set_orderby( null, $query ) );
							$query->set( 'order', self::set_order( null, $query ) );
							break;
						case 'single-event':
							if ( $query->get( 'eventDate' ) != '' ) {
								$query->set( 'start_date', $query->get( 'eventDate' ) );
								$query->set( 'eventDate', $query->get( 'eventDate' ) );
							}
							break;
						case 'list':
						default: // default display query
							$event_date = ( $query->get( 'eventDate' ) != '' )
								? $query->get( 'eventDate' )
								: date_i18n( TribeDateUtils::DBDATETIMEFORMAT );
							if ( ! $query->tribe_is_past ) {
								$query->set( 'start_date', tribe_event_beginning_of_day( $event_date ) );
								$query->set( 'end_date', '' );
								$query->set( 'order', self::set_order( 'ASC', $query ) );
							} else {
								// on past view, set the passed date as the end date
								$query->set( 'start_date', '' );
								$query->set( 'end_date', tribe_event_end_of_day( $event_date ) );
								$query->set( 'order', self::set_order( 'DESC', $query ) );
							}
							$query->set( 'orderby', self::set_orderby( null, $query ) );
							$query->set( 'hide_upcoming', true );
							break;
					}
				} else {
					$query->set( 'hide_upcoming', true );
					$query->set( 'start_date', date_i18n( TribeDateUtils::DBDATETIMEFORMAT ) );
					$query->set( 'orderby', self::set_orderby( null, $query ) );
					$query->set( 'order', self::set_order( null, $query ) );
				}

				// eventCat becomes a standard taxonomy query - will need to deprecate and update views eventually
				if ( ! in_array( $query->get( TribeEvents::TAXONOMY ), array( '', '-1' ) ) ) {
					$tax_query[] = array(
						'taxonomy'         => TribeEvents::TAXONOMY,
						'field'            => is_numeric( $query->get( TribeEvents::TAXONOMY ) ) ? 'id' : 'slug',
						'terms'            => $query->get( TribeEvents::TAXONOMY ),
						'include_children' => apply_filters( 'tribe_events_query_include_children', true ),
					);
				}

				// Only add the postmeta hack if it's not the main admin events list
				// Because this method filters out drafts without EventStartDate.
				// For this screen we're doing the JOIN manually in TribeEventsAdminList

				$screen = ! is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ? null : get_current_screen();

				if ( empty( $screen ) || $screen->id != 'edit-tribe_events' ) {
					$meta_query[] = array(
						'key'  => '_EventStartDate',
						'type' => 'DATETIME'
					);
				}
			}

			// filter by Venue ID
			if ( $query->tribe_is_event_query && $query->get( 'venue' ) != '' ) {
				$meta_query[] = array(
					'key'   => '_EventVenueID',
					'value' => $query->get( 'venue' )
				);
			}

			// filter by Organizer ID
			if ( $query->tribe_is_event_query && $query->get( 'organizer' ) != '' ) {
				$meta_query[] = array(
					'key'   => '_EventOrganizerID',
					'value' => $query->get( 'organizer' )
				);
			}

			// enable pagination setup
			if ( $query->tribe_is_event_query && $query->get( 'posts_per_page' ) == '' ) {
				$query->set( 'posts_per_page', (int) tribe_get_option( 'postsPerPage', 10 ) );
			}

			// hide upcoming events from query (only not in admin)
			if ( $query->tribe_is_event_query && $query->get( 'hide_upcoming' ) ) {
				$hide_upcoming_ids = self::getHideFromUpcomingEvents();
				if ( ! empty( $hide_upcoming_ids ) ) {
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

			// if is in the admin remove the event date & upcoming filters, unless is an ajax call
			global $current_screen;
			if ( is_admin() && $query->tribe_is_event_query && ! empty( $current_screen->id ) && $current_screen->id == 'edit-' . TribeEvents::POSTTYPE ) {
				if ( ( ! defined( 'DOING_AJAX' ) ) || ( defined( 'DOING_AJAX' ) && ! ( DOING_AJAX ) ) ) {

					remove_filter( 'posts_where', array( __CLASS__, 'posts_where' ), 10, 2 );
					remove_filter( 'posts_fields', array( __CLASS__, 'posts_fields' ) );
					remove_filter( 'posts_groupby', array( __CLASS__, 'posts_groupby' ) );
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
			}

			if ( $query->tribe_is_event_query ) {
				do_action( 'tribe_events_pre_get_posts', $query );
			}

			return $query;
		}

		/**
		 * Modifies the GROUP BY statement for Tribe Events queries.
		 *
		 * @param string   $groupby_sql The current GROUP BY statement.
		 * @param WP_Query $query       The current query.
		 *
		 * @return string The modified GROUP BY content.
		 * @todo remove in 3.10
		 */
		public static function posts_groupby( $groupby_sql, $query ) {
			if ( ! empty( $query->tribe_is_event_query ) || ! empty( $query->tribe_is_multi_posttype ) ) {
				if ( has_filter( 'tribe_events_query_posts_groupby' ) ) {
					_deprecated_function( "The 'tribe_events_query_posts_groupby' filter", '3.8', " the 'posts_groupby' filter" );

					return apply_filters( 'tribe_events_query_posts_groupby', $groupby_sql, $query );
				}
			}

			return $groupby_sql;
		}

		/**
		 * Adds DISTINCT to the query.
		 *
		 * @param string $distinct The current DISTINCT statement.
		 *
		 * @return string The modified DISTINCT statement.
		 */
		public static function posts_distinct( $distinct ) {
			return "DISTINCT";
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
			if ( ! empty( $query->tribe_is_event ) ) {
				global $wpdb;
				$postmeta_table             = self::postmeta_table( $query );
				$fields                     = array();
				$fields['event_start_date'] = "MIN({$postmeta_table}.meta_value) as EventStartDate";
				$fields['event_end_date']   = "MIN(tribe_event_end_date.meta_value) as EventEndDate";
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
			if ( ! empty( $query->tribe_is_multi_posttype ) ) {
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

			// if it's a true event query then we want create a join for where conditions
			if ( $query->tribe_is_event || $query->tribe_is_event_category || $query->tribe_is_multi_posttype ) {
				if ( $query->tribe_is_multi_posttype ) {
					// if we're getting multiple post types, we don't need the end date, just get the start date
					// for events-only post type queries, the start date postmeta join is already added by the main query args
					$joins['event_start_date'] = " LEFT JOIN {$wpdb->postmeta} as {$postmeta_table} on {$wpdb->posts}.ID = {$postmeta_table}.post_id AND {$postmeta_table}.meta_key = '_EventStartDate'";
				} else {
					// for events-only post type queries, we should also get the end date for display
					$joins['event_end_date'] = " LEFT JOIN {$wpdb->postmeta} as tribe_event_end_date ON ( {$wpdb->posts}.ID = tribe_event_end_date.post_id AND tribe_event_end_date.meta_key = '_EventEndDate' ) ";
				}
				$joins = apply_filters( 'tribe_events_query_posts_joins', $joins, $query );

				return $join_sql . implode( '', $joins );
			}

			return $join_sql;
		}

		/**
		 * Custom SQL join for orderby
		 *
		 * @param string   $join_sql
		 * @param wp_query $query
		 *
		 * @return string
		 */
		public static function posts_join_orderby( $join_sql, $query ) {
			global $wpdb;
			switch ( $query->get( 'orderby' ) ) {
				case 'venue':
					$join_sql .= " LEFT JOIN {$wpdb->postmeta} tribe_order_by_venue_meta ON {$wpdb->posts}.ID = tribe_order_by_venue_meta.post_id AND tribe_order_by_venue_meta.meta_key='_EventVenueID' LEFT JOIN {$wpdb->posts} tribe_order_by_venue ON tribe_order_by_venue_meta.meta_value = tribe_order_by_venue.ID ";
					break;
				case 'organizer':
					$join_sql .= " LEFT JOIN {$wpdb->postmeta} tribe_order_by_organizer_meta ON {$wpdb->posts}.ID = tribe_order_by_organizer_meta.post_id AND tribe_order_by_organizer_meta.meta_key='_EventOrganizerID' LEFT JOIN {$wpdb->posts} tribe_order_by_organizer ON tribe_order_by_organizer_meta.meta_value = tribe_order_by_organizer.ID ";
					break;
				default:
					break;
			}

			return apply_filters( 'tribe_events_query_posts_join_orderby', $join_sql );
		}

		/**
		 * Custom SQL conditional for event duration meta field
		 *
		 * @param string   $where_sql
		 * @param wp_query $query
		 *
		 * @return string
		 */
		public static function posts_where( $where_sql, $query ) {
			global $wpdb;

			// if it's a true event query then we to setup where conditions
			if ( $query->tribe_is_event || $query->tribe_is_event_category ) {

				$postmeta_table = self::postmeta_table( $query );

				$start_date = $query->get( 'start_date' );
				$end_date   = $query->get( 'end_date' );

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
							$where_sql .= " AND " . $wpdb->prepare( "$event_end_date < %s", $end_date );
						}
					}
				}
			}

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
			// What should $default be?
			if ( null === $default && isset( $query->query['order'] ) ) {
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
			$postmeta_table = self::postmeta_table( $query );

			if ( $query->tribe_is_event || $query->tribe_is_event_category ) {
				$order   = ( isset( $query->order ) && ! empty( $query->order ) ) ? $query->order : $query->get( 'order' );
				$orderby = ( isset( $query->orderby ) && ! empty( $query->orderby ) ) ? $query->orderby : $query->get( 'orderby' );

				$order_sql = "DATE(MIN({$postmeta_table}.meta_value)) {$order}, TIME({$postmeta_table}.meta_value) {$order}";

				do_action( 'log', 'orderby', 'default', $orderby );

				switch ( $orderby ) {
					case 'venue':
						$order_sql = "tribe_order_by_venue.post_title {$order}, " . $order_sql;
						break;
					case 'organizer':
						$order_sql = "tribe_order_by_organizer.post_title {$order}, " . $order_sql;
						break;
					case 'title':
						$order_sql = "{$wpdb->posts}.post_title {$order}, " . $order_sql;
						break;
					case 'menu_order':
						$order_sql = "{$wpdb->posts}.menu_order ASC, " . $order_sql;
						break;
					case 'event_date':
						// we've already setup $order_sql
						break;
				}
			} else {
				if ( $query->tribe_is_multi_posttype ) {
					if ( $query->get( 'orderby' ) == 'date' || $query->get( 'orderby' ) == '' ) {
						$order_sql = str_replace( "$wpdb->posts.post_date", 'post_date', $order_sql );
					}
				}
			}

			$order_sql = apply_filters( 'tribe_events_query_posts_orderby', $order_sql, $query );

			return $order_sql;
		}

		/**
		 * Custom SQL to retrieve post_id list of events marked to be hidden from upcoming lists.
		 *
		 * @return array
		 */
		public static function getHideFromUpcomingEvents() {
			global $wpdb;

			$cache     = new TribeEventsCache();
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
			global $wpdb;
			do_action( 'log', 'getEventCounts() $args', 'tribe-events-query', $args );
			$date     = date( 'Y-m-d' );
			$defaults = array(
				'post_type'         => TribeEvents::POSTTYPE,
				'start_date'        => tribe_event_beginning_of_day( $date ),
				'end_date'          => tribe_event_end_of_day( $date ),
				'display_type'      => 'daily',
				'hide_upcoming_ids' => null,
			);
			$args     = wp_parse_args( $args, $defaults );

			$args['posts_per_page'] = - 1;
			$args['fields']         = 'ids';

			// remove empty args and sort by key, this increases chance of a cache hit
			$args = array_filter( $args, array( __CLASS__, 'filter_args' ) );
			ksort( $args );

			$cache     = new TribeEventsCache();
			$cache_key = 'daily_counts_and_ids_' . serialize( $args );
			$found     = $cache->get( $cache_key, 'save_post' );
			if ( $found ) {
				do_action( 'log', 'cache hit ' . __LINE__, 'tribe-events-cache', $args );

				return $found;
			}
			do_action( 'log', 'no cache hit ' . __LINE__, 'tribe-events-cache', $args );

			$cache_key = 'month_post_ids_' . serialize( $args );
			$found     = $cache->get( $cache_key, 'save_post' );
			if ( $found && is_array( $found ) ) {
				do_action( 'log', 'cache hit ' . __LINE__, 'tribe-events-cache', $args );
				$post_ids = $found;
			} else {
				do_action( 'log', 'no cache hit ' . __LINE__, 'tribe-events-cache', $args );
				$post_id_query = new WP_Query();
				$post_ids      = $post_id_query->query( $args );
				do_action( 'log', 'final args for month view post ids', 'tribe-events-query', $post_id_query->query_vars );
				do_action( 'log', 'Month view getEventCounts SQL', 'tribe-events-query', $post_id_query->request );
				$cache->set( $cache_key, $post_ids, TribeEventsCache::NON_PERSISTENT, 'save_post' );
			}

			do_action( 'log', 'Month view post ids found', 'tribe-events-query', $post_ids );
			$counts    = array();
			$event_ids = array();
			if ( ! empty( $post_ids ) ) {
				switch ( $args['display_type'] ) {
					case 'daily':
					default :
						global $wp_query;
						$output_date_format = '%Y-%m-%d %H:%i:%s';
						do_action( 'log', 'raw counts args', 'tribe-events-query', $args );
						$raw_counts = $wpdb->get_results(
							$wpdb->prepare(
								"
							SELECT 	tribe_event_start.post_id as ID, 
									tribe_event_start.meta_value as EventStartDate, 
									DATE_FORMAT( tribe_event_end_date.meta_value, '%1\$s') as EventEndDate,
									{$wpdb->posts}.menu_order as menu_order
							FROM $wpdb->postmeta AS tribe_event_start
									LEFT JOIN $wpdb->posts ON (tribe_event_start.post_id = {$wpdb->posts}.ID)
							LEFT JOIN $wpdb->postmeta as tribe_event_end_date ON ( tribe_event_start.post_id = tribe_event_end_date.post_id AND tribe_event_end_date.meta_key = '_EventEndDate' )
							WHERE tribe_event_start.meta_key = '_EventStartDate'
							AND tribe_event_start.post_id IN ( %5\$s )
							AND ( (tribe_event_start.meta_value >= '%3\$s' AND  tribe_event_start.meta_value <= '%4\$s')
								OR (tribe_event_start.meta_value <= '%3\$s' AND tribe_event_end_date.meta_value >= '%3\$s')
								OR ( tribe_event_start.meta_value >= '%3\$s' AND  tribe_event_start.meta_value <= '%4\$s')
							)
							ORDER BY menu_order ASC, DATE(tribe_event_start.meta_value) ASC, TIME(tribe_event_start.meta_value) ASC;",
								$output_date_format,
								$output_date_format,
								$post_id_query->query_vars['start_date'],
								$post_id_query->query_vars['end_date'],
								implode( ',', array_map( 'intval', $post_ids ) )
							)
						);
						do_action( 'log', 'raw counts query', 'tribe-events-query', $wpdb->last_query );
						$start_date = new DateTime( $post_id_query->query_vars['start_date'] );
						$end_date   = new DateTime( $post_id_query->query_vars['end_date'] );
						$days       = TribeDateUtils::dateDiff( $start_date->format( 'Y-m-d' ), $end_date->format( 'Y-m-d' ) );
						$term_id    = isset( $wp_query->query_vars[ TribeEvents::TAXONOMY ] ) ? $wp_query->query_vars[ TribeEvents::TAXONOMY ] : null;
						$terms = array();
						if ( is_int( $term_id ) ) {
							$terms[0] = $term_id;
						} elseif ( is_string( $term_id ) ) {
							$term = get_term_by( 'slug', $term_id, TribeEvents::TAXONOMY );
							if ( ! is_wp_error( $term ) ) {
								$terms[0] = $term->term_id;
							}
						}
						if ( ! empty( $terms ) && is_tax( TribeEvents::TAXONOMY ) ) {
							$terms = array_merge( $terms, get_term_children( $terms[0], TribeEvents::TAXONOMY ) );
						}
						for ( $i = 0, $date = $start_date; $i <= $days; $i ++, $date->modify( '+1 day' ) ) {
							$formatted_date = $date->format( 'Y-m-d' );
							$start_of_day   = strtotime( tribe_event_beginning_of_day( $formatted_date ) );
							$end_of_day     = strtotime( tribe_event_end_of_day( $formatted_date ) ) + 1;
							$count          = 0;
							$_day_event_ids = array();
							foreach ( $raw_counts as $record ) {
								$record_start = strtotime( $record->EventStartDate );
								$record_end   = strtotime( $record->EventEndDate );

								/**
								 * conditions:
								 * event starts on this day (event start time is between start and end of day)
								 * event ends on this day (event end time is between start and end of day)
								 * event starts before start of day and ends after end of day (spans across this day)
								 * note:
								 * events that start exactly on the EOD cutoff will count on the following day
								 * events that end exactly on the EOD cutoff will count on the previous day

								 */

								$event_starts_today       = $record_start >= $start_of_day && $record_start < $end_of_day;
								$event_ends_today         = $record_end > $start_of_day && $record_end <= $end_of_day;
								$event_spans_across_today = $record_start < $start_of_day && $record_end > $end_of_day;

								if ( $event_starts_today || $event_ends_today || $event_spans_across_today ) {
									if ( ! empty ( $terms ) ) {
										if ( ! has_term( $terms, TribeEvents::TAXONOMY, $record->ID ) ) {
											continue;
										}
									}
									if ( count( $_day_event_ids ) < apply_filters( 'tribe_events_month_day_limit', tribe_get_option( 'monthEventAmount', '3' ) ) ) {
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
				$final_event_ids = array();
				$final_event_ids = call_user_func_array( 'array_merge', $event_ids );
				$final_event_ids = array_unique( $final_event_ids );
				do_action( 'log', 'updating term and postmeta caches for events', 'tribe-events-cache', $final_event_ids );
				update_object_term_cache( $final_event_ids, TribeEvents::POSTTYPE );
				update_postmeta_cache( $final_event_ids );
			}
			// return IDs per day and total counts per day
			$return    = array( 'counts' => $counts, 'event_ids' => $event_ids );
			$cache     = new TribeEventsCache;
			$cache_key = 'daily_counts_and_ids_' . serialize( $args );
			$cache->set( $cache_key, $return, TribeEventsCache::NON_PERSISTENT, 'save_post' );
			do_action( 'log', 'final event counts result', 'tribe-events-query', $return );

			return $return;
		}

		/**
		 * Customized WP_Query wrapper to setup event queries with default arguments.
		 *
		 * @param array $args
		 * @param bool  $full
		 *
		 * @return array|WP_Query
		 */
		public static function getEvents( $args = array(), $full = false ) {
			$defaults = array(
				'post_type'      => TribeEvents::POSTTYPE,
				'orderby'        => 'event_date',
				'order'          => 'ASC',
				'posts_per_page' => tribe_get_option( 'postsPerPage', 10 ),
			);
			$args     = wp_parse_args( $args, $defaults );

			// remove empty args and sort by key, this increases chance of a cache hit
			$args = array_filter( $args, array( __CLASS__, 'filter_args' ) );
			ksort( $args );

			$cache     = new TribeEventsCache();
			$cache_key = 'get_events_' . serialize( $args );

			$result = $cache->get( $cache_key, 'save_post' );
			if ( $result && is_a( $result, 'WP_Query' ) ) {
				do_action( 'log', 'cache hit', 'tribe-events-cache', $args );
			} else {
				do_action( 'log', 'no cache hit', 'tribe-events-cache', $args );
				$result = new WP_Query( $args );
				$cache->set( $cache_key, $result, TribeEventsCache::NON_PERSISTENT, 'save_post' );
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

			global $wpdb;

			if ( ! $query->tribe_is_multi_posttype ) {
				return $wpdb->postmeta;
			}


			$qv = $query->query_vars;

			// check if are any meta queries
			if ( ! empty( $qv['meta_key'] ) ) {
				$postmeta_table = 'tribe_event_postmeta';
			} else {
				if ( isset( $qv['meta_query'] ) ) {
					if (
						( is_array( $qv['meta_query'] ) && ! empty( $qv['meta_query'] ) ) ||
						( is_a( $qv['meta_query'], 'WP_Meta_Query' ) && ! empty( $qv['meta_query']->queries ) )
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
			if ( empty( $arg ) && $arg !== false ) {
				return false;
			}

			return true;
		}

	}
}
