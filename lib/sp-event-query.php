<?php
class SP_Event_Query {
	private $args;
	private $display_type;
	
	function __construct($args, $display_type = null) {
			$defaults = array(
				'posts_per_page' => get_option( 'posts_per_page', 10 ),
				'tax_query' => array(),
				'meta_query' => array(),
				'time_order' => $this->order,
				'post_type' => Events_Calendar_Pro::POSTTYPE
			);
			
			$this->args = wp_parse_args( $args, $defaults);
			$this->display_type = $display_type ? $display_type : $args['eventDisplay'];

			// eventCat becomes a standard taxonomy query - will need to deprecate and update views eventually
			if ($args['eventCat']) {
				$tax_field = is_numeric($args['eventCat']) ? "id" : "name";
				$args['tax_query'][] = array('taxonomy'=>Events_Calendar_Pro::TAXONOMY, 'field'=>$tax_field, 'terms'=>$args['eventCat']);
			}

			// proprietary metaKeys go to standard meta
			if ($args['metaKey'])
				$args['meta_query'][] = array('key'=>$args['metaKey'], 'value'=>$args['metaValue']);

			$this->setArgsFromDisplayType();
			
			add_filter( 'posts_join', array($this, 'setupJoins' ) );
			add_filter( 'posts_where', array($this, 'addEventConditions'));
			add_filter( 'posts_fields', array($this, 'setupFields' ) );
	}

	public function getArgs() {
		return $this->args;
	}

	public function getEvents() {
		return query_posts($this->args);
	}

	public function setArgsFromDisplayType() {
		global $wp_query;

		switch ( $this->display_type ) {
			case "past":
				$this->setPastDisplayTypeArgs();
				break;
			case "upcoming":
				$this->setUpcomingDisplayTypeArgs();
				break;
			case "month":
		   case "bydate":
				$this->setMonthDisplayTypeArgs();
		}
	}

	public function setupJoins($join) {
		global $wpdb;

		if ( get_query_var('post_type') != Events_Calendar_Pro::POSTTYPE ) {
			return $join;
		}
		add_filter( 'posts_join',		array( __CLASS__, 'events_search_join' ) );
		$join .= " LEFT JOIN {$wpdb->postmeta} as eventStart ON( {$wpdb->posts}.ID = eventStart.post_id AND eventStart.meta_key = '_EventStartDate') ";
		$join .= " LEFT JOIN {$wpdb->postmeta} as eventDuration ON( {$wpdb->posts}.ID = eventDuration.post_id AND eventDuration.meta_key = '_EventDuration') ";
		$join .= " LEFT JOIN {$wpdb->postmeta} as eventEnd ON( {$wpdb->posts}.ID = eventEnd.post_id AND eventEnd.meta_key = '_EventEndDate') ";
		
		return $join;
	}

	public static function setupFields( $fields ) {
		global $wpdb;

		if ( get_query_var('post_type') != Events_Calendar_Pro::POSTTYPE ) {
			return $fields;
		}

		$fields .= ", eventStart.meta_value as EventStartDate, IFNULL(DATE_ADD(CAST(eventStart.meta_value AS DATETIME), INTERVAL eventDuration.meta_value SECOND), eventEnd.meta_value) as EventEndDate ";
		return $fields;
	}
	
	public function setPastDisplayTypeArgs() {
		$this->args['end_date'] = date_i18n( DateUtils::DBDATETIMEFORMAT );
		add_filter('posts_orderby', array($this, 'setDescendingDisplayOrder'));
	}

	public function setUpcomingDisplayTypeArgs() {
		$this->args['start_date'] = date_i18n( DateUtils::DBDATETIMEFORMAT );
		add_filter('posts_orderby', array($this, 'setAscendingDisplayOrder'));
	}

	// month functions
	public function setMonthDisplayTypeArgs() {
		global $wp_query;
		global $sp_ecp;
		$this->args['posts_per_page'] = -1; // show ALL month posts
		$this->args['start_date'] = date_i18n( DateUtils::DBDATEFORMAT );
		$this->args['start_date'] = substr_replace( $this->args['start_date'], '01', -2 );

		if ( isset ( $wp_query->query_vars['eventDate'] ) )
			$this->args['start_date'] = $wp_query->query_vars['eventDate'] . "-01";

		$this->args['end_date'] = $sp_ecp->nextMonth($this->args['start_date']);

		add_filter('posts_orderby', array($this, 'setDescendingDisplayOrder'));
	}

	public function addEventConditions($where) {
		global $wp_query, $sp_ecp, $wpdb;

		// we can't store end date directly because it messes up the distinc clause
		$endDate = " IFNULL(DATE_ADD(CAST(eventStart.meta_value AS DATETIME), INTERVAL eventDuration.meta_value SECOND), eventEnd.meta_value) ";

		if($this->args['end_date'] && $this->args['start_date']) {
			$start_clause = $wpdb->prepare("(eventStart.meta_value >= %s AND eventStart.meta_value <= %s)", $this->args['start_date'], $this->args['end_date']);
			$end_clause = $wpdb->prepare("($endDate >= %s AND eventStart.meta_value <= %s )", $this->args['start_date'], $this->args['end_date']);
			$within_clause = $wpdb->prepare("(eventStart.meta_value < %s AND $endDate > %s )", $this->args['start_date'], $this->args['end_date']);
			$where .= " AND ($start_clause OR $end_clause OR $within_clause)";
		} else if($this->args['end_date']) {
			$start_clause = $wpdb->prepare("$endDate < %s", $this->args['end_date']);
			$where .= " AND $start_clause";
		} else if($this->args['start_date']) {
		   $end_clause = $wpdb->prepare("eventStart.meta_value > %s", $this->args['start_date']);
		   $where .= " AND $end_clause";
		}

		return $where;
	}

	public function setAscendingDisplayOrder($order_sql) {
		return "DATE(eventStart.meta_value) ASC, TIME(eventStart.meta_value) ASC";
	}

	public function setDescendingDisplayOrder($order_sql) {
		return "DATE(eventStart.meta_value) DESC, TIME(eventStart.meta_value) DESC";
	}
}
?>
