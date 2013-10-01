<?php

class ECP_APM_Filters {
	
	/**
	 * Class constructor, adds the actions and filters.
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'init', array($this, 'ecp_filters') );
		add_action( 'tribe_cpt_filters_after_init', array($this, 'default_columns') );
		add_filter( 'tribe_query_options', array( $this, 'query_options_for_date' ), 10, 3 );
	}
	
	/**
	 * Set the default columns if a custom set has not been created/being used.
	 *
	 * @param Tribe_APM $apm The passed APM instance.
	 * @return void
	 */
	public function default_columns($apm) {
		global $ecp_apm;
		if ( $ecp_apm === $apm ) {
			// Fallback is the order the columns fall back to if nothing was explicitly set
			// An array of column header IDs
			$ecp_apm->columns->set_fallback(array('title', 'ecp_organizer_filter_key', 'ecp_venue_filter_key', 'events-cats', 'recurring', 'start-date', 'end-date'));
		}
	}
	
	/**
	 * Create the events APM with the additional APM filters that TEC uses.
	 *
	 * @return void
	 */
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
			'ecp_start_date' => array(
				'name' => __('Start Date', 'tribe-events-calendar-pro'),
				'custom_type' => 'custom_date',
				'disable' => 'columns'
			),
			'ecp_end_date' => array(
				'name' => __('End Date', 'tribe-events-calendar-pro'),
				'custom_type' => 'custom_date',
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

	/**
	 * Comparison operators for comparing dates that TEC will need to use.
	 *
	 * @param array $options the current options.
	 * @param string $key
	 * @param mixed $filter
	 * @return array The options with the additional operators.
	 */
	function query_options_for_date( $options, $key, $filter ) {
		if ( 'ecp_start' == $key ) {
			$options = array( 'gte' => '>=', 'lte' => '<=' );
		}
		
		return $options;
	}
	
}
new ECP_APM_Filters;


class TribeDateFilter {

	protected $active = array();
	protected $type   = 'custom_date';

	private $query_search_options = array( 'is'  => 'Is',
	                                       'not' => 'Is Not',
	                                       'gte'  => 'On and After',
	                                       'lte'  => 'On and Before' );

	public function __construct() {
		$type = $this->type;
		add_filter( 'tribe_custom_row' . $type, array( $this, 'form_row' ), 10, 4 );
		add_filter( 'tribe_maybe_active' . $type, array( $this, 'maybe_set_active' ), 10, 3 );
		add_action( 'tribe_after_parse_query', array( $this, 'parse_query' ), 10, 2 );

	}

	public function form_row( $return, $key, $value, $filter ) {
		$value  = (array)$value;
		$value  = wp_parse_args( $value, array( 'is' => '', 'value' => '', 'is_date_field' => true ) );
		$return = tribe_select_field( 'is_' . $key, $this->query_search_options, $value['is'] );
		$return .= sprintf( '<input name="%s" value="%s" type="text" class="date tribe-datepicker" />', $key, esc_attr( $value['value'] ) );
		return $return;
	}

	public function maybe_set_active( $return, $key, $filter ) {
		if ( isset( $_POST[$key] ) && !empty( $_POST[$key] ) && isset( $_POST['is_' . $key] ) && !empty( $_POST['is_' . $key] ) ) {
			return array( 'value' => $_POST[$key], 'is' => $_POST['is_' . $key], 'is_date_field' => true );
		}
		return $return;
	}

	public function parse_query( $wp_query_current, $active ) {
		if ( empty( $active ) )
			return;

		global $wp_query;

		foreach ( $active as $key => $field ) {
			if ( isset( $field['is_date_field'] ) )
				$this->active[$key] = $field;

		}

		add_filter( 'posts_where', array( $this, 'where' ), 10, 2 );

	}

	public function where( $where, $wp_query ) {
		global $ecp_apm, $wpdb;
		// run once
		remove_filter( 'posts_where', array( $this, 'where' ), 10, 2 );

		foreach ( $this->active as $key => $active ) {

			$field = '';

			if ( $key === 'ecp_start_date' )
				$field = "$wpdb->postmeta.meta_value";
			if ( $key === 'ecp_end_date' )
				$field = "IFNULL(DATE_ADD(CAST($wpdb->postmeta.meta_value AS DATETIME), INTERVAL eventDuration.meta_value SECOND), eventEnd.meta_value)";

			if ( empty( $field ) )
				continue;

			$value = $active['value'];

			switch ( $active['is'] ) {
				case "is":
					$where .= $wpdb->prepare( " AND $field BETWEEN %s AND %s ", TribeDateUtils::beginningOfDay( $value ), TribeDateUtils::endOfDay( $value ) );
					break;
				case "not":
					$where .= $wpdb->prepare( " AND $field NOT BETWEEN %s AND %s ", TribeDateUtils::beginningOfDay( $value ), TribeDateUtils::endOfDay( $value ) );
					break;
				case "gte":
					$where .= $wpdb->prepare( " AND $field >= %s ", TribeDateUtils::beginningOfDay( $value ) );
					break;
				case "lte":
					$where .= $wpdb->prepare( " AND $field <= %s ", TribeDateUtils::endOfDay( $value ) );
					break;

			}
		}


		return $where;

	}

}

new TribeDateFilter;

class Tribe_Recur_Filter {
	protected $key = 'ecp_recur';
	protected $type = 'recur';
	protected $meta = '_EventRecurrence';

	protected $opts = array(
		'is' => 'Yes',
		'not' => 'No'
	);
	protected $not_recur = 's:4:"type";s:4:"None";';
	
	public function __construct() {
		$type = $this->type;
		add_filter( 'tribe_custom_row'.$type, array($this, 'form_row'), 10, 4 );
		add_filter( 'tribe_maybe_active'.$type, array($this, 'maybe_set_active'), 10, 3 );

		add_filter( 'posts_join', array($this, 'join_recur'), 10, 2);
		add_filter( 'posts_where', array( $this, 'where_recur' ) );

	}

	public function join_recur( $join, $wp_query ) {

		if ( empty( $_POST[$this->key] ) )
			return $join;

		global $wpdb;
		$join .= "LEFT JOIN {$wpdb->postmeta} AS recur_meta ON({$wpdb->posts}.ID = recur_meta.post_id AND recur_meta.meta_key='{$this->meta}') ";
		return $join;
	}

	public function where_recur( $where ) {
		if ( empty( $_POST[$this->key] ) )
			return $where;

		global $wpdb;


		if ( 'is' === $_POST[$this->key] ) {
			$where .= " AND ( recur_meta.meta_value NOT LIKE '%$this->not_recur%' AND recur_meta.meta_value <> '' )  ";
		} else {
			$where .= " AND ( recur_meta.meta_value LIKE '%$this->not_recur%' OR recur_meta.meta_value = '' ) ";
		}

		return $where;
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
		add_filter( 'tribe_maybe_active'.$type, array($this, 'maybe_set_active'), 10, 3 );

		add_filter( 'posts_join', array($this, 'join_venue'), 10, 2);
		add_filter( 'posts_where', array( $this, 'where_venue' ) );

	}
	
	public function maybe_set_active($return, $key, $filter) {
		if ( isset($_POST[$this->key]) && ! empty($_POST[$this->key]) ) {
			return $_POST[$this->key];
		}
		return $return;
	}

	public function join_venue( $join, $wp_query ) {
		if ( empty( $_POST[$this->key] ) )
			return $join;


		global $wpdb;
		$join .= " INNER JOIN {$wpdb->postmeta} AS venue_meta ON({$wpdb->posts}.ID = venue_meta.post_id AND venue_meta.meta_key='{$this->meta}') ";
		return $join;
	}

	public function where_venue( $where ) {
		if ( empty( $_POST[$this->key] ) )
			return $where;

		global $wpdb;

		$venues = (array) $_POST[$this->key];

		$ids_format_string = rtrim( str_repeat( '%d,', count( $venues ) ), ',' );

		$where .= $wpdb->prepare( " AND venue_meta.meta_value in ($ids_format_string) ", $venues );

		return $where;
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

		add_filter( 'posts_join', array($this, 'join_organizer'), 10, 2);
		add_filter( 'posts_where', array( $this, 'where_organizer' ) );

		add_filter( 'tribe_maybe_active'.$type, array($this, 'maybe_set_active'), 10, 3 );
	}
	

	public function maybe_set_active($return, $key, $filter) {
		if ( isset($_POST[$this->key]) && ! empty($_POST[$this->key]) ) {
			return $_POST[$this->key];
		}
		return $return;
	}


	public function join_organizer($join, $wp_query) {

		if ( empty( $_POST[$this->key] ) )
			return $join;

		global $wpdb;
		$join .= "LEFT JOIN {$wpdb->postmeta} AS organizer_meta ON({$wpdb->posts}.ID = organizer_meta.post_id AND organizer_meta.meta_key='{$this->meta}') ";
		return $join;
	}

	public function where_organizer( $where ) {
		if ( empty( $_POST[$this->key] ) )
			return $where;

		global $wpdb;

		$organizers = (array) $_POST[$this->key];

		$ids_format_string = rtrim( str_repeat( '%d,', count( $organizers ) ), ',' );

		$where .= $wpdb->prepare( " AND organizer_meta.meta_value in ($ids_format_string) ", $organizers );

		return $where;
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
