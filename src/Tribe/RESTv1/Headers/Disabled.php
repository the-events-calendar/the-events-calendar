<?php


class Tribe__Events__RESTv1__Headers__Disabled implements Tribe__Events__RESTv1__Headers__HeadersInterface {

	/**
	 * Prints TEC REST API related meta on the site.
	 */
	public function add_header() {
		// no-op
	}

	/**
	 * Sends TEC REST API related headers.
	 */
	public function send_header() {
		if ( headers_sent() ) {
			return;
		}

		header( 'X-TEC-API-VERSION: disabled' );
	}
}