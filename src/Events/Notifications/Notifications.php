<?php
/**
 * Class that handles interfacing with TEC\Common\Notifications.
 *
 * @since   6.4.0
 *
 * @package TEC\Events\Notifications
 */

namespace TEC\Events\Notifications;

/**
 * Class Notifications
 *
 * @since   6.4.0
 * @package TEC\Events\Notifications
 */
class Notifications {

	/**
	 * The slug of this plugin calling the Notifications class.
	 *
	 * @since 6.4.0
	 *
	 * @var string
	 */
	protected static string $slug = 'the-events-calendar';

	/**
	 * Outputs the hook that renders the Notifications icon on all TEC admin pages.
	 *
	 * @since 6.4.0
	 */
	public function render_icon() {
		// Don't double-dip on the action.
		if ( did_action( 'tec_ian_icon' ) ) {
			return;
		}

		/**
		 * Fires to trigger the IAN icon on admin pages.
		 *
		 * @since 6.4.0
		 */
		do_action( 'tec_ian_icon', static::$slug );
	}
}
