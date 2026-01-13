<?php
/**
 * Controller class for handling the category colors feature.
 * This class acts as the main entry point for managing the lifecycle of
 * category colors, including registering dependencies, adding filters, and
 * unregistering actions when necessary.
 *
 * @since 6.14.0
 *
 * @package TEC\Events\Category_Colors
 */

namespace TEC\Events\Category_Colors;

use TEC\Common\Contracts\Provider\Controller as Controller_Contract;
use TEC\Events\Category_Colors\Admin\Controller as Admin_Controller;
use TEC\Events\Category_Colors\Migration\Plugin_Manager;
use TEC\Events\Category_Colors\Repositories\Category_Color_Dropdown_Provider;
use TEC\Events\Category_Colors\Repositories\Category_Color_Priority_Category_Provider;
use TEC\Events\Category_Colors\Settings\Settings;
use TEC\Events\Category_Colors\Migration\Controller as Migration_Controller;
use TEC\Events\Category_Colors\CSS\Generator;
use Tribe\Events\Views\V2\View;
use Tribe__Events__Main;

/**
 * Class Controller
 *
 * @since 6.14.0
 *
 * @package TEC\Events\Category_Colors
 */
class Controller extends Controller_Contract {

	const CATEGORY_TEMPLATE_VIEWS = [
		'events/v2/list/event/category',
		'events/v2/day/event/category',
		'events-pro/v2/map/event-cards/event-card/event/category',
		'events-pro/v2/photo/event/category',
	];

	/**
	 * Whether the controller is active or not.
	 *
	 * @since 6.14.0
	 *
	 * @return bool Whether the controller is active or not.
	 */
	public function is_active(): bool {
		/**
		 * Filters whether the Category Colors feature is globally enabled.
		 *
		 * @since 6.14.0
		 *
		 * @param bool $enabled Whether the Category Colors feature should be enabled.
		 */
		return (bool) apply_filters( 'tec_events_category_colors_enabled', true );
	}

	/**
	 * Register the provider.
	 *
	 * @since 6.14.0
	 */
	protected function do_register(): void {
		$plugin_manager = $this->container->make( Plugin_Manager::class );

		if ( $plugin_manager->should_show_migration_controller() ) {
			$this->container->register_on_action( 'tribe_plugins_loaded', Migration_Controller::class );
			return;
		}

		// Ensure the old Category Colors plugin is disabled and kept disabled.
		if ( $plugin_manager->is_old_plugin_active() ) {
			$plugin_manager->deactivate_plugin();
		}

		// Register admin, migration, and CSS controllers.
		$this->container->register_on_action( 'tribe_plugins_loaded', Admin_Controller::class );
		$this->container->register_on_action( 'tribe_plugins_loaded', CSS\Controller::class );

		/** @var Settings $instance */
		$instance = $this->container->make( Settings::class );
		$instance->add_hooks();

		// Add filters for template variables and category data.
		add_filter( 'tec_events_views_v2_view_template_vars', [ $this, 'add_category_colors_vars' ], 10, 2 );
		add_filter( 'plugin_action_links_the-events-calendar-category-colors/the-events-calendar-category-colors.php', [ $plugin_manager, 'prevent_original_plugin_reactivation' ] );

		foreach ( self::CATEGORY_TEMPLATE_VIEWS as $template ) {
			add_filter( "tribe_template_context:{$template}", [ $this, 'add_category_data' ] );
		}

		// Add cache busting hooks for the dropdown provider.
		$dropdown_provider = tribe( Category_Color_Dropdown_Provider::class );
		add_action( 'created_' . Tribe__Events__Main::TAXONOMY, [ $dropdown_provider, 'bust_dropdown_categories_cache' ] );
		add_action( 'edited_' . Tribe__Events__Main::TAXONOMY, [ $dropdown_provider, 'bust_dropdown_categories_cache' ] );
		add_action( 'delete_' . Tribe__Events__Main::TAXONOMY, [ $dropdown_provider, 'bust_dropdown_categories_cache' ] );
		add_action( 'tec_events_category_colors_css_regenerated', [ $dropdown_provider, 'bust_dropdown_categories_cache' ] );
	}

	/**
	 * Unhooks actions and filters.
	 *
	 * @since 6.14.0
	 */
	public function unregister(): void {
		/** @var Admin\Controller $admin_controller */
		$admin_controller = $this->container->make( Admin\Controller::class );
		$admin_controller->unregister();

		/** @var Settings $settings */
		$settings = $this->container->make( Settings::class );
		$settings->unregister();

		// Remove cache busting hooks for the dropdown provider.
		$dropdown_provider = tribe( Category_Color_Dropdown_Provider::class );
		remove_action( 'created_' . Tribe__Events__Main::TAXONOMY, [ $dropdown_provider, 'bust_dropdown_categories_cache' ] );
		remove_action( 'edited_' . Tribe__Events__Main::TAXONOMY, [ $dropdown_provider, 'bust_dropdown_categories_cache' ] );
		remove_action( 'delete_' . Tribe__Events__Main::TAXONOMY, [ $dropdown_provider, 'bust_dropdown_categories_cache' ] );
		remove_action( 'tec_events_category_colors_css_regenerated', [ $dropdown_provider, 'bust_dropdown_categories_cache' ] );
	}

	/**
	 * Adds category color variables to the view template.
	 *
	 * @since 6.14.0
	 * @since 6.15.14 Unset the category colors context if no colors are enabled.
	 *
	 * @param array<string,mixed> $template_vars The template variables.
	 * @param View                $view          The current view instance.
	 *
	 * @return array<string,mixed> The modified template variables.
	 */
	public function add_category_colors_vars( array $template_vars, View $view ): array {
		$dropdown_provider = tribe( Category_Color_Dropdown_Provider::class );
		$categories        = $dropdown_provider->get_dropdown_categories();

		// Early bail if frontend UI should not be displayed.
		if ( ! $this->should_show_frontend_ui() ) {
			return $template_vars;
		}

		// Check if feature is enabled for view and categories have colors.
		$template_vars['category_colors_enabled']           = $dropdown_provider->should_display_on_view( $view ) && ! empty( $categories );
		$template_vars['category_colors_category_dropdown'] = $categories;
		$template_vars['category_colors_super_power']       = tribe_get_option( 'category-color-legend-superpowers', false );
		$template_vars['category_colors_show_reset_button'] = tribe_get_option( 'category-color-reset-button', false );

		return $template_vars;
	}

	/**
	 * Adds category data to the template context.
	 *
	 * @since 6.14.0
	 *
	 * @param array<string,mixed> $context The template context.
	 *
	 * @return array<string,mixed> The modified template context with category data.
	 */
	public function add_category_data( $context ) {
		// Early bail if frontend UI should not be displayed.
		if ( ! $this->should_show_frontend_ui() ) {
			return $context;
		}

		$event = tribe_get_event();
		if ( ! $event ) {
			return $context;
		}

		$category_data = tribe( Category_Color_Priority_Category_Provider::class )->get_highest_priority_category_with_meta( $event );

		if ( ! $category_data ) {
			unset( $context['category_colors_priority_category'], $context['category_colors_meta'] );
			return $context;
		}

		$context['category_colors_priority_category'] = $category_data['category'];
		$context['category_colors_meta']              = $category_data['meta'];

		return $context;
	}

	/**
	 * Determines if the Category Colors frontend UI should be displayed.
	 *
	 * @since 6.14.0
	 * @since 6.14.2 Added new UI option to enable/disable the frontend UI.
	 *
	 * @return bool True if the frontend UI should be displayed, false otherwise.
	 */
	public function should_show_frontend_ui(): bool {
		// Check if the user has disabled frontend display via the UI option.
		// This takes precedence over any filter or technical availability.
		if ( ! tribe_get_option( 'category-color-enable-frontend', true ) ) {
			return false;
		}

		/**
		 * Filters whether the Category Colors frontend UI should be displayed.
		 *
		 * Note: This filter only applies when the frontend display option is enabled.
		 * The UI option takes precedence over this filter.
		 *
		 * @since 6.14.0
		 *
		 * @param bool $show_frontend_ui Whether the frontend UI should be displayed.
		 */
		return (bool) apply_filters( 'tec_events_category_colors_show_frontend_ui', $this->is_in_use() );
	}

	/**
	 * Checks if the Category Colors feature is in use.
	 *
	 * @since 6.14.0
	 *
	 * @return bool True if the Category Colors feature is in use, false otherwise.
	 */
	public function is_in_use(): bool {
		return (bool) get_option( $this->container->get( Generator::class )->get_option_key(), '' );
	}
}
