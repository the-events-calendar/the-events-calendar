<?php
/**
 * The API provided by all Providers that allow their registration and unregistration.
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\WP_Query
 */

namespace TEC\Events\Custom_Tables\V1;

/**
 * Interface Provider_Contract
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\WP_Query
 */
interface Provider_Contract {
	/**
	 * Register the filters and DI container bindings required to integrate the plugin custom tables logic.
	 *
	 * @since 6.0.0
	 */
	public function register();

	/**
	 * Unregisters the filters registered by the provider.
	 *
	 * @since 6.0.0
	 */
	public function unregister();
}
