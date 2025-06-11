<?php
/**
 * Service Provider for interfacing with TEC\Common\Admin\Notice.
 *
 * @since
 *
 * @package TEC\Events\Admin\Notice
 */

namespace TEC\Events\Admin\Notice;

use TEC\Common\Contracts\Service_Provider;

/**
 * Class Provider
 *
 * @since
 *
 * @package TEC\Events\Admin\Notice
 */
class Provider extends Service_Provider {

	/**
	 * Register implementations.
	 *
	 * @since
	 */
	public function register() {
		$this->container->singleton( Rest_Api::class, Rest_Api::class );

		$this->add_actions();
		$this->add_filters();
	}

	/**
	 * Add the action hooks.
	 *
	 * @since
	 */
	public function add_actions() {
		$this->container->make( Rest_Api::class )->hook();
	}


	/**
	 * Add the filter hooks.
	 *
	 * @since
	 */
	public function add_filters() {
	}
}
