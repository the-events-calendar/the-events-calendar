<?php
/**
 * Handles The Events Calendar integration.
 *
 * @since   6.0.4
 *
 * @package TEC\Events\Integrations
 */
namespace TEC\Events\Integrations;

use TEC\Common\lucatume\DI52\ServiceProvider;


/**
 * Class Provider
 *
 * @since   6.0.4
 *
 * @package TEC\Events\Integrations
 */
class Provider extends ServiceProvider {

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
	}
}
