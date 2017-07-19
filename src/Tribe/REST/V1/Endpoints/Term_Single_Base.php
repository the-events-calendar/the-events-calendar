<?php
abstract class Tribe__Events__REST__V1__Endpoints__Term_Single_Base
	extends Tribe__Events__REST__V1__Endpoints__Base {

	/**
	 * @var \Tribe__Events__REST__Interfaces__Post_Repository
	 */
	protected $repository;

	/**
	 * @var \Tribe__Events__Validator__Interface
	 */
	protected $validator;

	/**
	 * @var \WP_REST_Terms_Controller
	 */
	protected $terms_controller;

	/**
	 * Tribe__Events__REST__V1__Endpoints__Term_Single_Base constructor.
	 *
	 * @since TBD
	 *
	 * @param \Tribe__REST__Messages_Interface                  $messages
	 * @param \Tribe__Events__REST__Interfaces__Post_Repository $repository
	 * @param \Tribe__Events__Validator__Interface              $validator
	 * @param \WP_REST_Terms_Controller                         $terms_controller
	 */
	public function __construct(
		Tribe__REST__Messages_Interface $messages,
		Tribe__Events__REST__Interfaces__Post_Repository $repository,
		Tribe__Events__Validator__Interface $validator,
		WP_REST_Terms_Controller $terms_controller
	) {
		parent::__construct( $messages );
		$this->repository = $repository;
		$this->validator = $validator;
		$this->terms_controller = $terms_controller;
	}

	/**
	 * Returns the taxonomy of the terms handled by the endpoint.
	 *
	 * @since TBD
	 *
	 * @return string
	 */
	abstract public function get_taxonomy();

	/**
	 * Handles GET requests on the endpoint.
	 *
	 * @since TBD
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response An array containing the data on success or a WP_Error instance on failure.
	 */
	public function get( WP_REST_Request $request ) {
		$term_response = $this->terms_controller->get_item( $request );

		$data = $this->repository->prepare_term_data( $term_response->get_data(), $this->get_taxonomy(), $this->get_term_namespace() );

		$term_response->set_data( $data );

		return $term_response;
	}

	/**
	 * Returns the term namespace used by the endpoint.
	 *
	 * @since TBD
	 *
	 * @return string
	 */
	abstract protected function get_term_namespace();
}