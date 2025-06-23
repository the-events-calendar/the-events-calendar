<?php
/**
 * Class that handles interfacing with TEC\Common\Notifications.
 *
 * @since   6.4.0
 *
 * @package TEC\Events\Notifications
 */

namespace TEC\Events\Notifications;

use TEC\Events\Integrations\Integration_Abstract;
use TEC\Common\Integrations\Traits\Plugin_Integration;

/**
 * Class Notifications
 *
 * @since   6.4.0
 * @package TEC\Events\Notifications
 */
class Notifications extends Integration_Abstract {
	use Plugin_Integration;

	/**
	 * The slug of this plugin calling the Notifications class.
	 *
	 * @since 6.4.0
	 *
	 * @return string
	 */
	public static function get_slug(): string {
		return 'the-events-calendar';
	}

	/**
	 * @inheritDoc
	 */
	public function load_conditionals(): bool {
		return has_action( 'tribe_common_loaded' );
	}

	/**
	 * @inheritDoc
	 */
	protected function load(): void {
		add_filter( 'tec_common_ian_allowed_pages', [ $this, 'add_allowed_pages' ] );

		add_action( 'admin_footer', [ $this, 'render_icon' ] );
	}

	/**
	 * Adds the Events pages to the list of allowed pages for Notifications.
	 *
	 * @since 6.10.2
	 *
	 * @param array $allowed An array of pages where notifications will be displayed.
	 *
	 * @return array
	 */
	public function add_allowed_pages( $allowed ) {
		$allowed[] = 'tribe_events_page_tec-events-settings';
		$allowed[] = 'edit-tribe_events';
		$allowed[] = 'tribe_events';
		$allowed[] = 'tribe_events_page_first-time-setup';
		return $allowed;
	}

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
		do_action( 'tec_ian_icon', $this->get_slug() );
	}
}
