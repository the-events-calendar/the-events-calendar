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
	 * Generate the parameters for the Outlook export buttons.
	 *
	 * @since 5.16.0
	 *
	 * @param string $calendar Whether it's Outlook live or Outlook 365.
	 *
	 * @return string Part of the URL containing the event information.
	 */
	protected function generate_outlook_add_url_parameters( $calendar = 'live' ) {
		// Getting the event details
		$event = tribe_get_event();

		$path = '/calendar/action/compose';
		$rrv  = 'addevent';

		/**
		 * If event is an all day event, then adjust the end time.
		 * Using the 'allday' parameter doesn't work well through time zones.
		 */
		if ( $event->all_day ) {
			$enddt = Dates::build_date_object( $event->end_date )->format( 'Y-m-d' )
				. 'T'
				. Dates::build_date_object( $event->start_date )->format( 'H:i:s' );
		} else {
			$enddt = Dates::build_date_object( $event->end_date )->format( 'c' );
			$enddt = substr( $enddt, 0, strlen( $enddt ) - 6 );
		}

		$startdt = Dates::build_date_object( $event->start_date )->format( 'c' );
		$startdt = substr( $startdt, 0, strlen( $startdt ) - 6 );

		$location = Venue::generate_string_address( $event );

		$subject = $this->space_replace_and_encode( strip_tags( $event->post_title ) );

		/**
		 * A filter to hide or show the event description.
		 *
		 * @since 5.16.0
		 *
		 * @param bool $include_event_description Whether to include the event description or not.
		 */
		$include_event_description = (bool) apply_filters( 'tec_events_ical_outlook_include_event_description', true );

		if ( $include_event_description ) {
			$body = $event->post_content;

			// Stripping tags
			$body = strip_tags( $body, '<p>' );

			// Truncate Event Description and add permalink if greater than 900 characters
			if ( strlen( $body ) > 900 ) {

				$body = substr( $body, 0, 900 );

				$event_url = get_permalink( $event->ID );

				//Only add the permalink if it's shorter than 900 characters, so we don't exceed the browser's URL limits (~2000)
				if ( strlen( $event_url ) < 900 ) {
					$body .= ' ' . sprintf( esc_html__( '(View Full %1$s Description Here: %2$s)', 'the-events-calendar' ), tribe_get_event_label_singular(), $event_url );
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

			// Encoding and trimming
			if ( (int) $num_words > 0 ) {
				$body = wp_trim_words( $body, $num_words );
			}

			// Changing the spaces to %20, Outlook can take that.
			$body = $this->space_replace_and_encode( $body );
		} else {
			$body = false;
		}

		$params = [
			'path'     => $path,
			'rrv'      => $rrv,
			'startdt'  => $startdt,
			'enddt'    => $enddt,
			'location' => $location,
			'subject'  => $subject,
			'body'     => $body,
		];

		return $params;
	}

	/**
	 * Generate the single event "Add to calendar" URL.
	 *
	 * @since 5.16.0
	 *
	 * @return string The singe event add to calendar URL.
	 */
	public function generate_outlook_full_url() {
		$params   = $this->generate_outlook_add_url_parameters();
		$base_url = 'https://outlook.' . static::$calendar_slug . '.com/owa/';
		$url      = add_query_arg( $params, $base_url );

		/**
		 * Filter the Outlook single event import url.
		 *
		 * @since 5.16.0
		 *
		 * @param string               $url      The url used to subscribe to a calendar in Outlook.
		 * @param string               $base_url The base url used to subscribe in Outlook.
		 * @param array<string|string> $params   An array of parameters added to the base url.
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
	 * @return string The subscribe url.
	 */
	public function generate_outlook_subscribe_url( View $view = null ) {
		$base_url = 'https://outlook.' . static::$calendar_slug . '.com/owa?path=/calendar/action/compose';

		if ( null !== $view ) {
			$feed_url = $this->get_canonical_ics_feed_url( $view );
		}

		// Remove ical query and add back after urlencoding the rest of the url.
		$feed_url = remove_query_arg( 'ical', $feed_url );

		$feed_url = str_replace( [ 'http://', 'https://' ], 'webcal://', $feed_url );

		$feed_url = urlencode( $feed_url );

		$feed_url = $feed_url . '&ical=1';

		$params = [
			'rru'  => 'addsubscription',
			'url'  => urlencode( $feed_url ),
			'name' => urlencode( get_bloginfo( 'name' ) . ' ' . $view->get_title() ),
		];

		$url = add_query_arg( $params, $base_url );

		/**
		 * Filter the Outlook subscribe url.
		 *
		 * @since 5.16.0
		 *
		 * @param string                  $url      The url used to subscribe to a calendar in Outlook.
		 * @param string                  $base_url The base url used to subscribe in Outlook.
		 * @param string                  $feed_url The subscribe url used on the site.
		 * @param array<string|string>    $params   An array of parameters added to the base url.
		 * @param Outlook_Abstract_Export $this     An instance of the link abstract.
		 */
		$url = apply_filters( 'tec_events_ical_outlook_subscribe_url', $url, $base_url, $feed_url, $params, $this );

		return $url;
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
}
