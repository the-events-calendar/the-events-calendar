<?php

class Tribe__Events__REST__V1__Endpoints__Swagger_Documentation
	implements Tribe__REST__Endpoints__READ_Endpoint_Interface,
	Tribe__Documentation__Swagger__Provider_Interface,
	Tribe__Documentation__Swagger__Builder_Interface {

	/**
	 * @var string
	 */
	protected $swagger_version = '3.0.0';

	/**
	 * @var string
	 */
	protected $tec_rest_api_version;

	/**
	 * @var Tribe__Documentation__Swagger__Provider_Interface[]
	 */
	protected $documentation_providers = [];

	/**
	 * @var Tribe__Documentation__Swagger__Provider_Interface[]
	 */
	protected $definition_providers = [];

	/**
	 * Tribe__Events__REST__V1__Endpoints__Swagger_Documentation constructor.
	 *
	 * @param string $tec_rest_api_version
	 */
	public function __construct( $tec_rest_api_version ) {
		$this->tec_rest_api_version = $tec_rest_api_version;
	}

	/**
	 * Handles GET requests on the endpoint.
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response An array containing the data on success or a WP_Error instance on failure.
	 */
	public function get( WP_REST_Request $request ) {
		$data = $this->get_documentation();

		return new WP_REST_Response( $data );
	}

	/**
	 * Returns an array in the format used by Swagger 2.0.
	 *
	 * While the structure must conform to that used by v2.0 of Swagger the structure can be that of a full document
	 * or that of a document part.
	 * The intelligence lies in the "gatherer" of information rather than in the single "providers" implementing this
	 * interface.
	 *
	 * @link http://swagger.io/
	 *
	 * @return array An array description of a Swagger supported component.
	 */
	public function get_documentation() {
		$scheme = is_ssl() ? 'https' : 'http';
		$documentation = [
			'openapi'     => $this->swagger_version,
			'info'        => $this->get_api_info(),
			'components'  => [ 'schemas' => $this->get_definitions() ],
			'servers'     => [
				[
					'url' => $scheme . '://' . parse_url( home_url(), PHP_URL_HOST ) . str_replace( home_url(), '', tribe_events_rest_url() ),
				]
			], 
			'paths'       => $this->get_paths()
		];

		/**
		 * Filters the Swagger documentation generated for the TEC REST API.
		 *
		 * @param array                                                     $documentation An associative PHP array in the format supported by Swagger.
		 * @param Tribe__Events__REST__V1__Endpoints__Swagger_Documentation $this          This documentation endpoint instance.
		 *
		 * @link http://swagger.io/
		 */
		$documentation = apply_filters( 'tribe_rest_swagger_documentation', $documentation, $this );

		return $documentation;
	}

	protected function get_api_info() {
		return [
			'version'     => $this->tec_rest_api_version,
			'title'       => __( 'The Events Calendar REST API', 'the-events-calendar' ),
			'description' => __( 'The Events Calendar REST API allows accessing upcoming events information easily and conveniently.', 'the-events-calendar' ),
		];
	}

	protected function get_paths() {
		$paths = [];
		foreach ( $this->documentation_providers as $path => $endpoint ) {
			if ( $endpoint !== $this ) {
				/** @var Tribe__Documentation__Swagger__Provider_Interface $endpoint */
				$documentation = $endpoint->get_documentation();
			} else {
				$documentation = $this->get_own_documentation();
			}
			$paths[ $path ] = $documentation;
		}

		return $paths;
	}

	/**
	 * Registers a documentation provider for a path.
	 *
	 * @param                                            $path
	 * @param Tribe__Documentation__Swagger__Provider_Interface $endpoint
	 */
	public function register_documentation_provider( $path, Tribe__Documentation__Swagger__Provider_Interface $endpoint ) {
		$this->documentation_providers[ $path ] = $endpoint;
	}

	protected function get_own_documentation() {
		return [
			'get' => [
				'responses' => [
					'200' => [
						'description' => __( 'Returns the documentation for The Events Calendar REST API in Swagger consumable format.', 'the-events-calendar' ),
					],
				],
			],
		];
	}

	protected function get_definitions() {
		$definitions = [];
		/** @var Tribe__Documentation__Swagger__Provider_Interface $provider */
		foreach ( $this->definition_providers as $type => $provider ) {
			$definitions[ $type ] = $provider->get_documentation();
		}

		return $definitions;
	}

	/**
	 * @return Tribe__Documentation__Swagger__Provider_Interface[]
	 */
	public function get_registered_documentation_providers() {
		return $this->documentation_providers;
	}

	/**
	 * Registers a documentation provider for a definition.
	 *
	 * @param                                                  string $type
	 * @param Tribe__Documentation__Swagger__Provider_Interface       $provider
	 */
	public function register_definition_provider( $type, Tribe__Documentation__Swagger__Provider_Interface $provider ) {
		$this->definition_providers[ $type ] = $provider;
	}

	/**
	 * @return Tribe__Documentation__Swagger__Provider_Interface[]
	 */
	public function get_registered_definition_providers() {
		return $this->definition_providers;
	}

	/**
	 * Returns the content of the `args` array that should be used to register the endpoint
	 * with the `register_rest_route` function.
	 *
	 * @return array
	 */
	public function READ_args() {
		return [];
	}
}
