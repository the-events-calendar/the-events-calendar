<?php
/**
 * Class for managing technical support components
 */

// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }

if( !class_exists( 'TribeEventsProSupport' ) ) {
	class TribeEventsProSupport {

		/**
		 * Enforce Singleton Pattern
		 */
		private static $instance;
		public function getInstance() {
			if(null == self::$instance) {
				$className = __CLASS__;
				self::$instance = new $className;
			}
			return self::$instance;
		}
		
		private static $debug_log = array();
		public static $support;
		
		private function __construct( ) {
			//add_action( 'init', array( $this, 'addSupportLink'), 10 );
			add_action( 'wp_before_admin_bar_render', array( $this, 'addSupportForm') );
			add_action( 'tribe_debug', array( $this, 'logDebug' ), 8, 3 );
		}
		
		public function addSupportLink() {
			if (class_exists('Debug_Bar')) {
				TribeEvents::debug(self::supportLink());
			}
		}
		
		public function addSupportForm() {
			if (class_exists('Debug_Bar')) {
				TribeEvents::debug(self::supportForm());
			}
		}

		/**
		 * Generate a support link based on the user's options. This link is serialized and base64 encoded. On the other end we can decode it and then unserialize it to create a ticket.
		 *
		 * @return void
		 * @author Peter Chester
		 */
		public static function supportLink() {
			$text = __('Send a support request for this site.','tribe-events-calendar-pro');
			$link = TribeEvents::$tribeUrl.'support?supportinfo='.self::generateSupportHash();
			$html = '<div class="tribe-support-link"><a href="'.$link.'" target="_blank">'.$text.'</a></div>';
			return apply_filters('tribe-events-pro-support-link',$html,$link);
		}

		/**
		 * Generate a support form.
		 *
		 * @return void
		 * @author Peter Chester
		 */
		public static function supportForm() {
			ob_start();
			include( TribeEventsPro::instance()->pluginPath . 'admin-views/support-form.php' );
			$form = ob_get_contents();
			ob_get_clean();
			return $form;
		}

		/**
		 * Generate a hash with all the system support information
		 *
		 * @return string of encoded support info
		 * @author Peter Chester
		 */
		public static function generateSupportHash() {
			$user = wp_get_current_user();
			$plugins_raw = wp_get_active_and_valid_plugins();
			$plugins = array();
			foreach($plugins_raw as $k => $v) {
				$plugins[] = basename($v);
			}
			$systeminfo = array(
				'URL' => 'http://'.$_SERVER["HTTP_HOST"].$_SERVER['REQUEST_URI'],
				'NAME' => $user->display_name,
				'EMAIL' => $user->user_email,
				'PLUGIN VERSION' => TribeEvents::VERSION,
				'WORDPRESS VERSION' => get_bloginfo('version'),				
				'PHP VERSION' => phpversion(),				
				'PLUGINS' => $plugins,			
				'THEME' => get_current_theme(),
				'MU INSTALL' => (is_multisite()) ? 'TRUE' : 'FALSE',			
				'SETTINGS' => TribeEvents::getOptions(),
				'ERRORS' => self::$debug_log
			);
			$systeminfo = apply_filters('tribe-events-pro-support',$systeminfo);			
			$systeminfo = serialize($systeminfo);
			$systeminfo = base64_encode($systeminfo);
			return $systeminfo;
		}

		/**
		 * capture log messages for support requests.
		 *
		 * @param string $title - message to display in log
		 * @param string $data - optional data to display
		 * @param string $format - optional format (log|warning|error|notice)
		 * @return void
		 * @author Peter Chester
		 */
		public function logDebug($title,$data=false,$format='log') {
			self::$debug_log[] = array(
				'title' => $title,
				'data' => $data,
				'format' => $format,
			);
		}

	}

	TribeEventsProSupport::getInstance();
}
?>