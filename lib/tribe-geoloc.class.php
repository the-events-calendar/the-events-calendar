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

	const LAT        = '_VenueLat';
	const LNG        = '_VenueLng';
	const ADDRESS    = '_VenueGeoAddress';
	const OPTIONNAME = 'tribe_geoloc_options';


	protected static $options;
	protected $rewrite_slug;

	function __construct() {

		$this->rewrite_slug = $this->getOption( 'rewrite_slug', 'map' );

		add_action( 'wp_router_generate_routes', array( $this, 'add_routes' ) );

		add_action( 'tribe_events_venue_updated', array( $this, 'save_venue_geodata' ), 10, 2 );
		add_action( 'tribe_events_venue_created', array( $this, 'save_venue_geodata' ), 10, 2 );

		add_action( 'wp_ajax_geosearch', array( $this, 'ajax_geosearch' ) );
		add_action( 'wp_ajax_nopriv_geosearch', array( $this, 'ajax_geosearch' ) );

		add_filter( 'tribe-events-bar-views', array( $this, 'setup_view_for_bar' ) );

	}


	public function scripts() {

		wp_register_script( 'gmaps', 'http://maps.google.com/maps/api/js?sensor=false', array( 'jquery' ) );
		wp_register_script( 'tribe-geoloc', trailingslashit( TribeEventsPro::instance()->pluginUrl ) . 'resources/maps.js', array( 'gmaps' ) );
		wp_enqueue_script( 'tribe-geoloc' );

		$data = array( 'ajaxurl' => admin_url( 'admin-ajax.php' ), 'nonce'   => wp_create_nonce( 'geosearch' ) );

		wp_localize_script( 'tribe-geoloc', 'GeoLoc', $data );
	}

	public function setup_view_for_bar( $views ) {
		$tec = TribeEvents::instance();
		$views[] = array( 'displaying' => 'map', 'anchor' => 'Map', 'url' =>  $tec->getOption( 'eventsSlug', 'events' ) . '/' . $this->rewrite_slug );

		return $views;
	}

	public function add_routes( $router ) {

		$tec = TribeEvents::instance();

		// list events
		$router->add_route( 'geoloc-list-route', array( 'path'            => '^' . $tec->getOption( 'eventsSlug', 'events' ) . '/' . $this->rewrite_slug . '$',
		                                                'query_vars'      => array( 'eventDisplay' => 'map' ),
		                                                'page_callback'   => array( $this, 'map_view' ),
		                                                'access_callback' => true,
		                                                'title'           => apply_filters( 'tribe_geoloc_page_title', __( 'Geo Search', 'tribe-events-calendar-pro' ) ),
		                                                'template'        => array( 'page.php',
		                                                                            dirname( __FILE__ ) . '/page.php' ) ) );


	}

	public function map_view() {

		add_filter( 'tribe-events-bar-should-show', '__return_true' );

		$this->scripts();
		$pro = TribeEventsPro::instance();
		include $pro->pluginPath . 'views/map.php';

	}

	function save_venue_geodata( $venueId, $data ) {

		$address = trim( $data["Address"] . ' ' . $data["City"] . ' ' . $data["Province"] . ' ' . $data["State"] . ' ' . $data["Zip"] . ' ' . $data["Country"] );

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

	function ajax_geosearch() {

		$action = isset( $_POST["action"] ) ? $_POST["action"] : false;
		$lat    = isset( $_POST["lat"] ) ? (float)$_POST["lat"] : false;
		$lng    = isset( $_POST["lng"] ) ? (float)$_POST["lng"] : false;
		$nonce  = isset( $_POST["nonce"] ) ? $_POST["nonce"] : false;

		//		if ( !wp_verify_nonce( $nonce, 'geosearch' ) ) {
		//			echo "-1";
		//			exit;
		//		}
		if ( !$action || !$lat || !$lng ) {
			echo "-1";
			exit;
		}


		//First lets create a bounding box so we don't need to calculate distance to really far points

		$earth_radio    = 6371; //Earth radius. Should be in miles?
		$geofence_radio = 50; //Geofence. Limit the search to a 50km radius from the given point

		$maxLat = $lat + rad2deg( $geofence_radio / $earth_radio );
		$minLat = $lat - rad2deg( $geofence_radio / $earth_radio );
		$maxLng = $lng + rad2deg( $geofence_radio / $earth_radio / cos( deg2rad( $lat ) ) );
		$minLng = $lng - rad2deg( $geofence_radio / $earth_radio / cos( deg2rad( $lat ) ) );

		global $wpdb;

		//FTW!
		$sql = "
				SELECT p.*, geolocated.distance FROM $wpdb->posts p INNER JOIN (
					SELECT post_id, max(lat) AS lat, max(lng) AS lng,
					((2 * $earth_radio *
				        ATAN2(
				          SQRT(
				            POWER(SIN((RADIANS($lat - max(lat) ))/2), 2) +
				            COS(RADIANS(max(lat) )) *
				            COS(RADIANS($lat)) *
				            POWER(SIN((RADIANS($lng - max(lng) ))/2), 2)
				          ),
				          SQRT(1-(
				            POWER(SIN((RADIANS($lat - max(lat) ))/2), 2) +
				            COS(RADIANS(max(lat) )) *
				            COS(RADIANS($lat)) *
				            POWER(SIN((RADIANS($lng - max(lng) ))/2), 2)
				          ))
				        )
				      )) AS distance
				    FROM (
						SELECT post_id, lat AS lat, lng AS lng FROM(
						  SELECT
						    post_id,
						    CASE WHEN meta_key = '" . self::LAT . "' THEN meta_value END AS LAT,
						    CASE WHEN meta_key = '" . self::LNG . "' THEN meta_value END AS LNG
						      FROM $wpdb->postmeta
						      WHERE meta_key = '" . self::LAT . "' OR meta_key = '" . self::LNG . "'
						) coords
						WHERE (lat > $minLat OR lat IS NULL) AND (lat < $maxLat OR lat IS NULL) AND (lng > $minLng OR lng IS NULL) AND (lng < $maxLng OR lng IS NULL)
						) first_cut
					GROUP BY post_id
					HAVING lat IS NOT NULL AND lng IS NOT NULL
					) geolocated ON p.id = geolocated.post_id
				ORDER BY geolocated.distance
			";

		$data = $wpdb->get_results( $sql, ARRAY_A );

		echo "<h2>Nearest places</h2>";
		echo sprintf( "<p class='found'>%d events found</p>", count( $data ) );

		if ( count( $data ) > 0 ) {


			echo "<div class='post'>";
			echo "<span class='title heading'>Venue</span>";
			echo "<span class='address heading'>Address</span>";
			echo "<span class='distance heading'>Distance (Kms.)</span>";
			echo "</div>";

			foreach ( $data as $post ) {

				echo "<div class='post'>";
				echo "<span class='title'>" . $post["post_title"] . "</span>";
				echo "<span class='address'>" . get_post_meta( $post["ID"], self::ADDRESS, true ) . "</span>";
				echo "<span class='distance'>" . round( $post["distance"], 2 ) . "</span>";
				echo "</div>";

			}
		}


		exit;

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
