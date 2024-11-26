<?php
/**
 * Class that handles interfacing with TEC\Common\Notifications.
 *
 * @since   TBD
 *
 * @package TEC\Events\Notifications
 */

namespace TEC\Events\Notifications;

/**
 * Class Notifications
 *
 * @since   TBD
 * @package TEC\Events\Notifications
 */
class Notifications {

	/**
	 * Outputs the hook that renders the Notifications icon on all TEC admin pages.
	 *
	 * @since TBD
	 */
	public function render_icon() {
		// Don't double-dip on the action.
		if ( did_action( 'tec_ian_icon' ) ) {
			return;
		}

		// 'the-events-calendar'
		$plugin_slug = substr( basename( TRIBE_EVENTS_FILE ), 0, -4 );

		/**
		 * Fires to trigger the IAN icon on admin pages.
		 *
		 * @since TBD
		 */
		do_action( 'tec_ian_icon', $plugin_slug );
	}
}
