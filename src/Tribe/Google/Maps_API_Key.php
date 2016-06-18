<?php


/**
 * Class Tribe__Events__Google__Maps_API_Key
 *
 * Handles support for the Google Maps API key.
 */
class Tribe__Events__Google__Maps_API_Key {

	/**
	 * @var string
	 */
	protected $api_key_option_name = 'google_maps_js_api_key';

	/**
	 * @var static
	 */
	protected static $instance;

	/**
	 * The class singleton constructor.
	 *
	 * @return Tribe__Events__Google__Maps_API_Key
	 */
	public static function instance() {
		if ( empty( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Adds Google Maps API key fields to the addon fields.
	 *
	 * @param array $addon_fields
	 *
	 * @return array
	 */
	public function filter_tribe_addons_tab_fields( array $addon_fields ) {
		$gmaps_api_fields = array(
			'gmaps-js-api-start' => array(
				'type' => 'html',
				'html' => '<h3>' . esc_html__( 'Google Maps API', 'the-events-calendar' ) . '</h3>',
			),

			'gmaps-js-api-info-box' => array(
				'type' => 'html',
				'html' => '<p>' . sprintf( __( 'We highly recommend that you specify a valid %s for The Events Calendar to use. Doing this will help prevent problems with maps, especially for sites that receive a lot of traffic.',
						'the-events-calendar' ), '<a href="https://developers.google.com/maps/documentation/javascript/get-api-key" target="_blank">' . __( 'Google Maps API key',
							'the-events-calendar' ) . '</a>' ) . '</p>',
			),

			$this->api_key_option_name => array(
				'type'            => 'text',
				'label'           => esc_html__( 'Google Maps API key', 'the-events-calendar' ),
				'tooltip'         => sprintf( __( '<p>%s to create your Google Maps API key.', 'the-events-calendar' ),
					'<a href="https://developers.google.com/maps/documentation/javascript/get-api-key" target="_blank"></p>' . __( 'Click here', 'the-events-calendar' ) . '</a>' ),
				'size'            => 'medium',
				'validation_type' => 'alpha_numeric_with_dashes_and_underscores',
				'can_be_empty'    => true,
				'parent_option'   => Tribe__Events__Main::OPTIONNAME,
			),
		);

		return array_merge( (array) $addon_fields, $gmaps_api_fields );
	}

	/**
	 * Adds the browser key api key to the Google Maps JavaScript API url if set by the user.
	 *
	 * @param string $js_maps_api_url
	 *
	 * @return string
	 */
	public function filter_tribe_events_google_maps_api( $js_maps_api_url ) {
		$key = tribe_get_option( $this->api_key_option_name );
		if ( ! empty( $key ) ) {
			$js_maps_api_url = add_query_arg( 'key', $key, $js_maps_api_url );
		}

		return $js_maps_api_url;
	}

	public function filter_tribe_events_pro_google_maps_api($js_maps_api_url  ) {
		
	}
}