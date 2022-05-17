<?php
/**
 * Handles Outlook 365 export/subscribe links.
 *
 * @since   TBD
 *
 * @package Tribe\Events\Views\V2\iCalendar
 */

namespace Tribe\Events\Views\V2\iCalendar\Links;

/**
 * Class Outlook_365
 *
 * @since   TBD
 *
 * @package Tribe\Events\Views\V2\iCalendar
 */
class Outlook_365 extends Outlook_Abstract_Export {

	/**
	 * {@inheritDoc}
	 */
	public static $slug = 'outlook-365';

	/**
	 * {@inheritDoc}
	 */
	public static $calendar_slug = 'office';

	/**
	 * {@inheritDoc}
	 */
	public function register() {
		$this->label        = __( 'Outlook 365', 'the-events-calendar' );
		$this->single_label = $this->label;
	}
}
