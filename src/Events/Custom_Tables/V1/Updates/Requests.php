<?php
/**
 * A Request builder that uses the WP REST Request class as a base to
 * provide information about any HTTP request.
 *
 * @since   TBD
 *
 * @package TEC\Events\Custom_Tables\V1\Updates;
 */

namespace TEC\Events\Custom_Tables\V1\Updates;

use Tribe__Utils__Array as Arr;
use WP_REST_Request;
use WP_REST_Server;

/**
 * Class Request
 *
 * @since   TBD
 *
 * @package TEC\Events\Custom_Tables\V1\Editors\Classic
 */
class Requests {
	/**
	 * Models the current HTTP request using a WP REST Request object.
	 *
	 * @since TBD
	 *
	 * @return WP_REST_Request A reference to an instance of the WP_Rest_Request
	 *                         set up to provide information about the current HTTP request.
	 */
	public static function from_http_request() {
		$method  = isset( $_SERVER['REQUEST_METHOD'] ) ? $_SERVER['REQUEST_METHOD'] : '';
		$route   = isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '';
		$request = new WP_REST_Request( $method, $route );
		$request->set_query_params( wp_unslash( $_GET ) );
		$request->set_body_params( wp_unslash( $_POST ) );
		$request->set_file_params( $_FILES );
		$server = new WP_REST_Server();
		$request->set_headers( $server->get_headers( wp_unslash( $_SERVER ) ) );
		$request->set_body( WP_REST_Server::get_raw_data() );

		/*
		 * HTTP method override for clients that can't use PUT/PATCH/DELETE. First, we check
		 * $_GET['_method']. If that is not set, we check for the HTTP_X_HTTP_METHOD_OVERRIDE
		 * header.
		 */
		if ( isset( $_GET['_method'] ) ) {
			$request->set_method( $_GET['_method'] );
		} elseif ( isset( $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] ) ) {
			$request->set_method( $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] );
		}

		$post_id = Arr::get_first_set( $request->get_params(), [ 'ID', 'post_id', 'post_ID', 'id', 'post' ], 0 );

		// For consistency with the REST Request, set up the `id` parameter.
		$request->set_param( 'id', (int) $post_id );

		return $request;
	}
}
