<?php

class Tribe__Events__Aggregator__Record__Meetup extends Tribe__Events__Aggregator__Record__Abstract {
	public static $origin = 'meetup';

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
	public function queue_import( $args = array() ) {
		$meetup_api_key    = tribe_get_option( 'meetup_api_key' );

		$defaults = array(
			'meetup_api_key' => $meetup_api_key,
		);

		$args = wp_parse_args( $args, $defaults );

		return parent::queue_import( $args );
	}
}
