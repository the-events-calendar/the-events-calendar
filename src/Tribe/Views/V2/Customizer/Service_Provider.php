<?php
/**
 * The main service provider for the version 2 of the Views.
 *
 * @package Tribe\Events\Views\V2\Customizer
 * @since   5.7.0
 */

namespace Tribe\Events\Views\V2\Customizer;

use Tribe\Events\Views\V2\Customizer;
use Tribe\Events\Views\V2\Customizer\Section\Month_View;

/**
 * Class Service_Provider
 *
 * @since   5.7.0
 *
 * @package Tribe\Events\Views\V2\Customizer
 */
class Service_Provider extends \tad_DI52_ServiceProvider {
	public function register() {
		$this->container->singleton( 'events.views.v2.customizer.provider', $this );

		$this->register_hooks();
		$this->register_assets();

		tribe_singleton( 'events.views.v2.customizer.month-view', new Month_View() );
		tribe('events.views.v2.customizer.month-view');
	}

	public function register_hooks() {
		$hooks = new Hooks( $this->container );
		$hooks->register();

		// Allow Hooks to be removed, by having the them registered to the container
		$this->container->singleton( Hooks::class, $hooks );
		$this->container->singleton( 'events.views.v2.customizer.hooks', $hooks );
	}

	public function register_assets() {}

}
