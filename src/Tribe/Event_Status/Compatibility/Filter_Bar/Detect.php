<?php
/**
 * Handles detecting if the Filter Bar Plugin this there.
 *
 * @since   TBD
 *
 * @package Tribe\Events\Event_Status\Compatibility\Filter_Bar
 */

namespace Tribe\Events\Event_Status\Compatibility\Filter_Bar;

/**
 * Class Detect
 *
 * @since   TBD
 *
 * @package Tribe\Events\Event_Status\Compatibility\Filter_Bar
 */
class Detect {

	/**
	 * Detects if the Filter Bar constant is defined and thus active.
	 *
	 * @since TBD
	 *
	 * @return bool  Whether the constant is defined.
	 */
	public static function is_active() {
		/**
		 * Allow filtering whether Filter Bar is active for Event Status Filter.
		 *
		 * @since TBD
		 *
		 * @param boolean Whether or not the constant is defined.
		 */
		return (boolean) apply_filters( 'tec_event_status_filterbar_values', defined( 'TRIBE_EVENTS_FILTERBAR_FILE' ) );
	}
}
