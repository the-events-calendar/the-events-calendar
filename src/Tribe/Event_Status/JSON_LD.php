<?php
/**
 * The Event Status JSON LD modifier.
 *
 * @package Tribe\Events\Event_Status
 * @since   TBD
 */

namespace Tribe\Events\Event_Status;

use WP_Post;

/**
 * Class JSON_LD.
 *
 * @since   TBD
 *
 * @package Tribe\Events\Event_Status
 */
class JSON_LD {

	/**
	 * The reference schema URL for an offline event attendance mode.
	 *
	 * @since TBD
	 */
	const OFFLINE_EVENT_ATTENDANCE_MODE = 'https://schema.org/OfflineEventAttendanceMode';

	/**
	 * The reference schema URL for a canceled event attendance mode.
	 *
	 * @since TBD
	 */
	const CANCELED_SCHEMA = 'https://schema.org/EventCancelled';

	/**
	 * The reference schema URL for a postponed event attendance mode.
	 *
	 * @since TBD
	 */
	const POSTPONED_SCHEMA = 'https://schema.org/EventPostponed';


	/**
	 * Modifiers to the JSON LD event object.
	 *
	 * @since TBD
	 *
	 * @param object  $data The JSON-LD object.
	 * @param array   $args The arguments used to get data.
	 * @param WP_Post $post The post object.
	 *
	 * @return object JSON LD object after modifications.
	 */
	public function modify_event( $data, $args, $post ) {
		$event = tribe_get_event( $post );

		if ( ! $event instanceof \WP_Post) {
			return $data;
		}

		/**
		 * Filters the default attendance mode.
		 *
		 * @since TBD
		 *
		 * @param string  $attendance_mode The default attendance mode.
		 * @param object  $data            The JSON-LD object.
		 * @param array   $args            The arguments used to get data.
		 * @param WP_Post $post            The post object.
		 */
		$attendance_mode = apply_filters( 'tribe_events_status_default_single_event_online_status', static::OFFLINE_EVENT_ATTENDANCE_MODE, $data, $args, $post );

		$data->eventAttendanceMode = $attendance_mode;

		// Update event status schema.
		if ( 'canceled' === $event->event_status ) {
			$data->eventStatus = static::CANCELED_SCHEMA;
		} elseif ( 'postponded' === $event->event_status ) {
			$data->eventStatus = static::POSTPONED_SCHEMA;
		}

		return $data;
	}
}
