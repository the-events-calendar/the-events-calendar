<?php
/**
 * Handles Google Calendar export/subscribe links.
 *
 * @since   5.12.0
 *
 * @package Tribe\Events\Views\V2\iCalendar
 */

namespace Tribe\Events\Views\V2\iCalendar\Links;

use Tribe\Events\Views\V2\View;

/**
 * Class Google_Calendar
 *
 * @since   5.12.0
 *
 * @package Tribe\Events\Views\V2\iCalendar
 */
class Google_Calendar extends Link_Abstract {
	/**
	 * {@inheritDoc}
	 */
	public static $slug = 'gcal';

	/**
	 * {@inheritDoc}
	 */
	public function register() {
		$this->label = __( 'Google Calendar', 'the-events-calendar' );
		$this->single_label = __( 'Add to Google Calendar', 'the-events-calendar' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_uri( View $view = null ) {
		$feed_url = parent::get_uri( $view );

		return add_query_arg(
			[ 'cid' => urlencode( $feed_url ) ],
			'https://www.google.com/calendar/render?cid='
		);
	}
}
