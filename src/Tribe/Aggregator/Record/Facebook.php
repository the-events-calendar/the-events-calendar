<?php
// Don't load directly
defined( 'WPINC' ) or die;

class Tribe__Events__Aggregator__Record__Facebook extends Tribe__Events__Aggregator__Record__Abstract {
	public $origin = 'facebook';

	/**
	 * Queues the import on the Aggregator service
	 */
	public function queue_import( $args = array() ) {
		$fb_api_key    = tribe_get_option( 'fb_api_key' );
		$fb_api_secret = tribe_get_option( 'fb_api_secret' );

		$defaults = array(
			'facebook_app_id' => $fb_api_key,
			'facebook_secret' => $fb_api_secret,
		);

		$args = wp_parse_args( $args, $defaults );

		return parent::queue_import( $args );
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
