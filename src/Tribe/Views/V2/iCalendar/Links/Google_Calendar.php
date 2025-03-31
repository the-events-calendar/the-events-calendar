<?php
/**
 * Handles Google Calendar export/subscribe links.
 *
 * @since   5.12.0
 *
 * @package Tribe\Events\Views\V2\iCalendar
 */

namespace Tribe\Events\Views\V2\iCalendar\Links;

use Tribe\Events\Views\V2\View;
use Tribe__Events__Main;
use Tribe__Timezones;
use Tribe__Events__Timezones;
use WP_Post;

/**
 * Class Google_Calendar
 *
 * @since   5.12.0
 *
 * @package Tribe\Events\Views\V2\iCalendar
 */
class Google_Calendar extends Link_Abstract {
	/**
	 * {@inheritDoc}
	 *
	 * @since 5.12.0
	 *
	 * @var string
	 */
	public static $slug = 'gcal';

	/**
	 * {@inheritDoc}
	 *
	 * @since 5.12.0
	 *
	 * @var string
	 */
	public $block_slug = 'hasGoogleCalendar';

	/**
	 * {@inheritDoc}
	 */
	public function register() {
		// intentionally left blank.
	}

	/**
	 * {@inheritDoc}
	 */
	protected function label(): string {
		return __( 'Google Calendar', 'the-events-calendar' );
	}

	/**
	 * {@inheritDoc}
	 */
	protected function single_label(): string {
		return __( 'Add to Google Calendar', 'the-events-calendar' );
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since 5.12.0
	 *
	 * @param View|null $view The view object.
	 */
	public function get_uri( View $view = null ) {
		if ( null === $view || is_singular( Tribe__Events__Main::POSTTYPE ) ) {
			// Try to construct it for the event single.

			/**
			 * Allows "turning off" the single event link for Google Calendar.
			 *
			 * @since 5.14.0
			 *
			 * @param boolean $use_single_url Use the single event url for single event views. Default true.
			 */
			$use_single_url = apply_filters( 'tec_views_v2_subscribe_links_gcal_single_url', true );

			if ( $use_single_url ) {
				return $this->generate_single_url();
			}
		}

		/**
		 * Filters the Google Calendar feed URL.
		 *
		 * @since 6.11.0
		 *
		 * @param string $feed_url The Google Calendar feed URL.
		 * @param View   $view The view.
		 */
		$feed_url = (string) apply_filters( 'tec_views_v2_subscribe_links_gcal_feed_url', parent::get_uri( $view ), $view );

		return add_query_arg(
			[ 'cid' => urlencode( $feed_url ) ],
			'https://www.google.com/calendar/render?cid='
		);
	}

	/**
	 * Generate a link that will import a single event into Google Calendar.
	 *
	 * Required link items:
	 *     action=TEMPLATE
	 *     text=[the title of the event]
	 *     dates= in YYYYMMDDHHMMSS format. start datetime / end datetime
	 *
	 * Optional link items:
	 *     ctz=[time zone]
	 *     details=[event details]
	 *     location=[event location]
	 *
	 * URL format: https://www.google.com/calendar/render?action=TEMPLATE&text=Title&dates=20190227/20190228
	 *
	 * @since 5.14.0
	 *
	 * @param string|int|WP_post $post The ID or post object the rui is for, defaults to the current post.
	 *
	 * @return string                  URL string. Empty string if post not found or post is not an event.
	 */
	public function generate_single_url( $post = null ) {
		if ( empty( $post ) ) {
			$post = get_the_ID();
		}

		$event = tribe_get_event( $post );

		if ( empty( $event ) || ! tribe_is_event( $event ) ) {
			return '';
		}

		$base_url = 'https://www.google.com/calendar/event';

		/**
		 * Allow users to Filter our Google Calendar Link base URL before constructing the URL.
		 * After this filter, the list will be trimmed to remove any empty values and discarded if any required params are missing.
		 * Returning an empty/falsy value here will short-circuit the function to bail out now with an empty string.
		 *
		 * @since 5.14.0
		 *
		 * @var array   $base_url The base url used in the add_query_arg.
		 * @var WP_Post $event    The Event the link is for. As decorated by tribe_get_event().
		 */
		$base_url = apply_filters( 'tec_views_v2_single_event_gcal_link_base_url', $base_url, $event );

		if ( empty( $base_url ) ) {
			return '';
		}

		$event_details = '';
		if ( ! empty( $event->description ) ) {
			$event_details = $event->description;
		} elseif ( ! empty( $event->post_content ) ) {
			$event_details = $event->post_content;
		}

		// Removes any Elementor comments from the event description before we try to process it.
		$re            = '/(<!-- .* \/?-->)/m';
		$event_details = preg_replace( $re, '', $event_details );

		if ( ! empty( $event_details ) ) {
			// Truncate Event Description and add permalink if greater than 996 characters.
			$event_details = $this->format_event_details_for_url( $event_details, $event, 996 );
		}

		// Moved to after we do any tag shenanigans - otherwise one or both are meaningless.
		$event_details = urlencode( $event_details );

		if ( Tribe__Timezones::is_mode( Tribe__Timezones::SITE_TIMEZONE ) ) {
			$ctz = Tribe__Timezones::build_timezone_object()->getName();
		} else {
			$ctz = Tribe__Events__Timezones::get_event_timezone_string( $event->ID );
		}

		$pieces = [
			'action'   => 'TEMPLATE',
			'dates'    => $event->dates->start->format( 'Ymd\THis' ) . '/' . $event->dates->end->format( 'Ymd\THis' ),
			'text'     => rawurlencode( get_the_title( $event ) ),
			'details'  => $event_details,
			'location' => self::generate_string_address( $event ),
			'trp'      => 'false',
			'ctz'      => $ctz,
			'sprop'    => 'website:' . home_url(),
		];

		/**
		 * Allow users to Filter our Google Calendar Link params
		 *
		 * @deprecated 5.14.0 Moved generic hook to something more specific and appropriate.
		 *
		 * @var array Params used in the add_query_arg
		 * @var int   Event ID
		 */
		$pieces = apply_filters_deprecated(
			'tribe_google_calendar_parameters',
			[ $pieces, $event->ID ],
			'5.14.0',
			'tec_views_v2_single_event_gcal_link_parameters',
			'Moved generic hook to something more specific and appropriate while moving function.'
		);

		/**
		 * Allow users to Filter our Google Calendar Link params before constructing the URL.
		 * After this filter, the list will be trimmed to remove any empty values and discarded if any required params are missing.
		 *
		 * @since 5.14.0
		 *
		 * @var array   $pieces   The params used in the add_query_arg.
		 * @var WP_Post $event    The Event the link is for. As decorated by tribe_get_event().
		 */
		$pieces = apply_filters( 'tec_views_v2_single_event_gcal_link_parameters', $pieces, $event );

		$pieces = array_filter( $pieces );

		// Missing required info - bail.
		if ( empty( $pieces['action'] ) || empty( $pieces['dates'] ) || empty( $pieces['text'] ) ) {
			return '';
		}

		$url = add_query_arg( $pieces, $base_url );

		/**
		 * Allow users to Filter our Google Calendar Link URL - after all params have been applied to the URL.
		 *
		 * @since 5.14.0
		 *
		 * @var array   $url   The url to use.
		 * @var WP_Post $event The Event the link is for. As decorated by tribe_get_event().
		 */
		return apply_filters( 'tec_views_v2_single_gcal_subscribe_link', $url, $event );
	}

	/**
	 * Truncate Event Description and add permalink if greater than $length characters.
	 *
	 * @since 5.14.0
	 *
	 * @param string      $event_details The event description.
	 * @param WP_Post|int $post          The event post or ID.
	 * @param int         $length        The max length for the description before adding a "read more" link.
	 *
	 * @return string The possibly modified event description.
	 */
	public function format_event_details_for_url( $event_details, $post, int $length = 0 ) {
		// Hack: Add space after paragraph
		// Normally Google Cal understands the newline character %0a
		// And that character will automatically replace newlines on urlencode().
		$event_details = str_replace( '</p>', '</p> ', $event_details );

		if ( strlen( $event_details ) <= 996 ) {
			return $event_details;
		}

		$event_details = substr( $event_details, 0, 996 );
		$event_details = force_balance_tags( $event_details ); // Ensure we don't have any unclosed tags.
		$event_url     = get_permalink( $post );

		// Only add the permalink if it's shorter than 900 characters, so we don't exceed the browser's URL limits.
		if ( strlen( $event_url ) > 900 ) {
			return $event_details;
		}

		// Append the "read more" link.
		$event_details .= sprintf(
			/* Translators: %1$s: Event singular label. %2$s: Event URL. */
			esc_html_x( ' (View Full %1$s Description Here: %2$s)', 'Link to full description. %1$s: pre=translated event term. %2$s: event url.', 'the-events-calendar' ),
			tribe_get_event_label_singular_lowercase(),
			$event_url
		);

		return $event_details;
	}

	/**
	 *  Returns a string version of the full address of an event.
	 *
	 * @since 5.14.0
	 *
	 * @todo This should really live in Tribe__Events__Venue, so move it there at some point
	 * @see Tribe__Events__Main->fullAddressString()
	 *
	 * @param int|WP_Post|null $event The post object or post id.
	 *
	 * @return string The event venue's address. Empty string if the event or venue isn't found.
	 */
	public static function generate_string_address( $event = null ) {
		if ( empty( $event ) ) {
			$event = get_the_ID();
		}

		$event = tribe_get_event( $event );

		// Not an event? Bail.
		if ( ! tribe_is_event( $event ) ) {
			return '';
		}

		if ( ! tribe_has_venue( $event ) ) {
			return '';
		}

		// The below includes the venue name.
		return \Tribe__Events__Venue::get_address_full_string( $event );
	}
}
