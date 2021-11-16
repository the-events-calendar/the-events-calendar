<?php
/**
 * The base implementation for the Views v2 query controllers.
 *
 * @package Tribe\Events\Views\V2\iCalendar
 * @since TBD
 */

namespace Tribe\Events\Views\V2\iCalendar\Links;

use Tribe__Date_Utils as Dates;

/**
 * Class Abstract_Link
 *
 * @package Tribe\Events\Views\V2\iCalendar
 * @since TBD
 */
abstract class Link_Abstract implements Link_Interface {

	/**
	 * The (translated) text/label for the link.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public static $label;

	/**
	 * Whether to display the link or not.
	 *
	 * @since TBD
	 *
	 * @var boolean
	 */
	public static $display = true;

	/**
	 * the link provider slug.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public static $slug;

	/**
	 * Registers the objects and filters required by the provider to manage subscribe links.
	 *
	 * @since TBD
	 */
	public function register() {
		add_filter( 'tec_views_v2_subscribe_links', [ $this, 'filter_tec_views_v2_subscribe_links'], 10, 2 );
		add_filter( 'tec_views_v2_single_subscribe_links', [ $this, 'filter_tec_views_v2_single_subscribe_links' ], 10, 2 );
	}

	/**
	 * {@inheritDoc}
	 */
	public function filter_tec_views_v2_subscribe_links( $template_vars, $view ) {
		$template_vars['subscribe_links'][static::get_slug()] = $this;

		return $template_vars;
	}

	/**
	 * {@inheritDoc}
	 */
	public function filter_tec_views_v2_single_subscribe_links( $links, $view ) {
		$class = sanitize_html_class( 'tribe-events-' . static::get_slug() );
		$links[] = '<a class="tribe-events-button ' . $class
				. '" href="' . esc_url( $this->get_uri( $view ) )
				. '" title="' . esc_attr( static::get_single_label( $view ) )
				. '">+ ' . esc_html( static::get_single_label( $view ) ) . '</a>';

		return $links;
	}

	/**
	 * {@inheritDoc}
	 */
	public static function is_visible( $view ) {
		return static::$display;
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_label( $view ) {
		return static::$label;
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_single_label( $view ) {
		return static::get_label( $view );
	}

	/**
	 * {@inheritDoc}
	 */
	public static function get_slug() {
		return static::$slug;
	}

	/**
	 * {@inheritDoc}
	 */
	public static function set_visibility( bool $visible ) {
		static::$display = $visible;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_uri( \Tribe\Events\Views\V2\View $view ) {
		// If we're on a Single Event view, let's bypass the canonical function call and logic.
		$feed_url = $view->get_context()->get( 'single_ical_link', false );

		if ( ! $feed_url  ) {
			$feed_url = $this->get_canonical_ics_feed_url( $view );
		}

		$feed_url = str_replace( [ 'http://', 'https://' ], 'webcal://', $feed_url );

		return $feed_url;
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
	protected function get_canonical_ics_feed_url( \Tribe\Events\Views\V2\View $view ) {
		$view_url_args = $view->get_url_args();

		// Clean query params to only contain canonical arguments.
		$canonical_args = [ 'post_type', 'tribe_events_cat' ];

		foreach ( $view_url_args as $arg => $value ) {
			if ( ! in_array( $arg, $canonical_args, true ) ) {
				unset( $view_url_args[ $arg ] );
			}
		}

		// Subscribe from today.
		$view_url_args['tribe-bar-date'] = Dates::build_date_object()->format( Dates::DBDATEFORMAT );

		// iCalendarize!
		$view_url_args['ical'] = 1;

		return add_query_arg( urlencode_deep( $view_url_args ), home_url( '/' ) );
	}
}
