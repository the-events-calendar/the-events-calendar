<?php
/**
 * Service Provider for interfacing with TEC\Common\phpqrcode
 *
 * @since   TBD
 *
 * @package TEC\Events\QR
 */

namespace TEC\Events\QR;

use TEC\Common\Contracts\Service_Provider;

/**
 * Class Provider
 *
 * @since   TBD
 * @package TEC\Events\QR
 */
class Provider extends Service_Provider {

	/**
	 * Handles the registering of the provider.
	 *
	 * @since TBD
	 */
	public function register() {
		$this->container->register_on_action( 'tec_qr_code_loaded', Controller::class );
	}
}
