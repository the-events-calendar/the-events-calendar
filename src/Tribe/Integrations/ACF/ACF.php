<?php
/**
 * Class Tribe__Events__Integrations__ACF__ACF
 *
 * Handles the integration between The Events Calendar plugin and Advanced Custom Fields.
 *
 * This class is meant to be an entry point hooking specialized classes and not
 * a logic hub per se.
 */
class Tribe__Events__Integrations__ACF__ACF {

	/**
	 * @var Tribe__Events__Integrations__ACF__ACF
	 */
	protected static $instance;

	/**
	 * @return Tribe__Events__Integrations__ACF__ACF
	 */
	public static function instance() {
		if ( empty( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Hooks the filters and actions neede for this integration to work.
	 *
	 * @since 4.6.3
	 */
	public function hook() {
		add_action( 'admin_enqueue_scripts', array( $this, 'load_compat_js' ) );
	}

	/**
	 * Load our compatability JS script to supplement the events-admin.js script.
	 *
	 * @since 4.6.3
	 */
	public function load_compat_js() {
		$admin_helpers = Tribe__Admin__Helpers::instance();

		if ( $admin_helpers->is_post_type_screen() ) {
			wp_enqueue_script(
				'tribe-admin-acf-compat',
				plugin_dir_url( __FILE__ ) . 'resources/tribe-admin-acf-compat.js',
				array( 'jquery' ),
				'1.0.0',
				true
			);
		}
	}
}
