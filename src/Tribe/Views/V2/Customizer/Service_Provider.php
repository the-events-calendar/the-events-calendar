<?php
/**
 * The main service provider for the version 2 of the Views.
 *
 * @package Tribe\Events\Views\V2\Customizer
 * @since   TBD
 */

namespace Tribe\Events\Views\V2\Customizer;

use Tribe\Events\Views\V2\Customizer;
use Tribe\Events\Views\V2\Customizer\Section\Global_Elements;
use Tribe\Events\Views\V2\Customizer\Section\Single_Event;
use Tribe\Events\Views\V2\Customizer\Section\Month_View;

/**
 * Class Service_Provider
 *
 * @since   TBD
 *
 * @package Tribe\Events\Views\V2\Customizer
 */
class Service_Provider extends \tad_DI52_ServiceProvider {
	public function register() {
		$this->container->singleton( 'events.views.v2.customizer.provider', $this );

		$this->register_hooks();
		$this->register_assets();

		tribe_singleton( 'tec.customizer.month-view', new Month_View() );
		tribe_singleton( 'tec.customizer.global-elements', new Global_Elements() );
		tribe_singleton( 'tec.customizer.single-event', new Single_Event() );
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
