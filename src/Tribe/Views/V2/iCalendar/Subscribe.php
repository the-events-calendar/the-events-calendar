<?php
/**
 * Handles (optionally) converting iCalendar export links to subscribe links.
 *
 * @since   4.6.0
 *
 * @package Tribe\Events\Views\V2\iCalendar
 */

namespace Tribe\Events\Views\V2\iCalendar;

/**
 * Class Subscribe
 *
 * @since   TBD
 *
 * @package Tribe\Events\Views\V2\iCalendar
 */
class Subscribe extends \tad_DI52_ServiceProvider {

	/**
	 * A placeholder for the option to keep
	 * "legacy" export links vs. the new subscription links.
	 *
	 * @since TBD
	 *
	 * @var boolean
	 */
	protected static $toggle;

	/**
	 * Template slug for legacy export template.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public static $export_template = 'ical-link';

	/**
	 * Template slug for new subscribe template.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public static $subscribe_template = 'ical-subscribe-dropdown';

	public function register() {
		static::$toggle = apply_filters( 'tec_use_subscribe_links', true );

		if ( static::$toggle ) {
			$this->register_hooks();
		}
	}

	public function register_hooks() {
		add_filter( 'tribe_template_file', [ $this, 'replace_export_links' ], 10, 3 );
		add_filter( 'tribe_events_views_v2_view_template_vars', [ $this, 'template_vars' ], 10, 2 );
		add_filter( 'tribe_events_ical_single_event_links', [ $this, 'single_event_links' ], 11 );
		add_filter( 'tribe_ical_properties', [ $this, 'ical_properties' ] );
	}

	/**
	 * This method will replace the "The Events Calendar Extension: Events Control" ical-link template
	 * with a dropdown that includes subscribe links.
	 *
	 * @since TBD
	 *
	 * @param string               $file       The template file found for the template name.
	 * @param array<string>|string $name       The name, or name fragments, of the requested template.
	 * @param \Tribe__Template     $template   The template instance that is currently handling the template location
	 *                                                                                                     request.
	 *
	 * @return string The path to the template to load.
	 */
	public function replace_export_links( $file, $name, \Tribe__Template $template ) {
		if ( is_string( $name ) && static::$export_template !== $name ) {
			return $file;
		}

		if ( is_array( $name ) && ! in_array( static::$export_template, $name ) ) {
			return $file;
		}

		return str_replace( static::$export_template, static::$subscribe_template, $file );
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
		$template_vars['subscribe_links'] = [
			[
				'label' => __( 'Google Calendar', 'the-events-calendar' ),
				'uri'   => static::get_gcal_uri( $view ),
			],
			[
				'label' => __( 'iCalendar', 'the-events-calendar' ),
				'uri'   => static::get_ical_uri( $view ),
			],
		];

		/**
		 * Add the .ics legacy export link.
		 *
		 * This is controlled by the default iCal_Data trait.
		 *
		 * @see Tribe\Events\Views\V2\Views\Traits\iCal_Data
		 */
		if ( isset( $template_vars['ical'] ) && $template_vars['ical']->display_link ) {
			$template_vars['subscribe_links'][] = [
				'label' => __( 'Download as .ICS', 'the-events-calendar' ),
				'uri'   => $template_vars['ical']->link->url,
			];
		}

		return $template_vars;
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
		$single_ical_link = tribe_get_single_ical_link();

		$view = new class extends \Tribe\Events\Views\V2\View {
		};
		$view->set_url( [] );
		$view->set_context( tribe_context()->alter( [
			'single_ical_link' => $single_ical_link,
		] ) );

		$labels = [
			'gcal' => __( 'Subscribe via Google Calendar', 'the-events-calendar' ),
			'ical' => __( 'Subscribe via iCalendar', 'the-events-calendar' ),
		];

		$calendar_links = '<div class="tribe-events-cal-links">';
		$calendar_links .= '<a class="tribe-events-gcal tribe-events-button" href="' . esc_url( static::get_gcal_uri( $view ) ) . '" title="' . esc_attr( $labels['gcal'] ) . '">+ ' . esc_html( $labels['gcal'] ) . '</a>';
		$calendar_links .= '<a class="tribe-events-ical tribe-events-button" href="' . esc_url( static::get_ical_uri( $view ) ) . '" title="' . esc_attr( $labels['ical'] ) . '" >+ ' . esc_html( $labels['ical'] ) . '</a>';
		$calendar_links .= '</div><!-- .tribe-events-cal-links -->';

		return $calendar_links;
	}

	/**
	 * Add iCal REFRESH and TTL headers.
	 *
	 * Some clients may ignore these refresh headers.
	 * https://support.google.com/calendar/answer/37100?hl=en&ref_topic=1672445
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


	/**
	 * Retrieve the iCal Feed URL with current context parameters.
	 *
	 * Both iCal and gCal URIs can be built from the Feed URL which simply
	 * points to a canonical URL that the generator can parse
	 * via `tribe_get_global_query_object` and spew out results in the
	 * ICS format.
	 *
	 * This is exactly what \Tribe__Events__iCal::do_ical_template does
	 * and lets it generate from a less vague and a more context-bound URL
	 * for more granular functionality. This lets us have shortcode support
	 * among other things.
	 *
	 * We strip some of the things that we don't need for subscriptions
	 * like end dates, view types, etc., ignores pagination and always returns
	 * fresh future events. Subscriptions to past events is pointless.
	 *
	 * The URL generated is also inert to the Permalink and Rewrite Rule settings
	 * in WordPress, so will work out of the box on any website, even if
	 * the settings are changes or break.
	 *
	 * @param \Tribe\Events\Views\V2\View $view The View we're being called from.
	 *
	 * @return string The iCal Feed URI.
	 */
	public static function get_canonical_ics_feed_url( \Tribe\Events\Views\V2\View $view ) {
		if ( $single_ical_link = $view->get_context()->get( 'single_ical_link' ) ) {
			/**
			 * This is not really canonical. As single event views are not actually
			 * Views\V2\View instances we pass them out as is. A lot of extra fundamental
			 * things need to happen before we can actually canonicalize single iCal links.
			 */
			return $single_ical_link;
		}

		$view_url_args = $view->get_url_args();

		// Clean query params to only contain canonical arguments.
		$canonical_args = [ 'post_type', 'tribe_events_cat' ];

		foreach ( $view_url_args as $arg => $value ) {
			if ( ! in_array( $arg, $canonical_args, true ) ) {
				unset( $view_url_args[ $arg ] );
			}
		}

		$view_url_args['tribe-bar-date'] = date( 'Y-m-d' ); // Subscribe from today.
		$view_url_args['ical'] = 1; // iCalendarize!

		return add_query_arg( urlencode_deep( $view_url_args ), home_url( '/' ) );
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
		return add_query_arg(
			[ 'cid' => urlencode( static::get_ical_uri( $view ) ) ],
			'https://www.google.com/calendar/render?cid='
		);
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
