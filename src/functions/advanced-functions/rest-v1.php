<?php
if ( ! function_exists( 'tribe_rest_url_prefix' ) ) {
	/**
	 * Returns TEC REST API URL prefix.
	 *
	 * @return string TEC REST API URL prefix; default `wp-json/tec/v1`.
	 */
	function tribe_rest_url_prefix() {
		return tribe( 'tec.rest-v1.main' )->get_url_prefix();
	}
}

if ( ! function_exists( 'tribe_rest_url' ) ) {
	/**
	 * Retrieves the URL to a TEC REST endpoint on a site.
	 *
	 * Note: The returned URL is NOT escaped.
	 *
	 * @global WP_Rewrite $wp_rewrite
	 *
	 * @param int         $blog_id Optional. Blog ID. Default of null returns URL for current blog.
	 * @param string      $path    Optional. TEC REST route. Default '/'.
	 * @param string      $scheme  Optional. Sanitization scheme. Default 'rest'.
	 *
	 * @return string Full URL to the endpoint.
	 */
	function tribe_rest_url( $blog_id = null, $path = '/', $scheme = 'rest' ) {
		return tribe( 'tec.rest-v1.main' )->get_url( $blog_id, $path, $scheme );
	}
}
