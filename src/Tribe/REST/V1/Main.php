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
		tribe_singleton( 'tec.rest-v1.validator', 'Tribe__Events__REST__V1__Validator__Base' );
		tribe_singleton( 'tec.rest-v1.repository', 'Tribe__Events__REST__V1__Post_Repository' );
		tribe_singleton( 'tec.rest-v1.endpoints.single-venue', array( $this, 'build_single_venue_endpoint' ) );
		tribe_singleton( 'tec.rest-v1.endpoints.single-organizer', array( $this, 'build_single_organizer_endpoint' ) );
		tribe_singleton( 'tec.json-ld.event', array( 'Tribe__Events__JSON_LD__Event', 'instance' ) );
		tribe_singleton( 'tec.json-ld.venue', array( 'Tribe__Events__JSON_LD__Venue', 'instance' ) );
		tribe_singleton( 'tec.json-ld.organizer', array( 'Tribe__Events__JSON_LD__Organizer', 'instance' ) );

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
		add_filter( 'tribe_events_register_event_cat_type_args', array( $this, 'filter_taxonomy_args' ) );
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

	protected function hook_messages() {
		add_filter( 'tribe_aggregator_service_messages', array( $this, 'filter_service_messages' ) );
		add_filter( 'tribe_aggregator_localized_data', array( $this, 'filter_localized_data' ) );
	}

	/**
	 * Registers the endpoints, and the handlers, supported by the REST API
	 *
	 * @param bool $register_routes Whether routes should be registered as well or not.
	 */
	public function register_endpoints( $register_routes = true ) {
		$this->register_documentation_endpoint( $register_routes );
		$this->register_event_archives_endpoint( $register_routes );
		$this->register_single_event_endpoint( $register_routes );
		$this->register_single_event_slug_endpoint( $register_routes );
		$this->register_venue_archives_endpoint( $register_routes );
		$this->register_single_venue_endpoint( $register_routes );
		$this->register_organizer_archives_endpoint( $register_routes );
		$this->register_single_organizer_endpoint( $register_routes );

		global $wp_version;

		if ( version_compare( $wp_version, '4.7', '>=' ) ) {
			$this->register_categories_endpoint( $register_routes );
			$this->register_tags_endpoint( $register_routes );
		}
	}

	/**
	 * Builds and hooks the documentation endpoint
	 *
	 * @param bool $register_routes Whether routes for the endpoint should be registered or not.
	 *
	 * @since 4.5
	 */
	protected function register_documentation_endpoint( $register_routes = true ) {
		$endpoint = new Tribe__Events__REST__V1__Endpoints__Swagger_Documentation( $this->get_semantic_version() );

		tribe_singleton( 'tec.rest-v1.endpoints.documentation', $endpoint );

		if ( $register_routes ) {
			register_rest_route( $this->get_events_route_namespace(), '/doc', array(
				'methods'  => WP_REST_Server::READABLE,
				'callback' => array( $endpoint, 'get' ),
			) );
		}

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
	 * Builds and hooks the event archives endpoint
	 *
	 * @param bool $register_routes Whether routes for the endpoint should be registered or not.
	 *
	 * @since 4.5
	 */
	protected function register_event_archives_endpoint( $register_routes = true ) {
		$messages = tribe( 'tec.rest-v1.messages' );
		$post_repository = tribe( 'tec.rest-v1.repository' );
		$validator = tribe( 'tec.rest-v1.validator' );
		$endpoint = new Tribe__Events__REST__V1__Endpoints__Archive_Event( $messages, $post_repository, $validator );

		tribe_singleton( 'tec.rest-v1.endpoints.archive-event', $endpoint );

		if ( $register_routes ) {
			register_rest_route( $this->get_events_route_namespace(), '/events', array(
				'methods'  => WP_REST_Server::READABLE,
				'callback' => array( $endpoint, 'get' ),
				'args'     => $endpoint->READ_args(),
			) );
		}

		tribe( 'tec.rest-v1.endpoints.documentation' )->register_documentation_provider( '/events', $endpoint );
	}

	/**
	 * Registers the endpoint that will handle requests for a single event.
	 *
	 * @param bool $register_routes Whether routes for the endpoint should be registered or not.
	 *
	 * @since 4.5
	 */
	protected function register_single_event_endpoint( $register_routes = true ) {
		$messages = tribe( 'tec.rest-v1.messages' );
		$post_repository = tribe( 'tec.rest-v1.repository' );
		$validator = tribe( 'tec.rest-v1.validator' );
		$venue_endpoint = tribe( 'tec.rest-v1.endpoints.single-venue' );
		$organizer_endpoint = tribe( 'tec.rest-v1.endpoints.single-organizer' );

		$endpoint = new Tribe__Events__REST__V1__Endpoints__Single_Event( $messages, $post_repository, $validator, $venue_endpoint, $organizer_endpoint );

		tribe_singleton( 'tec.rest-v1.endpoints.single-event', $endpoint );

		$namespace = $this->get_events_route_namespace();

		if ( $register_routes ) {
			register_rest_route(
				$namespace,
				'/events/(?P<id>\\d+)',
				array(
					array(
						'methods'  => WP_REST_Server::READABLE,
						'args'     => $endpoint->READ_args(),
						'callback' => array( $endpoint, 'get' ),
					),
					array(
						'methods'             => WP_REST_Server::DELETABLE,
						'args'                => $endpoint->DELETE_args(),
						'permission_callback' => array( $endpoint, 'can_delete' ),
						'callback'            => array( $endpoint, 'delete' ),
					),
					array(
						'methods'             => WP_REST_Server::EDITABLE,
						'args'                => $endpoint->EDIT_args(),
						'permission_callback' => array( $endpoint, 'can_edit' ),
						'callback'            => array( $endpoint, 'update' ),
					),
				)
			);

			register_rest_route(
				$namespace,
				'/events', array(
					'methods'             => WP_REST_Server::CREATABLE,
					'args'                => $endpoint->CREATE_args(),
					'permission_callback' => array( $endpoint, 'can_create' ),
					'callback'            => array( $endpoint, 'create' ),
				)
			);
		}

		tribe( 'tec.rest-v1.endpoints.documentation' )->register_documentation_provider( '/events/{id}', $endpoint );
	}

	/**
	 * Registers the endpoint that will handle requests for a single event slug.
	 *
	 * @param bool $register_routes Whether routes for the endpoint should be registered or not.
	 *
	 * @since 4.5
	 */
	protected function register_single_event_slug_endpoint( $register_routes = true ) {
		$messages = tribe( 'tec.rest-v1.messages' );
		$post_repository = tribe( 'tec.rest-v1.repository' );
		$validator = tribe( 'tec.rest-v1.validator' );
		$venue_endpoint = tribe( 'tec.rest-v1.endpoints.single-venue' );
		$organizer_endpoint = tribe( 'tec.rest-v1.endpoints.single-organizer' );

		$endpoint = new Tribe__Events__REST__V1__Endpoints__Single_Event_Slug( $messages, $post_repository, $validator, $venue_endpoint, $organizer_endpoint );

		tribe_singleton( 'tec.rest-v1.endpoints.single-event-slug', $endpoint );

		$namespace = $this->get_events_route_namespace();

		if ( $register_routes ) {
			register_rest_route(
				$namespace,
				'/events/by-slug/(?P<slug>[^/]+)',
				array(
					array(
						'methods'  => WP_REST_Server::READABLE,
						'args'     => $endpoint->READ_args(),
						'callback' => array( $endpoint, 'get' ),
					),
					array(
						'methods'             => WP_REST_Server::DELETABLE,
						'args'                => $endpoint->DELETE_args(),
						'permission_callback' => array( $endpoint, 'can_delete' ),
						'callback'            => array( $endpoint, 'delete' ),
					),
					array(
						'methods'             => WP_REST_Server::EDITABLE,
						'args'                => $endpoint->EDIT_args(),
						'permission_callback' => array( $endpoint, 'can_edit' ),
						'callback'            => array( $endpoint, 'update' ),
					),
				)
			);
		}

		tribe( 'tec.rest-v1.endpoints.documentation' )->register_documentation_provider( '/events/by-slug/{slug}', $endpoint );
	}

	/**
	 * Returns the URL where the API users will find the API documentation.
	 *
	 * @return string
	 */
	public function get_reference_url() {
		return esc_attr( 'https://theeventscalendar.com/' );
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
		$rest_messages = tribe( 'tec.rest-v1.ea-messages' );
		$messages_array = $rest_messages->get_messages();
		$prefixed_rest_messages_keys = array_map( array(
			$rest_messages,
			'prefix_message_slug'
		), array_keys( $messages_array ) );
		$messages = array_merge( $messages, array_combine( $prefixed_rest_messages_keys, array_values( $messages_array ) ) );

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
		$rest_messages = tribe( 'tec.rest-v1.ea-messages' );
		$localized_data['l10n']['url'] = $rest_messages->get_messages();

		return $localized_data;
	}

	/**
	 * Builds an instance of the single venue endpoint.
	 *
	 * @return Tribe__Events__REST__V1__Endpoints__Single_Venue
	 */
	public function build_single_venue_endpoint() {
		$messages = tribe( 'tec.rest-v1.messages' );
		$post_repository = tribe( 'tec.rest-v1.repository' );
		$validator = tribe( 'tec.rest-v1.validator' );

		return new Tribe__Events__REST__V1__Endpoints__Single_Venue( $messages, $post_repository, $validator );
	}

	/**
	 * Builds an instance of the single organizer endpoint.
	 *
	 * @return Tribe__Events__REST__V1__Endpoints__Single_Organizer
	 */
	public function build_single_organizer_endpoint() {
		$messages = tribe( 'tec.rest-v1.messages' );
		$post_repository = tribe( 'tec.rest-v1.repository' );
		$validator = tribe( 'tec.rest-v1.validator' );

		return new Tribe__Events__REST__V1__Endpoints__Single_Organizer( $messages, $post_repository, $validator );
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
	 * Registers the endpoint that will handle requests for a single venue.
	 *
	 * @param bool $register_routes Whether routes for the endpoint should be registered or not.
	 *
	 * @since 4.6
	 */
	protected function register_single_venue_endpoint( $register_routes = true ) {
		$messages = tribe( 'tec.rest-v1.messages' );
		$post_repository = tribe( 'tec.rest-v1.repository' );
		$validator = tribe( 'tec.rest-v1.validator' );

		$endpoint = new Tribe__Events__REST__V1__Endpoints__Single_Venue( $messages, $post_repository, $validator );

		tribe_singleton( 'tec.rest-v1.endpoints.single-venue', $endpoint );

		$namespace = $this->get_events_route_namespace();

		if ( $register_routes ) {
			register_rest_route(
				$namespace,
				'/venues/(?P<id>\\d+)',
				array(
					array(
						'methods'  => WP_REST_Server::READABLE,
						'args'     => $endpoint->READ_args(),
						'callback' => array( $endpoint, 'get' ),
					),
					array(
						'methods'             => WP_REST_Server::DELETABLE,
						'args'                => $endpoint->DELETE_args(),
						'permission_callback' => array( $endpoint, 'can_delete' ),
						'callback'            => array( $endpoint, 'delete' ),
					),
					array(
						'methods'             => WP_REST_Server::EDITABLE,
						'args'                => $endpoint->EDIT_args(),
						'permission_callback' => array( $endpoint, 'can_edit' ),
						'callback'            => array( $endpoint, 'update' ),
					),
				)
			);

			register_rest_route(
				$namespace,
				'/venues',
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'args'                => $endpoint->CREATE_args(),
					'permission_callback' => array( $endpoint, 'can_create' ),
					'callback'            => array( $endpoint, 'create' ),
				)
			);
		}

		tribe( 'tec.rest-v1.endpoints.documentation' )->register_documentation_provider( '/venues/{id}', $endpoint );
	}

	/**
	 * Registers the endpoint that will handle requests for a single organizer.
	 *
	 * @param bool $register_routes Whether routes for the endpoint should be registered or not.
	 *
	 * @since bucket/full-rest-api
	 */
	protected function register_single_organizer_endpoint( $register_routes = true ) {
		$messages = tribe( 'tec.rest-v1.messages' );
		$post_repository = tribe( 'tec.rest-v1.repository' );
		$validator = tribe( 'tec.rest-v1.validator' );

		$endpoint = new Tribe__Events__REST__V1__Endpoints__Single_Organizer( $messages, $post_repository, $validator );

		tribe_singleton( 'tec.rest-v1.endpoints.single-organizer', $endpoint );

		$namespace = $this->get_events_route_namespace();

		if ( $register_routes ) {
			register_rest_route(
				$namespace,
				'/organizers/(?P<id>\\d+)',
				array(
					array(
						'methods'  => WP_REST_Server::READABLE,
						'args'     => $endpoint->READ_args(),
						'callback' => array( $endpoint, 'get' ),
					),
					array(
						'methods'             => WP_REST_Server::DELETABLE,
						'args'                => $endpoint->DELETE_args(),
						'permission_callback' => array( $endpoint, 'can_delete' ),
						'callback'            => array( $endpoint, 'delete' ),
					),
					array(
						'methods'             => WP_REST_Server::EDITABLE,
						'args'                => $endpoint->EDIT_args(),
						'permission_callback' => array( $endpoint, 'can_edit' ),
						'callback'            => array( $endpoint, 'update' ),
					),
				)
			);

			register_rest_route(
				$namespace,
				'/organizers',
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'args'                => $endpoint->CREATE_args(),
					'permission_callback' => array( $endpoint, 'can_create' ),
					'callback'            => array( $endpoint, 'create' ),
				)
			);
		}

		tribe( 'tec.rest-v1.endpoints.documentation' )->register_documentation_provider( '/organizers/{id}', $endpoint );
	}

	/**
	 * Builds and hooks the venue archives endpoint
	 *
	 * @param bool $register_routes Whether routes for the endpoint should be registered or not.
	 *
	 * @since 4.6
	 */
	protected function register_venue_archives_endpoint( $register_routes = true ) {
		$messages = tribe( 'tec.rest-v1.messages' );
		$post_repository = tribe( 'tec.rest-v1.repository' );
		$validator = tribe( 'tec.rest-v1.validator' );
		$endpoint = new Tribe__Events__REST__V1__Endpoints__Archive_Venue( $messages, $post_repository, $validator );

		tribe_singleton( 'tec.rest-v1.endpoints.archive-venue', $endpoint );

		if ( $register_routes ) {
			register_rest_route( $this->get_events_route_namespace(), '/venues', array(
				'methods'  => WP_REST_Server::READABLE,
				'callback' => array( $endpoint, 'get' ),
				'args'     => $endpoint->READ_args(),
			) );
		}

		tribe( 'tec.rest-v1.endpoints.documentation' )->register_documentation_provider( '/venues', $endpoint );
	}

	/**
	 * Builds and hooks the organizer archives endpoint
	 *
	 * @param bool $register_routes Whether routes for the endpoint should be registered or not.
	 *
	 * @since 4.6
	 */
	protected function register_organizer_archives_endpoint( $register_routes = true ) {
		$messages = tribe( 'tec.rest-v1.messages' );
		$post_repository = tribe( 'tec.rest-v1.repository' );
		$validator = tribe( 'tec.rest-v1.validator' );
		$endpoint = new Tribe__Events__REST__V1__Endpoints__Archive_Organizer( $messages, $post_repository, $validator );

		tribe_singleton( 'tec.rest-v1.endpoints.archive-organizer', $endpoint );

		if ( $register_routes ) {
			register_rest_route( $this->get_events_route_namespace(), '/organizers', array(
				'methods'  => WP_REST_Server::READABLE,
				'callback' => array( $endpoint, 'get' ),
				'args'     => $endpoint->READ_args(),
			) );
		}

		tribe( 'tec.rest-v1.endpoints.documentation' )->register_documentation_provider( '/organizers', $endpoint );
	}

	/**
	 * Builds and hooks the event categories archives endpoint
	 *
	 * @since 4.6
	 *
	 * @param bool $register_routes Whether routes for the endpoint should be registered or not.
	 */
	protected function register_categories_endpoint( $register_routes ) {
		$messages         = tribe( 'tec.rest-v1.messages' );
		$post_repository  = tribe( 'tec.rest-v1.repository' );
		$validator        = tribe( 'tec.rest-v1.validator' );
		$terms_controller = new WP_REST_Terms_Controller( Tribe__Events__Main::TAXONOMY );
		$archive_endpoint = new Tribe__Events__REST__V1__Endpoints__Archive_Category( $messages, $post_repository, $validator, $terms_controller );
		$single_endpoint  = new Tribe__Events__REST__V1__Endpoints__Single_Category( $messages, $post_repository, $validator, $terms_controller );

		tribe_singleton( 'tec.rest-v1.endpoints.archive-category', $archive_endpoint );

		if ( $register_routes ) {
			$namespace = $this->get_events_route_namespace();

			register_rest_route(
				$namespace,
				'/categories',
				array(
					array(
						'methods'  => WP_REST_Server::READABLE,
						'callback' => array( $archive_endpoint, 'get' ),
						'args'     => $archive_endpoint->READ_args(),
					),
					array(
						'methods'             => WP_REST_Server::CREATABLE,
						'args'                => $single_endpoint->CREATE_args(),
						'permission_callback' => array( $single_endpoint, 'can_create' ),
						'callback'            => array( $single_endpoint, 'create' ),
					),
				)
			);

			register_rest_route(
				$namespace,
				'/categories/(?P<id>\\d+)',
				array(
					array(
						'methods'  => WP_REST_Server::READABLE,
						'callback' => array( $single_endpoint, 'get' ),
						'args'     => $single_endpoint->READ_args(),
					),
					array(
						'methods'             => WP_REST_Server::EDITABLE,
						'args'                => $single_endpoint->EDIT_args(),
						'permission_callback' => array( $single_endpoint, 'can_edit' ),
						'callback'            => array( $single_endpoint, 'update' ),
					),
					array(
						'methods'             => WP_REST_Server::DELETABLE,
						'args'                => $single_endpoint->DELETE_args(),
						'permission_callback' => array( $single_endpoint, 'can_delete' ),
						'callback'            => array( $single_endpoint, 'delete' ),
					),
				)
			);
		}

		$documentation_endpoint = tribe( 'tec.rest-v1.endpoints.documentation' );
		$documentation_endpoint->register_documentation_provider( '/categories', $archive_endpoint );
		$documentation_endpoint->register_documentation_provider( '/categories/{id}', $single_endpoint );
	}

	/**
	 * Builds and hooks the event tags archives endpoint
	 *
	 * @since 4.6
	 *
	 * @param bool $register_routes Whether routes for the endpoint should be registered or not.
	 */
	protected function register_tags_endpoint( $register_routes ) {
		$messages         = tribe( 'tec.rest-v1.messages' );
		$post_repository  = tribe( 'tec.rest-v1.repository' );
		$validator        = tribe( 'tec.rest-v1.validator' );
		$terms_controller = new WP_REST_Terms_Controller( 'post_tag' );
		$archive_endpoint = new Tribe__Events__REST__V1__Endpoints__Archive_Tag( $messages, $post_repository, $validator, $terms_controller );
		$single_endpoint = new Tribe__Events__REST__V1__Endpoints__Single_Tag( $messages, $post_repository, $validator, $terms_controller );

		tribe_singleton( 'tec.rest-v1.endpoints.archive-category', $archive_endpoint );

		if ( $register_routes ) {
			$namespace = $this->get_events_route_namespace();

			register_rest_route(
				$namespace,
				'/tags',
				array(
					array(
						'methods'  => WP_REST_Server::READABLE,
						'callback' => array( $archive_endpoint, 'get' ),
						'args'     => $archive_endpoint->READ_args(),
					),
					array(
						'methods'             => WP_REST_Server::CREATABLE,
						'args'                => $single_endpoint->CREATE_args(),
						'permission_callback' => array( $single_endpoint, 'can_create' ),
						'callback'            => array( $single_endpoint, 'create' ),
					),
				)
			);

			register_rest_route(
				$namespace,
				'/tags/(?P<id>\\d+)',
				array(
					array(
						'methods'  => WP_REST_Server::READABLE,
						'callback' => array( $single_endpoint, 'get' ),
						'args'     => $single_endpoint->READ_args(),
					),
					array(
						'methods'             => WP_REST_Server::EDITABLE,
						'args'                => $single_endpoint->EDIT_args(),
						'permission_callback' => array( $single_endpoint, 'can_edit' ),
						'callback'            => array( $single_endpoint, 'update' ),
					),
					array(
						'methods'             => WP_REST_Server::DELETABLE,
						'args'                => $single_endpoint->DELETE_args(),
						'permission_callback' => array( $single_endpoint, 'can_delete' ),
						'callback'            => array( $single_endpoint, 'delete' ),
					),
				)
			);
		}

		$documentation_endpoint = tribe( 'tec.rest-v1.endpoints.documentation' );
		$documentation_endpoint->register_documentation_provider( '/tags', $archive_endpoint );
		$documentation_endpoint->register_documentation_provider( '/tags/{id}', $single_endpoint );
	}

	/**
	 * Filters the event category taxonomy registration arguments to make it show in REST API requests.
	 *
	 * @since 4.6
	 *
	 * @param array $taxonomy_args
	 *
	 * @return array
	 */
	public function filter_taxonomy_args( array $taxonomy_args ) {
		$taxonomy_args['show_in_rest'] = true;

		return $taxonomy_args;
	}
}
