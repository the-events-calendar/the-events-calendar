<?php
/**
 * Controller class for handling the category colors feature.
 * This class acts as the main entry point for managing the lifecycle of
 * category colors, including registering dependencies, adding filters, and
 * unregistering actions when necessary.
 *
 * @since   TBD
 *
 * @package TEC\Events\Category_Colors
 */

namespace TEC\Events\Category_Colors;

use TEC\Common\Contracts\Provider\Controller as Controller_Contract;
use TEC\Events\Category_Colors\Settings\Settings;

/**
 * Class Controller
 *
 * @since   TBD
 *
 * @package TEC\Events\Category_Colors
 */
class Controller extends Controller_Contract {

	/**
	 * Register the provider.
	 *
	 * @since TBD
	 */
	public function do_register(): void {
		$this->container->register_on_action( 'tribe_plugins_loaded', Admin\Controller::class );
		$this->container->register_on_action( 'tribe_plugins_loaded', CSS\Controller::class );

		/** @var Settings $instance */
		$instance = $this->container->make( Settings::class );
		$instance->add_hooks();
	}

	/**
	 * Unhooks actions and filters.
	 *
	 * @since TBD
	 */
	public function unregister(): void {
		/** @var Admin\Controller $admin_controller */
		$admin_controller = $this->container->make( Admin\Controller::class );
		$admin_controller->unregister();

		/** @var Settings $settings */
		$settings = $this->container->make( Settings::class );
		$settings->unregister();
	}
}
