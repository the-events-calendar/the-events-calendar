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
use TEC\Events\Category_Colors\Category\Events_Category;
use TEC\Events\Category_Colors\CSS_Generator\Generator;

/**
 * Class Controller
 *
 * @since TBD
 */
class Controller extends Controller_Contract {

	/**
	 * Register the provider.
	 *
	 * @since TBD
	 */
	public function do_register(): void {
		$this->container->singleton( Generator::class );
		$this->container->singleton( Events_Category::class );

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
