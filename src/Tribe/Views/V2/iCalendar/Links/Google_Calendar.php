<?php
/**
 * Handles Google_Calendar export/subscribe links.
 *
 * @since   TBD
 *
 * @package Tribe\Events\Views\V2\iCalendar
 */

namespace Tribe\Events\Views\V2\iCalendar\Links;

use Tribe\Events\Views\V2\iCalendar\Service_Provider;

/**
 * Class Google_Calendar
 *
 * @since   TBD
 *
 * @package Tribe\Events\Views\V2\iCalendar
 */
class Google_Calendar extends Abstract_Link {
	/**
	 * {@inheritDoc}
	 */
	public function add_subscribe_link( $template_vars, $view ) {
		$template_vars['subscribe_links']['google'] = [
			'display' => tribe( Service_Provider::class)->use_subscribe_links(),
			'label' => __( 'Google Calendar', 'the-events-calendar' ),
			'uri'   => static::get_gcal_uri( $view ),
		];

		return $template_vars;
	}

	/**
	 * {@inheritDoc}
	 */
	public function add_single_subscribe_link( $links, $view ) {
		$label = __( 'Subscribe via Google Calendar', 'the-events-calendar' );
		$links[] = '<a class="tribe-events-gcal tribe-events-button" href="' . esc_url( static::get_gcal_uri( $view ) ) . '" title="' . esc_attr( $label ) . '">+ ' . esc_html( $label ) . '</a>';

		return $links;
	}

	/**
	 * Retrieve the Google Calendar URI.
	 *
	 * Clicking this link will open up Google Calendar.
	 *
	 * @since TBD
	 *
	 * @param \Tribe\Events\Views\V2\View $view The View we're being called from.
	 *
	 * @return string The Google Calendar URI.
	 */
	public static function get_gcal_uri( \Tribe\Events\Views\V2\View $view ) {
		$canonical_ics_feed_url = static::get_canonical_ics_feed_url( $view );

		$canonical_ics_feed_url = str_replace( [ 'http://', 'https://' ], 'webcal://', $canonical_ics_feed_url );

		return add_query_arg(
			[ 'cid' => urlencode( $canonical_ics_feed_url ) ],
			'https://www.google.com/calendar/render?cid='
		);
	}
}
