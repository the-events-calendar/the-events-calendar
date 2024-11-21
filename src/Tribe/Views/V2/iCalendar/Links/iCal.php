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
	public $block_slug = 'hasiCal';

	/**
	 * {@inheritDoc}
	 */
	public function register() {
		// intentionally left blank.
	}

	/**
	 * {@inheritDoc}
	 */
	protected function label(): string {
		return __( 'iCalendar', 'the-events-calendar' );
	}

	/**
	 * {@inheritDoc}
	 */
	protected function single_label(): string {
		return __( 'Add to iCalendar', 'the-events-calendar' );
	}
}
