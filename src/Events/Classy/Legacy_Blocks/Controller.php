<?php
/**
 * Controller to handle Legacy blocks.
 */

declare( strict_types=1 );

namespace TEC\Events\Classy\Legacy_Blocks;

use TEC\Common\Contracts\Provider\Controller as Controller_Contract;
use Tribe__Events__Editor__Blocks__Classic_Event_Details;
use Tribe__Events__Editor__Blocks__Event_Category;
use Tribe__Events__Editor__Blocks__Event_Datetime;
use Tribe__Events__Editor__Blocks__Event_Links;
use Tribe__Events__Editor__Blocks__Event_Organizer;
use Tribe__Events__Editor__Blocks__Event_Price;
use Tribe__Events__Editor__Blocks__Event_Tags;
use Tribe__Events__Editor__Blocks__Event_Venue;
use Tribe__Events__Editor__Blocks__Event_Website;
use Tribe__Events__Editor__Blocks__Featured_Image;

/**
 * Handle Legacy blocks and block templates with classy.
 *
 * @since TBD
 */
class Controller extends Controller_Contract {

	/**
	 * Registers the filters and actions hooks added by the controller.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function do_register(): void {
		$this->container->singleton( 'events.editor.template', 'Tribe__Events__Editor__Template' );
		$this->container->singleton( 'events.editor.template.overwrite', 'Tribe__Events__Editor__Template__Overwrite' );
		tribe( 'events.editor.template.overwrite' )->hook();

		// Register blocks classes.
		$this->container->singleton( 'events.editor.blocks.classic-event-details', Tribe__Events__Editor__Blocks__Classic_Event_Details::class, [ 'load' ] );
		$this->container->singleton( 'events.editor.blocks.event-datetime', Tribe__Events__Editor__Blocks__Event_Datetime::class, [ 'load' ] );
		$this->container->singleton( 'events.editor.blocks.event-venue', Tribe__Events__Editor__Blocks__Event_Venue::class, [ 'load' ] );
		$this->container->singleton( 'events.editor.blocks.event-organizer', Tribe__Events__Editor__Blocks__Event_Organizer::class, [ 'load' ] );
		$this->container->singleton( 'events.editor.blocks.event-links', Tribe__Events__Editor__Blocks__Event_Links::class, [ 'load' ] );
		$this->container->singleton( 'events.editor.blocks.event-price', Tribe__Events__Editor__Blocks__Event_Price::class, [ 'load' ] );
		$this->container->singleton( 'events.editor.blocks.event-category', Tribe__Events__Editor__Blocks__Event_Category::class, [ 'load' ] );
		$this->container->singleton( 'events.editor.blocks.event-tags', Tribe__Events__Editor__Blocks__Event_Tags::class, [ 'load' ] );
		$this->container->singleton( 'events.editor.blocks.event-website', Tribe__Events__Editor__Blocks__Event_Website::class, [ 'load' ] );
		$this->container->singleton( 'events.editor.blocks.featured-image', Tribe__Events__Editor__Blocks__Featured_Image::class, [ 'load' ] );

		add_action( 'tribe_editor_register_blocks', [ tribe( 'events.editor.blocks.classic-event-details' ),  'register' ] );
		add_action( 'tribe_editor_register_blocks', [ tribe( 'events.editor.blocks.event-datetime' ), 'register' ] );
		add_action( 'tribe_editor_register_blocks', [ tribe( 'events.editor.blocks.event-venue' ), 'register' ] );
		add_action( 'tribe_editor_register_blocks', [ tribe( 'events.editor.blocks.event-organizer' ), 'register' ] );
		add_action( 'tribe_editor_register_blocks', [ tribe( 'events.editor.blocks.event-links' ), 'register' ] );
		add_action( 'tribe_editor_register_blocks', [ tribe( 'events.editor.blocks.event-price' ), 'register' ] );
		add_action( 'tribe_editor_register_blocks', [ tribe( 'events.editor.blocks.event-category' ), 'register' ] );
		add_action( 'tribe_editor_register_blocks', [ tribe( 'events.editor.blocks.event-tags' ), 'register' ] );
		add_action( 'tribe_editor_register_blocks', [ tribe( 'events.editor.blocks.event-website' ), 'register' ] );
		add_action( 'tribe_editor_register_blocks', [ tribe( 'events.editor.blocks.featured-image' ), 'register' ] );
	}

	/**
	 * Un-registers the filters and actions hooks added by the controller.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function unregister(): void {
		$this->container->get( 'events.editor.template' )->unregister();
		$this->container->get( 'events.editor.template.overwrite' )->unregister();

		// Unregister the blocks.
		$this->container->get( 'events.editor.blocks.classic-event-details' )->unregister();
		$this->container->get( 'events.editor.blocks.event-datetime' )->unregister();
		$this->container->get( 'events.editor.blocks.event-venue' )->unregister();
		$this->container->get( 'events.editor.blocks.event-organizer' )->unregister();
		$this->container->get( 'events.editor.blocks.event-links' )->unregister();
		$this->container->get( 'events.editor.blocks.event-price' )->unregister();
		$this->container->get( 'events.editor.blocks.event-category' )->unregister();
		$this->container->get( 'events.editor.blocks.event-tags' )->unregister();
		$this->container->get( 'events.editor.blocks.event-website' )->unregister();
		$this->container->get( 'events.editor.blocks.featured-image' )->unregister();

		// Remove the actions.
		remove_action( 'tribe_editor_register_blocks', [ tribe( 'events.editor.blocks.classic-event-details' ),  'register' ] );
		remove_action( 'tribe_editor_register_blocks', [ tribe( 'events.editor.blocks.event-datetime' ), 'register' ] );
		remove_action( 'tribe_editor_register_blocks', [ tribe( 'events.editor.blocks.event-venue' ), 'register' ] );
		remove_action( 'tribe_editor_register_blocks', [ tribe( 'events.editor.blocks.event-organizer' ), 'register' ] );
		remove_action( 'tribe_editor_register_blocks', [ tribe( 'events.editor.blocks.event-links' ), 'register' ] );
		remove_action( 'tribe_editor_register_blocks', [ tribe( 'events.editor.blocks.event-price' ), 'register' ] );
		remove_action( 'tribe_editor_register_blocks', [ tribe( 'events.editor.blocks.event-category' ), 'register' ] );
		remove_action( 'tribe_editor_register_blocks', [ tribe( 'events.editor.blocks.event-tags' ), 'register' ] );
		remove_action( 'tribe_editor_register_blocks', [ tribe( 'events.editor.blocks.event-website' ), 'register' ] );
		remove_action( 'tribe_editor_register_blocks', [ tribe( 'events.editor.blocks.featured-image' ), 'register' ] );
	}
}
