<?php

namespace TEC\Events\Editor\Full_Site;

use Tribe\Events\Editor\Blocks\Archive_Events;

/**
 * Class Provider
 *
 * @since TBD
 *
 * @package
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
		$this->register_hooks();

		// Register the Service Provider for Assets.
		$this->register_assets();

		// Register the service provider itself on the container.
		$this->container->singleton( static::class, $this );
	}

	protected function register_singletons() {
		$this->container->singleton( Archive_Events::class, Archive_Events::class, [ 'load' ] );
	}

	/**
	 * Registers the provider handling all the 1st level filters and actions for this Service Provider
	 *
	 * @since TBD
	 */
	protected function register_assets() {
		$assets = new Assets( $this->container );
		$assets->register();

		$this->container->singleton( Assets::class, $assets );
	}

	/**
	 * Registers the provider handling all the 1st level filters and actions for this Service Provider.
	 *
	 * @since TBD
	 */
	protected function register_hooks() {
		$hooks = new Hooks( $this->container );
		$hooks->register();

		// Allow Hooks to be removed, by having the them registered to the container
		$this->container->singleton( Hooks::class, $hooks );
		$this->container->singleton( 'events.editor.full-site.hooks', $hooks );
	}
}
