<?php

class Tribe__Events__Aggregator__Record__Meetup extends Tribe__Events__Aggregator__Record__Abstract {
	public $origin = 'meetup';

	/**
	 * Queues the import on the Aggregator service
	 */
	public function queue_import( $args = array() ) {
		$meetup_api_key    = tribe_get_option( 'meetup_api_key' );

		$defaults = array(
			'meetup_api_key' => $meetup_api_key,
		);

		$args = wp_parse_args( $args, $defaults );

		return parent::queue_import( $args );
	}
}
