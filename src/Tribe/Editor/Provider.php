<?php

class Tribe__Events__Editor__Provider extends tad_DI52_ServiceProvider {

	/**
	 * Binds and sets up implementations.
	 *
	 * @since 4.7
	 *
	 */
	public function register() {
		// Setup to check if gutenberg is active
		$this->container->singleton( 'events.editor', 'Tribe__Events__Editor' );
		$this->container->singleton( 'events.editor.compatibility', 'Tribe__Events__Editor__Compatibility', array( 'hook' ) );

		if (
			! tribe( 'editor' )->should_load_blocks()
			|| ! tribe( 'events.editor.compatibility' )->is_blocks_editor_toggled_on()
		) {
			return;
		}

		$this->container->singleton( 'events.editor.meta', 'Tribe__Events__Editor__Meta' );
		$this->container->singleton( 'events.editor.settings', 'Tribe__Events__Editor__Settings' );
		$this->container->singleton( 'events.editor.i18n', 'Tribe__Events__Editor__I18n', array( 'hook' ) );
		$this->container->singleton( 'events.editor.template', 'Tribe__Events__Editor__Template' );
		$this->container->singleton( 'events.editor.template.overwrite', 'Tribe__Events__Editor__Template__Overwrite', array( 'hook' ) );
		$this->container->singleton( 'events.editor.configuration', 'Tribe__Events__Editor__Configuration', array( 'hook' ) );

		$this->container->singleton( 'events.editor.blocks.classic-event-details', 'Tribe__Events__Editor__Blocks__Classic_Event_Details' );
		$this->container->singleton( 'events.editor.blocks.event-datetime', 'Tribe__Events__Editor__Blocks__Event_Datetime' );
		$this->container->singleton( 'events.editor.blocks.event-venue', 'Tribe__Events__Editor__Blocks__Event_Venue' );
		$this->container->singleton( 'events.editor.blocks.event-organizer', 'Tribe__Events__Editor__Blocks__Event_Organizer' );
		$this->container->singleton( 'events.editor.blocks.event-links', 'Tribe__Events__Editor__Blocks__Event_Links' );
		$this->container->singleton( 'events.editor.blocks.event-price', 'Tribe__Events__Editor__Blocks__Event_Price' );
		$this->container->singleton( 'events.editor.blocks.event-category', 'Tribe__Events__Editor__Blocks__Event_Category' );
		$this->container->singleton( 'events.editor.blocks.event-tags', 'Tribe__Events__Editor__Blocks__Event_Tags' );
		$this->container->singleton( 'events.editor.blocks.event-website', 'Tribe__Events__Editor__Blocks__Event_Website' );
		$this->container->singleton( 'events.editor.blocks.featured-image', 'Tribe__Events__Editor__Blocks__Featured_Image' );

		$this->hook();

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
		// Prevents loading of blocks if gutenberg plugin is active
		// We wil deactivate this plugin right after after `admin_init`
		if ( class_exists( 'Tribe__Gutenberg__Plugin' ) ) {
			return false;
		}

		// Setup the Meta registration
		add_action( 'init', tribe_callback( 'events.editor.meta', 'register' ), 15 );

		tribe( 'events.editor' )->hook();

		// Register blocks to own own action
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

	/**
	 * Binds and sets up implementations at boot time.
	 *
	 * @since 4.7
	 */
	public function boot() {
		// no ops
	}
}
