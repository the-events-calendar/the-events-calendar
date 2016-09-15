<?php
// Don't load directly
defined( 'WPINC' ) or die;

class Tribe__Events__Aggregator__Record__iCal extends Tribe__Events__Aggregator__Record__Abstract {
	public $origin = 'ical';

	/**
	 * Public facing Label for this Origin
	 *
	 * @return string
	 */
	public function get_label() {
		return __( 'iCalendar', 'the-events-calendar' );
	}
}
