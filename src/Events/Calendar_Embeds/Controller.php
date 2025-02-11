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
	}

	/**
	 * @inheritDoc
	 */
	public function unregister(): void {
		$this->remove_actions();
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
		add_action( 'admin_menu', [ $this, 'register_menu_item' ] );
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
		remove_action( 'admin_menu', [ $this, 'register_menu_item' ] );
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
	 */
	public function register_menu_item() {
		$this->container->make( Calendar_Embeds::class )->register_menu_item();
	}
}
