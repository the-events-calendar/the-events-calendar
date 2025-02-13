<?php
/**
 * Manages the External Calendar Embeds Feature.
 *
 * @since TBD
 *
 * @package TEC\Events\Calendar_Embeds
 */

namespace TEC\Events\Calendar_Embeds;

use TEC\Common\Contracts\Provider\Controller as Controller_Contract;

/**
 * Class Controller
 *
 * @since TBD

 * @package TEC\Events\Calendar_Embeds
 */
class Controller extends Controller_Contract {

	/**
	 * @inheritDoc
	 */
	public function do_register(): void {
		$this->container->singleton( Calendar_Embeds::class );

		$this->add_actions();
		$this->add_filters();
	}

	/**
	 * @inheritDoc
	 */
	public function unregister(): void {
		$this->remove_actions();
		$this->remove_filters();
	}

	/**
	 * Add actions for the feature.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function add_actions() {
		add_action( 'init', [ $this, 'register_post_type' ] );
		add_action( 'admin_menu', [ $this, 'register_menu_item' ], 11 );
	}

	/**
	 * Add actions for the feature.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function remove_actions() {
		remove_action( 'init', [ $this, 'register_post_type' ] );
		remove_action( 'admin_menu', [ $this, 'register_menu_item' ], 11 );
	}

	/**
	 * Add filters for the feature.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function add_filters() {
		add_filter( 'submenu_file', [ $this, 'keep_parent_menu_open' ] );
	}

	/**
	 * Remove filters for the feature.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function remove_filters() {
		remove_filter( 'submenu_file', [ $this, 'keep_parent_menu_open' ] );
	}

	/**
	 * Register custom post type.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function register_post_type() {
		$this->container->make( Calendar_Embeds::class )->register_post_type();
	}

	/**
	 * Create menu item.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function register_menu_item() {
		$this->container->make( Calendar_Embeds::class )->register_menu_item();
	}

	/**
	 * Keep parent menu open when viewing the calendar embeds page.
	 *
	 * @since TBD
	 *
	 * @param string $submenu_file The submenu file.
	 *
	 * @return string
	 */
	public function keep_parent_menu_open( $submenu_file ) {
		return $this->container->make( Calendar_Embeds::class )->keep_parent_menu_open( $submenu_file );
	}
}
