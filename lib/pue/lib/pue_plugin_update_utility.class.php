<?php
/**
 * Plugin Update Utility Class
 */

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( ! class_exists( 'TribePluginUpdateUtility' ) ) {

	/**
	 * A simple container class for holding information about an available update.
	 *
	 * @version 1.7
	 * @access  public
	 */
	class TribePluginUpdateUtility {
		public $id = 0;
		public $slug;
		public $version;
		public $homepage;
		public $download_url;
		public $sections = array();
		public $upgrade_notice;

		/**
		 * Create a new instance of TribePluginUpdateUtility from its JSON-encoded representation.
		 *
		 * @param string $json
		 *
		 * @return TribePluginUpdateUtility
		 */
		public static function from_json( $json ) {
			//Since update-related information is simply a subset of the full plugin info,
			//we can parse the update JSON as if it was a plugin info string, then copy over
			//the parts that we care about.
			$pluginInfo = Tribe_PU_PluginInfo::from_json( $json );
			if ( $pluginInfo != null ) {
				return TribePluginUpdateUtility::from_plugin_info( $pluginInfo );
			} else {
				return null;
			}
		}

		/**
		 * Create a new instance of TribePluginUpdateUtility based on an instance of Tribe_PU_PluginInfo.
		 * Basically, this just copies a subset of fields from one object to another.
		 *
		 * @param Tribe_PU_PluginInfo $info
		 *
		 * @return TribePluginUpdateUtility
		 */
		public static function from_plugin_info( $info ) {
			$update     = new TribePluginUpdateUtility();
			$copyFields = array( 'id', 'slug', 'version', 'homepage', 'download_url', 'upgrade_notice', 'sections' );
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
?>