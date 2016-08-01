<?php

class Tribe__Events__Aggregator__Record__CSV extends Tribe__Events__Aggregator__Record__Abstract {
	public $origin = 'csv';

	/**
	 * Creates an import record
	 *
	 * @param string $origin EA origin
	 * @param string $type Type of record to create - import or schedule
	 * @param array $args Post type args
	 *
	 * @return WP_Post|WP_Error
	 */
	public function create( $type = 'manual', $args = array(), $meta = array() ) {
		$defaults = array(
			'file'   => empty( $this->meta['file'] ) ? null : $this->meta['file'],
		);

		$meta = wp_parse_args( $meta, $defaults );

		return parent::create( $type, $args, $meta );
	}
}
