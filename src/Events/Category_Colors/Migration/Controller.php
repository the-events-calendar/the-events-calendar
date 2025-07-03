<?php
/**
 * Controller class for handling the category colors feature.
 * This class acts as the main entry point for managing the lifecycle of
 * category colors, including registering dependencies, adding filters, and
 * unregistering actions when necessary.
 *
 * @since 6.14.0
 *
 * @package TEC\Events\Category_Colors\Migration
 */

namespace TEC\Events\Category_Colors\Migration;

use TEC\Common\Contracts\Container;
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
 * @since 6.14.0
 *
 * @package TEC\Events\Category_Colors
 */
class Controller extends Controller_Contract {
	/**
	 * The migration notice instance.
	 *
	 * @since 6.14.0
	 *
	 * @var Migration_Notice
	 */
	private Migration_Notice $notice;

	/**
	 * The plugin manager instance.
	 *
	 * @since 6.14.0
	 *
	 * @var Plugin_Manager
	 */
	private Plugin_Manager $plugin_manager;

	/**
	 * Constructor for the Controller class.
	 *
	 * @since 6.14.0
	 *
	 * @param Container      $container      The container instance.
	 * @param Plugin_Manager $plugin_manager The plugin manager instance.
	 */
	public function __construct( Container $container, Plugin_Manager $plugin_manager ) {
		parent::__construct( $container );
		$this->plugin_manager = $plugin_manager;
	}

	/**
	 * Register the provider.
	 *
	 * @since 6.14.0
	 */
	protected function do_register(): void {
		$this->plugin_manager->register_legacy_hooks();

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

		// Store the notice instance.
		$this->notice = $this->container->make( Migration_Notice::class );

		$this->hook();
	}

	/**
	 * Register the action hooks for the migration process.
	 *
	 * @since 6.14.0
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
	 * @since 6.14.0
	 *
	 * @return void
	 */
	public function maybe_disable_category_colors_plugin(): void {
		// Check if the plugin is currently active.
		if ( ! $this->plugin_manager->is_old_plugin_active() ) {
			return;
		}

		// Case 1: If teccc_options doesn't exist, the plugin has never been used.
		if ( ! $this->plugin_manager->has_original_settings() ) {
			$this->plugin_manager->deactivate_plugin();

			return;
		}

		// Case 2: Check migration status.
		$status = Status::get_migration_status();
		if ( Status::$postprocessing_completed === $status['status'] ) {
			$this->plugin_manager->deactivate_plugin();

			return;
		}

		// Case 3: If no migration status exists, check if we have any category meta values.
		if ( empty( $status ) && ! $this->plugin_manager->has_category_meta() ) {
			$this->plugin_manager->deactivate_plugin();

			return;
		}

		// Show notice if plugin is deactivated.
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

	/**
	 * Unhooks actions and filters.
	 *
	 * @since 6.14.0
	 */
	public function unregister(): void {
		remove_action( 'admin_init', [ $this, 'maybe_disable_category_colors_plugin' ] );
		remove_action( 'current_screen', [ $this->notice, 'maybe_show_migration_notice' ] );
		remove_action( 'admin_post_tec_start_category_colors_migration', [ $this->notice, 'handle_migration' ] );
	}

	/**
	 * Sets up the admin UI hooks.
	 *
	 * @since 6.14.0
	 */
	public function hook(): void {
		add_action( 'admin_init', [ $this, 'maybe_disable_category_colors_plugin' ] );
		add_action( 'current_screen', [ $this->notice, 'maybe_show_migration_notice' ] );
		add_action( 'admin_post_tec_start_category_colors_migration', [ $this->notice, 'handle_migration' ] );
	}
}
