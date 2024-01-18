<?php

namespace TEC\Events\Blocks;

use TEC\Events\Editor\Full_Site\Event\Archive_Block_Template;
use TEC\Events\Editor\Full_Site\Event\Single_Block_Template;
use TEC\Common\Contracts\Provider\Controller as Controller_Contract;
use TEC\Events\Editor\Full_Site\Venue\Single_Block_Template as Single_Venue_Block_Template;
// @todo Ditch this - does very little, seems odd considering lift of FS/Controller ?
/**
 * Class Controller
 *
 * @since 6.2.7
 *
 * @package TEC\Events\Blocks
 */
class Controller extends Controller_Contract {
	/**
	 * Register the provider.
	 *
	 * @since 6.2.7
	 */
	public function do_register(): void {
		$this->add_actions();

		// Register the service provider itself on the container.
		$this->container->singleton( static::class, $this );
	}

	/**
	 * Unhooks actions and filters.
	 */
	public function unregister(): void {
		$this->remove_actions();
	}

	/**
	 * Adds the actions required by the Blocks components.
	 *
	 * @since 6.2.7
	 */
	protected function add_actions() {

	}

	/**
	 * Removes registered actions.
	 *
	 * @since 6.2.7
	 */
	public function remove_actions() {

	}



	/**
	 * Registers the Events Archive template.
	 *
	 * @since 6.2.7
	 */
	public function action_register_archive_template() {
		return $this->container->make( Archive_Block_Template::class )->register();
	}

	/**
	 * Registers the Single Event template.
	 *
	 * @since 6.2.7
	 */
	public function action_register_single_event_template() {
		return $this->container->make( Single_Block_Template::class )->register();
	}
}
