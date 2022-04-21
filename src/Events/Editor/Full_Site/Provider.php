<?php

namespace TEC\Events\Editor\Full_Site;

use Tribe\Events\Editor\Blocks\Archive_Events;

/**
 * Class Provider
 *
 * @since TBD
 *
 * @package TEC\Events\Editor\Full_Site
 */
class Provider extends \tad_DI52_ServiceProvider {
	/**
	 * Register the provider singletons.
	 *
	 * @since TBD
	 */
	public function register() {
		if ( ! tec_is_full_site_editor() ) {
			return;
		}

		$this->container->singleton( Templates::class );

		// Register singletons.
		$this->register_singletons();

		// Register the Service Provider for Hooks.
		$this->container->register(Hooks::class);

		// Register the Service Provider for Assets.
		$this->container->register( Assets::class );

		// Register the service provider itself on the container.
		$this->container->singleton( static::class, $this );
	}

	protected function register_singletons() {
		$this->container->singleton( Archive_Events::class, Archive_Events::class, [ 'load' ] );
	}
}
