<?php

namespace TEC\Events\Blocks;

use TEC\Events\Blocks\Archive_Events\Block as Archive_Events_Block;
use TEC\Events\Blocks\Single_Event\Block as Single_Event_Block;
use TEC\Common\Contracts\Provider\Controller as Controller_Contract;

/**
 * Class Controller
 *
 * @since   6.3.3 Decoupled from Block Templates, focusing on Block requirements and a cleaner separation of concerns.
 * @since   6.2.7
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
		add_action( 'tribe_editor_register_blocks', [ $this, 'register_archive_events_block' ] );
		add_action( 'tribe_editor_register_blocks', [ $this, 'register_single_event_block' ] );
	}

	/**
	 * Removes registered actions.
	 *
	 * @since 6.2.7
	 */
	public function remove_actions() {
		remove_action( 'tribe_editor_register_blocks', [ $this, 'register_archive_events_block' ] );
		remove_action( 'tribe_editor_register_blocks', [ $this, 'register_single_event_block' ] );
	}

	/**
	 * Registers the Events Archive block.
	 *
	 * @since 6.2.7
	 * @since 6.3.3 Renamed function.
	 */
	public function register_archive_events_block() {
		return $this->container->make( Archive_Events_Block::class )->register();
	}

	/**
	 * Registers the Single Event block.
	 *
	 * @since 6.2.7
	 * @since 6.3.3 Renamed function.
	 */
	public function register_single_event_block() {
		return $this->container->make( Single_Event_Block::class )->register();
	}

	/**
	 * Registers the Events Archive template.
	 *
	 * @since      6.2.7
	 * @deprecated 6.3.3
	 */
	public function action_register_archive_template() {
		_deprecated_function( __FUNCTION__, '6.3.3' );

		return $this->register_archive_events_block();
	}

	/**
	 * Registers the Single Event template.
	 *
	 * @since      6.2.7
	 * @deprecated 6.3.3
	 */
	public function action_register_single_event_template() {
		_deprecated_function( __FUNCTION__, '6.3.3' );

		return $this->register_single_event_block();
	}
}
