<?php
/**
 * Handles The Events Calendar integration.
 *
 * @since   6.0.4
 *
 * @package TEC\Events\Integrations
 */

namespace TEC\Events\Integrations;

use TEC\Common\Contracts\Service_Provider;

/**
 * Class Provider
 *
 * @since   6.0.4
 * @since 6.15.8 Added Avada Provider.
 *
 * @package TEC\Events\Integrations
 */
class Provider extends Service_Provider {

	/**
	 * Binds and sets up implementations.
	 *
	 * @since 6.0.4
	 */
	public function register() {
		$this->container->singleton( static::class, $this );

		$this->container->register( Plugins\WordPress_SEO\Provider::class );
		$this->container->register( Plugins\Rank_Math\Provider::class );
		$this->container->register( Plugins\Colbri_Page_Builder\Provider::class );
		$this->container->register( Plugins\Event_Tickets\Provider::class );
		$this->container->register( Plugins\Elementor\Controller::class );
		$this->container->register( Themes\Avada\Provider::class );
		$this->container->register_on_action( 'tribe_plugins_loaded', Plugins\TEC_Tweaks_Extension\Provider::class );
		$this->container->register_on_action( 'tec_container_registered_provider_TEC\Tickets_Wallet_Plus\Controller', Plugins\Tickets_Wallet_Plus\Controller::class );
	}
}
