<?php
/**
 * Handles Outlook 365 export/subscribe links.
 *
 * @since   5.16.0
 *
 * @package Tribe\Events\Views\V2\iCalendar
 */

namespace Tribe\Events\Views\V2\iCalendar\Links;

use Tribe\Events\Views\V2\iCalendar\Traits\Outlook_Methods;

/**
 * Class Outlook_365
 *
 * @since   5.16.0
 *
 * @package Tribe\Events\Views\V2\iCalendar
 */
class Outlook_365 extends Link_Abstract {

	use Outlook_Methods;

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
	public $block_slug = 'hasOutlook365';

	/**
	 * {@inheritDoc}
	 */
	public function register() {
		$this->label        = __( 'Outlook 365', 'the-events-calendar' );
		$this->single_label = $this->label;
	}
}
