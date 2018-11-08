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
	 * @since 4.6
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
		$this->repository       = $repository;
		$this->validator        = $validator;
		$this->terms_controller = $terms_controller;
	}

	/**
	 * Returns the taxonomy of the terms handled by the endpoint.
	 *
	 * @since 4.6
	 *
	 * @return string
	 */
	abstract public function get_taxonomy();

	/**
	 * Handles GET requests on the endpoint.
	 *
	 * @since 4.6
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response An array containing the data on success or a WP_Error instance on failure.
	 */
	public function get( WP_REST_Request $request ) {
		$term_response = $this->terms_controller->get_item( $request );

		if ( is_wp_error( $term_response ) ) {
			return $term_response;
		}

		$data = $this->repository->prepare_term_data( $term_response->get_data(), $this->get_taxonomy(), $this->get_term_namespace() );

		$term_response->set_data( $data );

		return $term_response;
	}

	/**
	 * Returns the term namespace used by the endpoint.
	 *
	 * @since 4.6
	 *
	 * @return string
	 */
	abstract protected function get_term_namespace();

	/**
	 * Handles POST requests on the endpoint.
	 *
	 * @since 4.6
	 *
	 * @param WP_REST_Request $request
	 * @param bool            $return_id Whether the created post ID should be returned or the full response object.
	 *
	 * @return WP_Error|WP_REST_Response|int An array containing the data on success or a WP_Error instance on failure.
	 */
	public function create( WP_REST_Request $request, $return_id = false ) {
		$term_response = $this->terms_controller->create_item( $request );

		if ( is_wp_error( $term_response ) ) {
			return $term_response;
		}
		$term_data = $term_response->get_data();

		if ( $return_id ) {
			return $term_data['id'];
		}

		$data = $this->repository->prepare_term_data( $term_data, $this->get_taxonomy(), $this->get_term_namespace() );

		$term_response->set_data( $data );

		return $term_response;
	}

	/**
	 * Whether the current user can create content of the specified type or not.
	 *
	 * @since 4.6
	 *
	 * @return bool Whether the current user can post or not.
	 */
	public function can_create() {
		$cap = get_post_type_object( Tribe__Events__Main::POSTTYPE )->cap->edit_posts;

		return current_user_can( $cap );
	}

	/**
	 * Whether the current user can update content of this type or not.
	 *
	 * @since 4.6
	 *
	 * @return bool Whether the current user can update or not.
	 */
	public function can_edit() {
		return $this->can_create();
	}

	/**
	 * Handles UPDATE requests on the endpoint.
	 *
	 * @since 4.6
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response An array containing the data of the updated post on
	 *                                   success or a WP_Error instance on failure.
	 */
	public function update( WP_REST_Request $request ) {
		$term_response = $this->terms_controller->update_item( $request );

		if ( is_wp_error( $term_response ) ) {
			return $term_response;
		}

		$data = $this->repository->prepare_term_data( $term_response->get_data(), $this->get_taxonomy(), $this->get_term_namespace() );

		$term_response->set_data( $data );

		return $term_response;
	}

	/**
	 * Returns the content of the `args` array that should be used to register the endpoint
	 * with the `register_rest_route` function.
	 *
	 * @since 4.6
	 *
	 * @return array
	 */
	public function EDIT_args() {
		// when editing the only required argument is the ID ('id')
		$create_args = $this->CREATE_args();
		array_walk( $create_args, array( $this, 'unrequire_arg' ) );

		return array_merge( $this->READ_args(), $create_args );
	}

	/**
	 * Returns the content of the `args` array that should be used to register the endpoint
	 * with the `register_rest_route` function.
	 *
	 * @since 4.6
	 *
	 * @return array
	 */
	public function DELETE_args() {
		return $this->READ_args();
	}

	/**
	 * Whether the current user can delete content of this type or not.
	 *
	 * @since 4.6
	 *
	 * @return bool Whether the current user can delete or not.
	 */
	public function can_delete() {
		return $this->can_create();
	}

	/**
	 * Handles DELETE requests on the endpoint.
	 *
	 * @since 4.6
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response An array containing the data of the trashed post on
	 *                                   success or a WP_Error instance on failure.
	 */
	public function delete( WP_REST_Request $request ) {
		/**
		 * Filters whether term deletion is supported in TEC REST API or not.
		 *
		 * @since 4.6
		 *
		 * @param bool $support_deletion
		 */
		$support_deletion = apply_filters( 'tribe_events_rest_term_allow_delete', true );

		if ( $support_deletion ) {
			$request['force'] = true;
		}

		$term_response = $this->terms_controller->delete_item( $request );

		if ( is_wp_error( $term_response ) ) {
			return $term_response;
		}

		$term_data = $term_response->get_data();
		$data      = $this->repository->prepare_term_data( $term_data['previous'], $this->get_taxonomy(), $this->get_term_namespace() );

		$term_response->set_data( $data );

		return $term_response;
	}
}
