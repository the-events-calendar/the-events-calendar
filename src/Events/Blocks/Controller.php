<?php

namespace TEC\Events\Blocks;

use TEC\Events\Blocks\Archive_Events\Block as Archive_Events_Block;
use TEC\Events\Blocks\Single_Event\Block as Single_Event_Block;
use TEC\Common\Contracts\Provider\Controller as Controller_Contract;
use TEC\Events\Blocks\Single_Venue\Block as Single_Venue_Block;
use TEC\Events\Blocks\Single_Organizer\Block as Single_Organizer_Block;

/**
 * Class Controller
 *
 * @since TBD Decoupled from Block Templates, focusing on Block requirements and a cleaner separation of concerns.
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
		add_action( 'tribe_editor_register_blocks', [ $this, 'register_single_venue_block' ] );
		add_action( 'tribe_editor_register_blocks', [ $this, 'register_single_organizer_block' ] );
	}

	/**
	 * Removes registered actions.
	 *
	 * @since 6.2.7
	 */
	public function remove_actions() {
		remove_action( 'tribe_editor_register_blocks', [ $this, 'register_archive_events_block' ] );
		remove_action( 'tribe_editor_register_blocks', [ $this, 'register_single_event_block' ] );
		remove_action( 'tribe_editor_register_blocks', [ $this, 'register_single_venue_block' ] );
		remove_action( 'tribe_editor_register_blocks', [ $this, 'register_single_organizer_block' ] );
	}

	/**
	 * Registers the Events Archive block.
	 *
	 * @since 6.2.7
	 */
	public function register_archive_events_block() {
		return $this->container->make( Archive_Events_Block::class )->register();
	}

	/**
	 * Registers the Single Event block.
	 *
	 * @since 6.2.7
	 */
	public function register_single_event_block() {
		return $this->container->make( Single_Event_Block::class )->register();
	}

	/**
	 * Registers the Single Venue block.
	 *
	 * @since TBD
	 */
	public function register_single_venue_block() {
		return $this->container->make( Single_Venue_Block::class )->register();
	}

	/**
	 * Registers the Single Organizer block.
	 *
	 * @since TBD
	 */
	public function register_single_organizer_block() {
		return $this->container->make( Single_Organizer_Block::class )->register();
	}
}
