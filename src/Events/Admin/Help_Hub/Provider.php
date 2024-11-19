<?php
/**
 * Service Provider for interfacing with TEC\Events\Admin\Notice\Help_Hub.
 *
 * @since
 *
 * @package TEC\Events\Admin\Notice\Help_Hub
 */

namespace TEC\Events\Admin\Help_Hub;

use TEC\Common\Contracts\Service_Provider;

/**
 * Class Provider
 *
 * @since
 *
 * @package TEC\Events\Admin\Notice\Help_Hub
 */
class Provider extends Service_Provider {

	/**
	 * Register implementations.
	 *
	 * @since
	 */
	public function register() {
		$this->container->singleton( self::class, $this );
		$this->container->bind( TEC_Hub_Resource_Data::class );
	}
}
