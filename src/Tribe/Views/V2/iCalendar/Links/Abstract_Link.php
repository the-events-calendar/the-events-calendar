<?php
/**
 * The base implementation for the Views v2 query controllers.
 *
 * @package Tribe\Events\Views\V2\iCalendar
 * @since TBD
 */

namespace Tribe\Events\Views\V2\iCalendar\Links;

/**
 * Class Abstract_Link
 *
 * @package Tribe\Events\Views\V2\iCalendar
 * @since TBD
 */
abstract class Abstract_Link {
	public function register() {
		add_filter( 'tec_views_v2_subscribe_links', [ $this, 'add_subscribe_link'], 10, 2 );
		add_filter( 'tec_views_v2_single_subscribe_links', [ $this, 'add_single_subscribe_link' ], 10, 2 );
	}

	/**
	 * Adds a subscribe link and associated data to the list of links.
	 * In the format:
	 *		['subscribe_links']['provider_slug'] = [
	 *			'display' => true,
	 *			'label'   => string,
	 *			'uri'     => string,
	 *		]
	 *
	 * Setting 'display' to false will "opt out" of displaying that link in the drop-down.
	 *
	 * @since TBD
	 *
	 * @param array $template_vars The template variables.
	 * @param \Tribe\Events\Views\V2\View $view The View implementation.
	 *
	 * @return array $template_vars The modified vars.
	 */
	public function add_subscribe_link( $template_vars, $view ) {}

	/**
	 * Adds a link to those displayed on the single event view.
	 *
	 * @since TBD
	 *
	 * @param array<string>               $links The current list of links.
	 * @param \Tribe\Events\Views\V2\View $view  The View implementation.
	 *
	 * @return array<string> $links The modified list of links.
	 */
	public function add_single_subscribe_link( $links, $view ) {}

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
}
