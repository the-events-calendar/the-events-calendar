<?php
/*-------------------------------------------------------------------------------------
* File description: Main class for Geo Location functionality
*
*
* Created by:  Daniel Dvorkin
* For:         Modern Tribe Inc . ( http://tri.be/)
*
* Date: 		9 / 18 / 12 12:31 PM
*-------------------------------------------------------------------------------------*/

class TribeEventsGeoLoc {

	const LAT                  = '_VenueLat';
	const LNG                  = '_VenueLng';
	const ADDRESS              = '_VenueGeoAddress';
	const OPTIONNAME           = 'tribe_geoloc_options';
	const ESTIMATION_CACHE_KEY = 'geoloc_center_point_estimation';
	const EARTH_RADIO          = 6371; // IN KMS.

	protected static $options;
	public $rewrite_slug;

	private $selected_geofence;

	function __construct() {

		$this->rewrite_slug = $this->getOption( 'geoloc_rewrite_slug', 'map' );

		add_filter( 'generate_rewrite_rules', array( $this, 'add_routes' ) );

		add_action( 'tribe_events_venue_updated', array( $this, 'save_venue_geodata' ), 10, 2 );
		add_action( 'tribe_events_venue_created', array( $this, 'save_venue_geodata' ), 10, 2 );

		add_action( 'wp_ajax_geosearch', array( $this, 'ajax_geosearch' ) );
		add_action( 'wp_ajax_nopriv_geosearch', array( $this, 'ajax_geosearch' ) );

		add_filter( 'tribe-events-bar-views', array( $this, 'setup_view_for_bar' ), 25, 1 );
		add_filter( 'tribe-events-bar-filters', array( $this, 'setup_geoloc_filter_in_bar' ), 1, 1 );
		add_action( 'wp_enqueue_scripts', array( $this, 'scripts' ) );
		add_filter( 'tribe_events_pre_get_posts', array( $this, 'setup_geoloc_in_query' ) );

		add_action( 'tribe_events_filters_create_filters', array( $this, 'setup_geoloc_filter_in_filters' ), 1 );

		add_filter( 'tribe_settings_tab_fields', array( $this, 'inject_settings' ), 10, 2 );

		add_filter( 'tribe_events_list_inside_before_loop', array( $this, 'add_event_distance' ) );

	}

	public function setup_geoloc_filter_in_filters() {
		if ( !tribe_get_option( 'hideLocationSearch', false ) ) {
			$current_filters = get_option( 'tribe_events_filters_current_active_filters', TribeEventsFilterView::instance()->getDefaultFilters() );

			$distances = apply_filters( 'geoloc-values-for-filters', array( '5'    => '5 miles',
																			'10'   => '10 miles',
																			'25'   => '25 miles',
																			'50'   => '50 miles',
																			'100'  => '100 miles',
																			'250'  => '250 miles' ) );
			
			$distances_values = array();
			foreach( $distances as $value => $name ) {
				$distances_values[] = array(
					'name' => $name,
					'value' => $value,
				);
			}

			$geoloc_filter_array = array( 'name'   => __( 'Distance', 'tribe-events-calendar-pro' ),
										  'slug'   => 'geofence',
										  'values' => $distances_values, );

			$geoloc_filter_array['type'] = isset( $current_filters[$geoloc_filter_array['slug']]['type'] ) ? $current_filters[$geoloc_filter_array['slug']]['type'] : 'select';
			$geoloc_filter_array['title'] = isset( $current_filters[$geoloc_filter_array['slug']]['title'] ) ? $current_filters[$geoloc_filter_array['slug']]['title'] : $geoloc_filter_array['name'];

			$geoloc_filter_array['admin_form'] = sprintf( __( 'Title: %s', 'tribe-events-calendar-pro' ), '<input type="text" name="title" value="' . $geoloc_filter_array['title'] . '">' );
			$geoloc_filter_array['admin_form'] .= '<br />';
			$geoloc_filter_array['admin_form'] .= sprintf( __( '%sType: %s', 'tribe-events-calendar-pro' ), '<br />', '<br /><label><input type="radio" name="type" value="select" ' . checked( $geoloc_filter_array['type'], 'select', false ) . ' /> ' . __( 'Select Dropdown', 'tribe-events-calendar-pro' ) .'</label><br />' );
			$geoloc_filter_array['admin_form'] .= '<label><input type="radio" name="type" value="radio" ' . checked( $geoloc_filter_array['type'], 'radio', false ) . ' /> ' . __( 'Radio Buttons', 'tribe-events-calendar-pro' ) .'</label><br />';
				
			$geoloc_filter = new TribeEventsFilter( $geoloc_filter_array['name'], $geoloc_filter_array['slug'], $geoloc_filter_array['values'], $geoloc_filter_array['type'], $geoloc_filter_array['admin_form'], $geoloc_filter_array['title'] );

			if ( isset( $geoloc_filter->currentValue ) ) {
				$this->selected_geofence = tribe_convert_units( $geoloc_filter->currentValue, 'miles', 'kms' );
				add_filter( 'tribe_geoloc_geofence', array( $this, 'setup_geofence_in_query' ) );
			}
		}
	}

	public function setup_geofence_in_query( $distance ) {
		if ( !empty( $this->selected_geofence ) ) {
			$distance = $this->selected_geofence;
		}
		return $distance;
	}

	public function scripts() {
		if ( tribe_is_event_query() ) {
			Tribe_PRO_Template_Factory::asset_package( 'ajax-maps' );
		}
	}


	public function inject_settings( $args, $id ) {

		if ( $id == 'general' ) {

			// we want to inject the map default distance and unit into the map section directly after "enable Google Maps" 
			$args = TribeEvents::array_insert_after_key( 'embedGoogleMaps', $args, array( 
				'geoloc_default_geofence' => array( 
					'type'            => 'text',
					'label'           => __( 'Map view search distance limit', 'tribe-events-calendar-pro' ),
					'size'            => 'small',
					'tooltip'         => __( 'Set the distance that the location search covers (find events within X distance units of location search input).', 'tribe-events-calendar-pro' ),
					'default'         => '25',
					'class'           => '',
					'validation_type' => 'number_or_percent' ),
				'geoloc_default_unit' => array( 
					'type'            => 'dropdown',
					'label'           => __( 'Map view distance unit', 'tribe-events-calendar-pro' ),
					'validation_type' => 'options',
					'size'            => 'small',
					'default'         => 'miles',
					'options'         => apply_filters( 'tribe_distance_units', array( 'miles' => __( 'Miles', 'tribe-events-calendar-pro' ),
																						'kms'   => __( 'Kilometers', 'tribe-events-calendar-pro' ) ) ) ) ) 
				);

		} elseif ( $id == 'display' ) {
			$args = TribeEvents::array_insert_after_key( 'viewOption', $args, array(
				'hideLocationSearch' => array( 
					'type' => 'checkbox_bool',
					'label' => __( 'Hide location search', 'tribe-events-calendar-pro' ),
					'tooltip' => __( 'Removes location search field from the events bar.', 'tribe-events-calendar-pro' ),
					'default' => false,
					'validation_type' => 'boolean',
				),
			) );
	  	}

		return $args;
	}

	public function setup_view_for_bar( $views ) {
		$views[] = array( 'displaying' => 'map', 'event_bar_hook' => 'tribe_events_list_the_title', 'anchor'=> 'Map', 'url' => tribe_get_mapview_link() );
		return $views; 
	}

	public function setup_geoloc_filter_in_bar( $filters ) {
		if ( tribe_is_map() || !tribe_get_option( 'hideLocationSearch', false ) ) {
			$value = "";
			if ( !empty( $_REQUEST['tribe-bar-geoloc'] ) ) {
				$value = $_REQUEST['tribe-bar-geoloc'];
			}

			$lat = "";
			if ( !empty( $_REQUEST['tribe-bar-geoloc-lat'] ) ) {
				$lat = $_REQUEST['tribe-bar-geoloc-lat'];
			}

			$lng = "";
			if ( !empty( $_REQUEST['tribe-bar-geoloc-lng'] ) ) {
				$lng = $_REQUEST['tribe-bar-geoloc-lng'];
			}

			$filters[] = array( 'name'    => 'tribe-bar-geoloc',
								'caption' => __( 'Near', 'tribe-events-calendar-pro' ),
								'html'    => '<input type="hidden" name="tribe-bar-geoloc-lat" id="tribe-bar-geoloc-lat" value="' . esc_attr( $lat ) . '" /><input type="hidden" name="tribe-bar-geoloc-lng" id="tribe-bar-geoloc-lng" value="' . esc_attr( $lng ) . '" /><input type="text" name="tribe-bar-geoloc" id="tribe-bar-geoloc" value="' . esc_attr( $value ) . '" placeholder="Location">' );
		}
		return $filters;
	}

	public function is_geoloc_query() {
		return ( !empty( $_REQUEST['tribe-bar-geoloc-lat'] ) && !empty( $_REQUEST['tribe-bar-geoloc-lng'] ) );
	}

	public function setup_geoloc_in_query( $query ) {

		$force = false;
		if ( !empty( $_REQUEST['tribe-bar-geoloc-lat'] ) && !empty( $_REQUEST['tribe-bar-geoloc-lng'] ) ) {
			$force  = true;
			$venues = $this->get_venues_in_geofence( $_REQUEST['tribe-bar-geoloc-lat'], $_REQUEST['tribe-bar-geoloc-lng'] );
		} else if ( TribeEvents::instance()->displaying == 'map' || ( !empty( $query->query_vars['eventDisplay'] ) && $query->query_vars['eventDisplay'] == 'map' ) ) {
			// Show only venues that have geoloc info
			$force  = true;
			//Get all geoloc'ed venues
			$venues = $this->get_venues_in_geofence( 1, 1, self::EARTH_RADIO * M_PI);
		}

		if ( $force ) {
			if ( empty( $venues ) )
				$venues = -1;

			$meta_query = array( 'key'     => '_EventVenueID',
			                     'value'   => $venues,
			                     'type'    => 'NUMERIC',
			                     'compare' => 'IN' );

			if ( empty( $query->query_vars['meta_query'] ) ) {
				$query->set( 'meta_query', array( $meta_query ) );
			} else {
				$query->query_vars['meta_query'][] = $meta_query;
			}
		}

		return $query;

	}


	public function add_routes( $wp_rewrite ) {
		$tec = TribeEvents::instance();

		$base = trailingslashit( $tec->getOption( 'eventsSlug', 'events' ) );
		$baseTax = trailingslashit( $tec->taxRewriteSlug );
		$baseTax = "(.*)" . $baseTax . "(?:[^/]+/)*";
		$baseTag = trailingslashit( $tec->tagRewriteSlug );
		$baseTag = "(.*)" . $baseTag;
		
		$newRules = array();

		$newRules[$base . $this->rewrite_slug] = 'index.php?post_type=' . TribeEvents::POSTTYPE . '&eventDisplay=map';
		$newRules[$baseTax . '([^/]+)/' . $this->rewrite_slug . '/?$'] = 'index.php?tribe_events_cat=' . $wp_rewrite->preg_index(2) . '&post_type=' . TribeEvents::POSTTYPE . '&eventDisplay=map';
		$newRules[$baseTag . '([^/]+)/' . $this->rewrite_slug . '/?$'] = 'index.php?tag=' . $wp_rewrite->preg_index(2) . '&post_type=' . TribeEvents::POSTTYPE . '&eventDisplay=map';
		
		$wp_rewrite->rules = $newRules + $wp_rewrite->rules;
	}

	public function add_event_distance ( $html ) {
		global $post;
			if ( !empty( $post->distance ) )
				$html .= '<span class="tribe-events-distance">'. tribe_get_distance_with_unit( $post->distance ) .'</span>';
		return $html;
	}	

	// public function setup_geoloc_template() {
	// 	remove_action( 'the_content', array( $this, 'setup_geoloc_template' ) );
	// 	$this->scripts();
	// 	$pro = TribeEventsPro::instance();

	// 	include $pro->pluginPath . 'views/hooks/map.php';

	// 	return $pro->pluginPath . 'views/map.php';
	// }

	// public function setup_geoloc_title( $title ) {
	// 	return __( 'Upcoming Events', 'tribe-events-calendar-pro' );
	// }

	function save_venue_geodata( $venueId, $data ) {


		$_address  = ( ! empty( $data["Address"] ) )  ? $data["Address"]  : '';
		$_city     = ( ! empty( $data["City"] ) )     ? $data["City"]     : '';
		$_province = ( ! empty( $data["Province"] ) ) ? $data["Province"] : '';
		$_state    = ( ! empty( $data["State"] ) )    ? $data["State"]    : '';
		$_zip      = ( ! empty( $data["Zip"] ) )      ? $data["Zip"]      : '';
		$_country  = ( ! empty( $data["Country"] ) )  ? $data["Country"]  : '';

		$address = trim( $_address . ' ' . $_city . ' ' . $_province . ' ' . $_state . ' ' . $_zip . ' ' . $_country );

		if ( empty( $address ) )
			return;

		// If the address didn't change, doesn't make sense to query google again for the geo data
		if ( $address === get_post_meta( $venueId, self::ADDRESS, true ) )
			return;

		$data = wp_remote_get( "http://maps.googleapis.com/maps/api/geocode/json?address=" . urlencode( $address ) . "&sensor=false" );

		if ( is_wp_error( $data ) || !isset( $data["body"] ) )
			return;

		$data_arr = json_decode( $data["body"] );

		if ( !empty( $data_arr->results[0]->geometry->location->lat ) ) {
			update_post_meta( $venueId, self::LAT, $data_arr->results[0]->geometry->location->lat );
		}

		if ( !empty( $data_arr->results[0]->geometry->location->lng ) ) {
			update_post_meta( $venueId, self::LNG, $data_arr->results[0]->geometry->location->lng );
		}

		// Saving the aggregated address so we don't need to ping google on every save
		update_post_meta( $venueId, self::ADDRESS, $address );

		delete_transient( self::ESTIMATION_CACHE_KEY );

	}

	public static function getOptions( $force = false ) {
		if ( !isset( self::$options ) || $force ) {
			$options       = get_option( self::OPTIONNAME, array() );
			self::$options = apply_filters( 'tribe_geoloc_get_options', $options );
		}
		return self::$options;
	}

	public function getOption( $optionName, $default = '', $force = false ) {

		if ( !$optionName )
			return;

		if ( !isset( self::$options ) || $force ) {
			self::getOptions( $force );
		}

		if ( isset( self::$options[$optionName] ) ) {
			$option = self::$options[$optionName];
		} else {
			$option = $default;
		}

		return apply_filters( 'tribe_geoloc_get_single_option', $option, $default );
	}

	private function get_geofence_default_size() {

		$tec = TribeEvents::instance();

		$geofence = $tec->getOption( 'geoloc_default_geofence', 25 );
		$unit     = $tec->getOption( 'geoloc_default_unit', 'miles' );

		//Our queries need the size always in kms
		$geofence = tribe_convert_units( $geofence, $unit, 'kms' );

		return apply_filters( 'tribe_geoloc_geofence', $geofence );
	}

	function get_venues_in_geofence( $lat, $lng, $geofence_radio = null ) {


		if ( !$geofence_radio ) {
			$geofence_radio = $this->get_geofence_default_size();
		}

		$maxLat = $lat + rad2deg( $geofence_radio / self::EARTH_RADIO );
		$minLat = $lat - rad2deg( $geofence_radio / self::EARTH_RADIO );
		$maxLng = $lng + rad2deg( $geofence_radio / self::EARTH_RADIO / cos( deg2rad( $lat ) ) );
		$minLng = $lng - rad2deg( $geofence_radio / self::EARTH_RADIO / cos( deg2rad( $lat ) ) );


		global $wpdb;

		//FTW!

		$sql = "Select distinct venue_id from (
		SELECT coords.venue_id,
		       Max(lat) AS lat,
		       Max(lng) AS lng
		FROM   (SELECT post_id AS venue_id,
		               CASE
		                 WHEN meta_key = '" . self::LAT . "' THEN meta_value
		               end     AS LAT,
		               CASE
		                 WHEN meta_key = '" . self::LNG . "' THEN meta_value
		               end     AS LNG
		        FROM   $wpdb->postmeta
		        WHERE  meta_key = '" . self::LAT . "'
		            OR meta_key = '" . self::LNG . "') coords
		        INNER JOIN $wpdb->posts p
		            ON coords.venue_id = p.id
		WHERE (lat > $minLat OR lat IS NULL) AND (lat < $maxLat OR lat IS NULL) AND (lng > $minLng OR lng IS NULL) AND (lng < $maxLng OR lng IS NULL)
			AND p.post_status = 'publish'
		GROUP  BY venue_id
		HAVING lat IS NOT NULL
		       AND lng IS NOT NULL
		       ) query";

		$data = $wpdb->get_results( $sql, ARRAY_A );

		if ( empty( $data ) )
			return null;

		return wp_list_pluck( $data, 'venue_id' );

	}

	public function set_past_events_query( $query ) {
		$query->set( 'start_date', '' );
		$query->set( 'eventDate', '' );
		$query->set( 'order', 'DESC' );
		$query->set( 'end_date', date_i18n( TribeDateUtils::DBDATETIMEFORMAT ) );
		return $query;
	}

	function ajax_geosearch() {

		if ( class_exists( 'TribeEventsFilterView' ) ) {
			TribeEventsFilterView::instance()->createFilters( null, true );
			$this->setup_geoloc_filter_in_filters();
		}

		$tribe_paged = !empty( $_POST["tribe_paged"] ) ? $_POST["tribe_paged"] : 1;

		TribeEventsQuery::init();

		$defaults = array( 'post_type'      => TribeEvents::POSTTYPE,
						   'orderby'        => 'event_date',
						   'order'          => 'ASC',
						   'posts_per_page' => tribe_get_option( 'postsPerPage', 10 ),
						   'paged'          => $tribe_paged,
						   'post_status'    => array( 'publish' ),
						   'eventDisplay'   => 'map',
		);

		$view_state = 'map';

		/* if past view */
		if ( ! empty( $_POST['tribe_event_display'] ) && $_POST['tribe_event_display'] == 'past' ){
			$view_state = 'past';
			add_filter( 'tribe_events_pre_get_posts', array( $this, 'set_past_events_query' ) );
		}

		$query = TribeEventsQuery::getEvents( $defaults, true );

		if ( $this->is_geoloc_query() && $query->found_posts > 0 ) {
			$lat = isset( $_POST['tribe-bar-geoloc-lat'] ) ? $_POST['tribe-bar-geoloc-lat'] : 0;
			$lng = isset( $_POST['tribe-bar-geoloc-lng'] ) ? $_POST['tribe-bar-geoloc-lng'] : 0;

			$this->order_posts_by_distance( $query->posts, $lat, $lng );
		}


		$response = array( 'html'        => '',
		                   'markers'     => array(),
		                   'success'     => true,
						   'tribe_paged' => $tribe_paged,
		                   'max_pages'   => $query->max_num_pages,
		                   'total_count' => $query->found_posts,
		                   'view'        => $view_state,
		);

		if ( $query->found_posts > 0 ) {
			global $wp_query, $post;
			$data     = $query->posts;
			$post     = $query->posts[0];
			$wp_query = $query;
			TribeEvents::instance()->setDisplay();
			ob_start();

			// global $wp_query;
			// print_r($wp_query,true);
			tribe_get_view();
			$response['html'] .= ob_get_clean();
			$response['markers'] = $this->generate_markers( $data );
		}
		
		apply_filters( 'tribe_events_ajax_response', $response );

		header( 'Content-type: application/json' );
		echo json_encode( $response );

		exit;

	}


	private function order_posts_by_distance( &$posts, $lat_from, $lng_from ) {

		// add distances
		for ( $i = 0; $i < count( $posts ); $i++ ) {
			$posts[$i]->lat      = $this->get_lat_for_event( $posts[$i]->ID );
			$posts[$i]->lng      = $this->get_lng_for_event( $posts[$i]->ID );
			$posts[$i]->distance = $this->get_distance_between_coords( $lat_from, $lng_from, $posts[$i]->lat, $posts[$i]->lng );
		}

		//sort
		$this->quickSort( $posts );

		//no return, $posts passed by ref
	}

	// Implementation and benchmark from: http://stackoverflow.com/questions/1462503/sort-array-by-object-property-in-php
	private function quickSort( &$array ) {
		$cur           = 1;
		$stack[1]['l'] = 0;
		$stack[1]['r'] = count( $array ) - 1;

		do {
			$l = $stack[$cur]['l'];
			$r = $stack[$cur]['r'];
			$cur--;

			do {
				$i   = $l;
				$j   = $r;
				$tmp = $array[(int)( ( $l + $r ) / 2 )];

				do {
					/* Divide... */
					while ( $array[$i]->distance < $tmp->distance )
						$i++;

					while ( $tmp->distance < $array[$j]->distance )
						$j--;

					/* ...and conquer! */
					if ( $i <= $j ) {
						$w         = $array[$i];
						$array[$i] = $array[$j];
						$array[$j] = $w;

						$i++;
						$j--;
					}

				} while ( $i <= $j );

				if ( $i < $r ) {
					$cur++;
					$stack[$cur]['l'] = $i;
					$stack[$cur]['r'] = $r;
				}
				$r = $j;

			} while ( $l < $r );

		} while ( $cur != 0 );


	}

	// Implementation of the Haversine Formula
	public function get_distance_between_coords( $lat_from, $lng_from, $lat_to, $lng_to ) {

		$delta_lat = $lat_to - $lat_from;
		$delta_lng = $lng_to - $lng_from;
		$a         = sin( deg2rad( $delta_lat / 2 ) ) * sin( deg2rad( $delta_lat / 2 ) ) + cos( deg2rad( $lat_from ) ) * cos( deg2rad( $lat_to ) ) * sin( deg2rad( $delta_lng / 2 ) ) * sin( deg2rad( $delta_lng / 2 ) );
		$c         = asin( min( 1, sqrt( $a ) ) );
		$distance  = 2 * self::EARTH_RADIO * $c;
		$distance  = round( $distance, 4 );

		return $distance;
	}

	public function get_lat_for_event( $event_id ) {
		$venue = tribe_get_venue_id( $event_id );
		return get_post_meta( $venue, self::LAT, true );
	}

	public function get_lng_for_event( $event_id ) {
		$venue = tribe_get_venue_id( $event_id );
		return get_post_meta( $venue, self::LNG, true );
	}

	public function estimate_center_point() {
		global $wpdb;

		$data = get_transient( self::ESTIMATION_CACHE_KEY );

		if ( empty( $data ) ) {

			$sql = "SELECT Max(lat) max_lat,
					       Max(lng) max_lng,
					       Min(lat) min_lat,
					       Min(lng) min_lng
					FROM   (SELECT post_id AS venue_id,
				               CASE
				                 WHEN meta_key = '" . self::LAT . "' THEN meta_value
				               end     AS LAT,
				               CASE
				                 WHEN meta_key = '" . self::LNG . "' THEN meta_value
				               end     AS LNG
				        FROM   $wpdb->postmeta
				        WHERE  meta_key = '" . self::LAT . "'
				            OR meta_key = '" . self::LNG . "') coors
		";

			$data = $wpdb->get_results( $sql, ARRAY_A );

			if ( !empty( $data ) )
				$data = array_shift( $data );

			set_transient( self::ESTIMATION_CACHE_KEY, $data, 5000 );
		}

		return $data;

	}

	private function generate_markers( $events ) {

		$markers = array();

		foreach ( $events as $event ) {

			$venue_id = tribe_get_venue_id( $event->ID );
			$lat      = get_post_meta( $venue_id, self::LAT, true );
			$lng      = get_post_meta( $venue_id, self::LNG, true );
			$address  = tribe_get_address( $event->ID );
			$title    = $event->post_title;
			$link     = get_permalink( $event->ID );

			$markers[] = array( 'lat'     => $lat,
			                    'lng'     => $lng,
			                    'title'   => $title,
			                    'address' => $address,
			                    'link'    => $link );

		}

		return $markers;

	}


	/* Static Singleton Factory Method */
	private static $instance;

	public static function instance() {
		if ( !isset( self::$instance ) ) {
			$className      = __CLASS__;
			self::$instance = new $className;
		}
		return self::$instance;
	}

}
