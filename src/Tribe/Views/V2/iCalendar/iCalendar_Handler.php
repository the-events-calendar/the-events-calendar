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
use Tribe\Events\Views\V2\iCalendar\Links\Link_Abstract;
use Tribe\Events\Views\V2\iCalendar\Links\Outlook_365;
use Tribe\Events\Views\V2\iCalendar\Links\Outlook_Export;
use Tribe\Events\Views\V2\iCalendar\Links\Outlook_Live;
use Tribe\Events\Views\V2\View;
use TEC\Common\Contracts\Service_Provider;


/**
 * Class iCalendar_Handler
 *
 * @since   5.12.0
 *
 * @package Tribe\Events\Views\V2\iCalendar
 */
class iCalendar_Handler extends Service_Provider {

	/**
	 * Which classes we will load for links by default.
	 *
	 * @since 5.12.0
	 *
	 * @var string[]
	 */
	protected $default_feeds = [
		Google_Calendar::class,
		iCal::class,
		Outlook_365::class,
		Outlook_Live::class,
		iCalendar_Export::class,
		Outlook_Export::class,
	];

	/**
	 * Which classes we will load for links by default.
	 *
	 * @since 5.12.3
	 *
	 * @var Link_Abstract[]
	 */
	protected $feeds = [];

	/**
	 * Register singletons and main hook.
	 *
	 * @since 5.12.0
	 */
	public function register() {
		if ( ! $this->use_subscribe_links() ) {
			return;
		}

		foreach ( $this->default_feeds as $feed_class ) {
			// Register as a singleton for internal ease of use.
			$this->container->singleton( $feed_class, $feed_class );
			// Ensure all $feed_class hooks are set up now.
			tribe( $feed_class )->hook();
		}

		$this->container->singleton( static::class, $this );

		$this->register_hooks();
	}

	/**
	 * Register all our hooks here.
	 *
	 * @since 5.12.0
	 */
	public function register_hooks() {
		add_action( 'tribe_events_views_v2_before_make_view', [ $this, 'get_feeds' ] );
		add_action( 'send_headers', [ $this, 'ical_header_properties' ] );

		add_filter( 'tribe_events_views_v2_view_template_vars', [ $this, 'filter_template_vars' ], 10, 2 );
		add_filter( 'tribe_events_ical_single_event_links', [ $this, 'single_event_links' ], 20 );
		add_filter( 'tribe_ical_properties', [ $this, 'ical_properties' ] );
		add_filter( 'tribe_template_context:events/blocks/event-links', [ $this, 'filter_template_context' ], 10, 4 );
	}

	/**
	 * Allow toggling off the new subscribe link list via a hook.
	 *
	 * @since 5.12.0
	 *
	 * @return boolean Whether to use the new subscribe link list.
	 */
	public function use_subscribe_links() {
		/**
		 * Determines if Subscribe links should be enabled.
		 *
		 * @since 5.12.0
		 *
		 * @param bool $use_subscribe_links Should we use subscribe links.
		 */
		return apply_filters( 'tec_views_v2_use_subscribe_links', true );
	}

	/**
	 * Initializes, sets the internal feeds array and returns it.
	 *
	 * @since 5.12.3
	 *
	 * @return array
	 */
	public function get_feeds() {
		if ( empty( $this->feeds ) ) {
			$this->feeds = array_map( static function ( $feed_class ) {
				return tribe( $feed_class );
			}, $this->default_feeds );
		}

		return $this->feeds;
	}

	public function filter_template_context( $context, $file, $name, $template ) {
		$context['subscribe_links'] = $this->get_subscribe_links();

		return $context;
	}

	/**
	 * Add iCal feed link labels and URIs to the global template vars.
	 *
	 * Usable in ical-link.php via the $subscribe_links global.
	 *
	 * @see   `tribe_events_views_v2_view_template_vars` filter.
	 *
	 * @since 5.12.0
	 *
	 * @param array<string,mixed> $template_vars The View template variables.
	 * @param View|null           $view          The View implementation.
	 *
	 * @return array The filtered template variables.
	 */
	public function filter_template_vars( array $template_vars, View $view = null ) {
		// Set up the section of the $template vars for the links.
		$template_vars['subscribe_links'] = $this->get_subscribe_links( $view );

		return $template_vars;
	}

	/**
	 * Builds the subscribe links in a separate process.
	 *
	 * @since 5.12.0
	 *
	 * @param View|null $view
	 *
	 * @return array
	 */
	public function get_subscribe_links( View $view = null ) {
		// Set up the list of links.
		$subscribe_links = [];

		/**
		 * Allows each link type to dynamically add itself to the list for Calendar views.
		 *
		 * @since 5.12.0
		 *
		 * @param array<string|object> $subscribe_links The array of links.
		 * @param View|null            $view            The View implementation.
		 */
		return apply_filters( 'tec_views_v2_subscribe_links', $subscribe_links, $view );
	}

	/**
	 * Replace (overwrite) the default single event links with subscription links.
	 *
	 * @see   `tribe_events_ical_single_event_links` filter.
	 *
	 * @since 5.12.0
	 * @since 5.16.0 - Move to Single_Events class to handle Outlook.
	 *
	 * @param string $calendar_links The link content.
	 *
	 * @return string The altered link content.
	 */
	public function single_event_links( $calendar_links ) {
		// If someone has opted out of the new subscribe links - don't change anything!
		if ( ! $this->use_subscribe_links() ) {
			return $calendar_links;
		}

		$subscribe_links = $this->get_subscribe_links();

		return $this->container->make( Single_Events::class )->single_event_links( $calendar_links, $subscribe_links );
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
	 * X-Robots-Tag (keep robots from indexing the downloads pages):
	 * https://developers.google.com/search/docs/advanced/crawling/block-indexing
	 *
	 * Note: PT1H means Once per hour.
	 *
	 * @see   `tribe_ical_properties` filter.
	 *
	 * @since 5.12.0
	 *
	 * @param string $content The iCal content.
	 *
	 * @return string The filtered content.
	 */
	public function ical_properties( $content ) {
		$content .= "REFRESH-INTERVAL;VALUE=DURATION:PT1H\r\n";
		$content .= "X-Robots-Tag:noindex\r\n";
		$content .= "X-PUBLISHED-TTL:PT1H\r\n";

		return $content;
	}

	/**
	 * Add x-robots tag to response headers for ical download links.
	 *
	 * @since 6.2.6
	 */
	public function ical_header_properties() {
		/**
		 * Allows short-circuiting the logic to prevent the x-robots tag from being added to the response headers.
		 *
		 * @param bool $add If the x-robots tag should be added to the response headers.
		 */
		$add = (bool) apply_filters( 'tec_events_ical_header_noindex', true );

		if ( ! $add ) {
			return;
		}

		if ( ! tribe_get_request_var( 'ical' ) && ! tribe_get_request_var( 'outlook-ical' ) ) {
			return;
		}

		header( 'X-Robots-Tag:noindex, nofollow' );
	}
}
