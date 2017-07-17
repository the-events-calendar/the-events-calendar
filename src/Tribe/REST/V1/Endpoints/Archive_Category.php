<?php

class Tribe__Events__REST__V1__Endpoints__Archive_Category
	extends Tribe__Events__REST__V1__Endpoints__Archive_Base
	implements Tribe__REST__Endpoints__READ_Endpoint_Interface,
	Tribe__Documentation__Swagger__Provider_Interface {

	/**
	 * @var \WP_REST_Terms_Controller
	 */
	protected $terms_controller;

	/**
	 * @var array An array mapping the REST request supported query vars to the args used in a WP_REST_Terms_Controller query.
	 */
	protected $supported_query_vars = array(
		'page'       => 'page',
		'per_page'   => 'per_page',
		'search'     => 'search',
		'exclude'    => 'exclude',
		'include'    => 'include',
		'order'      => 'order',
		'orderby'    => 'orderby',
		'hide_empty' => 'hide_empty',
		'parent'     => 'parent',
		'post'       => 'post',
		'slug'       => 'slug',
	);

	public function __construct(
		\Tribe__REST__Messages_Interface $messages,
		\Tribe__Events__REST__Interfaces__Post_Repository $repository,
		\Tribe__Events__Validator__Interface $validator,
		WP_REST_Terms_Controller $terms_controller
	) {
		parent::__construct( $messages, $repository, $validator );
		$this->terms_controller = $terms_controller;
	}

	/**
	 * Returns an array in the format used by Swagger 2.0.
	 *
	 * While the structure must conform to that used by v2.0 of Swagger the structure can be that of a full document
	 * or that of a document part.
	 * The intelligence lies in the "gatherer" of informations rather than in the single "providers" implementing this
	 * interface.
	 *
	 * @link http://swagger.io/
	 *
	 * @return array An array description of a Swagger supported component.
	 */
	public function get_documentation() {
		return array();
	}

	/**
	 * Handles GET requests on the endpoint.
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response An array containing the data on success or a WP_Error instance on failure.
	 */
	public function get( WP_REST_Request $request ) {
		$request_params = array();

		foreach ( $this->supported_query_vars as $key ) {
			$request_params[ $key ] = $request[ $key ];
		}

		$params = $this->parse_args( $request_params, $request->get_default_params() );

		foreach ( $params as $key => $value ) {
			$request->set_param( $key, $value );
		}

		$terms_response = $this->terms_controller->get_items( $request );

		if ( $terms_response instanceof WP_Error ) {
			return $terms_response;
		}

		$terms_data = $terms_response->get_data();

		if ( empty( $terms_data ) ) {
			$message = $this->messages->get_message( 'category-archive-page-not-found' );

			return new WP_Error( 'category-archive-page-not-found', $message, array( 'status' => 404 ) );
		}

		$prepared = $this->repository->prepare_terms_data( $terms_data, $this->get_taxonomy() );

		$response = new WP_REST_Response();
		$response->set_data( array( 'categories' => $prepared ) );

		return $response;
	}

	/**
	 * Returns the content of the `args` array that should be used to register the endpoint
	 * with the `register_rest_route` function.
	 *
	 * @return array
	 */
	public function READ_args() {
		return array(
			'page'       => array(
				'required'          => false,
				'validate_callback' => array( $this->validator, 'is_positive_int' ),
				'default'           => 1,
				'description'       => __( 'The archive page to return', 'the-events-calendar' ),
				'type'              => 'integer',
			),
			'per_page'   => array(
				'required'          => false,
				'validate_callback' => array( $this->validator, 'is_positive_int' ),
				'sanitize_callback' => array( $this, 'sanitize_per_page' ),
				'default'           => $this->get_default_posts_per_page(),
				'description'       => __( 'The number of event categories to return on each page', 'the-events-calendar' ),
				'type'              => 'integer',
			),
			'search'     => array(
				'required'          => false,
				'validate_callback' => array( $this->validator, 'is_string' ),
				'description'       => __( 'Limit results to those matching a string', 'the-events-calendar' ),
				'type'              => 'string',
			),
			'exclude'    => array(
				'required'          => false,
				'validate_callback' => array( $this->validator, 'is_event_category' ),
				'description'       => __( 'Ensure result set exclude specific IDs', 'the-events-calendar' ),
				'swagger_type'      => 'array',
			),
			'include'    => array(
				'required'          => false,
				'validate_callback' => array( $this->validator, 'is_event_category' ),
				'description'       => __( 'Limit result set to specific IDs', 'the-events-calendar' ),
				'swagger_type'      => 'array',
			),
			'order'      => array(
				'required'          => false,
				'validate_callback' => array( $this->validator, 'is_string' ),
				'description'       => __( 'Order sort attribute ascending or descending', 'the-events-calendar' ),
				'type'              => 'string',
			),
			'orderby'    => array(
				'required'          => false,
				'validate_callback' => array( $this->validator, 'is_string' ),
				'description'       => __( 'Sort collection by term attribute', 'the-events-calendar' ),
				'type'              => 'string',
			),
			'hide_empty' => array(
				'required'    => false,
				'description' => __( 'Whether to hide terms not assigned to any posts', 'the-events-calendar' ),
				'type'        => 'boolean',
				'default'     => '1',
			),
			'parent'     => array(
				'required'          => false,
				'validate_callback' => array( $this->validator, 'is_event_category' ),
				'description'       => __( 'Limit result set to terms assigned to a specific parent', 'the-events-calendar' ),
				'type'              => 'integer',
			),
			'post'       => array(
				'required'          => false,
				'validate_callback' => array( $this->validator, 'is_event_id' ),
				'description'       => __( 'Limit result set to terms assigned to a specific post', 'the-events-calendar' ),
				'type'              => 'integer',
			),
			'slug'       => array(
				'required'          => false,
				'validate_callback' => array( $this->validator, 'is_string' ),
				'description'       => __( 'Limit result set to terms with a specific slug', 'the-events-calendar' ),
				'type'              => 'string',
			),
		);
	}

	/**
	 * Returns the maximum number of posts per page fetched via the REST API.
	 *
	 * @return int
	 */
	public function get_max_posts_per_page() {
		/**
		 * Filters the maximum number of event categories per page that should be returned.
		 *
		 * @param int $per_page Default to 50.
		 */
		return apply_filters( 'tribe_rest_event_category_max_per_page', 50 );
	}

	/**
	 * @return string
	 */
	public function get_taxonomy() {
		return Tribe__Events__Main::TAXONOMY;
	}

	/**
	 * Returns the archive base REST URL
	 *
	 * @return string
	 */
	protected function get_base_rest_url() {
		$url = tribe_events_rest_url( 'categories/' );

		return $url;
	}
}