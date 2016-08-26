<?php
// Don't load directly
defined( 'WPINC' ) or die;

class Tribe__Events__Aggregator__Record__Facebook extends Tribe__Events__Aggregator__Record__Abstract {
	public $origin = 'facebook';

	/**
	 * Queues the import on the Aggregator service
	 */
	public function queue_import( $args = array() ) {
		$fb_token = tribe_get_option( 'fb_token' );

		$defaults = array(
			'facebook_token' => $fb_token,
		);

		$args = wp_parse_args( $args, $defaults );

		return parent::queue_import( $args );
	}

	public static function get_iframe_url( $args = array() ) {
		$service = Tribe__Events__Aggregator__Service::instance();
		$url = $service->api()->domain . 'facebook/' . $service->api()->key;
		$site = (object) parse_url( home_url() );

		$defaults = array(
			'label' => __( 'Log In', 'the-events-calendar' ),
			'domain' => $site->host,
		);

		$args = wp_parse_args( $args, $defaults );

		$url = add_query_arg( $args, $url );

		return $url;
	}

	/**
	 * Public facing Label for this Origin
	 *
	 * @return string
	 */
	public function get_label() {
		return __( 'Facebook', 'the-events-calendar' );
	}
}
