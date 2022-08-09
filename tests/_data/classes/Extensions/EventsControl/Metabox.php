<?php
/**
 * A mock metabox class for the "The Events Calendar Extension: Events Control" plugin to be used in tests.
 *
 * @since 5.11.0
 *
 * @package Tribe\Extensions\EventsControl
 */

namespace Tribe\Extensions\EventsControl;

/**
 * Class Metabox
 *
 * @since   5.11.0
 *
 * @package Tribe\Extensions\EventsControl
 */
class Metabox {

	/**
	 * ID for the metabox in WP.
	 *
	 * @since 5.11.0
	 *
	 * @var string
	 */
	public static $id = 'tribe-events-control';

	/**
	 * Action name used for the nonce on saving the metabox.
	 *
	 * @since 5.11.0
	 *
	 * @var string
	 */
	public static $nonce_action = 'tribe-event-control-nonce';
}
