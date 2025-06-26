<?php
/**
 * Controller class for handling the category colors feature.
 * This class acts as the main entry point for managing the lifecycle of
 * category colors, including registering dependencies, adding filters, and
 * unregistering actions when necessary.
 *
 * @since TBD
 *
 * @package TEC\Events\Category_Colors
 */

namespace TEC\Events\Category_Colors\CSS;

use TEC\Common\Contracts\Provider\Controller as Controller_Contract;
use TEC\Events\Category_Colors\Repositories\Category_Color_Dropdown_Provider;

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
	protected function do_register(): void {
		add_action( 'tec_events_category_colors_saved', [ $this, 'generate_css' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_frontend_scripts' ] );
	}

	/**
	 * Generates and saves the category color CSS.
	 *
	 * @since TBD
	 */
	public function generate_css() {
		$this->container->make( Generator::class )->generate_and_save_css();
		
		// Bust the dropdown categories cache when CSS is regenerated.
		/** @var Category_Color_Dropdown_Provider $dropdown_provider */
		$dropdown_provider = tribe( Category_Color_Dropdown_Provider::class );
		$dropdown_provider->bust_dropdown_categories_cache();
	}

	/**
	 * Enqueues the frontend styles for category colors.
	 *
	 * @since TBD
	 */
	public function enqueue_frontend_scripts() {
		$this->container->make( Assets::class )->enqueue_frontend_scripts();
	}

	/**
	 * Unhooks actions and filters.
	 *
	 * @since TBD
	 */
	public function unregister(): void {
		remove_action( 'tec_events_category_colors_saved', [ $this, 'generate_css' ] );
		remove_action( 'wp_enqueue_scripts', [ $this, 'enqueue_frontend_scripts' ] );
	}
}
