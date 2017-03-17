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
	protected $api_origin_header = 'X-TEC-API-ORIGIN';
	/**
	 * @var string
	 */
	protected $api_version_meta_name = 'tec-api-version';
	/**
	 * @var string
	 */
	protected $api_origin_meta_name = 'tec-api-origin';

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
	 * Returns the header the REST API will print on the page head to report its origin
	 * url. Normaly the home_url()
	 *
	 * @return string
	 */
	public function get_api_origin_header() {
		return $this->api_origin_header;
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
	 * Returns the `name` of the meta tag that will be printed on the page to indicate
	 * the REST API Origin URL.
	 *
	 * @return string
	 */
	public function get_api_origin_meta_name() {
		return $this->api_origin_meta_name;
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

		/** @var WP_Query $wp_query */
		global $wp_query;

		$is_featured = (bool) $wp_query->get( 'featured', false );
		if ( $is_featured ) {
			return add_query_arg( array( 'featured' => true ), tribe_events_rest_url( 'events/' ) );
		}

		if ( ! empty( $wp_query->tribe_is_event_category ) && $wp_query->tribe_is_event_category ) {
			$category = $wp_query->get( Tribe__Events__Main::TAXONOMY );

			return add_query_arg( array( 'categories' => $category ), tribe_events_rest_url( 'events/' ) );
		}

		if ( $wp_query->is_tag ) {
			$tag = $wp_query->get( 'tag' );

			return add_query_arg( array( 'tags' => $tag ), tribe_events_rest_url( 'events/' ) );
		}

		if ( $wp_query->get( 'post_type' ) === Tribe__Events__Venue::POSTTYPE ) {
			$venue = $wp_query->get( 'name' );
			if ( ! empty( $wp_query->queried_object_id ) && is_numeric( $wp_query->queried_object_id ) ) {
				$venue = $wp_query->queried_object_id;
			}
			return add_query_arg( array( 'venue' => $venue ), tribe_events_rest_url( 'events/' ) );
		}

		return tribe_events_rest_url();
	}

	/**
	 * Returns the REST API Origin Site.
	 *
	 * @return string
	 */
	public function get_rest_origin_url() {
		return home_url();
	}
}