<?php
/*-------------------------------------------------------------------------------------
* File description: Main class for Geo Location functionality
*
*
* Created by:  Daniel Dvorkin
* For:         Modern Tribe Inc. ( http://m.tri.be/20 )
*
* Date: 		9 / 18 / 12 12:31 PM
*-------------------------------------------------------------------------------------*/

class TribeEventsGeoLoc {

	/**
	 * Meta key for the venue Latitude
	 */
	const LAT = '_VenueLat';
	/**
	 * Meta key for the venue Longitude
	 */
	const LNG = '_VenueLng';
	/**
	 * Meta key for the full address we used to get the geo points from Google Maps
	 * It's used as a cache, so we only ping Google when the user changed something in the address.
	 */
	const ADDRESS = '_VenueGeoAddress';
	/**
	 * Option key for the Geoloc settings
	 */
	const OPTIONNAME = 'tribe_geoloc_options';
	/**
	 * Cache key for the geo point at the center of all site's venues
	 */
	const ESTIMATION_CACHE_KEY = 'geoloc_center_point_estimation';
	/**
	 * Earth radio in Kms. Used for the distance math.
	 */
	const EARTH_RADIO = 6371;

	/**
	 * Settings.
	 * @var
	 */
	protected static $options;
	/**
	 * Slug of the map view
	 * @var mixed|void
	 */
	public $rewrite_slug;

	/**
	 * Limit for the distance search
	 * @var
	 */
	private $selected_geofence;

	/**
	 * Cache of how many venues we "fixed" (ie: generated geopoints for)
	 * @var int
	 */
	private $last_venues_fixed_count = 0;

	/**
	 * Singleton instance of this class
	 * @var TribeEventsGeoLoc
	 */
	private static $instance;

	/**
	 * Class constructor
	 */
	function __construct() {

		$this->rewrite_slug = $this->getOption( 'geoloc_rewrite_slug', 'map' );


		add_action( 'tribe_events_venue_updated',           array( $this, 'save_venue_geodata'                      ), 10, 2 );
		add_action( 'tribe_events_venue_created',           array( $this, 'save_venue_geodata'                      ), 10, 2 );
		add_action( 'tribe_events_filters_create_filters',  array( $this, 'setup_geoloc_filter_in_filters'          ),  1    );
		add_action( 'wp_ajax_tribe_geosearch',              array( $this, 'ajax_tribe_geosearch'                    )        );
		add_action( 'wp_ajax_nopriv_tribe_geosearch',       array( $this, 'ajax_tribe_geosearch'                    )        );
		add_action( 'wp_enqueue_scripts',                   array( $this, 'scripts'                                 )        );
		add_action( 'admin_init',                           array( $this, 'maybe_generate_geopoints_for_all_venues' )        );
		add_action( 'admin_init',                           array( $this, 'maybe_offer_generate_geopoints'          )        );

		add_filter( 'tribe-events-bar-views',               array( $this, 'setup_view_for_bar'         ), 25, 1 );
		add_filter( 'tribe_settings_tab_fields',            array( $this, 'inject_settings'            ), 10, 2 );
		add_filter( 'tribe-events-bar-filters',             array( $this, 'setup_geoloc_filter_in_bar' ),  1, 1 );
		add_filter( 'generate_rewrite_rules',               array( $this, 'add_routes'                 )        );
		add_action( 'tribe_events_pre_get_posts',           array( $this, 'setup_geoloc_in_query'      )        );
		add_filter( 'tribe_events_list_inside_before_loop', array( $this, 'add_event_distance'         )        );

	}

	/**
	 * If the "Filters bar" add-on is active, setup the distance filter.
	 */
	public function setup_geoloc_filter_in_filters() {
		if ( ! tribe_get_option( 'hideLocationSearch', false ) ) {
			require_once('TribeEventsFilter_GeoLoc.php');
			new TribeEventsFilter_GeoLoc( __( 'Distance', 'tribe-events-calendar-pro' ), 'geofence' );
		}
	}

	/**
	 * Enqueue the maps JS in all the views (Needed for the location filter in the Tribe Bar)
	 */
	public function scripts() {
		if ( tribe_is_event_query() && ! is_single() ) {
			Tribe_PRO_Template_Factory::asset_package( 'ajax-maps' );
		}
	}


	/**
	 * Inject the GeoLoc settings into the general TEC settings screen
	 *
	 * @param $args
	 * @param $id
	 *
	 * @return array
	 */
	public function inject_settings( $args, $id ) {

		if ( $id == 'general' ) {

			$venues = $this->get_venues_without_geoloc_info();

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
					'geoloc_default_unit'     => array(
						'type'            => 'dropdown',
						'label'           => __( 'Map view distance unit', 'tribe-events-calendar-pro' ),
						'validation_type' => 'options',
						'size'            => 'small',
						'default'         => 'miles',
						'options'         => apply_filters( 'tribe_distance_units', array( 'miles' => __( 'Miles', 'tribe-events-calendar-pro' ),
						                                                                   'kms'   => __( 'Kilometers', 'tribe-events-calendar-pro' ) ) ) ),
					'geoloc_fix_venues'       => array(
						'type'        => 'html',
						'html'        => '<a name="geoloc_fix"></a><fieldset class="tribe-field tribe-field-html"><legend>' . __( 'Fix geolocation data', 'tribe-events-calendar-pro' ) . '</legend><div class="tribe-field-wrap">' . $this->fix_geoloc_data_button() . '<p class="tribe-field-indent description">' . sprintf( __( "You have %d venues for which we don't have geolocation data. We need to use the Google Maps API to get that information. Doing this may take a while (aprox. 1 minute for every 200 venues).", 'tribe-events-calendar-pro' ), $venues->found_posts ) . '</p></div></fieldset>',
						'conditional' => ( $venues->found_posts > 0 )
					),
				)
			);
		} elseif ( $id == 'display' ) {
			$args = TribeEvents::array_insert_after_key( 'tribeDisableTribeBar', $args, array(
				'hideLocationSearch' => array(
					'type'            => 'checkbox_bool',
					'label'           => __( 'Hide location search', 'tribe-events-calendar-pro' ),
					'tooltip'         => __( 'Removes location search field from the events bar on all views except for map view.', 'tribe-events-calendar-pro' ),
					'default'         => false,
					'validation_type' => 'boolean',
				),
			) );
		}

		return $args;
	}


	/**
	 * @param bool $full_data
	 *
	 * @return WP_Query
	 */
	protected function get_venues_without_geoloc_info( $full_data = false ) {
		$query_args = array(
			'post_type'      => TribeEvents::VENUE_POST_TYPE,
			'post_status'    => 'publish',
			'posts_per_page' => 250,
			'meta_query'     => array(
				array(
					'key'     => '_VenueGeoAddress',
					'compare' => 'NOT EXISTS'
				),
				array(
					'key'     => '_VenueAddress',
					'compare' => '!=',
					'value'   => ''
				)
			)
		);

		if ( ! $full_data ) {
			$query_args['fields']         = 'ids';
			$query_args['posts_per_page'] = 1;
		}


		$venues = new WP_Query( $query_args );

		return $venues;
	}

	/**
	 * Add the Map View to the view switcher in the Tribe Bar
	 * @param $views
	 *
	 * @return array
	 */
	public function setup_view_for_bar( $views ) {
		$views[] = array( 'displaying' => 'map', 'event_bar_hook' => 'tribe_events_list_the_title', 'anchor' => __( 'Map', 'tribe-events-calendar-pro' ), 'url' => tribe_get_mapview_link() );

		return $views;
	}

	/**
	 * Add the location filter in the Tribe Bar
	 * @param $filters
	 *
	 * @return array
	 */
	public function setup_geoloc_filter_in_bar( $filters ) {
		if ( tribe_is_map() || ! tribe_get_option( 'hideLocationSearch', false ) ) {
			if ( tribe_get_option( 'tribeDisableTribeBar', false ) == false ) {
				$value = "";
				if ( ! empty( $_REQUEST['tribe-bar-geoloc'] ) ) {
					$value = $_REQUEST['tribe-bar-geoloc'];
				}

				$lat = "";
				if ( ! empty( $_REQUEST['tribe-bar-geoloc-lat'] ) ) {
					$lat = $_REQUEST['tribe-bar-geoloc-lat'];
				}

				$lng = "";
				if ( ! empty( $_REQUEST['tribe-bar-geoloc-lng'] ) ) {
					$lng = $_REQUEST['tribe-bar-geoloc-lng'];
				}

				$filters['tribe-bar-geoloc'] = array( 'name'    => 'tribe-bar-geoloc',
				                                      'caption' => __( 'Near', 'tribe-events-calendar-pro' ),
				                                      'html'    => '<input type="hidden" name="tribe-bar-geoloc-lat" id="tribe-bar-geoloc-lat" value="' . esc_attr( $lat ) . '" /><input type="hidden" name="tribe-bar-geoloc-lng" id="tribe-bar-geoloc-lng" value="' . esc_attr( $lng ) . '" /><input type="text" name="tribe-bar-geoloc" id="tribe-bar-geoloc" value="' . esc_attr( $value ) . '" placeholder="'. __( 'Location', 'tribe-events-calendar-pro' ) .'">' );
			}
		}

		return $filters;
	}

	/**
	 * Returns whether the user made a location search in the Tribe Bar
	 * @return bool
	 */
	public function is_geoloc_query() {
		return ( ! empty( $_REQUEST['tribe-bar-geoloc-lat'] ) && ! empty( $_REQUEST['tribe-bar-geoloc-lng'] ) );
	}

	/**
	 * Filter the main query and:
	 *  1) If the user made a Location search, get the events close to that location (inside the geo fence)
	 *  2) If the user is in the map view and didn't make a location search, only get events in venues with geo data,
	 *     so we can map them.
	 *
	 *
	 * @param WP_Query $query
	 *
	 * @return void
	 */
	public function setup_geoloc_in_query( $query ) {
		if ( ( ! $query->is_main_query() && ! defined( 'DOING_AJAX' ) ) || ! $query->get( 'post_type' ) == TribeEvents::POSTTYPE ) {
			return;
		}

		$force = false;
		if ( ! empty( $_REQUEST['tribe-bar-geoloc-lat'] ) && ! empty( $_REQUEST['tribe-bar-geoloc-lng'] ) ) {
			$force  = true;
			$venues = $this->get_venues_in_geofence( $_REQUEST['tribe-bar-geoloc-lat'], $_REQUEST['tribe-bar-geoloc-lng'] );
		} else if ( TribeEvents::instance()->displaying == 'map' || ( ! empty( $query->query_vars['eventDisplay'] ) && $query->query_vars['eventDisplay'] == 'map' ) ) {
			// Show only venues that have geoloc info
			$force = true;
			//Get all geoloc'ed venues (set a geofence the size of the planet)
			$venues = $this->get_venues_in_geofence( 1, 1, self::EARTH_RADIO * M_PI );
		}

		if ( $force ) {
			if ( empty( $venues ) )
				$venues = - 1;

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
	}


	/**
	 * Adds the rewrite rules to make the map view work
	 *
	 * @param $wp_rewrite
	 */
	public function add_routes( $wp_rewrite ) {
		$tec = TribeEvents::instance();

		$base    = trailingslashit( $tec->getOption( 'eventsSlug', 'events' ) );
		$baseTax = trailingslashit( $tec->taxRewriteSlug );
		$baseTax = "(.*)" . $baseTax . "(?:[^/]+/)*";
		$baseTag = trailingslashit( $tec->tagRewriteSlug );
		$baseTag = "(.*)" . $baseTag;

		$newRules = array();

		$newRules[$base . $this->rewrite_slug]                         = 'index.php?post_type=' . TribeEvents::POSTTYPE . '&eventDisplay=map';
		$newRules[$baseTax . '([^/]+)/' . $this->rewrite_slug . '/?$'] = 'index.php?tribe_events_cat=' . $wp_rewrite->preg_index( 2 ) . '&post_type=' . TribeEvents::POSTTYPE . '&eventDisplay=map';
		$newRules[$baseTag . '([^/]+)/' . $this->rewrite_slug . '/?$'] = 'index.php?tag=' . $wp_rewrite->preg_index( 2 ) . '&post_type=' . TribeEvents::POSTTYPE . '&eventDisplay=map';

		$wp_rewrite->rules = $newRules + $wp_rewrite->rules;
	}

	/**
	 *  Adds the distance of each event in the resulting list, when the user makes a location search.
	 *
	 * @param $html
	 *
	 * @return string
	 */
	public function add_event_distance( $html ) {
		global $post;
		if ( ! empty( $post->distance ) )
			$html .= '<span class="tribe-events-distance">' . tribe_get_distance_with_unit( $post->distance ) . '</span>';

		return $html;
	}

	/**
	 * Hooks into the venue save and if we don't have Geo Data for that address,
	 * it calls the Google Maps API and grabs the Lat and Lng for that venue.
	 * @param $venueId
	 * @param $data
	 *
	 * @return bool
	 */
	function save_venue_geodata( $venueId, $data ) {


		$_address  = ( ! empty( $data["Address"] ) ) ? $data["Address"] : '';
		$_city     = ( ! empty( $data["City"] ) ) ? $data["City"] : '';
		$_province = ( ! empty( $data["Province"] ) ) ? $data["Province"] : '';
		$_state    = ( ! empty( $data["State"] ) ) ? $data["State"] : '';
		$_zip      = ( ! empty( $data["Zip"] ) ) ? $data["Zip"] : '';
		$_country  = ( ! empty( $data["Country"] ) ) ? $data["Country"] : '';

		$address = trim( $_address . ' ' . $_city . ' ' . $_province . ' ' . $_state . ' ' . $_zip . ' ' . $_country );

		if ( empty( $address ) )
			return false;

		// If the address didn't change, doesn't make sense to query google again for the geo data
		if ( $address === get_post_meta( $venueId, self::ADDRESS, true ) )
			return false;

		$data = wp_remote_get( "http://maps.googleapis.com/maps/api/geocode/json?address=" . urlencode( $address ) . "&sensor=false" );

		if ( is_wp_error( $data ) || ! isset( $data["body"] ) )
			return false;

		$data_arr = json_decode( $data["body"] );

		if ( ! empty( $data_arr->results[0]->geometry->location->lat ) ) {
			update_post_meta( $venueId, self::LAT, $data_arr->results[0]->geometry->location->lat );
		}

		if ( ! empty( $data_arr->results[0]->geometry->location->lng ) ) {
			update_post_meta( $venueId, self::LNG, $data_arr->results[0]->geometry->location->lng );
		}

		// Saving the aggregated address so we don't need to ping google on every save
		update_post_meta( $venueId, self::ADDRESS, $address );

		delete_transient( self::ESTIMATION_CACHE_KEY );

		return true;

	}

	/**
	 * Gets all settings
	 * @static
	 *
	 * @param bool $force
	 *
	 * @return mixed|void
	 */
	public static function getOptions( $force = false ) {
		if ( ! isset( self::$options ) || $force ) {
			$options       = get_option( self::OPTIONNAME, array() );
			self::$options = apply_filters( 'tribe_geoloc_get_options', $options );
		}

		return self::$options;
	}

	/**
	 * Gets a single option from the settings
	 *
	 * @param string $optionName
	 * @param string $default
	 * @param bool   $force
	 *
	 * @return mixed|void
	 */
	public function getOption( $optionName, $default = '', $force = false ) {


		if ( ! isset( self::$options ) || $force ) {
			self::getOptions( $force );
		}

		if ( isset( self::$options[$optionName] ) ) {
			$option = self::$options[$optionName];
		} else {
			$option = $default;
		}

		return apply_filters( 'tribe_geoloc_get_single_option', $option, $default );
	}

	/**
	 * Returns the default geo fence size in kms.
	 * @return mixed|void
	 */
	private function get_geofence_default_size() {

		$tec = TribeEvents::instance();

		$geofence = $tec->getOption( 'geoloc_default_geofence', 25 );
		$unit     = $tec->getOption( 'geoloc_default_unit', 'miles' );

		//Our queries need the size always in kms
		$geofence = tribe_convert_units( $geofence, $unit, 'kms' );

		return apply_filters( 'tribe_geoloc_geofence', $geofence );
	}

	/**
	 * Get a list of venues inside a given geo fence with the given geo point at the center.
	 *
	 * @param float $lat
	 * @param float $lng
	 * @param float $geofence_radio
	 *
	 * @return array|null
	 */
	function get_venues_in_geofence( $lat, $lng, $geofence_radio = null ) {


		if ( empty( $geofence_radio ) ) {
			$geofence_radio = $this->get_geofence_default_size();
		}

		// get the limits of the geofence

		$maxLat = $lat + rad2deg( $geofence_radio / self::EARTH_RADIO );
		$minLat = $lat - rad2deg( $geofence_radio / self::EARTH_RADIO );
		$maxLng = $lng + rad2deg( $geofence_radio / self::EARTH_RADIO / cos( deg2rad( $lat ) ) );
		$minLng = $lng - rad2deg( $geofence_radio / self::EARTH_RADIO / cos( deg2rad( $lat ) ) );


		global $wpdb;

		// Get the venues inside a geofence

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

	/**
	 * AJAX handler for the Map view
	 */
	function ajax_tribe_geosearch() {

		$tribe_paged = ! empty( $_POST["tribe_paged"] ) ? $_POST["tribe_paged"] : 1;

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
		if ( ! empty( $_POST['tribe_event_display'] ) && $_POST['tribe_event_display'] == 'past' ) {
			$view_state = 'past';
		}

		if ( isset( $_POST['tribe_event_category'] ) ) {
			$defaults[TribeEvents::TAXONOMY] = $_POST['tribe_event_category'];
		}
		$query = TribeEventsQuery::getEvents( $defaults, true );
		$have_events = ( 0 < $query->found_posts );

		if ( $have_events && $this->is_geoloc_query() ) {
			$lat = isset( $_POST['tribe-bar-geoloc-lat'] ) ? $_POST['tribe-bar-geoloc-lat'] : 0;
			$lng = isset( $_POST['tribe-bar-geoloc-lng'] ) ? $_POST['tribe-bar-geoloc-lng'] : 0;

			$this->order_posts_by_distance( $query->posts, $lat, $lng );
		}
		elseif ( ! $have_events && isset($_POST['tribe-bar-geoloc']) ) {
			TribeEvents::setNotice( 'event-search-no-results', sprintf( __( 'No results were found for events in or near <strong>"%s"</strong>.', 'tribe-events-calendar-pro' ), esc_html($_POST['tribe-bar-geoloc']) ) );
		}
		elseif ( ! $have_events ) {
			TribeEvents::setNotice( 'event-search-no-results', __( 'There were no results found.', 'tribe-events-calendar-pro' ) );
		}

		$response = array( 'html'        => '',
		                   'markers'     => array(),
		                   'success'     => true,
		                   'tribe_paged' => $tribe_paged,
		                   'max_pages'   => $query->max_num_pages,
		                   'total_count' => $query->found_posts,
		                   'view'        => $view_state,
		);

		if ( $have_events) {
			global $wp_query, $post;
			$data     = $query->posts;
			$post     = $query->posts[0];
			$wp_query = $query;
			TribeEvents::instance()->displaying = 'map';

			ob_start();

			tribe_get_view( 'pro/map/content' );
			$response['html'] .= ob_get_clean();
			$response['markers'] = $this->generate_markers( $data );
		} else {
			global $wp_query;
			$wp_query = $query;
			TribeEvents::instance()->setDisplay();

			ob_start();

			tribe_get_view( 'pro/map/content' );
			$response['html'] .= ob_get_clean();
		}

		apply_filters( 'tribe_events_ajax_response', $response );

		header( 'Content-type: application/json' );
		echo json_encode( $response );

		exit;

	}


	/**
	 * Orders a list of posts by distance to a given geo point
	 * @param $posts
	 * @param $lat_from
	 * @param $lng_from
	 */
	private function order_posts_by_distance( &$posts, $lat_from, $lng_from ) {

		// add distances
		for ( $i = 0; $i < count( $posts ); $i ++ ) {
			$posts[$i]->lat      = $this->get_lat_for_event( $posts[$i]->ID );
			$posts[$i]->lng      = $this->get_lng_for_event( $posts[$i]->ID );
			$posts[$i]->distance = $this->get_distance_between_coords( $lat_from, $lng_from, $posts[$i]->lat, $posts[$i]->lng );
		}

		//sort
		$this->quickSort( $posts );

		//no return, $posts passed by ref
	}

	/**
	 * QuickSort Implementation and benchmark from: http://stackoverflow.com/questions/1462503/sort-array-by-object-property-in-php
	 *
	 * @param $array
	 */
	private function quickSort( &$array ) {
		$cur           = 1;
		$stack[1]['l'] = 0;
		$stack[1]['r'] = count( $array ) - 1;

		do {
			$l = $stack[$cur]['l'];
			$r = $stack[$cur]['r'];
			$cur --;

			do {
				$i   = $l;
				$j   = $r;
				$tmp = $array[(int) ( ( $l + $r ) / 2 )];

				do {
					/* Divide... */
					while ( $array[$i]->distance < $tmp->distance ) {
						$i ++;
					}

					while ( $tmp->distance < $array[$j]->distance ) {
						$j --;
					}

					/* ...and conquer! */
					if ( $i <= $j ) {
						$w         = $array[$i];
						$array[$i] = $array[$j];
						$array[$j] = $w;

						$i ++;
						$j --;
					}

				} while ( $i <= $j );

				if ( $i < $r ) {
					$cur ++;
					$stack[$cur]['l'] = $i;
					$stack[$cur]['r'] = $r;
				}
				$r = $j;

			} while ( $l < $r );

		} while ( $cur != 0 );


	}

	/**
	 * Implementation of the Haversine Formula to get the distance in kms between two geo points
	 *
	 * @param float $lat_from
	 * @param float $lng_from
	 * @param float $lat_to
	 * @param float $lng_to
	 *
	 * @return float
	 */
	public function get_distance_between_coords( $lat_from, $lng_from, $lat_to, $lng_to ) {

		$delta_lat = $lat_to - $lat_from;
		$delta_lng = $lng_to - $lng_from;


		$a        = sin( deg2rad( (double) ( $delta_lat / 2 ) ) ) * sin( deg2rad( (double) ( $delta_lat / 2 ) ) ) + cos( deg2rad( (double) $lat_from ) ) * cos( deg2rad( (double) $lat_to ) ) * sin( deg2rad( (double) ( $delta_lng / 2 ) ) ) * sin( deg2rad( (double) ( $delta_lng / 2 ) ) );
		$c        = asin( min( 1, sqrt( $a ) ) );
		$distance = 2 * self::EARTH_RADIO * $c;
		$distance = round( $distance, 4 );

		return $distance;
	}

	/**
	 * Returns the latitude of the venue for an event
	 *
	 * @param $event_id
	 *
	 * @return mixed
	 */
	public function get_lat_for_event( $event_id ) {
		$venue = tribe_get_venue_id( $event_id );

		return get_post_meta( $venue, self::LAT, true );
	}

	/**
	 * Returns the longitude of the venue for an event
	 *
	 * @param $event_id
	 *
	 * @return mixed
	 */
	public function get_lng_for_event( $event_id ) {
		$venue = tribe_get_venue_id( $event_id );

		return get_post_meta( $venue, self::LNG, true );
	}

	/**
	 * Gets an estimated point in the center of all the venues
	 * so we can center the map in the first load of map view
	 *
	 * @return mixed
	 */
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
				        WHERE  ( meta_key = '" . self::LAT . "'
				            OR meta_key = '" . self::LNG . "')
				            AND post_id IN (SELECT meta_value FROM $wpdb->postmeta WHERE meta_key='_EventVenueID')
				            ) coors
		";

			$data = $wpdb->get_results( $sql, ARRAY_A );

			if ( ! empty( $data ) )
				$data = array_shift( $data );

			set_transient( self::ESTIMATION_CACHE_KEY, $data, 5000 );
		}

		return $data;

	}

	/**
	 * Generates the array of markers to pin the events in the Google Map embed in the map view
	 *
	 * @param $events
	 *
	 * @return array
	 */
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


	/**
	 * Generates the button to add the geo data info to all venues that are missing it
	 * @return string
	 */
	private function fix_geoloc_data_button() {
		$settings = TribeSettings::instance();
		$url      = apply_filters( 'tribe_settings_url', add_query_arg( array( 'post_type' => TribeEvents::POSTTYPE, 'page' => $settings->adminSlug ), admin_url( 'edit.php' ) ) );
		$url      = add_query_arg( array( 'geoloc_fix_venues' => '1' ), $url );
		$url      = wp_nonce_url( $url, 'geoloc_fix_venues' );

		return sprintf( '<a href="%s" class="button">%s</a>', $url, __( "Fix venues data", "tribe-events-calendar-pro" ) );
	}

	/**
	 * Check if there are venues without geo data and hook into admin_notices to show a message to the user.
	 */
	public function maybe_offer_generate_geopoints() {
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX )
			return;

		$done = get_option( '_tribe_geoloc_fixed' );

		if ( ! empty( $done ) )
			return;

		$venues = $this->get_venues_without_geoloc_info();

		if ( $venues->found_posts === 0 )
			return;

		add_action( 'admin_notices', array( $this, 'show_offer_to_fix_notice' ) );

	}

	/**
	 * If there are venues without geo data, offer the user to fix them.
	 */
	public function show_offer_to_fix_notice() {

		$settings = TribeSettings::instance();
		$url      = apply_filters( 'tribe_settings_url', add_query_arg( array( 'post_type' => TribeEvents::POSTTYPE, 'page' => $settings->adminSlug ), admin_url( 'edit.php' ) ) );

		?>
		<div class="updated">
			<p><?php echo sprintf( __( "You have venues for which we don't have Geolocation information. <a href='%s'>Click here to generate it</a>.", 'tribe-events-calendar-pro' ), $url . '#geoloc_fix' ); ?></p>
		</div>
	<?php
	}

	/**
	 * If the user pressed the button to fix all the venues without geo data, it shows a message
	 * showing the amount of venues fixed.
	 */
	public function maybe_generate_geopoints_for_all_venues() {

		if ( empty( $_GET['geoloc_fix_venues'] ) || ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'geoloc_fix_venues' ) )
			return;

		$this->last_venues_fixed_count = $this->generate_geopoints_for_all_venues();

		add_action( 'admin_notices', array( $this, 'show_fixed_notice' ) );

	}

	/**
	 * Shows a message with the amount of venues fixed.
	 */
	public function show_fixed_notice() {
		?>
		<div class="updated">
			<p><?php echo sprintf( __( 'Fixed geolocation data for %d venues', 'tribe-events-calendar-pro' ), $this->last_venues_fixed_count ); ?></p>
		</div>
	<?php
	}

	/**
	 * Grabs all the venues without geo data and uses the Google Maps API to get it.
	 *
	 * @static
	 * @return int
	 */
	public function generate_geopoints_for_all_venues() {

		set_time_limit( 5 * 60 );

		$venues = $this->get_venues_without_geoloc_info(true);

		$count  = 0;
		foreach ( $venues->posts as $venue ) {
			$data             = array();
			$data["Address"]  = get_post_meta( $venue->ID, '_VenueAddress', true );
			$data["City"]     = get_post_meta( $venue->ID, '_VenueCity', true );
			$data["Province"] = get_post_meta( $venue->ID, '_VenueProvince', true );
			$data["State"]    = get_post_meta( $venue->ID, '_VenueState', true );
			$data["Zip"]      = get_post_meta( $venue->ID, '_VenueZip', true );
			$data["Country"]  = get_post_meta( $venue->ID, '_VenueCountry', true );

			self::instance()->save_venue_geodata( $venue->ID, $data );

			$count ++;

		}

		update_option( '_tribe_geoloc_fixed', 1 );

		return $count;

	}

	/**
	 * Static Singleton Factory Method
	 * @return TribeEventsGeoLoc
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			$className      = __CLASS__;
			self::$instance = new $className;
		}

		return self::$instance;
	}

}
