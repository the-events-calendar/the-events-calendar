<?php
/**
 * Handles The Events Calendar integration.
 *
 * @since   TBD
 *
 * @package TEC\Events\Integrations
 */
namespace TEC\Events\Integrations;

/**
 * Class Provider
 *
 * @since   TBD
 *
 * @package TEC\Events\Integrations
 */
class Provider extends \tad_DI52_ServiceProvider {
	/**
	 * Binds and sets up implementations.
	 *
	 * @since TBD
	 */
	public function register() {
		$this->container->singleton( static::class, $this );

		$this->container->register( Plugins\WordPress_SEO\Provider::class );
	}
}
