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
				'name'=>'Venue',
				'custom_type' => 'ecp_venue_filter',
				'sortable' => 'true'
			),
			'ecp_organizer_filter_key'=>array(
				'name'=>'Organizer',
				'custom_type' => 'ecp_organizer_filter',
				'sortable' => 'true'
         )
      );
		
		global $ecp_apm;
		$ecp_apm = tribe_setup_apm(TribeEvents::POSTTYPE, $filter_args );
		$ecp_apm->do_metaboxes = false;
		$ecp_apm->add_taxonomies = false;
	}

	public function log($data = array() ) {
		error_log(print_r($data,1));
	}
	
}
new ECP_APM_Filters;

class Tribe_Venue_Filter {
	
	protected $key = 'ecp_venue_filter';
	protected $type = 'ecp_venue_filter';
	
	public function __construct() {
		$type = $this->type;
		
		add_filter( 'tribe_custom_column'.$type, array($this, 'column_value'), 10, 3 );
		add_filter( 'tribe_custom_row'.$type, array($this, 'form_row'), 10, 4 );
		add_action( 'tribe_orderby_custom'.$type, array($this, 'orderby'), 10, 2 );
	}
	
	public function orderby($wp_query, $filter) {
		add_filter( 'posts_orderby', array($this, 'set_orderby'), 10, 2 );
		add_filter( 'posts_join', array($this, 'join_venue'), 10, 2 );
	}

   public function join_venue($join, $wp_query) {
      $join .= "LEFT JOIN wp_postmeta AS venue_meta ON(wp_posts.ID = venue_meta.post_id AND venue_meta.meta_key='_EventVenueID') "; 
      $join .= "LEFT JOIN wp_posts AS venue ON (venue_meta.meta_value = venue.ID) ";
      return $join;
   }
	
	public function set_orderby($orderby, $wp_query) {
		// run once
		remove_filter( 'posts_orderby', array($this, 'set_orderby'), 10, 2 );
		global $wpdb;
		list($by, $order) = explode(' ', trim($orderby) );
      $by = 'venue.post_title';
		return $by . ' ' . $order;
	}
	
	public function form_row($return, $key, $value, $filter) {
		$venues = get_posts( array( 'post_type'=>TribeEvents::VENUE_POST_TYPE ) );

		foreach ( $venues as $venue ) {
			$args[$venue->ID] = $venue->post_title;
		}
		
		return tribe_select_field($key, $args, $value['value'], true);
	}

	public function column_value($value, $column_id, $post_id) {
      $venue_id = get_post_meta($post_id, '_EventVenueID', true);
		$venue = get_post( $venue_id );

		if( $venue_id && $venue )
			echo $venue->post_title;
      else
         echo "--";
   }
	
	public function log($data = array() ) {
		error_log(print_r($data,1));
	}
	
	
}
new Tribe_Venue_Filter;

class Tribe_Organizer_Filter {
	protected $key = 'ecp_organizer_filter';
	protected $type = 'ecp_organizer_filter';
	
	public function __construct() {
		$type = $this->type;
		
		add_filter( 'tribe_custom_column'.$type, array($this, 'column_value'), 10, 3 );
		add_filter( 'tribe_custom_row'.$type, array($this, 'form_row'), 10, 4 );
		add_action( 'tribe_orderby_custom'.$type, array($this, 'orderby'), 10, 2 );
	}
	
	public function orderby($wp_query, $filter) {
		add_filter( 'posts_orderby', array($this, 'set_orderby'), 10, 2 );
      add_filter( 'posts_join', array($this, 'join_organizer'), 10, 2);
	}

   public function join_organizer($join, $wp_query) {
      $join .= "LEFT JOIN wp_postmeta AS organizer_meta ON(wp_posts.ID = organizer_meta.post_id AND organizer_meta.meta_key='_EventOrganizerID') "; 
      $join .= "LEFT JOIN wp_posts AS organizer ON (organizer_meta.meta_value = organizer.ID) ";
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
		$organizers = get_posts( array( 'post_type'=>TribeEvents::ORGANIZER_POST_TYPE ) );

		foreach ( $organizers as $organizers ) {
			$args[$organizers->ID] = $organizers->post_title;
		}
		
		return tribe_select_field($key, $args, $value['value'], true);
	}

	public function column_value($value, $column_id, $post_id) {
      $organizer_id = get_post_meta($post_id, '_EventOrganizerID', true);
		$org = get_post( $organizer_id );

		if( $organizer_id && $org )
			echo $org->post_title;
      else
         echo "--";
	}
	
	public function log($data = array() ) {
		error_log(print_r($data,1));
	}
}
new Tribe_Organizer_Filter;
