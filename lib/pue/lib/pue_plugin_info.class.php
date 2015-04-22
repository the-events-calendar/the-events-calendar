<?php
/**
 * Plugin Info Class
 */

// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }

if ( !class_exists('Tribe_PU_PluginInfo') ) {
	/**
	 * A container class for holding and transforming various plugin metadata.
	 * @version 1.7
	 * @access public
	 */
	class Tribe_PU_PluginInfo {
		//Most fields map directly to the contents of the plugin's info.json file.

		public $name;
		public $slug;
		public $version;
		public $homepage;
		public $sections;
		public $download_url;

		public $author;
		public $author_homepage;

		public $requires;
		public $tested;
		public $upgrade_notice;

		public $rating;
		public $num_ratings;
		public $downloaded;
		public $last_updated;

		public $id = 0; //The native WP.org API returns numeric plugin IDs, but they're not used for anything.

		/**
		 * Create a new instance of Tribe_PU_PluginInfo from JSON-encoded plugin info
		 * returned by an external update API.
		 *
		 * @param string $json Valid JSON string representing plugin info.
		 * @return Tribe_PU_PluginInfo New instance of Tribe_PU_PluginInfo, or NULL on error.
		 */
		public static function from_json($json){
			$apiResponse = json_decode($json);
			if ( empty($apiResponse) || !is_object($apiResponse) ){
				return null;
			}

			//Very, very basic validation.
			$valid = (isset($apiResponse->name) && !empty($apiResponse->name) && isset($apiResponse->version) && !empty($apiResponse->version)) || (isset($apiResponse->api_invalid) || isset($apiResponse->no_api));
			if ( !$valid ){
				return null;
			}

			$info = new Tribe_PU_PluginInfo();

			foreach(get_object_vars($apiResponse) as $key => $value){
				$key = str_replace('plugin_', '', $key); //let's strip out the "plugin_" prefix we've added in plugin-updater-classes.
				$info->$key = $value;
			}

			return $info;
		}

		/**
		 * Transform plugin info into the format used by the native WordPress.org API
		 *
		 * @return object
		 */
		public function to_wp_format(){
			$info = new StdClass;

			//The custom update API is built so that many fields have the same name and format
			//as those returned by the native WordPress.org API. These can be assigned directly.

			$sameFormat = array(
				'name', 'slug', 'version', 'requires', 'tested', 'rating', 'upgrade_notice',
				'num_ratings', 'downloaded', 'homepage', 'last_updated',
			);
			foreach($sameFormat as $field){
				if ( isset($this->$field) ) {
					$info->$field = $this->$field;
				} else {
					$info->$field = NULL;
				}
			}

			//Other fields need to be renamed and/or transformed.
			$info->download_link = $this->download_url;

			if ( !empty($this->author_homepage) ){
				$info->author = sprintf('<a href="%s">%s</a>', esc_url( $this->author_homepage ), $this->author);
			} else {
				$info->author = $this->author;
			}

			if ( is_object($this->sections) ){
				$info->sections = get_object_vars($this->sections);
			} elseif ( is_array($this->sections) ) {

				$info->sections = $this->sections;

			} else {
				$info->sections = array('description' => '');
			}

			return $info;
		}
	}
}
?>
