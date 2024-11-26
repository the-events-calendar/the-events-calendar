<?php
/**
 * Service Provider for interfacing with TEC\Common\Notifications.
 *
 * @since   TBD
 *
 * @package TEC\Events\Notifications
 */

namespace TEC\Events\Notifications;

use TEC\Common\Contracts\Service_Provider;

/**
 * Class Provider
 *
 * @since   TBD
 * @package TEC\Events\Notifications
 */
class Provider extends Service_Provider {

	/**
	 * Handles the registering of the provider.
	 *
	 * @since TBD
	 */
	public function register() {
		add_action( 'admin_footer', [ $this, 'render_icon' ] );
	}

	/**
	 * Renders the Notification icon.
	 *
	 * @since TBD
	 */
	public function render_icon() {
		return $this->container->get( Notifications::class )->render_icon();
	}
}
