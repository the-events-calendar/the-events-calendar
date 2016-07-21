<?php

class Tribe__Events__Aggregator__Record__Facebook extends Tribe__Events__Aggregator__Record__Abstract {
	public static $origin = 'facebook';

	/**
	 * Creates an import record
	 *
	 * @param string $origin EA origin
	 * @param string $type Type of record to create - import or schedule
	 * @param array $args Post type args
	 *
	 * @return WP_Post|WP_Error
	 */
	public function create( $origin = false, $type = 'import', $args = array() ) {
		$defaults = array(
			'origin' => self::$origin,
		);

		$args = wp_parse_args( $args, $defaults );

		return parent::create( $origin, $type, $args );
	}

	/**
	 * Queues the import on the Aggregator service
	 */
	public function queue_import() {
		$fb_api_key    = Tribe__Settings_Manager::get_option( 'fb_api_key' );
		$fb_api_secret = Tribe__Settings_Manager::get_option( 'fb_api_secret' );

		$args = array(
			'type'            => $this->meta['type'],
			'origin'          => $this->meta['origin'],
			'source'          => $this->meta['source'],
			'facebook_app_id' => $fb_api_key,
			'facebook_secret' => $fb_api_secret,
		);

		return parent::queue_import( $args );
	}
}
