<?php

class Tribe__Events__Aggregator__Record__ICS extends Tribe__Events__Aggregator__Record__Abstract {
	public static $origin = 'ics';

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
			'file'   => empty( $this->meta['file'] ) ? null : $this->meta['file'],
		);

		$args = wp_parse_args( $args, $defaults );

		return parent::create( $origin, $type, $args );
	}
}
