<?php

namespace TEC\Events\Blocks;

use TEC\Events\Editor\Full_Site\Archive_Block_Template;
use TEC\Events\Editor\Full_Site\Single_Block_Template;
use TEC\Common\Contracts\Provider\Controller as Controller_Contract;


/**
 * Class Controller
 *
 * @since   TBD
 *
 * @package TEC\Events\Blocks
 */
class Controller extends Controller_Contract {
	/**
	 * Register the provider.
	 *
	 * @since TBD
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
	 * @since TBD
	 */
	protected function add_actions() {
		add_action( 'tribe_editor_register_blocks', [ $this, 'action_register_archive_template' ] );
		add_action( 'tribe_editor_register_blocks', [ $this, 'action_register_single_event_template' ] );
	}

	/**
	 * Removes registered actions.
	 *
	 * @since TBD
	 */
	public function remove_actions() {
		remove_action( 'tribe_editor_register_blocks', [ $this, 'action_register_archive_template' ] );
		remove_action( 'tribe_editor_register_blocks', [ $this, 'action_register_single_event_template' ] );
	}

	/**
	 * Registers the Events Archive template.
	 *
	 * @since TBD
	 */
	public function action_register_archive_template() {
		return $this->container->make( Archive_Block_Template::class )->register();
	}

	/**
	 * Registers the Single Event template.
	 *
	 * @since TBD
	 */
	public function action_register_single_event_template() {
		return $this->container->make( Single_Block_Template::class )->register();
	}
}
