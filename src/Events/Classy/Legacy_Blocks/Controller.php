<?php

declare( strict_types=1 );

namespace TEC\Events\Classy\Legacy_Blocks;

use TEC\Common\Contracts\Provider\Controller as Controller_Contract;
class Controller extends Controller_Contract {
	
	/**
	 * Registers the filters and actions hooks added by the controller.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function do_register(): void {
		$this->container->singleton( 'events.editor.template', 'Tribe__Events__Editor__Template' );
		$this->container->singleton( 'events.editor.template.overwrite', 'Tribe__Events__Editor__Template__Overwrite', [ 'hook' ] );
		tribe( 'events.editor.template.overwrite' );
	}
	
	/**
	 * Un-registers the filters and actions hooks added by the controller.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function unregister(): void {
	}
}
