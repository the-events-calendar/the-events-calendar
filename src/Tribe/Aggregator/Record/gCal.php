<?php
// Don't load directly
defined( 'WPINC' ) or die;

class Tribe__Events__Aggregator__Record__gCal extends Tribe__Events__Aggregator__Record__Abstract {
	public $origin = 'gcal';

	/**
	 * Public facing Label for this Origin
	 *
	 * @return string
	 */
	public function get_label() {
		return __( 'Google Calendar', 'the-events-calendar' );
	}
}
