<?php


/**
 * Class Tribe__Events__RESTv1__Headers
 *
 * Handles headers and header equivalent to be printed/sent in responses.
 */
interface Tribe__Events__RESTv1__Headers__HeadersInterface {

	/**
	 * Prints TEC REST API related meta on the site.
	 */
	public function add_header();

	/**
	 * Sends TEC REST API related headers.
	 */
	public function send_header();
}