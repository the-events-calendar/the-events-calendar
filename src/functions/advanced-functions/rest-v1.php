<?php
if ( ! function_exists( 'tribe_events_rest_url_prefix' ) ) {
	/**
	 * Returns TEC REST API URL prefix.
	 *
	 * @return string TEC REST API URL prefix; default `wp-json/tec/v1`.
	 */
	function tribe_events_rest_url_prefix() {
		return tribe( 'tec.rest-v1.main' )->get_url_prefix();
	}
}

if ( ! function_exists( 'tribe_events_rest_url' ) ) {
	/**
	 * Retrieves the URL to a TEC REST endpoint on a site.
	 *
	 * Note: The returned URL is NOT escaped.
	 *
	 * @global WP_Rewrite $wp_rewrite
	 *
	 * @param string      $path    Optional. TEC REST route. Default '/'.
	 * @param string      $scheme  Optional. Sanitization scheme. Default 'rest'.
	 * @param int         $blog_id Optional. Blog ID. Default of null returns URL for current blog.
	 *
	 * @return string Full URL to the endpoint.
	 */
	function tribe_events_rest_url( $path = '/', $scheme = 'rest', $blog_id = null ) {
		return tribe( 'tec.rest-v1.main' )->get_url( $path, $scheme, $blog_id );
	}
}
