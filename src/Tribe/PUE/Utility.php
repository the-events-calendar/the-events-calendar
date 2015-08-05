<?php
/**
 * Plugin Update Utility Class
 */

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( ! class_exists( 'Tribe__Events__PUE__Utility' ) ) {

	/**
	 * A simple container class for holding information about an available update.
	 *
	 * @version 1.7
	 * @access  public
	 */
	class Tribe__Events__PUE__Utility {
		public $id = 0;
		public $plugin;
		public $slug;
		public $version;
		public $homepage;
		public $download_url;
		public $sections = array();
		public $upgrade_notice;

		/**
		 * Create a new instance of Tribe__Events__PUE__Utility from its JSON-encoded representation.
		 *
		 * @param string $json
		 *
		 * @return Tribe__Events__PUE__Utility
		 */
		public static function from_json( $json ) {
			//Since update-related information is simply a subset of the full plugin info,
			//we can parse the update JSON as if it was a plugin info string, then copy over
			//the parts that we care about.
			$pluginInfo = Tribe__Events__PUE__Plugin_Info::from_json( $json );
			if ( $pluginInfo != null ) {
				return self::from_plugin_info( $pluginInfo );
			} else {
				return null;
			}
		}

		/**
		 * Create a new instance of Tribe__Events__PUE__Utility based on an instance of Tribe__Events__PUE__Plugin_Info.
		 * Basically, this just copies a subset of fields from one object to another.
		 *
		 * @param Tribe__Events__PUE__Plugin_Info $info
		 *
		 * @return Tribe__Events__PUE__Utility
		 */
		public static function from_plugin_info( $info ) {
			$update     = new Tribe__Events__PUE__Utility();
			$copyFields = array( 'id', 'slug', 'version', 'homepage', 'download_url', 'upgrade_notice', 'sections', 'plugin' );
			foreach ( $copyFields as $field ) {
				$update->$field = $info->$field;
			}

			return $update;
		}

		/**
		 * Transform the update into the format used by WordPress native plugin API.
		 *
		 * @return object
		 */
		public function to_wp_format() {
			$update = new StdClass;

			$update->id          = $this->id;
			$update->plugin      = $this->plugin;
			$update->slug        = $this->slug;
			$update->new_version = $this->version;
			$update->url         = $this->homepage;
			$update->package     = $this->download_url;
			if ( ! empty( $this->upgrade_notice ) ) {
				$update->upgrade_notice = $this->upgrade_notice;
			}

			return $update;
		}
	}
}
