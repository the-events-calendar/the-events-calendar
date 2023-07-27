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
use TEC\Tickets\Custom_Tables\V1\Provider as ET_Custom_Tables;

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
	 * @since TBD Load the Event Tickets Custom Tables logic by register_on_action.
	 * @since 6.0.0
	 */
	public function register() {
		// Class defined by the Event Events plugin.
		$this->container->register_on_action( 'tec_tickets_custom_tables_controller_registered', ET_Custom_Tables::class );

		// Class defined by the Advanced Custom Fields plugin.
		if ( class_exists( 'ACF' ) ) {
			$this->container->register( ACF_Controller::class );
		}
	}
}
