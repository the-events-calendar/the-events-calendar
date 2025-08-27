<?php
/**
 * Controller for the Events V1 REST API.
 *
 * @since 6.15.0
 *
 * @package TEC\Events\REST\TEC\V1
 */

declare( strict_types=1 );

namespace TEC\Events\REST\TEC\V1;

use TEC\Common\Contracts\Provider\Controller as Controller_Contract;

/**
 * Controller for the Events V1 REST API.
 *
 * @since 6.15.0
 *
 * @package TEC\Events\REST\TEC\V1
 */
class Controller extends Controller_Contract {
	/**
	 * Registers the filters and actions hooks added by the controller.
	 *
	 * @since 6.15.0
	 *
	 * @return void
	 */
	protected function do_register(): void {
		$this->container->register( Endpoints::class );
	}

	/**
	 * Unregisters the filters and actions hooks added by the controller.
	 *
	 * @since 6.15.0
	 *
	 * @return void
	 */
	public function unregister(): void {
		$this->container->get( Endpoints::class )->unregister();
	}
}
