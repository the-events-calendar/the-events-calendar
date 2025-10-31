<?php
/**
 * Controller for request-level sanitization in TEC.
 *
 * @since TBD
 *
 * @package TEC\Events\Request
 */

declare(strict_types=1);

namespace TEC\Events\Request;

use TEC\Common\Contracts\Provider\Controller as Controller_Contract;

/**
 * Class Controller
 *
 * @since TBD
 *
 * @package TEC\Events\Request
 */
class Controller extends Controller_Contract {

	/**
	 * Registers the filters and actions hooks added by the controller.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	protected function do_register(): void {
		$this->container->singleton( Query_Vars::class, Query_Vars::class, [ 'register' ] );

		// Initialize the singleton and register the hooks.
		$this->container->make( Query_Vars::class );
	}

	/**
	 * Unregisters the filters and actions hooks added by the controller.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function unregister(): void {
		if ( ! $this->container->isBound( Query_Vars::class ) ) {
			return;
		}

		$this->container->get( Query_Vars::class )->unregister();
	}
}
