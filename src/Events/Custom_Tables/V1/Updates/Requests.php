<?php
/**
 * A Request factory that uses the WP REST Request class as a base to
 * provide information about any HTTP request.
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Updates;
 */

namespace TEC\Events\Custom_Tables\V1\Updates;

use Tribe__Utils__Array as Arr;
use WP_REST_Request;
use WP_REST_Server;
use Tribe__Date_Utils as Dates;
use Tribe__Timezones as Timezones;

/**
 * Class Requests
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Editors\Classic
 */
class Requests {
	/**
	 * A list of the HTTP methods considered to be updating in nature
	 * by either creating, updating or deleting a post.
	 *
	 * @since 6.0.0
	 *
	 * @var array<string>
	 */
	private static $update_http_methods = [ 'POST', 'PUT', 'PATCH', 'DELETE' ];

	/**
	 * Models the current HTTP request using a WP REST Request object.
	 *
	 * @since 6.0.0
	 *
	 * @return WP_REST_Request A reference to an instance of the WP_Rest_Request
	 *                         set up to provide information about the current HTTP request.
	 */
	public function from_http_request() {
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

		$post_id_locations = [ 'ID', 'post_id', 'post_ID', 'id', 'post' ];

		/**
		 * Allows filtering the locations the factory will look up, **in order**, to find the current post ID
		 * value.
		 *
		 * @since 6.0.0
		 *
		 * @param array<string> A list of keys the factory will look up, in the HTTP super-globals, to find
		 *                       the current post ID.
		 */
		$post_id_locations = apply_filters( 'tec_events_custom_tables_v1_request_factory_post_id_keys', $post_id_locations );

		$post_id = Arr::get_first_set( $request->get_params(), $post_id_locations, 0 );

		// If we're loading one of the front-end forms, `post_id` will be empty.
		if ( empty( $post_id ) && tribe_is_frontend() ) {
			global $post;
			$post_id = $post->ID ?? $post_id;
		}

		// For consistency with the REST Request, set up the `id` parameter.
		$request->set_param( 'id', (int) $post_id );

		return $request;
	}

	/**
	 * Identifies a request as being an update one.
	 *
	 * In the context of this class an "update" is either a POST, PUT or PATCH
	 * request for a post, or a GET request to trash or delete a post.
	 *
	 * @since 6.0.0
	 *
	 * @param WP_REST_Request $request A reference to the Request object that
	 *                                 should be inspected.
	 *
	 * @return bool Whether the input Request is an update one or not.
	 */
	public function is_update_request( WP_REST_Request $request ): bool {
		return ! empty( $request->get_param( 'id' ) )
		       && (
			       in_array( $request->get_method(), self::$update_http_methods, true )
			       || in_array( $request->get_param( 'action' ), [ 'trash', 'delete', 'untrash' ], true )
		       );
	}

	/**
	 * Models the current HTTP request using a WP REST Request object and updates the
	 * request parameters related to the definition of an Event start, end and timezone
	 * to match the parameters.
	 *
	 * @since 6.0.0
	 * @param mixed $start The description, timestamp or `DateTime` object representing
	 *                     the Event start date and time.
	 * @param mixed $end   The description, timestamp or `DateTime` object representing
	 *                     the Event end date and time.
	 * @param mixed|null The timezone string, object or `null` to use the default timezone.
	 *
	 * @return WP_REST_Request A reference to an instance of the WP_Rest_Request
	 *                         set up to provide information about the current HTTP request.
	 */
	public function from_http_request_with_dates( $start, $end, $timezone = null ) {
		$timezone = Timezones::build_timezone_object( $timezone );
		$utc      = Timezones::build_timezone_object( 'UTC' );
		$start    = Dates::immutable( $start, $timezone );
		$end      = Dates::immutable( $end, $timezone );

		$request  = $this->from_http_request();

		// Set up params as the Classic Editor request would.
		$request->set_param( 'EventStartDate', $start->format( Dates::DBDATEFORMAT ) );
		$request->set_param( 'EventStartTime', $start->format( Dates::DBTIMEFORMAT ) );
		$request->set_param( 'EventEndDate', $end->format( Dates::DBDATEFORMAT ) );
		$request->set_param( 'EventEndTime', $end->format( Dates::DBTIMEFORMAT ) );
		$request->set_param( 'EventTimezone', $timezone->getName() );

		// Set up params as the Blocks Editor would.
		$request->set_param( '_EventStartDate', $start->format( Dates::DBDATETIMEFORMAT ) );
		$request->set_param( '_EventStartDateUTC', $start->setTimezone( $utc )->format( Dates::DBDATETIMEFORMAT ) );
		$request->set_param( '_EventEndDate', $end->format( Dates::DBDATETIMEFORMAT ) );
		$request->set_param( '_EventEndDateUTC', $end->setTimezone( $utc )->format( Dates::DBDATETIMEFORMAT ) );
		$request->set_param( '_EventTimezone', $timezone->getName() );

		return $request;
	}

	/**
	 * Determines if the current request is a request to delete or trash a post or not.
	 *
	 * @since 6.0.0
	 *
	 * @param WP_REST_Request $request A reference to the Request object to check.
	 *
	 * @return bool Whether the input Request is a request to delete or trash a post or not.
	 */
	public function is_delete_request( WP_REST_Request $request ): bool {
		$method = $request->get_method();

		return ! empty( $request->get_param( 'id' ) )
		       && (
			       (
				       // A Classic Editor format request.
				       in_array( $method, self::$update_http_methods, true )
				       && in_array( $request->get_param( 'action' ), [ 'trash', 'delete' ], true )
			       )
			       ||
			       // A REST API format request.
			       in_array( $method, Arr::list_to_array( WP_REST_Server::DELETABLE ), true )
		       );
	}
}
