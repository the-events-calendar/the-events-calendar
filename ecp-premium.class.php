<?php
/*
 Plugin Name:  Events Calendar Pro - Premium
 Description:  The Events Calendar Pro Premium plugin enables recurring events, custom meta, and other premium features for the Events Calendar Pro plugin 
 Version: 2.0
 Author: Shane & Peter, Inc.
 Author URI: http://www.shaneandpeter.com/
 Text Domain: events-calendar-pro
 */

if ( !class_exists( 'TribeEventsPro' ) ) {
	class TribeEventsPro {

		const PLUGIN_DOMAIN = 'tribe-events-calendar-pro';

	    private static $instance;

		//instance variables
		public $pluginDir;
		public $pluginPath;
		public $pluginUrl;
		public $pluginDomain = 'events-calendar-pro';		
		
	    private function __construct()
	    {
			$this->pluginDir		= trailingslashit( basename( dirname(__FILE__) ) );
			$this->pluginPath		= trailingslashit( dirname(__FILE__) );
			$this->pluginUrl 		= WP_PLUGIN_URL.'/'.$this->pluginDir;
			
			include 'template-tags.php';

			//TODO: manually include files
	    	foreach (glob($this->pluginPath . "lib/*.php") as $filename) {
				include $filename;
			}
			
			add_action( 'init', array( $this, 'init'), 10 );			
	    }
		
		public function init() {
			Tribe_ECP_Custom_Meta::init();
			Events_Recurrence_Meta::init();
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
		 */public static function check_for_ecp() {
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
