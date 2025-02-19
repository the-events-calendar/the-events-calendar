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
use TEC\Events\Category_Colors\Migration\Logger;
use TEC\Events\Category_Colors\Migration\Migration_Process;
use TEC\Events\Category_Colors\Migration\Migration_Runner;
use TEC\Events\Category_Colors\Migration\Migration_Trait;
use TEC\Events\Category_Colors\Migration\Post_Processor;
use TEC\Events\Category_Colors\Migration\Pre_Processor;
use TEC\Events\Category_Colors\Migration\Validator;

/**
 * Class Controller
 *
 * @since   TBD
 *
 * @package TEC\Events\Category_Colors
 */
class Controller extends Controller_Contract {

	use Migration_Trait;

	/**
	 * Register the provider.
	 *
	 * @since TBD
	 */
	public function do_register(): void {
		$this->container->bind( Event_Category_Meta::class );
		$this->container->singleton( Pre_Processor::class );
		$this->container->singleton( Validator::class );
		$this->container->singleton( Migration_Runner::class );
		$this->container->singleton( Post_Processor::class );
		$this->container->singleton( Logger::class );
		$this->container->singleton( Migration_Process::class );

		$this->add_filters();
	}

	/**
	 * Unhooks actions and filters.
	 *
	 * @since TBD
	 */
	public function unregister(): void {
		$this->remove_filters();
	}

	/**
	 * Adds the filters required.
	 *
	 * @since TBD
	 */
	protected function add_filters() {}

	/**
	 * Removes registered filters.
	 *
	 * @since TBD
	 */
	public function remove_filters() {}
}
