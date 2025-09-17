<?php
/**
 * Controller to handle Legacy blocks.
 */

declare( strict_types=1 );

namespace TEC\Events\Classy\Legacy_Blocks;

use TEC\Common\Contracts\Provider\Controller as Controller_Contract;

/**
 * Handle Legacy blocks and block templates with classy.
 *
 * @since TBD
 */
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
		$this->container->singleton( 'events.editor.template.overwrite', 'Tribe__Events__Editor__Template__Overwrite' );
		tribe( 'events.editor.template.overwrite' )->hook();
	}
	
	/**
	 * Un-registers the filters and actions hooks added by the controller.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function unregister(): void {
		$this->container->get( 'events.editor.template' )->unregister();
		$this->container->get( 'events.editor.template.overwrite' )->unregister();
	}
}
