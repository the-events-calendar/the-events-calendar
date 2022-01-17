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
	 */
	public static $slug = 'gcal';

	/**
	 * {@inheritDoc}
	 */
	public function register() {
		$this->label = __( 'Google Calendar', 'the-events-calendar' );
		$this->single_label = __( 'Add to Google Calendar', 'the-events-calendar' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_uri( View $view = null ) {
		if ( null === $view || is_single() ) {
			// Try to construct it for the event single.
			return $this->generate_single_uri( $view );
		}

		$feed_url = parent::get_uri( $view );

		return add_query_arg(
			[ 'cid' => urlencode( $feed_url ) ],
			'https://www.google.com/calendar/render?cid='
		);
	}

	/**
	 * Generate a link that will import a single event into Google Calendar.
	 *
	 *	Required link items:
     *	action=TEMPLATE
     *	text=[the title of the event]
     *	dates= in YYYYMMDDHHMMSS format. start datetime / end datetime
	 *
	 *	Optional link items:
     *	ctz=[time zone]
     *	details=[event details]
     *	location=[event location]
	 *
	 * URL format: https://www.google.com/calendar/render?action=TEMPLATE&text=Title&dates=20190227/20190228
	 *
	 * @since TBD
	 *
	 * @param string|int|WP_post $post The ID or post object the rui is for, defaults to the current post.
	 *
	 * @return string                  URL string. Empty string if post not found or post is not an event.
	 */
	public function generate_single_uri( $post = null ) {
		if ( empty( $post ) ) {
			$post = get_the_ID();
		}

		$event = tribe_get_event( $post );

		if ( empty( $event ) || ! tribe_is_event( $event ) ) {
			return '';
		}

		$base_url =  'https://www.google.com/calendar/r/eventedit';
		$pieces   = [
			'action'   => 'TEMPLATE',
     		'dates'    => $event->dates->start_utc->format( 'Ymd\THis\Z' ) . '/' . $event->dates->end_utc->format( 'Ymd\THis\Z' ),
     		'text'     => urlencode( get_the_title( $event ) ),
			'details'  => empty( $event->description ) ? urlencode( $event->description ) : 'Test Event Description',
			'location' => self::generate_string_address( $event ),
			'ctz'      => $event->dates->start->format( 'e' ),
		];

		$pieces = array_filter( $pieces );

		$uri = add_query_arg( $pieces, $base_url );

		return apply_filters( 'tec_views_v2_single_gcal_subscribe_link', $uri, $event );

	}

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

		$tec     = Tribe__Events__Main::instance();
		$address = $tec->fullAddressString( $event );
		// The above includes the venue name,

		return $address;
	}
}
