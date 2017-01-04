<?php


class Tribe__Events__REST__V1__Headers__Base implements Tribe__REST__Headers__Base_Interface {

	/**
	 * @var string
	 */
	protected $api_version_header = 'X-TEC-API-VERSION';
	/**
	 * @var string
	 */
	protected $api_root_header = 'X-TEC-API-ROOT';
	/**
	 * @var string
	 */
	protected $api_version_meta_name = 'tec-api-version';

	/**
	 * Returns the header that the REST API will print on the page head to report
	 * its version.
	 *
	 * @return string
	 */
	public function get_api_version_header() {
		return $this->api_version_header;
	}

	/**
	 * Returns the header the REST API will print on the page head to report its root
	 * url.
	 *
	 * @return string
	 */
	public function get_api_root_header() {
		return $this->api_root_header;
	}

	/**
	 * Returns the `name` of the meta tag that will be printed on the page to indicate
	 * the REST API version.
	 *
	 * @return string
	 */
	public function get_api_version_meta_name() {
		return $this->api_version_meta_name;
	}

	/**
	 * Returns the REST API URL.
	 *
	 * @return string
	 */
	public function get_rest_url() {
		if ( is_single() && tribe_is_event() ) {
			return tribe_events_rest_url( 'events/' . Tribe__Main::post_id_helper() );
		}

		return tribe_events_rest_url();
	}
}