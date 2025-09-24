<?php
/**
 * Controller for request-level sanitization in TEC.
 *
 * @since TBD
 *
 * @package TEC\Events\Request
 */

namespace TEC\Events\Request;

use TEC\Common\Contracts\Provider\Controller as Controller_Contract;
use TEC\Events\Request\Ical;

/**
 * Class Controller
 *
 * @since TBD
 *
 * @package TEC\Events\Request
 */
class Controller extends Controller_Contract {
	/**
	 * Register the controller.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	protected function do_register(): void {
		$this->container->register( Ical::class );
	}

	/**
	 * Unregister the controller.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function unregister(): void {
		$this->container->get( Ical::class )->unregister();
	}
}
