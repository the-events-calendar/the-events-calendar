<?php
/**
 * A mock metabox class for the "The Events Calendar Extension: Events Control" plugin to be used in tests.
 *
 * @since TBD
 *
 * @package Tribe\Extensions\EventsControl
 */

namespace Tribe\Extensions\EventsControl;

/**
 * Class Metabox
 *
 * @since   TBD
 *
 * @package Tribe\Extensions\EventsControl
 */
class Metabox {

	/**
	 * ID for the metabox in WP.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public static $id = 'tribe-events-control';

	/**
	 * Action name used for the nonce on saving the metabox.
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	public static $nonce_action = 'tribe-event-control-nonce';
}
