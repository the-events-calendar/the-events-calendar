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
	 * @var array
	 */
	protected $registered_endpoints = array();

	/**
	 * Binds the implementations needed to support the REST API.
	 */
	public function bind_implementations() {
		tribe_singleton( 'tec.rest-v1.messages', 'Tribe__Events__REST__V1__Messages' );
		tribe_singleton( 'tec.rest-v1.ea-messages', 'Tribe__Events__REST__V1__EA_Messages' );
		tribe_singleton( 'tec.rest-v1.headers-base', 'Tribe__Events__REST__V1__Headers__Base' );
		tribe_singleton( 'tec.rest-v1.settings', 'Tribe__Events__REST__V1__Settings' );
		tribe_singleton( 'tec.rest-v1.system', 'Tribe__Events__REST__V1__System' );
		tribe_singleton( 'tec.rest-v1.validator', 'Tribe__REST__Validator' );
		tribe_singleton( 'tec.rest-v1.repository', 'Tribe__Events__REST__V1__Post_Repository' );

		include_once Tribe__Events__Main::instance()->plugin_path . 'src/functions/advanced-functions/rest-v1.php';
	}

	/**
	 * Hooks the filters and actions required for the REST API support to kick in.
	 */
	public function hook() {
		$this->hook_headers();
		$this->hook_settings();
		$this->hook_messages();

		/** @var Tribe__Events__REST__V1__System $system */
		$system = tribe( 'tec.rest-v1.system' );

		if ( ! $system->supports_tec_rest_api() ) {
			return;
		}

		add_action( 'rest_api_init', array( $this, 'register_endpoints' ) );
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
		add_filter( 'tribe_addons_tab_fields', array(
			tribe( 'tec.rest-v1.settings' ),
			'filter_tribe_addons_tab_fields'
		) );
	}

	/**
	 * Registers the endpoints, and the handlers, supported by the REST API
	 */
	public function register_endpoints() {
		$this->register_documentation_endpoint();
		$this->register_event_archives_endpoint();
		$this->register_single_event_endpoint();
	}

	protected function register_event_archives_endpoint() {
		$messages        = tribe( 'tec.rest-v1.messages' );
		$post_repository = tribe( 'tec.rest-v1.repository' );
		$endpoint        = new Tribe__Events__REST__V1__Endpoints__Archive_Event( $messages, $post_repository );

		tribe_singleton( 'tec.rest-v1.endpoints.archive-event', $endpoint );

		register_rest_route( $this->get_events_route_namespace(), '/events', array(
			'methods'  => 'GET',
			'callback' => array( $endpoint, 'get' ),
		) );

		tribe( 'tec.rest-v1.endpoints.documentation' )->register_documentation_provider( '/events', $endpoint );
	}

	/**
	 * Registers the endpoint that will handle requests for a single event.
	 */
	protected function register_single_event_endpoint() {
		$messages        = tribe( 'tec.rest-v1.messages' );
		$post_repository = tribe( 'tec.rest-v1.repository' );
		$endpoint        = new Tribe__Events__REST__V1__Endpoints__Single_Event( $messages, $post_repository );

		tribe_singleton( 'tec.rest-v1.endpoints.single-event', $endpoint );

		register_rest_route(
			$this->get_events_route_namespace(),
			'/events/(?P<id>\\d+)',
			array(
				'methods'  => 'GET',
				'args'     => array(
					'id' => array(
						'validate_callback' => array( tribe( 'tec.rest-v1.validator' ), 'is_numeric' )
					)
				),
				'callback' => array( $endpoint, 'get' ),
			)
		);

		tribe( 'tec.rest-v1.endpoints.documentation' )->register_documentation_provider( '/events/{id}', $endpoint );
	}

	/**
	 * Returns the events REST API namespace string that should be used to register a route.
	 *
	 * @return string
	 */
	protected function get_events_route_namespace() {
		return $this->get_namespace() . '/events/' . $this->get_version();
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
		return esc_attr( 'https://theeventscalendar.com' );
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

	protected function hook_messages() {
		add_filter( 'tribe_aggregator_service_messages', array( $this, 'filter_service_messages' ) );
		add_filter( 'tribe_aggregator_localized_data', array( $this, 'filter_localized_data' ) );
	}

	/**
	 * Filters the messages returned by the Event Aggregator Service to add those specific to the REST API v1.
	 *
	 * @param array $messages
	 *
	 * @return array The original messages plus those specific to the REST API V1.
	 */
	public function filter_service_messages( array $messages = array() ) {
		/** @var Tribe__REST__Messages_Interface $rest_messages */
		$rest_messages               = tribe( 'tec.rest-v1.ea-messages' );
		$messages_array              = $rest_messages->get_messages();
		$prefixed_rest_messages_keys = array_map( array(
			$rest_messages,
			'prefix_message_slug'
		), array_keys( $messages_array ) );
		$messages                    = array_merge( $messages, array_combine( $prefixed_rest_messages_keys, array_values( $messages_array ) ) );

		return $messages;
	}

	/**
	 * Filters the messages localized by the Event Aggregator Service to add those specific to the REST API v1.
	 *
	 * @param array $localized_data
	 *
	 * @return array
	 */
	public function filter_localized_data( array $localized_data = array() ) {
		/** @var Tribe__REST__Messages_Interface $rest_messages */
		$rest_messages                 = tribe( 'tec.rest-v1.ea-messages' );
		$localized_data['l10n']['url'] = $rest_messages->get_messages();

		return $localized_data;
	}

	protected function register_documentation_endpoint() {
		$endpoint = new Tribe__Events__REST__V1__Endpoints__Swagger_Documentation( $this->get_semantic_version() );

		tribe_singleton( 'tec.rest-v1.endpoints.documentation', $endpoint );

		register_rest_route( $this->get_events_route_namespace(), '/doc', array(
			'methods'  => 'GET',
			'callback' => array( $endpoint, 'get' ),
		) );

		/** @var Tribe__Documentation__Swagger__Builder_Interface $documentation */
		$documentation = tribe( 'tec.rest-v1.endpoints.documentation' );
		$documentation->register_documentation_provider( '/doc', $endpoint );
		$documentation->register_definition_provider( 'Event', new Tribe__Events__REST__V1__Documentation__Event_Definition_Provider() );
		$documentation->register_definition_provider( 'Venue', new Tribe__Events__REST__V1__Documentation__Venue_Definition_Provider() );
		$documentation->register_definition_provider( 'Organizer', new Tribe__Events__REST__V1__Documentation__Organizer_Definition_Provider() );
		$documentation->register_definition_provider( 'Image', new Tribe__Documentation__Swagger__Image_Definition_Provider() );
		$documentation->register_definition_provider( 'ImageSize', new Tribe__Documentation__Swagger__Image_Size_Definition_Provider() );
		$documentation->register_definition_provider( 'DateDetails', new Tribe__Documentation__Swagger__Date_Details_Definition_Provider() );
		$documentation->register_definition_provider( 'CostDetails', new Tribe__Documentation__Swagger__Cost_Details_Definition_Provider() );
		$documentation->register_definition_provider( 'Term', new Tribe__Documentation__Swagger__Term_Definition_Provider() );
	}

	protected function get_semantic_version() {
		return '1.0.0';
	}
}
