<?php
/**
 * Controls the main event query.  Allows for recurring events.
 */

// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }

if (!class_exists('TribeEventsQuery')) {
	class TribeEventsQuery {

		function __construct(){
			add_action('tribe_events_init_pre_get_posts', array(__CLASS__,'init'));
		}

		/**
		 * Initialize The Events Calendar query filters and post processing.
		 * @return null
		 */
		public static function init() {

			// if tribe event query add filters
			add_filter( 'pre_get_posts', array( __CLASS__, 'pre_get_posts' ), 0 );

			// setup returned posts with event fields ( start date, end date, duration etc )
			add_filter( 'the_posts', array( __CLASS__, 'the_posts'), 0 );
		}


		/**
		 * Is hooked by init() filter to parse the WP_Query arguments for main and alt queries.
		 * @param  object $query WP_Query object args supplied or default
		 * @return object $query (modified)
		 */
		public function pre_get_posts( $query ) {

			// check if any possiblity of this being an event query
			$query->tribe_is_event = ( isset($query->query_vars['post_type']) && $query->query_vars['post_type'] == TribeEvents::POSTTYPE )
				? true // it was an event query
				: false;

			// check if any possiblity of this being an event category
			$query->tribe_is_event_category = ( isset($query->query_vars[TribeEvents::TAXONOMY]) && $query->query_vars[TribeEvents::TAXONOMY] != '' ) 
				? true // it was an event category
				: false;

			$query->tribe_is_event_venue = ( isset($query->query_vars['post_type']) && $query->query_vars['post_type'] == TribeEvents::VENUE_POST_TYPE )
				? true // it was an event venue
				: false;

			$query->tribe_is_event_organizer = ( isset($query->query_vars['post_type']) && $query->query_vars['post_type'] == TribeEvents::ORGANIZER_POST_TYPE )
				? true // it was an event organizer
				: false;

			$query->tribe_is_event_query = ( $query->tribe_is_event
				|| $query->tribe_is_event_category
				|| $query->tribe_is_event_venue
				|| $query->tribe_is_event_organizer )
				? true // this is an event query of some type
				: false; // move along, this is not the query you are looking for

			if( $query->tribe_is_event || $query->tribe_is_event_category) {

				add_filter( 'posts_join', array(__CLASS__, 'posts_join' ), 10, 2 );
				add_filter( 'posts_where', array(__CLASS__, 'posts_where'), 10, 2);

				if( !empty($query->query_vars['eventDisplay']) ) {
	            	switch ( $query->query_vars['eventDisplay'] ) {
	               		case 'past': // setup past event display query
							$query->set( 'end_date', date_i18n( TribeDateUtils::DBDATETIMEFORMAT ) );
							$query->set( 'orderby', 'event_date' );
							$query->set( 'order', 'DESC' );
	                  		break;
	               		case 'all':
							$query->set( 'orderby', 'event_date' );
							$query->set( 'order', 'ASC' );
	                  		break;				
	               		case 'month':
							$start_date = substr_replace( date_i18n( TribeDateUtils::DBDATEFORMAT ), '01', -2 );
							$start_date = ( $query->get('eventDate') != '' ) ? $query->get('eventDate') . '-01' : $start_date;
							$query->set( 'start_date', $start_date );
							$query->set( 'eventDate', $start_date );
							$query->set( 'end_date', date( 'Y-m-d', strtotime( TribeEvents::instance()->nextMonth($start_date) ) -(24*3600) ));
							$query->set( 'orderby', 'event_date' );
							$query->set( 'order', 'ASC' );
							$query->set('posts_per_page', -1); // show ALL month posts
	                  		break;
	               		case 'upcoming':
	               		default: // default display query
							$query->set( 'hide_upcoming', true );
							$query->set( 'start_date', date_i18n( TribeDateUtils::DBDATETIMEFORMAT ) );
							$query->set( 'orderby', 'event_date' );
							$query->set( 'order', 'ASC' );
	                  		break;	
	            	}
	         	} else if ( is_single() ) {
	         		if( $query->get('eventDate') != '' ) {
						$query->set( 'start_date', $query->get('eventDate') );
						$query->set( 'eventDate', $query->get('eventDate') );
					}
				} else {
					$query->set( 'hide_upcoming', true );
					$query->set( 'start_date', date_i18n( TribeDateUtils::DBDATETIMEFORMAT ) );
					$query->set( 'orderby', 'event_date' );
					$query->set( 'order', 'ASC' );
				}

				// eventCat becomes a standard taxonomy query - will need to deprecate and update views eventually
				if ( ! in_array( $query->get('eventCat'), array( '', '-1' )) ) {
					$tax_query[] = array(
						'taxonomy' => TribeEvents::TAXONOMY, 
						'field' => is_numeric($query->get('eventCat')) ? 'id' : 'name', 
						'terms' => $query->get('eventCat')
						);
				}

			}

			// filter by Venue ID
			if( $query->tribe_is_event_query && $query->get('venue') != '' ) {
				$meta_query[] = array(
					'key' => '_EventVenueID', 
					'value' => $query->get('venue')
					);
			}

			// proprietary metaKeys go to standard meta
			if( $query->tribe_is_event_query && $query->get('metaKey') != '' ) {
				$meta_query[] = array(
					'key' => $query->get('metaKey'), 
					'value' => $query->get('metaValue')
					);
			}

			// enable pagination setup
			if ( $query->tribe_is_event_query && $query->get('numResults') != '' ) {
				$query->set( 'posts_per_page', $query->get('numResults'));
			} elseif ( $query->get('posts_per_page') == '' ) {
				$query->set( 'posts_per_page', (int) tribe_get_option( 'postsPerPage', 10 ) );
			}

			// hide upcoming events from query (only not in admin)
			if ( $query->tribe_is_event_query && $query->get('hide_upcoming') ) {
				$hide_upcoming_ids = self::getHideFromUpcomingEvents();
				if( !empty($hide_upcoming_ids) )
					$query->set('post__not_in', $hide_upcoming_ids);
			}

			if( $query->tribe_is_event_query && !empty($meta_query) ) {
				// setup default relation for meta queries
				$meta_query['relation'] = 'AND';
				$meta_query_combined = array_merge( (array) $meta_query, (array) $query->get( 'meta_query'));
				$query->set( 'meta_query', $meta_query_combined );
			}

			if( $query->tribe_is_event_query && !empty($tax_query) ) {
				// setup default relation for tax queries
				$tax_query_combined = array_merge( (array) $tax_query, (array) $query->get( 'tax_query'));
				$query->set( 'tax_query', $tax_query_combined );
			}

			if( $query->tribe_is_event_query ) {
				add_filter( 'posts_orderby', array(__CLASS__, 'posts_orderby'), 10, 2);
			}

			// if is in the admin remove the event date & upcoming filters, unless is an ajax call
			if ( is_admin() && $query->tribe_is_event_query ) {
				if ( ( !defined( 'DOING_AJAX' ) ) || ( defined( 'DOING_AJAX' ) && !( DOING_AJAX ) ) ) {


					remove_filter( 'posts_join', array( __CLASS__, 'posts_join' ), 10, 2 );
					remove_filter( 'posts_where', array( __CLASS__, 'posts_where' ), 10, 2 );
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
			$query->tribe_is_event_query ? apply_filters( 'tribe_events_pre_get_posts', $query ) : $query;

			// setup default Event Start join/filter
			if ( ( $query->tribe_is_event || $query->tribe_is_event_category ) && empty( $query->query_vars['meta_query'] ) ) {
				$query->set( 'meta_query', array( array( 'key' => '_EventStartDate', 'type' => 'DATETIME' ) ) );
			}

			return $query;
		}

		/**
		 * Filter all returned event posts & add additional required fields
		 * @param  array $posts returned via wp_query
		 * @return array $posts (modified)
		 */
		public function the_posts( $posts ) {
			if( !empty($posts) ) {
				foreach( $posts as $id => $post ) {
					$posts[$id]->tribe_is_event = false;

					// is event add required fields
					if( tribe_is_event( $post->ID) ) {
						$posts[$id]->tribe_is_event = true;
						$posts[$id]->tribe_is_allday = tribe_get_event_meta( $post->ID, '_EventAllDay' ) ? true : false;
						$posts[$id]->EventStartDate = get_post_meta( $post->ID, '_EventStartDate', true);
						$posts[$id]->EventDuration = get_post_meta( $post->ID, '_EventDuration', true);
						// DO NOT USE THIS - end dates are deprecated due to recurrance
						$posts[$id]->EventEndDate = get_post_meta( $post->ID, '_EventEndDate', true);
					}
				}
			}

			// return modified event posts with additional fields if added
			return $posts;
		}

		/**
		 * Custom SQL join for event duration meta field
		 * @param  string $join_sql 
		 * @param  wp_query $query
		 * @return string
		 */
		public static function posts_join( $join_sql, $query ) {
			global $wpdb;

			// if it's a true event query then we want create a join for where conditions
			if ( $query->tribe_is_event || $query->tribe_is_event_category ) {
				$join_sql .= " LEFT JOIN {$wpdb->postmeta} as tribe_event_duration ON ( {$wpdb->posts}.ID = tribe_event_duration.post_id AND tribe_event_duration.meta_key = '_EventDuration' ) ";
			}

			return $join_sql;
		}

		/**
		 * Custom SQL conditional for event duration meta field
		 * @param  string $where_sql
		 * @param  wp_query $query
		 * @return string
		 */
		public static function posts_where( $where_sql, $query ) {
			global $wpdb;

			// if it's a true event query then we to setup where conditions
			if ( $query->tribe_is_event || $query->tribe_is_event_category ) {

				// we can't store end date directly because it messes up the distinc clause
				$duration_filter = " DATE_ADD(CAST({$wpdb->postmeta}.meta_value AS DATETIME), INTERVAL tribe_event_duration.meta_value SECOND) ";

				// build where conditionals for events if date range params are set
				if( $query->get( 'start_date') != '' && $query->get( 'end_date') != '' ){ 
					$start_clause = $wpdb->prepare("({$wpdb->postmeta}.meta_value >= %s AND {$wpdb->postmeta}.meta_value <= %s)", $query->get( 'start_date'), $query->get( 'end_date'));
					$end_clause = $wpdb->prepare("($duration_filter >= %s AND {$wpdb->postmeta}.meta_value <= %s )", $query->get( 'start_date'), $query->get( 'end_date'));
					$within_clause = $wpdb->prepare("({$wpdb->postmeta}.meta_value < %s AND $duration_filter >= %s )", $query->get( 'start_date'), $query->get( 'end_date'));
					$where_sql .= " AND ($start_clause OR $end_clause OR $within_clause)";
				} else if( $query->get( 'start_date') != ''){
					$end_clause = $wpdb->prepare("{$wpdb->postmeta}.meta_value > %s", $query->get( 'start_date'));
					$within_clause = $wpdb->prepare("({$wpdb->postmeta}.meta_value <= %s AND $duration_filter >= %s )", $query->get( 'start_date'), $query->get( 'start_date'));
					$where_sql .= " AND ($end_clause OR $within_clause)";
				} else if( $query->get( 'end_date') != ''){
					$where_sql .= " AND " . $wpdb->prepare( "$duration_filter < %s", $query->get( 'end_date') );
				}
			}

			return $where_sql;
		}

		/**
		 * Custom SQL order by statement for Event Start Date result order.
		 * @param  string $order_sql
		 * @param  wp_query $query
		 * @return string
		 */
		public static function posts_orderby( $order_sql, $query ){
			global $wpdb;
			if( $query->get( 'orderby' ) == 'event_date' ) {
				$order_direction = $query->get( 'order' );
				$order_sql = "DATE({$wpdb->postmeta}.meta_value) {$order_direction}, TIME({$wpdb->postmeta}.meta_value) {$order_direction}";
			}
		
			return $order_sql;
		}

		/**
		 * Custom SQL to retrieve post_id list of events marked to be hidden from upcoming lists.
		 * @return array
		 */
		public static function getHideFromUpcomingEvents(){
			global $wpdb;

			// custom sql to get ids of posts that hide_upcoming_ids
			$hide_upcoming_ids = $wpdb->get_col("SELECT {$wpdb->postmeta}.post_id FROM {$wpdb->postmeta} WHERE {$wpdb->postmeta}.meta_key = '_EventHideFromUpcoming' AND {$wpdb->postmeta}.meta_value = 'yes'");
			return apply_filters('tribe_events_hide_from_upcoming_ids', $hide_upcoming_ids);
		}

		/**
		 * Customized WP_Query wrapper to setup event queries with default arguments.
		 * @param  array  $args
		 * @return array
		 */
		public static function getEvents( $args = array(), $full = false ) {
			$defaults = array(
				'post_type' => TribeEvents::POSTTYPE,
				'orderby' => 'event_date',
				'order' => 'ASC',
				'posts_per_page' => tribe_get_option( 'postsPerPage', 10 )
			);	
			$args = wp_parse_args( $args, $defaults);

			// print_r($args);

			$wp_query = new WP_Query( $args );

			if( ! empty($wp_query->posts) ) {
				if ( $full ) {
					return $wp_query;
				} else {
					$posts = $wp_query->posts;
					return $posts;
				}
			} else {
				return NULL;
			}
		}
	}
}
