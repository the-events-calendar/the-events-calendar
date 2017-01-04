<?php


/**
 * Class Tribe__Events__REST__V1__Main
 *
 * The main entry point for TEC REST API.
 *
 * This class should not contain business logic and merely set up and start the TEC REST API support.
 */
class Tribe__Events__REST__V1__Main extends Tribe__REST__Main {

	/**
	 * The Events Calendar REST API URL prefix.
	 *
	 * This prefx is appended to the Modern Tribe REST API URL ones.
	 *
	 * @var string
	 */
	protected $url_prefix = '/events/v1';


	/**
	 * Binds the implementations needed to support the REST API.
	 */
	public function bind_implementations() {
		tribe_singleton( 'tec.rest-v1.headers-base', 'Tribe__Events__REST__V1__Headers__Base' );
		tribe_singleton( 'tec.rest-v1.settings', 'Tribe__Events__REST__V1__Settings' );
		tribe_singleton( 'tec.rest-v1.system', 'Tribe__Events__REST__V1__System' );

		include_once Tribe__Events__Main::instance()->plugin_path . 'src/functions/advanced-functions/rest-v1.php';
	}

	/**
	 * Hooks the filters and actions required for the REST API support to kick in.
	 */
	public function hook() {
		$this->hook_settings();
		$this->hook_headers();
	}

	/**
	 * Returns the REST API URL prefix that will be appended to the namespace.
	 *
	 * The prefix should be in the `/some/path` format.
	 *
	 * @return string
	 */
	protected function url_prefix() {
		return $this->url_prefix;
	}

	/**
	 * Returns the string indicating the REST API version.
	 *
	 * @return string
	 */
	public function get_version() {
		return 'v1';
	}

	/**
	 * Returns the URL where the API users will find the API documentation.
	 *
	 * @return string
	 */
	public function get_reference_url() {
		return esc_attr( 'htt://theeventscalendar.com' );
	}

	/**
	 * Hooks the additional headers and meta tags related to the REST API.
	 */
	protected function hook_headers() {
		/** @var Tribe__Events__REST__V1__System $system */
		$system = tribe( 'tec.rest-v1.system' );
		/** @var Tribe__REST__Headers__Base_Interface $headers_base */
		$headers_base = tribe( 'tec.rest-v1.headers-base' );

		if ( ! $system->tec_rest_api_is_enabled() ) {
			if ( ! $system->supports_tec_rest_api() ) {
				tribe_singleton( 'tec.rest-v1.headers', new Tribe__REST__Headers__Unsupported( $headers_base, $this ) );
			} else {
				tribe_singleton( 'tec.rest-v1.headers', new Tribe__REST__Headers__Disabled( $headers_base ) );
			}
		} else {
			tribe_singleton( 'tec.rest-v1.headers', new Tribe__REST__Headers__Supported( $headers_base, $this ) );
		}

		/** @var Tribe__REST__Headers__Headers_Interface $headers */
		$headers = tribe( 'tec.rest-v1.headers' );

		add_action( 'wp_head', array( $headers, 'add_header' ), 10, 0 );
		add_action( 'template_redirect', array( $headers, 'send_header' ), 11, 0 );
	}

	/**
	 * Hooks the additional Events Settings related to the REST API.
	 */
	protected function hook_settings() {
		add_filter( 'tribe_addons_tab_fields', array( tribe( 'tec.rest-v1.settings' ), 'filter_tribe_addons_tab_fields' ) );
	}
}
