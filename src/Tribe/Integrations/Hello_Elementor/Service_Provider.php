<?php
/**
 * Handles The Events Calendar integration with the "Hello Elementor" theme.
 *
 * @since   TBD
 *
 * @package Tribe\Events\Integrations\Hello_Elementor
 */

namespace Tribe\Events\Integrations\Hello_Elementor;

/**
 * Class Service_Provider
 *
 * @since   TBD
 *
 * @package Tribe\Events\Integrations\Hello_Elementor
 */
class Service_Provider extends \tad_DI52_ServiceProvider {
	public function register() {
		if('hello-elementor' !== get_template()){
			return;
		}
		require_once __DIR__ . '/hello-elementor-functions.php';
	}
}
