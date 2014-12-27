<?php


class Tribe__Events__Deactivation {
	private $network = FALSE;

	public function __construct( $network ) {
		$this->network = (bool) $network;
	}

	/**
	 * Set a flag to indicate that the plugin has been deactivated
	 * and needs to be reinitialized if it is reactivated
	 *
	 * @return void
	 */
	private function set_flags() {
		require_once( dirname( __FILE__ ) . '/Updater.php' );
		$updater = new Tribe__Events__Updater( TribeEvents::VERSION );
		$updater->reset();
	}

	/**
	 * Remove event-related capabilities
	 *
	 * @return void
	 */
	private function clear_capabilities() {
		require_once( dirname( __FILE__ ) . '/Capabilities.php' );
		$capabilities = new Tribe__Events__Capabilities();
		$capabilities->remove_all_caps();
	}

	/**
	 * Tell WordPress to flush rewrite rules.
	 * Since our post types are already registered,
	 * we delete the option and let WP regenerate it
	 * on the next page load.
	 */
	private function flush_rewrite_rules() {
		delete_option( 'rewrite_rules' );
	}

	/**
	 * Deactivate the plugin. This should not remove data.
	 * It's job is to remove run-time traces of the plugin.
	 *
	 * @return void
	 */
	public function deactivate() {
		if ( is_multisite() && $this->network ) {
			$this->multisite_deactivate();
		} else {
			$this->blog_deactivate();
		}
	}

	/**
	 * Run the deactivation script on every blog for a multisite install
	 *
	 * @return void
	 */
	private function multisite_deactivate() {
		/** @var wpdb $wpdb */
		global $wpdb;
		$site = get_current_site();
		$blog_ids = $wpdb->get_col( $wpdb->prepare( 'SELECT blog_id FROM {$wpdb->blogs} WHERE site_id=%d', $site->id ) );
		$large = wp_is_large_network();
		foreach ( $blog_ids as $blog ) {
			set_time_limit( 30 );
			switch_to_blog( $blog );
			$large ? $this->short_blog_deactivate() : $this->blog_deactivate();
			restore_current_blog();
		}
	}

	/**
	 * The deactivation routine for a single blog
	 *
	 * @return void
	 */
	private function blog_deactivate() {
		$this->set_flags();
		$this->clear_capabilities();
		$this->flush_rewrite_rules();
	}

	/**
	 * An abridged version that is less DB intensive.
	 *
	 * @see wp_is_large_network() and the 'wp_is_large_network' filter
	 *
	 * @return void
	 */
	private function short_blog_deactivate() {
		$this->set_flags();
	}
}