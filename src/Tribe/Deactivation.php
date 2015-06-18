<?php

/**
 * Class Tribe__Events__Pro__Deactivation
 */
class Tribe__Events__Pro__Deactivation extends Tribe__Events__Abstract_Deactivation {

	/**
	 * Set a flag to indicate that the plugin has been deactivated
	 * and needs to be reinitialized if it is reactivated
	 *
	 * @return void
	 */
	private function set_flags() {
		$updater = new Tribe__Events__Pro__Updater( Tribe__Events__Pro__Main::VERSION );
		$updater->reset();
	}

	/**
	 * The deactivation routine for a single blog
	 *
	 * @return void
	 */
	protected function blog_deactivate() {
		$this->set_flags();
		$this->flush_rewrite_rules();
		do_action( 'tribe_events_pro_blog_deactivate' );
	}

	/**
	 * An abridged version that is less DB intensive.
	 *
	 * @see wp_is_large_network() and the 'wp_is_large_network' filter
	 *
	 * @return void
	 */
	protected function short_blog_deactivate() {
		$this->set_flags();
	}
}
