<?php
/**
 * Controller to handle Legacy blocks.
 */

declare( strict_types=1 );

namespace TEC\Events\Classy\Legacy_Blocks;

use TEC\Common\Contracts\Provider\Controller as Controller_Contract;
use Tribe__Editor;
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
use Tribe__Events__Editor__Template;
use Tribe__Events__Editor__Template__Overwrite;

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
		if ( ! tribe_context()->doing_php_initial_state() ) {
			return;
		}

		$this->container->singleton( 'editor', 'Tribe__Editor' );
		$this->container->singleton( 'events.editor.template', 'Tribe__Events__Editor__Template' );
		$this->container->singleton( 'events.editor.template.overwrite', 'Tribe__Events__Editor__Template__Overwrite' );
		tribe( 'events.editor.template.overwrite' )->hook();

		// Register blocks singletons.
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

		add_action( 'tribe_editor_register_blocks', [ $this, 'register_blocks' ] );
	}

	/**
	 * Un-registers the filters and actions hooks added by the controller.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function unregister(): void {
		if ( $this->container->isBound( 'editor' ) && $this->container->get( 'editor' ) instanceof Tribe__Editor ) {
			unset( $this->container['editor'] );
		}

		if ( $this->container->isBound( 'events.editor.template' ) && $this->container->get( 'events.editor.template' ) instanceof Tribe__Events__Editor__Template ) {
			unset( $this->container['events.editor.template'] );
		}

		if ( $this->container->isBound( 'events.editor.template.overwrite' ) && $this->container->get( 'events.editor.template.overwrite' ) instanceof Tribe__Events__Editor__Template__Overwrite ) {
			unset( $this->container['events.editor.template.overwrite'] );
		}

		// Unregister the blocks.
		if ( $this->container->isBound( 'events.editor.blocks.classic-event-details' ) && $this->container->get( 'events.editor.blocks.classic-event-details' ) instanceof Tribe__Events__Editor__Blocks__Classic_Event_Details ) {
			unset( $this->container['events.editor.blocks.classic-event-details'] );
		}

		if ( $this->container->isBound( 'events.editor.blocks.event-datetime' ) && $this->container->get( 'events.editor.blocks.event-datetime' ) instanceof Tribe__Events__Editor__Blocks__Event_Datetime ) {
			unset( $this->container['events.editor.blocks.event-datetime'] );
		}

		if ( $this->container->isBound( 'events.editor.blocks.event-venue' ) && $this->container->get( 'events.editor.blocks.event-venue' ) instanceof Tribe__Events__Editor__Blocks__Event_Venue ) {
			unset( $this->container['events.editor.blocks.event-venue'] );

		}

		if ( $this->container->isBound( 'events.editor.blocks.event-organizer' ) && $this->container->get( 'events.editor.blocks.event-organizer' ) instanceof Tribe__Events__Editor__Blocks__Event_Organizer ) {
			unset( $this->container['events.editor.blocks.event-organizer'] );
		}

		if ( $this->container->isBound( 'events.editor.blocks.event-links' ) && $this->container->get( 'events.editor.blocks.event-links' ) instanceof Tribe__Events__Editor__Blocks__Event_Links ) {
			unset( $this->container['events.editor.blocks.event-links'] );
		}

		if ( $this->container->isBound( 'events.editor.blocks.event-price' ) && $this->container->get( 'events.editor.blocks.event-price' ) instanceof Tribe__Events__Editor__Blocks__Event_Price ) {
			unset( $this->container['events.editor.blocks.event-price'] );
		}

		if ( $this->container->isBound( 'events.editor.blocks.event-category' ) && $this->container->get( 'events.editor.blocks.event-category' ) instanceof Tribe__Events__Editor__Blocks__Event_Category ) {
			unset( $this->container['events.editor.blocks.event-category'] );
		}

		if ( $this->container->isBound( 'events.editor.blocks.event-tags' ) && $this->container->get( 'events.editor.blocks.event-tags' ) instanceof Tribe__Events__Editor__Blocks__Event_Tags ) {
			unset( $this->container['events.editor.blocks.event-tags'] );
		}

		if ( $this->container->isBound( 'events.editor.blocks.event-website' ) && $this->container->get( 'events.editor.blocks.event-website' ) instanceof Tribe__Events__Editor__Blocks__Event_Website ) {
			unset( $this->container['events.editor.blocks.event-website'] );
		}

		if ( $this->container->isBound( 'events.editor.blocks.featured-image' ) && $this->container->get( 'events.editor.blocks.featured-image' ) instanceof Tribe__Events__Editor__Blocks__Featured_Image ) {
			unset( $this->container['events.editor.blocks.featured-image'] );
		}
		
		remove_action( 'tribe_editor_register_blocks', [ $this, 'register_blocks' ] );
	}

	/**
	 * Register the blocks.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	public function register_blocks() {
		tribe( 'events.editor.blocks.classic-event-details' )->register();
		tribe( 'events.editor.blocks.event-datetime' )->register();
		tribe( 'events.editor.blocks.event-venue' )->register();
		tribe( 'events.editor.blocks.event-organizer' )->register();
		tribe( 'events.editor.blocks.event-links' )->register();
		tribe( 'events.editor.blocks.event-price' )->register();
		tribe( 'events.editor.blocks.event-category' )->register();
		tribe( 'events.editor.blocks.event-tags' )->register();
		tribe( 'events.editor.blocks.event-website' )->register();
		tribe( 'events.editor.blocks.featured-image' )->register();
	}
}
