<?php

class Tribe__Events__REST__V1__Endpoints__Archive_Category
	extends Tribe__Events__REST__V1__Endpoints__Archive_Base
	implements Tribe__REST__Endpoints__READ_Endpoint_Interface,
	Tribe__Documentation__Swagger__Provider_Interface {

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
		$taxonomy = $this->get_taxonomy();

		$query = new WP_Term_Query( array( 'taxonomy' => $taxonomy ) );
		$terms = $query->get_terms();

		if ( empty( $terms ) ) {
			$message = $this->messages->get_message( 'event-archive-page-not-found' );

			return new WP_Error( 'event-archive-page-not-found', $message, array( 'status' => 404 ) );
		}

		$data['categories'] = $this->repository->get_terms_data( $terms, $taxonomy );

		return $data;
	}

	/**
	 * Returns the content of the `args` array that should be used to register the endpoint
	 * with the `register_rest_route` function.
	 *
	 * @return array
	 */
	public function READ_args() {
		return array();
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