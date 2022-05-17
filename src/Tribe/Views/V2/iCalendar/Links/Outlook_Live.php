<?php
/**
 * Handles Outlook Live export/subscribe links.
 *
 * @since   TBD
 *
 * @package Tribe\Events\Views\V2\iCalendar
 */

namespace Tribe\Events\Views\V2\iCalendar\Links;

/**
 * Class Outlook_Live
 *
 * @since   TBD
 *
 * @package Tribe\Events\Views\V2\iCalendar
 */
class Outlook_Live extends Outlook_Abstract_Export {

	/**
	 * {@inheritDoc}
	 */
	public static $slug = 'outlook-live';

	/**
	 * {@inheritDoc}
	 */
	public static $calendar_slug = 'live';

	/**
	 * {@inheritDoc}
	 */
	public function register() {
		$this->label        = __( 'Outlook Live', 'the-events-calendar' );
		$this->single_label = $this->label;
	}
}
