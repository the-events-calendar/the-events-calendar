<?php

class Tribe__Events__Aggregator__Record__Manual_Test extends Tribe__Events__Aggregator__Record__Abstract {
	public $origin = 'manual-test';

	/**
	 * Public facing Label for this Origin
	 *
	 * @return string
	 */
	public function get_label() {
		return __( 'Manual Test', 'the-events-calendar' );
	}

	public function get_existing_ids_from_import_data( $import_data ) {
		return parent::get_existing_ids_from_import_data( $import_data );
	}
}