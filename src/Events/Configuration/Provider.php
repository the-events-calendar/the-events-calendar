<?php

namespace TEC\Events\Configuration;

use TEC\Common\Configuration\Configuration;
use TEC\Common\Configuration\Configuration_Loader;
use TEC\Common\Configuration\Constants_Provider;
use TEC\Common\Provider\Controller;

class Provider extends Controller {

	/**
	 * Registers Configuration provider.
	 *
	 * @since TBD
	 */
	protected function do_register(): void {
		tribe( Configuration_Loader::class )->add( new Constants_Provider() );
	}

	/**
	 * Removes provider.
	 *
	 * @since TBD
	 */
	public function unregister(): void {
		tribe()->offsetUnset( Configuration_Loader::class );
		tribe()->offsetUnset( Configuration::class );
	}
}