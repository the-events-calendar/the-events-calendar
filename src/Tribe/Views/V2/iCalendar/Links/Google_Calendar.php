<?php
/**
 * Handles Google_Calendar export/subscribe links.
 *
 * @since   TBD
 *
 * @package Tribe\Events\Views\V2\iCalendar
 */

namespace Tribe\Events\Views\V2\iCalendar\Links;

/**
 * Class Google_Calendar
 *
 * @since   TBD
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
	public static function get_label( $view ) {
		return __( 'Google Calendar', 'the-events-calendar' );
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_single_label( $view ) {
		return __( 'Subscribe via Google Calendar', 'the-events-calendar' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_uri( $view ) {
		$feed_url = parent::get_uri( $view );

		return add_query_arg(
			[ 'cid' => urlencode( $feed_url ) ],
			'https://www.google.com/calendar/render?cid='
		);
	}
}
