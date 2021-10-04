<?php
/**
 * The API implemented by a Service Provider that can be registered, unregistered or otherwise controlled.
 *
 * @since   TBD
 *
 * @package TEC\Custom_Tables\V1\Service_Providers
 */

namespace TEC\Custom_Tables\V1\Service_Providers;

/**
 * Interface Controllable_Service_Provider
 *
 * @since   TBD
 *
 * @package TEC\Custom_Tables\V1\Service_Providers
 */
interface Controllable_Service_Provider {
	/**
	 * Registers the Service Provider, inherited from the `\tad_DI52_ServiceProvider` class.
	 *
	 * @since TBD
	 *
	 * @return bool Whether the Service Provider correctly registered or not.
	 */
	public function register();

	/**
	 * Unregisters the Service Provider.
	 *
	 * @since TBD
	 *
	 * @return bool Whether the unregistration was successful or not.
	 */
	public function unregister();
}
