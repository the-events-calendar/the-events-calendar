<?php
/**
* Central Tribe Events Calendar class.
*/

// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }

if ( !class_exists( 'TribeEvents' ) ) {

	class TribeEvents {
		const EVENTSERROROPT = '_tribe_events_errors';
		const OPTIONNAME = 'tribe_events_calendar_options';
		const TAXONOMY = 'tribe_events_cat';
		const POSTTYPE = 'tribe_events';
		const VENUE_POST_TYPE = 'tribe_venue';
		const ORGANIZER_POST_TYPE = 'tribe_organizer';
		const PLUGIN_DOMAIN = 'tribe-events-calendar';
		const VERSION = '2.0.10';
		const FEED_URL = 'http://tri.be/category/products/feed/';
		const INFO_API_URL = 'http://wpapi.org/api/plugin/the-events-calendar.php';
		const WP_PLUGIN_URL = 'http://wordpress.org/extend/plugins/the-events-calendar/';

		protected $postTypeArgs = array(
			'public' => true,
			'rewrite' => array('slug' => 'event', 'with_front' => false),
			'menu_position' => 6,
			'supports' => array('title','editor','excerpt','author','thumbnail', 'custom-fields'),
			'capability_type' => array('tribe_event', 'tribe_events'),
			'map_meta_cap' => true
		);
		protected $postVenueTypeArgs = array(
			'public' => true,
			'rewrite' => array('slug'=>'venue', 'with_front' => false),
			'show_ui' => true,
			'show_in_menu' => 0,
			'supports' => array('title', 'editor'),
			'capability_type' => array('tribe_venue', 'tribe_venues'),
			'map_meta_cap' => true,
			'exclude_from_search' => true
		);
		protected $postOrganizerTypeArgs = array(
			'public' => true,
			'rewrite' => false,
			'show_ui' => true,
			'show_in_nav_menus' => false,
			'show_in_menu' => 0,
			'menu_position' => 6,
			'supports' => array(''),
			'capability_type' => array('tribe_organizer', 'tribe_organizers'),
			'map_meta_cap' => true,
			'exclude_from_search' => true
		);
		protected $taxonomyLabels;

		public static $tribeUrl = 'http://tri.be/';
		public static $addOnPath = 'shop/';
		public static $supportPath = 'support/';
		public static $refQueryString = '?ref=tec-plugin';
		public static $dotOrgSupportUrl = 'http://wordpress.org/tags/the-events-calendar';

		protected static $instance;
		protected $rewriteSlug = 'events';
		protected $rewriteSlugSingular = 'event';
		protected $taxRewriteSlug = 'event/category';
		protected $tagRewriteSlug = 'event/tag';
		protected $monthSlug = 'month';
		protected $pastSlug = 'past';
		protected $upcomingSlug = 'upcoming';
		protected $postExceptionThrown = false;
		protected $optionsExceptionThrown = false;
		protected static $options;
		public $displaying;
		public $pluginDir;
		public $pluginPath;
		public $pluginUrl;
		public $pluginName;
		public $date;
		protected $tabIndexStart = 2000;
		
		public $form_errors = array();
		public $form_message = array();

		public $metaTags = array(
			'_EventAllDay',
			'_EventStartDate',
			'_EventEndDate',
			'_EventVenueID',
			'_EventShowMapLink',
			'_EventShowMap',
			'_EventCost',
			'_EventOrganizerID',
			'_EventPhone',
			'_EventHideFromUpcoming',
			self::EVENTSERROROPT
		);

		public $venueTags = array(
			'_VenueVenue',
			'_VenueCountry',
			'_VenueAddress',
			'_VenueCity',
			'_VenueStateProvince',
			'_VenueState',
			'_VenueProvince',
			'_VenueZip',
			'_VenuePhone'
		);

		public $organizerTags = array(
			'_OrganizerOrganizer',
			'_OrganizerEmail',
			'_OrganizerWebsite',
			'_OrganizerPhone'
		);

		public $states = array();
		public $currentPostTimestamp;
		public $daysOfWeekShort;
		public $daysOfWeek;
		public $daysOfWeekMin;
		public $monthsFull;
		public $monthsShort;

		/* Static Singleton Factory Method */
		public static function instance() {
			if (!isset(self::$instance)) {
				$className = __CLASS__;
				self::$instance = new $className;
			}
			return self::$instance;
		}		

		/**
		 * Initializes plugin variables and sets up WordPress hooks/actions.
		 *
		 * @return void
		 */
		protected function __construct( ) {
			$this->pluginPath = trailingslashit( dirname( dirname(__FILE__) ) );
			$this->pluginDir = trailingslashit( basename( $this->pluginPath ) );
			$this->pluginUrl = plugins_url().'/'.$this->pluginDir;
			if (self::supportedVersion('wordpress') && self::supportedVersion('php')) {
				register_deactivation_hook( __FILE__, array( $this, 'on_deactivate' ) );
				$this->addFilters();
				$this->addActions();
				$this->loadLibraries();
			} else {
				// Either PHP or WordPress version is inadequate so we simply return an error.
				add_action('init', array($this,'loadTextDomain'));
				add_action('admin_head', array($this,'notSupportedError'));
			}
		}

		/**
		 *Load all the required library files.
		 **/
		protected function loadLibraries() {
			// Exceptions Helper
			require_once( 'tribe-event-exception.class.php' );

			// Load Template Tags
			require_once( $this->pluginPath.'public/template-tags/general.php' );
			require_once( $this->pluginPath.'public/template-tags/calendar.php' );
			require_once( $this->pluginPath.'public/template-tags/loop.php' );
			require_once( $this->pluginPath.'public/template-tags/google-map.php' );
			require_once( $this->pluginPath.'public/template-tags/organizer.php' );
			require_once( $this->pluginPath.'public/template-tags/venue.php' );
			require_once( $this->pluginPath.'public/template-tags/date.php' );
			require_once( $this->pluginPath.'public/template-tags/link.php' );

			// Load Advanced Functions
			require_once( $this->pluginPath.'public/advanced-functions/event.php' );
			require_once( $this->pluginPath.'public/advanced-functions/venue.php' );
			require_once( $this->pluginPath.'public/advanced-functions/organizer.php' );

			// Load Deprecated Template Tags
			if ( ! defined( 'TRIBE_DISABLE_DEPRECATED_TAGS' ) ) {
				require_once( 'template-tags-deprecated.php' );
			}

			// Load Classes
			require_once( 'widget-list.class.php' );
			require_once( 'tribe-admin-events-list.class.php' );
			require_once( 'tribe-date-utils.class.php' );
			require_once( 'tribe-templates.class.php' );
			require_once( 'tribe-event-api.class.php' );
			require_once( 'tribe-event-query.class.php' );
			require_once( 'tribe-view-helpers.class.php' );
			require_once( 'tribe-the-events-calendar-import.class.php' );
			require_once( 'tribe-debug-bar.class.php' );

			// App Shop
			if (!defined("TRIBE_HIDE_UPSELL") || TRIBE_HIDE_UPSELL !== true ){
				require_once( 'tribe-app-shop.class.php' );
			}

			// Tickets
			require_once( 'tickets/tribe-ticket-object.php' );
			require_once( 'tickets/tribe-tickets.php' );
			require_once( 'tickets/tribe-tickets-metabox.php' );

		}

		protected function addFilters() {
			add_filter( 'post_class', array( $this, 'post_class') );
			add_filter( 'body_class', array( $this, 'body_class' ) );
			add_filter( 'query_vars',		array( $this, 'eventQueryVars' ) );
			add_filter( 'admin_body_class', array($this, 'admin_body_class') );
			//add_filter( 'the_content', array($this, 'emptyEventContent' ), 1 );
			add_filter( 'wp_title', array($this, 'maybeAddEventTitle' ), 10, 2 );
			add_filter( 'bloginfo_rss',	array($this, 'add_space_to_rss' ) );
			add_filter( 'post_type_link', array($this, 'addDateToRecurringEvents'), 10, 2 );
			add_filter( 'post_updated_messages', array($this, 'updatePostMessage') );
	
			/* Add nav menu item - thanks to http://wordpress.org/extend/plugins/cpt-archives-in-nav-menus/ */
			add_filter( 'nav_menu_items_' . TribeEvents::POSTTYPE, array( $this, 'add_events_checkbox_to_menu' ), null, 3 );
			add_filter( 'wp_nav_menu_objects', array( $this, 'add_current_menu_item_class_to_events'), null, 2);
			
			add_filter( 'generate_rewrite_rules', array( $this, 'filterRewriteRules' ) );
			
			if ( !is_admin() )
				add_filter( 'get_comment_link', array( $this, 'newCommentLink' ), 10, 2 );
		}

		protected function addActions() {
			add_action( 'init', array( $this, 'init'), 10 );
			add_action( 'template_redirect', array( $this, 'loadStyle' ) );
			add_action( 'admin_menu', array( $this, 'addEventBox' ) );	
			add_action( 'wp_insert_post', array( $this, 'addPostOrigin' ), 10, 2 );		
			add_action( 'save_post', array( $this, 'addEventMeta' ), 15, 2 );
			add_action( 'save_post', array( $this, 'save_venue_data' ), 16, 2 );
			add_action( 'save_post', array( $this, 'save_organizer_data' ), 16, 2 );
			add_action( 'save_post', array( $this, 'addToPostAuditTrail' ), 10, 2 );
			add_action( 'save_post', array( $this, 'publishAssociatedTypes'), 25, 2 );
			add_action( 'pre_get_posts', array( $this, 'setDate' ));
			add_action( 'wp', array( $this, 'setDisplay' ));
			add_action( 'tribe_events_post_errors', array( 'TribeEventsPostException', 'displayMessage' ) );
			add_action( 'tribe_settings_top', array( 'TribeEventsOptionsException', 'displayMessage') );
			add_action( 'admin_enqueue_scripts', array( $this, 'addAdminScriptsAndStyles' ) );
			add_action( 'plugins_loaded', array( $this, 'accessibleMonthForm'), -10 );
			add_action( 'the_post', array( $this, 'setReccuringEventDates' ) );			
			add_action( "trash_" . TribeEvents::VENUE_POST_TYPE, array($this, 'cleanupPostVenues'));
			add_action( "trash_" . TribeEvents::ORGANIZER_POST_TYPE, array($this, 'cleanupPostOrganizers'));
			add_action( "wp_ajax_tribe_event_validation", array($this,'ajax_form_validate') );
			add_action( 'tribe_debug', array( $this, 'renderDebug' ), 10, 2 );
			
			if( defined('TRIBE_SHOW_EVENT_AUDITING') && TRIBE_SHOW_EVENT_AUDITING )
				add_action('tribe_events_details_bottom', array($this,'showAuditingData') );
				
			// noindex grid view
			add_action('wp_head', array( $this, 'noindex_months' ) );
			add_action( 'plugin_row_meta', array( $this, 'addMetaLinks' ), 10, 2 );
			// organizer and venue
			add_action( 'tribe_venue_table_top', array($this, 'displayEventVenueInput') );
			add_action( 'tribe_organizer_table_top', array($this, 'displayEventOrganizerInput') );
			if( !defined('TRIBE_HIDE_UPSELL') || !TRIBE_HIDE_UPSELL ) {
				add_action( 'wp_dashboard_setup', array( $this, 'dashboardWidget' ) );
				add_action( 'tribe_events_cost_table', array($this, 'maybeShowMetaUpsell'));
			}
			// option pages
			add_action( '_admin_menu', array( $this, 'initOptions' ) );
			add_action( 'tribe_settings_do_tabs', array( $this, 'doSettingTabs' ) );
			add_action( 'tribe_settings_content_tab_help', array( $this, 'doHelpTab' ) );
			// add-on compatibility
			add_action( 'admin_notices', array( $this, 'checkAddOnCompatibility' ), 200 );
			
			add_action( 'wp_before_admin_bar_render', array( $this, 'addToolbarItems' ), 10 );
			add_action( 'admin_notices', array( $this, 'activationMessage' ) );
			add_action( 'all_admin_notices', array( $this, 'addViewCalendar' ) );
			add_action( 'admin_head', array( $this, 'setInitialMenuMetaBoxes' ), 500 );
			add_action( 'plugin_action_links_' . trailingslashit( $this->pluginDir ) . 'the-events-calendar.php', array( $this, 'addLinksToPluginActions' ) );
			add_action( 'admin_menu', array( $this, 'addHelpAdminMenuItem' ), 50 );
			add_action( 'comment_form', array( $this, 'addHiddenRecurringField' ) );
		}

		public static function ecpActive( $version = '2.0.7' ) {
			return class_exists( 'TribeEventsPro' ) && defined('TribeEventsPro::VERSION') && version_compare( TribeEventsPro::VERSION, $version, '>=');
		}

		/**
		 * Add code to tell search engines not to index the grid view of the 
		 * calendar.  Users were seeing 100s of months being indexed.
		 */
		function noindex_months() {
			if (get_query_var('eventDisplay') == 'month') {
				echo " <meta name=\"robots\" content=\"noindex, follow\"/>\n";
			} 
		}
		
		/**
		 * Run on applied action init
		 */
		public function init() {
			$this->loadTextDomain();
			$this->pluginName = __( 'The Events Calendar', 'tribe-events-calendar' );
			$this->rewriteSlug = sanitize_title($this->getOption('eventsSlug', 'events'));
			$this->rewriteSlugSingular = sanitize_title($this->getOption('singleEventSlug', 'event'));
			$this->taxRewriteSlug = $this->rewriteSlug . '/' . sanitize_title(__( 'category', 'tribe-events-calendar' ));
			$this->tagRewriteSlug = $this->rewriteSlug . '/' . sanitize_title(__( 'tag', 'tribe-events-calendar' ));
			$this->monthSlug = sanitize_title(__('month', 'tribe-events-calendar'));
			$this->upcomingSlug = sanitize_title(__('upcoming', 'tribe-events-calendar'));
			$this->pastSlug = sanitize_title(__('past', 'tribe-events-calendar'));
			$this->postTypeArgs['rewrite']['slug'] = sanitize_title($this->rewriteSlugSingular);
			$this->postVenueTypeArgs['rewrite']['slug'] = sanitize_title(__( 'venue', 'tribe-events-calendar' ));
			$this->postVenueTypeArgs['show_in_nav_menus'] = class_exists( 'TribeEventsPro' ) ? true : false;			
			$this->currentDay = '';
			$this->errors = '';
			TribeEventsQuery::init();
			$this->registerPostType();

			//If the custom post type's rewrite rules have not been generated yet, flush them. (This can happen on reactivations.)
			if(is_array(get_option('rewrite_rules')) && !array_key_exists($this->rewriteSlugSingular.'/[^/]+/([^/]+)/?$',get_option('rewrite_rules'))) {
				TribeEvents::flushRewriteRules();
			}
			self::debug(sprintf(__('Initializing Tribe Events on %s','tribe-events-calendar'),date('M, jS \a\t h:m:s a')));
			$this->maybeMigrateDatabase();
			$this->maybeRenameOptions();
			$this->maybeSetTECVersion();
		}

		public function maybeMigrateDatabase( ) {
			// future migrations should actually check the db_version
			if( !get_option('tribe_events_db_version') ) {
				global $wpdb; 
				// rename option
				update_option(self::OPTIONNAME, get_option('sp_events_calendar_options'));
				delete_option('sp_events_calendar_options');

				// update post type names
				$wpdb->update($wpdb->posts, array( 'post_type' => self::POSTTYPE ), array( 'post_type' => 'sp_events') );
				$wpdb->update($wpdb->posts, array( 'post_type' => self::VENUE_POST_TYPE ), array( 'post_type' => 'sp_venue') );
				$wpdb->update($wpdb->posts, array( 'post_type' => self::ORGANIZER_POST_TYPE ), array( 'post_type' => 'sp_organizer') );

				// update taxonomy names
				$wpdb->update($wpdb->term_taxonomy, array( 'taxonomy' => self::TAXONOMY ), array( 'taxonomy' => 'sp_events_cat') );
				update_option('tribe_events_db_version', '2.0.1');
			}
		}
		
		public function maybeRenameOptions() {
			if ( version_compare( get_option('tribe_events_db_version'), '2.0.6', '<' ) ) {
				$option_names = array(
					'spEventsTemplate' => 'tribeEventsTemplate',
					'spEventsBeforeHTML' => 'tribeEventsBeforeHTML',
					'spEventsAfterHTML' => 'tribeEventsAfterHTML',
				);
				$old_option_names = array_keys( $option_names );
				$new_option_names = array_values( $option_names );
				$new_options = array();
				$current_options = self::getOptions();
				for ( $i = 0; $i < count( $old_option_names ); $i++ ) {
					$new_options[$new_option_names[$i]] = $this->getOption( $old_option_names[$i] );
					unset( $current_options[$old_option_names[$i]] );
				}
				$this->setOptions( wp_parse_args( $new_options, $current_options ) );
				update_option('tribe_events_db_version', '2.0.6');
			}
		}
		
		public function maybeSetTECVersion() {
			if ( version_compare($this->getOption('latest_ecp_version'), self::VERSION, '<') ) {
				$previous_versions = $this->getOption('previous_ecp_versions') ? $this->getOption('previous_ecp_versions') : array();
				$previous_versions[] = ($this->getOption('latest_ecp_version')) ? $this->getOption('latest_ecp_version') : '0';
				
				$this->setOption('previous_ecp_versions', $previous_versions);
				$this->setOption('latest_ecp_version', self::VERSION);
			}
		}


		/**
		 * Check add-ons to make sure they are supported by currently running TEC version.
		 *
		 * @since 2.0.5
		 * @author Paul Hughes
		 * @return void
		 */
		public function checkAddOnCompatibility() {
			$operator = apply_filters( 'tribe_tec_addons_comparison_operator', '!=' );
			$output = '';
			$bad_versions = array();
			$tec_addons_required_versions = array();
			$out_of_date_addons = array();
			$update_link = get_admin_url() . 'plugins.php';
			$tec_out_of_date = false;
			
			$tec_addons_required_versions = (array) apply_filters('tribe_tec_addons', $tec_addons_required_versions);
			foreach ($tec_addons_required_versions as $plugin) {
				if ( version_compare( $plugin['required_version'], self::VERSION, $operator) ) {
					if ( isset( $plugin['current_version'] ) )
						$bad_versions[$plugin['plugin_name']] = $plugin['current_version'];
					else
						$bad_versions[$plugin['plugin_name']] = '';
					if ( ( isset( $plugin['plugin_dir_file'] ) ) )				
						$addon_short_path = $plugin['plugin_dir_file'];
					else
						$addon_short_path = null;
				}
				if ( version_compare( $plugin['required_version'], self::VERSION, '>' ) ) {
					$tec_out_of_date = true;
				}
			}
			if ( $tec_out_of_date == true ) {
				$plugin_short_path = basename( dirname( dirname( __FILE__ ) ) ) . '/the-events-calendar.php';
				$upgrade_path = wp_nonce_url( add_query_arg( array( 'action' => 'upgrade-plugin', 'plugin' => $plugin_short_path ), get_admin_url() . 'update.php' ), 'upgrade-plugin_' . $plugin_short_path );
				$output .= '<div class="error">';
				$output .= '<p>' . sprintf( __('Your version of The Events Calendar is not up-to-date with one of your The Events Calendar add-ons. Please %supdate now.%s', 'tribe-events-calendar'), '<a href="' . $upgrade_path . '">', '</a>') .'</p>';
				$output .= '</div>';
			} else {
				if ( !empty($bad_versions) ) {
					foreach ($bad_versions as $plugin => $version) {
						if ( $version )
							$out_of_date_addons[] = $plugin . ' ' . $version;
						else
							$out_of_date_addons[] = $plugin;
					}
					if ( count( $out_of_date_addons ) == 1 && $addon_short_path ) {
						$update_link = wp_nonce_url( add_query_arg( array( 'action' => 'upgrade-plugin', 'plugin' => $addon_short_path ), get_admin_url() . 'update.php' ), 'upgrade-plugin_' . $addon_short_path );
					}
					$output .= '<div class="error">';
					$output .= '<p>'.sprintf( __('The following plugins are out of date: <b>%s</b>. Please %supdate now%s. All add-ons contain dependencies on The Events Calendar and will not function properly unless paired with the right version. %sWant to pair an older version%s?', 'tribe-events-calendar'), join( $out_of_date_addons, ', ' ), '<a href="' . $update_link . '">', '</a>', '<a href="http://tri.be/version-relationships-in-modern-tribe-pluginsadd-ons/">', '</a>' ).'</p>';
					$output .= '</div>';
				}
			}
			if ( current_user_can( 'edit_plugins' ) ) {
				echo apply_filters('tribe_add_on_compatibility_errors', $output);
			}
		}

		/**
		 * init the settings API and add a hook to add your own setting tabs
		 *
		 * @since 2.0.5
		 * @author jkudish
		 * @return void
		 */
		public function initOptions() {

			require_once( 'tribe-settings.class.php' );
			require_once( 'tribe-settings-tab.class.php' );
			require_once( 'tribe-field.class.php' );
			require_once( 'tribe-validate.class.php' );

			TribeSettings::instance();
		}

		/**
		 * create setting tabs
		 *
		 * @since 2.0.5
		 * @author jkudish
		 * @return void
		 */
		public function doSettingTabs() {

			include_once($this->pluginPath.'admin-views/tribe-options-general.php');
			include_once($this->pluginPath.'admin-views/tribe-options-templates.php');
			
			$tribe_licences_tab_fields = array(
				'info-start' => array(
					'type' => 'html',
					'html' => '<div id="modern-tribe-info">'
				),
				'info-box-title' => array(
					'type' => 'html',
					'html' => '<h2>' . __('Licenses', 'tribe-events-calendar') . '</h2>',
				),
				'info-box-description' => array(
					'type' => 'html',
					'html' => '<p>' . __('The license key you received when completing your purchase will grant you access to support and updates. You do not need to enter the key below for the plugins to work, but you will need to enter it to get automatic updates, and our support team won\'t be able to help you out unless it is added and current.</p><p>Each plugin/add-on has its own unique license key. Simply paste the key into its appropriate field on the list below, and give it a moment to validate. You know you\'re set when a green expiration date appears alongside a "valid" message.</p><p>If you\'re seeing a red message telling you that your key isn\'t valid or is out of installs, it means that your key was not accepted. Visit <a href="http://tri.be">http://tri.be</a>, log in and navigate to <i>Account Central > Licenses</i> on the tri.be site to see if the key is tied to another site or past its expiration date. For more on automatic updates and using your license key, please see <a href="http://tri.be/updating-the-plugin/">this blog post</a>.</p><p>Not seeing an update but expecting one? In WordPress go to <i>Dashboard > Updates</i> and click "Check Again".', 'tribe-events-calendar') . '</p>',
				),
				'info-end' => array(
					'type' => 'html',
					'html' => '</div>',
				),
			);

			new TribeSettingsTab( 'general', __('General', 'tribe-events-calendar'), $generalTab );
			new TribeSettingsTab( 'template', __('Template', 'tribe-events-calendar'), $templatesTab );
			// If none of the addons are activated, do not show the licenses tab.
			if ( class_exists( 'TribeEventsPro' ) || class_exists( 'Event_Tickets_PRO' ) || class_exists( 'TribeCommunityEvents' ) || class_exists( 'Tribe_FB_Importer' ) ) {
				new TribeSettingsTab( 'licenses', __('Licenses', 'tribe-events-calendar'), array('priority' => '40',
					'fields' => apply_filters('tribe_license_fields', $tribe_licences_tab_fields) ) );
			}
			new TribeSettingsTab( 'help', __('Help', 'tribe-events-calendar'), array('priority' => 60, 'show_save' => false) );
			
		}

		public function doHelpTab() {
			include_once($this->pluginPath.'admin-views/tribe-options-help.php');
		}

		/**
		 * Test PHP and WordPress versions for compatibility
		 *
		 * @param string $system - system to be tested such as 'php' or 'wordpress'
		 * @return boolean - is the existing version of the system supported?
		 */
		public function supportedVersion($system) {
			if ($supported = wp_cache_get($system,'tribe_version_test')) {
				return $supported;
			} else {
				switch (strtolower($system)) {
					case 'wordpress' :
						$supported = version_compare(get_bloginfo('version'), '3.0', '>=');
						break;
					case 'php' :
						$supported = version_compare( phpversion(), '5.2', '>=');
						break;
				}
				$supported = apply_filters('tribe_events_supported_version',$supported,$system);
				wp_cache_set($system,$supported,'tribe_version_test');
				return $supported;
			}
		}

		public function notSupportedError() {
			if ( !self::supportedVersion('wordpress') ) {
				echo '<div class="error"><p>'.sprintf(__('Sorry, The Events Calendar requires WordPress %s or higher. Please upgrade your WordPress install.', 'tribe-events-calendar'),'3.0').'</p></div>';
			}
			if ( !self::supportedVersion('php') ) {
				echo '<div class="error"><p>'.sprintf(__('Sorry, The Events Calendar requires PHP %s or higher. Talk to your Web host about moving you to a newer version of PHP.', 'tribe-events-calendar'),'5.2').'</p></div>';
			}
		}

		public function add_current_menu_item_class_to_events( $items, $args ) {
			foreach($items as $item) {
				if($item->url == $this->getLink() ) {
					if ( is_singular( TribeEvents::POSTTYPE ) 
						|| is_singular( TribeEvents::VENUE_POST_TYPE ) 
						|| is_tax(TribeEvents::TAXONOMY) 
						|| ( ( tribe_is_upcoming() 
							|| tribe_is_past() 
							|| tribe_is_month() ) 
						&& isset($wp_query->query_vars['eventDisplay']) ) ) {
						$item->classes[] = 'current-menu-item current_page_item';
					}
					break;
				}
			}

			return $items;
		}

		public function add_events_checkbox_to_menu( $posts, $args, $post_type ) {
			global $_nav_menu_placeholder, $wp_rewrite;
			$_nav_menu_placeholder = ( 0 > $_nav_menu_placeholder ) ? intval($_nav_menu_placeholder) - 1 : -1;
			$archive_slug = $this->getLink();

			array_unshift( $posts, (object) array(
				'ID' => 0,
				'object_id' => $_nav_menu_placeholder,
				'post_content' => '',
				'post_excerpt' => '',
				'post_title' => $post_type['args']->labels->all_items,
				'post_type' => 'nav_menu_item',
				'type' => 'custom',
				'url' => $archive_slug,
			) );

			return $posts;			
		}

		/**
		 * Tribe debug function. usage: TribeEvents::debug('Message',$data,'log');
		 *
		 * @param string $title - message to display in log
		 * @param string $data - optional data to display
		 * @param string $format - optional format (log|warning|error|notice)
		 * @return void
		 * @author Peter Chester
		 */
		public static function debug($title,$data=false,$format='log') {
			do_action('tribe_debug',$title,$data,$format);
		}
		
		/**
		 * Render the debug logging to the php error log. This can be over-ridden by removing the filter.
		 *
		 * @param string $title - message to display in log
		 * @param string $data - optional data to display
		 * @param string $format - optional format (log|warning|error|notice)
		 * @return void
		 * @author Peter Chester
		 */
		public function renderDebug($title,$data=false,$format='log') {
			$format = ucfirst($format);
			if ($this->getOption('debugEvents')) {
				error_log($this->pluginName." $format: $title");
				if ($data && $data!='') {
					error_log($this->pluginName." $format: ".print_r($data,true));
				}
			}
		}

		public function get_event_taxonomy() {
			return self::TAXONOMY;
		}

		public function add_space_to_rss($title) {
			global $wp_query;
			if(get_query_var('eventDisplay') == 'upcoming' && get_query_var('post_type') == TribeEvents::POSTTYPE) {
				return $title . ' ';
			}

			return $title;
		}


		public function addDateToRecurringEvents($permalink, $post) {
			if(  function_exists('tribe_is_recurring_event') && $post->post_type == self::POSTTYPE && tribe_is_recurring_event($post->ID) && !is_search()) {
				if( is_admin() && (!isset($post->EventStartDate) || !$post->EventStartDate) ) {
					if( isset($_REQUEST['eventDate'] ) ) {
						$post->EventStartDate = $_REQUEST['eventDate'];
					} else	{
						$post->EventStartDate = TribeEvents::getRealStartDate( $post->ID );
					}
				}

				// prevent any call from outside the tribe from appending bad date on the end of recurring permalinks (looking at Yoast WP SEO)
				if(!isset($post->EventStartDate) || !$post->EventStartDate)
					return $permalink;
		
				if( '' == get_option('permalink_structure') ) {
					return add_query_arg('eventDate', TribeDateUtils::dateOnly( $post->EventStartDate ), $permalink ); 					
				} else {
					return trailingslashit($permalink) . TribeDateUtils::dateOnly( isset($post->EventStartDate) ? $post->EventStartDate : null );
				}
			}
			return $permalink;
		}

		// sorts the meta to ensure we are getting the real start date
		public static function getRealStartDate( $postId ) {
			$start_dates = get_post_meta( $postId, '_EventStartDate' );

			if( is_array( $start_dates ) && sizeof( $start_dates ) > 0 ) {
				sort($start_dates);
				return $start_dates[0];
			}

			return null;
		}		

		public function maybeAddEventTitle($title, $sep = null){
			if(get_query_var('eventDisplay') == 'upcoming'){
				$new_title = apply_filters( 'tribe_upcoming_events_title', __("Upcoming Events", 'tribe-events-calendar'). ' '.$sep . ' ' . $title, $sep );
			}elseif(get_query_var('eventDisplay') == 'past'){
					$new_title = apply_filters( 'tribe_past_events_title', __("Past Events", 'tribe-events-calendar') . ' '. $sep . ' ' . $title, $sep );

			}elseif(get_query_var('eventDisplay') == 'month'){
				if(get_query_var('eventDate')){
					$title_date = date_i18n("F, Y",strtotime(get_query_var('eventDate')));
					$new_title = apply_filters( 'tribe_month_grid_view_title', sprintf(__("Events for %s", 'tribe-events-calendar'), $title_date) . ' '. $sep . ' ' . $title, $sep, $title_date );
				}else{
					$new_title = apply_filters( 'tribe_events_this_month_title', sprintf(__("Events this month", 'tribe-events-calendar'),get_query_var('eventDate')) . ' '. $sep . ' ' . $title, $sep );
				}

			} elseif(get_query_var('eventDisplay') == 'day') {
				$title_date = date_i18n("F d, Y",strtotime(get_query_var('eventDate')));
				$new_title = apply_filters( 'tribe_events_day_view_title', sprintf(__("Events for %s", 'tribe-events-calendar'), $title_date) . ' '. $sep . ' ' . $title, $sep, $title_date );
         } elseif(get_query_var('post_type') == self::POSTTYPE && is_single() && $this->getOption('tribeEventsTemplate') != '' ) {
				global $post;
				$new_title = $post->post_title . ' '. $sep . ' ' . $title;
			} elseif(get_query_var('post_type') == self::VENUE_POST_TYPE && $this->getOption('tribeEventsTemplate') != '' ) {
				global $post;
				$new_title = apply_filters( 'tribe_events_venue_view_title', sprintf(__("Events at %s", 'tribe-events-calendar'), $post->post_title) . ' '. $sep . ' ' . $title,  $sep );
			} else {
				return $title;
			}


			return $new_title;

		}

		public function emptyEventContent( $content ) {
			global $post;
			if ( '' == $content && isset($post->post_type) && $post->post_type == self::POSTTYPE ) {
				$content = __('No description has been entered for this event.', 'tribe-events-calendar');
			}
			return $content;
		}

		public function accessibleMonthForm() {
			if ( isset($_GET['EventJumpToMonth']) && isset($_GET['EventJumpToYear'] )) {
				$_GET['eventDisplay'] = 'month';
				$_GET['eventDate'] = intval($_GET['EventJumpToYear']) . '-' . intval($_GET['EventJumpToMonth']);
			}
		}

		public function body_class( $c ) {
			if ( get_query_var('post_type') == self::POSTTYPE ) {
				if (! is_single() ) {
					if ( (tribe_is_upcoming() || tribe_is_past()) ) {
						$c[] = 'events-list';
					} else {
						$c[] = 'events-gridview';
					}
				} 
				if ( is_tax( self::TAXONOMY ) ) {
					$c[] = 'events-category';
					$category = get_term_by('name', single_cat_title( '', false ), self::TAXONOMY );
					
					$c[] = 'events-category-' . $category->slug;
				}
				if ( ! is_single() || tribe_is_showing_all() ) {
					$c[] = 'events-archive';
				}
				else {
					$c[] = 'events-single';
				}
			}
			global $post;
			if ( is_object($post) && tribe_is_venue( $post->ID ) ) {
					$c[] = 'events-venue';
			}
			
			
			return $c;
		}

		public function post_class( $c ) {
			global $post;
			if ( is_object($post) && isset($post->post_type) && $post->post_type == self::POSTTYPE && $terms = get_the_terms( $post->ID , self::TAXONOMY ) ) {
				foreach ($terms as $term) {
					$c[] = 'cat_' . sanitize_html_class($term->slug, $term->term_taxonomy_id);
				}
			}
			return $c;
		}
		
		private function addCapabilities() {
			$role = get_role( 'administrator' );
			if ( $role ) {
				$role->add_cap( 'edit_tribe_event' );
				$role->add_cap( 'read_tribe_event' );
				$role->add_cap( 'delete_tribe_event' );
				$role->add_cap( 'delete_tribe_events');
				$role->add_cap( 'edit_tribe_events' );
				$role->add_cap( 'edit_others_tribe_events' );
				$role->add_cap( 'delete_others_tribe_events' );
				$role->add_cap( 'publish_tribe_events' );
				$role->add_cap( 'edit_published_tribe_events' );
				$role->add_cap( 'delete_published_tribe_events' );
				$role->add_cap( 'delete_private_tribe_events' );
				$role->add_cap( 'edit_private_tribe_events' );
				$role->add_cap( 'read_private_tribe_events' );
			
				$role->add_cap( 'edit_tribe_venue' );
				$role->add_cap( 'read_tribe_venue' );
				$role->add_cap( 'delete_tribe_venue' );
				$role->add_cap( 'delete_tribe_venues');
				$role->add_cap( 'edit_tribe_venues' );
				$role->add_cap( 'edit_others_tribe_venues' );
				$role->add_cap( 'delete_others_tribe_venues' );
				$role->add_cap( 'publish_tribe_venues' );
				$role->add_cap( 'edit_published_tribe_venues' );
				$role->add_cap( 'delete_published_tribe_venues' );
				$role->add_cap( 'delete_private_tribe_venues' );
				$role->add_cap( 'edit_private_tribe_venues' );
				$role->add_cap( 'read_private_tribe_venues' );
			
				$role->add_cap( 'edit_tribe_organizer' );
				$role->add_cap( 'read_tribe_organizer' );
				$role->add_cap( 'delete_tribe_organizer' );
				$role->add_cap( 'delete_tribe_organizers');
				$role->add_cap( 'edit_tribe_organizers' );
				$role->add_cap( 'edit_others_tribe_organizers' );
				$role->add_cap( 'delete_others_tribe_organizers' );
				$role->add_cap( 'publish_tribe_organizers' );
				$role->add_cap( 'edit_published_tribe_organizers' );
				$role->add_cap( 'delete_published_tribe_organizers' );
				$role->add_cap( 'delete_private_tribe_organizers' );
				$role->add_cap( 'edit_private_tribe_organizers' );
				$role->add_cap( 'read_private_tribe_organizers' );
			}
			 
			$editor = get_role( 'editor' );
			if ( $editor ) {
				$editor->add_cap( 'edit_tribe_event' );
				$editor->add_cap( 'read_tribe_event' );
				$editor->add_cap( 'delete_tribe_event' );
				$editor->add_cap( 'delete_tribe_events');
				$editor->add_cap( 'edit_tribe_events' );
				$editor->add_cap( 'edit_others_tribe_events' );
				$editor->add_cap( 'delete_others_tribe_events' );
				$editor->add_cap( 'publish_tribe_events' );
				$editor->add_cap( 'edit_published_tribe_events' );
				$editor->add_cap( 'delete_published_tribe_events' );
				$editor->add_cap( 'delete_private_tribe_events' );
				$editor->add_cap( 'edit_private_tribe_events' );
				$editor->add_cap( 'read_private_tribe_events' );
			
				$editor->add_cap( 'edit_tribe_venue' );
				$editor->add_cap( 'read_tribe_venue' );
				$editor->add_cap( 'delete_tribe_venue' );
				$editor->add_cap( 'delete_tribe_venues');
				$editor->add_cap( 'edit_tribe_venues' );
				$editor->add_cap( 'edit_others_tribe_venues' );
				$editor->add_cap( 'delete_others_tribe_venues' );
				$editor->add_cap( 'publish_tribe_venues' );
				$editor->add_cap( 'edit_published_tribe_venues' );
				$editor->add_cap( 'delete_published_tribe_venues' );
				$editor->add_cap( 'delete_private_tribe_venues' );
				$editor->add_cap( 'edit_private_tribe_venues' );
				$editor->add_cap( 'read_private_tribe_venues' );
			
				$editor->add_cap( 'edit_tribe_organizer' );
				$editor->add_cap( 'read_tribe_organizer' );
				$editor->add_cap( 'delete_tribe_organizer' );
				$editor->add_cap( 'delete_tribe_organizers');
				$editor->add_cap( 'edit_tribe_organizers' );
				$editor->add_cap( 'edit_others_tribe_organizers' );
				$editor->add_cap( 'delete_others_tribe_organizers' );
				$editor->add_cap( 'publish_tribe_organizers' );
				$editor->add_cap( 'edit_published_tribe_organizers' );
				$editor->add_cap( 'delete_published_tribe_organizers' );
				$editor->add_cap( 'delete_private_tribe_organizers' );
				$editor->add_cap( 'edit_private_tribe_organizers' );
				$editor->add_cap( 'read_private_tribe_organizers' );
			}
			
			$author = get_role( 'author' );
			if ( $author ) {		 
				$author->add_cap( 'edit_tribe_event' );
				$author->add_cap( 'read_tribe_event' );
				$author->add_cap( 'delete_tribe_event' );
				$author->add_cap( 'delete_tribe_events' );
				$author->add_cap( 'edit_tribe_events' );
				$author->add_cap( 'publish_tribe_events' );
				$author->add_cap( 'edit_published_tribe_events' );
				$author->add_cap( 'delete_published_tribe_events' );
				
				$author->add_cap( 'edit_tribe_venue' );
				$author->add_cap( 'read_tribe_venue' );
				$author->add_cap( 'delete_tribe_venue' );
				$author->add_cap( 'delete_tribe_venues' );
				$author->add_cap( 'edit_tribe_venues' );
				$author->add_cap( 'publish_tribe_venues' );
				$author->add_cap( 'edit_published_tribe_venues' );
				$author->add_cap( 'delete_published_tribe_venues' );
				
				$author->add_cap( 'edit_tribe_organizer' );
				$author->add_cap( 'read_tribe_organizer' );
				$author->add_cap( 'delete_tribe_organizer' );
				$author->add_cap( 'delete_tribe_organizers' );
				$author->add_cap( 'edit_tribe_organizers' );
				$author->add_cap( 'publish_tribe_organizers' );
				$author->add_cap( 'edit_published_tribe_organizers' );
				$author->add_cap( 'delete_published_tribe_organizers' );
			}
			
			$contributor = get_role( 'contributor' );
			if ( $contributor ) {
				$contributor->add_cap( 'edit_tribe_event' );
				$contributor->add_cap( 'read_tribe_event' );
				$contributor->add_cap( 'delete_tribe_event' );
				$contributor->add_cap( 'delete_tribe_events' );
				$contributor->add_cap( 'edit_tribe_events' );
				
				$contributor->add_cap( 'edit_tribe_venue' );
				$contributor->add_cap( 'read_tribe_venue' );
				$contributor->add_cap( 'delete_tribe_venue' );
				$contributor->add_cap( 'delete_tribe_venues' );
				$contributor->add_cap( 'edit_tribe_venues');
				
				$contributor->add_cap( 'edit_tribe_organizer' );
				$contributor->add_cap( 'read_tribe_organizer' );
				$contributor->add_cap( 'delete_tribe_organizer' );
				$contributor->add_cap( 'delete_tribe_organizers' );
				$contributor->add_cap( 'edit_tribe_organizers' );
			}
			
			$subscriber = get_role( 'subscriber' );
			if ( $subscriber ) {
				$subscriber->add_cap( 'read_tribe_event' );
				
				$subscriber->add_cap( 'read_tribe_organizer' );
	
				$subscriber->add_cap( 'read_tribe_venue' );
			}
		}

		public function registerPostType() {
			$this->generatePostTypeLabels();
			register_post_type(self::POSTTYPE, $this->postTypeArgs);
			register_post_type(self::VENUE_POST_TYPE, $this->postVenueTypeArgs);
			register_post_type(self::ORGANIZER_POST_TYPE, $this->postOrganizerTypeArgs);

			$this->addCapabilities();
			         
			register_taxonomy( self::TAXONOMY, self::POSTTYPE, array(
				'hierarchical' => true,
				'update_count_callback' => '',
				'rewrite' => array( 'slug'=> $this->taxRewriteSlug, 'with_front' => false ),
				'public' => true,
				'show_ui' => true,
				'labels' => $this->taxonomyLabels,
				'capabilities' => array( 
					'manage_terms' => 'publish_tribe_events',
					'edit_terms' => 'publish_tribe_events',
					'delete_terms' => 'publish_tribe_events',
					'assign_terms' => 'edit_tribe_events'
				)
			));
	
			if( $this->getOption('showComments','no') == 'yes' ) {
				add_post_type_support( self::POSTTYPE, 'comments');
			}
	
		}
		
		public function getVenuePostTypeArgs() {
			return $this->postVenueTypeArgs;
		}

		public function getOrganizerPostTypeArgs() {
			return $this->postOrganizerTypeArgs;
		}

		protected function generatePostTypeLabels() {
			$this->postTypeArgs['labels'] = array(
				'name' => __('Events', 'tribe-events-calendar'),
				'singular_name' => __('Event', 'tribe-events-calendar'),
				'add_new' => __('Add New', 'tribe-events-calendar'),
				'add_new_item' => __('Add New Event', 'tribe-events-calendar'),
				'edit_item' => __('Edit Event', 'tribe-events-calendar'),
				'new_item' => __('New Event', 'tribe-events-calendar'),
				'view_item' => __('View Event', 'tribe-events-calendar'),
				'search_items' => __('Search Events', 'tribe-events-calendar'),
				'not_found' => __('No events found', 'tribe-events-calendar'),
				'not_found_in_trash' => __('No events found in Trash', 'tribe-events-calendar')
			);
	
			$this->postVenueTypeArgs['labels'] = array(
				'name' => __('Venues', 'tribe-events-calendar'),
				'singular_name' => __('Venue', 'tribe-events-calendar'),
				'add_new' => __('Add New', 'tribe-events-calendar'),
				'add_new_item' => __('Add New Venue', 'tribe-events-calendar'),
				'edit_item' => __('Edit Venue', 'tribe-events-calendar'),
				'new_item' => __('New Venue', 'tribe-events-calendar'),
				'view_item' => __('View Venue', 'tribe-events-calendar'),
				'search_items' => __('Search Venues', 'tribe-events-calendar'),
				'not_found' => __('No venue found', 'tribe-events-calendar'),
				'not_found_in_trash' => __('No venues found in Trash', 'tribe-events-calendar')
			);
	
			$this->postOrganizerTypeArgs['labels'] = array(
				'name' => __('Organizers', 'tribe-events-calendar'),
				'singular_name' => __('Organizer', 'tribe-events-calendar'),
				'add_new' => __('Add New', 'tribe-events-calendar'),
				'add_new_item' => __('Add New Organizer', 'tribe-events-calendar'),
				'edit_item' => __('Edit Organizer', 'tribe-events-calendar'),
				'new_item' => __('New Organizer', 'tribe-events-calendar'),
				'view_item' => __('View Venue', 'tribe-events-calendar'),
				'search_items' => __('Search Organizers', 'tribe-events-calendar'),
				'not_found' => __('No organizer found', 'tribe-events-calendar'),
				'not_found_in_trash' => __('No organizers found in Trash', 'tribe-events-calendar')
			);
	
			$this->taxonomyLabels = array(
				'name' =>	__( 'Event Categories', 'tribe-events-calendar' ),
				'singular_name' =>	__( 'Event Category', 'tribe-events-calendar' ),
				'search_items' =>	__( 'Search Event Categories', 'tribe-events-calendar' ),
				'all_items' => __( 'All Event Categories', 'tribe-events-calendar' ),
				'parent_item' =>	__( 'Parent Event Category', 'tribe-events-calendar' ),
				'parent_item_colon' =>	__( 'Parent Event Category:', 'tribe-events-calendar' ),
				'edit_item' =>	__( 'Edit Event Category', 'tribe-events-calendar' ),
				'update_item' =>	__( 'Update Event Category', 'tribe-events-calendar' ),
				'add_new_item' =>	__( 'Add New Event Category', 'tribe-events-calendar' ),
				'new_item_name' =>	__( 'New Event Category Name', 'tribe-events-calendar' )
			);
	
		}

		public function updatePostMessage( $messages ) {
			global $post, $post_ID;

			$messages[self::POSTTYPE] = array(
				0 => '', // Unused. Messages start at index 1.
				1 => sprintf( __('Event updated. <a href="%s">View event</a>', 'tribe-events-calendar'), esc_url( get_permalink($post_ID) ) ),
				2 => __('Custom field updated.', 'tribe-events-calendar'),
				3 => __('Custom field deleted.', 'tribe-events-calendar'),
				4 => __('Event updated.', 'tribe-events-calendar'),
				/* translators: %s: date and time of the revision */
				5 => isset($_GET['revision']) ? sprintf( __('Event restored to revision from %s', 'tribe-events-calendar'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
				6 => sprintf( __('Event published. <a href="%s">View event</a>', 'tribe-events-calendar'), esc_url( get_permalink($post_ID) ) ),
				7 => __('Event saved.', 'tribe-events-calendar'),
				8 => sprintf( __('Event submitted. <a target="_blank" href="%s">Preview event</a>', 'tribe-events-calendar'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
				9 => sprintf( __('Event scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview event</a>', 'tribe-events-calendar'),
				// translators: Publish box date format, see http://php.net/date
				date_i18n( __( 'M j, Y @ G:i' , 'tribe-events-calendar'), strtotime( $post->post_date ) ), esc_url( get_permalink($post_ID) ) ),
				10 => sprintf( __('Event draft updated. <a target="_blank" href="%s">Preview event</a>', 'tribe-events-calendar'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
			);

			$messages[self::VENUE_POST_TYPE] = array(
				0 => '', // Unused. Messages start at index 1.
				1 => sprintf( __('Venue updated. <a href="%s">View venue</a>', 'tribe-events-calendar'), esc_url( get_permalink($post_ID) ) ),
				2 => __('Custom field updated.', 'tribe-events-calendar'),
				3 => __('Custom field deleted.', 'tribe-events-calendar'),
				4 => __('Venue updated.', 'tribe-events-calendar'),
				/* translators: %s: date and time of the revision */
				5 => isset($_GET['revision']) ? sprintf( __('Venue restored to revision from %s', 'tribe-events-calendar'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
				6 => sprintf( __('Venue published. <a href="%s">View venue</a>', 'tribe-events-calendar'), esc_url( get_permalink($post_ID) ) ),
				7 => __('Venue saved.'),
				8 => sprintf( __('Venue submitted. <a target="_blank" href="%s">Preview venue</a>', 'tribe-events-calendar'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
				9 => sprintf( __('Venue scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview venue</a>', 'tribe-events-calendar'),
				// translators: Publish box date format, see http://php.net/date
				date_i18n( __( 'M j, Y @ G:i' , 'tribe-events-calendar'), strtotime( $post->post_date ) ), esc_url( get_permalink($post_ID) ) ),
				10 => sprintf( __('Venue draft updated. <a target="_blank" href="%s">Preview venue</a>', 'tribe-events-calendar'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
				);

			$messages[self::ORGANIZER_POST_TYPE] = array(
				0 => '', // Unused. Messages start at index 1.
				1 => sprintf( __('Organizer updated. <a href="%s">View organizer</a>', 'tribe-events-calendar'), esc_url( get_permalink($post_ID) ) ),
				2 => __('Custom field updated.', 'tribe-events-calendar'),
				3 => __('Custom field deleted.', 'tribe-events-calendar'),
				4 => __('Organizer updated.', 'tribe-events-calendar'),
				/* translators: %s: date and time of the revision */
				5 => isset($_GET['revision']) ? sprintf( __('Organizer restored to revision from %s', 'tribe-events-calendar'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
				6 => sprintf( __('Organizer published. <a href="%s">View organizer</a>', 'tribe-events-calendar'), esc_url( get_permalink($post_ID) ) ),
				7 => __('Organizer saved.'),
				8 => sprintf( __('Organizer submitted. <a target="_blank" href="%s">Preview organizer</a>', 'tribe-events-calendar'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
				9 => sprintf( __('Organizer scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview organizer</a>', 'tribe-events-calendar'),
				// translators: Publish box date format, see http://php.net/date
				date_i18n( __( 'M j, Y @ G:i' , 'tribe-events-calendar'), strtotime( $post->post_date ) ), esc_url( get_permalink($post_ID) ) ),
				10 => sprintf( __('Organizer draft updated. <a target="_blank" href="%s">Preview organizer</a>', 'tribe-events-calendar'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
			);			

			return $messages;
		}

		public function admin_body_class( $classes ) {
			global $current_screen;			
			if ( isset($current_screen->post_type) &&
					($current_screen->post_type == self::POSTTYPE || $current_screen->id == 'settings_page_tribe-settings')
			) {
				$classes .= ' events-cal ';
			}
			return $classes;
		}

		public function addAdminScriptsAndStyles() {

			global $current_screen;

			// admin stylesheet - always loaded for a few persistent things (e.g. icon)
			wp_enqueue_style( self::POSTTYPE.'-admin', $this->pluginUrl . 'resources/events-admin.css' );

			// settings screen
			if (isset($current_screen->id) && $current_screen->id == 'settings_page_tribe-settings') {

				wp_enqueue_script( 'tribe-settings', $this->pluginUrl . 'resources/tribe-settings.js', array('jquery'), '', true );
				wp_enqueue_style( 'chosen-style', $this->pluginUrl . 'resources/chosen.css' );
				wp_enqueue_script( 'chosen-jquery', $this->pluginUrl . 'resources/chosen.jquery.min.js', array('jquery'), '0.9.5', false );
				wp_enqueue_script( self::POSTTYPE.'-admin', $this->pluginUrl . 'resources/events-admin.js', array('jquery-ui-datepicker'), '', true );
				wp_enqueue_script( 'thickbox' );
				wp_enqueue_style( 'thickbox' );

				// hook for other plugins
				do_action('tribe_settings_enqueue');
			}

			// post type editiong
			if ( isset($current_screen->post_type) ) {

				if ( $current_screen->post_type == self::POSTTYPE ) { // events editing

					wp_enqueue_style( 'chosen-style', $this->pluginUrl . 'resources/chosen.css' );
					wp_enqueue_script( 'chosen-jquery', $this->pluginUrl . 'resources/chosen.jquery.min.js', array('jquery'), '0.9.5', false );
					wp_enqueue_style( self::POSTTYPE.'-admin-ui', $this->pluginUrl . 'resources/events-admin-ui.css' );
					wp_enqueue_script( 'jquery-ui-datepicker', $this->pluginUrl . 'resources/ui.datepicker.min.js', array('jquery-ui-core'), '1.7.3', true );
					wp_enqueue_script( 'jquery-ui-dialog', $this->pluginUrl . 'resources/ui.dialog.min.js', array('jquery-ui-core'), '1.7.3', true );
					wp_enqueue_script( 'jquery-ecp-plugins', $this->pluginUrl . 'resources/jquery-ecp-plugins.js', array('jquery') );
					wp_enqueue_script( self::POSTTYPE.'-admin', $this->pluginUrl . 'resources/events-admin.js', array('jquery-ui-datepicker'), '', true );

					// calling our own localization because wp_localize_scripts doesn't support arrays or objects for values, which we need.
					add_action('admin_footer', array($this, 'printLocalizedAdmin') );

					// hook for other plugins
					do_action('tribe_events_enqueue');

				} elseif( $current_screen->post_type == self::VENUE_POST_TYPE) { // venue editing

					wp_enqueue_style( 'chosen-style', $this->pluginUrl . 'resources/chosen.css' );
					wp_enqueue_script( 'chosen-jquery', $this->pluginUrl . 'resources/chosen.jquery.min.js', array('jquery'), '0.9.5', false );
					wp_enqueue_script( 'jquery-ui-datepicker', $this->pluginUrl . 'resources/ui.datepicker.min.js', array('jquery-ui-core'), '1.7.3', true );
					wp_enqueue_script( 'jquery-ui-dialog', $this->pluginUrl . 'resources/ui.dialog.min.js', array('jquery-ui-core'), '1.7.3', true );
					wp_enqueue_script( 'jquery-ecp-plugins', $this->pluginUrl . 'resources/jquery-ecp-plugins.js', array('jquery') );
					wp_enqueue_style( self::POSTTYPE.'-admin-ui', $this->pluginUrl . 'resources/events-admin-ui.css' );
					wp_enqueue_script( self::VENUE_POST_TYPE.'-admin', $this->pluginUrl . 'resources/events-admin.js');
					wp_enqueue_style( self::VENUE_POST_TYPE.'-admin', $this->pluginUrl . 'resources/hide-visibility.css' );

					// hook for other plugins
					do_action('tribe_venues_enqueue');


				} elseif( $current_screen->post_type == self::ORGANIZER_POST_TYPE) { // organizer editing

					wp_enqueue_style( 'chosen-style', $this->pluginUrl . 'resources/chosen.css' );
					wp_enqueue_script( 'chosen-jquery', $this->pluginUrl . 'resources/chosen.jquery.min.js', array('jquery'), '0.9.5', false );
					wp_enqueue_script( 'jquery-ui-datepicker', $this->pluginUrl . 'resources/ui.datepicker.min.js', array('jquery-ui-core'), '1.7.3', true );
					wp_enqueue_script( 'jquery-ui-dialog', $this->pluginUrl . 'resources/ui.dialog.min.js', array('jquery-ui-core'), '1.7.3', true );
					wp_enqueue_script( 'jquery-ecp-plugins', $this->pluginUrl . 'resources/jquery-ecp-plugins.js', array('jquery') );
					wp_enqueue_style( self::POSTTYPE.'-admin-ui', $this->pluginUrl . 'resources/events-admin.css' );
					wp_enqueue_script( self::ORGANIZER_POST_TYPE.'-admin', $this->pluginUrl . 'resources/events-admin.js');
					wp_enqueue_style( self::ORGANIZER_POST_TYPE.'-admin', $this->pluginUrl . 'resources/hide-visibility.css' );

					// hook for other plugins
					do_action('tribe_organizers_enqueue');

				}
			}
		}

		public function localizeAdmin() {	
			$bits = array(
				'dayNames' => $this->daysOfWeek,
				'dayNamesShort' => $this->daysOfWeekShort,
				'dayNamesMin' => $this->daysOfWeekMin,
				'monthNames' => array_values( $this->monthNames() ),
				'monthNamesShort' => array_values( $this->monthNames( true ) ),
				'nextText' => __( 'Next', 'tribe-events-calendar' ),
				'prevText' => __( 'Prev', 'tribe-events-calendar' ),
				'currentText' => __( 'Today', 'tribe-events-calendar' ),
				'closeText' => __( 'Done', 'tribe-events-calendar' )
			);
			return $bits;
		}

		public function printLocalizedAdmin() {
			$object_name = 'TEC';
			$vars = $this->localizeAdmin();
	
			$data = "var $object_name = {\n";
			$eol = '';
			foreach ( $vars as $var => $val ) {
		
				if ( gettype($val) == 'array' || gettype($val) == 'object' ) {
					$val = json_encode($val);
				}
				else {
					$val = '"' . esc_js( $val ) . '"';
				} 
		
				$data .= "$eol\t$var: $val";
				$eol = ",\n";
			}
			$data .= "\n};\n";
	
			echo "<script type='text/javascript'>\n";
			echo "/* <![CDATA[ */\n";
			echo $data;
			echo "/* ]]> */\n";
			echo "</script>\n";
	
		}

		/**
		 * Get all options for the Events Calendar
		 *
		 * @return array of options
		 */
		public static function getOptions() {
			if ( !isset( self::$options ) ) {
				$options = get_option( TribeEvents::OPTIONNAME, array() );
				self::$options = apply_filters( 'tribe_get_options', $options );
			}
			return self::$options;
		}

		/**
		 * Get value for a specific option
		 *
		 * @param string $optionName name of option
		 * @param string $default default value
		 * @return mixed results of option query
		 */
		public function getOption($optionName, $default = '') {
			if( !$optionName )
				return null;

			if( !isset( self::$options ) ) 
				self::getOptions();
		
			if ( isset( self::$options[$optionName] ) ) {
				$option = self::$options[$optionName];
			} else {
				$option = $default;
			}
	
			return apply_filters( 'tribe_get_single_option', $option, $default );	
		}

		/**
		 * Saves the options for the plugin
		 *
		 * @param array $options formatted the same as from getOptions()
		 * @return void
		 */
		public function setOptions($options, $apply_filters=true) {
			if (!is_array($options)) {
				return;
			}
			if ( $apply_filters == true ) {
				$options = apply_filters( 'tribe-events-save-options', $options );
			}
			if ( update_option( TribeEvents::OPTIONNAME, $options ) ) {
				self::$options = apply_filters( 'tribe_get_options', $options );
				if ( isset( TribeEvents::$options['eventsSlug'] ) ) {
					if ( TribeEvents::$options['eventsSlug'] != '' ) {
						TribeEvents::flushRewriteRules();
					}
				}
				return true;
			} else {
				TribeEvents::$options = TribeEvents::getOptions();
				return false;
			}
		}

		public function setOption($name, $value) {
			$newOption = array();
			$newOption[$name] = $value;
			$options = self::getOptions();
			$this->setOptions( wp_parse_args( $newOption, $options ) );
		}

		// clean up trashed venues
		public function cleanupPostVenues($postId) {
			$this->removeDeletedPostTypeAssociation('_EventVenueID', $postId);
		}

		// clean up trashed organizers
		public function cleanupPostOrganizers($postId) {
			$this->removeDeletedPostTypeAssociation('_EventOrganizerID', $postId);
		}		

		// do clean up for trashed venues or organizers
		protected function removeDeletedPostTypeAssociation($key, $postId) {
			$the_query = new WP_Query(array('meta_key'=>$key, 'meta_value'=>$postId, 'post_type'=> TribeEvents::POSTTYPE ));

			while ( $the_query->have_posts() ): $the_query->the_post();
				delete_post_meta(get_the_ID(), $key);
			endwhile;

			wp_reset_postdata();
		}

		public function truncate($text, $excerpt_length = 44) {

			$text = apply_filters('the_content', $text);
			$text = str_replace(']]>', ']]&gt;', $text);
			$text = strip_tags($text);

			$words = explode(' ', $text, $excerpt_length + 1);
			if (count($words) > $excerpt_length) {
				array_pop($words);
				$text = implode(' ', $words);
				$text = rtrim($text);
				$text .= '&hellip;';
				}

			return $text;
		}

		public function loadTextDomain() {
			load_plugin_textdomain( 'tribe-events-calendar', false, $this->pluginDir . 'lang/');
			$this->constructDaysOfWeek();
			$this->initMonthNames();
		}

		public function loadStyle() {
	
			$eventsURL = trailingslashit( $this->pluginUrl ) . 'resources/';
			wp_enqueue_script('tribe-events-pjax', $eventsURL.'jquery.pjax.js', array('jquery') );			
			wp_enqueue_script('tribe-events-calendar-script', $eventsURL.'events.js', array('jquery', 'tribe-events-pjax') );
			// is there an events.css file in the theme?
			if ( $user_style = locate_template(array('events/events.css')) ) {
				$styleUrl = str_replace( get_theme_root(), get_theme_root_uri(), $user_style );
			}
			else {
				$styleUrl = $eventsURL.'events.css';
			}
			$styleUrl = apply_filters( 'tribe_events_stylesheet_url', $styleUrl );
	
			if ( $styleUrl )
				wp_enqueue_style('tribe-events-calendar-style', $styleUrl);
		}


		public function setDate($query) {
			if ( $query->get('eventDisplay') == 'month' ) {
				$this->date = $query->get('eventDate') . "-01";
			} else if ( $query->get('eventDate') ) {
				$this->date = $query->get('eventDate');
			} else if ( $query->get('eventDisplay') == 'month' ) {
				$date = date_i18n( TribeDateUtils::DBDATEFORMAT );
				$this->date = substr_replace( $date, '01', -2 );
			} else if (is_singular() && $query->get('eventDate') ) {
				$this->date = $query->get('eventDate');
			} else if (!is_singular()) { // don't set date for single event unless recurring
				$this->date = date(TribeDateUtils::DBDATETIMEFORMAT);
			}
		}

		public function setDisplay() {
			if (is_admin()) {
				$this->displaying = 'admin';
			} else {
				global $wp_query;
				$this->displaying = isset( $wp_query->query_vars['eventDisplay'] ) ? $wp_query->query_vars['eventDisplay'] : 'upcoming';
			}
		}

		public function setReccuringEventDates( $post ) {	
			if( function_exists('tribe_is_recurring_event') && 
				is_singular(self::POSTTYPE) && 
				tribe_is_recurring_event() && 
				!tribe_is_showing_all() && 
				!tribe_is_upcoming() && 
				!tribe_is_past() && 
				!tribe_is_month() && 
				!tribe_is_by_date() ) {

				$startTime = get_post_meta($post->ID, '_EventStartDate', true);
				$startTime = TribeDateUtils::timeOnly($startTime);
		
				$post->EventStartDate = TribeDateUtils::addTimeToDate($this->date, $startTime);
				$post->EventEndDate = date( TribeDateUtils::DBDATETIMEFORMAT, strtotime($post->EventStartDate) + get_post_meta($post->ID, '_EventDuration', true) );
			}
		}		

		/**
		 * Helper method to return an array of 1-12 for months
		 */
		public function months( ) {
			$months = array();
			foreach( range( 1, 12 ) as $month ) {
				$months[ $month ] = $month;
			}
			return $months;
		}

		protected function initMonthNames() {
			global $wp_locale;
			$this->monthsFull = array( 
				'January' => $wp_locale->get_month('01'), 
				'February' => $wp_locale->get_month('02'), 
				'March' => $wp_locale->get_month('03'), 
				'April' => $wp_locale->get_month('04'), 
				'May' => $wp_locale->get_month('05'), 
				'June' => $wp_locale->get_month('06'), 
				'July' => $wp_locale->get_month('07'), 
				'August' => $wp_locale->get_month('08'), 
				'September' => $wp_locale->get_month('09'), 
				'October' => $wp_locale->get_month('10'), 
				'November' => $wp_locale->get_month('11'), 
				'December' => $wp_locale->get_month('12') 
			);
			// yes, it's awkward. easier this way than changing logic elsewhere.
			$this->monthsShort = $months = array( 
				'Jan' => $wp_locale->get_month_abbrev( $wp_locale->get_month('01') ), 
				'Feb' => $wp_locale->get_month_abbrev( $wp_locale->get_month('02') ), 
				'Mar' => $wp_locale->get_month_abbrev( $wp_locale->get_month('03') ), 
				'Apr' => $wp_locale->get_month_abbrev( $wp_locale->get_month('04') ), 
				'May' => $wp_locale->get_month_abbrev( $wp_locale->get_month('05') ), 
				'Jun' => $wp_locale->get_month_abbrev( $wp_locale->get_month('06') ), 
				'Jul' => $wp_locale->get_month_abbrev( $wp_locale->get_month('07') ), 
				'Aug' => $wp_locale->get_month_abbrev( $wp_locale->get_month('08') ), 
				'Sep' => $wp_locale->get_month_abbrev( $wp_locale->get_month('09') ), 
				'Oct' => $wp_locale->get_month_abbrev( $wp_locale->get_month('10') ), 
				'Nov' => $wp_locale->get_month_abbrev( $wp_locale->get_month('11') ), 
				'Dec' => $wp_locale->get_month_abbrev( $wp_locale->get_month('12') )
			); 
		}

		/**
		 * Helper method to return an array of translated month names or short month names
		 * @return Array translated month names
		 */
		public function monthNames( $short = false ) {
			if ($short)
				return $this->monthsShort;
			return $this->monthsFull;
		}

		/**
		 * Flush rewrite rules to support custom links
		 *
		 * @link http://codex.wordpress.org/Custom_Queries#Permalinks_for_Custom_Archives
		 */
		public static function flushRewriteRules() {

			global $wp_rewrite;
			$wp_rewrite->flush_rules();
			// in case this was called too early, let's get it in the end.
			add_action('shutdown', array('TribeEvents', 'flushRewriteRules'));
		}		
		/**
		 * Adds the event specific query vars to WordPress
		 *
		 * @link http://codex.wordpress.org/Custom_Queries#Permalinks_for_Custom_Archives
		 * @return mixed array of query variables that this plugin understands
		 */
		public function eventQueryVars( $qvars ) {
			$qvars[] = 'eventDisplay';
			$qvars[] = 'eventDate';
			$qvars[] = 'ical';
			$qvars[] = 'start_date';
			$qvars[] = 'end_date';
			return $qvars;			
		}
		/**
		 * Adds Event specific rewrite rules.
		 *
		 *	events/				=>	/?post_type=tribe_events
		 *	events/month		=>	/?post_type=tribe_events&eventDisplay=month
		 *	events/upcoming		=>	/?post_type=tribe_events&eventDisplay=upcoming
		 *	events/past			=>	/?post_type=tribe_events&eventDisplay=past
		 *	events/2008-01/#15	=>	/?post_type=tribe_events&eventDisplay=bydate&eventDate=2008-01-01
		 * events/category/some-events-category => /?post_type=tribe_events&tribe_event_cat=some-events-category
		 *
		 * @return void
		 */
		public function filterRewriteRules( $wp_rewrite ) {
			if ( '' == get_option('permalink_structure') ) {
		
			}

			$this->rewriteSlug         = sanitize_title( $this->getOption( 'eventsSlug', 'events' ) );
			$this->rewriteSlugSingular = sanitize_title( $this->getOption( 'singleEventSlug', 'event' ) );
			$this->taxRewriteSlug      = $this->rewriteSlug . '/' . sanitize_title( __( 'category', 'tribe-events-calendar' ) );
			$this->tagRewriteSlug      = $this->rewriteSlug . '/' . sanitize_title( __( 'tag', 'tribe-events-calendar' ) );


			$base = trailingslashit( $this->rewriteSlug );
			$baseSingle = trailingslashit( $this->rewriteSlugSingular );
			$baseTax = trailingslashit( $this->taxRewriteSlug );
			$baseTax = "(.*)" . $baseTax;
			$baseTag = trailingslashit( $this->tagRewriteSlug );
			$baseTag = "(.*)" . $baseTag;
	
			$month = $this->monthSlug;
			$upcoming = $this->upcomingSlug;
			$past = $this->pastSlug;
			$newRules = array();
	
			// single event
			$newRules[$baseSingle . '([^/]+)/(\d{4}-\d{2}-\d{2})/?$'] = 'index.php?' . self::POSTTYPE . '=' . $wp_rewrite->preg_index(1) . "&eventDate=" . $wp_rewrite->preg_index(2);
			$newRules[$baseSingle . '([^/]+)/(\d{4}-\d{2}-\d{2})/ical/?$'] = 'index.php?ical=1&' . self::POSTTYPE . '=' . $wp_rewrite->preg_index(1) . "&eventDate=" . $wp_rewrite->preg_index(2);
			$newRules[$baseSingle . '([^/]+)/all/?$'] = 'index.php?' . self::POSTTYPE . '=' . $wp_rewrite->preg_index(1) . "&eventDisplay=all";			
	
			$newRules[$base . 'page/(\d+)'] = 'index.php?post_type=' . self::POSTTYPE . '&eventDisplay=upcoming&paged=' . $wp_rewrite->preg_index(1);
			$newRules[$base . 'ical'] = 'index.php?post_type=' . self::POSTTYPE . '&ical=1';
			$newRules[$base . '(feed|rdf|rss|rss2|atom)/?$'] = 'index.php?post_type=' . self::POSTTYPE . '&eventDisplay=upcoming&feed=' . $wp_rewrite->preg_index(1);
			$newRules[$base . $month] = 'index.php?post_type=' . self::POSTTYPE . '&eventDisplay=month';
			$newRules[$base . $upcoming . '/page/(\d+)'] = 'index.php?post_type=' . self::POSTTYPE . '&eventDisplay=upcoming&paged=' . $wp_rewrite->preg_index(1);
			$newRules[$base . $upcoming] = 'index.php?post_type=' . self::POSTTYPE . '&eventDisplay=upcoming';
			$newRules[$base . $past . '/page/(\d+)'] = 'index.php?post_type=' . self::POSTTYPE . '&eventDisplay=past&paged=' . $wp_rewrite->preg_index(1);
			$newRules[$base . $past] = 'index.php?post_type=' . self::POSTTYPE . '&eventDisplay=past';
			$newRules[$base . '(\d{4}-\d{2})$'] = 'index.php?post_type=' . self::POSTTYPE . '&eventDisplay=month' .'&eventDate=' . $wp_rewrite->preg_index(1);
			$newRules[$base . '(\d{4}-\d{2}-\d{2})$'] = 'index.php?post_type=' . self::POSTTYPE . '&eventDisplay=day' .'&eventDate=' . $wp_rewrite->preg_index(1);
			$newRules[$base . 'feed/?$'] = 'index.php?eventDisplay=upcoming&post_type=' . self::POSTTYPE . '&feed=rss2';
			$newRules[$base . '?$']						= 'index.php?post_type=' . self::POSTTYPE . '&eventDisplay=' . $this->getOption('viewOption','month');

			// single ical
			$newRules[$baseSingle . '([^/]+)/ical/?$' ] = 'index.php?post_type=' . self::POSTTYPE . '&name=' . $wp_rewrite->preg_index(1) . '&ical=1';

			// taxonomy rules.
			$newRules[$baseTax . '([^/]+)/page/(\d+)'] = 'index.php?post_type=' . self::POSTTYPE . '&eventDisplay=upcoming&tribe_events_cat=' . $wp_rewrite->preg_index(2) . '&paged=' . $wp_rewrite->preg_index(3);
			$newRules[$baseTax . '([^/]+)/' . $month] = 'index.php?tribe_events_cat=' . $wp_rewrite->preg_index(2) . '&post_type=' . self::POSTTYPE . '&eventDisplay=month';
			$newRules[$baseTax . '([^/]+)/' . $upcoming . '/page/(\d+)'] = 'index.php?tribe_events_cat=' . $wp_rewrite->preg_index(2) . '&post_type=' . self::POSTTYPE . '&eventDisplay=upcoming&paged=' . $wp_rewrite->preg_index(3);
			$newRules[$baseTax . '([^/]+)/' . $upcoming] = 'index.php?tribe_events_cat=' . $wp_rewrite->preg_index(2) . '&post_type=' . self::POSTTYPE . '&eventDisplay=upcoming';
			$newRules[$baseTax . '([^/]+)/' . $past . '/page/(\d+)'] = 'index.php?tribe_events_cat=' . $wp_rewrite->preg_index(2) . '&post_type=' . self::POSTTYPE . '&eventDisplay=past&paged=' . $wp_rewrite->preg_index(3);
			$newRules[$baseTax . '([^/]+)/' . $past] = 'index.php?tribe_events_cat=' . $wp_rewrite->preg_index(2) . '&post_type=' . self::POSTTYPE . '&eventDisplay=past';
			$newRules[$baseTax . '([^/]+)/(\d{4}-\d{2})$'] = 'index.php?tribe_events_cat=' . $wp_rewrite->preg_index(2) . '&post_type=' . self::POSTTYPE . '&eventDisplay=month' .'&eventDate=' . $wp_rewrite->preg_index(3);
			$newRules[$baseTax . '([^/]+)/feed/?$'] = 'index.php?tribe_events_cat=' . $wp_rewrite->preg_index(2) . '&eventDisplay=upcoming&post_type=' . self::POSTTYPE . '&feed=rss2';
			$newRules[$baseTax . '([^/]+)/?$'] = 'index.php?tribe_events_cat=' . $wp_rewrite->preg_index(2) . '&post_type=' . self::POSTTYPE . '&eventDisplay=' . $this->getOption('viewOption','month');
			$newRules[$baseTax . '([^/]+)/ical/?$'] = 'index.php?post_type=' . self::POSTTYPE . '&eventDisplay=upcoming&tribe_events_cat=' . $wp_rewrite->preg_index(2) . '&ical=1';
			$newRules[$baseTax . '([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$'] = 'index.php?post_type=' . self::POSTTYPE . '&tribe_events_cat=' . $wp_rewrite->preg_index(2) . '&feed=' . $wp_rewrite->preg_index(3);
			$newRules[$baseTax . '([^/]+)$'] = 'index.php?post_type=' . self::POSTTYPE . '&eventDisplay=upcoming&tribe_events_cat=' . $wp_rewrite->preg_index(2);

			$wp_rewrite->rules = $newRules + $wp_rewrite->rules; 
		}

		/**
		 * returns various internal events-related URLs
		 * @param string $type type of link. See switch statement for types.
		 * @param string $secondary for $type = month, pass a YYYY-MM string for a specific month's URL
		 */

		public function getLink	( $type = 'home', $secondary = false, $term = null ) {
			// if permalinks are off or user doesn't want them: ugly.
			if( '' == get_option('permalink_structure') ) {
				return esc_url($this->uglyLink($type, $secondary));
			}

       // account for semi-pretty permalinks
      if( strpos(get_option('permalink_structure'),"index.php") !== FALSE ) {
        $eventUrl = trailingslashit( home_url() . '/index.php/' . sanitize_title($this->getOption('eventsSlug', 'events')) );
       } else {
       	$eventUrl = trailingslashit( home_url() . '/' . sanitize_title($this->getOption('eventsSlug', 'events')) );
       }

			// if we're on an Event Cat, show the cat link, except for home and days.
			if ( $type !== 'home' && $type !== 'day' && is_tax( self::TAXONOMY ) ) {
				$eventUrl = trailingslashit( get_term_link( get_query_var('term'), self::TAXONOMY ) );
			} else if ( $term ) {
				$eventUrl = trailingslashit( get_term_link( $term, self::TAXONOMY ) );
			}

			switch( $type ) {
				case 'home':
					return trailingslashit( esc_url($eventUrl) );
				case 'month':
					if ( $secondary ) {
						return trailingslashit( esc_url($eventUrl . $secondary) );
					}
					return trailingslashit( esc_url($eventUrl . $this->monthSlug) );
				case 'upcoming':
					return trailingslashit( esc_url($eventUrl . $this->upcomingSlug) );
				case 'past':
					return trailingslashit( esc_url($eventUrl . $this->pastSlug) );
				case 'dropdown':
					return esc_url($eventUrl);
				case 'ical':
					if ( $secondary == 'single' )
						$eventUrl = trailingslashit(get_permalink());
					return trailingslashit( esc_url($eventUrl . 'ical') );
				case 'single':
					global $post;
					$p = $secondary ? $secondary : $post;
					remove_filter( 'post_type_link', array($this, 'addDateToRecurringEvents') );
					$link = trailingslashit(get_permalink($p));
					add_filter( 'post_type_link', array($this, 'addDateToRecurringEvents'), 10, 2 );
					return trailingslashit( esc_url($link) );
				case 'day':
					$date = strtotime($secondary);
					$secondary = date('Y-m-d', $date);
					return trailingslashit( esc_url($eventUrl . $secondary) );
				case 'all':
					remove_filter( 'post_type_link', array($this, 'addDateToRecurringEvents') );
					$eventUrl = trailingslashit(get_permalink());
					add_filter( 'post_type_link', array($this, 'addDateToRecurringEvents'), 10, 2 );
					return trailingslashit( esc_url($eventUrl . 'all') );
				default:
					return esc_url($eventUrl);
			}
		}

		protected function uglyLink( $type = 'home', $secondary = false ) {
	
			$eventUrl = add_query_arg('post_type', self::POSTTYPE, home_url() );
	
			// if we're on an Event Cat, show the cat link, except for home.
			if ( $type !== 'home' && is_tax( self::TAXONOMY ) ) {
				$eventUrl = add_query_arg( self::TAXONOMY, get_query_var('term'), $eventUrl );
			}
	
			switch( $type ) {
		
				case 'home':
					return $eventUrl;
				case 'month':
					$month = add_query_arg( array( 'eventDisplay' => 'month'), $eventUrl );
					if ( $secondary )
						$month = add_query_arg( array( 'eventDate' => $secondary ), $month );
					return $month;
				case 'day':
					$month = add_query_arg( array( 'eventDisplay' => 'day'), $eventUrl );
					if ( $secondary )
						$month = add_query_arg( array( 'eventDate' => $secondary ), $month );
					return $month;
				case 'upcoming':
					return add_query_arg( array( 'eventDisplay' => 'upcoming'), $eventUrl );
				case 'past':
					return add_query_arg( array( 'eventDisplay' => 'past'), $eventUrl );
				case 'dropdown':
					$dropdown = add_query_arg( array( 'eventDisplay' => 'month', 'eventDate' => ' '), $eventUrl );
					return rtrim($dropdown); // tricksy
				case 'ical':
					if ( $secondary == 'single' ) {
						return add_query_arg('ical', '1', get_permalink() );
					}
					return home_url() . '/?ical';
				case 'single':
					global $post;
					$p = $secondary ? $secondary : $post;
					$link = get_permalink($p);
					return $link;
				case 'all':
					remove_filter( 'post_type_link', array($this, 'addDateToRecurringEvents') );					
					$eventUrl = add_query_arg('eventDisplay', 'all', get_permalink() );
					add_filter( 'post_type_link', array( $this, 'addDateToRecurringEvents' ), 10, 2 );
					return $eventUrl;
				default:
					return $eventUrl;
			}
		}

		/**
		 * Returns a link to google maps for the given event
		 *
		 * @param string $postId 
		 * @return string a fully qualified link to http://maps.google.com/ for this event
		 */
		public function get_google_maps_args() {

			$locationMetaSuffixes = array( 'address', 'city', 'state', 'province', 'zip', 'country' );
			$toUrlEncode = "";
			$languageCode = substr( get_bloginfo( 'language' ), 0, 2 );
			foreach( $locationMetaSuffixes as $val ) {
				$metaVal = call_user_func('tribe_get_'.$val);
				if ( $metaVal ) 
					$toUrlEncode .= $metaVal . " ";
			}
			if ( $toUrlEncode ) 
				return 'f=q&amp;source=embed&amp;hl=' . $languageCode . '&amp;geocode=&amp;q='. urlencode( trim( $toUrlEncode ) );
			return "";
	
		}

		/**
		 * Returns a link to google maps for the given event
		 *
		 * @param string $postId 
		 * @return string a fully qualified link to http://maps.google.com/ for this event
		 */
		public function googleMapLink( $postId = null ) {
			if ( $postId === null || !is_numeric( $postId ) ) {
				global $post;
				$postId = $post->ID;
			}
	
			$locationMetaSuffixes = array( 'address', 'city', 'state', 'province', 'zip', 'country' );
			$toUrlEncode = "";
			foreach( $locationMetaSuffixes as $val ) {
				$metaVal = call_user_func('tribe_get_'.$val, $postId);
				if ( $metaVal ) 
					$toUrlEncode .= $metaVal . " ";
			}
			if ( $toUrlEncode ) 
				return "http://maps.google.com/maps?f=q&amp;source=s_q&amp;hl=en&amp;geocode=&amp;q=" . urlencode( trim( $toUrlEncode ) );
			return "";
		}
		
		/**
		 *  Returns the full address of an event along with HTML markup.  It 
		 *  loads the full-address template to generate the HTML
		 */  
		public function fullAddress( $postId=null, $includeVenueName=false ) {
			ob_start();
			load_template( TribeEventsTemplates::getTemplateHierarchy( 'full-address' ), false );
			$address = ob_get_contents();
			ob_end_clean();
			return $address;
		}

		/**
		 *  Returns a string version of the full address of an event
		 */
		public function fullAddressString( $postId=null ) {
			$address = '';
			if( tribe_get_address( $postId ) ) { 
				$address .= tribe_get_address( $postId );
			} 

			if( tribe_get_city( $postId ) ) {
				if($address != '') $address .= ", ";
				$address .= tribe_get_city( $postId );
			}

			if( tribe_get_region( $postId ) ) {
				if($address != '') $address .= ", ";
				$address .= tribe_get_region( $postId );
			}

			if( tribe_get_zip( $postId ) ) { 
				if($address != '') $address .= ", ";
				$address .= tribe_get_zip( $postId );
			} 

			if( tribe_get_country( $postId ) ) {
				if($address != '') $address .= ", ";
				$address .= tribe_get_country( $postId );
			}

			return $address;
		}

		/**
		 * This plugin does not have any deactivation functionality. Any events, categories, options and metadata are
		 * left behind.
		 * 
		 * @return void
		 */
		public function on_deactivate( ) { 
			TribeEvents::flushRewriteRules();
		}

		/**
		 * Converts a set of inputs to YYYY-MM-DD HH:MM:SS format for MySQL
		 */
		public function dateToTimeStamp( $date, $hour, $minute, $meridian ) {
			if ( preg_match( '/(PM|pm)/', $meridian ) && $hour < 12 ) $hour += "12";
			if ( preg_match( '/(AM|am)/', $meridian ) && $hour == 12 ) $hour = "00";
			$date = $this->dateHelper($date);
			return "$date $hour:$minute:00";
		}
		public function getTimeFormat( $dateFormat = TribeDateUtils::DATEONLYFORMAT ) {
			return $dateFormat . ' ' . get_option( 'time_format', TribeDateUtils::TIMEFORMAT );
		}

/*
		 * Ensures date follows proper YYYY-MM-DD format
		 * converts /, - and space chars to -
		 */
		protected function dateHelper( $date ) {

			if($date == '')
				return date(TribeDateUtils::DBDATEFORMAT);

			$date = str_replace( array('-','/',' ',':',chr(150),chr(151),chr(45)), '-', $date );
			// ensure no extra bits are added
			list($year, $month, $day) = explode('-', $date);
	
			if ( ! checkdate($month, $day, $year) )
				$date = date(TribeDateUtils::DBDATEFORMAT); // today's date if error
			else
				$date = $year . '-' . $month . '-' . $day;

			return $date;
		}

		/**
		 * Adds an alias for get_post_meta so we can do extra stuff to the plugin values.
		 * If you need the raw unfiltered data, use get_post_meta directly. 
		 * This is mainly for templates.
		 */
		public function getEventMeta( $id, $meta, $single = true ){
			$use_def_if_empty = (class_exists( 'TribeEventsPro' )) ? tribe_get_option('defaultValueReplace') : false;
			if($use_def_if_empty){
				$cleaned_tag = str_replace('_Event','',$meta);
				$default = tribe_get_option('eventsDefault'.$cleaned_tag);
				$default = apply_filters('filter_eventsDefault'.$cleaned_tag,$default);
				return (get_post_meta( $id, $meta, $single ) !== false) ? get_post_meta( $id, $meta, $single ) : $default;
			}else{
				return get_post_meta( $id, $meta, $single );
			}

		}
		/**
		 * Adds / removes the event details as meta tags to the post.
		 *
		 * @param string $postId 
		 * @return void
		 */
		public function addEventMeta( $postId, $post ) {
			// only continue if it's an event post
			if ( $post->post_type != self::POSTTYPE || defined('DOING_AJAX') ) {
				return;
			}
			// don't do anything on autosave or auto-draft either or massupdates
			if ( wp_is_post_autosave( $postId ) || $post->post_status == 'auto-draft' || isset($_GET['bulk_edit']) || (isset($_REQUEST['action']) && $_REQUEST['action'] == 'inline-save') ) {
				return;
			}
	
			// remove these actions even if nonce is not set
			// note: we're removing these because these actions are actually for PRO,
			// these functions are used when editing an existing venue or organizer
			remove_action( 'save_post', array( $this, 'save_venue_data' ), 16, 2 );
			remove_action( 'save_post', array( $this, 'save_organizer_data' ), 16, 2 );			
			
			if( !isset($_POST['ecp_nonce']) )
				return;
				
			if ( !wp_verify_nonce( $_POST['ecp_nonce'], TribeEvents::POSTTYPE ) )
				return;
	
			if ( !current_user_can( 'edit_tribe_events' ) )
				return;

			$_POST['Organizer'] = isset($_POST['organizer']) ? stripslashes_deep($_POST['organizer']) : null;
			$_POST['Venue'] = isset($_POST['venue']) ? stripslashes_deep($_POST['venue']) : null;


			/**
			 * When using pro and we have a VenueID/OrganizerID, we just save the ID, because we're not
			 * editing the venue/organizer from within the event.
			 */
			if( isset($_POST['Venue']['VenueID']) && !empty($_POST['Venue']['VenueID']) && class_exists('TribeEventsPro') )
				$_POST['Venue'] = array('VenueID' => intval($_POST['Venue']['VenueID']));

			if( isset($_POST['Organizer']['OrganizerID']) && !empty($_POST['Organizer']['OrganizerID']) && class_exists('TribeEventsPro') )
				$_POST['Organizer'] = array('OrganizerID' => intval($_POST['Organizer']['OrganizerID']));


			TribeEventsAPI::saveEventMeta($postId, $_POST, $post);
		}

		/**
		 * Adds the '_<posttype>Origin' meta field for a newly inserted events-calendar post.
		 *
		 * @since 2.1
		 * @author paulhughes
		 * @param int $postId, the post ID
		 * @param stdClass $post, the post object
		 * @return void
		 */
		public function addPostOrigin( $postId, $post ) {
			// Only continue of the post being added is an event, venue, or organizer.
			if ( isset($postId) && isset($post->post_type) ) {
				if ( $post->post_type == self::POSTTYPE ) {
					$post_type = '_Event';
				} elseif ( $post->post_type == self::VENUE_POST_TYPE ) {
					$post_type = '_Venue';
				} elseif ( $post->post_type == self::ORGANIZER_POST_TYPE ) {
					$post_type = '_Organizer';
				} else {
					return;
				}

				//only set origin once
				$origin = get_post_meta($postId , $post_type . 'Origin', true);
				if( !$origin )
					add_post_meta( $postId, $post_type . 'Origin', apply_filters( 'tribe-post-origin', 'events-calendar', $postId, $post ) );
			}
		}

		public function showAuditingData(){

			$events_audit_trail_template = $this->pluginPath . 'admin-views/events-audit-trail.php';
			$events_audit_trail_template = apply_filters('tribe_events_audit_trail_template', $events_audit_trail_template);
			include( $events_audit_trail_template );
		
		}

		/**
		 * Adds to the '_<posttype>AuditTrail' meta field for an events-calendar post.
		 *
		 * @since 2.1
		 * @author paulhughes
		 * @param int $postId, the post ID
		 * @param stdClass $post, the post object
		 * @return void
		 */
		public function addToPostAuditTrail( $postId, $post ) {
			// Only continue of the post being added is an event, venue, or organizer.
			if ( isset($postId) && isset($post->post_type) ) {
				if ( $post->post_type == self::POSTTYPE ) {
					$post_type = '_Event';
				} elseif ( $post->post_type == self::VENUE_POST_TYPE ) {
					$post_type = '_Venue';
				} elseif ( $post->post_type == self::ORGANIZER_POST_TYPE ) {
					$post_type = '_Organizer';
				} else {
					return;
				}
				$post_audit_trail = get_post_meta( $postId, $post_type . 'AuditTrail', true );
				if ( !isset( $post_audit_trail ) || !$post_audit_trail || !is_array($post_audit_trail) ) {
					$post_audit_trail = array();
				}
				$post_audit_trail[] = array( apply_filters( 'tribe-post-origin', 'events-calendar' ), time() );
				update_post_meta( $postId, $post_type . 'AuditTrail', $post_audit_trail );
			}
		}

		/**
		 * Publishes associated venue/organizer when an event is published
		 *
		 * @since 2.0.6
		 * @author nciske
		 * @param int $postId, the post ID
		 * @param stdClass $post, the post object
		 * @return void
		 */
		public function publishAssociatedTypes( $postID, $post ) {
			
			remove_action( 'save_post', array( $this, 'addEventMeta' ), 15, 2 );
			remove_action( 'save_post', array( $this, 'save_venue_data' ), 16, 2 );
			remove_action( 'save_post', array( $this, 'save_organizer_data' ), 16, 2 );
			remove_action( 'save_post', array( $this, 'addToPostAuditTrail' ), 10, 2 );

			remove_action( 'save_post', array( $this, 'publishAssociatedTypes'), 25, 2 );
			
			// Only continue if the post being published is an event
			if ( wp_is_post_autosave( $postID ) || $post->post_status == 'auto-draft' ||
						isset($_GET['bulk_edit']) || (isset($_REQUEST['action']) && $_REQUEST['action'] == 'inline-save') || 
						($post->post_type != self::POSTTYPE && $postID)) {
				return;
			}
				
				//echo '$postID='.$postID;
				
				global $wpdb;
				
				if( isset( $post->post_status ) && $post->post_status == 'publish' ){
				
					//get venue and organizer and publish them

					$pm = get_post_custom($post->ID);
					
					if( isset($pm['_EventVenueID']) && $pm['_EventVenueID'] ){
						
						if( is_array($pm['_EventVenueID']) ){
							$venue_id = current($pm['_EventVenueID']);
						}else{
							$venue_id = $pm['_EventVenueID'];
						}
						
						
						$venue_post = array(
							'ID' => $venue_id, 
							'post_status' => 'publish',
						);
						
						//wp_update_post( $venue_post );
						$sql = "UPDATE $wpdb->posts SET post_status = 'publish' WHERE ID = '".intval($venue_id)."' AND post_type = '".TribeEvents::VENUE_POST_TYPE."' AND post_status != 'publish'";
						$wpdb->query($sql);
						
					}
	
					if( isset($pm['_EventOrganizerID']) && $pm['_EventOrganizerID'] ){
						
						if( is_array($pm['_EventOrganizerID']) ){
							$org_id = current($pm['_EventOrganizerID']);
						}else{
							$org_id = $pm['_EventOrganizerID'];
						}
						

						$org_post = array(
							'ID' => $org_id, 
							'post_status' => 'publish',
						);

						//wp_update_post( $org_post );
						$sql = "UPDATE $wpdb->posts SET post_status = 'publish' WHERE ID = '".intval($org_id)."' AND post_type = '".TribeEvents::ORGANIZER_POST_TYPE."' AND post_status != 'publish'";
						$wpdb->query($sql);
					}
				}
				
		}

		//** If you are saving a new venue separate from an event
		public function save_venue_data( $postID = null, $post=null ) {
			global $_POST;

			// don't do anything on autosave or auto-draft either or massupdates
			// Or inline saves, or data being posted without a venue Or
			// finally, called from the save_post action, but on save_posts that
			// are not venue posts
			if ( wp_is_post_autosave( $postID ) || $post->post_status == 'auto-draft' ||
						isset($_GET['bulk_edit']) || (isset($_REQUEST['action']) && $_REQUEST['action'] == 'inline-save') ||
						(isset($_POST['venue']) && !$_POST['venue']) ||
						($post->post_type != self::VENUE_POST_TYPE && $postID)) {
				return;
			}
			
			if ( !current_user_can( 'edit_tribe_venues' ) )
				return;					

			//There is a possibility to get stuck in an infinite loop. 
			//That would be bad.
			remove_action( 'save_post', array( $this, 'save_venue_data' ), 16, 2 );

			if( !isset($_POST['post_title']) || !$_POST['post_title'] ) { $_POST['post_title'] = "Unnamed Venue"; }
			$_POST['venue']['Venue'] = $_POST['post_title'];
			$data = stripslashes_deep($_POST['venue']);
			$venue_id = TribeEventsAPI::updateVenue($postID, $data);

			return $venue_id;
		}
		/**
		 *
		 * @param $p
		 * @param $post_status (deprecated)
		 * @param $args
		 *
		 * @return WP_Query->posts || false
		 */
		function get_venue_info( $p = null, $deprecated = null, $args = array() ){
			$defaults = array(
				'post_type' => self::VENUE_POST_TYPE, 
				'nopaging' => 1, 
				'post_status' => 'publish',
				'ignore_sticky_posts ' => 1,
				'orderby'=>'title', 
				'order'=>'ASC',
				'p' => $p
			);

			// allow deprecated param to pass through by default
			// NOTE: setting post_status in $args will override $post_status
			if ( $deprecated != null ) {
				_deprecated_argument( __FUNCTION__, 'The Event Calendar v2.0.9', 'To use the latest code, please supply post_status in the argument array params.' );
				$defaults['post_status'] = $deprecated;
			}


			$args = wp_parse_args( $args, $defaults );
			$r = new WP_Query( $args );
			if ($r->have_posts()) :
				return $r->posts;
			endif;
			return false;
		}

		//** If you are saving a new organizer along with the event, we will do this:
		public function save_organizer_data( $postID = null, $post=null ) {
			global $_POST;
			
			// don't do anything on autosave or auto-draft either or massupdates
			// Or inline saves, or data being posted without a organizer Or
			// finally, called from the save_post action, but on save_posts that
			// are not organizer posts
			
			if( !isset($_POST['organizer']) ) $_POST['organizer'] = null;
			
			if ( wp_is_post_autosave( $postID ) || $post->post_status == 'auto-draft' ||
						isset($_GET['bulk_edit']) || (isset($_REQUEST['action']) && $_REQUEST['action'] == 'inline-save') ||
						!$_POST['organizer'] ||
						($post->post_type != self::ORGANIZER_POST_TYPE && $postID)) {
				return;
			}
			
			if ( !current_user_can( 'edit_tribe_organizers' ) )
				return;										

			//There is a possibility to get stuck in an infinite loop. 
			//That would be bad.
			remove_action( 'save_post', array( $this, 'save_organizer_data' ), 16, 2 );

			$data = stripslashes_deep($_POST['organizer']);

			$organizer_id = TribeEventsAPI::updateOrganizer($postID, $data);

			/**
			 * Put our hook back
			 * @link http://codex.wordpress.org/Plugin_API/Action_Reference/save_post#Avoiding_infinite_loops
			 */
			add_action( 'save_post', array( $this, 'save_organizer_data' ), 16, 2 );

			return $organizer_id;
		}

		// abstracted for EventBrite
		public function add_new_organizer($data, $post=null) {
			if($data['OrganizerID'])
				return $data['OrganizerID'];

			if ( $post->post_type == self::ORGANIZER_POST_TYPE && $post->ID) {
				$data['OrganizerID'] = $post->ID;
			}

			//google map checkboxes
			$postdata = array(
				'post_title' => $data['Organizer'],
				'post_type' => self::ORGANIZER_POST_TYPE,
				'post_status' => 'publish',
				'ID' => $data['OrganizerID']
			);

			if( isset($data['OrganizerID']) && $data['OrganizerID'] != "0" ) {
				$organizer_id = $data['OrganizerID'];
				wp_update_post( array('post_title' => $data['Organizer'], 'ID'=>$data['OrganizerID'] ));
			} else {
				$organizer_id = wp_insert_post($postdata, true);
			}

			if( !is_wp_error($organizer_id) ) {
				foreach ($data as $key => $var) {
					update_post_meta($organizer_id, '_Organizer'.$key, $var);
				}

				return $organizer_id;
			}
		}

		/**
		 *
		 * @param $p
		 * @param $post_status (deprecated)
		 * @param $args
		 *
		 * @return WP_Query->posts || false
		 */
		function get_organizer_info( $p = null, $deprecated = null, $args = array() ){
			$defaults = array(
				'post_type' => self::ORGANIZER_POST_TYPE, 
				'nopaging' => 1, 
				'post_status' => 'publish',
				'ignore_sticky_posts ' => 1,
				'orderby'=>'title', 
				'order'=>'ASC',
				'p' => $p
			);

			// allow deprecated param to pass through by default
			// NOTE: setting post_status in $args will override $post_status
			if ( $deprecated != null ) {
				_deprecated_argument( __FUNCTION__, 'The Event Calendar v2.0.9', 'To use the latest code, please supply post_status in the argument array params.' );
				$defaults['post_status'] = $deprecated;
			}


			$args = wp_parse_args( $args, $defaults );
			$r = new WP_Query( $args );
			if ($r->have_posts()) :
				return $r->posts;
			endif;
		}

		/**
		 * Adds a style chooser to the write post page
		 *
		 * @return void
		 */
		public function EventsChooserBox($event = null) {
			
			$saved = false;
			
			if(!$event){
				global $post;
				
				if( isset($_GET['post']) && $_GET['post'] )
					$saved = true;
			}else{
				$post = $event;
				
				//echo $post->ID;
				
				if($post->ID){
					$saved = true;
				}else{
					$saved = false;
				}
			}
			
			$options = '';
			$style = '';
			
			if(isset($post->ID)){
				$postId = $post->ID;
			}else{
				$postId = 0;
			}
			
				foreach ( $this->metaTags as $tag ) {
					if ( $postId && $saved ) { //if there is a post AND the post has been saved at least once.
					
						// Sort the meta to make sure it is correct for recurring events
						
						$meta = get_post_meta($postId,$tag);
						sort($meta);
						if (isset($meta[0])) { $$tag = $meta[0]; }
					} else {
						$cleaned_tag = str_replace('_Event','',$tag);
						
						//allow posted data to override default data
						if( isset($_POST['Event'.$cleaned_tag]) ){
							$$tag = stripslashes_deep($_POST['Event'.$cleaned_tag]);
						}else{
							$$tag = (class_exists('TribeEventsPro') && $this->defaultValueReplaceEnabled() ) ? tribe_get_option('eventsDefault'.$cleaned_tag) : "";
						}
					}
				}
			
			if( isset($_EventOrganizerID) && $_EventOrganizerID ) {
				foreach($this->organizerTags as $tag) {
					$$tag = get_post_meta($_EventOrganizerID, $tag, true );
				}
			}else{
				foreach($this->organizerTags as $tag) {
					$cleaned_tag = str_replace('_Organizer','',$tag);
					if( isset($_POST['organizer'][$cleaned_tag]) )
						$$tag = stripslashes_deep($_POST['organizer'][$cleaned_tag]);
				}
			}

			if(isset($_EventVenueID) && $_EventVenueID){
			
				foreach($this->venueTags as $tag) {
					$$tag = get_post_meta($_EventVenueID, $tag, true );
				}

			}else{
			
				$defaults = $this->venueTags;
				$defaults[] = '_VenueState';
				$defaults[] = '_VenueProvince';

				foreach ( $defaults as $tag ) {

					$cleaned_tag = str_replace('_Venue','',$tag);
					//echo $tag.' | '.$cleaned_tag.'<BR>';

					$var_name = '_Venue'.$cleaned_tag;

					if ($cleaned_tag != 'Cost') {

						$$var_name = (class_exists('TribeEventsPro') && $this->defaultValueReplaceEnabled() ) ? tribe_get_option('eventsDefault'.$cleaned_tag) : "";
					}

					if( isset($_POST['venue'][$cleaned_tag]) )
						$$var_name = stripslashes_deep($_POST['venue'][$cleaned_tag]);

				}

				if ( isset($_VenueState) && !empty($_VenueState) ) {
					$_VenueStateProvince = $_VenueState;
				} elseif ( isset($_VenueProvince) ) {
					$_VenueStateProvince = $_VenueProvince;
				} else {
					$_VenueStateProvince = null;
				}
				
				if( isset($_POST['venue']['Country']) ){
					if( $_POST['venue']['Country'] == 'United States' ){
						$_VenueStateProvince = stripslashes_deep($_POST['venue']['State']);
					}else{
						$_VenueStateProvince = stripslashes_deep($_POST['venue']['Province']);
					}
				}

			}

			$_EventStartDate = (isset($_EventStartDate)) ? $_EventStartDate : null;
			$_EventEndDate = (isset($_EventEndDate)) ? $_EventEndDate : null;
			$_EventAllDay = isset($_EventAllDay) ? $_EventAllDay : false;
			$isEventAllDay = ( $_EventAllDay == 'yes' || ! TribeDateUtils::dateOnly( $_EventStartDate ) ) ? 'checked="checked"' : ''; // default is all day for new posts
			$startMonthOptions 		= TribeEventsViewHelpers::getMonthOptions( $_EventStartDate );
			$endMonthOptions 			= TribeEventsViewHelpers::getMonthOptions( $_EventEndDate );
			$startYearOptions 		= TribeEventsViewHelpers::getYearOptions( $_EventStartDate );
			$endYearOptions			= TribeEventsViewHelpers::getYearOptions( $_EventEndDate );
			$startMinuteOptions 		= TribeEventsViewHelpers::getMinuteOptions( $_EventStartDate, true );
			$endMinuteOptions		= TribeEventsViewHelpers::getMinuteOptions( $_EventEndDate );
			$startHourOptions				= TribeEventsViewHelpers::getHourOptions( $_EventAllDay == 'yes' ? null : $_EventStartDate, true );
			$endHourOptions			= TribeEventsViewHelpers::getHourOptions( $_EventAllDay == 'yes' ? null : $_EventEndDate );
			$startMeridianOptions = TribeEventsViewHelpers::getMeridianOptions( $_EventStartDate, true );
			$endMeridianOptions		= TribeEventsViewHelpers::getMeridianOptions( $_EventEndDate );
	
			if( $_EventStartDate )
				$start = TribeDateUtils::dateOnly($_EventStartDate);

			$EventStartDate = ( isset($start) && $start ) ? $start : date('Y-m-d');
	
			if ( !empty($_REQUEST['eventDate']) )
				$EventStartDate = $_REQUEST['eventDate'];
	
			if( $_EventEndDate )
				$end = TribeDateUtils::dateOnly($_EventEndDate);

			$EventEndDate = ( isset($end) && $end ) ? $end : date('Y-m-d');
			$recStart = isset($_REQUEST['event_start']) ? $_REQUEST['event_start'] : null;
			$recPost = isset($_REQUEST['post']) ? $_REQUEST['post'] : null;
	
			if ( !empty($_REQUEST['eventDate']) ) {
				$duration = get_post_meta( $postId, '_EventDuration', true );
				$EventEndDate = TribeDateUtils::dateOnly( strtotime($EventStartDate) + $duration, true );
			}

			$events_meta_box_template = $this->pluginPath . 'admin-views/events-meta-box.php';
			$events_meta_box_template = apply_filters('tribe_events_meta_box_template', $events_meta_box_template);
			include( $events_meta_box_template );
		}

		public function displayEventVenueInput($postId) {
			$VenueID = get_post_meta( $postId, '_EventVenueID', true);
			?><input type='hidden' name='venue[VenueID]' value='<?php echo esc_attr($VenueID) ?>'/><?php
		}

		public function displayEventOrganizerInput($postId) {
			$OrganizerID = get_post_meta( $postId, '_EventOrganizerID', true);
			?><input type='hidden' name='organizer[OrganizerID]' value='<?php echo esc_attr($OrganizerID) ?>'/><?php
		}

		/**
		 * Adds a style chooser to the write post page
		 *
		 * @return void
		 */
		public function VenueMetaBox() {
			global $post;
			$options = '';
			$style = '';
			$postId = $post->ID;

			if($post->post_type == self::VENUE_POST_TYPE){
			
				if( (is_admin() && isset($_GET['post']) && $_GET['post']) || (!is_admin() && isset($postId) ) )
					$saved = true;
			
				foreach ( $this->venueTags as $tag ) {
					if ( $postId && isset( $saved ) && $saved ) { //if there is a post AND the post has been saved at least once.
						$$tag = esc_html(get_post_meta( $postId, $tag, true ));
					} else {
						$cleaned_tag = str_replace('_Venue','',$tag);
						$$tag = tribe_get_option('eventsDefault'.$cleaned_tag);
					}
				}
			}
			?>
				<style type="text/css">
						#EventInfo {border:none;}
				</style>
				<div id='eventDetails' class="inside eventForm">	
					<table cellspacing="0" cellpadding="0" id="EventInfo" class="VenueInfo">
					<?php
					$venue_meta_box_template = $this->pluginPath . 'admin-views/venue-meta-box.php';
					$venue_meta_box_template = apply_filters('tribe_events_venue_meta_box_template', $venue_meta_box_template);
					include( $venue_meta_box_template );
					?>
					</table>
				</div>
			<?php
		}
		
		/**
		 * Adds a style chooser to the write post page
		 *
		 * @return void
		 */
		public function OrganizerMetaBox() {
			global $post;
			$options = '';
			$style = '';
			$postId = $post->ID;
			$saved = false;
			
			if($post->post_type == self::ORGANIZER_POST_TYPE){

				if( (is_admin() && isset($_GET['post']) && $_GET['post']) || (!is_admin() && isset($postId) ))
					$saved = true;
			
				foreach ( $this->organizerTags as $tag ) {
					if ( $postId && $saved ) { //if there is a post AND the post has been saved at least once.
						$$tag = get_post_meta( $postId, $tag, true );
					}
				}
			}
			?>
				<style type="text/css">
						#EventInfo {border:none;}
				</style>
				<div id='eventDetails' class="inside eventForm">	
					<table cellspacing="0" cellpadding="0" id="EventInfo" class="OrganizerInfo">
					<?php
					$organizer_meta_box_template = $this->pluginPath . 'admin-views/organizer-meta-box.php';
					$organizer_meta_box_template = apply_filters('tribe_events_organizer_meta_box_template', $organizer_meta_box_template);
					include( $organizer_meta_box_template );
					?>
					</table>
				</div>
			<?php
		}

		/**
		 * Handle ajax requests from admin form
		 */
		public function ajax_form_validate() {
			if ($_REQUEST['name'] && $_REQUEST['nonce'] && wp_verify_nonce($_REQUEST['nonce'], 'tribe-validation-nonce')) {
				if($_REQUEST['type'] == 'venue'){
					echo $this->verify_unique_name($_REQUEST['name'],'venue');
					exit;
				} elseif ($_REQUEST['type'] == 'organizer'){
					echo $this->verify_unique_name($_REQUEST['name'],'organizer');
					exit;
				}
			}
		}

		/**
		 * Allow programmatic override of defaultValueReplace setting
		 *
		 * @return boolean
		 */
		 public function defaultValueReplaceEnabled(){
		
			if( !is_admin() )
				return false;
				
			return tribe_get_option('defaultValueReplace');
		
		}

		/**
		 * Verify that a venue or organizer is unique
		 *
		 * @param string $name - name of venue or organizer
		 * @param string $type - post type (venue or organizer) 
		 * @return boolean
		 */
		public function verify_unique_name($name, $type){
			global $wpdb;
			$name = stripslashes($name);
			if ('' == $name) { return 1; }
			if ($type == 'venue') {
				$post_type = self::VENUE_POST_TYPE;
			} elseif($type == 'organizer') {
				$post_type = self::ORGANIZER_POST_TYPE;
			}
			$results = $wpdb->get_var($wpdb->prepare("SELECT id FROM {$wpdb->posts} WHERE post_type = %s && post_title = %s && post_status = 'publish'",$post_type,$name));
			return ($results) ? 0 : 1;
		}

		/**
		 * Given a date (YYYY-MM-DD), returns the first of the next month
		 *
		 * @param date
		 * @return date
		 */
		public function nextMonth( $date ) {
			$dateParts = split( '-', $date );
			if ( $dateParts[1] == 12 ) {
				$dateParts[0]++;
				$dateParts[1] = "01";
				$dateParts[2] = "01";
			} else {
				$dateParts[1]++;
				$dateParts[2] = "01";
			}
			if ( $dateParts[1] < 10 && strlen( $dateParts[1] ) == 1 ) {
				$dateParts[1] = "0" . $dateParts[1];
			}
			$return =	$dateParts[0] . '-' . $dateParts[1];
			return $return;
		}
		/**
		 * Given a date (YYYY-MM-DD), return the first of the previous month
		 *
		 * @param date
		 * @return date
		 */
		public function previousMonth( $date ) {
			$dateParts = split( '-', $date );

			if ( $dateParts[1] == 1 ) {
				$dateParts[0]--;
				$dateParts[1] = "12";
				$dateParts[2] = "01";
			} else {
				$dateParts[1]--;
				$dateParts[2] = "01";
			}
			if ( $dateParts[1] < 10 ) {
				$dateParts[1] = "0" . $dateParts[1];
			}
			$return =	$dateParts[0] . '-' . $dateParts[1];

			return $return;
		}

		/**
		 * Callback for adding the Meta box to the admin page
		 * @return void
		 */
		public function addEventBox( ) {
			add_meta_box( 'Event Details', $this->pluginName, array( $this, 'EventsChooserBox' ), self::POSTTYPE, 'normal', 'high' );
			add_meta_box( 'Event Options', __('Event Options', 'tribe-events-calendar'), array( $this, 'eventMetaBox' ), self::POSTTYPE, 'side', 'default' );
	
			add_meta_box( 'Venue Details', __('Venue Information', 'tribe-events-calendar'), array( $this, 'VenueMetaBox' ), self::VENUE_POST_TYPE, 'normal', 'high' );
			add_meta_box( 'Organizer Details', __('Organizer Information', 'tribe-events-calendar'), array( $this, 'OrganizerMetaBox' ), self::ORGANIZER_POST_TYPE, 'normal', 'high' );
		}
		public function eventMetaBox() {
			include( $this->pluginPath . 'admin-views/event-sidebar-options.php' );
		}

		public function getDateString( $date ) {
			$monthNames = $this->monthNames();
			$dateParts = split( '-', $date );
			$timestamp = mktime( 0, 0, 0, $dateParts[1], 1, $dateParts[0] );
			return $monthNames[date( "F", $timestamp )] . " " . $dateParts[0];
		}

		public function getDateStringShortened( $date ) {
			$monthNames = $this->monthNames();
			$dateParts = split( '-', $date );
			$timestamp = mktime( 0, 0, 0, $dateParts[1], 1, $dateParts[0] );
			return $monthNames[date( "F", $timestamp )];
		}
		/**
		 * echo the next tab index
		 * @return void
		 */
		public function tabIndex() {
			echo $this->tabIndexStart;
			$this->tabIndexStart++;
		}

		public function getEvents( $args = '' ) {
			$tribe_ecp = TribeEvents::instance();
			$defaults = array(
				'posts_per_page' => tribe_get_option( 'postsPerPage', 10 ),
				'post_type' => TribeEvents::POSTTYPE,
				'orderby' => 'event_date',
				'order' => 'ASC'
			);			

			$args = wp_parse_args( $args, $defaults);
			return TribeEventsQuery::getEvents($args);
		}

		public function isEvent( $postId = null ) {
			if ( $postId === null || ! is_numeric( $postId ) ) {
				global $post;
				$postId = $post->ID;
			}
			if ( get_post_field('post_type', $postId) == self::POSTTYPE ) {
				return true;
			}
			return false;
		}

		public function isVenue( $postId = null ) {
			if ( $postId === null || ! is_numeric( $postId ) ) {
				global $post;
				$postId = $post->ID;
			}
			if ( get_post_field('post_type', $postId) == self::VENUE_POST_TYPE ) {
				return true;
			}
			return false;
		}

		public function isOrganizer( $postId = null ) {
			if ( $postId === null || ! is_numeric( $postId ) ) {
				global $post;
				$postId = $post->ID;
			}
			if ( get_post_field('post_type', $postId) == self::ORGANIZER_POST_TYPE ) {
				return true;
			}
			return false;
		}

		/**
	 ** Get a "previous/next post" link for events. Ordered by start date instead of ID.
	 **/

		public function get_event_link($post, $mode = 'next',$anchor = false){
			global $wpdb;

			if($mode == 'previous'){
				$order = 'DESC';
				$sign = '<';
			}else{
				$order = 'ASC';
				$sign = '>';
			}
	
			$date = $post->EventStartDate;
			$id = $post->ID;
	
			$eventsQuery = "
				SELECT $wpdb->posts.*, d1.meta_value as EventStartDate
				FROM $wpdb->posts 
				LEFT JOIN $wpdb->postmeta as d1 ON($wpdb->posts.ID = d1.post_id)
				WHERE $wpdb->posts.post_type = '".self::POSTTYPE."'
				AND d1.meta_key = '_EventStartDate'
				AND ((d1.meta_value = '" .$date . "' AND ID $sign ".$id.") OR
					d1.meta_value $sign '" .$date . "')
				AND $wpdb->posts.post_status = 'publish'
				AND ($wpdb->posts.ID != $id OR d1.meta_value != '$date')
				ORDER BY TIMESTAMP(d1.meta_value) $order, ID $order
				LIMIT 1";

			$results = $wpdb->get_row($eventsQuery, OBJECT);
			if(is_object($results)) {
				if ( !$anchor ) {
					$anchor = $results->post_title;
            } elseif ( strpos( $anchor, '%title%' ) !== false ) {
					$anchor = preg_replace( '|%title%|', $results->post_title, $anchor );
				}

				echo '<a href='.tribe_get_event_link($results).'>'.$anchor.'</a>';
		
			}
		}

		public function addMetaLinks( $links, $file ) {
			if ( $file == $this->pluginDir . 'the-events-calendar.php' ) {
				$anchor = __( 'Support', 'tribe-events-calendar' );
				$links []= '<a href="'.self::$dotOrgSupportUrl.'">' . $anchor . '</a>';

				$anchor = __( 'View All Add-Ons', 'tribe-events-calendar' );
				$links []= '<a href="'.self::$tribeUrl.self::$addOnPath.self::$refQueryString.'">' . $anchor . '</a>';
			}
			return $links;
		}

		public function dashboardWidget() {
			wp_add_dashboard_widget( 'tribe_dashboard_widget', __( 'News from Modern Tribe' ), array( $this, 'outputDashboardWidget' ) );
		}

		public function outputDashboardWidget() {
			echo '<div class="rss-widget">';
			wp_widget_rss_output( self::FEED_URL, array( 'items' => 10 ) );
			echo "</div>";
		}

		protected function constructDaysOfWeek() {
			global $wp_locale;
			for ($i = 0; $i <= 6; $i++) {
				$day = $wp_locale->get_weekday($i);
				$this->daysOfWeek[$i] = $day;
				$this->daysOfWeekShort[$i] = $wp_locale->get_weekday_abbrev($day);
				$this->daysOfWeekMin[$i] = $wp_locale->get_weekday_initial($day);
			}
		}

		public function setPostExceptionThrown( $thrown ) {
			$this->postExceptionThrown = $thrown;
		}
		public function getPostExceptionThrown() {
			return $this->postExceptionThrown;
		}

		public function do_action($name, $event_id = null, $showMessage = false, $extra_args = null) {
			try {
				do_action( $name, $event_id, $extra_args );
				if( !$this->getPostExceptionThrown() && $event_id ) delete_post_meta( $event_id, TribeEvents::EVENTSERROROPT );
			} catch ( TribeEventsPostException $e ) {
				$this->setPostExceptionThrown(true);
				if ($event_id) {
					update_post_meta( $event_id, self::EVENTSERROROPT, trim( $e->getMessage() ) );
				}

				if( $showMessage ) {
					$e->displayMessage($showMessage);
				}
			}
		}

		public function maybeShowMetaUpsell($postId) {
			?><tr class="eventBritePluginPlug">
				<td colspan="2" class="tribe_sectionheader">
					<h4><?php _e('Additional Functionality', 'tribe-events-calendar'); ?></h4>	
				</td>
			</tr>
			<tr class="eventBritePluginPlug">
				<td colspan="2">
					<p><?php _e('Looking for additional functionality including recurring events, custom meta, community events, ticket sales and more?', 'tribe-events-calendar' ) ?> <?php printf( __('Check out the <a href="%s">available add-ons</a>.', 'tribe-events-calendar' ), TribeEvents::$tribeUrl.'shop/?ref=tec-event' ); ?></p>
				</td>
			</tr><?php 
		}

		
		/**
		 * Helper function for getting Post Id. Accepts null or a post id.
		 *
		 * @param int $postId (optional)
		 * @return int post ID
		 */
		public static function postIdHelper( $postId = null ) {
			if ( $postId != null && is_numeric( $postId ) > 0 ) {
				return (int) $postId;
			} else {
				return get_the_ID();
			}
		}
		
		/**
		 * Add the buttons/dropdown to the admin toolbar
		 *
		 * @since 2.0.7
		 * @return null
		 */
		public function addToolbarItems() {
			if ( !defined( 'TRIBE_DISABLE_TOOLBAR_ITEMS' ) || !TRIBE_DISABLE_TOOLBAR_ITEMS ) {
				global $wp_admin_bar;
				
				$wp_admin_bar->add_menu( array(
					'id' => 'tribe-events',
					'title' => __( 'Events', 'tribe-events-calendar' ),
					'href' => $this->getLink( 'home' )
				) );
				
				$wp_admin_bar->add_group( array(
					'id' => 'tribe-events-group',
					'parent' => 'tribe-events'
				) );
				
				$wp_admin_bar->add_group( array(
					'id' => 'tribe-events-add-ons-group',
					'parent' => 'tribe-events'
				) );
				
				$wp_admin_bar->add_group( array(
					'id' => 'tribe-events-settings-group',
					'parent' => 'tribe-events'
				) );
				
				$wp_admin_bar->add_group( array(
					'id' => 'tribe-events-import-group',
					'parent' => 'tribe-events-add-ons-group'
				) );
				
				$wp_admin_bar->add_menu( array(
					'id' => 'tribe-events-view-calendar',
					'title' => __( 'View Calendar', 'tribe-events-calendar' ),
					'href' => $this->getLink( 'home' ),
					'parent' => 'tribe-events-group'
				) );
				
				if( current_user_can( 'edit_tribe_events' ) ) {
					$wp_admin_bar->add_menu( array(
						'id' => 'tribe-events-add-event',
						'title' => __( 'Add Event', 'tribe-events-calendar' ),
						'href' => trailingslashit( get_admin_url() ) . 'post-new.php?post_type=' . self::POSTTYPE,
						'parent' => 'tribe-events-group'
					) );
				}
				
				if( current_user_can( 'edit_tribe_events' ) ) {
					$wp_admin_bar->add_menu( array(
						'id' => 'tribe-events-edit-events',
						'title' => __( 'Edit Events', 'tribe-events-calendar' ),
						'href' => trailingslashit( get_admin_url() ) . 'edit.php?post_type=' . self::POSTTYPE,
						'parent' => 'tribe-events-group'
					) );
				}
							
				if ( current_user_can( 'manage_options' ) ) {			
					$wp_admin_bar->add_menu( array(
						'id' => 'tribe-events-settings',
						'title' => __( 'Settings', 'tribe-events-calendar' ),
						'parent' => 'tribe-events-settings-group'
					) );
				}
				
				if ( current_user_can( 'manage_options' ) ) {			
					$wp_admin_bar->add_menu( array(
						'id' => 'tribe-events-settings-sub',
						'title' => __( 'Events', 'tribe-events-calendar' ),
						'href' => trailingslashit( get_admin_url() ) . 'edit.php?post_type=' . self::POSTTYPE . '&page=tribe-events-calendar',
						'parent' => 'tribe-events-settings'
					) );
				}
	
				if ( current_user_can( 'manage_options' ) ) {			
					$wp_admin_bar->add_menu( array(
						'id' => 'tribe-events-help',
						'title' => __( 'Help', 'tribe-events-calendar' ),
						'href' => trailingslashit( get_admin_url() ) . 'edit.php?post_type=' . self::POSTTYPE . '&page=tribe-events-calendar&tab=help',
						'parent' => 'tribe-events-settings-group'
					) );
				}
			}
		}
		
		/**
		 * Displays activation welcome admin notice.
		 *
		 * @since 2.0.8
		 * @author PaulHughes01
		 *
		 * @return void
		 */
		public function activationMessage() {
			$has_been_activated = $this->getOption( 'welcome_notice', false );
			if ( !$has_been_activated ) {
				echo '<div class="updated tribe-notice"><p>'.sprintf( __('Welcome to The Events Calendar! Your events calendar can be found at %s. To change the events slug, visit %sEvents -> Settings%s.', 'tribe-events-calendar'), '<a href="' . $this->getLink() .'">' . $this->getLink() . '</a>', '<i><a href="' . add_query_arg( array( 'post_type' => self::POSTTYPE, 'page' => 'tribe-events-calendar' ), admin_url( 'edit.php' ) ) . '">', '</i></a>' ).'</p></div>';
				$this->setOption( 'welcome_notice', true );
			}
		}
		
		/**
		 * Resets the option such that the activation message is again displayed on reactivation.
		 *
		 * @since 2.0.8
		 * @author PaulHughes01
		 *
		 * @return void
		 */
		public function resetActivationMessage() {
			$tec = TribeEvents::instance();
			$tec->setOption( 'welcome_notice', false );
		}
		
		/**
		 * Displays the View Calendar link at the top of the Events list in admin.
		 *
		 * @since 2.0.8
		 * @author PaulHughes01
		 *
		 * @return void
		 */
		public function addViewCalendar() {
			global $current_screen;
			if ( $current_screen->id == 'edit-' . self::POSTTYPE )
				echo '<div class="view-calendar-link-div"><h2 class="wrap"><a class="add-new-h2 view-calendar-link" href="' . $this->getLink() . '">' . __( 'View Calendar', 'tribe-events-calendar' ) . '</a></h2></div>';
		}
		
		/**
		 * Set the menu-edit-page to default display the events-related items.
		 *
		 * @since 2.0.8
		 * @author PaulHughes01
		 *
		 * @return void
		 */
		public function setInitialMenuMetaBoxes() {
			global $current_screen;
			if ( $current_screen->id == 'nav-menus' ) {
				$user = wp_get_current_user();
				if ( !get_user_option( 'tribe_setDefaultNavMenuBoxes', $user->ID ) ) {
					
					$current_hidden_boxes = array();
					$current_hidden_boxes =  get_user_option( 'metaboxhidden_nav-menus', $user->ID );
					if ( $array_key = array_search( 'add-' . self::POSTTYPE, $current_hidden_boxes ) )
						unset( $current_hidden_boxes[$array_key] );
					if ( $array_key = array_search( 'add-' . self::VENUE_POST_TYPE, $current_hidden_boxes ) )
						unset( $current_hidden_boxes[$array_key] );
					if ( $array_key = array_search( 'add-' . self::ORGANIZER_POST_TYPE, $current_hidden_boxes ) )
						unset( $current_hidden_boxes[$array_key] );
					if ( $array_key = array_search( 'add-' . self::TAXONOMY, $current_hidden_boxes ) )
						unset( $current_hidden_boxes[$array_key] );
					
					update_user_option( $user->ID, 'metaboxhidden_nav-menus', $current_hidden_boxes, true );
					
					update_user_option( $user->ID, 'tribe_setDefaultNavMenuBoxes', true, true );
				}
			}
		}
		
		public function addLinksToPluginActions( $actions ) {
			$actions['settings'] = '<a href="' . add_query_arg( array( 'post_type' => self::POSTTYPE, 'page' => 'tribe-events-calendar' ), admin_url( 'edit.php' ) ) .'">' . __('Settings', 'tribe-events-calendar') . '</a>';
			$actions['tribe-calendar'] = '<a href="' . $this->getLink() .'">' . __('Calendar', 'tribe-events-calendar') . '</a>';
			return $actions;
		}
		
		public function addHelpAdminMenuItem() {
    		global $submenu;
    		$submenu['edit.php?post_type=' . self::POSTTYPE][500] = array( __('Help', 'tribe-events-calendar'), 'manage_options' , add_query_arg( array( 'post_type' => self::POSTTYPE, 'page' => 'tribe-events-calendar', 'tab' => 'help' ), admin_url( 'edit.php' ) ) ); 
		} 
		
		/**
		 * Filter call that returns the proper link for after a comment is submitted to a recurring event.
		 *
		 * @author PaulHughes01
		 * @since 2.0.8
		 *
		 * @param string $content
		 * @param object $comment the comment object
		 * @return string the link
		 */ 
		public function newCommentLink( $content, $comment ) {
			if ( class_exists( 'TribeEventsPro' ) && tribe_is_recurring_event( get_the_ID() ) && isset( $_REQUEST['eventDate'] ) ) {
				$link = trailingslashit( $this->getLink( 'single' ) ) . $_REQUEST['eventDate'] . '#comment-' . $comment->comment_ID;
			} else {
				$link = $content;
			}
			return $link;
		}
		
		/**
		 * Adds a hidden field to recurring events comments forms that stores the eventDate.
		 *
		 * @author PaulHughes01
		 * @since 2.0.8
		 * 
		 * @return void
		 */
		public function addHiddenRecurringField() {
			echo '<input type="hidden" name="eventDate" value="' . get_query_var( 'eventDate' ) . '" />';
		}

	} // end TribeEvents class

} // end if !class_exists TribeEvents