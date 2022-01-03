<?php
/**
 * Handles iCal export/subscribe links.
 *
 * @since   5.12.0
 *
 * @package Tribe\Events\Views\V2\iCalendar
 */

namespace Tribe\Events\Views\V2\iCalendar\Links;

/**
 * Class iCal
 *
 * @since   5.12.0
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
	public function register() {
		$this->label = __( 'iCalendar', 'the-events-calendar' );
		$this->single_label = __( 'Add to iCalendar', 'the-events-calendar' );
	}
}
