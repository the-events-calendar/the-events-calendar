<?php
/**
 * Controls the main event query.  Allows for recurring events.
 */

// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }

if (!class_exists('TribeEventsQuery')) {
	class TribeEventsQuery {

		public static function init() {
			add_filter( 'pre_get_posts', array( __CLASS__, 'pre_get_posts' ), 0 );
			add_filter( 'the_posts', array( __CLASS__, 'the_posts'), 0 );
			add_filter( 'posts_orderby', array(__CLASS__, 'posts_orderby'), 10, 2);
			// add_action( 'parse_query', array( __CLASS__, 'setupQuery'), 0 );			
		}

		public function pre_get_posts( $query ) {

			// check if any possiblity of this being an event query
			$query->tribe_is_event = ( (isset($_GET['post_type']) && $_GET['post_type'] == TribeEvents::POSTTYPE) 
				|| (isset($query->query_vars['post_type']) && $query->query_vars['post_type'] == TribeEvents::POSTTYPE) )
				? true // it was an event query
				: false;

			// check if any possiblity of this being an event category
			$query->tribe_is_event_category = ( (isset($_GET[TribeEvents::TAXONOMY]) && $_GET[TribeEvents::TAXONOMY] != '')
				|| (isset($query->query_vars[TribeEvents::TAXONOMY]) && $query->query_vars[TribeEvents::TAXONOMY] != '') ) 
				? true // it was an event category
				: false;

			$query->tribe_is_event_venue = ( (isset($_GET['post_type']) && $_GET['post_type'] == TribeEvents::VENUE_POST_TYPE) 
				|| (isset($query->query_vars['post_type']) && $query->query_vars['post_type'] == TribeEvents::VENUE_POST_TYPE) )
				? true // it was an event venue
				: false;

			$query->tribe_is_event_organizer = ( (isset($_GET['post_type']) && $_GET['post_type'] == TribeEvents::ORGANIZER_POST_TYPE) 
				|| (isset($query->query_vars['post_type']) && $query->query_vars['post_type'] == TribeEvents::ORGANIZER_POST_TYPE) )
				? true // it was an event organizer
				: false;

			$query->tribe_is_event_query = ( $query->tribe_is_event
				|| $query->tribe_is_event_category
				|| $query->tribe_is_event_venue
				|| $query->tribe_is_event_organizer )
				? true // this is an event query of some type
				: false; // move along, this is not the query you are looking for

			if( $query->tribe_is_event_query || $query->tribe_is_event_category) {
				if( !empty($query->query_vars['eventDisplay']) ) {
	            	switch ( $query->query_vars['eventDisplay'] ) {
	               		case 'past': // setup past event display query
							$query->set( 'end_date', date_i18n( TribeDateUtils::DBDATETIMEFORMAT ) );
							$query->set( 'orderby', 'event_date' );
							$query->set( 'order', 'DESC' );
	                  		break;
	               		case 'day':
							$query->set( 'start_date', $query->get('eventDate') );
							$query->set( 'end_date', $query->get('start_date') );
							$query->set( 'orderby', 'event_date' );
							$query->set( 'order', 'ASC' );
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
	         	} else if ( is_single() &&  $query->get('eventDate') != '' ) {
					$query->set( 'start_date', $query->get('eventDate') );
					$query->set( 'eventDate', $query->get('eventDate') );
				} else {
					$query->set( 'hide_upcoming', true );
					$query->set( 'start_date', date_i18n( TribeDateUtils::DBDATETIMEFORMAT ) );
					$query->set( 'orderby', 'event_date' );
					$query->set( 'order', 'ASC' );
				}

				$meta_query = array( 'relation' => 'AND' );
				if( $query->get( 'start_date') != '' && $query->get( 'end_date') != '' ){ 
					$meta_query[] = array(
						'key'     => '_EventStartDate',
						'value'   => array(
							$query->get( 'start_date'),
							$query->get( 'end_date')),
						'compare' => 'BETWEEN',
						'type'    => 'DATETIME'
					);
				} else if( $query->get( 'start_date') != ''){
					$meta_query[] = array(
						'key' => '_EventStartDate',
						'value' => $query->get( 'start_date'),
						'compare' => '>',
						'type' => 'DATETIME'
					);
				} else if( $query->get( 'end_date') != ''){
					$meta_query[] = array(
						'key' => '_EventStartDate',
						'value' => $query->get( 'end_date'),
						'compare' => '<',
						'type' => 'DATETIME'
					);
				} else {
					$meta_query[] = array(
						'key' => '_EventStartDate',
						'type' => 'DATETIME'
					);
				}

				// filter by Venue ID
				if( $query->get('venue') != '' ) {
					$meta_query[] = array(
						'key' => '_EventVenueID', 
						'value' => $query->get('venue')
					);
				}

				// proprietary metaKeys go to standard meta
				if( $query->get('metaKey') != '' ) {
					$meta_query[] = array(
						'key' => $query->get('metaKey'), 
						'value' => $query->get('metaValue')
					);
				}

				// setup custom meta_queries
				$query->set( 'meta_query', $meta_query );

				// eventCat becomes a standard taxonomy query - will need to deprecate and update views eventually
				if ( $query->get('eventCat') != '-1' ) {
					$tax_query[] = array(
						'taxonomy' => TribeEvents::TAXONOMY, 
						'field' => is_numeric($query->get('eventCat')) ? 'id' : 'name', 
						'terms' => $query->get('eventCat')
						);
					$query->set( 'tax_query', $meta_query );

				}
		
				// enable pagination setup
				if ( $query->get('numResults') != '' ) {
					$query->set( 'posts_per_page', $query->get('numResults'));
				} elseif ( $query->get('posts_per_page') == '' ) {
					$query->set( 'posts_per_page', (int) tribe_get_option( 'postsPerPage', 10 ) );
				}

				// hide upcoming events from query (only not in admin)
				if ( !is_admin() && $query->get('hide_upcoming') ) {
					$hide_upcoming_ids = self::getHideFromUpcomingEvents();
					if( !empty($hide_upcoming_ids) )
						$query->set('post__not_in', $hide_upcoming_ids);
				}

			}

			// check if is_event_query === true and hook filter
			return $query->tribe_is_event_query ? apply_filters( 'tribe_events_pre_get_posts', $query ) : $query;
		}

		public function the_posts( $posts ) {
			global $wp_query;
			print_r($wp_query->request);
			foreach( $posts as $id => $post ) {
				$posts[$id]->tribe_is_event = false;

				// is event add required fields
				if( tribe_is_event( $post->ID) ) {
					$posts[$id]->tribe_is_event = true;
					$posts[$id]->EventStartDate = get_post_meta( $post->ID, '_EventStartDate', true);
					$posts[$id]->EventDuration = get_post_meta( $post->ID, '_EventDuration', true);
					$posts[$id]->EventEndDate = get_post_meta( $post->ID, '_EventEndDate', true);
				}
			}

			// return modified event posts
			return $posts;
		}

		public static function posts_orderby( $order_sql, $cur_query ){
			if( $cur_query->get( 'orderby' ) == 'event_date' ) {
				$order_direction = $cur_query->get( 'order' );
				$order_sql = "DATE(wp_postmeta.meta_value) {$order_direction}, TIME(wp_postmeta.meta_value) {$order_direction}";
			}
		
			return $order_sql;
		}

		public static function getHideFromUpcomingEvents(){
			global $wpdb;

			// custom sql to get ids of posts that hide_upcoming_ids
			$hide_upcoming_ids = $wpdb->get_col("SELECT {$wpdb->postmeta}.post_id FROM {$wpdb->postmeta} WHERE {$wpdb->postmeta}.meta_key = '_EventHideFromUpcoming' AND {$wpdb->postmeta}.meta_value = 'yes'");
			return apply_filters('tribe_events_hide_from_upcoming_ids', $hide_upcoming_ids);
		}

		public static function getEvents( $args = array() ) {

			$defaults = array(
				'post_type' => TribeEvents::POSTTYPE,
				'meta_query' => array(
					'relation' => 'AND',
					array(
						'key' => '_EventStartDate',
						// 'value' => 
						'type' => 'DATETIME'
						)
					),
				// 'meta_key' => '_EventStartDate',
				'orderby' => 'meta_value',
				'order' => 'ASC',
				'posts_per_page' => tribe_get_option( 'postsPerPage', 10 )
			);	
			$args = wp_parse_args( $args, $defaults);

			$wp_query = new WP_Query( $args );
			// print_r($wp_query);

			if( ! empty($wp_query->posts) ) {
				$posts = $wp_query->posts;
				return $posts;
			} else {
				return NULL;
			}
		}




		
		// Remove all the filters we've used once we're done with them.
		// public static function deregister( $post ){
		// 	remove_filter('parse_tribe_event_query', array( __CLASS__, 'setupQueryArgs' ) );
		// 	remove_filter('parse_tribe_event_query', array( __CLASS__, 'setArgsFromDisplayType' ) );			
		// 	remove_filter( 'posts_join', array(__CLASS__, 'setupJoins' ), 10, 2 );
		// 	remove_filter( 'posts_where', array(__CLASS__, 'addEventConditions'), 10, 2);
		// 	remove_filter( 'posts_fields', array(__CLASS__, 'setupFields' ) );	
		// 	remove_filter( 'posts_groupby', array(__CLASS__, 'addStartDateToGroupBy'));
		// 	remove_filter( 'posts_orderby', array(__CLASS__, 'dateOrderBy'), 10, 2);
		// 	remove_action( 'the_post', array( __CLASS__, 'deregister' ), 10, 1 );	
		// }
	
		// if this is an event, then set up our query vars
		// public static function setupQuery($query) {
		// 	if ( !is_admin() && (
		// 			  ((isset($_GET['post_type']) && $_GET['post_type'] == TribeEvents::POSTTYPE) || (isset($_GET['tribe_events_cat']) && $_GET['tribe_events_cat'] != '')) ||
		// 			  ((isset($query->query_vars['post_type']) && $query->query_vars['post_type'] == TribeEvents::POSTTYPE) || (isset($query->query_vars['tribe_events_cat']) && $query->query_vars['tribe_events_cat'] != ''))
		// 			)
		// 		)
		// 	{
		// 		$query->query_vars['suppress_filters'] = false;

		// 		add_filter('parse_tribe_event_query', array( __CLASS__, 'setupQueryArgs' ) );
		// 		add_filter('parse_tribe_event_query', array( __CLASS__, 'setArgsFromDisplayType' ) );			
			
		// 		// filter to manipulate the tribe_event_query parameters
		// 		apply_filters( 'parse_tribe_event_query', $query );		
		// 		add_filter( 'posts_join', array(__CLASS__, 'setupJoins' ), 10, 2 );
		// 		add_filter( 'posts_where', array(__CLASS__, 'addEventConditions'), 10, 2);
		// 		add_filter( 'posts_fields', array(__CLASS__, 'setupFields' ) );	
		// 		add_filter( 'posts_groupby', array(__CLASS__, 'addStartDateToGroupBy'));
		// 		add_filter( 'posts_orderby', array(__CLASS__, 'dateOrderBy'), 10, 2);
				
		// 		// Set up to deregister the filters once we're done with them.
		// 		add_action( 'the_post', array( __CLASS__, 'deregister' ), 10, 1 );
		// 	}	
		// }
	
		// sets query vars based on display type if designated and deals with backwards compatability
		// public static function setupQueryArgs($query) {	
		// 	$args = &$query->query_vars;
		
		// 	// eventCat becomes a standard taxonomy query - will need to deprecate and update views eventually
		// 	if (!empty($args['eventCat']) && $args['eventCat'] != '-1') {
		// 		$tax_field = is_numeric($args['eventCat']) ? "id" : "name";
		// 		$args['tax_query'][] = array('taxonomy'=>TribeEvents::TAXONOMY, 'field'=>$tax_field, 'terms'=>$args['eventCat']);
		// 	}
		
		// 	if (!empty($args['numResults'])) {
		// 		$args['posts_per_page'] = $args['numResults'];
		// 	} elseif (empty($args['posts_per_page'])) {
		// 		$args['posts_per_page'] = (int) tribe_get_option( 'postsPerPage', 10 );
		// 	}
			
		// 	if (!empty($args['venue'])) {
		// 		$args['meta_query'][] = array('key'=>'_EventVenueID', 'value'=>$args['venue']);
		// 	}

		// 	// proprietary metaKeys go to standard meta
		// 	if (!empty($args['metaKey']))
		// 		$args['meta_query'][] = array('key'=>$args['metaKey'], 'value'=>$args['metaValue']);
		
		// 	return $query;
		// }


		// public static function setArgsFromDisplayType($query) { 
  //       	if( !empty($query->query_vars['eventDisplay']) ) {
  //           	switch ( $query->query_vars['eventDisplay'] ) {
  //              		case "past":
  //                 		$query = self::setPastDisplayTypeArgs($query);
  //                 		break;
  //              		case "upcoming":
  //                 		$query = self::setUpcomingDisplayTypeArgs($query);
  //                 		break;
  //              		case "day":
  //                 		$query = self::setDayDisplayTypeArgs($query);
  //                 		break;
  //              		case "all":
  //                 		$query = self::setAllDisplayTypeArgs($query);
  //                 		break;				
  //              		case "month":
  //                 		$query = self::setMonthDisplayTypeArgs($query);				
  //           	}
  //        	} else if ( is_single() ) {
		// 		$args = &$query->query_vars;
		// 		if( isset($args['eventDate']) ) {
		// 			$args['start_date'] = $args['eventDate'];
		// 			$args['end_date'] = $args['eventDate'];
		// 		}
		// 	} else {
		// 		$query = self::setUpcomingDisplayTypeArgs($query);
		// 	}
		
		// 	return $query;
		// }

		// public static function setupJoins($join, $cur_query) {
		// 	global $wpdb;

		// 	if ( $cur_query->get('hide_upcoming') ) {
		// 		$join .= " LEFT JOIN {$wpdb->postmeta} as hideUpcoming ON( {$wpdb->posts}.ID = hideUpcoming.post_id AND hideUpcoming.meta_key = '_EventHideFromUpcoming') ";	
		// 	}

		// 	$join .= " LEFT JOIN {$wpdb->postmeta} as eventStart ON( {$wpdb->posts}.ID = eventStart.post_id AND eventStart.meta_key = '_EventStartDate') ";
		// 	$join .= " LEFT JOIN {$wpdb->postmeta} as eventDuration ON( {$wpdb->posts}.ID = eventDuration.post_id AND eventDuration.meta_key = '_EventDuration') ";
		// 	$join .= " LEFT JOIN {$wpdb->postmeta} as eventEnd ON( {$wpdb->posts}.ID = eventEnd.post_id AND eventEnd.meta_key = '_EventEndDate') ";

		// 	return $join;
		// }

		// public static function setupFields( $fields ) {
		// 	global $wpdb;

		// 	$fields .= ", eventStart.meta_value as EventStartDate, IFNULL(DATE_ADD(CAST(eventStart.meta_value AS DATETIME), INTERVAL eventDuration.meta_value SECOND), eventEnd.meta_value) as EventEndDate ";
		// 	return $fields;
		// }
	
		// public static function addStartDateToGroupBy( $group ) {
		// 	if ( $group ) {
		// 		$group .= ', eventStart.meta_value';
		// 	}

		// 	return $group;
		// }
	
		// public static function setPastDisplayTypeArgs($query) {		
		// 	$args = &$query->query_vars;
		// 	$args['end_date'] = date_i18n( TribeDateUtils::DBDATETIMEFORMAT );
		// 	$args['orderby'] = 'event_date';
		// 	$args['order'] = "DESC";
		
		// 	return $query;
		// }

		// public static function setUpcomingDisplayTypeArgs($query) {
		// 	$args = &$query->query_vars;
		// 	$args['hide_upcoming'] = true;
		// 	$args['start_date'] = date_i18n( TribeDateUtils::DBDATETIMEFORMAT );
		// 	$args['orderby'] = 'event_date';
		// 	$args['order'] = "ASC";

		// 	return $query;
		// }

		// public static function setDayDisplayTypeArgs($query) {
  //        global $wp_query;
		// 	$args = &$query->query_vars;
  //        $args['start_date'] = $wp_query->query_vars['eventDate'];
  //        $args['end_date'] = $args['start_date'];
		// 	$args['orderby'] = 'event_date';
		// 	$args['order'] = "ASC";

		// 	return $query;
		// }

	
		// public static function setAllDisplayTypeArgs($query) {
		// 	$args = &$query->query_vars;
		// 	$args['orderby'] = 'event_date';
		// 	$args['order'] = "ASC";
		
		// 	return $query;		
		// }

		// // month functions
		// public static function setMonthDisplayTypeArgs($query) {
		// 	global $wp_query;
		// 	$tribe_ecp = TribeEvents::instance();
		// 	$args = &$query->query_vars;		
		
		// 	$args['posts_per_page'] = -1; // show ALL month posts
		// 	$args['start_date'] = substr_replace( date_i18n( TribeDateUtils::DBDATEFORMAT ), '01', -2 );
		// 	$args['start_date'] = substr_replace( $args['start_date'], '01', -2 );

		// 	if ( isset ( $wp_query->query_vars['eventDate'] ) )
		// 		$args['start_date'] = $wp_query->query_vars['eventDate'] . "-01";

		// 	$args['eventDate'] = $args['start_date'];		
		// 	$args['end_date'] = date( 'Y-m-d', strtotime( $tribe_ecp->nextMonth($args['start_date']) ) -(24*3600) );
		// 	$args['orderby'] = 'event_date';
		// 	$args['order'] = "ASC";
		
		// 	return $query;		
		// }

		public static function addEventConditions($where, $cur_query) {
			global $wpdb;

			if ( $cur_query->get('start_date') ) {
				$start_date = TribeDateUtils::beginningOfDay($cur_query->get('start_date'));
			}

			if ( $cur_query->get('end_date') ) {
				$end_date = TribeDateUtils::endOfDay(  $cur_query->get('end_date') );
			}

			// we can't store end date directly because it messes up the distinc clause
			$endDate = " IFNULL(DATE_ADD(CAST(eventStart.meta_value AS DATETIME), INTERVAL eventDuration.meta_value SECOND), eventEnd.meta_value) ";

			if(!empty($start_date) && !empty($end_date)) {
				$start_clause = $wpdb->prepare("(eventStart.meta_value >= %s AND eventStart.meta_value <= %s)", $start_date, $end_date);
				$end_clause = $wpdb->prepare("($endDate >= %s AND eventStart.meta_value <= %s )", $start_date, $end_date);
				$within_clause = $wpdb->prepare("(eventStart.meta_value < %s AND $endDate >= %s )", $start_date, $end_date);
				$where .= " AND ($start_clause OR $end_clause OR $within_clause)";
			} else if(!empty($end_date)) {
				$start_clause = $wpdb->prepare("$endDate < %s", $end_date);
				$where .= " AND $start_clause";
			} else if(!empty($start_date)) {
			   $end_clause = $wpdb->prepare("eventStart.meta_value > %s", $start_date);
				$within_clause = $wpdb->prepare("(eventStart.meta_value <= %s AND $endDate >= %s )", $start_date, $start_date);
			   $where .= " AND ($end_clause OR $within_clause)";
			}
		
			// if ( $cur_query->get('hide_upcoming') ) {
			// 	$where .= " AND (hideUpcoming.meta_value != 'yes' OR hideUpcoming.meta_value IS null) ";	
			// }		

			return $where;
		}

		// public static function dateOrderBy($order_sql, $cur_query) {
		// 	if( $cur_query->get( 'orderby' ) == 'event_date' ) {
		// 		$direction = $cur_query->get( 'order' );
		// 		$order_sql = "DATE(eventStart.meta_value) $direction, TIME(eventStart.meta_value) $direction";
		// 	}
		
		// 	return $order_sql;
		// }

	}
}
