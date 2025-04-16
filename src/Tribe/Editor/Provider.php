<?php

use TEC\Common\Contracts\Service_Provider;
use Tribe\Events\Editor\Hooks;
use Tribe__Events__Editor as Editor;
use Tribe__Events__Editor__Blocks__Classic_Event_Details as Details;
use Tribe__Events__Editor__Blocks__Event_Category as Category;
use Tribe__Events__Editor__Blocks__Event_Datetime as Datetime;
use Tribe__Events__Editor__Blocks__Event_Links as Links;
use Tribe__Events__Editor__Blocks__Event_Organizer as Organizer;
use Tribe__Events__Editor__Blocks__Event_Price as Price;
use Tribe__Events__Editor__Blocks__Event_Tags as Tags;
use Tribe__Events__Editor__Blocks__Event_Venue as Venue;
use Tribe__Events__Editor__Blocks__Event_Website as Website;
use Tribe__Events__Editor__Blocks__Featured_Image as Image;
use Tribe__Events__Editor__Compatibility as Compatibility;
use Tribe__Events__Editor__Configuration as Configuration;
use Tribe__Events__Editor__I18n as I18n;
use Tribe__Events__Editor__Meta as Meta;
use Tribe__Events__Editor__Settings as Settings;
use Tribe__Events__Editor__Template as Template;
use Tribe__Events__Editor__Template__Overwrite as Template_Overwrite;

class Tribe__Events__Editor__Provider extends Service_Provider {


	/**
	 * Binds and sets up implementations.
	 *
	 * @since 4.7
	 *
	 */
	public function register() {
		// Setup to check if gutenberg is active
		$this->container->singleton( 'events.editor', Editor::class );
		$this->container->singleton( 'events.editor.compatibility', Compatibility::class );

		tribe( 'events.editor.compatibility' )->hook();
		tribe( 'events.editor' )->hook();

		if ( ! tribe( 'editor' )->should_load_blocks() && ! tec_is_full_site_editor() ) {
			return;
		}

		$this->register_singletons();
		$this->hook();
		$this->call_singletons();
	}

	public function register_singletons() {
		$this->container->singleton( 'events.editor.meta', Meta::class );
		$this->container->singleton( 'events.editor.settings', Settings::class );
		$this->container->singleton( 'events.editor.i18n', I18n::class, [ 'hook' ] );
		$this->container->singleton( 'events.editor.template', Template::class );
		$this->container->singleton( 'events.editor.template.overwrite', Template_Overwrite::class, [ 'hook' ] );
		$this->container->singleton( 'events.editor.configuration', Configuration::class, [ 'hook' ] );
		$this->container->singleton( 'events.editor.blocks.classic-event-details', Details::class, [ 'load' ] );
		$this->container->singleton( 'events.editor.blocks.event-datetime', Datetime::class, [ 'load' ] );
		$this->container->singleton( 'events.editor.blocks.event-venue', Venue::class, [ 'load' ] );
		$this->container->singleton( 'events.editor.blocks.event-organizer', Organizer::class, [ 'load' ] );
		$this->container->singleton( 'events.editor.blocks.event-links', Links::class, [ 'load' ] );
		$this->container->singleton( 'events.editor.blocks.event-price', Price::class, [ 'load' ] );
		$this->container->singleton( 'events.editor.blocks.event-category', Category::class, [ 'load' ] );
		$this->container->singleton( 'events.editor.blocks.event-tags', Tags::class, [ 'load' ] );
		$this->container->singleton( 'events.editor.blocks.event-website', Website::class, [ 'load' ] );
		$this->container->singleton( 'events.editor.blocks.featured-image', Image::class, [ 'load' ] );
	}

	public function call_singletons() {
		/**
		 * Call all the Singletons that need to be setup/hooked
		 */
		tribe( 'events.editor.i18n' );
		tribe( 'events.editor.template.overwrite' );
		tribe( 'events.editor.configuration' );
	}

	/**
	 * Any hooking any class needs happen here.
	 *
	 * In place of delegating the hooking responsibility to the single classes they are all hooked here.
	 *
	 * @since 4.7
	 *
	 */
	protected function hook() {
		$this->container->register( Hooks::class );

		// Setup the Meta registration
		add_action( 'init', tribe_callback( 'events.editor.meta', 'register' ), 15 );

		// Register blocks to our own action
		add_action( 'tribe_editor_register_blocks', tribe_callback( 'events.editor.blocks.classic-event-details', 'register' ) );
		add_action( 'tribe_editor_register_blocks', tribe_callback( 'events.editor.blocks.event-datetime', 'register' ) );
		add_action( 'tribe_editor_register_blocks', tribe_callback( 'events.editor.blocks.event-venue', 'register' ) );
		add_action( 'tribe_editor_register_blocks', tribe_callback( 'events.editor.blocks.event-organizer', 'register' ) );
		add_action( 'tribe_editor_register_blocks', tribe_callback( 'events.editor.blocks.event-links', 'register' ) );
		add_action( 'tribe_editor_register_blocks', tribe_callback( 'events.editor.blocks.event-price', 'register' ) );
		add_action( 'tribe_editor_register_blocks', tribe_callback( 'events.editor.blocks.event-category', 'register' ) );
		add_action( 'tribe_editor_register_blocks', tribe_callback( 'events.editor.blocks.event-tags', 'register' ) );
		add_action( 'tribe_editor_register_blocks', tribe_callback( 'events.editor.blocks.event-website', 'register' ) );
		add_action( 'tribe_editor_register_blocks', tribe_callback( 'events.editor.blocks.featured-image', 'register' ) );
	}
}
