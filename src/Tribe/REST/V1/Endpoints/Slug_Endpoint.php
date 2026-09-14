<?php
/**
 * Shared behavior for the REST API v1 by-slug endpoints.
 *
 * @since TBD
 */

/**
 * Resolves the route slug to a post ID before every read, write and permission check.
 *
 * Meant for the `*_Slug` endpoints, which extend the matching by-ID endpoint: the slug is
 * resolved and set as `id` on the request, then the parent implementation runs as usual.
 * Resolving in the permission callbacks too means authorization is checked against the object
 * the request will actually write, and a body or query `id` can never win over the route slug.
 *
 * @since TBD
 */
trait Tribe__Events__REST__V1__Endpoints__Slug_Endpoint {

	/**
	 * Returns the post type handled by the endpoint.
	 *
	 * @return string
	 */
	abstract protected function get_post_type();

	/**
	 * Resolves the slug in the request to the post ID and sets it on the request.
	 *
	 * @since TBD
	 *
	 * @param WP_REST_Request $request The request object.
	 */
	protected function set_id_from_slug( WP_REST_Request $request ): void {
		$post = get_page_by_path( $request['slug'], OBJECT, $this->get_post_type() );

		$request->set_param( 'id', $post ? $post->ID : 0 );
	}

	/**
	 * Handles GET requests on the endpoint.
	 *
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return WP_REST_Response|WP_Error An array containing the data on success or a WP_Error instance on failure.
	 */
	public function get( WP_REST_Request $request ) {
		$this->set_id_from_slug( $request );

		return parent::get( $request );
	}

	/**
	 * Handles DELETE requests on the endpoint.
	 *
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return WP_Error|WP_REST_Response An array containing the data of the trashed post on
	 *                                   success or a WP_Error instance on failure.
	 */
	public function delete( WP_REST_Request $request ) {
		$this->set_id_from_slug( $request );

		return parent::delete( $request );
	}

	/**
	 * Handles UPDATE requests on the endpoint.
	 *
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return WP_Error|WP_REST_Response An array containing the data of the updated post on
	 *                                   success or a WP_Error instance on failure.
	 */
	public function update( WP_REST_Request $request ) {
		$this->set_id_from_slug( $request );

		return parent::update( $request );
	}

	/**
	 * Whether the current user can delete the post identified by the request slug.
	 *
	 * @since TBD
	 *
	 * @param WP_REST_Request|null $request The request object.
	 *
	 * @return bool
	 */
	public function can_delete( ?WP_REST_Request $request = null ) {
		if ( $request ) {
			$this->set_id_from_slug( $request );
		}

		return parent::can_delete( $request );
	}

	/**
	 * Whether the current user can edit the post identified by the request slug.
	 *
	 * @since TBD
	 *
	 * @param WP_REST_Request|null $request The request object.
	 *
	 * @return bool
	 */
	public function can_edit( ?WP_REST_Request $request = null ) {
		if ( $request ) {
			$this->set_id_from_slug( $request );
		}

		return parent::can_edit( $request );
	}
}
