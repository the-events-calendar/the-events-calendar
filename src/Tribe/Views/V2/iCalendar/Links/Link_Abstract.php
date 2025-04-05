<?php
/**
 * The base implementation for the Views v2 query controllers.
 *
 * @since   5.12.0
 * @package Tribe\Events\Views\V2\iCalendar
 */

namespace Tribe\Events\Views\V2\iCalendar\Links;

use JsonSerializable;
use Tribe__Date_Utils as Dates;
use Tribe\Events\Views\V2\View;


/**
 * Class Abstract_Link
 *
 * @since   5.12.0
 * @package Tribe\Events\Views\V2\iCalendar
 */
abstract class Link_Abstract implements Link_Interface, JsonSerializable {

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
	public $visible = true;

	/**
	 * The link provider slug.
	 *
	 * @since 5.12.0
	 *
	 * @var string
	 */
	public static $slug;

	/**
	 * The slug used for the single event sharing block toggle.
	 *
	 * @since 5.16.1
	 *
	 * @var string
	 */
	public $block_slug;

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

		$this->set_hooked();
	}

	/**
	 * {@inheritDoc}
	 */
	public function filter_tec_views_v2_subscribe_links( $subscribe_links ) {
		// Bail early if we're not supposed to show this link.
		if ( ! $this->is_visible() ) {
			return $subscribe_links;
		}

		$subscribe_links[ self::get_slug() ] = $this;

		return $subscribe_links;
	}

	/**
	 * {@inheritDoc}
	 */
	public function filter_tec_views_v2_single_subscribe_links( $links ) {
		// Bail early if we're not supposed to show this link.
		if ( ! $this->is_visible() ) {
			return $links;
		}

		$label = $this->get_single_label( null );
		$uri   = $this->get_uri( null );

		// Don't add invalid or "invisible" links.
		if ( empty( $label ) || empty( $uri ) ) {
			return $links;
		}

		$class                     = sanitize_html_class( 'tribe-events-' . self::get_slug() );
		$links[ self::get_slug() ] = sprintf(
			'<a class="tribe-events-button %1$s" href="%2$s" title="%3$s"  rel="noopener noreferrer noindex">%4$s</a>',
			$class,
			esc_url( $uri ),
			esc_attr( $label ),
			esc_html( $label )
		);

		return $links;
	}

	/**
	 * {@inheritDoc}
	 */
	public function is_visible() {
		$visible = $this->visible;

		/**
		 * Allows filtering the visibility of the Subscribe to Calendar and Add to Calendar links.
		 *
		 * @since 5.14.0
		 *
		 * @param boolean $visible Whether to display the link.
		 *
		 * @return boolean $visible Whether to display the link.
		 */
		$visible = (boolean) apply_filters( 'tec_views_v2_subscribe_link_visibility', $visible );

		/**
		 * Allows link-specific filtering of the visibility of the Subscribe to Calendar and Add to Calendar links.
		 * `self::get_slug()` is the slug of the particular instance of the Link.
		 * Accepted values:
		 * - Google Calendar: gcal
		 * - iCalendar: ical
		 * - Outlook 365: outlook-365
		 * - Outlook Live: outlook-live
		 * - Export .ics file: ics
		 * - Export Outlook .ics file: outlook-ics
		 *
		 * @since 5.14.0
		 *
		 * @param boolean $visible Whether to display the link.
		 *
		 * @return boolean $visible Whether to display the link.
		 */
		$visible = (boolean) apply_filters( 'tec_views_v2_subscribe_link_' . self::get_slug() . '_visibility', $visible );

		// Set the object property to the filtered value.
		$this->set_visibility( $visible );

		// Return
		return $visible;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_label( View $view = null ): string {
		return $this->filter_get_label( $this->label(), $view );
	}

	/**
	 * Returns the label for the link.
	 *
	 * @since 6.8.2.1
	 *
	 * @return string
	 */
	abstract protected function label(): string;

	/**
	 * Filters the label for the link.
	 *
	 * @since 6.8.2.1
	 *
	 * @param string    $value The label to filter.
	 * @param View|null $view  The current View object.
	 *
	 * @return string
	 */
	protected function filter_get_label( string $value, View $view = null ): string {
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
		return (string) apply_filters( "tec_views_v2_subscribe_links_{$slug}_label", $value, $this, $view );
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_single_label( View $view = null ): string {
		return $this->filter_get_single_label( $this->single_label(), $view );
	}

	/**
	 * Returns the label for the single event view.
	 *
	 * @since 6.8.2.1
	 *
	 * @return string
	 */
	abstract protected function single_label(): string;

	/**
	 * Filters the single label for the link.
	 *
	 * @since 6.8.2.1
	 *
	 * @param string    $value The label to filter.
	 * @param View|null $view  The current View object.
	 *
	 * @return string
	 */
	protected function filter_get_single_label( string $value, View $view = null ): string {
		$slug = self::get_slug();

		/**
		 * Allows filtering of the labels for the Single Event view labels.
		 *
		 * @since 5.12.0
		 *
		 * @param string        $single_label The label that will be displayed.
		 * @param Link_Abstract $link_obj     The link object the label is for.
		 * @param View          $view         The current View object.
		 *
		 * @return string $label The label that will be displayed.
		 */
		return (string) apply_filters( "tec_views_v2_single_subscribe_links_{$slug}_label", $value, $this, $view );
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
		$this->visible = $visible;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_uri( View $view = null ) {
		// If we're on a Single Event view, let's bypass the canonical function call and logic.
		if ( is_single() ) {
			$feed_url = null === $view ? tribe_get_single_ical_link() : $view->get_context()->get( 'single_ical_link', false );
		}

		if ( empty( $feed_url ) && null !== $view ) {
			$feed_url = $this->get_canonical_ics_feed_url( $view );
		}

		if ( empty( $feed_url ) ) {
			return '';
		}

		$feed_url = str_replace( [ 'http://', 'https://' ], 'webcal://', $feed_url );

		/**
		 * Filters the feed URL for the subscribe link.
		 *
		 * @since 6.11.0
		 *
		 * @param string $feed_url The feed URL.
		 * @param View   $view The view.
		 */
		return (string) apply_filters( 'tec_views_v2_subscribe_links_feed_url', $feed_url, $view );
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

		// Allow all views to utilize the list view so they collect the appropriate number of events.
		// Note: this is only applied to subscription links - the ics direct link downloads what you see on the page!
		$passthrough_args["eventDisplay"] = \Tribe\Events\Views\V2\Views\List_View::get_view_slug();

		// Tidy (remove empty-value pairs).
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

	/**
	 * Magic method to allow getting the label and single_label properties.
	 * These two params are deprecated and will be removed in a future release.
	 *
	 * @since 6.8.2.1
	 *
	 * @param string $name The property name.
	 *
	 * @return string|null
	 */
	public function __get( $name ) {
		if ( 'label' === $name ) {
			_doing_it_wrong( __METHOD__, 'The `label` property is deprecated and will be removed in a future release.', '6.8.2.1' );
			return $this->get_label();
		}

		if ( 'single_label' === $name ) {
			_doing_it_wrong( __METHOD__, 'The `single_label` property is deprecated and will be removed in a future release.', '6.8.2.1' );
			return $this->get_single_label();
		}

		return null;
	}

	/**
	 * Magic method surrounding the JSON serialization to enable the object to be serialized with all props.
	 *
	 * @since 6.8.2.1
	 *
	 * @return array
	 */
	#[\ReturnTypeWillChange]
	public function jsonSerialize() {
		return [
			'label'        => $this->get_label(),
			'single_label' => $this->get_single_label(),
			'visible'      => $this->is_visible(),
			'block_slug'   => $this->block_slug,
		];
	}
}
