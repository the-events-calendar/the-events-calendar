<?php
/**
 * Beta Test Feedback
 *
 * Generates a system for providing beta test feedback.
 *
 * @author Peter Chester
 * @version 1.0
 * @copyright Modern Tribe, Inc. 2012
 **/

// Block direct requests
if ( !defined('ABSPATH') )
	die();

if ( !class_exists('TribeBetaTester') ) {

	/**
	 * Beta Test Feedback
	 */
	class TribeBetaTester {

		private static $email = 'beta-feedback@tri.be';
		private static $plugin = array();


		/********** CORE FUNCTIONS **********/

		/**
		 * Class Constructor
		 *
		 * Enqueue scripts and init filters.
		 */
		public function __construct( $plugin ) {
			self::$plugin['slug'] = $plugin;
			add_action( 'wp_before_admin_bar_render', array( $this, 'admin_bar_support_link'), 10000, 1 );
			add_action( 'wp_head', array( $this, 'admin_bar_header' ) );
			add_action( 'in_admin_header', array( $this, 'admin_bar_header' ) );
		}

		/**
		 * Stylize beta test button.
		 */
		public function admin_bar_header() {
			?>
			<style>
			#wpadminbar ul.ab-top-menu li#wp-admin-bar-tribe-beta-test a {
				background-color: #ff6600;
				color: #F0F0F0;
				text-shadow: #555 0 -1px 0;
			}
			#wpadminbar ul.ab-top-menu li#wp-admin-bar-tribe-beta-test:hover a,
			#wpadminbar ul.ab-top-menu li#wp-admin-bar-tribe-beta-test.hover a {
				color: #FFF;
				text-shadow: #777 0 -1px 0;
				background-color: #ff5500;
				background-image: none;
			}
			</style>
			<?php
		}

		/**
		 * Add a support link to the admin bar
		 *
		 * It would make much more sense to use admin_bar_menu,
		 * but WP Super Cache insists on using the later
		 * 'wp_before_admin_bar_render' hook. Since this
		 * needs to be at the end of the menu, we're forced
		 * to use the same.
		 */
		public function admin_bar_support_link(  ) {
			/** @var WP_Admin_Bar $wp_admin_bar */
			global $wp_admin_bar;

			$link = $this->mail_link();

			if ( isset( self::$plugin['Version'] ) ) {
				$title = sprintf( __('%s Beta Feedback', 'tribe-beta-test'), self::$plugin['Name']);
			} else {
				$title = __('Beta Feedback', 'tribe-beta-test');
			}

			$wp_admin_bar->add_menu( array(
				'id' => 'tribe-beta-test',
				'title' => $title,
				'href' => '#',
				'meta' => array(
					'title' => __('Please let us know what we can improve.', 'tribe-beta-test'),
					'target' => '_self',
					'class' => 'tribe-beta-test-class',
					'onclick' => 'window.location.href="'.$link.'";',
				),
			));
		}

		private function collect_system_data() {

			$data = array();

			// SYSTEM DETAILS
			include ABSPATH . WPINC . '/version.php';
			$system_key = 'system';
			//$system_key = __('System Details');
			$data[$system_key] = array();
			$data[$system_key]['WordPress Version'] = $wp_version;
			$data[$system_key]['PHP Version'] = phpversion();
			$data[$system_key]['Install Type'] = ( is_multisite() ) ? "Multiuser" : "Single";
			$data[$system_key]['Url'] = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

			// todo: cache the theme / plugin data below.

			// THEME
			$theme = wp_get_theme();
			$theme_key = 'theme';
			//$theme_key = __('Theme: ').$theme->get('Name').' '.$theme->get('Version');
			$data[$theme_key] = array();
			$keys = array('Name','Version','Author','Author URI');
			foreach ( $keys as $key ) {
				$theme_meta = $theme->get($key);
				if ( !empty( $theme_meta ) ) {
					$data[$theme_key][$key] = $theme_meta;
				}
			}

			// PLUGINS
			include_once ( ABSPATH . '/wp-admin/includes/plugin.php' );
			$plugins = array(
				'plugin' => get_plugins(),
				'muplugin' => get_mu_plugins(),
				'dropin' => get_dropins(),
			);

			foreach ( $plugins as $plugin_group => $plugin_list ) {
				foreach ( $plugin_list as $k => $v) {
					if ( $plugin_group != 'plugin' || is_plugin_active( $k ) || ( is_multisite() && is_plugin_active_for_network( $k ) ) ) {

						$plugin_key = $plugin_group;
						if ( isset( $data[$plugin_key] ) && !is_array( $data[$plugin_key] ) ) {
							$data[$plugin_key] = array();
						}

						$data[$plugin_key][] = "{$v['Name']} {$v['Version']}";

						if ( isset(self::$plugin['slug']) && strpos( $k, self::$plugin['slug'] ) ) {
							self::$plugin = $v;
							$data[$system_key][$v['Name']] = $v['Version'];

						}
					}
				}
			}

			return $data;
		}

		private function mail_link() {

			$data = $this->collect_system_data();

			if ( isset( self::$plugin['Version'] ) ) {
				$subject = sprintf( __('Beta Test Feedback for %s %s'), self::$plugin['Name'], self::$plugin['Version']);
			} else {
				$subject = __('Beta Test Feedback');
			}

			$body = _("Please note your feedback here:")."\n\n\n\n\n";
			foreach ($data as $k => $v) {
				if ( is_array( $v ) ) {
					$body .= "\n======= $k =======\n";
					foreach ( $v as $subk => $subv ) {
						if ( is_numeric( $subk ) ) {
							$body .= "$subv\n";
						} else {
							$body .= "$subk : $subv\n";
						}
					}
				} else {
					$body .= "$k : $v\n";
				}
			}
			$body = rawurlencode( $body );

			//$body = 'data:text/text;base64,'.base64_encode( $body );
			$link = sprintf( 'mailto:%s?subject=%s&body=%s', self::$email, $subject, $body );
			$link = substr( $link, 0, 2048 );
			return $link;
		}

		/********** SINGLETON FUNCTIONS **********/

		/* Don't edit below here! */

		/**
		 * Instance of this class for use as singleton
		 */
		private static $instance;

		/**
		 * Create the instance of the class
		 *
		 * @static
		 * @return void
		 */
		public static function init( $plugin ) {
			self::$instance = self::get_instance( $plugin );
		}

		/**
		 * Get (and instantiate, if necessary) the instance of the class
		 *
		 * @static
		 * @return TribeBetaTester
		 */
		public static function get_instance( $plugin ) {
			if ( !is_a(self::$instance, __CLASS__) ) {
				self::$instance = new self( $plugin );
			}
			return self::$instance;
		}
	}
}
?>