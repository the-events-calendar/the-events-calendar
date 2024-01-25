<?php

namespace TEC\Events\Blocks;

use TEC\Events\Block_Templates\Archive_Events\Archive_Block_Template;
use TEC\Events\Block_Templates\Single_Event\Single_Block_Template;
use TEC\Common\Contracts\Provider\Controller as Controller_Contract;
use TEC\Events\Block_Templates\Single_Venue\Single_Block_Template as Single_Venue_Block_Template;
use TEC\Events\Blocks\Single_Venue\Block;

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
		add_action( 'tribe_editor_register_blocks', [ $this, 'action_register_archive_template' ] );
		add_action( 'tribe_editor_register_blocks', [ $this, 'action_register_single_event_template' ] );
		add_action( 'tribe_editor_register_blocks', [ $this, 'register_single_venue_block' ] );
	}

	/**
	 * Removes registered actions.
	 *
	 * @since 6.2.7
	 */
	public function remove_actions() {
		remove_action( 'tribe_editor_register_blocks', [ $this, 'action_register_archive_template' ] );
		remove_action( 'tribe_editor_register_blocks', [ $this, 'action_register_single_event_template' ] );
		remove_action( 'tribe_editor_register_blocks', [ $this, 'register_single_venue_block' ] );
	}

	/**
	 * Registers the Events Archive block.
	 *
	 * @since 6.2.7
	 */
	public function action_register_archive_template() {
		return $this->container->make( Archive_Block_Template::class )->register();
	}

	/**
	 * Registers the Single Event block.
	 *
	 * @since 6.2.7
	 */
	public function action_register_single_event_template() {
		return $this->container->make( Single_Block_Template::class )->register();
	}

	/**
	 * Registers the Single Venue block.
	 *
	 * @since TBD
	 */
	public function register_single_venue_block() {
		return $this->container->make( Block::class )->register();
	}
}
