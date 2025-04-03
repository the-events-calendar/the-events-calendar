<?php
/**
 * Facilitates embedding one or more maps utilizing the Google Maps API.
 */
class Tribe__Events__Embedded_Maps {
	/**
	 * Script handle for the embedded maps script.
	 */
	const MAP_HANDLE = 'tribe_events_embedded_map';

	/**
	 * @var Tribe__Events__Embedded_Maps
	 */
	protected static $instance;

	/**
	 * Post ID of the current event.
	 *
	 * @var int
	 */
	protected $event_id = 0;

	/**
	 * Post ID of the current venue (if known/if can be determined).
	 *
	 * @var int
	 */
	protected $venue_id = 0;

	/**
	 * Address of the current event/venue.
	 *
	 * @var string
	 */
	protected $address = '';

	/**
	 * Container for map address data (potentially allowing for multiple maps
	 * per page).
	 *
	 * @var array
	 */
	protected $embedded_maps = [];

	/**
	 * Indicates if the Google Maps API script has been enqueued.
	 *
	 * @var bool
	 */
	protected $map_script_enqueued = false;


	/**
	 * @return Tribe__Events__Embedded_Maps
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Returns the placeholder HTML needed to embed a map within a page and
	 * additionally enqueues supporting scripts, etc.
	 *
	 * @param int  $post_id ID of the pertinent event or venue
	 * @param int  $width
	 * @param int  $height
	 * @param bool $force_load add the map even if no address data can be found
	 *
	 * @return string
	 */
	public function get_map( $post_id, $width, $height, $force_load ) {
		$this->get_ids( $post_id );

		// Bail if either the venue or event couldn't be determined
		if ( ! tribe_is_venue( $this->venue_id ) && ! tribe_is_event( $this->event_id ) ) {
			return apply_filters( 'tribe_get_embedded_map', '' );
		}

		$this->form_address();

		if ( empty( $this->address ) && ! $force_load ) {
			return apply_filters( 'tribe_get_embedded_map', '' );
		}

		$this->embedded_maps[] = [
			'address' => $this->address,
			'title'   => esc_html( get_the_title( $this->venue_id ) ),
		];

		end( $this->embedded_maps );
		$index = key( $this->embedded_maps );

		ob_start();

		if ( is_numeric( $width ) ) {
			$width .= 'px';
		}

		if ( is_numeric( $height ) ) {
			$height .= 'px';
		}

		if ( tribe_is_using_basic_gmaps_api() ) {

			// Get a basic embed that doesn't use the JavaScript API
			tribe_get_template_part(
				'modules/map-basic',
				null,
				[
					'venue'     => esc_html( get_the_title( $this->venue_id ) ),
					'embed_url' => tribe_get_basic_gmap_embed_url( $this->address ),
					'address'   => $this->address,
					'index'     => $index,
					'width'     => null === $width ? apply_filters( 'tribe_events_single_map_default_width', '100%' ) : $width,
					'height'    => null === $height ? apply_filters( 'tribe_events_single_map_default_height', '350px' ) : $height,
				]
			);

		} else {
		 	// Generate the HTML used to "house" the JavaScript API-enabled map
			tribe_get_template_part(
				'modules/map',
				null,
				[
					'index' => $index,
					'width' => null === $width ? apply_filters( 'tribe_events_single_map_default_width', '100%' ) : $width,
					'height' => null === $height ? apply_filters( 'tribe_events_single_map_default_height', '350px' ) : $height,
				]
			);
		}

		$this->setup_scripts();

		do_action( 'tribe_events_map_embedded', $index, $this->venue_id );
		return apply_filters( 'tribe_get_embedded_map', ob_get_clean() );
	}

	protected function get_ids( $post_id ) {
		$post_id = $post_id = Tribe__Events__Main::postIdHelper( $post_id );
		$this->event_id = tribe_is_event( $post_id ) ? $post_id : 0;
		$this->venue_id = tribe_is_venue( $post_id ) ? $post_id : tribe_get_venue_id( $post_id );
	}

	protected function form_address() {
		$this->address = '';
		$location_parts = [ 'address', 'city', 'state', 'province', 'zip', 'country' ];

		// Form the address string for the map
		foreach ( $location_parts as $val ) {
			$address_part = call_user_func( 'tribe_get_' . $val, $this->venue_id );
			if ( $address_part ) {
				$this->address .= $address_part . ' ';
			}
		}

		if ( class_exists( 'Tribe__Events__Pro__Geo_Loc' ) && empty( $this->address ) ) {
			$overwrite = (int) get_post_meta( $this->venue_id, Tribe__Events__Pro__Geo_Loc::OVERWRITE, true );
			if ( $overwrite ) {
				$lat = get_post_meta( $this->venue_id, Tribe__Events__Pro__Geo_Loc::LAT, true );
				$lng = get_post_meta( $this->venue_id, Tribe__Events__Pro__Geo_Loc::LNG, true );
				$this->address = $lat . ',' . $lng;
			}
		}
	}

	public function get_map_data( $map_index ) {
		return isset( $this->embedded_maps[ $map_index ] ) ? $this->embedded_maps[ $map_index ] : [];
	}

	public function update_map_data( $map_index, array $data ) {
		$this->embedded_maps[ $map_index ] = $data;
		$this->setup_scripts();
	}

	protected function setup_scripts() {
		if ( ! $this->map_script_enqueued ) {
			$this->enqueue_map_scripts();
		}

		// Provide address data
		wp_localize_script(
			self::MAP_HANDLE,
			'tribeEventsSingleMap',
			[
				'addresses' => $this->embedded_maps,
				'zoom'      => apply_filters(
					'tribe_events_single_map_zoom_level',
					(int) tribe_get_option( 'embedGoogleMapsZoom', 8 )
				),
				'pin_url'   => tribe( 'customizer' )->get_option( [ 'global_elements', 'map_pin' ], false ),
			]
		);
	}

	protected function enqueue_map_scripts() {

		$api_key = tribe_get_option( 'google_maps_js_api_key', Tribe__Events__Google__Maps_API_Key::$default_api_key );

		// bail if we don't have an API key
		if ( empty( $api_key ) ) {
			return;
		}

		tribe_asset_enqueue( 'tribe_events_google_maps_api' );
		tribe_asset_enqueue(  self::MAP_HANDLE );

		$this->map_script_enqueued = true;
	}
}
