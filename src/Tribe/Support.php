<?php
/**
 * Class for managing technical support components
 *
 * @version 0.3
 */

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( ! class_exists( 'Tribe__Events__Support' ) ) {

	class Tribe__Events__Support {

		public static $support;
		public $rewrite_rules_purged = false;

		/**
		 * Fields listed here contain HTML and should be escaped before being
		 * printed.
		 *
		 * @var array
		 */
		protected $must_escape = array(
			'tribeEventsAfterHTML',
			'tribeEventsBeforeHTML',
		);

		private function __construct() {
			$this->must_escape = (array) apply_filters( 'tribe_help_must_escape_fields', $this->must_escape );
			add_action( 'tribe_help_tab_sections', array( $this, 'displayHelpTabInfo' ), 10, 0 );
			add_action( 'delete_option_rewrite_rules', array( $this, 'log_rewrite_rule_purge' ) );
		}

		/**
		 * Display help tab info in events settings
		 */
		public function displayHelpTabInfo() {

			if ( ! current_user_can( 'administrator' ) ) {
				return;
			}

			$system_text[] = '<p>' . __( "Sometimes it's hard to tell what's going wrong without knowing more about your system steup. For your convenience, we've put together a little report on what's cooking under the hood.", 'the-events-calendar' ) . '</p>';
			$system_text[] = '<p>' . __( "If you suspect that the problem you're having is related to another plugin, or we're just plain having trouble reproducing your bug report, please copy and send all of this to our support team.", 'the-events-calendar' ) . '</p>';
			$system_text   = implode( $system_text );
			?>

			<h2><?php esc_html_e( 'System Information', 'the-events-calendar' ); ?></h2>
			<?php
			echo apply_filters( 'tribe_help_tab_system', $system_text );
			echo $this->formattedSupportStats();
			$this->formattedSupportStatsStyle();
		}

		/**
		 * Collect system information for support
		 *
		 * @return array of system data for support
		 */
		public function getSupportStats() {
			$user = wp_get_current_user();

			$plugins = array();
			if ( function_exists( 'get_plugin_data' ) ) {
				$plugins_raw = wp_get_active_and_valid_plugins();
				foreach ( $plugins_raw as $k => $v ) {
					$plugin_details = get_plugin_data( $v );
					$plugin         = $plugin_details['Name'];
					if ( ! empty( $plugin_details['Version'] ) ) {
						$plugin .= sprintf( ' version %s', $plugin_details['Version'] );
					}
					if ( ! empty( $plugin_details['Author'] ) ) {
						$plugin .= sprintf( ' by %s', $plugin_details['Author'] );
					}
					if ( ! empty( $plugin_details['AuthorURI'] ) ) {
						$plugin .= sprintf( '(%s)', $plugin_details['AuthorURI'] );
					}
					$plugins[] = $plugin;
				}
			}

			$network_plugins = array();
			if ( is_multisite() && function_exists( 'get_plugin_data' ) ) {
				$plugins_raw = wp_get_active_network_plugins();
				foreach ( $plugins_raw as $k => $v ) {
					$plugin_details = get_plugin_data( $v );
					$plugin         = $plugin_details['Name'];
					if ( ! empty( $plugin_details['Version'] ) ) {
						$plugin .= sprintf( ' version %s', $plugin_details['Version'] );
					}
					if ( ! empty( $plugin_details['Author'] ) ) {
						$plugin .= sprintf( ' by %s', $plugin_details['Author'] );
					}
					if ( ! empty( $plugin_details['AuthorURI'] ) ) {
						$plugin .= sprintf( '(%s)', $plugin_details['AuthorURI'] );
					}
					$network_plugins[] = $plugin;
				}
			}

			$mu_plugins = array();
			if ( function_exists( 'get_mu_plugins' ) ) {
				$mu_plugins_raw = get_mu_plugins();
				foreach ( $mu_plugins_raw as $k => $v ) {
					$plugin = $v['Name'];
					if ( ! empty( $v['Version'] ) ) {
						$plugin .= sprintf( ' version %s', $v['Version'] );
					}
					if ( ! empty( $v['Author'] ) ) {
						$plugin .= sprintf( ' by %s', $v['Author'] );
					}
					if ( ! empty( $v['AuthorURI'] ) ) {
						$plugin .= sprintf( '(%s)', $v['AuthorURI'] );
					}
					$mu_plugins[] = $plugin;
				}
			}

			$keys = apply_filters( 'tribe-pue-install-keys', array() );

			$systeminfo = array(
				'url'                => 'http://' . $_SERVER['HTTP_HOST'],
				'name'               => $user->display_name,
				'email'              => $user->user_email,
				'install keys'       => $keys,
				'WordPress version'  => get_bloginfo( 'version' ),
				'PHP version'        => phpversion(),
				'plugins'            => $plugins,
				'network plugins'    => $network_plugins,
				'mu plugins'         => $mu_plugins,
				'theme'              => wp_get_theme()->get( 'Name' ),
				'multisite'          => is_multisite(),
				'settings'           => Tribe__Events__Main::getOptions(),
				'WordPress timezone' => get_option( 'timezone_string', __( 'Unknown or not set', 'the-events-calendar' ) ),
				'server timezone'    => date_default_timezone_get(),
			);

			if ( $this->rewrite_rules_purged ) {
				$systeminfo['rewrite rules purged'] = __( 'Rewrite rules were purged on load of this help page. Chances are there is a rewrite rule flush occurring in a plugin or theme!', 'the-events-calendar' );
			}

			$systeminfo = apply_filters( 'tribe-events-pro-support', $systeminfo );

			return $systeminfo;
		}

		/**
		 * Render system information into a pretty output
		 *
		 * @return string pretty HTML
		 */
		public function formattedSupportStats() {
			$systeminfo = $this->getSupportStats();
			$output     = '';
			$output .= '<dl class="support-stats">';
			foreach ( $systeminfo as $k => $v ) {

				switch ( $k ) {
					case 'name' :
					case 'email' :
						continue 2;
						break;
					case 'url' :
						$v = sprintf( '<a href="%s">%s</a>', $v, $v );
						break;
				}

				if ( is_array( $v ) ) {
					$keys             = array_keys( $v );
					$key              = array_shift( $keys );
					$is_numeric_array = is_numeric( $key );
					unset( $keys );
					unset( $key );
				}

				$output .= sprintf( '<dt>%s</dt>', $k );
				if ( empty( $v ) ) {
					$output .= '<dd class="support-stats-null">-</dd>';
				} elseif ( is_bool( $v ) ) {
					$output .= sprintf( '<dd class="support-stats-bool">%s</dd>', $v );
				} elseif ( is_string( $v ) ) {
					$output .= sprintf( '<dd class="support-stats-string">%s</dd>', $v );
				} elseif ( is_array( $v ) && $is_numeric_array ) {
					$output .= sprintf( '<dd class="support-stats-array"><ul><li>%s</li></ul></dd>', join( '</li><li>', $v ) );
				} else {
					$formatted_v = array();
					foreach ( $v as $obj_key => $obj_val ) {
						if ( in_array( $obj_key, $this->must_escape ) ) {
							$obj_val = esc_html( $obj_val );
						}
						if ( is_array( $obj_val ) ) {
							$formatted_v[] = sprintf( '<li>%s = <pre>%s</pre></li>', $obj_key, print_r( $obj_val, true ) );
						} else {
							$formatted_v[] = sprintf( '<li>%s = %s</li>', $obj_key, $obj_val );
						}
					}
					$v = join( "\n", $formatted_v );
					$output .= sprintf( '<dd class="support-stats-object"><ul>%s</ul></dd>', print_r( $v, true ) );
				}
			}
			$output .= '</dl>';

			return $output;
		}

		public function formattedSupportStatsStyle() {
			?>
			<style>
				dl.support-stats {
					background: #000;
					color: #888;
					padding: 10px;
					overflow: scroll;
					max-height: 400px;
					border-radius: 2px;
				}

				dl.support-stats dt {
					text-transform: uppercase;
					font-weight: bold;
					width: 25%;
					clear: both;
					float: left;
				}

				dl.support-stats dd {
					padding-left: 10px;
					margin-left: 25%;
				}
			</style>
		<?php
		}

		/**
		 * Logs the occurence of rewrite rule purging
		 */
		public function log_rewrite_rule_purge() {
			$this->rewrite_rules_purged = true;
		}//end log_rewrite_rule_purge

		/****************** SINGLETON GUTS ******************/

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

	}

}
