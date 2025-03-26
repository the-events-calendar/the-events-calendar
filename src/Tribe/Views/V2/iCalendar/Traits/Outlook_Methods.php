<?php
/**
 * Handles Outlook Methods Trait export/subscribe links.
 *
 * @since   5.16.0
 *
 * @package Tribe\Events\Views\V2\iCalendar
 */

namespace Tribe\Events\Views\V2\iCalendar\Traits;

use Tribe\Events\Views\V2\View as View;
use Tribe__Date_Utils as Dates;
use Tribe__Events__Venue as Venue;
use Tribe__Timezones;

/**
 * Class Outlook_Methods
 *
 * @since   5.16.0
 *
 * @package Tribe\Events\Views\V2\iCalendar
 */
trait Outlook_Methods {

	/**
	 * Space replacement used to in Outlook link.
	 *
	 * @since 5.16.0
	 *
	 * @var string
	 */
	protected static $outlook_space = '%20';

	/**
	 * Temporary space replacement used to to urlencode an Outlook link.
	 *
	 * @since 5.16.0
	 *
	 * @var string
	 */
	protected static $outlook_temp_space = 'TEC_OUTLOOK_SPACE';

	/**
	 * Holds the slug for the service we're sending to (i.e. live or office).
	 * Used in the outlook URLs.
	 *
	 * @since 5.16.0
	 * @since TBD Renamed from $calendar_slug to $service_slug. Protected, moved into the trait from the classes.
	 *
	 * @var string
	 */
	protected static $service_slug;

	/**
	 * Generate the parameters for the Outlook export buttons.
	 *
	 * @since 5.16.0
	 * @since 6.3.6 Adding timezone to the start and end dates generated.
	 *
	 * @param string $calendar Whether it's Outlook live or Outlook 365.
	 *
	 * @return array<string,string> Params for the URL containing the event information.
	 */
	protected function generate_outlook_add_url_parameters( $calendar = 'live' ) {
		// Getting the event details.
		$event = tribe_get_event();
		if ( ! $event ) {
			return [];
		}

		$path     = '/calendar/action/compose';
		$rrv      = 'addevent';
		$timezone = $event->timezone ?? Tribe__Timezones::wp_timezone_string();

		/**
		 * If event is an all day event, then adjust the end time.
		 * Using the 'allday' parameter doesn't work well through time zones.
		 */
		if ( $event->all_day ) {
			$enddt = urlencode( Dates::build_date_object( $event->end_date, $timezone )->format( 'Y-m-d' ) . 'T' . Dates::build_date_object( $event->start_date, $timezone )->format( 'H:i:s' ) );
		} else {
			$enddt = urlencode( Dates::build_date_object( $event->end_date, $timezone )->format( 'c' ) );
		}

		$startdt = urlencode( Dates::build_date_object( $event->start_date, $timezone )->format( 'c' ) );

		$params = [
			'path'     => '/calendar/action/compose',
			'rrv'      => 'addevent',
			'startdt'  => $start_datetime,
			'enddt'    => $end_datetime,
			'location' => Venue::generate_string_address( $event ),
			'subject'  => $this->space_replace_and_encode( wp_strip_all_tags( $event->post_title ) ),
			'body'     => $this->generate_outlook_event_description( $event ),
		];

		/**
		 * Allow users to filter the params for our Outlook links before constructing the URL.
		 *
		 * @since TBD
		 *
		 * @var array    $params   The params used in the add_query_arg.
		 * @var /WP_Post $event    The Event the link is for. As decorated by tribe_get_event().
		 * @var string   $calendar The slug of the calendar. Values can be "outlook-365" and "outlook-live".
		 */
		$params = apply_filters( 'tec_events_single_event_outlook_link_parameters', $params, $event, $calendar );

		$service_slug = static::get_service_slug();

		/**
		 * Allow users to filter the params for a specific Outlook link before constructing the URL.
		 *
		 * @since TBD
		 *
		 * @var array    $params   The params used in the add_query_arg.
		 * @var /WP_Post $event    The Event the link is for. As decorated by tribe_get_event().
		 * @var string   $calendar The slug of the calendar. Values can be "outlook-365" and "outlook-live".
		 */
		$params = apply_filters( "tec_events_single_event_outlook_link_parameters_{$service_slug}", $params, $event, $calendar );

		return $params;
	}

	/**
	 * Generate the event description for Outlook.
	 *
	 * @since TBD
	 *
	 * @param /WP_Post $event The Event the link is for. As decorated by tribe_get_event().
	 *
	 * @return string The event description.
	 */
	public function generate_outlook_event_description( $event ) {
		/**
		 * A filter to hide or show the event description.
		 *
		 * @since 5.16.0
		 *
		 * @param bool $include_event_description Whether to include the event description or not.
		 */
		$include_event_description = (bool) apply_filters( 'tec_events_ical_outlook_include_event_description', true );

		if ( ! $include_event_description ) {
			return '';
		}

		$body = $event->post_content;

		// Stripping most tags.
		$body = wp_kses_post( $body );

		/**
		 * Allows filtering the content of the event description.
		 * Note: This happens before space conversion and truncation happens!
		 *
		 * @since TBD
		 *
		 * @param string $body The event description.
		 */
		$body = apply_filters( 'tec_events_ical_outlook_event_description_content', $body );

		// Truncate the Event description and add permalink if greater than 900 characters.
		if ( strlen( $body ) > 900 ) {

			$body = substr( $body, 0, 900 );

			$event_url = get_permalink( $event->ID );

			// Only add the permalink if it's shorter than 900 characters, so we don't exceed the browser's URL limits (~2000).
			if ( strlen( $event_url ) < 900 ) {
				$body .= ' ' . sprintf(
					/* Translators: %1$s singular event label %2$s URL */
					esc_html_x(
						'(View Full %1$s Description Here: %2$s)',
						'Link text to full post description.',
						'the-events-calendar'
					),
					tribe_get_event_label_singular(),
					$event_url
				);
			}
		}

		/**
		 * Allows filtering the length of the event description.
		 *
		 * @since 5.16.0
		 *
		 * @param bool|int $num_words
		 */
		$num_words = apply_filters( 'tec_events_ical_outlook_event_description_num_words', false );

		// Encoding and trimming.
		if ( (int) $num_words > 0 ) {
			$body = wp_trim_words( $body, $num_words );
		}

		// Changing the spaces to %20, Outlook can take that.
		$body = $this->space_replace_and_encode( $body );

		return $body;
	}

	/**
	 * Generate the single event "Add to calendar" URL.
	 *
	 * @since 5.16.0
	 *
	 * @return string The single event add to calendar URL.
	 */
	public function generate_outlook_full_url() {
		$params   = $this->generate_outlook_add_url_parameters();
		$base_url = 'https://outlook.' . static::get_service_slug() . '.com/owa/';
		$url      = add_query_arg( $params, $base_url );

		/**
		 * Filter the Outlook single event import URL.
		 *
		 * @since 5.16.0
		 *
		 * @param string               $url      The URL used to subscribe to a calendar in Outlook.
		 * @param string               $base_url The base URL used to subscribe in Outlook.
		 * @param array<string|string> $params   An array of parameters added to the base URL.
		 * @param Outlook_Methods      $this     An instance of the link abstract.
		 */
		$url = apply_filters( 'tec_events_ical_outlook_single_event_import_url', $url, $base_url, $params, $this );

		return $url;
	}

	/**
	 * Generate the subscribe URL.
	 *
	 * @since 5.16.0
	 *
	 * @return string The subscribe URL.
	 */
	public function generate_outlook_subscribe_url( View $view = null ) {
		$base_url = 'https://outlook.' . static::get_service_slug() . '.com/owa?path=/calendar/action/compose';

		if ( null !== $view ) {
			$feed_url = $this->get_canonical_ics_feed_url( $view );
		}

		$feed_url = str_replace( [ 'http://', 'https://' ], 'webcal://', $feed_url );

		$feed_url = urlencode( $feed_url );

		$params = [
			'rru'  => 'addsubscription',
			'url'  => urlencode( $feed_url ),
			'name' => urlencode( get_bloginfo( 'name' ) . ' ' . $view->get_title() ),
		];

		$url = add_query_arg( $params, $base_url );

		/**
		 * Filter the Outlook subscribe URL.
		 *
		 * @since 5.16.0
		 *
		 * @param string                  $url      The URL used to subscribe to a calendar in Outlook.
		 * @param string                  $base_url The base URL used to subscribe in Outlook.
		 * @param string                  $feed_url The subscribe URL used on the site.
		 * @param array<string|string>    $params   An array of parameters added to the base URL.
		 * @param Outlook_Abstract_Export $this     An instance of the link abstract.
		 */
		$url = apply_filters( 'tec_events_ical_outlook_subscribe_url', $url, $base_url, $feed_url, $params, $this );

		return rawurldecode( $url );
	}

	/**
	 * Changing spaces to %20 and encoding.
	 * urlencode() changes the spaces to +. That is also how Outlook will show it.
	 * So we're replacing it temporarily and then changing them to %20 which will work.
	 *
	 * @since 5.16.0
	 *
	 * @param string $string The URL string.
	 *
	 * @return string The encoded URL string.
	 */
	public function space_replace_and_encode( $string ) {
		$string = str_replace( ' ', static::$outlook_temp_space, $string );
		$string = urlencode( $string );
		$string = str_replace( static::$outlook_temp_space, static::$outlook_space, $string );

		return $string;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_uri( View $view = null ) {
		if ( is_single() ) {
			return $this->generate_outlook_full_url();
		}

		if ( null === $view ) {
			return '';
		}

		return $this->generate_outlook_subscribe_url( $view );
	}

	/**
	 * Public getter for protected service slug property.
	 *
	 * @since TBD
	 *
	 * @return string The service slug.
	 */
	public static function get_service_slug(): string {
		return static::$service_slug;
	}
}
