<?php
/**
 * Class for managing overlapping helper plugins. This ensures that we use the latest versions of common code.
 *
 * Usage: include this file on any plugin that may have shared code BEFORE the 'plugins_loaded' action is completed.
 * After including this file, register the helper files using the TribeCommonLibraries::register() instead of including the files directly.
 *
 * @version 1.1
 */

// Don't load directly
if ( ! defined('ABSPATH') ) { die('-1'); }

if ( ! class_exists( 'TribeCommonLibraries' ) ) {

	class TribeCommonLibraries {

		private static $tribe_registered_plugins;

		/**
		 * Enforce Singleton Pattern
		 */
		private static $instance;


		public static function getInstance() {
			if ( null == self::$instance ) {
				$className      = __CLASS__;
				self::$instance = new $className;
			}

			return self::$instance;
		}


		private function __construct() {
			self::$tribe_registered_plugins = array();
			add_action( 'plugins_loaded', array( $this, 'activate_plugins' ), 999 );
		}

		/**
		 * Register a plugin / helper class
		 *
		 * @param string $slug    - slug specific to the helper class / plugin
		 * @param string $version - version of the helper class / plugin
		 * @param string $path    - absolute path of the helper class / plugin file
		 */
		public static function register( $slug, $version, $path ) {
			if ( ! isset( self::$tribe_registered_plugins[$slug] ) || version_compare( self::$tribe_registered_plugins[$slug]['version'], $version, '<' ) ) {
				self::$tribe_registered_plugins[$slug] = array(
					'version' => $version,
					'path'    => $path,
				);
			}
		}

		/**
		 * Activate all plugins.
		 */
		public function activate_plugins() {
			foreach ( self::$tribe_registered_plugins as $k => $v ) {
				require_once( $v['path'] );
				do_action( 'tribe_helper_activate_' . $k );
			}
			do_action( 'tribe_helper_activation_complete' );
		}
	}
}

TribeCommonLibraries::getInstance();