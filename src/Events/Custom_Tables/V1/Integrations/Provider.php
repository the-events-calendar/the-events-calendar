<?php
/**
 * Provides the integrations required by the plugin to work with other plugins.
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Integrations
 */

namespace TEC\Events\Custom_Tables\V1\Integrations;


use tad_DI52_ServiceProvider as Service_Provider;
use TEC\Events\Custom_Tables\V1\Integrations\ACF\Controller as ACF_Controller;

/**
 * Class Provider
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Integrations
 */
class Provider extends Service_Provider {
	/**
	 * Registers the Service Providers required for the plugin to work with other plugins.
	 *
	 * @since 6.0.0
	 */
	public function register() {
		// Class defined by the Event Events plugin.
		if ( class_exists( '\\TEC\\Event_Tickets\\Custom_Tables\\V1\\Provider' ) ) {
			$this->container->register( \TEC\Tickets\Custom_Tables\V1\Provider::class );
		}

		// Class defined by the Advanced Custom Fields plugin.
		if ( class_exists( 'ACF' ) ) {
			$this->container->register( ACF_Controller::class );
		}
	}
}
