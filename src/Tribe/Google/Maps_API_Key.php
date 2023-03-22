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
	public static $api_key_option_name = 'google_maps_js_api_key';

	/**
	 * The Events Calendar's default Google Maps API Key, which supports the Basic Embed API.
	 *
	 * @since 4.6.24
	 *
	 * @var string
	 */
	public static $default_api_key = 'AIzaSyDNsicAsP6-VuGtAb1O9riI3oc_NOb7IOU';

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

		$tooltip = sprintf(
			'<p><strong>%1$s</strong></p> <p><a href="https://theeventscalendar.com/knowledgebase/setting-up-your-google-maps-api-key/" target="_blank">%2$s</a> %3$s',
			esc_html__( 'You are using a custom Google Maps API key.', 'the-events-calendar' ),
			esc_html__( 'Click here', 'the-events-calendar' ),
			esc_html__( 'to learn more about using it with The Events Calendar', 'the-events-calendar' )
		);

		if ( tribe_is_using_basic_gmaps_api() ) {
			$tooltip = $this->get_basic_embed_api_tooltip();
		}

		$gmaps_api_fields = [
			'gmaps-js-api-start' => [
				'type' => 'html',
				'html' => '<h3>' . esc_html__( 'Google Maps API', 'the-events-calendar' ) . '</h3>',
			],

			'gmaps-js-api-info-box' => [
				'type' => 'html',
				'html' => '<p>' . sprintf(
						__(
							'The Events Calendar comes with an API key for basic maps functionality. If you’d like to use more advanced features like custom map pins or dynamic map loads, you’ll need to get your own %1$s. %2$s.',
							'the-events-calendar'
						),
						'<a href="https://developers.google.com/maps/documentation/javascript/get-api-key" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Google Maps API key', 'the-events-calendar' ) . '</a>',
						'<a href="https://theeventscalendar.com/knowledgebase/setting-up-your-google-maps-api-key/" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Read more', 'the-events-calendar' ) . '</a>'
					) . '</p>',
			],

			self::$api_key_option_name => [
				'type'            => 'text',
				'label'           => esc_html__( 'Google Maps API key', 'the-events-calendar' ),
				'tooltip'         => $tooltip,
				'size'            => 'medium',
				'validation_type' => 'alpha_numeric_with_dashes_and_underscores',
				'can_be_empty'    => true,
				'parent_option'   => Tribe__Events__Main::OPTIONNAME,
			],
		];

		return array_merge( (array) $addon_fields, $gmaps_api_fields );
	}

	/**
	 * Generates the tooltip text for when The Events Calendar's fallback API key is being used instead of a custom one.
	 *
	 * @since 4.6.24
	 *
	 * @return string
	 */
	public function get_basic_embed_api_tooltip() {
		return sprintf(
			'<p><strong>%1$s</strong></p><p>%2$s <a href="https://theeventscalendar.com/knowledgebase/setting-up-your-google-maps-api-key/">%3$s</a>.</p><p><a href="https://developers.google.com/maps/documentation/javascript/get-api-key" target="_blank">%4$s</a> %5$s</p>',
			esc_html__( 'You are using The Events Calendar\'s built-in Google Maps API key.', 'the-events-calendar' ),
			esc_html__( 'If you do not add your own API key, the built-in API key will always populate this field and some map-related functionality will be limited ', 'the-events-calendar' ),
			esc_html__( '(click here for details)', 'the-events-calendar' ),
			esc_html__( 'Click here', 'the-events-calendar' ),
			esc_html__( 'to create your own free Google Maps API key.', 'the-events-calendar' )
		);
	}

	/**
	 * Adds the browser key api key to the Google Maps JavaScript API url if set by the user.
	 *
	 * @param string $js_maps_api_url
	 *
	 * @return string
	 */
	public function filter_tribe_events_google_maps_api( $js_maps_api_url ) {
		$key = tribe_get_option( self::$api_key_option_name, self::$default_api_key );

		if ( ! empty( $key ) ) {
			$js_maps_api_url = add_query_arg( 'key', $key, $js_maps_api_url );
		}

		return $js_maps_api_url;
	}

	public function filter_tribe_events_pro_google_maps_api( $js_maps_api_url ) {

	}

	/**
	 * Ensures the Google Maps API Key field in Settings > Integrations is always populated with TEC's
	 * default API key if no user-supplied key is present.
	 *
	 * @since 4.6.24
	 *
	 * @param string $value_string The original HTML string for the input's value attribute.
	 * @param string $field_name The name of the field; usually the key of the option it's associated with.
	 * @return string The default license key as the input's new value.
	 */
	public function populate_field_with_default_api_key( $value_string, $field_name ) {

		if ( ! isset( $field_name ) || self::$api_key_option_name !== $field_name ) {
			return $value_string;
		}

		if ( empty( $value_string ) ) {

			remove_filter( 'tribe_field_value', [ $this, 'populate_field_with_default_api_key' ], 10, 2 );

			$value_string = self::$default_api_key;

			tribe_update_option( self::$api_key_option_name, self::$default_api_key );

			add_filter( 'tribe_field_value', [ $this, 'populate_field_with_default_api_key' ], 10, 2 );
		}

		return $value_string;
	}

	/**
	 * Ensures the Google Maps API Key field in Settings > Integrations shows the correct tooltip text, especially when
	 * the auto-populating of the field is done via populate_field_with_default_api_key().
	 *
	 * @since 4.6.24
	 *
	 * @param string $tooltip_string The original HTML string for the input's tooltip attribute.
	 * @param string $field_name The name of the field; usually the key of the option it's associated with.
	 * @return string The default license key as the input's new value.
	 */
	public function populate_field_tooltip_with_helper_text( $tooltip_string, $field_name ) {

		if ( ! isset( $field_name ) || self::$api_key_option_name !== $field_name ) {
			return $tooltip_string;
		}

		$api_key = tribe_get_option( self::$api_key_option_name, self::$default_api_key );

		if ( empty( $api_key ) || self::$default_api_key === $api_key ) {
			$tooltip_string = $this->get_basic_embed_api_tooltip();
		}

		return $tooltip_string;
	}
}
