<?php


/**
 * Class Tribe__Events__Pro__Recurrence__Utils
 *
 * An aliasing class to make utils dependency injection easier in recurrence
 * related classes.
 */
class Tribe__Events__Pro__Recurrence__Utils {

	public function datepicker_formats( $translate = null ) {
		return Tribe__Date_Utils::datepicker_formats( $translate );
	}

	public function datetime_from_format( $format, $date ) {
		return Tribe__Date_Utils::datetime_from_format( $format, $date );
	}

	public function to_key( $custom_type ) {
		return Tribe__Events__Pro__Recurrence__Custom_Types::to_key( $custom_type );
	}

	public function is_valid( $event_id, array $recurrence_meta ) {
		return Tribe__Events__Pro__Recurrence__Validator::instance()->is_valid( $event_id, $recurrence_meta );
	}

	public function recurrence_strings() {
		return Tribe__Events__Pro__Recurrence__Strings::recurrence_strings();
	}
}
