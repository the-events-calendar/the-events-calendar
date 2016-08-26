<?php
// Don't load directly
defined( 'WPINC' ) or die;

class Tribe__Events__Aggregator__Record__Eventbrite extends Tribe__Events__Aggregator__Record__Abstract {
	public $origin = 'eventbrite';

	/**
	 * Public facing Label for this Origin
	 *
	 * @return string
	 */
	public function get_label() {
		return __( 'Eventbrite', 'the-events-calendar' );
	}
}
