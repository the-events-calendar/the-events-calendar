<?php
/**
 * Handles Outlook iCalendar export links.
 *
 * @since 5.16.0
 *
 * @package Tribe\Events\Views\V2\iCalendar
 */

namespace Tribe\Events\Views\V2\iCalendar\Links;
use Tribe\Events\Views\V2\View;
use Tribe__Events__Main;
use Tribe\Events\Views\V2\iCalendar\Traits\Export_Link;

/**
 * Class Outlook
 *
 * @since 5.16.0
 *
 * @package Tribe\Events\Views\V2\iCalendar
 */
class Outlook_Export extends Link_Abstract {
	use Export_Link;

	/**
	 * The link provider slug.
	 *
	 * @since 5.12.0
	 *
	 * @var string
	 */
	public static $slug = 'outlook-ics';

	/**
	 * {@inheritDoc}
	 */
	public function register() {
		self::$query_arg    = 'outlook-ical';
		$this->label        = _x( 'Export Outlook .ics file', 'The text for the link to export and Outlook ics file.', 'the-events-calendar' );
		$this->single_label = $this->label;
		$this->filters();
	}

	/**
	 * Filters the is_visible() function to not display on single events.
	 *
	 * @since 5.16.0
	 * @deprecated TBD
	 *
	 * @param boolean $visible Whether to display the link.
	 * @param View    $view     The current View object.
	 *
	 * @return boolean $visible Whether to display the link.
	 */
	public function filter_tec_views_v2_subscribe_link_outlook_ics_visibility( $visible ) {
		_deprecated_function( __METHOD__, 'TBD', 'Outlook_Export::filter_tec_views_v2_subscribe_link_visibility' );
		// Don't display on single event by default.
		return self::filter_tec_views_v2_subscribe_link_visibility( $visible, $this );
	}
}
