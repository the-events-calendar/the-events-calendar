<?php
class Tribe__Events__Editor__Provider extends tad_DI52_ServiceProvider {

	/**
	 * Binds and sets up implementations.
	 *
	 * @since  TBD
	 *
	 */
	public function register() {
		$this->container->singleton( 'tec.editor', 'Tribe__Events__Editor' );

		// Should we continue loading?
		if ( ! tribe( 'tec.editor' )->is_gutenberg_active() ) {
			return;
		}

		$this->container->singleton( 'tec.editor.blocks.event-details', 'Tribe__Events__Editor__Blocks__Event_Details' );

		$this->hook();

		/**
		 * @todo  Remove this later on
		 */
		tribe( 'tec.editor' )->assets();
	}

	/**
	 * Any hooking any class needs happen here.
	 *
	 * In place of delegating the hooking responsibility to the single classes they are all hooked here.
	 *
	 * @since  TBD
	 *
	 */
	protected function hook() {
		add_filter( 'tribe_events_register_event_type_args', tribe_callback( 'tec.editor', 'add_support' ) );

		add_action( 'init', tribe_callback( 'tec.editor', 'register_blocks' ), 20 );

		add_action( 'tribe_events_editor_register_blocks', tribe_callback( 'tec.editor.blocks.event-details', 'register' ) );
	}

	/**
	 * Binds and sets up implementations at boot time.
	 *
	 * @since  TBD
	 */
	public function boot() {
		// no ops
	}
}
