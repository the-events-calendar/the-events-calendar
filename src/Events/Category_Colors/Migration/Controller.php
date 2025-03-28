<?php
/**
 * Controller class for handling the category colors feature.
 * This class acts as the main entry point for managing the lifecycle of
 * category colors, including registering dependencies, adding filters, and
 * unregistering actions when necessary.
 *
 * @since TBD
 *
 * @package TEC\Events\Category_Colors\Migration
 */

namespace TEC\Events\Category_Colors\Migration;

use TEC\Common\Contracts\Provider\Controller as Controller_Contract;
use TEC\Common\StellarWP\AdminNotices\AdminNotices;
use TEC\Events\Category_Colors\Migration\Scheduler\Execution_Action;
use TEC\Events\Category_Colors\Migration\Scheduler\Postprocessing_Action;
use TEC\Events\Category_Colors\Migration\Scheduler\Preprocessing_Action;
use TEC\Events\Category_Colors\Migration\Scheduler\Validation_Action;
use TEC\Events\Category_Colors\Migration\Notice\Migration_Flow;
use TEC\Events\Category_Colors\Migration\Notice\Migration_Notice;

/**
 * Class Controller
 *
 * @since TBD
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
		// Add hooks to handle plugin deactivation.
		add_action( 'admin_init', [ $this, 'maybe_disable_category_colors_plugin' ] );

		// Check if we should force show the notice.
		$force_show = apply_filters( 'tec_events_category_colors_force_migration_notice', false );

		// Only skip registration if migration is completed and we're not forcing.
		if ( ! $force_show && Status::$postprocessing_completed === Status::get_migration_status()['status'] ) {
			return;
		}

		// Register action hooks.
		$this->register_action_hooks();

		// Register Migration_Flow as a singleton.
		$this->container->singleton( Migration_Flow::class );

		// Register Migration_Notice with Migration_Flow dependency.
		$this->container->singleton(
			Migration_Notice::class,
			function () {
				return new Migration_Notice( $this->container->make( Migration_Flow::class ) );
			}
		);

		$this->container->make( Migration_Notice::class )->hook();
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

		foreach ( $actions as $action_class ) {
			$action = $this->container->make( $action_class );
			add_action( $action->get_hook(), [ $action, 'execute' ] );
		}
	}

	/**
	 * Disables the Category Colors plugin and prevents it from being reactivated.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function maybe_disable_category_colors_plugin(): void {
		// Only proceed if migration is completed.
		$status = Status::get_migration_status();
		if ( Status::$postprocessing_completed !== $status['status'] ) {
			return;
		}

		// Add a filter to prevent reactivation.
		add_filter(
			'plugin_action_links_the-events-calendar-category-colors/the-events-calendar-category-colors.php',
			function ( $actions ) {
				unset( $actions['activate'] );

				return $actions;
			}
		);

		// Show notice if plugin is deactivated.
		if ( ! is_plugin_active( 'the-events-calendar-category-colors/the-events-calendar-category-colors.php' ) ) {
			$screen = get_current_screen();
			if ( $screen && 'plugins' === $screen->id ) {
				AdminNotices::show(
					'tec_category_colors_plugin_deactivated',
					sprintf(
						'<p>%s</p>',
						esc_html__( 'The Events Calendar Category Colors plugin has been deactivated because its functionality is now included in The Events Calendar core.', 'the-events-calendar' )
					)
				)
					->urgency( 'warning' )
					->dismissible( true )
					->inline( true );
			}
		}

		// Deactivate the plugin if it's still active.
		if ( is_plugin_active( 'the-events-calendar-category-colors/the-events-calendar-category-colors.php' ) ) {
			deactivate_plugins( 'the-events-calendar-category-colors/the-events-calendar-category-colors.php' );
		}
	}

	/**
	 * Unhooks actions and filters.
	 *
	 * @since TBD
	 */
	public function unregister(): void {}
}
