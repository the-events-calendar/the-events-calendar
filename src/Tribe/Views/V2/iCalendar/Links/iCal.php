<?php
/**
 * Handles iCal export/subscribe links.
 *
 * @since   TBD
 *
 * @package Tribe\Events\Views\V2\iCalendar
 */

namespace Tribe\Events\Views\V2\iCalendar\Links;

/**
 * Class iCal
 *
 * @since   TBD
 *
 * @package Tribe\Events\Views\V2\iCalendar
 */
class iCal extends Link_Abstract {
	/**
	 * {@inheritDoc}
	 */
	public static $slug = 'ical';

	/**
	 * {@inheritDoc}
	 */
	public static function get_label( $view ) {
		return __( 'iCalendar', 'the-events-calendar' );
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_single_label( $view ) {
		return __( 'Add to iCalendar', 'the-events-calendar' );
	}
}
