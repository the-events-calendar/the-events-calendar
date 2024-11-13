<?php
/**
 * Class that handles interfacing with TEC\Common\Notifications.
 *
 * @since   TBD
 *
 * @package TEC\Events\Notifications
 */

namespace TEC\Events\Notifications;

use Tribe__Events__Main as TEC;

/**
 * Class Notifications
 *
 * @since   TBD
 * @package TEC\Events\Notifications
 */
class Notifications {

	/**
	 * Determines if we are on a TEC admin page except the post edit page.
	 *
	 * @since TBD
	 *
	 * @return boolean
	 */
	public static function is_tec_admin_page(): bool {
		$current_screen = get_current_screen();
		$helper         = \Tribe__Admin__Helpers::instance();

		// Are we on a tec post-type admin screen?
		if ( ! $helper->is_post_type_screen( TEC::POSTTYPE ) ) {
			return false;
		}

		// Are we on a new post screen?
		if ( $current_screen instanceof \WP_Screen && $current_screen->action === 'add' ) {
			return false;
		}

		return true;
	}

	/**
	 * Outputs the hook that renders the Notifications icon on all TEC admin pages.
	 *
	 * @since TBD
	 */
	public function render_icon() {
		if ( ! static::is_tec_admin_page() ) {
			return;
		}

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
