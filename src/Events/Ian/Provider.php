<?php
/**
 * Service Provider for interfacing with TEC\Common\Ian.
 *
 * @since   TBD
 *
 * @package TEC\Events\Ian
 */

namespace TEC\Events\Ian;

use TEC\Common\Contracts\Service_Provider;

 /**
  * Class Provider
  *
  * @since   TBD
  * @package TEC\Events\Ian
  */
class Provider extends Service_Provider {

	/**
	 * Handles the registering of the provider.
	 *
	 * @since TBD
	 */
	public function register() {
		$this->add_actions();
	}

	/**
	 * Handles the action hooks for this module.
	 *
	 * @since TBD
	 */
	public function add_actions() {
		add_action( 'admin_footer', [ $this, 'action_inject_ian_icon' ] );
	}


	/**
	 * Conditionally injects the hook to trigger the Telemetry modal.
	 *
	 * @since TBD
	 */
	public function action_inject_ian_icon() {
		return $this->container->get( Ian_Client::class )->inject_ian_icon();
	}
}
