<?php
/**
 * The Event Status service provider.
 *
 * @package Tribe\Events\Event_Status
 * @since   TBD
 */

namespace Tribe\Events\Event_Status;

use Tribe__Events__Main as Events_Plugin;
use Tribe__Context as Context;
use WP_Post;

/**
 * Class Statuses
 *
 * @since   TBD
 *
 * @package Tribe\Events\Event_Status
 */
class Statuses {

	/**
	 * Get the scheduled status label.
	 *
	 * @since TBD
	 *
	 * @return string The label for the scheduled status.
	 */
	public function get_scheduled_label() {

		/**
		 * Filter the scheduled label for event status.
		 *
		 * @since
		 *
		 * @param string The default label for the scheduled status.
		 */
		return apply_filters( 'tribe_events_status_scheduled_label', _x( 'Scheduled', 'Scheduled label.', 'the-events-calendar' ) );
	}

	/**
	 * Get the canceled status label.
	 *
	 * @since TBD
	 *
	 * @return string The label for the canceled status.
	 */
	public function get_canceled_label() {

		/**
		 * Filter the canceled label for event status.
		 *
		 * @since
		 *
		 * @param string The default label for the canceled status.
		 */
		return apply_filters( 'tribe_events_status_canceled_label', _x( 'Canceled', 'Canceled label.', 'the-events-calendar' ) );
	}

	/**
	 * Get the postponed status label.
	 *
	 * @since TBD
	 *
	 * @return string The label for the postponed status.
	 */
	public function get_postponed_label() {

		/**
		 * Filter the postponed label for event status.
		 *
		 * @since
		 *
		 * @param string The default label for the postponed status.
		 */
		return apply_filters( 'tribe_events_status_postponed_label', _x( 'Postponed', 'Postponed label', 'the-events-calendar' ) );
	}
}
