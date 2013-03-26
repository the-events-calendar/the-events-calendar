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
		add_filter( 'tribe_current_events_page_template', array( $this, 'load_template' ) );

		add_action( 'tribe_events_venue_updated', array( $this, 'save_venue_geodata' ), 10, 2 );
		add_action( 'tribe_events_venue_created', array( $this, 'save_venue_geodata' ), 10, 2 );

		add_action( 'wp_ajax_geosearch', array( $this, 'ajax_geosearch' ) );
		add_action( 'wp_ajax_nopriv_geosearch', array( $this, 'ajax_geosearch' ) );

		add_filter( 'tribe-events-bar-views', array( $this, 'setup_view_for_bar' ), 25, 1 );
		add_filter( 'tribe-events-bar-filters', array( $this, 'setup_geoloc_filter_in_bar' ), 1, 1 );
		add_action( 'tribe-events-bar-enqueue-scripts', array( $this, 'scripts' ) );
		add_filter( 'tribe_events_pre_get_posts', array( $this, 'setup_geoloc_in_query' ) );

		add_action( 'tribe_events_filters_create_filters', array( $this, 'setup_geoloc_filter_in_filters' ), 1 );

		add_filter( 'tribe_settings_tab_fields', array( $this, 'inject_settings' ), 10, 2 );

	}

	public function setup_geoloc_filter_in_filters() {
		$current_filters = get_option( 'tribe_events_filters_current_active_filters', TribeEventsFilterView::instance()->getDefaultFilters() );

		$distances = apply_filters( 'geoloc-values-for-filters', array( '5'    => '5 miles',
		                                                                '10'   => '10 miles',
		                                                                '25'   => '25 miles',
		                                                                '50'   => '50 miles',
		                                                                '100'  => '100 miles',
		                                                                '250'  => '250 miles' ) );

		$geoloc_filter_array = array( 'name'   => __( 'Distance', 'tribe-events-calendar-pro' ),
		                              'slug'   => 'geofence',
		                              'values' => $distances, );

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

	public function setup_geofence_in_query( $distance ) {
		if ( !empty( $this->selected_geofence ) ) {
			$distance = $this->selected_geofence;
		}
		return $distance;
	}

	public function scripts() {

		$tec = TribeEvents::instance();

		$http = is_ssl() ? 'https' : 'http';

		wp_register_script( 'gmaps', $http . '://maps.google.com/maps/api/js?sensor=false', array( 'jquery' ) );
		wp_register_script( 'tribe-geoloc', trailingslashit( TribeEventsPro::instance()->pluginUrl ) . 'resources/maps.js', array( 'gmaps' ) );
		wp_enqueue_script( 'tribe-geoloc' );

		$data = array( 'ajaxurl'  => admin_url( 'admin-ajax.php', ( is_ssl() ? 'https' : 'http' ) ),
		               'nonce'    => wp_create_nonce( 'geosearch' ),
		               'center'   => $this->estimate_center_point(),
		               'map_view' => ( $tec->displaying == 'map' ) ? TRUE : FALSE );

		wp_localize_script( 'tribe-geoloc', 'GeoLoc', $data );


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
		if ( !tribe_get_option( 'hideLocationSearch', false ) ) {
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

	public function load_template( $template ) {
		global $wp_query;


		if ( !empty( $wp_query->query_vars['eventDisplay'] ) && $wp_query->query_vars['eventDisplay'] === 'map' ) {

			add_filter( 'tribe-events-bar-should-show', '__return_true' );
			// add_action( 'tribe_current_events_page_template', array( $this, 'setup_geoloc_template' ), 1 );
			// add_action( 'tribe_get_events_title', array( $this, 'setup_geoloc_title' ), 1 );

			// $pro      = TribeEventsPro::instance();
			$template = TribeEventsTemplates::getTemplateHierarchy( 'map', '', 'pro', TribeEventsPro::instance()->pluginPath );
			$template = TribeEventsTemplates::getTemplateHierarchy('list');

			/* This view only works with JS active, we don't want content
			 *  on the first load.. just the wrapper. ajax will take care of it
			*/
			if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) {

				// Start list loop
				remove_filter( 'tribe_events_list_before_loop', array( 'Tribe_Events_List_Template', 'before_loop' ), 1, 2 );
				remove_filter( 'tribe_events_list_inside_before_loop', array( 'Tribe_Events_List_Template', 'inside_before_loop' ), 1, 3 );

				// Event title
				remove_filter( 'tribe_events_list_the_event_title', array( 'Tribe_Events_List_Template', 'the_event_title' ), 1, 2 );

				// Event meta
				remove_filter( 'tribe_events_list_before_the_meta', array( 'Tribe_Events_List_Template', 'before_the_meta' ), 1, 2 );
				remove_filter( 'tribe_events_list_the_meta', array( 'Tribe_Events_List_Template', 'the_meta' ), 1, 2 );
				remove_filter( 'tribe_events_list_after_the_meta', array( 'Tribe_Events_List_Template', 'after_the_meta' ), 1, 2 );

				// Event featured image
				remove_filter( 'tribe_events_list_the_event_image', array( 'Tribe_Events_List_Template', 'the_event_image' ), 1, 2 );

				// Event content
				remove_filter( 'tribe_events_list_before_the_content', array( 'Tribe_Events_List_Template', 'before_the_content' ), 1, 2 );
				remove_filter( 'tribe_events_list_the_content', array( 'Tribe_Events_List_Template', 'the_content' ), 1, 2 );
				remove_filter( 'tribe_events_list_after_the_content', array( 'Tribe_Events_List_Template', 'after_the_content' ), 1, 2 );

				// End list loop
				remove_filter( 'tribe_events_list_inside_after_loop', array( 'Tribe_Events_List_Template', 'inside_after_loop' ), 1, 2 );
				remove_filter( 'tribe_events_list_after_loop', array( 'Tribe_Events_List_Template', 'after_loop' ), 1, 2 );

			}
			
		}

		return $template;
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
		if ( $address === get_post_meta( $venueId, self::ADDRESS, TRUE ) )
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

	public static function getOptions( $force = FALSE ) {
		if ( !isset( self::$options ) || $force ) {
			$options       = get_option( self::OPTIONNAME, array() );
			self::$options = apply_filters( 'tribe_geoloc_get_options', $options );
		}
		return self::$options;
	}

	public function getOption( $optionName, $default = '', $force = FALSE ) {

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

	function get_venues_in_geofence( $lat, $lng, $geofence_radio = NULL ) {


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
		SELECT venue_id,
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
		WHERE (lat > $minLat OR lat IS NULL) AND (lat < $maxLat OR lat IS NULL) AND (lng > $minLng OR lng IS NULL) AND (lng < $maxLng OR lng IS NULL)
		GROUP  BY venue_id
		HAVING lat IS NOT NULL
		       AND lng IS NOT NULL
		       ) query";

		$data = $wpdb->get_results( $sql, ARRAY_A );

		if ( empty( $data ) )
			return NULL;

		return wp_list_pluck( $data, 'venue_id' );

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
		                   'eventDisplay'   => 'map' );

		$query = TribeEventsQuery::getEvents( $defaults, true );


		if ( $this->is_geoloc_query() && $query->found_posts > 0 ) {
			$lat = isset( $_POST['tribe-bar-geoloc-lat'] ) ? $_POST['tribe-bar-geoloc-lat'] : 0;
			$lng = isset( $_POST['tribe-bar-geoloc-lng'] ) ? $_POST['tribe-bar-geoloc-lng'] : 0;

			$this->order_posts_by_distance( $query->posts, $lat, $lng );
		}


		$response = array( 'html'        => '',
		                   'markers'     => array(),
		                   'success'     => TRUE,
		                   'max_pages'   => $query->max_num_pages,
		                   'total_count' => $query->found_posts,
		                   'view'        => 'map',
		);

		if ( $query->found_posts === 1 ) {
			$response['html'] .= sprintf( __( "<div class='event-notices'>%d event found</div>", 'tribe-events-calendar-pro' ), $query->found_posts );
		} else {
			$extra = "";
			if ( $query->max_num_pages > 1 ) {
				$extra = sprintf( __( " / %d in this page", 'tribe-events-calendar-pro' ), $query->post_count );
			}

			$response['html'] .= sprintf( __( "<div class='event-notices'>%d events found%s</div>", 'tribe-events-calendar-pro' ), $query->found_posts, $extra );
		}

		if ( $query->found_posts > 0 ) {
			global $wp_query, $post;
			$data     = $query->posts;
			$post     = $query->posts[0];
			$wp_query = $query;
			ob_start();

			add_filter( 'tribe_events_list_show_separators', '__return_false' );
			add_filter( 'tribe_events_list_show_ical_link', '__return_false' );

			echo '<div id="tribe-geo-results">';
			// global $wp_query;
			// print_r($wp_query,true);
			include TribeEventsTemplates::getTemplateHierarchy( 'list' );
			echo '</div>';
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
		return get_post_meta( $venue, self::LAT, TRUE );
	}

	public function get_lng_for_event( $event_id ) {
		$venue = tribe_get_venue_id( $event_id );
		return get_post_meta( $venue, self::LNG, TRUE );
	}

	private function estimate_center_point() {
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
			$lat      = get_post_meta( $venue_id, self::LAT, TRUE );
			$lng      = get_post_meta( $venue_id, self::LNG, TRUE );
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
