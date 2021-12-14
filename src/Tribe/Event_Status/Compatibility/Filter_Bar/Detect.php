<?php
/**
 * Handles detecting if the Filter Bar Plugin this there.
 *
 * @since   5.12.1
 *
 * @package Tribe\Events\Event_Status\Compatibility\Filter_Bar
 */

namespace Tribe\Events\Event_Status\Compatibility\Filter_Bar;

/**
 * Class Detect
 *
 * @since   5.12.1
 *
 * @package Tribe\Events\Event_Status\Compatibility\Filter_Bar
 */
class Detect {

	/**
	 * Detects if the Filter Bar constant is defined and thus active.
	 *
	 * @since 5.12.1
	 *
	 * @return bool  Whether the constant is defined.
	 */
	public static function is_active() {
		/**
		 * Allow filtering whether Filter Bar is active for Event Status Filter.
		 *
		 * @since 5.12.1
		 *
		 * @param boolean Whether or not the constant is defined.
		 */
		return (boolean) apply_filters( 'tec_event_status_detect_filterbar_constant', defined( 'TRIBE_EVENTS_FILTERBAR_FILE' ) );
	}
}
