<?php
/**
 * Handles the modifications to the event model returned by the `tribe_get_event` function.
 *
 * @since   5.11.0
 *
 * @package Tribe\Events\Event_Status\Model
 */

namespace Tribe\Events\Event_Status\Models;

use Tribe\Events\Event_Status\Event_Meta;
use WP_Post;

/**
 * Class Event
 *
 * @since   5.11.0
 *
 * @package Tribe\Events\Event_Status\Model
 */
class Event {

	/**
	 * Filters the object returned by the `tribe_get_event` function to add to it properties related to event status.
	 *
	 * @since 5.11.0
	 *
	 * @param WP_Post $event The event post object.
	 *
	 * @return WP_Post The original event object decorated with properties related to event status.
	 */
	public function add_properties( WP_Post $event ) {
		$event->event_status        = $this->get_status( $event );
		$event->event_status_reason = $this->get_reason( $event );

		/**
		 * Fires after the event object has been decorated with properties related to event status.
		 *
		 * @since 5.11.0
		 *
		 * @param \WP_Post $event The event post object as decorated by the `tribe_get_event` function, with event
		 *                        status related properties added.
		 */
		do_action( 'tribe_events_event_status_add_properties', $event );

		return $event;
	}

	/**
	 * Retrieves an event's status.
	 *
	 * @since 5.11.0
	 *
	 * @param WP_Post $event Event post object.
	 *
	 * @return null|string The event's status.
	 */
	public function get_status( $event ) {
		if ( ! $event instanceof WP_Post ) {
			$event = tribe_get_event( $event );
		}

		if ( ! $event ) {
			return null;
		}

		return get_post_meta( $event->ID, Event_Meta::$key_status, true );
	}

	/**
	 * Retrieves an event's status reason.
	 *
	 * @since 5.11.0
	 *
	 * @param WP_Post $event Event ID.
	 *
	 * @return string The event's status reason, or empty string if none or scheduled status.
	 */
	public function get_reason( $event ) {
		if ( ! $event instanceof WP_Post ) {
			$event = tribe_get_event( $event );
		}

		if ( ! $event ) {
			return '';
		}

		if ( empty( $event->event_status ) ) {
			return '';
		}

		if ( 'scheduled' === $event->event_status ) {
			return '';
		}

		return get_post_meta( $event->ID, Event_Meta::$key_status_reason, true );
	}
}
