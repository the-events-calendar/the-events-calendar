<?php
/**
 * The main service provider for the version 2 of the Views.
 *
 * @package Tribe\Events\Views\V2\Customizer
 * @since   TBD
 */

namespace Tribe\Events\Views\V2\Customizer;

use Tribe\Events\Views\V2\Customizer;
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
		$this->container->singleton( Customizer::class, Customizer::class );

		tribe_singleton( 'events.views.v2.customizer.month-view', new Month_View() );
		tribe('events.views.v2.customizer.month-view');

		$this->register_hooks();
		$this->register_assets();
	}

	public function register_hooks() {}

	public function register_assets() {}

}
