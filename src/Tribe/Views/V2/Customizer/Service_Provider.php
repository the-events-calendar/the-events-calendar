<?php
/**
 * The main service provider for the version 2 of the Views.
 *
 * @package Tribe\Events\Views\V2\Customizer
 * @since   5.7.0
 */

namespace Tribe\Events\Views\V2\Customizer;

use Tribe\Events\Views\V2\Customizer\Section\Global_Elements;
use Tribe\Events\Views\V2\Customizer\Section\Month_View;
use Tribe\Events\Views\V2\Customizer\Section\Events_Bar;
use Tribe\Events\Views\V2\Customizer\Section\Single_Event;
use TEC\Common\Contracts\Service_Provider as Provider_Contract;


/**
 * Class Service_Provider
 *
 * @since   5.7.0
 *
 * @package Tribe\Events\Views\V2\Customizer
 */
class Service_Provider extends Provider_Contract {

	public function register() {
		$this->container->singleton( 'events.views.v2.customizer.provider', $this );

		$this->register_hooks();

		tribe_singleton( 'events.views.v2.customizer.global-elements', Global_Elements::class );
		// For backwards-compatibility.
		tribe_singleton(
			'tec.customizer.global-elements',
			static function() {
				return tribe( 'events.views.v2.customizer.global-elements' );
			}
		);
		tribe_singleton( 'events.views.v2.customizer.month-view', Month_View::class );
		tribe_singleton( 'events.views.v2.customizer.events-bar', Events_Bar::class );
		tribe_singleton( 'events.views.v2.customizer.single-event', Single_Event::class );

		// Notice for extension incompatibility
		tribe_singleton( Notice::class, Notice::class, [ 'hook' ] );
	}

	public function register_hooks() {
		$hooks = new Hooks( $this->container );
		$hooks->register();

		// Allow Hooks to be removed, by having the them registered to the container
		$this->container->singleton( Hooks::class, $hooks );
		$this->container->singleton( 'events.views.v2.customizer.hooks', $hooks );
	}

}
