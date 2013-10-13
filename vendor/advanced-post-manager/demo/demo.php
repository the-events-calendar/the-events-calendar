<?php

/** I get included by the APM Demo plugin.
 * Helpful for demoing/testing.
 * See Tribe_Status_Type class below for a custom filter type example
 *
 */

class Tribe_Demo_APM {

	private $post_type = 'tribe_movie';
	private $textdomain = 'tribe-apm';

	public function __construct() {
		add_action( 'init', array($this, 'test_filters') );
		add_action( 'admin_notices', array($this, 'notice') );
	}

	public function notice() {
		$screen = get_current_screen();
		if ( 'edit-'.$this->post_type !== $screen->id ) {
			return;
		}
		global $wp_query;
		if ( (int) $wp_query->found_posts === 0 ) {
			$path = str_replace(ABSPATH, '', dirname(__FILE__));
			$url = home_url($path) . '/demo_data.xml';
			$import_url = admin_url('import.php');
			echo '<div id="messsage" class="updated"><p>';
			printf( __('It looks like you might not have any demo data. <a href="%s">Download our data</a> and use the <a href="%s">WordPress Importer</a>.', 'tribe-apm'), $url, $import_url );
			echo '</p></div>';
		}
	}

	public function test_filters() {
		$filter_args = array(
			'release_date' =>  array(
				'name' => 'Release Date',
				'meta' => '_release_date',
				'cast' => 'DATE',
				// If it's a date, optionally supply your own date_format for display purposes
				'date_format' => 'M j, Y'
			),
			'stars' => array(
				'name' => 'Stars',
				'meta' => '_stars',
				'options' => array(
					'0.5' => '½',
					'1' => '1',
					'1.5' => '1 ½',
					'2' => '2',
					'2.5' => '2 ½',
					'3' => '3',
					'3.5' => '3 ½',
					'4' => '4'
				),
				'cast' => 'DECIMAL',
#				'metabox_order' => 0
			),
			'director' => array(
				'name' => 'Director',
				'meta' => '_director',
				'metabox' => 'my_box'
			),
			'tribe_post_status' => array(
				'name' => 'Status',
				'custom_type' => 'post_status',
				'sortable' => true
			)
		);
		$boxes = array(
			'my_box' => 'Awesome Box'
		);

		$labels = array(
			'name' => 'Demo Movies',
			'singular_name' => 'Demo Movie',
			'add_new_item' => 'Add New Demo Movie'
		);
		register_post_type($this->post_type, array(
			'show_ui' => true,
			'labels' => $labels,
			'supports' => array('title')
		));
		register_taxonomy('tribe_studio', $this->post_type, array(
			'show_ui' => true,
			'hierarchical' => true,
			'labels' => array(
				'name' => 'Studios',
				'singluar_name' => 'Studio'
			)
		));
		register_taxonomy('tribe_genre', $this->post_type, array(
			'show_ui' => true,
			'hierarchical' => true,
			'labels' => array(
				'name' => 'Genres',
				'singluar_name' => 'Genre'
			)
		));
		global $cpt_filters;
		$cpt_filters = tribe_setup_apm($this->post_type, $filter_args, $boxes );
		#$cpt_filters->add_taxonomies = false;
	}

	public function log($data = array() ) {
		error_log(print_r($data,1));
	}

}
new Tribe_Demo_APM;

class Tribe_Status_Type {

	protected $key = 'tribe_post_status';
	protected $type = 'post_status';

	public function __construct() {
		$type = $this->type;

		add_filter( 'tribe_custom_column'.$type, array($this, 'column_value'), 10, 3 );
		add_filter( 'tribe_custom_row'.$type, array($this, 'form_row'), 10, 4 );
		add_filter( 'tribe_maybe_active'.$type, array($this, 'maybe_set_active'), 10, 3 );
		add_action( 'tribe_after_parse_query', array($this, 'parse_query'), 10, 2 );
		add_action( 'tribe_orderby_custom'.$type, array($this, 'orderby'), 10, 2 );
	}

	public function orderby($wp_query, $filter) {
		add_filter( 'posts_orderby', array($this, 'set_orderby'), 10, 2 );
	}

	public function set_orderby($orderby, $wp_query) {
		// run once
		remove_filter( 'posts_orderby', array($this, 'set_orderby'), 10, 2 );
		global $wpdb;
		list($by, $order) = explode(' ', trim($orderby) );
		$by = "{$wpdb->posts}.post_status";
		return $by . ' ' . $order;
	}

	public function parse_query($wp_query, $active) {
		if ( ! isset($active[$this->key]) ) {
			return;
		}
		$status = $active[$this->key]['value'];
		$wp_query->set('post_status', $status);
	}

	public function maybe_set_active($return, $key, $filter) {
		if ( isset($_POST[$key]) && ! empty($_POST[$key]) ) {
			return array('value' => $_POST[$key]);
		}
		return $return;
	}

	public function form_row($return, $key, $value, $filter) {
		$stati = get_post_stati(array('show_in_admin_status_list'=>true), 'objects');
		$args = array();
		foreach ( $stati as $k => $object ) {
			$args[$k] = $object->label;
		}
		return tribe_select_field($key, $args, $value['value'], true);
	}

	public function column_value($value, $column_id, $post_id) {
		$status = get_post_status($post_id);
		$status_object = get_post_status_object($status);
		return ( isset($status_object->label) ) ? $status_object->label : $status;
	}

	public function log($data = array() ) {
		error_log(print_r($data,1));
	}


}
new Tribe_Status_Type;





















