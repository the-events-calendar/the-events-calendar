<?php
/**
 *
 *
 * @package Tribe\Events\Views\V2
 * @since 4.9.2
 */
namespace Tribe\Events\Views\V2;

class Rest_Endpoint {
	/**
	 * Returns the final REST URL for the HTML
	 *
	 * @since   4.9.2
	 *
	 * @return  string
	 */
	public function get_url() {
		return get_rest_url( null, Service_Provider::NAME_SPACE . '/html' );
	}
}