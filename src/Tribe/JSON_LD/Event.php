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
	public    $type = 'Event';

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
	 * Fetches the JSON-LD data for this type of object
	 *
	 * @param  int|WP_Post|null $post The post/event
	 * @param  array  $args
	 * @return array
	 */
	public function get_data( $posts = null, $args = array() ) {
		$posts = (array) $posts;
		$return = array();

		foreach ( $posts as $i => $post ) {
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

			if ( tribe_has_venue( $post_id ) ) {
				$venue_data = Tribe__Events__JSON_LD__Venue::instance()->get_data( tribe_get_venue_id( $post_id ) );
				$data->location = reset( $venue_data );
			}

			if ( tribe_has_organizer( $post_id ) ) {
				$organizer_data = Tribe__Events__JSON_LD__Organizer::instance()->get_data( tribe_get_organizer_id( $post_id ) );
				$data->organizer = reset( $organizer_data );
			}

			$price = tribe_get_cost( $post_id );
			if ( ! empty( $price ) ) {
				// Manually Include the Price for non Event Tickets
				$data->offers = (object) array(
					'@type' => 'Offer',
					'price' => $price,

					// Use the same url as the event
					'url' => $data->url,
				);
			}

			$return[ $post_id ] = $data;
		}

		return $return;
	}
}
