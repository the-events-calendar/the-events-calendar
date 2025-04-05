<?php
/**
 * Provides the integrations required by the plugin to work with other plugins.
 *
 * @since   6.0.0
 *
 * @package TEC\Events\Custom_Tables\V1\Integrations
 */

namespace TEC\Events\Custom_Tables\V1\Integrations;


use TEC\Common\Contracts\Service_Provider;
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
	 * @since 6.1.4 Removed registering ET CT1 logic.
	 * @since 6.0.0
	 */
	public function register() {
		// Class defined by the Advanced Custom Fields plugin.
		if ( class_exists( 'ACF' ) ) {
			$this->container->register( ACF_Controller::class );
		}
	}
}
