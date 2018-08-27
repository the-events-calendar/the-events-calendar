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

	/**
	 * Filters the event to ensure that fields are preserved that are not otherwise supported by ICS
	 *
	 * @param array $event Event data
	 * @param Tribe__Events__Aggregator__Record__Abstract $record Aggregator Import Record
	 *
	 * @return array
	 */
	public static function filter_event_to_preserve_fields( $event, $record ) {
		if ( 'gcal' !== $record->origin ) {
			return $event;
		}

		return self::preserve_event_option_fields( $event );
	}
}
