<?php
/**
 * Controls the main event query.  Allows for recurring events.
 */

// Don't load directly
if ( !defined( 'ABSPATH' ) ) { die( '-1' ); }

if ( !class_exists( 'TribeEventsQuery' ) ) {
	class TribeEventsQuery {

		public static $start_date;
		public static $end_date;
		public static $is_event;
		public static $is_event_category;
		public static $is_event_venue;
		public static $is_event_organizer;
		public static $is_event_query;

        /**
         *  Class Constructor
         *
         * @return void
         */
        function __construct() {
			add_action( 'tribe_events_init_pre_get_posts', array( __CLASS__, 'init' ) );
		}

		/**
		 * Initialize The Events Calendar query filters and post processing.
		 *
		 * @return void
		 */
		public static function init() {

			// if tribe event query add filters
			add_filter( 'pre_get_posts', array( __CLASS__, 'pre_get_posts' ), 0 );
			add_filter( 'parse_query', array( __CLASS__, 'parse_query') );

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
		 * @return $query WP_Query
		 * @author Jessica Yazbek
		 * @since 3.0.3
		 **/
		public function parse_query( $query ) {
			if ($query->get('eventDisplay') == 'month') {
				// never allow 404 on month view
				$query->is_post_type_archive = true;
			}
			return $query;
		}

		/**
		 * Is hooked by init() filter to parse the WP_Query arguments for main and alt queries.
		 *
		 * @param object  $query WP_Query object args supplied or default
		 * @return object $query (modified)
		 */
		public function pre_get_posts( $query ) {

			global $wp_the_query;

			$types = ( !empty( $query->query_vars['post_type'] ) ? (array) $query->query_vars['post_type'] : array() );

			// is the query pulling posts from the past
			$query->tribe_is_past = !empty( $query->query_vars['tribe_is_past'] ) ? $query->query_vars['tribe_is_past'] : false ;

			// check if any possiblity of this being an event query
			$query->tribe_is_event = ( in_array( TribeEvents::POSTTYPE, $types ) && count( $types ) < 2 )
				? true // it was an event query
			: false;

			// check if any possiblity of this being an event category
			$query->tribe_is_event_category = ( isset( $query->query_vars[TribeEvents::TAXONOMY] ) && $query->query_vars[TribeEvents::TAXONOMY] != '' )
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

			// setup static const to preserve query type through hooks
			self::$is_event = $query->tribe_is_event;
			self::$is_event_category = $query->tribe_is_event_category;
			self::$is_event_venue = $query->tribe_is_event_venue;
			self::$is_event_organizer = $query->tribe_is_event_organizer;
			self::$is_event_query = $query->tribe_is_event_query;

			if ( $query === $wp_the_query && $query->is_main_query() && tribe_get_option( 'showEventsInMainLoop', false ) && !is_page() && !is_admin() && !is_single() && !is_singular() && ( ( is_home() && !$query->tribe_is_event_query ) || is_archive() || is_category() || is_tax() ) ) {
				$query->query_vars['post_type'] = isset( $query->query_vars['post_type'] ) ? (array) $query->query_vars['post_type'] : array( 'post' );
				$query->query_vars['post_type'][] = TribeEvents::POSTTYPE;
			}

			if ( $query->tribe_is_event || $query->tribe_is_event_category ) {

				self::$start_date = null;
				self::$end_date = null;

				add_filter( 'posts_join', array( __CLASS__, 'posts_join' ), 10, 2 );
				add_filter( 'posts_join', array( __CLASS__, 'posts_join_orderby' ), 10, 2 );
				add_filter( 'posts_where', array( __CLASS__, 'posts_where' ), 10, 2 );
				add_filter( 'posts_fields', array( __CLASS__, 'posts_fields' ), 10, 2 );
				add_filter( 'posts_distinct', array( __CLASS__, 'posts_distinct' ) );
				add_filter( 'posts_groupby', array( __CLASS__, 'posts_groupby' ), 10, 2 );

				// if a user selects a date in the event bar we want it to persist as long as possible
				if ( !empty( $_REQUEST['tribe-bar-date'] ) ) {
					$query->set( 'eventDate', $_REQUEST['tribe-bar-date'] );
				}

				// if a user provides a search term we want to use that in the search params
				if ( !empty( $_REQUEST['tribe-bar-search'] ) ) {
					$query->query_vars['s'] = $_REQUEST['tribe-bar-search'];
				}

				$query->query_vars['eventDisplay'] = !empty( $query->query_vars['eventDisplay'] ) ? $query->query_vars['eventDisplay'] : TribeEvents::instance()->displaying;

				if ( !empty( $query->query_vars['eventDisplay'] ) ) {
					switch ( $query->query_vars['eventDisplay'] ) {
					case 'custom':
							// if set this allows for a custom query to not be burdened with these settings
						break;
					case 'past': // setup past event display query
						$query->set( 'end_date', date_i18n( TribeDateUtils::DBDATETIMEFORMAT ) );
						$query->set( 'orderby', self::set_orderby() );
						$query->set( 'order', self::set_order( 'DESC' ) );
						self::$end_date = $query->get( 'end_date' );
						$query->tribe_is_past = true;
						break;
					case 'all':
						$query->set( 'orderby', self::set_orderby() );
						$query->set( 'order', self::set_order() );
						break;
					case 'month':
						$start_date = substr_replace( date_i18n( TribeDateUtils::DBDATEFORMAT ), '01', -2 );
						$passed_date = $query->get( 'eventDate' ) ? substr_replace( date_i18n( TribeDateUtils::DBDATEFORMAT, strtotime( $query->get( 'eventDate' ) ) ), '01', -2 ) : false;
						$start_date = $passed_date ? $passed_date : $start_date;
						$query->set( 'start_date', $start_date );
						$query->set( 'eventDate', $start_date );
						$query->set( 'end_date', date( 'Y-m-d', strtotime( TribeEvents::instance()->nextMonth( $start_date ) ) -( 24*3600 ) ) );
						if ( $query->is_main_query() ) {
							$query->set( 'posts_per_page', 1 ); // we're going to do this day-by-day later, so limit or order necessary for this
							$query->set( 'no_found_rows', TRUE );
						} else {
							$query->set( 'orderby', self::set_orderby() );
							$query->set( 'order', self::set_order() );
							$query->set( 'posts_per_page', -1 ); // get all events for the month
						}
						self::$start_date = $query->get( 'start_date' );
						self::$end_date = $query->get( 'end_date' );
						break;
					case 'single-event':
						if ( $query->get( 'eventDate' ) != '' ) {
							$query->set( 'start_date', $query->get( 'eventDate' ) );
							$query->set( 'eventDate', $query->get( 'eventDate' ) );
							self::$start_date = $query->get( 'start_date' );
						}
						break;
					case 'upcoming':
					default: // default display query
						$start_date = date_i18n( TribeDateUtils::DBDATETIMEFORMAT );
						$start_date = ( $query->get( 'eventDate' ) != '' ) ? $query->get( 'eventDate' ) : $start_date;
						$query->set( 'hide_upcoming', true );
						$query->set( 'start_date', $start_date );
						$query->set( 'orderby', self::set_orderby() );
						$query->set( 'order', self::set_order() );
						self::$start_date = $query->get( 'start_date' );
						break;
					}
				} else {
					$query->set( 'hide_upcoming', true );
					$query->set( 'start_date', date_i18n( TribeDateUtils::DBDATETIMEFORMAT ) );
					$query->set( 'orderby', self::set_orderby() );
					$query->set( 'order', self::set_order() );
					self::$start_date = $query->get( 'start_date' );
				}
				// eventCat becomes a standard taxonomy query - will need to deprecate and update views eventually
				if ( ! in_array( $query->get( TribeEvents::TAXONOMY ), array( '', '-1' ) ) ) {
					$tax_query[] = array(
						'taxonomy' => TribeEvents::TAXONOMY,
						'field' => is_numeric( $query->get( TribeEvents::TAXONOMY ) ) ? 'id' : 'slug',
						'terms' => $query->get( TribeEvents::TAXONOMY ),
						'include_children' => false,
					);
				}

				$meta_query[] = array(
					'key' => '_EventStartDate',
					'type' => 'DATETIME'
				);

			}

			// filter by Venue ID
			if ( $query->tribe_is_event_query && $query->get( 'venue' ) != '' ) {
				$meta_query[] = array(
					'key' => '_EventVenueID',
					'value' => $query->get( 'venue' )
				);
			}

			// filter by Organizer ID
			if ( $query->tribe_is_event_query && $query->get( 'organizer' ) != '' ) {
				$meta_query[] = array(
					'key' => '_EventOrganizerID',
					'value' => $query->get( 'organizer' )
				);
			}

			// proprietary metaKeys go to standard meta
			if ( $query->tribe_is_event_query && $query->get( 'metaKey' ) != '' ) {
				$meta_query[] = array(
					'key' => $query->get( 'metaKey' ),
					'value' => $query->get( 'metaValue' )
				);
			}

			// enable pagination setup
			if ( $query->tribe_is_event_query && $query->get( 'numResults' ) != '' ) {
				$query->set( 'posts_per_page', $query->get( 'numResults' ) );
			} elseif ( $query->tribe_is_event_query && $query->get( 'posts_per_page' ) == '' ) {
				$query->set( 'posts_per_page', (int) tribe_get_option( 'postsPerPage', 10 ) );
			}

			// hide upcoming events from query (only not in admin)
			if ( $query->tribe_is_event_query && $query->get( 'hide_upcoming' ) ) {
				$hide_upcoming_ids = self::getHideFromUpcomingEvents();
				if ( !empty( $hide_upcoming_ids ) )
					$query->set( 'post__not_in', $hide_upcoming_ids );
			}

			if ( $query->tribe_is_event_query && !empty( $meta_query ) ) {
				// setup default relation for meta queries
				$meta_query['relation'] = 'AND';
				$meta_query_combined = array_merge( (array) $meta_query, (array) $query->get( 'meta_query' ) );
				$query->set( 'meta_query', $meta_query_combined );
			}

			if ( $query->tribe_is_event_query && !empty( $tax_query ) ) {
				// setup default relation for tax queries
				$tax_query_combined = array_merge( (array) $tax_query, (array) $query->get( 'tax_query' ) );
				$query->set( 'tax_query', $tax_query_combined );
			}

			if ( $query->tribe_is_event_query ) {
				add_filter( 'posts_orderby', array( __CLASS__, 'posts_orderby' ), 10, 2 );
			}

			// if is in the admin remove the event date & upcoming filters, unless is an ajax call
			global $current_screen;
			if ( is_admin() && $query->tribe_is_event_query && !empty( $current_screen->id ) && $current_screen->id == 'edit-' . TribeEvents::POSTTYPE ) {
				if ( ( !defined( 'DOING_AJAX' ) ) || ( defined( 'DOING_AJAX' ) && !( DOING_AJAX ) ) ) {

					// remove_filter( 'posts_join', array( __CLASS__, 'posts_join' ), 10, 2 );
					remove_filter( 'posts_where', array( __CLASS__, 'posts_where' ), 10, 2 );
					remove_filter( 'posts_fields', array( __CLASS__, 'posts_fields' ) );
					remove_filter( 'posts_distinct', array( __CLASS__, 'posts_distinct' ) );
					remove_filter( 'posts_groupby', array( __CLASS__, 'posts_groupby' ) );
					$query->set( 'post__not_in', '' );

					// set the default order for posts within admin lists
					if ( !isset( $query->query['order'] ) ) {
						$query->set( 'order', 'DESC' );
					} else {
						// making sure we preserve the order supplied by the query string even if it is overwritten above
						$query->set( 'order', $query->query['order'] );
					}
				}
			}

			// check if is_event_query === true and hook filter
			if ( $query->tribe_is_event_query ) {
				// fixing is_home param
				$query->is_home = !empty( $query->query_vars['is_home'] ) ? $query->query_vars['is_home'] : false;
				apply_filters( 'tribe_events_pre_get_posts', $query );
			}

			return $query;
		}

        /**
         * Modifies the GROUP BY statement for Tribe Events queries.
         *
         * @param string $groupby_sql The current GROUP BY statement.
         * @param WP_Query $query The current query.
         * @return string The modified GROUP BY content.
         */
        public static function posts_groupby( $groupby_sql, $query ) {
			global $wpdb;
			if ( self::$is_event_query ) {
				return apply_filters( 'tribe_events_query_posts_groupby', '', $query );
			} else {
				return $groupby_sql;
			}
		}

        /**
         * Adds DISTINCT to the query.
         *
         * @param string $distinct The current DISTINCT statement.
         * @return string The modified DISTINCT statement.
         */
        public static function posts_distinct( $distinct ) {
			return "DISTINCT";
		}

        /**
         * Adds the proper fields to the FIELDS statement in the query.
         *
         * @param string $fields The current/original FIELDS statement.
         * @param WP_Query $query The current query object.
         * @return string The modified FIELDS statement.
         */
        public static function posts_fields( $field_sql, $query ) {
			if ( self::$is_event ) {
				global $wpdb;
				$fields = array();
				$fields['event_start_date'] = "{$wpdb->postmeta}.meta_value as EventStartDate";
				$fields['event_end_date'] ="tribe_event_end_date.meta_value as EventEndDate";
				$fields = apply_filters( 'tribe_events_query_posts_fields', $fields );
				return $field_sql . ', '.implode(', ', $fields);
			} else {
				return $field_sql;
			}
		}

		/**
		 * Custom SQL join for event end date
		 *
		 * @param string  $join_sql
		 * @param wp_query $query
		 * @return string
		 */
		public static function posts_join( $join_sql, $query ) {
			global $wpdb;
			$joins = array();

			// if it's a true event query then we want create a join for where conditions
			if ( $query->tribe_is_event || $query->tribe_is_event_category ) {
				$joins['event_start_date'] = " AND {$wpdb->postmeta}.meta_key = '_EventStartDate'";
				$joins['event_end_date'] = " LEFT JOIN {$wpdb->postmeta} as tribe_event_end_date ON ( {$wpdb->posts}.ID = tribe_event_end_date.post_id AND tribe_event_end_date.meta_key = '_EventEndDate' ) ";
				$joins = apply_filters( 'tribe_events_query_posts_joins', $joins );
				return $join_sql . implode('', $joins);
			}
			return $join_sql;
		}

		/**
		 * Custom SQL join for orderby
		 *
		 * @param string  $join_sql
		 * @param wp_query $query
		 * @return string
		 */
		public static function posts_join_orderby( $join_sql, $query ) {
			switch ($query->get( 'orderby' )) {
				case 'venue':
					$join_sql .= " LEFT JOIN {$wpdb->postmeta} tribe_order_by_venue_meta ON {$wpdb->posts}.ID = tribe_order_by_venue_meta.post_id AND tribe_order_by_venue_meta.meta_key='_EventVenueID' LEFT JOIN {$wpdb->posts} tribe_order_by_venue ON tribe_order_by_venue_meta.meta_value = tribe_order_by_venue.ID ";
					break;
				case 'organizer':
					$join_sql .= " LEFT JOIN {$wpdb->postmeta} tribe_order_by_organizer_meta ON {$wpdb->posts}.ID = tribe_order_by_organizer_meta.post_id AND tribe_order_by_organizer_meta.meta_key='_EventOrganizerID' LEFT JOIN {$wpdb->posts} tribe_order_by_organizer ON tribe_order_by_organizer_meta.meta_value = tribe_order_by_organizer.ID ";
					break;
				default: break;
			}

			return apply_filters( 'tribe_events_query_posts_join_orderby', $join_sql);
		}

		/**
		 * Custom SQL conditional for event duration meta field
		 *
		 * @param string  $where_sql
		 * @param wp_query $query
		 * @return string
		 */
		public static function posts_where( $where_sql, $query ) {
			global $wpdb;

			// if it's a true event query then we to setup where conditions
			if ( $query->tribe_is_event || $query->tribe_is_event_category ) {

				$start_date = !empty( $query->start_date ) ? $query->start_date : $query->get( 'start_date' );
				$end_date = !empty( $query->end_date ) ? $query->end_date : $query->get( 'end_date' );

				// we can't store end date directly because it messes up the distinc clause
				$event_end_date = apply_filters('tribe_events_query_end_date_column', 'tribe_event_end_date.meta_value');

				// event start date
				$event_start_date = "{$wpdb->postmeta}.meta_value";

				// build where conditionals for events if date range params are set
				if ( $start_date != '' && $end_date != '' ) {
					$start_clause = $wpdb->prepare( "($event_start_date >= %s AND $event_start_date <= %s)", $start_date, $end_date );
					$end_clause = $wpdb->prepare( "($event_end_date >= %s AND $event_start_date <= %s )", $start_date, $end_date );
					$within_clause = $wpdb->prepare( "($event_start_date < %s AND $event_end_date >= %s )", $start_date, $end_date );
					$where_sql .= " AND ($start_clause OR $end_clause OR $within_clause)";
				} else if ( $start_date != '' ) {
					$start_clause = $wpdb->prepare( "{$wpdb->postmeta}.meta_value >= %s", $start_date );
					$within_clause = $wpdb->prepare( "({$wpdb->postmeta}.meta_value <= %s AND $event_end_date >= %s )", $start_date, $start_date );
					$where_sql .= " AND ($start_clause OR $within_clause)";
					if ( $query->is_singular() && $query->get( 'eventDate' ) ) {
						$tomorrow = date( 'Y-m-d', strtotime( $query->get( 'eventDate' ).' +1 day' ) );
						$tomorrow_clause = $wpdb->prepare( "{$wpdb->postmeta}.meta_value < %s", $tomorrow );
						$where_sql .= " AND $tomorrow_clause";
					}
				} else if ( $end_date != '' ) {
					$where_sql .= " AND " . $wpdb->prepare( "$event_end_date < %s", $end_date );
				}
			}

			return $where_sql;
		}

		/**
		 * Internal method for properly setting a currated orderby value to $wp_query
		 * @param string $default
		 * @return string
		 */
		function set_orderby( $default = 'event_date' ) {
			$url_param = !empty( $_GET['orderby'] ) ? $_GET['orderby'] : null;
			$url_param = !empty( $_GET['tribe-orderby'] ) ? $_GET['tribe-orderby'] : $url_param;
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
		 * Internal method for properly setting a currated order value to $wp_query
		 * @param string $default
		 * @return string
		 */
		function set_order( $default = 'ASC' ) {
			$url_param = !empty( $_GET['order'] ) ? $_GET['order'] : null;
			$url_param = !empty( $_GET['tribe-order'] ) ? $_GET['tribe-order'] : $url_param;
			$url_param = strtoupper( $url_param );
			$order = in_array( $url_param, array( 'ASC', 'DESC' ) ) ? $url_param : $default;
			return $order;
		}

		/**
		 * Custom SQL order by statement for Event Start Date result order.
		 *
		 * @param string  $order_sql
		 * @param wp_query $query
		 * @return string
		 */
		public static function posts_orderby( $order_sql, $query ) {
			global $wpdb;
			if ( $query->tribe_is_event || $query->tribe_is_event_category ) {
				$order = !empty( $query->order ) ? $query->order : $query->get( 'order' );
				$orderby = !empty( $query->orderby ) ? $query->orderby : $query->get( 'orderby' );

				$order_sql = "DATE({$wpdb->postmeta}.meta_value) {$order}, TIME({$wpdb->postmeta}.meta_value) {$order}";

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
					default:
						// we've already setup $order_sql
						break;
				}
			}

			return $order_sql;
		}

		/**
		 * Custom SQL to retrieve post_id list of events marked to be hidden from upcoming lists.
		 *
		 * @return array
		 */
		public static function getHideFromUpcomingEvents() {
			global $wpdb;

			$cache = new TribeEventsCache();
			$cache_key = 'tribe-hide-from-upcoming-events';
			$found = $cache->get( $cache_key, 'save_post' );
			if ( is_array( $found ) ) {
				return $found;
			}

			// custom sql to get ids of posts that hide_upcoming_ids
			$hide_upcoming_ids = $wpdb->get_col( "SELECT {$wpdb->postmeta}.post_id FROM {$wpdb->postmeta} WHERE {$wpdb->postmeta}.meta_key = '_EventHideFromUpcoming' AND {$wpdb->postmeta}.meta_value = 'yes'" );
			$hide_upcoming_ids = apply_filters( 'tribe_events_hide_from_upcoming_ids', $hide_upcoming_ids );
			$cache->set( $cache_key, $hide_upcoming_ids, 3600, 'save_post' );
			return $hide_upcoming_ids;
		}

        /**
         * Gets the event counts for individual days.
         *
         * @param array $args
         * @return array The counts array.
         */
        public static function getEventCounts( $args = array() ) {
			global $wpdb;
			$date = date( 'Y-m-d' );
			$defaults = array(
				'post_type' => TribeEvents::POSTTYPE,
				'start_date' => tribe_event_beginning_of_day( $date ),
				'end_date' => tribe_event_end_of_day( $date ),
				'display_type' => 'daily',
				'hide_upcoming_ids' => null
			);
			$args = wp_parse_args( $args, $defaults );

			$args['posts_per_page'] = -1;
			$args['fields'] = 'ids';
			$post_id_query = new WP_Query();
			$post_ids = $post_id_query->query( $args );
			if ( empty( $post_ids ) ) {
				return array();
			}

			$counts = array();
			switch ( $args['display_type'] ) {
			case 'daily':
			default :
				global $wp_query;

				$output_date_format = '%Y-%m-%d';
				$raw_counts = $wpdb->get_results( sprintf( "
						SELECT tribe_event_start.post_id as ID, 
							DATE_FORMAT( tribe_event_start.meta_value, '%1\$s') as EventStartDate, 
							IF (tribe_event_duration.meta_value IS NULL, DATE_FORMAT( tribe_event_end_date.meta_value, '%1\$s'), DATE_FORMAT(DATE_ADD(CAST(tribe_event_start.meta_value AS DATETIME), INTERVAL tribe_event_duration.meta_value SECOND), '%1\$s')) as EventEndDate
						FROM $wpdb->postmeta AS tribe_event_start
						LEFT JOIN $wpdb->postmeta as tribe_event_duration ON ( tribe_event_start.post_id = tribe_event_duration.post_id AND tribe_event_duration.meta_key = '_EventDuration' )
						LEFT JOIN $wpdb->postmeta as tribe_event_end_date ON ( tribe_event_start.post_id = tribe_event_end_date.post_id AND tribe_event_end_date.meta_key = '_EventEndDate' )
						WHERE tribe_event_start.meta_key = '_EventStartDate'
						AND tribe_event_start.post_id IN ( %5\$s )
						AND ( (tribe_event_start.meta_value >= '%3\$s' AND  tribe_event_start.meta_value <= '%4\$s')
							OR (tribe_event_start.meta_value <= '%3\$s' AND DATE_ADD(CAST( tribe_event_start.meta_value AS DATETIME), INTERVAL tribe_event_duration.meta_value SECOND) >= '%3\$s')
							OR (tribe_event_start.meta_value <= '%3\$s' AND tribe_event_end_date.meta_value >= '%3\$s')
							OR ( tribe_event_start.meta_value >= '%3\$s' AND  tribe_event_start.meta_value <= '%4\$s')
						)
						ORDER BY DATE(tribe_event_start.meta_value) ASC, TIME(tribe_event_start.meta_value) ASC;",
						$output_date_format,
						$output_date_format,
						$args['start_date'],
						$args['end_date'],
						implode( ',', array_map( 'intval', $post_ids ) )
					) );
				// echo $wpdb->last_query;
				$start_date = new DateTime( $args['start_date'] );
				$end_date = new DateTime( $args['end_date'] );
				$days = self::dateDiff( $start_date->format( 'Y-m-d' ), $end_date->format( 'Y-m-d' ) );
				$term_id = isset( $wp_query->query_vars[TribeEvents::TAXONOMY] ) ? $wp_query->query_vars[TribeEvents::TAXONOMY] : null;
				if ( is_int( $term_id ) ) {
					$term = get_term_by( 'id', $term_id, TribeEvents::TAXONOMY );
				} elseif ( is_string( $term_id ) ) {
					$term = get_term_by( 'slug', $term_id, TribeEvents::TAXONOMY );
				}
				for ( $i = 0, $date = $start_date; $i <= $days; $i++, $date->modify( '+1 day' ) ) {
					$formatted_date = $date->format( 'Y-m-d' );
					$count = 0;
					foreach ( $raw_counts as $record ) {
						$record_start = $record->EventStartDate;
						$record_end = $record->EventEndDate;
						if ( $record_start <= $formatted_date && $record_end >= $formatted_date ) {
							if ( isset( $term->term_id ) ) {
								$record_terms = get_the_terms( $record->ID, TribeEvents::TAXONOMY );
								if ( !$record_terms || ( $record_terms && !in_array( $term, $record_terms ) ) ) {
									$count--;
								}
							}
							$count++;
						}
					}
					$counts[ $formatted_date ] = $count;
				}
				break;
			}
			return $counts;
		}

        /**
         * The number of days between two arbitrary dates.
         *
         * @param string $date1 The first date.
         * @param string $date2 The second date.
         * @return int The number of days between two dates.
         */
        public static function dateDiff( $date1, $date2 ) {
			$current = $date1;
			$datetime2 = date_create( $date2 );
			$count = 0;
			while ( date_create( $current ) < $datetime2 ) {
				$current = gmdate( "Y-m-d", strtotime( "+1 day", strtotime( $current ) ) );
				$count++;
			}
			return $count;
		}

		/**
		 * Customized WP_Query wrapper to setup event queries with default arguments.
		 *
		 * @param array $args
		 * @return array|WP_Query
		 */
		public static function getEvents( $args = array(), $full = false ) {
			$defaults = array(
				'post_type' => TribeEvents::POSTTYPE,
				'orderby' => 'event_date',
				'order' => 'ASC',
				'posts_per_page' => tribe_get_option( 'postsPerPage', 10 )
			);
			$args = wp_parse_args( $args, $defaults );

			//print_r($args);

			$wp_query = new WP_Query( $args );

			// print_r($wp_query->request);

			if ( ! empty( $wp_query->posts ) ) {
				if ( $full ) {
					return $wp_query;
				} else {
					$posts = $wp_query->posts;
					return $posts;
				}
			} else {
				if ( $full ) {
					return $wp_query;
				} else {
					return array();
				}
			}
		}
	}
}
