<?php


/**
 * Class Tribe__Events__Aggregator__API__Requests
 *
 * Encapsulates the WordPress HTTP API.
 */
class Tribe__Events__Aggregator__API__Requests {
	/**
	 * Builds and returns the custom headers needed to identify the site in the service.
	 *
	 * @since 4.6.2
	 *
	 * @return array
	 */
	public function get_site_headers() {
		$site      = parse_url( home_url() );
		$x_ea_site = ! empty( $site['path'] ) ? $site['host'] . $site['path'] : $site['host'];

		return [ 'X-EA-Site' => $x_ea_site ];
	}

	/**
	 * Retrieve the raw response from the HTTP request using the GET method.
	 *
	 * @param string $url  Site URL to retrieve.
	 * @param array  $args Optional. Request arguments. Default empty array.
	 *
	 * @return array|WP_Error
	 */
	public function get( $url, $args = [] ) {
		$site_headers = $this->get_site_headers();

		if ( empty( $args['headers'] ) ) {
			$args['headers'] = $site_headers;
		} else {
			$args['headers'] = array_merge( $args['headers'], $site_headers );
		}

		$response = wp_remote_get( $url, $args );

		return $response;
	}

	/**
	 * Retrieve the raw response from the HTTP request using the POST method.
	 *
	 * @since 4.6.2
	 *
	 * @param string $url  Site URL to retrieve.
	 * @param array  $args Optional. Request arguments. Default empty array.
	 *
	 * @return array|WP_Error
	 */
	public function post( $url, $args = [] ) {
		$site_headers = $this->get_site_headers();

		if ( empty( $args['headers'] ) ) {
			$args['headers'] = $site_headers;
		} else {
			$args['headers'] = array_merge( $args['headers'], $site_headers );
		}

		$response = wp_remote_post( $url, $args );

		return $response;
	}
}
