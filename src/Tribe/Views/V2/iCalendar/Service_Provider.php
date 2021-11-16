<?php
/**
 * Handles (optionally) converting iCalendar export links to subscribe links.
 *
 * @since   4.6.0
 *
 * @package Tribe\Events\Views\V2\iCalendar
 */

namespace Tribe\Events\Views\V2\iCalendar;

use Tribe\Events\Views\V2\iCalendar\Links\Google_Calendar;
use Tribe\Events\Views\V2\iCalendar\Links\iCal;
use Tribe\Events\Views\V2\iCalendar\Links\iCalendar_Export;

/**
 * Class Service_Provider
 *
 * @since   TBD
 *
 * @package Tribe\Events\Views\V2\iCalendar
 */
class Service_Provider extends \tad_DI52_ServiceProvider {
	public function register() {
		$this->container->singleton( static::class, $this );
		$this->container->singleton( Google_Calendar::class, Google_Calendar::class );
		$this->container->singleton( iCal::class, iCal::class );
		$this->container->singleton( iCalendar_Export::class, iCalendar_Export::class );

		add_action( 'after_setup_theme', [ $this, 'register_hooks' ]);
	}

	public function use_subscribe_links() {
		return apply_filters( 'tec_views_v2_use_subscribe_links', true );
	}

	public function register_hooks() {
		tribe( Google_Calendar::class )->register();
		tribe( iCal::class )->register();
		tribe( iCalendar_Export::class )->register();

		add_filter( 'tribe_events_views_v2_view_template_vars', [ $this, 'template_vars' ], 10, 2 );
		add_filter( 'tribe_events_ical_single_event_links', [ $this, 'single_event_links' ], 11 );
		add_filter( 'tribe_ical_properties', [ $this, 'ical_properties' ] );
	}

	/**
	 * Add iCal feed link labels and URIs to the global template vars.
	 *
	 * Usable in ical-link.php via the $subscribe_links global.
	 *
	 * @see `tribe_events_views_v2_view_template_vars` filter.
	 *
	 * @since TBD
	 *
	 * @param array $template_vars The vars.
	 * @param \Tribe\Events\Views\V2\View $view The View implementation.
	 *
	 * @return array The filtered template variables.
	 */
	public function template_vars( $template_vars, \Tribe\Events\Views\V2\View $view ) {
		// Set up the section of the $template vars for the links.
		$template_vars['subscribe_links'] = [];
		return apply_filters( 'tec_views_v2_subscribe_links', $template_vars, $view );
	}

	/**
	 * Replace the default single event links with subscription links.
	 *
	 * As single calendars are not really a View\V2\View we have to emulate one.
	 * We use `tribe_get_single_ical_link` to figure out what the feed URI
	 * should be for this pseudo-View.
	 * Fun.
	 *
	 * @see `tribe_events_ical_single_event_links` filter.
	 *
	 * @since TBD
	 *
	 * @param string $calendar_links The link content.
	 *
	 * @return string The altered link content.
	 */
	public function single_event_links( $calendar_links ) {
		if ( ! $this->use_subscribe_links() ) {
			return $calendar_links;
		}

		$single_ical_link = tribe_get_single_ical_link();

		$view = new class extends \Tribe\Events\Views\V2\View {};
		$view->set_url( [] );
		$view->set_context( tribe_context()->alter( [
			'single_ical_link' => $single_ical_link,
		] ) );

		$links = [];
		$links = apply_filters( 'tec_views_v2_single_subscribe_links', $links, $view );

		$calendar_links = '<div class="tribe-events-cal-links">';

		foreach( $links as $link ) {
			$calendar_links .= $link;
		}

		$calendar_links .= '</div><!-- .tribe-events-cal-links -->';

		return $calendar_links;
	}

	/**
	 * Add iCal REFRESH and TTL headers.
	 *
	 * Some clients may ignore these refresh headers.
	 * https://support.google.com/calendar/answer/37100?hl=en&ref_topic=1672445
	 *
	 * REFRESH-INTERVAL (iCalendar standards, so Google and iCal):
	 * https://icalendar.org/New-Properties-for-iCalendar-RFC-7986/5-7-refresh-interval-property.html
	 *
	 * X-PUBLISHED-TTL (Recommended update interval for subscription to the calendar via extension, used by Microsoft):
	 * https://docs.microsoft.com/en-us/openspecs/exchange_server_protocols/ms-oxcical/1fc7b244-ecd1-4d28-ac0c-2bb4df855a1f
	 *
	 * Note: PT1H means Once per hour.
	 *
	 * @see `tribe_ical_properties` filter.
	 *
	 * @since TBD
	 *
	 * @param string $content The iCal content.
	 *
	 * @return string The filtered content.
	 */
	public function ical_properties( $content ) {
		$content .= "REFRESH-INTERVAL;VALUE=DURATION:PT1H\r\n";
		return $content . "X-PUBLISHED-TTL:PT1H\r\n";
	}
}
