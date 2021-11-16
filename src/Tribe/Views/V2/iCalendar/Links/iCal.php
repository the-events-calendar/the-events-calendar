<?php
/**
 * Handles iCal export/subscribe links.
 *
 * @since   TBD
 *
 * @package Tribe\Events\Views\V2\iCalendar
 */

namespace Tribe\Events\Views\V2\iCalendar\Links;

use Tribe\Events\Views\V2\iCalendar\Service_Provider;

/**
 * Class iCal
 *
 * @since   TBD
 *
 * @package Tribe\Events\Views\V2\iCalendar
 */
class iCal extends Abstract_Link {
	/**
	 * {@inheritDoc}
	 */
	public function add_subscribe_link( $template_vars, $view ) {
		$template_vars['subscribe_links']['ical'] = [
			'display' => tribe( Service_Provider::class)->use_subscribe_links(),
			'label' => __( 'iCalendar', 'the-events-calendar' ),
			'uri'   => static::get_ical_uri( $view ),
		];

		return $template_vars;
	}

	/**
	 * {@inheritDoc}
	 */
	public function add_single_subscribe_link( $links, $view) {
		$label = __( 'Subscribe via iCalendar', 'the-events-calendar' );
		$links[] = '<a class="tribe-events-ical tribe-events-button" href="' . esc_url( static::get_ical_uri( $view ) ) . '" title="' . esc_attr( $label ) . '" >+ ' . esc_html( $label ) . '</a>';

		return $links;
	}

	/**
	 * Retrieve the iCalendar URI.
	 *
	 * Clicking this link will open up the default iCalendar
	 * handler. Might open Google Calendar in some cases.
	 *
	 * The initial request will go out over HTTP, then switched to HTTPs by the
	 * server. There's no webcal`s`://-based scheme that's officially supported.
	 *
	 * @since TBD
	 *
	 * @param \Tribe\Events\Views\V2\View $view The View we're being called from.
	 *
	 * @return string The iCalendar URI.
	 */
	public static function get_ical_uri( \Tribe\Events\Views\V2\View $view ) {
		$canonical_ics_feed_url = static::get_canonical_ics_feed_url( $view );

		return str_replace( [ 'http://', 'https://' ], 'webcal://', $canonical_ics_feed_url );
	}
}
