<?php


/**
 * Class Tribe__Events__RESTv1__Headers
 *
 * Handles headers and header equivalent to be printed/sent in responses.
 */
class Tribe__Events__RESTv1__Headers {

	public function add_header() {
		$api_root = tribe_rest_url();

		if ( empty( $api_root ) ) {
			return;
		}

		echo  '<meta name="tec-api-version" content="v1">',
			'<link rel="https://theeventscalendar.com/" href="' . esc_url( $api_root ) . '" />\n';
	}

	public function send_header() {
		if ( headers_sent() ) {
			return;
		}

		$api_root = tribe_rest_url();

		if ( empty( $api_root ) ) {
			return;
		}

		header( 'X-TEC-API-VERSION: v1' );
		header( 'X-TEC-API-ROOT: ' . esc_url_raw( $api_root ) );
	}
}