<?php
/*
 Plugin Name: Events Calendar Pro
 Description: The Events Calendar Pro Premium plugin enables recurring events, custom meta, and other premium features for The Events Calendar plugin 
 Version: 2.0
 Author: Modern Tribe, Inc.
 Author URI: http://tribe.pro/
 Text Domain: events-calendar-pro
 */

if ( !class_exists( 'TribeEventsPro' ) ) {
	class TribeEventsPro {

		const PLUGIN_DOMAIN = 'events-calendar-pro';

	    private static $instance;

		//instance variables
		public $pluginDir;
		public $pluginPath;
		public $pluginUrl;
		public static $updateUrl = 'http://tribe.pro/updates/';
		
	    private function __construct()
	    {
			$this->pluginDir = trailingslashit( basename( dirname(__FILE__) ) );
			$this->pluginPath = trailingslashit( dirname(__FILE__) );
			$this->pluginUrl = WP_PLUGIN_URL.'/'.$this->pluginDir;
			
			require_once( 'lib/tribe-date-series-rules.class.php' );
			require_once( 'lib/tribe-ecp-custom-meta.class.php' );
			require_once( 'lib/tribe-events-recurrence-meta.class.php' );
			require_once( 'lib/tribe-recurrence.class.php' );
			require_once( 'lib/tribe-support.class.php' );
			require_once( 'template-tags.php' );
			require_once( 'lib/plugins/pue-client.php' );
			
			add_action( 'init', array( $this, 'init' ), 10 );			
	    }
		
		public function init() {
			TribeEventsCustomMeta::init();
			TribeEventsRecurrenceMeta::init();
			new PluginUpdateEngineChecker(self::$updateUrl, self::PLUGIN_DOMAIN);
		}
	
		/* Static Methods */
	    public static function instance()
	    {
	        if (!isset(self::$instance)) {
	            $className = __CLASS__;
	            self::$instance = new $className;
	        }

	        return self::$instance;
	    }
		
		/**
		 * check_for_ecp
		 *
		 * Check that the required minimum version of the base events plugin is activated.
		 * 
		 * @author John Gadbois 
		 */
		public static function check_for_ecp() {
			if( !class_exists( 'TribeEvents' ) || !defined('TribeEvents::VERSION') || !version_compare( TribeEvents::VERSION, '2.0', '>=') ) {
				deactivate_plugins(basename(__FILE__)); // Deactivate ourself
				wp_die("Sorry, but you must activate Events Calendar Pro 2.0 or greater in order for this plugin to be installed.");	
			}
		}
	}
	
	register_activation_hook( __FILE__, array('TribeEventsPro', 'check_for_ecp') );	

	// Instantiate class and set up WordPress actions.
	TribeEventsPro::instance();
}
?>