<?php


/**
 * Class Tribe__Events__Aggregator__API__Requests
 *
 * Encapsulates the WordPress HTTP API.
 */
class Tribe__Events__Aggregator__API__Requests {

	/**
	 * Retrieve the raw response from the HTTP request using the GET method.
	 *
	 * @param string $url  Site URL to retrieve.
	 * @param array  $args Optional. Request arguments. Default empty array.
	 *
	 * @return array|WP_Error
	 */
	public function get( $url, $args = array() ) {
		return wp_remote_get( $url, $args );
	}
}