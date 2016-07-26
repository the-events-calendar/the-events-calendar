<?php

class Tribe__Events__Aggregator__Record__iCal extends Tribe__Events__Aggregator__Record__Abstract {
	public static $origin = 'ical';

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
}
