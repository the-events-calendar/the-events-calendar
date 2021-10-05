<?php
/**
 * Provides the integrations required by the plugin to work with other plugins.
 *
 * @since   TBD
 *
 * @package TEC\Custom_Tables\V1\Integrations
 */

namespace TEC\Custom_Tables\V1\Integrations;


use tad_DI52_ServiceProvider;

/**
 * Class Provider
 *
 * @since   TBD
 *
 * @package TEC\Custom_Tables\V1\Integrations
 */
class Provider extends tad_DI52_ServiceProvider {
	/**
	 * Registers the Service Providers required for the plugin to work with other plugins.
	 *
	 * @since TBD
	 */
	public function register() {
		// Class defined by the Event Events plugin.
		if ( class_exists( '\\TEC\\Event_Tickets\\Custom_Tables\\V1\\Provider' ) ) {
			$this->container->register( \TEC\Event_Tickets\Custom_Tables\V1\Provider::class );
		}

		// Class defined by the GitHub Updater plugin.
		if ( class_exists( '\\Fragen\\Git_Updater\\Plugin' ) ) {
			$this->container->register( GH_Updater\Provider::class );
		}
	}
}
