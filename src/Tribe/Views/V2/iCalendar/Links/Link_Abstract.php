<?php
/**
 * The base implementation for the Views v2 query controllers.
 *
 * @since   5.12.0
 * @package Tribe\Events\Views\V2\iCalendar
 */

namespace Tribe\Events\Views\V2\iCalendar\Links;

use Tribe__Date_Utils as Dates;
use \Tribe\Events\Views\V2\View;

/**
 * Class Abstract_Link
 *
 * @since   5.12.0
 * @package Tribe\Events\Views\V2\iCalendar
 */
abstract class Link_Abstract implements Link_Interface {

	/**
	 * The (translated) text/label for the link.
	 *
	 * @since 5.12.0
	 *
	 * @var string
	 */
	public $label;

	/**
	 * The (translated) text/label for the link.
	 *
	 * @since 5.12.0
	 *
	 * @var string
	 */
	public $single_label;

	/**
	 * Whether to display the link or not.
	 *
	 * @since 5.12.0
	 *
	 * @var boolean
	 */
	public $display = true;

	/**
	 * the link provider slug.
	 *
	 * @since 5.12.0
	 *
	 * @var string
	 */
	public static $slug;

	/**
	 * Determines if this instance of the class has it's actions and filters hooked.
	 *
	 * @since 5.12.3
	 *
	 * @var bool
	 */
	protected $hooked = false;

	/**
	 * Link_Abstract constructor.
	 *
	 * @since 5.12.3
	 */
	public function __construct() {
		$this->register();
	}

	/**
	 * Sets the hooked param for flagging if the hooks were created.
	 *
	 * @since 5.12.3
	 *
	 * @param bool $hooked What to save in the hooked var.
	 */
	public function set_hooked( bool $hooked = true ) {
		$this->hooked = $hooked;
	}

	/**
	 * Hooks this instance actions and filters.
	 *
	 * @since 5.12.3
	 */
	public function hook() {
		if ( true === $this->hooked ) {
			return;
		}

		add_filter( 'tec_views_v2_subscribe_links', [ $this, 'filter_tec_views_v2_subscribe_links' ], 10 );
		add_filter( 'tec_views_v2_single_subscribe_links', [ $this, 'filter_tec_views_v2_single_subscribe_links' ], 10, 2 );

		$this->set_hooked();
	}

	/**
	 * {@inheritDoc}
	 */
	public function filter_tec_views_v2_subscribe_links( $subscribe_links ) {
		$subscribe_links[ static::get_slug() ] = $this;

		return $subscribe_links;
	}

	/**
	 * {@inheritDoc}
	 */
	public function filter_tec_views_v2_single_subscribe_links( $links ) {
		$class   = sanitize_html_class( 'tribe-events-' . static::get_slug() );
		$links[] = '<a class="tribe-events-button ' . $class
		           . '" href="' . esc_url( $this->get_uri( null ) )
		           . '" title="' . esc_attr( $this->get_single_label( null ) )
		           . '">+ ' . esc_html( $this->get_single_label( null ) ) . '</a>';

		return $links;
	}

	/**
	 * {@inheritDoc}
	 */
	public function is_visible( View $view = null ) {
		return $this->display;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_label( View $view = null ) {
		$slug = self::get_slug();

		/**
		 * Allows filtering of the labels for the Calendar view labels.
		 *
		 * @since 5.12.0
		 *
		 * @param string        $label    The label that will be displayed.
		 * @param Link_Abstract $link_obj The link object the label is for.
		 * @param View          $view     The current View object.
		 *
		 * @return string $label The label that will be displayed.
		 */
		return apply_filters( "tec_views_v2_subscribe_links_{$slug}_label", $this->label, $this, $view );
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_single_label( View $view = null ) {
		$slug = self::get_slug();

		/**
		 * Allows filtering of the labels for the Single Event view labels.
		 *
		 * @since 5.12.0
		 *
		 * @param string        $label    The label that will be displayed.
		 * @param Link_Abstract $link_obj The link object the label is for.
		 * @param View          $view     The current View object.
		 *
		 * @return string $label The label that will be displayed.
		 */
		return apply_filters( "tec_views_v2_single_subscribe_links_{$slug}_label", $this->single_label, $this, $view );
	}

	/**
	 * Fetches the slug of this particular instance of the Link.
	 *
	 * @since 5.12.0
	 *
	 * @return string
	 */
	public static function get_slug() {
		return static::$slug;
	}

	/**
	 * {@inheritDoc}
	 */
	public function set_visibility( bool $visible ) {
		$this->display = $visible;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_uri( View $view = null ) {
		// If we're on a Single Event view, let's bypass the canonical function call and logic.
		$feed_url = null === $view ? tribe_get_single_ical_link() : $view->get_context()->get( 'single_ical_link', false );

		if ( empty( $feed_url ) && null !== $view ) {
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
	 * fresh future events.
	 *
	 * The URL generated is also inert to the Permalink and Rewrite Rule settings
	 * in WordPress, so it will work out of the box on any website, even if
	 * the settings are changed or break.
	 *
	 * @param View $view The View we're being called from.
	 *
	 * @return string The iCal Feed URI.
	 */
	protected function get_canonical_ics_feed_url( View $view = null ) {
		if ( null === $view ) {
			return '';
		}

		$view_url_args = $view->get_url_args();

		// Some date magic.
		if ( isset( $view_url_args['eventDate'] ) ) {
			// Subscribe from the calendar date (pagination, shortcode calendars, etc).
			$view_url_args['tribe-bar-date'] = $view_url_args['eventDate'];
		} else {
			// Subscribe from today (default calendar view).
			$view_url_args['tribe-bar-date'] = Dates::build_date_object()->format( Dates::DBDATEFORMAT );
		}


		// Clean query params to only contain canonical arguments.
		$canonical_args = [ 'post_type', 'tribe-bar-date', 'tribe_events_cat', 'post_tag' ];

		/**
		 * Allows other plugins to alter what gets passed to the subscribe link.
		 *
		 * @since 5.12.0
		 *
		 * @param array<string> $canonical_args A list of "passthrough" argument keys.
		 * @param View|null     $view           The View we're being called from.
		 *
		 * @return array<string> $canonical_args The modified list of "passthrough" argument keys.
		 */
		$canonical_args = apply_filters( 'tec_views_v2_subscribe_links_canonical_args', $canonical_args, $view );

		// This array will become the args we pass to `add_query_arg()`
		$passthrough_args = [];

		foreach ( $view_url_args as $arg => $value ) {
			if ( in_array( $arg, $canonical_args, true ) ) {
				$passthrough_args[ $arg ] = $view_url_args[ $arg ];
			}
		}

		// iCalendarize!
		$passthrough_args['ical'] = 1;

		// Tidy.
		$passthrough_args = array_filter( $passthrough_args );

		/**
		 * Allows other plugins to alter the query args that get passed to the subscribe link.
		 *
		 * @since 5.12.0
		 *
		 * @param array<string|mixed> $passthrough_args The arguments used to build the ical links.
		 * @param array<string>       $canonical_args   A list of allowed argument keys.
		 * @param View                $view             The View we're being called from.
		 *
		 * @return array<string|mixed>        $passthrough_args The modified list of arguments used to build the ical links.
		 */
		$passthrough_args = apply_filters( 'tec_views_v2_subscribe_links_url_args', $passthrough_args, $view );

		return add_query_arg( urlencode_deep( $passthrough_args ), home_url( '/' ) );
	}
}
