<?php
/**
 * Manage upgrades for the Tribe Events Pro plugin
 *
 */

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( ! class_exists( 'TribeEventsProPUE' ) ) {
	class TribeEventsProPUE {

		/**
		 * @var string slug used for the plugin update engine
		 */
		private static $pue_slug = 'events-calendar-pro';

		/**
		 * @var string plugin update url
		 */
		private static $update_url = 'http://tri.be/';

		/**
		 * @var string plugin file name
		 */
		private static $plugin_file;

		/**
		 * Constructor function. a.k.a. Let's get this party started!
		 *
		 * @param string $plugin_file file path.
		 */
		public function __construct( $plugin_file ) {
			self::$plugin_file = $plugin_file;
			add_action( 'tribe_helper_activation_complete', array( $this, 'load_plugin_update_engine' ) );
			register_activation_hook( self::$plugin_file, array( $this, 'register_uninstall_hook' ) );
		}

		/**
		 * Load the Plugin Update Engine
		 */
		public function load_plugin_update_engine() {
			if ( apply_filters( 'tribe_enable_pue', true, self::$pue_slug ) && class_exists( 'TribePluginUpdateEngineChecker' ) ) {
				$this->pue_instance = new TribePluginUpdateEngineChecker( self::$update_url, self::$pue_slug, array(), plugin_basename( self::$plugin_file ) );
			}
		}

		/**
		 * Register the uninstall hook on activation
		 */
		public function register_uninstall_hook() {
			register_uninstall_hook( self::$plugin_file, array( get_class( $this ), 'uninstall' ) );
		}

		/**
		 * The uninstall hook for the pue option.
		 */
		public function uninstall() {
			$slug = str_replace( '-', '_', self::$pue_slug );
			delete_option( 'pue_install_key_' . $slug );
			delete_option( 'pu_dismissed_upgrade_' . $slug );
		}
	}
}
?>