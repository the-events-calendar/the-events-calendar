<?php
/**
 * Handles the event status meta.
 *
 * @since 5.11.0
 *
 * @package Tribe\Events\Event_Status
 */

namespace Tribe\Events\Event_Status;

/**
 * Class Event_Meta
 *
 * @since   5.11.0
 *
 * @package Tribe\Events\Event_Status
 */
class Event_Meta {
	/**
	 * Meta Key for Status field.
	 *
	 * @since 5.11.0
	 *
	 * @var string
	 */
	public static $key_status = '_tribe_events_status';

	/**
	 * Meta Key for Canceled reason field.
	 *
	 * @since 5.11.0
	 *
	 * @var string
	 */
	public static $key_status_reason = '_tribe_events_status_reason';

	/**
	 * Meta Key for event status field used for migration from extension.
	 *
	 * @since 5.11.0
	 *
	 * @var string
	 */
	public static $key_control_status = '_tribe_events_control_status';

	/**
	 * Meta Key for Canceled reason field used for migration from extension.
	 *
	 * @since 5.11.0
	 *
	 * @var string
	 */
	public static $key_status_canceled_reason = '_tribe_events_control_status_canceled_reason';

	/**
	 * Meta Key for Postponed reason field used for migration from extension.
	 *
	 * @since 5.11.0
	 *
	 * @var string
	 */
	public static $key_status_postponed_reason = '_tribe_events_control_status_postponed_reason';

	/**
	 * All the meta keys, in a set.
	 *
	 * @since 5.11.0
	 *
	 * @var array<string>
	 */
	public static $event_status_keys = [
		'_tribe_events_status',
		'_tribe_events_status_reason',
	];
}
