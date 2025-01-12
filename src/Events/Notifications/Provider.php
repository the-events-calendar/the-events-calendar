<?php
/**
 * Service Provider for interfacing with TEC\Common\Notifications.
 *
 * @since   6.4.0
 *
 * @package TEC\Events\Notifications
 */

namespace TEC\Events\Notifications;

use TEC\Common\Contracts\Service_Provider;

/**
 * Class Provider
 *
 * @since   6.4.0
 * @package TEC\Events\Notifications
 */
class Provider extends Service_Provider {

	/**
	 * Handles the registering of the provider.
	 *
	 * @since 6.4.0
	 */
	public function register() {
		$this->container->singleton( static::class, $this );

		$this->container->register_on_action( 'tribe_plugins_loaded', Notifications::class );
	}
}
