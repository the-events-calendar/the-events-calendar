<?php
/**
 * Plugin Update Engine Class
 */

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( ! class_exists( 'TribePluginUpdateEngineChecker' ) ) {
	/**
	 * A custom plugin update checker.
	 *
	 * @original author (c) Janis Elsts
	 * @heavily  modified by Darren Ethier
	 * @slighty  modified by Nick Ciske
	 * @slighty  modified by Joachim Kudish
	 * @heavily  modified by Peter Chester
	 * @license  GPL2 or greater.
	 * @version  1.7
	 * @access   public
	 */
	class TribePluginUpdateEngineChecker {

		private $pue_update_url = ''; //The URL of the plugin's metadata file.
		private $plugin_file = ''; //Plugin filename relative to the plugins directory.
		private $plugin_name = ''; //variable used to hold the plugin_name as set by the constructor.
		private $slug = ''; //Plugin slug. (with .php extension)
		private $download_query = array(); //used to hold the query variables for download checks;

		public $check_period = 12; //How often to check for updates (in hours).
		public $pue_option_name = ''; //Where to store the update info.
		public $json_error = ''; //for storing any json_error data that get's returned so we can display an admin notice.
		public $api_secret_key = ''; //used to hold the user API.  If not set then nothing will work!
		public $install_key = false; //used to hold the install_key if set (included here for addons that will extend PUE to use install key checks)
		public $dismiss_upgrade; //for setting the dismiss upgrade option (per plugin).
		public $pue_install_key; //we'll customize this later so each plugin can have it's own install key!

		/**
		 * Class constructor.
		 *
		 * @param string $pue_update_url The URL of the plugin's metadata file.
		 * @param string $slug           The plugin's 'slug'.
		 * @param array  $options        Contains any options that need to be set in the class initialization for construct.  These are the keys:
		 *
		 * @key integer $check_period How often to check for updates (in hours). Defaults to checking every 12 hours. Set to 0 to disable automatic update checks.
		 * @key string $pue_option_name Where to store book-keeping info about update checks. Defaults to 'external_updates-$slug'.
		 * @key string $apikey used to authorize download updates from developer server
		 *
		 * @param string $plugin_file    fully qualified path to the main plugin file.
		 */
		function __construct( $pue_update_url, $slug = '', $options = array(), $plugin_file = '' ) {

			$this->set_slug( $slug );
			$this->set_pue_update_url( $pue_update_url );
			$this->set_plugin_file( $plugin_file );
			$this->set_options( $options );
			$this->hooks();

		}

		/**
		 * Install the hooks required to run periodic update checks and inject update info
		 * into WP data structures.
		 * Also other hooks related to the automatic updates (such as checking agains API and what not (@from Darren)
		 * @return void
		 */
		function hooks() {
			// Override requests for plugin information
			add_filter( 'plugins_api', array( &$this, 'inject_info' ), 10, 3 );

			// Check for updates when the WP updates are checked and inject our update if needed.
			// Only add filter if the TRIBE_DISABLE_PUE constant is not set as true.
			if ( ! defined( 'TRIBE_DISABLE_PUE' ) || TRIBE_DISABLE_PUE !== true ) {
				add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_for_updates' ) );
			}

			add_filter( 'tribe_licensable_addons', array( $this, 'build_addon_list' ) );
			add_action( 'tribe_license_fields', array( $this, 'do_license_key_fields' ) );
			add_action( 'tribe_settings_after_content_tab_licenses', array( $this, 'do_license_key_javascript' ) );
			add_action( 'tribe_settings_success_message', array( $this, 'do_license_key_success_message' ), 10, 2 );

			// Key validation
			add_action( 'wp_ajax_pue-validate-key_' . $this->get_slug(), array( $this, 'ajax_validate_key' ) );

			// Dashboard message "dismiss upgrade" link
			add_action( 'wp_ajax_' . $this->dismiss_upgrade, array( $this, 'dashboard_dismiss_upgrade' ) );

			add_filter( 'tribe-pue-install-keys', array( $this, 'return_install_key' ) );
		}

		/********************** Getter / Setter Functions **********************/

		/**
		 * Get the slug
		 *
		 * @return string
		 */
		public function get_slug() {
			return apply_filters( 'pue_get_slug', $this->slug );
		}

		/**
		 * Set the slug
		 *
		 * @param string $slug
		 */
		private function set_slug( $slug = '' ) {
			$this->slug            = $slug;
			$clean_slug            = str_replace( '-', '_', $this->slug );
			$this->dismiss_upgrade = 'pu_dismissed_upgrade_' . $clean_slug;
			$this->pue_install_key = 'pue_install_key_' . $clean_slug;
		}

		/**
		 * Get the PUE update API endpoint url
		 *
		 * @return string
		 */
		public function get_pue_update_url() {
			return apply_filters( 'pue_get_update_url', $this->pue_update_url, $this->get_slug() );
		}

		/**
		 * Set the PUE update URL
		 *
		 * This can be overridden using the global constant 'PUE_UPDATE_URL'
		 *
		 * @param string $pue_update_url
		 */
		private function set_pue_update_url( $pue_update_url ) {
			$this->pue_update_url = ( defined( 'PUE_UPDATE_URL' ) ) ? trailingslashit( PUE_UPDATE_URL ) : trailingslashit( $pue_update_url );
		}

		/**
		 * Get the plugin file path
		 *
		 * @return string
		 */
		public function get_plugin_file() {
			return apply_filters( 'pue_get_plugin_file', $this->plugin_file, $this->get_slug() );
		}

		/**
		 * Set the plugin file path
		 *
		 * @param string $plugin_file
		 */
		private function set_plugin_file( $plugin_file = '' ) {

			if ( ! empty( $plugin_file ) ) {
				$this->plugin_file = $plugin_file;

				return;
			}

			$slug = $this->get_slug();
			if ( ! empty( $slug ) ) {
				$this->plugin_file = $slug . '/' . $slug . '.php';
			}
		}

		/**
		 * Set the plugin name
		 *
		 * @param string $plugin_name
		 */
		private function set_plugin_name( $plugin_name = '' ) {
			if ( ! empty( $plugin_name ) ) {
				$this->plugin_name = $plugin_name;
			} else {
				//get name from plugin file itself
				if ( ! function_exists( 'get_plugins' ) ) {
					require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
				}

				$plugin_details    = explode( '/', $this->get_plugin_file() );
				$plugin_folder     = get_plugins( '/' . $plugin_details[0] );
				$this->plugin_name = $plugin_folder[$plugin_details[1]]['Name'];
			}
		}

		/**
		 * Get the plugin name
		 *
		 * @return string
		 */
		public function get_plugin_name() {
			if ( empty( $this->plugin_name ) ) {
				$this->set_plugin_name();
			}

			return apply_filters( 'pue_get_plugin_name', $this->plugin_name, $this->get_slug() );
		}

		/**
		 * Set all the PUE instantiation options
		 *
		 * @param array $options
		 */
		private function set_options( $options = array() ) {

			$options = wp_parse_args(
				$options, array(
					'pue_option_name' => 'external_updates-' . $this->get_slug(),
					'apikey'          => '',
					'installkey'      => false,
					'check_period'    => 12
				)
			);

			$this->pue_option_name = $options['pue_option_name'];
			$this->check_period    = (int) $options['check_period'];
			$this->api_secret_key  = $options['apikey'];
			if ( isset( $options['installkey'] ) && $options['installkey'] ) {
				$this->install_key = trim( $options['installkey'] );
			} else {
				$this->install_key = trim( $this->get_option( $this->pue_install_key ) );
			}

		}

		/**
		 * Set all the download query array
		 *
		 * @param array $download_query
		 */
		private function set_download_query( $download_query = array() ) {

			if ( ! empty( $download_query ) ) {
				$this->download_query = $download_query;

				return;
			}

			//download query flag
			$this->download_query['pu_get_download'] = 1;

			//include current version
			if ( $version = $this->get_installed_version() ) {
				$this->download_query['pue_active_version'] = $version;
			}

			//the following is for install key inclusion (will apply later with PUE addons.)
			if ( isset( $this->install_key ) ) {
				$this->download_query['pu_install_key'] = $this->install_key;
			}

			if ( ! empty( $this->api_secret_key ) ) {
				$this->download_query['pu_plugin_api'] = $this->api_secret_key;
			}

		}

		/**
		 * Get the download_query args
		 *
		 * @return array
		 */
		public function get_download_query() {
			if ( empty( $this->download_query ) ) {
				$this->set_download_query();
			}

			return apply_filters( 'pue_get_download_query', $this->download_query, $this->get_slug() );
		}


		/********************** General Functions **********************/

		/**
		 * Compile  a list of addons
		 *
		 * @param array $addons list of addons
		 *
		 * @return array list of addons
		 */
		public function build_addon_list( $addons = array() ) {
			$addons[] = $this->get_plugin_name();

			return $addons;
		}

		/**
		 * Inserts license key fields on license key page
		 *
		 * @param array $fields List of fields
		 *
		 * @return array Modified list of fields.
		 */
		public function do_license_key_fields( $fields ) {

			// we want to inject the following license settings at the end of the licenses tab
			$fields = self::array_insert_after_key(
						  'tribe-form-content-start', $fields, array(
								  $this->pue_install_key . '-heading' => array(
									  'type'  => 'heading',
									  'label' => $this->get_plugin_name(),
								  ),
								  $this->pue_install_key              => array(
									  'type'            => 'license_key',
									  'size'            => 'large',
									  'validation_type' => 'license_key',
									  'label'           => sprintf( __( 'License Key', 'tribe-events-calendar' ) ),
									  'tooltip'         => __( 'A valid license key is required for support and updates', 'tribe-events-calendar' ),
									  'parent_option'   => false,
								  ),
							  )
			);

			return $fields;
		}

		/**
		 * Inserts the javascript that makes the ajax checking
		 * work on the license key page
		 *
		 * @return void
		 */
		public function do_license_key_javascript() {
			?>
			<script>
				jQuery(document).ready(function ($) {
					$('#tribe-field-<?php echo $this->pue_install_key ?>').change(function () {
						<?php echo $this->pue_install_key ?>_validateKey();
					});
					<?php echo $this->pue_install_key ?>_validateKey();
				});
				function <?php echo $this->pue_install_key ?>_validateKey() {
					var this_id = '#tribe-field-<?php echo $this->pue_install_key ?>';
					if (jQuery(this_id + ' input').val() != '') {
						jQuery(this_id + ' .invalid-key').hide();
						jQuery(this_id + ' .valid-key').hide();
						jQuery(this_id + ' .tooltip').hide();
						jQuery(this_id + ' .ajax-loading-license').show();
						//strip whitespace from key
						var <?php echo $this->pue_install_key ?>_license_key = jQuery(this_id + ' input').val().replace(/^\s+|\s+$/g, "");
						jQuery(this_id + ' input').val(<?php echo $this->pue_install_key ?>_license_key);

						var data = { action: 'pue-validate-key_<?php echo $this->get_slug(); ?>', key: <?php echo $this->pue_install_key ?>_license_key };
						jQuery.post(ajaxurl, data, function (response) {
							var data = jQuery.parseJSON(response);
							jQuery(this_id + ' .ajax-loading-license').hide();
							if (data.status == '1') {
								jQuery(this_id + ' .valid-key').show();
								jQuery(this_id + ' .valid-key').html(data.message);
								jQuery(this_id + ' .invalid-key').hide();
							} else {
								jQuery(this_id + ' .invalid-key').show();
								jQuery(this_id + ' .invalid-key').html(data.message);
								jQuery(this_id + ' .valid-key').hide();
							}
						});
					}
				}
			</script>
		<?php
		}

		/**
		 * Filter the success message on license key page
		 *
		 * @param string $message
		 * @param string $tab
		 *
		 * @return string
		 */
		public function do_license_key_success_message( $message, $tab ) {

			if ( $tab != 'licenses' ) {
				return $message;
			}

			return '<div id="message" class="updated"><p><strong>' . __( 'License key(s) updated.', 'tribe-events-calendar' ) . '</strong></p></div>';

		}

		/**
		 * Echo JSON results for key validation
		 */
		public function ajax_validate_key() {
			$response           = array();
			$response['status'] = 0;
			if ( isset( $_POST['key'] ) ) {

				$queryArgs = array(
					'pu_install_key'          => trim( $_POST['key'] ),
					'pu_checking_for_updates' => '1'
				);

				//include version info
				$queryArgs['pue_active_version'] = $this->get_installed_version();

				global $wp_version;
				$queryArgs['wp_version'] = $wp_version;

				//include domain and multisite stats
				$queryArgs['domain'] = $_SERVER['SERVER_NAME'];

				if ( is_multisite() ) {
					$queryArgs['multisite']         = 1;
					$queryArgs['network_activated'] = is_plugin_active_for_network( $this->get_plugin_file() );
					global $wpdb;
					$queryArgs['active_sites'] = $wpdb->get_var( "SELECT count(blog_id) FROM $wpdb->blogs WHERE public = '1' AND archived = '0' AND spam = '0' AND deleted = '0'" );
				} else {
					$queryArgs['multisite']         = 0;
					$queryArgs['network_activated'] = 0;
					$queryArgs['active_sites']      = 1;
				}

				$pluginInfo = $this->request_info( $queryArgs );

				if ( empty( $pluginInfo ) ) {
					$response['message'] = __( 'Sorry, key validation server is not available.', 'tribe-events-calendar' );
				} elseif ( isset( $pluginInfo->api_expired ) && $pluginInfo->api_expired == 1 ) {
					$response['message'] = __( 'Sorry, this key is expired.', 'tribe-events-calendar' );

				} elseif ( isset( $pluginInfo->api_upgrade ) && $pluginInfo->api_upgrade == 1 ) {
					$problem             = __( 'Sorry, this key is out of installs.', 'tribe-events-calendar' );
					$helpful_link        = sprintf( '<a href="%s" target="_blank">%s</a>', 'http://m.tri.be/lz', __( 'Why am I seeing this message?' ) );
					$response['message'] = "$problem $helpful_link";
				} elseif ( isset( $pluginInfo->api_invalid ) && $pluginInfo->api_invalid == 1 ) {
					$response['message'] = __( 'Sorry, this key is not valid.', 'tribe-events-calendar' );
				} else {
					$response['status']     = 1;
					$response['message']    = sprintf( __( 'Valid Key! Expires on %s', 'tribe-events-calendar' ), $pluginInfo->expiration );
					$response['expiration'] = $pluginInfo->expiration;
				}
			} else {
				$response['message'] = sprintf( __( 'Hmmm... something\'s wrong with this validator. Please contact <a href="%s">support.</a>', 'tribe-events-calendar' ), 'http://m.tri.be/1u' );
			}
			echo json_encode( $response );
			exit;
		}


		/**
		 * Echo JSON formatted errors
		 */
		function display_json_error() {
			$pluginInfo       = $this->json_error;
			$update_dismissed = $this->get_option( $this->dismiss_upgrade );

			$is_dismissed = ! empty( $update_dismissed ) && in_array( $pluginInfo->version, $update_dismissed ) ? true : false;

			if ( $is_dismissed ) {
				return;
			}

			//only display messages if there is a new version of the plugin.
			if ( version_compare( $pluginInfo->version, $this->get_installed_version(), '>' ) ) {
				if ( $pluginInfo->api_invalid && current_user_can( 'administrator' ) ) {
					$msg = str_replace( '%plugin_name%', '<b>' . $this->get_plugin_name() . '</b>', $pluginInfo->api_invalid_message );
					$msg = str_replace( '%plugin_slug%', $this->get_slug(), $msg );
					$msg = str_replace( '%update_url%', $this->get_pue_update_url(), $msg );
					$msg = str_replace( '%version%', $pluginInfo->version, $msg );
				}

				if ( isset( $msg ) ) {
					//Dismiss code idea below is obtained from the Gravity Forms Plugin by rocketgenius.com
					?>
					<div class="updated" style="padding:5px; position:relative;" id="pu_dashboard_message"><?php echo $msg ?>
						<a href="javascript:void(0);" onclick="PUDismissUpgrade();" style='float:right;'>[X]</a>
					</div>
					<script type="text/javascript">
						function PUDismissUpgrade() {
							jQuery("#pu_dashboard_message").slideUp();
							jQuery.post(ajaxurl, {action: "<?php echo $this->dismiss_upgrade; ?>", version: "<?php echo $pluginInfo->version; ?>", cookie: encodeURIComponent(document.cookie)});
						}
					</script>
				<?php
				}
			}
		}

		/**
		 * Retrieve plugin info from the configured API endpoint.
		 *
		 * @param array $queryArgs Additional query arguments to append to the request. Optional.
		 *
		 * @uses wp_remote_get()
		 * @return string $pluginInfo
		 */
		function request_info( $queryArgs = array() ) {
			//Query args to append to the URL. Plugins can add their own by using a filter callback (see add_query_arg_filter()).
			$queryArgs['installed_version'] = $this->get_installed_version();
			$queryArgs['pu_request_plugin'] = $this->get_slug();

			if ( empty( $queryArgs['pu_plugin_api'] ) && ! empty( $this->api_secret_key ) ) {
				$queryArgs['pu_plugin_api'] = $this->api_secret_key;
			}

			if ( empty( $queryArgs['pu_install_key'] ) && ! empty( $this->install_key ) ) {
				$queryArgs['pu_install_key'] = $this->install_key;
			}

			//include version info
			$queryArgs['pue_active_version'] = $this->get_installed_version();

			global $wp_version;
			$queryArgs['wp_version'] = $wp_version;

			//include domain and multisite stats
			$queryArgs['domain'] = $_SERVER['SERVER_NAME'];

			if ( is_multisite() ) {
				$queryArgs['multisite']         = 1;
				$queryArgs['network_activated'] = is_plugin_active_for_network( $this->get_plugin_file() );
				global $wpdb;
				$queryArgs['active_sites'] = $wpdb->get_var( "SELECT count(blog_id) FROM $wpdb->blogs WHERE public = '1' AND archived = '0' AND spam = '0' AND deleted = '0'" );

			} else {
				$queryArgs['multisite']         = 0;
				$queryArgs['network_activated'] = 0;
				$queryArgs['active_sites']      = 1;
			}

			$queryArgs = apply_filters( 'tribe_puc_request_info_query_args-' . $this->get_slug(), $queryArgs );

			//Various options for the wp_remote_get() call. Plugins can filter these, too.
			$options = array(
				'timeout' => 15, //seconds
				'headers' => array(
					'Accept' => 'application/json'
				),
			);
			$options = apply_filters( 'tribe_puc_request_info_options-' . $this->get_slug(), $options );

			$url = $this->get_pue_update_url();
			if ( ! empty( $queryArgs ) ) {
				$url = add_query_arg( $queryArgs, $url );
			}

			// Cache the API call so it only needs to be made once per plugin per page load.
			static $plugin_info_cache;
			$key = crc32( implode( '', $queryArgs ) );
			if ( isset( $plugin_info_cache[$key] ) ) {
				return $plugin_info_cache[$key];
			}

			$result = wp_remote_get(
				$url,
				$options
			);

			//Try to parse the response
			$pluginInfo = null;
			if ( ! is_wp_error( $result ) && isset( $result['response']['code'] ) && ( $result['response']['code'] == 200 ) && ! empty( $result['body'] ) ) {
				$pluginInfo = Tribe_PU_PluginInfo::from_json( $result['body'] );
			}
			$pluginInfo = apply_filters( 'tribe_puc_request_info_result-' . $this->get_slug(), $pluginInfo, $result );

			$plugin_info_cache[$key] = $pluginInfo;

			return $pluginInfo;
		}

		/**
		 * Retrieve the latest update (if any) from the configured API endpoint.
		 *
		 * @uses TribePluginUpdateEngineChecker::request_info()
		 *
		 * @return TribePluginUpdateUtility An instance of TribePluginUpdateUtility, or NULL when no updates are available.
		 */
		function request_update() {
			//For the sake of simplicity, this function just calls request_info()
			//and transforms the result accordingly.
			$pluginInfo = $this->request_info( array( 'pu_checking_for_updates' => '1' ) );
			if ( $pluginInfo == null ) {
				return null;
			}
			//admin display for if the update check reveals that there is a new version but the API key isn't valid.
			if ( isset( $pluginInfo->api_invalid ) ) { //we have json_error returned let's display a message
				$this->json_error = $pluginInfo;
				add_action( 'admin_notices', array( &$this, 'display_json_error' ) );

				return null;
			}

			if ( isset( $pluginInfo->new_install_key ) ) {
				$this->update_option( $this->pue_install_key, $pluginInfo->new_install_key );
			}

			//need to correct the download url so it contains the custom user data (i.e. api and any other paramaters)

			$download_query = $this->get_download_query();
			if ( ! empty( $download_query ) ) {
				$pluginInfo->download_url = add_query_arg( $download_query, $pluginInfo->download_url );
			}

			return TribePluginUpdateUtility::from_plugin_info( $pluginInfo );
		}


		/**
		 * Display the upgrade message in the plugin list under the plugin.
		 *
		 * @param $plugin_data
		 */
		function in_plugin_update_message( $plugin_data ) {
			$plugininfo = $this->json_error;
			//only display messages if there is a new version of the plugin.
			if ( is_object( $plugininfo ) && version_compare( $plugininfo->version, $this->get_installed_version(), '>' ) ) {
				if ( $plugininfo->api_invalid ) {
					$msg = str_replace( '%plugin_name%', '<strong>' . $this->get_plugin_name() . '</strong>', $plugininfo->api_inline_invalid_message );
					$msg = str_replace( '%plugin_slug%', $this->get_slug(), $msg );
					$msg = str_replace( '%update_url%', $this->get_pue_update_url(), $msg );
					$msg = str_replace( '%version%', $plugininfo->version, $msg );
					$msg = str_replace( '%changelog%', '<a class="thickbox" title="' . $this->get_plugin_name() . '" href="plugin-install.php?tab=plugin-information&plugin=' . $this->get_slug() . '&TB_iframe=true&width=640&height=808">what\'s new</a>', $msg );
					echo '</tr><tr class="plugin-update-tr"><td colspan="3" class="plugin-update"><div class="update-message">' . $msg . '</div></td>';
				}
			}
		}


		/**
		 * Display a changelog when the api key is missing.
		 */
		function display_changelog() {
			//contents of changelog display page when api-key is invalid or missing.  It will ONLY show the changelog (hook into existing thickbox?)
		}

		/**
		 * Update option to dismiss the upgrade notice.
		 */
		function dashboard_dismiss_upgrade() {
			$os_ary = $this->get_option( $this->dismiss_upgrade );
			if ( ! is_array( $os_ary ) ) {
				$os_ary = array();
			}

			$os_ary[] = $_POST['version'];
			$this->update_option( $this->dismiss_upgrade, $os_ary );
		}

		/**
		 * Get the currently installed version of the plugin.
		 *
		 * @return string Version number.
		 */
		function get_installed_version() {
			if ( function_exists( 'get_plugins' ) ) {
				$allPlugins = get_plugins();
				if ( array_key_exists( $this->get_plugin_file(), $allPlugins ) && array_key_exists( 'Version', $allPlugins[$this->get_plugin_file()] ) ) {
					return $allPlugins[$this->get_plugin_file()]['Version'];
				}
			}
		}

		/**
		 * Get MU compatible options.
		 *
		 * @param string     $option_key
		 * @param bool|mixed $default
		 *
		 * @return null|mixed
		 */
		function get_option( $option_key, $default = false ) {
			$return = $default;
			// Check if the option is in the site options
			if ( is_multisite() ) {
				$return = get_site_option( $option_key, $default );
			}
			// Fall back on local options
			if ( empty( $return ) ) {
				$return = get_option( $option_key, $default );
			}

			return $return;
		}

		/**
		 * Update MU compatible options.
		 *
		 * @param mixed $option_key
		 * @param mixed $value
		 */
		function update_option( $option_key, $value ) {
			// Check if the option is in the site options
			if ( is_network_admin() ) {
				update_site_option( $option_key, $value );
				delete_option( $option_key ); // make sure there is no local version of this option.
			} else {
				// Otherwise update it on the blog.
				update_option( $option_key, $value );
			}
		}

		/**
		 * Check for plugin updates.
		 *
		 * The results are stored in the DB option specified in $pue_option_name.
		 *
		 * @param array $updates
		 *
		 * @return void
		 */
		function check_for_updates( $updates = array() ) {
			$state = $this->get_option( $this->pue_option_name );
			if ( empty( $state ) ) {
				$state                 = new StdClass;
				$state->lastCheck      = 0;
				$state->checkedVersion = '';
				$state->update         = null;
			}

			$state->lastCheck      = time();
			$state->checkedVersion = $this->get_installed_version();
			$this->update_option( $this->pue_option_name, $state ); //Save before checking in case something goes wrong

			$state->update = $this->request_update();

			// If a null update was returned, skip the end of the function.
			if ( $state->update == null ) {
				return $updates;
			}

			//Is there an update to insert?
			if ( version_compare( $state->update->version, $this->get_installed_version(), '>' ) ) {
				$updates->response[$this->get_plugin_file()] = $state->update->to_wp_format();
			}

			$this->update_option( $this->pue_option_name, $state );
			add_action( 'after_plugin_row_' . $this->get_plugin_file(), array( &$this, 'in_plugin_update_message' ) );

			return $updates;
		}

		/**
		 * Intercept plugins_api() calls that request information about our plugin and
		 * use the configured API endpoint to satisfy them.
		 *
		 * @see plugins_api()
		 *
		 * @param mixed        $result
		 * @param string       $action
		 * @param array|object $args
		 *
		 * @return mixed
		 */
		function inject_info( $result, $action = null, $args = null ) {
			$relevant = ( $action == 'plugin_information' ) && isset( $args->slug ) && ( $args->slug == $this->slug );
			if ( ! $relevant ) {
				return $result;
			}

			$pluginInfo = $this->request_info( array( 'pu_checking_for_updates' => '1' ) );
			if ( $pluginInfo ) {
				return $pluginInfo->to_wp_format();
			}

			return $result;
		}

		/**
		 * Register a callback for filtering query arguments.
		 *
		 * The callback function should take one argument - an associative array of query arguments.
		 * It should return a modified array of query arguments.
		 *
		 * @uses add_filter() This method is a convenience wrapper for add_filter().
		 *
		 * @param callback $callback
		 *
		 * @return void
		 */
		function add_query_arg_filter( $callback ) {
			add_filter( 'tribe_puc_request_info_query_args-' . $this->get_slug(), $callback );
		}

		/**
		 * Register a callback for filtering arguments passed to wp_remote_get().
		 *
		 * The callback function should take one argument - an associative array of arguments -
		 * and return a modified array or arguments. See the WP documentation on wp_remote_get()
		 * for details on what arguments are available and how they work.
		 *
		 * @uses add_filter() This method is a convenience wrapper for add_filter().
		 *
		 * @param callback $callback
		 *
		 * @return void
		 */
		function add_http_request_arg_filter( $callback ) {
			add_filter( 'tribe_puc_request_info_options-' . $this->get_slug(), $callback );
		}

		/**
		 * Register a callback for filtering the plugin info retrieved from the external API.
		 *
		 * The callback function should take two arguments. If the plugin info was retrieved
		 * successfully, the first argument passed will be an instance of  Tribe_PU_PluginInfo. Otherwise,
		 * it will be NULL. The second argument will be the corresponding return value of
		 * wp_remote_get (see WP docs for details).
		 *
		 * The callback function should return a new or modified instance of Tribe_PU_PluginInfo or NULL.
		 *
		 * @uses add_filter() This method is a convenience wrapper for add_filter().
		 *
		 * @param callback $callback
		 *
		 * @return void
		 */
		function add_result_filter( $callback ) {
			add_filter( 'tribe_puc_request_info_result-' . $this->get_slug(), $callback, 10, 2 );
		}

		/**
		 * Insert an array after a specified key within another array.
		 *
		 * @param $key
		 * @param $source_array
		 * @param $insert_array
		 *
		 * @return array
		 *
		 */
		public static function array_insert_after_key( $key, $source_array, $insert_array ) {
			if ( array_key_exists( $key, $source_array ) ) {
				$position     = array_search( $key, array_keys( $source_array ) ) + 1;
				$source_array = array_slice( $source_array, 0, $position, true ) + $insert_array + array_slice( $source_array, $position, null, true );
			} else {
				// If no key is found, then add it to the end of the array.
				$source_array += $insert_array;
			}

			return $source_array;
		}

		/**
		 * Add this plugin key to the list of keys
		 *
		 * @param array $keys
		 *
		 * @return array $keys
		 *
		 */
		public function return_install_key( $keys = array() ) {
			if ( ! empty( $this->install_key ) ) {
				$keys[$this->get_slug()] = $this->install_key;
			}

			return $keys;
		}
	}
}
?>