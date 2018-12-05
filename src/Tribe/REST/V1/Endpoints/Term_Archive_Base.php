<?php

abstract class Tribe__Events__REST__V1__Endpoints__Term_Archive_Base
	extends Tribe__Events__REST__V1__Endpoints__Archive_Base {

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
		'event'      => 'post',
		'slug'       => 'slug',
	);

	public function __construct(
		Tribe__REST__Messages_Interface $messages,
		Tribe__Events__REST__Interfaces__Post_Repository $repository,
		Tribe__Events__Validator__Interface $validator,
		WP_REST_Terms_Controller $terms_controller
	) {
		parent::__construct( $messages, $repository, $validator );
		$this->terms_controller = $terms_controller;
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

		foreach ( $this->supported_query_vars as $origin => $destination ) {
			$request_params[ $destination ] = $request[ $origin ];
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

		$prepared = $this->repository->prepare_terms_data( $terms_data, $this->get_taxonomy() );

		$response = new WP_REST_Response();

		$terms_response_headers = $terms_response->get_headers();

		$data = array(
			'rest_url'            => $this->get_base_rest_url(),
			'total'               => $terms_response_headers['X-WP-Total'],
			'total_pages'         => $terms_response_headers['X-WP-TotalPages'],
			$this->get_data_key() => $prepared,
		);

		$current_page = isset( $params['page'] ) ? $params['page'] : 1;
		$has_next     = $current_page < $data['total_pages'];
		$has_previous = $current_page > 1;

		if ( $has_next ) {
			$data['next_rest_url'] = $this->get_next_rest_url( $data['rest_url'], $current_page );
		}

		if ( $has_previous ) {
			$data['previous_rest_url'] = $this->get_previous_rest_url( $data['rest_url'], $current_page );
		}

		$response->header( 'X-TEC-Total', $data['total'], true );
		$response->header( 'X-TEC-TotalPages', $data['total_pages'], true );

		$response->set_data( $data );

		return $response;
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
	 * Returns the taxonomy of the terms handled by the endpoint.
	 *
	 * @return string
	 *
	 * @since 4.6
	 */
	abstract protected function get_taxonomy();

	/**
	 * Returns the data key that will be used to store terms data in the response.
	 *
	 * @return string
	 *
	 * @since 4.6
	 */
	abstract protected function get_data_key();
}
