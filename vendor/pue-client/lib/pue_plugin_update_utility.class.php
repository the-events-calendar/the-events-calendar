<?php
/**
 * Plugin Update Utility Class
 */

// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }

if ( !class_exists('PluginUpdateUtility') ) {

	/**
	 * A simple container class for holding information about an available update.
	 * 
	 * @version 1.1
	 * @access public
	 */
	class PluginUpdateUtility {
		public $id = 0;
		public $slug;
		public $version;
		public $homepage;
		public $download_url;
		public $sections = array();
		public $upgrade_notice;
	
		/**
		 * Create a new instance of PluginUpdateUtility from its JSON-encoded representation.
		 * 
		 * @param string $json
		 * @return PluginUpdateUtility
		 */
		public static function fromJson($json){
			//Since update-related information is simply a subset of the full plugin info,
			//we can parse the update JSON as if it was a plugin info string, then copy over
			//the parts that we care about.
			$pluginInfo = PU_PluginInfo::fromJson($json);
			if ( $pluginInfo != null ) {
				return PluginUpdateUtility::fromPluginInfo($pluginInfo);
			} else {
				return null;
			}
		}
	
		/**
		 * Create a new instance of PluginUpdateUtility based on an instance of PU_PluginInfo.
		 * Basically, this just copies a subset of fields from one object to another.
		 * 
		 * @param PU_PluginInfo $info
		 * @return PluginUpdateUtility
		 */
		public static function fromPluginInfo($info){
			$update = new PluginUpdateUtility();
			$copyFields = array('id', 'slug', 'version', 'homepage', 'download_url', 'upgrade_notice', 'sections');
			foreach($copyFields as $field){
				$update->$field = $info->$field;
			}
			return $update;
		}
	
		/**
		 * Transform the update into the format used by WordPress native plugin API.
		 * 
		 * @return object
		 */
		public function toWpFormat(){
			$update = new StdClass;
		
			$update->id = $this->id;
			$update->slug = $this->slug;
			$update->new_version = $this->version;
			$update->url = $this->homepage;
			$update->package = $this->download_url;
			if ( !empty($this->upgrade_notice) ){
				$update->upgrade_notice = $this->upgrade_notice;
			}
		
			return $update;
		}
	}
}
?>