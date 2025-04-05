<?php
/**
 * Handles Outlook Live export/subscribe links.
 *
 * @since   5.16.0
 *
 * @package Tribe\Events\Views\V2\iCalendar
 */

namespace Tribe\Events\Views\V2\iCalendar\Links;

use Tribe\Events\Views\V2\iCalendar\Traits\Outlook_Methods;

/**
 * Class Outlook_Live
 *
 * @since   5.16.0
 *
 * @package Tribe\Events\Views\V2\iCalendar
 */
class Outlook_Live extends Link_Abstract {

	use Outlook_Methods;

	/**
	 * {@inheritDoc}
	 */
	public static $slug = 'outlook-live';

	/**
	 * {@inheritDoc}
	 */
	public $block_slug = 'hasOutlookLive';

	/**
	 * {@inheritDoc}
	 */
	public static $calendar_slug = 'live';

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
		return __( 'Outlook Live', 'the-events-calendar' );
	}

	/**
	 * {@inheritDoc}
	 */
	protected function single_label(): string {
		return $this->label();
	}

}
