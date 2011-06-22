<?php
class Tribe_Event_Query {
	
	public static function init() {
		add_action( 'parse_query', array( __CLASS__, 'setupQuery'), 0 );			
	}
	
	// if this is an event, then set up our query vars
	public static function setupQuery($query) {
		if ( !is_admin() && (
				  ((isset($_GET['post_type']) && $_GET['post_type'] == Events_Calendar_Pro::POSTTYPE) || (isset($_GET['sp_events_cat']) && $_GET['sp_events_cat'] != '')) ||
				  ((isset($query->query_vars['post_type']) && $query->query_vars['post_type'] == Events_Calendar_Pro::POSTTYPE) || (isset($query->query_vars['sp_events_cat']) && $query->query_vars['sp_events_cat'] != ''))
				)
			)
		{
			$query->query_vars['suppress_filters'] = false;

			add_filter('parse_tribe_event_query', array( __CLASS__, 'setupQueryArgs' ) );
			add_filter('parse_tribe_event_query', array( __CLASS__, 'setArgsFromDisplayType' ) );			
			
			// filter to manipulate the sp_event_query parameters
			apply_filters( 'parse_tribe_event_query', $query );		
			add_filter( 'posts_join', array(__CLASS__, 'setupJoins' ) );
			add_filter( 'posts_where', array(__CLASS__, 'addEventConditions'), 10, 2);
			add_filter( 'posts_fields', array(__CLASS__, 'setupFields' ) );	
			add_filter( 'posts_groupby', array(__CLASS__, 'addStartDateToGroupBy'));
		}	
	}
	
	// sets query vars based on display type if designated and deals with backwards compatability
	public static function setupQueryArgs($query) {	
		$args = &$query->query_vars;
		
		// eventCat becomes a standard taxonomy query - will need to deprecate and update views eventually
		if ($args['eventCat'] && $args['eventCat'] != '-1') {
			$tax_field = is_numeric($args['eventCat']) ? "id" : "name";
			$args['tax_query'][] = array('taxonomy'=>Events_Calendar_Pro::TAXONOMY, 'field'=>$tax_field, 'terms'=>$args['eventCat']);
		}
		
		if ($args['numResults']) {
			$args['posts_per_page'] = $args['numResults'];
		}

		// proprietary metaKeys go to standard meta
		if ($args['metaKey'])
			$args['meta_query'][] = array('key'=>$args['metaKey'], 'value'=>$args['metaValue']);
		
		return $query;
	}

	public static function getEvents($args) {
		return get_posts($args);
	}

	public static function setArgsFromDisplayType($query) {
		switch ( $query->query_vars['eventDisplay'] ) {
			case "past":
				$query = self::setPastDisplayTypeArgs($query);
				break;
			case "upcoming":
				$query = self::setUpcomingDisplayTypeArgs($query);
				break;
			case "all":
				$query = self::setAllDisplayTypeArgs($query);
				break;				
			case "month":
		   case "bydate":
				$query = self::setMonthDisplayTypeArgs($query);
		}
		
		return $query;
	}

	public static function setupJoins($join) {
		global $wpdb;

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
		$args['end_date'] = date_i18n( DateUtils::DBDATETIMEFORMAT );
		add_filter('posts_orderby', array(__CLASS__, 'setDescendingDisplayOrder'));
		
		return $query;
	}

	public static function setUpcomingDisplayTypeArgs($query) {
		$args = &$query->query_vars;
		$args['start_date'] = date_i18n( DateUtils::DBDATETIMEFORMAT );
		add_filter('posts_orderby', array(__CLASS__, 'setAscendingDisplayOrder'));

		return $query;
	}
	
	public static function setAllDisplayTypeArgs($query) {
		$args = &$query->query_vars;
		add_filter('posts_orderby', array(__CLASS__, 'setAscendingDisplayOrder'));
		
		return $query;		
	}

	// month functions
	public static function setMonthDisplayTypeArgs($query) {
		global $wp_query;
		global $sp_ecp;
		$args = &$query->query_vars;		
		
		$args['posts_per_page'] = -1; // show ALL month posts
		$args['start_date'] = date_i18n( DateUtils::DBDATEFORMAT );
		$args['start_date'] = substr_replace( $args['start_date'], '01', -2 );

		if ( isset ( $wp_query->query_vars['eventDate'] ) )
			$args['start_date'] = $wp_query->query_vars['eventDate'] . "-01";

		$args['eventDate'] = $args['start_date'];		
		$args['end_date'] = $sp_ecp->nextMonth($args['start_date']) . "-01";

		add_filter('posts_orderby', array(__CLASS__, 'setDescendingDisplayOrder'));
		
		return $query;		
	}

	public static function addEventConditions($where, $cur_query) {
		global $wpdb;

		// these should come from cur_query, but there appears to be a WP bug
		$start_date = $cur_query->get('start_date');
		
		if ( $cur_query->get('end_date') ) {
			$end_date = DateUtils::endOfDay(  $cur_query->get('end_date') );
		}

		// we can't store end date directly because it messes up the distinc clause
		$endDate = " IFNULL(DATE_ADD(CAST(eventStart.meta_value AS DATETIME), INTERVAL eventDuration.meta_value SECOND), eventEnd.meta_value) ";

		if($start_date && $end_date) {
			$start_clause = $wpdb->prepare("(eventStart.meta_value >= %s AND eventStart.meta_value <= %s)", $start_date, $end_date);
			$end_clause = $wpdb->prepare("($endDate >= %s AND eventStart.meta_value <= %s )", $start_date, $end_date);
			$within_clause = $wpdb->prepare("(eventStart.meta_value < %s AND $endDate > %s )", $start_date, $end_date);
			$where .= " AND ($start_clause OR $end_clause OR $within_clause)";
		} else if($end_date) {
			$start_clause = $wpdb->prepare("$endDate < %s", $end_date);
			$where .= " AND $start_clause";
		} else if($start_date) {
		   $end_clause = $wpdb->prepare("eventStart.meta_value > %s", $start_date);
		   $where .= " AND $end_clause";
		}

		return $where;
	}

	public static function setAscendingDisplayOrder($order_sql) {
		return "DATE(eventStart.meta_value) ASC, TIME(eventStart.meta_value) ASC";
	}

	public static function setDescendingDisplayOrder($order_sql) {
		return "DATE(eventStart.meta_value) DESC, TIME(eventStart.meta_value) DESC";
	}
}
?>
