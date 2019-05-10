<?php
/**
 * The main service provider for the version 2 of the Views.
 *
 * @package Tribe\Events\Views\V2
 * @since   TBD
 */

namespace Tribe\Events\Views\V2;

/**
 * Class Service_Provider
 *
 * @since   TBD
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

		$this->container->singleton( Template_Bootstrap::class, Template_Bootstrap::class );
		$this->container->singleton( Template\Event::class, Template\Event::class );
		$this->container->singleton( Template\Page::class, Template\Page::class );
		$this->container->singleton( Kitchen_Sink::class, Kitchen_Sink::class );
		$this->container->singleton( Assets::class, Assets::class );
		$this->container->singleton( Rest_Endpoint::class, Rest_Endpoint::class );

		$hooks = new Hooks( $this->container );
		$hooks->register();

		$assets = new Assets();
		$assets->register();

		// Allow Hooks to be removed, by having the them registred to the container
		$this->container->singleton( Hooks::class, $hooks );
		$this->container->singleton( 'events.views.v2.hooks', $hooks );
		$this->container->singleton( 'events.views.v2.provider', $this );

		View::set_container( $this->container );
	}

}