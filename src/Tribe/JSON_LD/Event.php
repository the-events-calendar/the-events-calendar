<?php

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

/**
 * A JSON-LD class extended of the Abstract that lies on the Common Package
 * Used for generating a Event JSON-LD markup
 */
class Tribe__Events__JSON_LD__Event extends Tribe__JSON_LD__Abstract {

	/**
	 * Which type of element this actually is
	 *
	 * @see https://developers.google.com/structured-data/rich-snippets/
	 * @var string
	 */
	public $type = 'Event';

	/**
	 * On PHP 5.2 the child class doesn't get spawned on the Parent one, so we don't have
	 * access to that information on the other side unless we pass it around as a param
	 * so we throw __CLASS__ to the parent::instance() method to be able to spawn new instance
	 * of this class and save on the parent::$instances variable.
	 *
	 * @return Tribe__Events__JSON_LD__Event
	 */
	public static function instance( $name = null ) {
		return parent::instance( __CLASS__ );
	}

	/**
	 * Converts the start and end dates to the local timezone
	 *
	 * @param  string $date
	 * @param  string $event_tz_string
	 * @return string
	 */
	private function get_localized_iso8601_string( $date, $event_tz_string ) {
		try {
			$timezone = 0 === strpos( $event_tz_string, 'UTC' )
				? Tribe__Timezones::timezone_from_utc_offset( $event_tz_string )
				: new DateTimeZone( $event_tz_string );

			if ( false === $timezone ) {
				return $date;
			}

			$datetime = new DateTime( $date, new DateTimeZone( 'UTC' ) );
			$datetime->setTimezone( $timezone );

			return $datetime->format( 'c' );
		} catch ( Exception $e ) {
			return $date;
		}
	}

	/**
	 * Fetches the JSON-LD data for this type of object
	 *
	 * @param  int|WP_Post|null $post The post/event
	 * @param  array  $args
	 *
	 * @return array
	 */
	public function get_data( $posts = null, $args = array() ) {
		// Fetch the global post object if no posts are provided
		if ( ! is_array( $posts ) && empty( $posts ) ) {
			$posts = array( $GLOBALS['post'] );
		}
		// If we only received a single post object, wrap it in an array
		else {
			$posts = ( $posts instanceof WP_Post ) ? array( $posts ) : (array) $posts;
		}

		$return = array();

		foreach ( $posts as $i => $post ) {
			// We may have been passed a post ID - let's ensure we have the post object
			if ( is_numeric( $post ) ) {
				$post = get_post( $post );
			}

			// If we don't have a valid post object, skip to the next item
			if ( ! $post instanceof WP_Post ) {
				continue;
			}

			$data = parent::get_data( $post, $args );

			// If we have an Empty data we just skip
			if ( empty( $data ) ) {
				continue;
			}

			// Fetch first key
			$post_id = key( $data );

			// Fetch first Value
			$data = reset( $data );

			$event_tz_string = get_post_meta( $post_id, '_EventTimezone', true );
			$tz_mode         = tribe_get_option( 'tribe_events_timezone_mode', 'event' );
			$tz_string       = $event_tz_string && $tz_mode === 'event' ? $event_tz_string : Tribe__Events__Timezones::wp_timezone_string();

			$data->startDate = Tribe__Events__Timezones::to_utc( tribe_get_start_date( $post_id, true, Tribe__Date_Utils::DBDATETIMEFORMAT ), $tz_string, 'c' );
			$data->endDate   = Tribe__Events__Timezones::to_utc( tribe_get_end_date( $post_id, true, Tribe__Date_Utils::DBDATETIMEFORMAT ), $tz_string, 'c' );

			// @todo once #90984 is resolved this extra step should not be required
			if ( ! empty( $tz_string ) ) {
				$data->startDate = $this->get_localized_iso8601_string( $data->startDate, $tz_string );
				$data->endDate   = $this->get_localized_iso8601_string( $data->endDate, $tz_string );
			}

			if ( tribe_has_venue( $post_id ) ) {
				$venue_id       = tribe_get_venue_id( $post_id );
				$venue_data     = Tribe__Events__JSON_LD__Venue::instance()->get_data( $venue_id );
				$data->location = reset( $venue_data );
			}

			if ( tribe_has_organizer( $post_id ) ) {
				$organizer_id    = tribe_get_organizer_id( $post_id );
				$organizer_data  = Tribe__Events__JSON_LD__Organizer::instance()->get_data( $organizer_id );
				$data->organizer = reset( $organizer_data );
			}

			$price = tribe_get_cost( $post_id );
			$price = $this->normalize_price( $price );
			if ( '' !== $price ) {

				// The currency has to be in ISO 4217
				$event_currency = get_post_meta( $post_id, '_EventCurrencySymbol', true );
				$currency       = ( '' !== $event_currency ) ? $event_currency : 'USD';

				// Manually Include the Price for non Event Tickets
				$data->offers = (object) [
					'@type'         => 'Offer',
					'price'         => $price,
					'priceCurrency' => $currency,
					// Use the same url as the event
					'url'           => $data->url,
					'category'      => 'primary',
					'availability'  => 'inStock',
					'validFrom'     => date( DateTime::ATOM, strtotime( get_the_date( '', $post_id ) ) ),
				];
			}

			// Setting a default parameter here to avoid Google console errors
			$data->performer = 'Organization';

			$data = $this->apply_object_data_filter( $data, $args, $post );
			$return[ $post_id ] = $data;
		}

		return $return;
	}

	/**
	 * Normalizes the price entry to make it compatible with JSON-LD guidelines.
	 *
	 * @param string|int $price
	 *
	 * @return string
	 */
	protected function normalize_price( $price ) {

		// Make it work with different languages
		$regex_free  = '/^\\s*' . __( 'Free', 'the-events-calendar' ) . '\\s*$/i';

		// Replace free with 0 (in any language)
		$map = array(
			$regex_free => '0',
		);

		foreach ( $map as $normalization_regex => $normalized_price ) {
			if ( preg_match( $normalization_regex, '' . $price ) ) {
				$price = $normalized_price;
			}
		}

		$locale_conv = localeconv();

		/**
		 * Allows filtering the monetary decimal point used in the site.
		 *
		 * @param string $mon_decimal_point The monetary decimal pointer; defaults to the one
		 *                                  returned by PHP `localeconv` function.
		 *
		 * @see localeconv()
		 */
		$mon_decimal_point = apply_filters( 'tribe_events_json_ld_price_decimal_point', $locale_conv['mon_decimal_point'] );

		// normalize the decimal separator
		$price = str_replace( $mon_decimal_point, '.', $price );

		/**
		 * Allows filtering the monetary thousands separator used in the site.
		 *
		 * @param string $mon_decimal_point The monetary thousands separator; defaults to the one
		 *                                  returned by PHP `localeconv` function.
		 *
		 * @see localeconv()
		 */
		$mon_thousands_sep = apply_filters( 'tribe_events_json_ld_price_thousands_separator', $locale_conv['mon_thousands_sep'] );

		// remove thousands separator
		return str_replace( $mon_thousands_sep, '', $price );
	}

	/**
	 * Get a link to the event
	 *
	 * @since 4.5.10
	 *
	 * @param  int|WP_Post  $post The Post Object or ID
	 *
	 * @return false|string Link to the event or false
	 */
	protected function get_link( $post ) {
		return tribe_get_event_link( $post );
	}

}
