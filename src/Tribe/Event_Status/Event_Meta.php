<?php
/**
 * Handles the event status meta.
 *
 * @since TBD
 *
 * @package Tribe\Events\Event_Status
 */

namespace Tribe\Events\Event_Status;

/**
 * Class Event_Meta
 *
 * @since   TBD
 *
 * @package Tribe\Events\Event_Status
 */
class Event_Meta {
	/**
	 * Meta Key for Status field.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public static $key_status = '_tribe_events_status';

	/**
	 * Meta Key for Canceled reason field.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public static $key_status_reason = '_tribe_events_status_reason';

	/**
	 * Meta Key for Canceled reason field used for migration from extension.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public static $key_status_canceled_reason = '_tribe_events_status_canceled_reason';

	/**
	 * Meta Key for Postponed reason field used for migration from extension.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public static $key_status_postponed_reason = '_tribe_events_status_postponed_reason';

	/**
	 * All the meta keys, in a set.
	 *
	 * @since TBD
	 *
	 * @var array<string>
	 */
	public static $event_status_keys = [
		'_tribe_events_status',
		'_tribe_events_status_reason',
	];
}
