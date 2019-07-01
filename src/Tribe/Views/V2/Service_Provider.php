<?php
/**
 * The main service provider for the version 2 of the Views.
 *
 * @package Tribe\Events\Views\V2
 * @since   4.9.2
 */

namespace Tribe\Events\Views\V2;

/**
 * Class Service_Provider
 *
 * @since   4.9.2
 *
 * @package Tribe\Events\Views\V2
 */
class Service_Provider extends \tad_DI52_ServiceProvider {

	const NAME_SPACE = 'tribe/views/v2';

	/**
	 * Binds and sets up implementations.
	 */
	public function register() {
		require_once tribe( 'tec.main' )->plugin_path . 'src/functions/views/provider.php';

		if ( ! tribe_events_views_v2_is_enabled() ) {
			return;
		}

		require_once tribe( 'tec.main' )->plugin_path . 'src/Tribe/Views/V2/functions/template-tags.php';

		$this->container->singleton( Manager::class, Manager::class );
		$this->container->singleton( Template_Bootstrap::class, Template_Bootstrap::class );
		$this->container->singleton( Template\Event::class, Template\Event::class );
		$this->container->singleton( Template\Page::class, Template\Page::class );
		$this->container->singleton( Kitchen_Sink::class, Kitchen_Sink::class );
		$this->container->singleton( Theme_Compatibility::class, Theme_Compatibility::class );
		$this->container->singleton( Rest_Endpoint::class, Rest_Endpoint::class );

		$this->register_hooks();
		$this->register_assets();

		$this->register_v1_compat();

		// Register the SP on the container
		$this->container->singleton( 'events.views.v2.provider', $this );

		// @todo: remove this when we hydrate the month view with data and we use the correct template tags.
		require_once tribe( 'tec.main' )->plugin_path . 'src/Tribe/Views/V2/month-view-demo-template-tags.php';

		// Since the View main class will act as a DI container itself let's provide it with the global container.
		View::set_container( $this->container );
	}

	/**
	 * Registers the provider handling all the 1st level filters and actions for Views v2.
	 *
	 * @since 4.9.3
	 */
	protected function register_assets() {
		$assets = new Assets( $this->container );
		$assets->register();

		$this->container->singleton( Assets::class, $assets );
	}

	/**
	 * Registers the provider handling all the 1st level filters and actions for Views v2.
	 *
	 * @since 4.9.2
	 */
	protected function register_hooks() {
		$hooks = new Hooks( $this->container );
		$hooks->register();

		// Allow Hooks to be removed, by having the them registred to the container
		$this->container->singleton( Hooks::class, $hooks );
		$this->container->singleton( 'events.views.v2.hooks', $hooks );
	}

	/**
	 * Registers the provider handling compatibility with v1 of the View system.
	 *
	 * @since 4.9.2
	 */
	protected function register_v1_compat() {
		$v1_compat = new V1_Compat( $this->container );
		$v1_compat->register();

		$this->container->singleton( V1_Compat::class, $v1_compat );
		$this->container->singleton( 'events.views.v1-compat', $v1_compat );
	}
}
