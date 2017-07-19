<?php

class Tribe__Events__REST__V1__Endpoints__Single_Tag
	extends Tribe__Events__REST__V1__Endpoints__Term_Single_Base
	implements Tribe__REST__Endpoints__READ_Endpoint_Interface,
	Tribe__REST__Endpoints__CREATE_Endpoint_Interface,
	Tribe__REST__Endpoints__DELETE_Endpoint_Interface,
	Tribe__REST__Endpoints__UPDATE_Endpoint_Interface,
	Tribe__Documentation__Swagger__Provider_Interface {

	/**
	 * Returns the content of the `args` array that should be used to register the endpoint
	 * with the `register_rest_route` function.
	 *
	 * @since TBD
	 *
	 * @return array
	 */
	public function CREATE_args() {
		return array(
			'name'             => array(
				'required'          => true,
				'validate_callback' => array( $this->validator, 'is_string' ),
				'type'              => 'string',
				'description'       => __( 'The event tag name', 'the-events-calendar' ),
			),
			'description'             => array(
				'required'          => false,
				'validate_callback' => array( $this->validator, 'is_string' ),
				'type'              => 'string',
				'description'       => __( 'The event tag description', 'the-events-calendar' ),
			),
			'slug'             => array(
				'required'          => false,
				'validate_callback' => array( $this->validator, 'is_string' ),
				'type'              => 'string',
				'description'       => __( 'The event tag slug', 'the-events-calendar' ),
			),
		);
	}

	/**
	 * Handles DELETE requests on the endpoint.
	 *
	 * @since TBD
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response An array containing the data of the trashed post on
	 *                                   success or a WP_Error instance on failure.
	 */
	public function delete( WP_REST_Request $request ) {
		// TODO: Implement delete() method.
	}

	/**
	 * Returns the content of the `args` array that should be used to register the endpoint
	 * with the `register_rest_route` function.
	 *
	 * @since TBD
	 *
	 * @return array
	 */
	public function DELETE_args() {
		return array();
	}

	/**
	 * Whether the current user can delete content of this type or not.
	 *
	 * @since TBD
	 *
	 * @return bool Whether the current user can delete or not.
	 */
	public function can_delete() {
		// TODO: Implement can_delete() method.
	}

	/**
	 * Returns an array in the format used by Swagger 2.0.
	 *
	 * While the structure must conform to that used by v2.0 of Swagger the structure can be that of a full document
	 * or that of a document part.
	 * The intelligence lies in the "gatherer" of informations rather than in the single "providers" implementing this
	 * interface.
	 *
	 * @since TBD
	 *
	 * @link  http://swagger.io/
	 *
	 * @return array An array description of a Swagger supported component.
	 */
	public function get_documentation() {
		return array();
	}

	/**
	 * Returns the content of the `args` array that should be used to register the endpoint
	 * with the `register_rest_route` function.
	 *
	 * @since TBD
	 *
	 * @return array
	 */
	public function READ_args() {
		return array(
			'id' => array(
				'in'                => 'path',
				'type'              => 'integer',
				'description'       => __( 'the event tag term ID', 'the-events-calendar' ),
				'required'          => true,
				'validate_callback' => array( $this->validator, 'is_post_tag' ),
			),
		);
	}

	/**
	 * Returns the taxonomy of the terms handled by the endpoint.
	 *
	 * @since TBD
	 *
	 * @return string
	 */
	public function get_taxonomy() {
		return 'post_tag';
	}

	/**
	 * Returns the term namespace used by the endpoint.
	 *
	 * @since TBD
	 *
	 * @return string
	 */
	protected function get_term_namespace() {
		return 'tags';
	}
}