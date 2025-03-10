<?php
/**
 * Handles iCalendar export links.
 *
 * @since   5.12.0
 *
 * @package Tribe\Events\Views\V2\iCalendar
 */

namespace Tribe\Events\Views\V2\iCalendar\Links;
use Tribe\Events\Views\V2\View;
use Tribe__Events__Main;
use Tribe\Events\Views\V2\iCalendar\Traits\Export_Link;

/**
 * Class iCal
 *
 * @since   5.12.0
 *
 * @package Tribe\Events\Views\V2\iCalendar
 */
class iCalendar_Export extends Link_Abstract {
	use Export_Link;

	/**
	 * The link provider slug.
	 *
	 * @since 5.12.0
	 *
	 * @var string
	 */
	public static $slug = 'ics';

	/**
	 * {@inheritDoc}
	 */
	public function register() {
		add_filter( 'tec_views_v2_subscribe_link_ics_visibility', [ $this, 'filter_tec_views_v2_subscribe_link_ics_visibility'], 10, 2 );
	}

	/**
	 * {@inheritDoc}
	 */
	protected function label(): string {
		return __( 'Export .ics file', 'the-events-calendar' );
	}

	/**
	 * {@inheritDoc}
	 */
	protected function single_label(): string {
		return $this->label();
	}

	/**
	 * Filters the is_visible() function to not display on single events.
	 *
	 * @since 5.14.0
	 *
	 * @param boolean $visible Whether to display the link.
	 * @param View    $view     The current View object.
	 *
	 * @return boolean $visible Whether to display the link.
	 */
	public function filter_tec_views_v2_subscribe_link_ics_visibility( $visible ) {
		_deprecated_function( __METHOD__, 'TBD', 'iCalendar_Export::filter_tec_views_v2_subscribe_link_visibility' );
		// Don't display on single event by default.
		return self::filter_tec_views_v2_subscribe_link_visibility( $visible, $this );
	}
}
