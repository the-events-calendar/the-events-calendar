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
use TEC\Events\Category_Colors\Admin\Controller as Admin_Controller;
use TEC\Events\Category_Colors\Repositories\Category_Color_Dropdown_Provider;
use TEC\Events\Category_Colors\Repositories\Category_Color_Priority_Category_Provider;
use TEC\Events\Category_Colors\Settings\Settings;
use Tribe\Events\Views\V2\View;
use WP_Post;

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
		$this->container->register_on_action( 'tribe_plugins_loaded', Admin_Controller::class );
		$this->container->register_on_action( 'tribe_plugins_loaded', Migration\Controller::class );
		$this->container->register_on_action( 'tribe_plugins_loaded', CSS\Controller::class );

		/** @var Settings $instance */
		$instance = $this->container->make( Settings::class );
		$instance->add_hooks();

		add_filter( 'tec_events_views_v2_view_template_vars', [ $this, 'add_category_colors_vars' ], 10, 2 );
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

	/**
	 * Adds category color variables to the view template.
	 *
	 * @since TBD
	 *
	 * @param array<string,mixed> $template_vars The template variables.
	 * @param View                $view          The current view instance.
	 *
	 * @return array<string,mixed> The modified template variables.
	 */
	public function add_category_colors_vars( array $template_vars, View $view ): array {
		$event = tribe_get_event();

		$template_vars['category_colors_priority_category'] = ( $event instanceof WP_Post )
			? tribe( Category_Color_Priority_Category_Provider::class )->get_highest_priority_category( $event )
			: [];

		$template_vars['category_colors_enabled']           = tribe( Category_Color_Dropdown_Provider::class )->should_display_on_view( $view );
		$template_vars['category_colors_category_dropdown'] = tribe( Category_Color_Dropdown_Provider::class )->get_dropdown_categories();
		$template_vars['category_colors_super_power']       = tribe_get_option( 'category-color-legend-superpowers', false );
		$template_vars['category_colors_show_reset_button'] = tribe_get_option( 'category-color-reset-button', false );

		return $template_vars;
	}
}
