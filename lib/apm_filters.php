<?php
class ECP_APM_Filters {
	
	public function __construct() {
		add_action( 'init', array($this, 'ecp_filters') );
		add_action( 'tribe_cpt_filters_after_init', array($this, 'default_columns') );
	}
	
	public function default_columns($apm) {
		global $ecp_apm;
		if ( $ecp_apm === $apm ) {
			// Fallback is the order the columns fall back to if nothing was explicitly set
			// An array of column header IDs
			$ecp_apm->columns->set_fallback(array('title', 'ecp_organizer_filter_key', 'ecp_venue_filter_key', 'events-cats', 'recurring', 'start-date', 'end-date'));
		}
	}
	
	public function ecp_filters() {
		$filter_args = array(
			'ecp_venue_filter_key'=>array(
				'name' => __('Venue', 'tribe-events-calendar-pro'),
				'custom_type' => 'ecp_venue_filter',
				'sortable' => 'true'
			),
			'ecp_organizer_filter_key'=>array(
				'name' => __('Organizer', 'tribe-events-calendar-pro'),
				'custom_type' => 'ecp_organizer_filter',
				'sortable' => 'true'
			),
			'ecp_start' => array(
				'name' => __('Start Date', 'tribe-events-calendar-pro'),
				'meta' => '_EventStartDate',
				'cast' => 'DATETIME',
				'disable' => 'columns'
			),
			'ecp_cost' => array(
				'name' => __('Event Cost', 'tribe-events-calendar-pro'),
				'meta' => '_EventCost',
				'cast' => 'NUMERIC'
			),
			'ecp_cat' => array(
				'name' => __('Event Cats', 'tribe-events-calendar-pro'),
				'taxonomy' => TribeEvents::TAXONOMY,
				'disable' => 'columns'
			),
			'ecp_title' => array(
				'name' => __('Title', 'tribe-events-calendar-pro'),
				'custom_type' => 'title',
				'disable' => 'columns'
			),
			'ecp_recur' => array(
				'name' => __('Recurring', 'tribe-events-calendar-pro'),
				'custom_type' => 'recur',
				'disable' => 'columns'
			),
			'ecp_content' => array(
				'name' => __('Description', 'tribe-events-calendar-pro'),
				'custom_type' => 'content',
				'disable' => 'columns'
			)
		);
		
		global $ecp_apm;
		$ecp_apm = tribe_setup_apm( TribeEvents::POSTTYPE, $filter_args );
		$ecp_apm->do_metaboxes = false;
		$ecp_apm->add_taxonomies = false;
	}
	
}
new ECP_APM_Filters;


class Tribe_Recur_Filter {
	protected $key = 'ecp_recur';
	protected $type = 'recur';
	protected $opts = array(
		'is' => 'Yes',
		'not' => 'No'
	);
	protected $not_recur = 'a:12:{s:4:"type";s:4:"None"';
	
	public function __construct() {
		$type = $this->type;
		add_filter( 'tribe_custom_row'.$type, array($this, 'form_row'), 10, 4 );
		add_filter( 'tribe_maybe_active'.$type, array($this, 'maybe_set_active'), 10, 3 );
		add_action( 'tribe_after_parse_query', array($this, 'parse_query'), 10, 2 );
	}
	
	public function parse_query($wp_query, $active) {
		if ( ! isset($active[$this->key]) ) {
			return;
		}
		$compare = ( 'is' === $active[$this->key] ) ? 'NOT LIKE' : 'LIKE';
		$meta_query = (array) $wp_query->get('meta_query');
		$meta_query[] = array(
			'key' => '_EventRecurrence',
			'value' => $this->not_recur,
			'compare' => $compare
		);
		$wp_query->set('meta_query', $meta_query);
	}
	
	public function maybe_set_active($return, $key, $filter) {
		if ( isset($_POST[$key]) && ! empty($_POST[$key])) {
			return $_POST[$key];
		}
		return $return;
	}
	
	public function form_row($return, $key, $value, $filter) {
		// in case we have a blank row
		$value = (string) $value;
		return tribe_select_field($this->key, $this->opts, $value);
	}
}
new Tribe_Recur_Filter;

class Tribe_Content_Filter {
	protected $key = 'ecp_content';
	protected $type = 'content';
	protected $is_key = 'is_ecp_content';

	public function __construct() {
		$type = $this->type;
		add_filter( 'tribe_custom_row'.$type, array($this, 'form_row'), 10, 4 );
		add_filter( 'tribe_maybe_active'.$type, array($this, 'maybe_set_active'), 10, 3 );
		add_action( 'tribe_after_parse_query', array($this, 'parse_query'), 10, 2 );
	}
	
	public function parse_query($wp_query, $active) {
		if ( ! isset($active[$this->key]) ) {
			return;
		}
		$this->active = $active[$this->key];
		add_filter( 'posts_where', array($this, 'where'), 10, 2 );
	}
	
	public function where($where, $wp_query) {
		global $ecp_apm, $wpdb;
		// run once
		remove_filter( 'posts_where', array($this, 'where'), 10, 2 );
		$value = "%{$this->active}%";
		$where .= $wpdb->prepare( " AND {$wpdb->posts}.post_content LIKE %s ", $value );
		return $where;
	}
	
	public function maybe_set_active($return, $key, $filter) {
		if ( isset($_POST[$this->key]) && ! empty($_POST[$this->key]) ) {
			return $_POST[$this->key];
		}
		return $return;
	}
	
	public function form_row($return, $key, $value, $filter) {
		// in case we have a blank row
		$value = (string) $value;
		return sprintf('<input name="%s" value="%s" type="text" />', $this->key, esc_attr($value) );
	}
}
new Tribe_Content_Filter;

class Tribe_Title_Filter {
	protected $key = 'ecp_title';
	protected $type = 'title';
	protected $is_key = 'is_ecp_title';
	
	private $query_search_options = array(
		'like' => 'Search',
		'is' => 'Is',
		'not' => 'Is Not',
		'gt' => '>',
		'lt' => '<',
		'gte' => '>=',
		'lte' => '<='
	);

	public function __construct() {
		$type = $this->type;
		add_filter( 'tribe_custom_row'.$type, array($this, 'form_row'), 10, 4 );
		add_filter( 'tribe_maybe_active'.$type, array($this, 'maybe_set_active'), 10, 3 );
		add_action( 'tribe_after_parse_query', array($this, 'parse_query'), 10, 2 );
		
	}
	
	public function parse_query($wp_query, $active) {
		if ( ! isset($active[$this->key]) ) {
			return;
		}
		$this->active = $active[$this->key];
		add_filter( 'posts_where', array($this, 'where'), 10, 2 );
	}
	
	public function where($where, $wp_query) {
		global $ecp_apm, $wpdb;
		// run once
		remove_filter( 'posts_where', array($this, 'where'), 10, 2 );
		$compare = $ecp_apm->filters->map_query_option($this->active['is']);
		$value = $this->active['value'];
		if ( 'LIKE' === $compare ) {
			$value = "%$value%";
		}
		$where .= $wpdb->prepare( " AND {$wpdb->posts}.post_title {$compare} %s ", $value );
		return $where;
	}
	
	public function maybe_set_active($return, $key, $filter) {
		if ( isset($_POST[$this->key]) && ! empty($_POST[$this->key]) && isset($_POST[$this->is_key]) && ! empty($_POST[$this->is_key])  ) {
			return array('value' => $_POST[$this->key], 'is' => $_POST[$this->is_key]);
		}
		return $return;
	}
	
	public function form_row($return, $key, $value, $filter) {
		// in case we have a blank row
		$value = (array) $value;
		$value = array_merge(array('is' => '', 'value' => ''), $value);
		$return = tribe_select_field($this->is_key, $this->query_search_options, $value['is']);
		$return .= sprintf('<input name="%s" value="%s" type="text" />', $this->key, esc_attr($value['value']) );
		return $return;
	}
	
	public function log($data = array() ) {
		error_log(print_r($data,1));
	}
}
new Tribe_Title_Filter;

class Tribe_Venue_Filter {
	protected $key = 'ecp_venue_filter_key';
	protected $type = 'ecp_venue_filter';
	protected $meta = '_EventVenueID';
	
	public function __construct() {
		$type = $this->type;
		
		add_filter( 'tribe_custom_column'.$type, array($this, 'column_value'), 10, 3 );
		add_filter( 'tribe_custom_row'.$type, array($this, 'form_row'), 10, 4 );
		add_action( 'tribe_orderby_custom'.$type, array($this, 'orderby'), 10, 2 );
		add_filter( 'tribe_maybe_active'.$type, array($this, 'maybe_set_active'), 10, 3 );
		add_action( 'tribe_after_parse_query', array($this, 'parse_query'), 10, 2 );
	}
	
	public function parse_query($wp_query, $active) {
		if ( ! isset($active[$this->key]) ) {
			return;
		}
		$value = $active[$this->key];
		$compare = is_array($value) ? 'IN' : '=';
		$meta_query = (array) $wp_query->get('meta_query');
		$meta_query[] = array(
			'key' => $this->meta,
			'value' => $value,
			'compare' => $compare
		);
		$wp_query->set('meta_query', $meta_query);
		
	}
	
	public function maybe_set_active($return, $key, $filter) {
		if ( isset($_POST[$this->key]) && ! empty($_POST[$this->key]) ) {
			return $_POST[$this->key];
		}
		return $return;
	}
	
	public function orderby($wp_query, $filter) {
		add_filter( 'posts_orderby', array($this, 'set_orderby'), 10, 2 );
		add_filter( 'posts_join', array($this, 'join_venue'), 10, 2);
	}

	public function join_venue($join, $wp_query) {
		global $wpdb;
		$join .= "LEFT JOIN {$wpdb->postmeta} AS venue_meta ON({$wpdb->posts}.ID = venue_meta.post_id AND venue_meta.meta_key='{$this->meta}') "; 
		$join .= "LEFT JOIN {$wpdb->posts} AS venue ON (venue_meta.meta_value = venue.ID) ";
		return $join;
	}
	
	public function set_orderby($orderby, $wp_query) {
		// run once
		remove_filter( 'posts_orderby', array($this, 'set_orderby'), 10, 2 );
		global $wpdb;
		list($by, $order) = explode(' ', trim($orderby) );
		$by = "venue.post_title";
		return $by . ' ' . $order;
	}
	
	public function form_row($return, $key, $value, $filter) {
		$venues = get_posts( array( 'post_type' => TribeEvents::VENUE_POST_TYPE, 'nopaging' => true ) );

		$args = array();

		foreach ( $venues as $venues ) {
			$args[$venues->ID] = $venues->post_title;
		}
		
		return tribe_select_field($key, $args, $value, true);
	}

	public function column_value($value, $column_id, $post_id) {
		$venue_id = get_post_meta($post_id, '_EventVenueID', true);
		$venue = get_post( $venue_id );

		if( $venue_id && $venue )
			return $venue->post_title;
		else
			return '';
	}
	
	public function log($data = array() ) {
		error_log(print_r($data,1));
	}
}
new Tribe_Venue_Filter;

class Tribe_Organizer_Filter {
	protected $key = 'ecp_organizer_filter_key';
	protected $type = 'ecp_organizer_filter';
	protected $meta = '_EventOrganizerID';
	
	public function __construct() {
		$type = $this->type;
		
		add_filter( 'tribe_custom_column'.$type, array($this, 'column_value'), 10, 3 );
		add_filter( 'tribe_custom_row'.$type, array($this, 'form_row'), 10, 4 );
		add_action( 'tribe_orderby_custom'.$type, array($this, 'orderby'), 10, 2 );
		add_filter( 'tribe_maybe_active'.$type, array($this, 'maybe_set_active'), 10, 3 );
		add_action( 'tribe_after_parse_query', array($this, 'parse_query'), 10, 2 );
	}
	
	public function parse_query($wp_query, $active) {
		if ( ! isset($active[$this->key]) ) {
			return;
		}
		$value = $active[$this->key];
		$compare = is_array($value) ? 'IN' : '=';
		$meta_query = (array) $wp_query->get('meta_query');
		$meta_query[] = array(
			'key' => $this->meta,
			'value' => $value,
			'compare' => $compare
		);
		$wp_query->set('meta_query', $meta_query);
		
	}
	
	public function maybe_set_active($return, $key, $filter) {
		if ( isset($_POST[$this->key]) && ! empty($_POST[$this->key]) ) {
			return $_POST[$this->key];
		}
		return $return;
	}
	
	public function orderby($wp_query, $filter) {
		add_filter( 'posts_orderby', array($this, 'set_orderby'), 10, 2 );
		add_filter( 'posts_join', array($this, 'join_organizer'), 10, 2);
	}

	public function join_organizer($join, $wp_query) {
		global $wpdb;
		$join .= "LEFT JOIN {$wpdb->postmeta} AS organizer_meta ON({$wpdb->posts}.ID = organizer_meta.post_id AND organizer_meta.meta_key='{$this->meta}') "; 
		$join .= "LEFT JOIN {$wpdb->posts} AS organizer ON (organizer_meta.meta_value = organizer.ID) ";
		return $join;
	}
	
	public function set_orderby($orderby, $wp_query) {
		// run once
		remove_filter( 'posts_orderby', array($this, 'set_orderby'), 10, 2 );
		global $wpdb;
		list($by, $order) = explode(' ', trim($orderby) );
		$by = "organizer.post_title";
		return $by . ' ' . $order;
	}
	
	public function form_row($return, $key, $value, $filter) {
		$organizers = get_posts( array( 'post_type' => TribeEvents::ORGANIZER_POST_TYPE, 'nopaging' => true ) );

		$args = array();

		foreach ( $organizers as $organizers ) {
			$args[$organizers->ID] = $organizers->post_title;
		}
		
		return tribe_select_field($key, $args, $value, true);
	}

	public function column_value($value, $column_id, $post_id) {
		$organizer_id = get_post_meta($post_id, '_EventOrganizerID', true);
		$organizer = get_post( $organizer_id );

		if( $organizer_id && $organizer )
			return $organizer->post_title;
		else
			return $value;
	}
	
	public function log($data = array() ) {
		error_log(print_r($data,1));
	}
}
new Tribe_Organizer_Filter;
