<?php
/**
 * Controller class for handling the category colors feature.
 * This class acts as the main entry point for managing the lifecycle of
 * category colors, including registering dependencies, adding filters, and
 * unregistering actions when necessary.
 *
 * @since   TBD
 *
 * @package TEC\Events\Category_Colors\Migration
 */

namespace TEC\Events\Category_Colors\Migration;

use TEC\Common\Contracts\Provider\Controller as Controller_Contract;
use TEC\Events\Category_Colors\Event_Category_Meta;
use TEC\Events\Category_Colors\Migration\Scheduler\Execution_Action;
use TEC\Events\Category_Colors\Migration\Scheduler\Postprocessing_Action;
use TEC\Events\Category_Colors\Migration\Scheduler\Preprocessing_Action;
use TEC\Events\Category_Colors\Migration\Scheduler\Validation_Action;

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
		if ( Status::$execution_completed === Handler::get_migration_status()['status'] ) {
			return;
		}
		$this->container->singleton( Pre_Processor::class );
		$this->container->singleton( Validator::class );
		$this->container->singleton( Worker::class );
		$this->container->singleton( Post_Processor::class );
		$this->container->singleton( Handler::class );
		$this->container->singleton( Execution_Action::class );
		$this->container->singleton( Preprocessing_Action::class );
		$this->container->singleton( Validation_Action::class );
		$this->container->singleton( Postprocessing_Action::class );
		
		// Register action hooks
		$this->register_action_hooks();
		
		$this->add_filters();
	}

	/**
	 * Register the action hooks for the migration process.
	 *
	 * @since TBD
	 */
	public function register_action_hooks(): void {
		$actions = [
			Preprocessing_Action::class,
			Validation_Action::class,
			Execution_Action::class,
			Postprocessing_Action::class,
		];

		foreach ($actions as $action_class) {
			$action = $this->container->make($action_class);
			add_action( $action->get_hook(), [ $action, 'execute' ] );
		}
	}

	/**
	 * Adds the filters required.
	 *
	 * @since TBD
	 */
	public function add_filters() {
		$this->container->make( Admin_UI::class )->hook();
	}

	/**
	 * Unhooks actions and filters.
	 *
	 * @since TBD
	 */
	public function unregister(): void {
	}
}
