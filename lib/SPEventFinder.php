<?php
class SPEventFinder {
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
			$this->setupMetaQuery();
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

	private function setupMetaQuery() {
		$this->args['meta_query'][] = array('key'=>'_EventStartDate');
		$this->args['meta_query'][] = array('key'=>'_EventEndDate');
		add_filter('get_meta_sql', array($this, 'addEventConditions'));
	}

	
	public function setPastDisplayTypeArgs() {
		$this->args['end_date'] = date_i18n( Events_Calendar_Pro::DBDATETIMEFORMAT );
		add_filter('posts_orderby', array($this, 'setDescendingDisplayOrder'));
	}

	public function setUpcomingDisplayTypeArgs() {
		$this->args['start_date'] = date_i18n( Events_Calendar_Pro::DBDATETIMEFORMAT );
		add_filter('posts_orderby', array($this, 'setAscendingDisplayOrder'));
	}

	// month functions
	public function setMonthDisplayTypeArgs() {
		global $wp_query;
		global $sp_ecp;
		$this->args['posts_per_page'] = -1; // show ALL month posts
		$this->args['start_date'] = date_i18n( Events_Calendar_Pro::DBDATEFORMAT );
		$this->args['start_date'] = substr_replace( $this->args['start_date'], '01', -2 );

		if ( isset ( $wp_query->query_vars['eventDate'] ) )
			$this->args['start_date'] = $wp_query->query_vars['eventDate'] . "-01";

		$this->args['end_date'] = $sp_ecp->nextMonth($this->args['start_date']);

		add_filter('posts_orderby', array($this, 'setDescendingDisplayOrder'));
	}

	public function addEventConditions($meta_sql) {
		global $wp_query, $sp_ecp, $wpdb;

		if($this->args['end_date'] && $this->args['start_date']) {
			$start_clause = $wpdb->prepare("(wp_postmeta.meta_value >= %s AND wp_postmeta.meta_value <= %s)", $this->args['start_date'], $this->args['end_date']);
			$end_clause = $wpdb->prepare("(mt1.meta_value >= %s AND wp_postmeta.meta_value <= %s )", $this->args['start_date'], $this->args['end_date']);
			$within_clause = $wpdb->prepare("(wp_postmeta.meta_value < %s AND mt1.meta_value > %s )", $this->args['start_date'], $this->args['end_date']);
			$meta_sql['where'] .= " AND ($start_clause OR $end_clause OR $within_clause)";
		} else if($this->args['end_date']) {
			$start_clause = $wpdb->prepare("mt1.meta_value < %s", $this->args['end_date']);
			$meta_sql['where'] .= " AND $start_clause";
		} else if($this->args['start_date']) {
		   $end_clause = $wpdb->prepare("wp_postmeta.meta_value > %s", $this->args['start_date']);
		   $meta_sql['where'] .= " AND $end_clause";
		}

		return $meta_sql;
	}

	public function setAscendingDisplayOrder($order_sql) {
		return "DATE(wp_postmeta.meta_value) ASC, TIME(wp_postmeta.meta_value) ASC";
	}

	public function setDescendingDisplayOrder($order_sql) {
		return "DATE(wp_postmeta.meta_value) DESC, TIME(wp_postmeta.meta_value) DESC";
	}
}
?>
