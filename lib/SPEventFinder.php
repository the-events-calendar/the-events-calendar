<?php
class SPEventFinder {
	private $args;
	private $display_type;
	
	function __construct($args, $display_type = null) {
			// eventCat becomes a standard taxonomy query - will need to deprecate and update views eventually
			if ($args['eventCat']) {
				$tax_field = is_numeric($args['eventCat']) ? "id" : "name";
				$args['tax_query'] = array(
					 array('taxonomy'=>Events_Calendar_Pro::TAXONOMY, 'field'=>$tax_field, 'terms'=>$args['eventCat'])
				);
			}

			// proprietary metaKeys go to standard meta
			if ($args['metaKey'])
				$args['meta_query'] = array(
					 array('key'=>$args['metaKey'], 'value'=>$args['metaValue'])
				);

			$defaults = array(
				'posts_per_page' => get_option( 'posts_per_page', 10 ),
				'tax_query' => null,
				'meta_query' => array(),
				'time_order' => $this->order,
				'post_type' => Events_Calendar_Pro::POSTTYPE
			);
			
			$this->args = wp_parse_args( $args, $defaults);
			$this->display_type = $display_type ? $display_type : $args['eventDisplay'];
			$this->setArgsFromDisplayType();
	}

	public function getArgs() {
		return $this->args;
	}

	public function getEvents() {
		return query_posts($this->args);
	}

	public function setArgsFromDisplayType() {
		global $wp_query;
echo $this->display_type;
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

			default:

		}
	}
	
	public function setPastDisplayTypeArgs() {
		// we need the start date and end date meta
		$this->args['meta_query'][] = array('key'=>'_EventStartDate');
		$this->args['meta_query'][] = array('key'=>'_EventEndDate');

		// we need to join our meta queries by OR
		add_filter('get_meta_sql', array($this, 'addPastDisplayTypeDateConditions'));
		add_filter('posts_orderby', array($this, 'setDescendingDisplayOrder'));
	}

	public function addPastDisplayTypeDateConditions($meta_sql) {
		global $wp_query, $sp_ecp, $wpdb;

		// use current date
		$date = date_i18n( Events_Calendar_Pro::DBDATETIMEFORMAT ); // TODO: Switch over to timestamps

		$start_clause = $wpdb->prepare("mt1.meta_value < %s", $date);
		$meta_sql['where'] .= " AND $start_clause";

		return $meta_sql;
	}

	public function setUpcomingDisplayTypeArgs() {
		// we need the start date and end date meta
		$this->args['meta_query'][] = array('key'=>'_EventStartDate');
		$this->args['meta_query'][] = array('key'=>'_EventEndDate');

		// we need to join our meta queries by OR
		add_filter('get_meta_sql', array($this, 'addUpcomingDisplayTypeDateConditions'));
		add_filter('posts_orderby', array($this, 'setAscendingDisplayOrder'));
	}

	public function addUpcomingDisplayTypeDateConditions($meta_sql) {
		global $wp_query, $sp_ecp, $wpdb;

		// use current date
		$date = date_i18n( Events_Calendar_Pro::DBDATETIMEFORMAT ); // TODO: Switch over to timestamps

		$start_clause = $wpdb->prepare("(wp_postmeta.meta_value > %s)", $date);
		$end_clause = $wpdb->prepare("(wp_postmeta.meta_value < %s AND mt1.meta_value > %s )", $date, $date);
		$meta_sql['where'] .= " AND ($start_clause OR $end_clause)";

		return $meta_sql;
	}

	// month functions
	public function setMonthDisplayTypeArgs() {
		global $wp_query;
		global $sp_ecp;
		$this->args['posts_per_page'] = -1; // show ALL month posts

		// we need the start date and end date meta
		$this->args['meta_query'][] = array('key'=>'_EventStartDate');
		$this->args['meta_query'][] = array('key'=>'_EventEndDate');

		$this->args['eventDate'] = date_i18n( Events_Calendar_Pro::DBDATEFORMAT );
		$this->args['eventDate'] = substr_replace( $date, '01', -2 );

		if ( isset ( $wp_query->query_vars['eventDate'] ) )
			$this->args['eventDate'] = $wp_query->query_vars['eventDate'] . "-01";

		// we need to join our meta queries by OR
		add_filter('get_meta_sql', array($this, 'addMonthDisplayTypeDateConditions'));
		add_filter('posts_orderby', array($this, 'setDescendingDisplayOrder'));
	}

	public function setAscendingDisplayOrder($order_sql) {
		return "DATE(wp_postmeta.meta_value) ASC, TIME(wp_postmeta.meta_value) ASC";
	}

	public function setDescendingDisplayOrder($order_sql) {
		return "DATE(wp_postmeta.meta_value) DESC, TIME(wp_postmeta.meta_value) DESC";
	}

	public function addMonthDisplayTypeDateConditions($meta_sql) {
		global $wp_query, $sp_ecp, $wpdb;
		$date = $this->args['eventDate'];

		$start_clause = $wpdb->prepare("(wp_postmeta.meta_value >= %s AND wp_postmeta.meta_value <= %s)", $date, $sp_ecp->nextMonth($date));
		$end_clause = $wpdb->prepare("(mt1.meta_value >= %s AND wp_postmeta.meta_value <= %s )", $date, $sp_ecp->nextMonth($date));
		$within_clause = $wpdb->prepare("(wp_postmeta.meta_value < %s AND mt1.meta_value > %s )", $date, $sp_ecp->nextMonth($date));
		$meta_sql['where'] .= " AND ($start_clause OR $end_clause OR $within_clause)";

		return $meta_sql;
	}
}
?>
