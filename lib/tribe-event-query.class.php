<?php
/**
 * Controls the main event query.  Allows for recurring events.
 */

// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }

if (!class_exists('TribeEventsQuery')) {
	class TribeEventsQuery {
	
		public static function init() {
			add_action( 'parse_query', array( __CLASS__, 'setupQuery'), 0 );			
		}
	
		// if this is an event, then set up our query vars
		public static function setupQuery($query) {
			if ( !is_admin() && (
					  ((isset($_GET['post_type']) && $_GET['post_type'] == TribeEvents::POSTTYPE) || (isset($_GET['tribe_events_cat']) && $_GET['tribe_events_cat'] != '')) ||
					  ((isset($query->query_vars['post_type']) && $query->query_vars['post_type'] == TribeEvents::POSTTYPE) || (isset($query->query_vars['tribe_events_cat']) && $query->query_vars['tribe_events_cat'] != ''))
					)
				)
			{
				$query->query_vars['suppress_filters'] = false;

				add_filter('parse_tribe_event_query', array( __CLASS__, 'setupQueryArgs' ) );
				add_filter('parse_tribe_event_query', array( __CLASS__, 'setArgsFromDisplayType' ) );			
			
				// filter to manipulate the tribe_event_query parameters
				apply_filters( 'parse_tribe_event_query', $query );		
				add_filter( 'posts_join', array(__CLASS__, 'setupJoins' ), 10, 2 );
				add_filter( 'posts_where', array(__CLASS__, 'addEventConditions'), 10, 2);
				add_filter( 'posts_fields', array(__CLASS__, 'setupFields' ) );	
				add_filter( 'posts_groupby', array(__CLASS__, 'addStartDateToGroupBy'));
				add_filter( 'posts_orderby', array(__CLASS__, 'dateOrderBy'), 10, 2);
			}	
		}
	
		// sets query vars based on display type if designated and deals with backwards compatability
		public static function setupQueryArgs($query) {	
			$args = &$query->query_vars;
		
			// eventCat becomes a standard taxonomy query - will need to deprecate and update views eventually
			if (!empty($args['eventCat']) && $args['eventCat'] != '-1') {
				$tax_field = is_numeric($args['eventCat']) ? "id" : "name";
				$args['tax_query'][] = array('taxonomy'=>TribeEvents::TAXONOMY, 'field'=>$tax_field, 'terms'=>$args['eventCat']);
			}
		
			if (!empty($args['numResults'])) {
				$args['posts_per_page'] = $args['numResults'];
			} elseif (empty($args['posts_per_page'])) {
				$args['posts_per_page'] = (int) tribe_get_option( 'postsPerPage', 10 );
			}
			
      if (!empty($args['venue'])) {
				$args['meta_query'][] = array('key'=>'_EventVenueID', 'value'=>$args['venue']);
      }

			// proprietary metaKeys go to standard meta
			if (!empty($args['metaKey']))
				$args['meta_query'][] = array('key'=>$args['metaKey'], 'value'=>$args['metaValue']);
		
			return $query;
		}

		public static function getEvents($args) {
			return get_posts($args);
		}

		public static function setArgsFromDisplayType($query) { 
        	if( !empty($query->query_vars['eventDisplay']) ) {
            	switch ( $query->query_vars['eventDisplay'] ) {
               		case "past":
                  		$query = self::setPastDisplayTypeArgs($query);
                  		break;
               		case "upcoming":
                  		$query = self::setUpcomingDisplayTypeArgs($query);
                  		break;
               		case "day":
                  		$query = self::setDayDisplayTypeArgs($query);
                  		break;
               		case "all":
                  		$query = self::setAllDisplayTypeArgs($query);
                  		break;				
               		case "month":
                  		$query = self::setMonthDisplayTypeArgs($query);				
            	}
         	} else if ( is_single() ) {
				$args = &$query->query_vars;
				if( isset($args['eventDate']) ) {
					$args['start_date'] = $args['eventDate'];
					$args['end_date'] = $args['eventDate'];
				}
			} else {
				$query = self::setUpcomingDisplayTypeArgs($query);
			}
		
			return $query;
		}

		public static function setupJoins($join, $cur_query) {
			global $wpdb;

			if ( $cur_query->get('hide_upcoming') ) {
				$join .= " LEFT JOIN {$wpdb->postmeta} as hideUpcoming ON( {$wpdb->posts}.ID = hideUpcoming.post_id AND hideUpcoming.meta_key = '_EventHideFromUpcoming') ";	
			}

			$join .= " LEFT JOIN {$wpdb->postmeta} as eventStart ON( {$wpdb->posts}.ID = eventStart.post_id AND eventStart.meta_key = '_EventStartDate') ";
			$join .= " LEFT JOIN {$wpdb->postmeta} as eventDuration ON( {$wpdb->posts}.ID = eventDuration.post_id AND eventDuration.meta_key = '_EventDuration') ";
			$join .= " LEFT JOIN {$wpdb->postmeta} as eventEnd ON( {$wpdb->posts}.ID = eventEnd.post_id AND eventEnd.meta_key = '_EventEndDate') ";

			return $join;
		}

		public static function setupFields( $fields ) {
			global $wpdb;

			$fields .= ", eventStart.meta_value as EventStartDate, IFNULL(DATE_ADD(CAST(eventStart.meta_value AS DATETIME), INTERVAL eventDuration.meta_value SECOND), eventEnd.meta_value) as EventEndDate ";
			return $fields;
		}
	
		public static function addStartDateToGroupBy( $group ) {
			if ( $group ) {
				$group .= ', eventStart.meta_value';
			}

			return $group;
		}
	
		public static function setPastDisplayTypeArgs($query) {		
			$args = &$query->query_vars;
			$args['end_date'] = date_i18n( TribeDateUtils::DBDATETIMEFORMAT );
			$args['orderby'] = 'event_date';
			$args['order'] = "DESC";
		
			return $query;
		}

		public static function setUpcomingDisplayTypeArgs($query) {
			$args = &$query->query_vars;
			$args['hide_upcoming'] = true;
			$args['start_date'] = date_i18n( TribeDateUtils::DBDATETIMEFORMAT );
			$args['orderby'] = 'event_date';
			$args['order'] = "ASC";

			return $query;
		}

		public static function setDayDisplayTypeArgs($query) {
         global $wp_query;
			$args = &$query->query_vars;
         $args['start_date'] = $wp_query->query_vars['eventDate'];
         $args['end_date'] = $args['start_date'];
			$args['orderby'] = 'event_date';
			$args['order'] = "ASC";

			return $query;
		}

	
		public static function setAllDisplayTypeArgs($query) {
			$args = &$query->query_vars;
			$args['orderby'] = 'event_date';
			$args['order'] = "ASC";
		
			return $query;		
		}

		// month functions
		public static function setMonthDisplayTypeArgs($query) {
			global $wp_query;
			$tribe_ecp = TribeEvents::instance();
			$args = &$query->query_vars;		
		
			$args['posts_per_page'] = -1; // show ALL month posts
			$args['start_date'] = date_i18n( TribeDateUtils::DBDATEFORMAT );
			$args['start_date'] = substr_replace( $args['start_date'], '01', -2 );

			if ( isset ( $wp_query->query_vars['eventDate'] ) )
				$args['start_date'] = $wp_query->query_vars['eventDate'] . "-01";

			$args['eventDate'] = $args['start_date'];		
			$args['end_date'] = date( 'Y-m-d', strtotime( $tribe_ecp->nextMonth($args['start_date']) ) -(24*3600) );
			$args['orderby'] = 'event_date';
			$args['order'] = "ASC";
		
			return $query;		
		}

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
		
			if ( $cur_query->get('hide_upcoming') ) {
				$where .= " AND (hideUpcoming.meta_value != 'yes' OR hideUpcoming.meta_value IS null) ";	
			}		

			return $where;
		}

		public static function dateOrderBy($order_sql, $cur_query) {
			if( $cur_query->get( 'orderby' ) == 'event_date' ) {
				$direction = $cur_query->get( 'order' );
				$order_sql = "DATE(eventStart.meta_value) $direction, TIME(eventStart.meta_value) $direction";
			}
		
			return $order_sql;
		}
	}
}
