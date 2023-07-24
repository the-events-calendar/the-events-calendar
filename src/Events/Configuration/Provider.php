<?php

namespace TEC\Events\Configuration;

use TEC\Common\Configuration\Configuration;
use TEC\Common\Configuration\Configuration_Loader;
use TEC\Common\Configuration\Constants_Provider;
use TEC\Common\Contracts\Service_Provider;

class Provider extends Service_Provider {

	/**
	 * Registers Configuration provider.
	 *
	 * @since 6.1.3
	 */
	public function register(): void {
		tribe( Configuration_Loader::class )->add( new Constants_Provider() );
	}

	/**
	 * Removes provider.
	 *
	 * @since 6.1.3
	 */
	public function unregister(): void {
		tribe()->offsetUnset( Configuration_Loader::class );
		tribe()->offsetUnset( Configuration::class );
	}
}
