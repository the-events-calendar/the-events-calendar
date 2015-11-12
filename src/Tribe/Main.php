<?php
/**
 * Main Tribe Events Calendar class.
 */

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( ! class_exists( 'Tribe__Events__Main' ) ) {

	/**
	 * The Events Calendar Class
	 *
	 * This is where all the magic happens, the unicorns run wild and the leprechauns use WordPress to schedule events.
	 */
	class Tribe__Events__Main {
		const EVENTSERROROPT      = '_tribe_events_errors';
		const OPTIONNAME          = 'tribe_events_calendar_options';
		const OPTIONNAMENETWORK   = 'tribe_events_calendar_network_options';
		const TAXONOMY            = 'tribe_events_cat';
		const POSTTYPE            = 'tribe_events';
		const VENUE_POST_TYPE     = 'tribe_venue';
		const ORGANIZER_POST_TYPE = 'tribe_organizer';

		const VERSION           = '3.12.6';
		const MIN_ADDON_VERSION = '3.12';
		const FEED_URL          = 'https://theeventscalendar.com/feed/';
		const INFO_API_URL      = 'http://wpapi.org/api/plugin/the-events-calendar.php';
		const WP_PLUGIN_URL     = 'http://wordpress.org/extend/plugins/the-events-calendar/';

		/**
		 * Notices to be displayed in the admin
		 * @var array
		 */
		protected $notices = array();

		/**
		 * Maybe display data wrapper
		 * @var array
		 */
		private $show_data_wrapper = array( 'before' => true, 'after' => true );

		/**
		 * Args for the event post type
		 * @var array
		 */
		protected $postTypeArgs = array(
			'public'          => true,
			'rewrite'         => array( 'slug' => 'event', 'with_front' => false ),
			'menu_position'   => 6,
			'supports'        => array(
				'title',
				'editor',
				'excerpt',
				'author',
				'thumbnail',
				'custom-fields',
				'comments',
			),
			'taxonomies'      => array( 'post_tag' ),
			'capability_type' => array( 'tribe_event', 'tribe_events' ),
			'map_meta_cap'    => true,
			'has_archive'     => true,
		);

		/**
		 * Args for venue post type
		 * @var array
		 */
		public $postVenueTypeArgs = array(
			'public'              => false,
			'rewrite'             => array( 'slug' => 'venue', 'with_front' => false ),
			'show_ui'             => true,
			'show_in_menu'        => 0,
			'supports'            => array( 'title', 'editor' ),
			'capability_type'     => array( 'tribe_venue', 'tribe_venues' ),
			'map_meta_cap'        => true,
			'exclude_from_search' => true,
		);

		protected $taxonomyLabels;

		/**
		 * Args for organizer post type
		 * @var array
		 */
		public $postOrganizerTypeArgs = array(
			'public'              => false,
			'rewrite'             => array( 'slug' => 'organizer', 'with_front' => false ),
			'show_ui'             => true,
			'show_in_menu'        => 0,
			'supports'            => array( 'title', 'editor' ),
			'capability_type'     => array( 'tribe_organizer', 'tribe_organizers' ),
			'map_meta_cap'        => true,
			'exclude_from_search' => true,
		);

		public static $tribeUrl = 'http://tri.be/';
		public static $tecUrl = 'http://theeventscalendar.com/';

		public static $addOnPath = 'products/';

		public static $dotOrgSupportUrl = 'http://wordpress.org/tags/the-events-calendar';

		protected static $instance;
		public $rewriteSlug = 'events';
		public $rewriteSlugSingular = 'event';
		public $taxRewriteSlug = 'event/category';
		public $tagRewriteSlug = 'event/tag';
		public $monthSlug = 'month';

		/** @var Tribe__Events__Admin__Timezone_Settings */
		public $timezone_settings;

		// @todo remove in 4.0
		public $upcomingSlug = 'upcoming';
		public $pastSlug = 'past';

		public $listSlug = 'list';
		public $daySlug = 'day';
		public $todaySlug = 'today';
		protected $postExceptionThrown = false;

		protected static $networkOptions;
		public $displaying;
		public $pluginDir;
		public $pluginPath;
		public $pluginUrl;
		public $pluginName;
		public $date;
		protected $tabIndexStart = 2000;

		public $metaTags = array(
			'_EventAllDay',
			'_EventStartDate',
			'_EventEndDate',
			'_EventStartDateUTC',
			'_EventEndDateUTC',
			'_EventDuration',
			'_EventVenueID',
			'_EventShowMapLink',
			'_EventShowMap',
			'_EventCurrencySymbol',
			'_EventCurrencyPosition',
			'_EventCost',
			'_EventCostMin',
			'_EventCostMax',
			'_EventURL',
			'_EventOrganizerID',
			'_EventPhone',
			'_EventHideFromUpcoming',
			'_EventTimezone',
			'_EventTimezoneAbbr',
			self::EVENTSERROROPT,
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
			'_VenuePhone',
			'_VenueURL',
		);

		public $organizerTags = array(
			'_OrganizerOrganizer',
			'_OrganizerEmail',
			'_OrganizerWebsite',
			'_OrganizerPhone',
		);

		public $currentPostTimestamp;

		public $daysOfWeekShort;
		public $daysOfWeek;
		public $daysOfWeekMin;
		public $monthsFull;
		public $monthsShort;

		public $singular_venue_label;
		public $plural_venue_label;

		public $singular_organizer_label;
		public $plural_organizer_label;

		public $singular_event_label;
		public $plural_event_label;

		/** @var Tribe__Events__Default_Values */
		private $default_values = null;

		public static $tribeEventsMuDefaults;

		/**
		 * Static Singleton Factory Method
		 *
		 * @return Tribe__Events__Main
		 */
		public static function instance() {
			if ( ! isset( self::$instance ) ) {
				$className      = __CLASS__;
				self::$instance = new $className;
			}

			return self::$instance;
		}

		/**
		 * Initializes plugin variables and sets up WordPress hooks/actions.
		 */
		protected function __construct() {
			$this->pluginPath = trailingslashit( dirname( dirname( dirname( __FILE__ ) ) ) );
			$this->pluginDir  = trailingslashit( basename( $this->pluginPath ) );
			$this->pluginUrl  = plugins_url( $this->pluginDir );

			// include the autolader class
			require_once( $this->pluginPath . '/src/Tribe/Autoloader.php' );
			$this->init_autoloading();

			add_action( 'init', array( $this, 'loadTextDomain' ), 1 );

			if ( self::supportedVersion( 'wordpress' ) && self::supportedVersion( 'php' ) ) {

				$this->addHooks();
				$this->loadLibraries();
			} else {
				// Either PHP or WordPress version is inadequate so we simply return an error.
				add_action( 'admin_head', array( $this, 'notSupportedError' ) );
			}
		}

		/**
		 * Load all the required library files.
		 */
		protected function loadLibraries() {
			// Tribe common resources
			require_once $this->pluginPath . 'vendor/tribe-common-libraries/tribe-common-libraries.class.php';

			// Load CSV importer
			require_once $this->pluginPath . 'src/io/csv/ecp-events-importer.php';

			// Load Template Tags
			require_once $this->pluginPath . 'src/functions/template-tags/query.php';
			require_once $this->pluginPath . 'src/functions/template-tags/general.php';
			require_once $this->pluginPath . 'src/functions/template-tags/month.php';
			require_once $this->pluginPath . 'src/functions/template-tags/loop.php';
			require_once $this->pluginPath . 'src/functions/template-tags/google-map.php';
			require_once $this->pluginPath . 'src/functions/template-tags/organizer.php';
			require_once $this->pluginPath . 'src/functions/template-tags/venue.php';
			require_once $this->pluginPath . 'src/functions/template-tags/date.php';
			require_once $this->pluginPath . 'src/functions/template-tags/link.php';
			require_once $this->pluginPath . 'src/functions/template-tags/widgets.php';
			require_once $this->pluginPath . 'src/functions/template-tags/meta.php';
			require_once $this->pluginPath . 'src/functions/template-tags/tickets.php';

			// Load Advanced Functions
			require_once $this->pluginPath . 'src/functions/advanced-functions/event.php';
			require_once $this->pluginPath . 'src/functions/advanced-functions/venue.php';
			require_once $this->pluginPath . 'src/functions/advanced-functions/organizer.php';

			// Load Deprecated Template Tags
			if ( ! defined( 'TRIBE_DISABLE_DEPRECATED_TAGS' ) ) {
				require_once $this->pluginPath . 'src/functions/template-tags/deprecated.php';
			}

			// Load multisite defaults
			if ( is_multisite() ) {
				$tribe_events_mu_defaults = array();
				if ( file_exists( WP_CONTENT_DIR . '/tribe-events-mu-defaults.php' ) ) {
					require_once WP_CONTENT_DIR . '/tribe-events-mu-defaults.php';
				}
				self::$tribeEventsMuDefaults = apply_filters( 'tribe_events_mu_defaults', $tribe_events_mu_defaults );
			}
		}

		/**
		 * before_html_data_wrapper adds a persistant tag to wrap the event display with a
		 * way for jQuery to maintain state in the dom. Also has a hook for filtering data
		 * attributes for inclusion in the dom
		 *
		 * @param  string $html
		 *
		 * @return string
		 */
		public function before_html_data_wrapper( $html ) {
			global $wp_query;

			if ( ! $this->show_data_wrapper['before'] ) {
				return $html;
			}

			$tec = self::instance();

			$data_attributes = array(
				'live_ajax'         => tribe_get_option( 'liveFiltersUpdate', true ) ? 1 : 0,
				'datepicker_format' => tribe_get_option( 'datepickerFormat' ),
				'category'          => is_tax( $tec->get_event_taxonomy() ) ? get_query_var( 'term' ) : '',
			);
			// allow data attributes to be filtered before display
			$data_attributes = (array) apply_filters( 'tribe_events_view_data_attributes', $data_attributes );

			// loop through the attributes and build the html output
			foreach ( $data_attributes as $id => $attr ) {
				$attribute_html[] = sprintf(
					'data-%s="%s"',
					sanitize_title( $id ),
					esc_attr( $attr )
				);
			}

			$this->show_data_wrapper['before'] = false;

			// return filtered html
			return apply_filters( 'tribe_events_view_before_html_data_wrapper', sprintf( '<div id="tribe-events" class="tribe-no-js" %s>%s', implode( ' ', $attribute_html ), $html ), $data_attributes, $html );
		}

		/**
		 * after_html_data_wrapper close out the persistant dom wrapper
		 *
		 * @param  string $html
		 *
		 * @return string
		 */
		public function after_html_data_wrapper( $html ) {
			if ( ! $this->show_data_wrapper['after'] ) {
				return $html;
			}

			$html .= '</div><!-- #tribe-events -->';
			$html .= tribe_events_promo_banner( false );
			$this->show_data_wrapper['after'] = false;

			return apply_filters( 'tribe_events_view_after_html_data_wrapper', $html );
		}

		/**
		 * Add filters and actions
		 */
		protected function addHooks() {
			// Load Rewrite
			add_action( 'plugins_loaded', array( Tribe__Events__Rewrite::instance(), 'hooks' ) );

			add_action( 'init', array( $this, 'init' ), 10 );
			add_action( 'admin_init', array( $this , 'admin_init' ) );

			// Frontend Javascript
			add_action( 'wp_enqueue_scripts', array( $this, 'loadStyle' ) );
			add_filter( 'tribe_events_before_html', array( $this, 'before_html_data_wrapper' ) );
			add_filter( 'tribe_events_after_html', array( $this, 'after_html_data_wrapper' ) );

			// Styling
			add_filter( 'post_class', array( $this, 'post_class' ) );
			add_filter( 'body_class', array( $this, 'body_class' ) );
			add_filter( 'admin_body_class', array( $this, 'admin_body_class' ) );

			add_filter( 'post_type_archive_link', array( $this, 'event_archive_link' ), 10, 2 );
			add_filter( 'query_vars', array( $this, 'eventQueryVars' ) );
			add_filter( 'bloginfo_rss', array( $this, 'add_space_to_rss' ) );
			add_filter( 'post_updated_messages', array( $this, 'updatePostMessage' ) );

			/* Add nav menu item - thanks to http://wordpress.org/extend/plugins/cpt-archives-in-nav-menus/ */
			add_filter( 'nav_menu_items_' . self::POSTTYPE, array( $this, 'add_events_checkbox_to_menu' ), null, 3 );
			add_filter( 'wp_nav_menu_objects', array( $this, 'add_current_menu_item_class_to_events' ), null, 2 );

			add_filter( 'template_redirect', array( $this, 'redirect_past_upcoming_view_urls' ), 11 );

			/* Setup Tribe Events Bar */
			add_filter( 'tribe-events-bar-views', array( $this, 'setup_listview_in_bar' ), 1, 1 );
			add_filter( 'tribe-events-bar-views', array( $this, 'setup_gridview_in_bar' ), 5, 1 );
			add_filter( 'tribe-events-bar-views', array( $this, 'setup_dayview_in_bar' ), 15, 1 );

			add_filter( 'tribe-events-bar-filters', array( $this, 'setup_date_search_in_bar' ), 1, 1 );
			add_filter( 'tribe-events-bar-filters', array( $this, 'setup_keyword_search_in_bar' ), 1, 1 );

			add_filter( 'tribe-events-bar-views', array( $this, 'remove_hidden_views' ), 9999, 2 );
			/* End Setup Tribe Events Bar */

			add_action( 'admin_menu', array( $this, 'addEventBox' ) );
			add_action( 'wp_insert_post', array( $this, 'addPostOrigin' ), 10, 2 );
			add_action( 'save_post', array( $this, 'addEventMeta' ), 15, 2 );

			/* Registers the list widget */
			add_action( 'widgets_init', array( $this, 'register_list_widget' ), 90 );

			add_action( 'save_post_' . self::VENUE_POST_TYPE, array( $this, 'save_venue_data' ), 16, 2 );
			add_action( 'save_post_' . self::ORGANIZER_POST_TYPE, array( $this, 'save_organizer_data' ), 16, 2 );
			add_action( 'save_post_' . self::POSTTYPE, array( $this, 'maybe_update_known_range' ) );
			add_action( 'tribe_events_csv_import_complete', array( $this, 'rebuild_known_range' ) );
			add_action( 'publish_' . self::POSTTYPE, array( $this, 'publishAssociatedTypes' ), 25, 2 );
			add_action( 'delete_post', array( $this, 'maybe_rebuild_known_range' ) );
			add_action( 'parse_query', array( $this, 'setDisplay' ), 51, 0 );
			add_action( 'tribe_events_post_errors', array( 'Tribe__Events__Post_Exception', 'displayMessage' ) );
			add_action( 'tribe_settings_top', array( 'Tribe__Events__Options_Exception', 'displayMessage' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'add_admin_assets' ) );
			add_filter( 'tribe_events_register_event_type_args', array( $this, 'setDashicon' ) );
			add_action( 'trash_' . self::VENUE_POST_TYPE, array( $this, 'cleanupPostVenues' ) );
			add_action( 'trash_' . self::ORGANIZER_POST_TYPE, array( $this, 'cleanupPostOrganizers' ) );
			add_action( 'wp_ajax_tribe_event_validation', array( $this, 'ajax_form_validate' ) );
			add_action( 'tribe_debug', array( $this, 'renderDebug' ), 10, 2 );
			add_action( 'tribe_debug', array( $this, 'renderDebug' ), 10, 2 );
			add_action( 'plugins_loaded', array( 'Tribe__Events__Cache_Listener', 'instance' ) );
			add_action( 'plugins_loaded', array( 'Tribe__Events__Cache', 'setup' ) );
			add_action( 'plugins_loaded', array( 'Tribe__Events__Support', 'getInstance' ) );
			add_action( 'plugins_loaded', array( $this, 'set_meta_factory_global' ) );
			add_action( 'plugins_loaded', array( 'Tribe__Events__App_Shop', 'instance' ) );
			add_action( 'current_screen', array( $this, 'init_admin_list_screen' ) );

			// Load organizer and venue editors
			add_action( 'admin_menu', array( $this, 'addVenueAndOrganizerEditor' ) );
			add_action( 'tribe_venue_table_top', array( $this, 'displayEventVenueDropdown' ) );
			add_action( 'tribe_venue_table_top', array( $this, 'display_rich_snippets_helper' ), 5 );

			add_action( 'template_redirect', array( $this, 'template_redirect' ) );

			add_action( 'add_meta_boxes', array( 'Tribe__Events__Tickets__Metabox', 'maybe_add_meta_box' ) );
			add_action( 'admin_enqueue_scripts', array( 'Tribe__Events__Tickets__Metabox', 'add_admin_scripts'  ) );

			add_action( 'wp', array( $this, 'issue_noindex' ) );
			add_action( 'plugin_row_meta', array( $this, 'addMetaLinks' ), 10, 2 );
			// organizer and venue
			if ( ! defined( 'TRIBE_HIDE_UPSELL' ) || ! TRIBE_HIDE_UPSELL ) {
				add_action( 'wp_dashboard_setup', array( $this, 'dashboardWidget' ) );
				add_action( 'tribe_events_cost_table', array( $this, 'maybeShowMetaUpsell' ) );
			}
			// option pages
			add_action( '_network_admin_menu', array( $this, 'initOptions' ) );
			add_action( '_admin_menu', array( $this, 'initOptions' ) );
			add_action( 'tribe_settings_do_tabs', array( $this, 'doSettingTabs' ) );
			add_action( 'tribe_settings_do_tabs', array( $this, 'doNetworkSettingTab' ), 400 );
			add_action( 'tribe_settings_content_tab_help', array( $this, 'doHelpTab' ) );
			add_action( 'tribe_settings_validate_tab_network', array( $this, 'saveAllTabsHidden' ) );

			add_action( 'load-tribe_events_page_tribe-events-calendar', array( 'Tribe__Events__Amalgamator', 'listen_for_migration_button' ), 10, 0 );
			add_action( 'tribe_settings_after_save', array( $this, 'flushRewriteRules' ) );
			add_action( 'load-edit-tags.php', array( $this, 'prepare_to_fix_tagcloud_links' ), 10, 0 );
			add_action( 'update_option_' . self::OPTIONNAME, array( $this, 'fix_all_day_events' ), 10, 2 );

			// Check for a page that might conflict with events archive
			add_action( 'admin_init', array( Tribe__Events__Admin__Notice__Archive_Slug_Conflict::instance(), 'maybe_add_admin_notice' ) );

			// add-on compatibility
			if ( is_multisite() ) {
				add_action( 'network_admin_notices', array( $this, 'checkAddOnCompatibility' ) );
			} else {
				add_action( 'admin_notices', array( $this, 'checkAddOnCompatibility' ) );
			}

			add_action( 'wp_before_admin_bar_render', array( $this, 'addToolbarItems' ), 10 );
			add_action( 'all_admin_notices', array( $this, 'addViewCalendar' ) );
			add_action( 'admin_head', array( $this, 'setInitialMenuMetaBoxes' ), 500 );
			add_action( 'plugin_action_links_' . trailingslashit( $this->pluginDir ) . 'the-events-calendar.php', array( $this, 'addLinksToPluginActions' ) );
			add_action( 'admin_menu', array( $this, 'addHelpAdminMenuItem' ), 50 );

			// override default wp_terms_checklist arguments to prevent checked items from bubbling to the top. Instead, retain hierarchy.
			add_filter( 'wp_terms_checklist_args', array( $this, 'prevent_checked_on_top_terms' ), 10, 2 );

			add_action( 'tribe_events_pre_get_posts', array( $this, 'set_tribe_paged' ) );

			// Upgrade material.
			add_action( 'init', array( $this, 'run_updates' ), 0, 0 );

			if ( defined( 'WP_LOAD_IMPORTERS' ) && WP_LOAD_IMPORTERS ) {
				add_filter( 'wp_import_post_data_raw', array( $this, 'filter_wp_import_data_before' ), 10, 1 );
				add_filter( 'wp_import_post_data_processed', array( $this, 'filter_wp_import_data_after' ), 10, 1 );
			}


			add_action( 'plugins_loaded', array( $this, 'init_ical' ), 2, 0 );
			add_action( 'plugins_loaded', array( $this, 'init_day_view' ), 2 );

			add_action( 'plugins_loaded', array( 'Tribe__Events__Bar', 'instance' ) );
			add_action( 'plugins_loaded', array( 'Tribe__Events__Templates', 'init' ) );

			add_action( 'init', array( $this, 'filter_cron_schedules' ) );

		}

		/**
		 * Load the ical template tags
		 * Loaded late due to potential upgrade conflict since moving them from pro
		 * @TODO move this require to be with the rest of the template tag includes in 3.9
		 */
		public function init_ical() {
			//iCal
			Tribe__Events__iCal::init();
				require_once $this->pluginPath . 'src/functions/template-tags/ical.php';
			}

		/**
		 * Allow users to specify their own plural label for Venues
		 * @return string
		 */
		public function get_venue_label_plural() {
			return apply_filters( 'tribe_venue_label_plural', __( 'Venues', 'the-events-calendar' ) );
		}

		/**
		 * Allow users to specify their own singular label for Venues
		 * @return string
		 */
		public function get_venue_label_singular() {
			return apply_filters( 'tribe_venue_label_singular', __( 'Venue', 'the-events-calendar' ) );
		}

		/**
		 * Allow users to specify their own plural label for Organizers
		 * @return string
		 */
		public function get_organizer_label_plural() {
			return apply_filters( 'tribe_organizer_label_plural', __( 'Organizers', 'the-events-calendar' ) );
		}

		/**
		 * Allow users to specify their own singular label for Organizers
		 * @return string
		 */
		public function get_organizer_label_singular() {
			return apply_filters( 'tribe_organizer_label_singular', __( 'Organizer', 'the-events-calendar' ) );
		}

		/**
		 * Allow users to specify their own plural label for Events
		 * @return string
		 */
		public function get_event_label_plural() {
			return apply_filters( 'tribe_event_label_plural', __( 'Events', 'the-events-calendar' ) );
		}

		/**
		 * Allow users to specify their own singular label for Events
		 * @return string
		 */
		public function get_event_label_singular() {
			return apply_filters( 'tribe_event_label_singular', __( 'Event', 'the-events-calendar' ) );
		}

		/**
		 * Load the day view template tags
		 * Loaded late due to potential upgrade conflict since moving them from pro
		 * @TODO move this require to be with the rest of the template tag includes in 3.9
		 */
		public function init_day_view() {
			// load day view functions
			require_once $this->pluginPath . 'src/functions/template-tags/day.php';
		}

		/**
		 * Runs on the "wp" action. Inspects the main query object and if it relates to an events
		 * query makes a decision to add a noindex meta tag based on whether events were returned
		 * in the query results or not.
		 *
		 * Disabling this behaviour always is possible with:
		 *
		 *     add_filter( 'tribe_events_add_no_index_meta', '__return_false' );
		 *
		 *  Enabling it for all event views is possible with:
		 *
		 *     add_filter( 'tribe_events_add_no_index_meta', '__return_true' );
		 */
		public function issue_noindex() {
			global $wp_query;

			if ( empty( $wp_query->tribe_is_event_query ) ) {
				return;
			}

			// By default, we add a noindex tag for all month view requests and any other
			// event views that are devoid of events
			$event_display = get_query_var( 'eventDisplay' );
			$add_noindex   = ( ! $wp_query->have_posts() || 'month' === $event_display );

			/**
			 * Determines if a noindex meta tag will be set for the current event view.
			 *
			 * @var bool $add_noindex
			 */
			$add_noindex = apply_filters( 'tribe_events_add_no_index_meta', $add_noindex );

			if ( $add_noindex ) {
				add_action( 'wp_head', array( $this, 'print_noindex_meta' ) );
			}
		}

		/**
		 * Prints a "noindex,follow" robots tag.
		 */
		public function print_noindex_meta() {
			echo ' <meta name="robots" content="noindex,follow" />' . "\n";
		}

		/**
		 * Run on applied action init
		 */
		public function init() {
			$this->pluginName                                 = __( 'The Events Calendar', 'the-events-calendar' );
			$this->rewriteSlug                                = $this->getRewriteSlug();
			$this->rewriteSlugSingular                        = $this->getRewriteSlugSingular();
			$this->taxRewriteSlug                             = $this->getTaxRewriteSlug();
			$this->tagRewriteSlug                             = $this->getTagRewriteSlug();
			$this->monthSlug                                  = sanitize_title( __( 'month', 'the-events-calendar' ) );
			$this->listSlug                               	  = sanitize_title( __( 'list', 'the-events-calendar' ) );
			$this->upcomingSlug                               = sanitize_title( __( 'upcoming', 'the-events-calendar' ) );
			$this->pastSlug                                   = sanitize_title( __( 'past', 'the-events-calendar' ) );
			$this->daySlug                                    = sanitize_title( __( 'day', 'the-events-calendar' ) );
			$this->todaySlug                                  = sanitize_title( __( 'today', 'the-events-calendar' ) );

			$this->singular_venue_label                       = $this->get_venue_label_singular();
			$this->plural_venue_label                         = $this->get_venue_label_plural();
			$this->singular_organizer_label                   = $this->get_organizer_label_singular();
			$this->plural_organizer_label                     = $this->get_organizer_label_plural();
			$this->singular_event_label                       = $this->get_event_label_singular();
			$this->plural_event_label                         = $this->get_event_label_plural();

			$this->postTypeArgs['rewrite']['slug']            = sanitize_title( $this->rewriteSlugSingular );
			$this->postVenueTypeArgs['rewrite']['slug']       = sanitize_title( $this->singular_venue_label );
			$this->postVenueTypeArgs['show_in_nav_menus']     = class_exists( 'Tribe__Events__Pro__Main' ) ? true : false;
			$this->postOrganizerTypeArgs['rewrite']['slug']   = sanitize_title( $this->singular_organizer_label );
			$this->postOrganizerTypeArgs['show_in_nav_menus'] = class_exists( 'Tribe__Events__Pro__Main' ) ? true : false;
			$this->postVenueTypeArgs['public']                = class_exists( 'Tribe__Events__Pro__Main' ) ? true : false;
			$this->postOrganizerTypeArgs['public']            = class_exists( 'Tribe__Events__Pro__Main' ) ? true : false;
			$this->currentDay                                 = '';
			$this->errors                                     = '';

			$this->default_values                             = apply_filters( 'tribe_events_default_value_strategy', new Tribe__Events__Default_Values() );

			Tribe__Events__Query::init();
			Tribe__Events__Backcompat::init();
			Tribe__Events__Credits::init();
			Tribe__Events__Timezones::init();
			$this->registerPostType();

			self::debug( sprintf( __( 'Initializing Tribe Events on %s', 'the-events-calendar' ), date( 'M, jS \a\t h:m:s a' ) ) );
			$this->maybeSetTECVersion();
		}

		/**
		 * Initializes any admin-specific code (expects to be called when admin_init fires).
		 */
		public function admin_init() {
			$this->timezone_settings = new Tribe__Events__Admin__Timezone_Settings;
		}

		/**
		 * Set the Calendar Version in the options table if it's not already set.
		 *
		 */
		public function maybeSetTECVersion() {
			if ( version_compare( $this->getOption( 'latest_ecp_version' ), self::VERSION, '<' ) ) {
				$previous_versions   = $this->getOption( 'previous_ecp_versions' ) ? $this->getOption( 'previous_ecp_versions' ) : array();
				$previous_versions[] = ( $this->getOption( 'latest_ecp_version' ) ) ? $this->getOption( 'latest_ecp_version' ) : '0';

				$this->setOption( 'previous_ecp_versions', $previous_versions );
				$this->setOption( 'latest_ecp_version', self::VERSION );
			}
		}

		/**
		 * Check add-ons to make sure they are supported by currently running TEC version.
		 *
		 * @return void
		 */
		public function checkAddOnCompatibility() {

			// Variable for storing output to admin notices.
			$output = '';

			// Array to store any plugins that are out of date.
			$out_of_date_addons = array();

			// Array to store all addons and their required CORE versions.
			$tec_addons_required_versions = array();

			// Is Core the thing that is out of date?
			$tec_out_of_date = false;

			// Get the addon information.
			$tec_addons_required_versions = (array) apply_filters( 'tribe_tec_addons', $tec_addons_required_versions );

			// Foreach addon, make sure that it is compatible with current version of core.
			foreach ( $tec_addons_required_versions as $plugin ) {
				// we're not going to check addons that we can't
				if ( empty( $plugin['required_version'] ) || empty( $plugin['current_version'] ) ) {
					continue;
				}

				// check if TEC is out of date
				if ( version_compare( $plugin['required_version'], self::VERSION, '>' ) ) {
					$tec_out_of_date = true;
					break;
				}

				// check if the add-on is out of date
				if ( version_compare( $plugin['current_version'], self::MIN_ADDON_VERSION, '<' ) ) {
					$out_of_date_addons[] = $plugin['plugin_name'] . ' ' . $plugin['current_version'];
				}
			}
			// If Core is out of date, generate the proper message.
			if ( $tec_out_of_date == true ) {
				$plugin_short_path = basename( dirname( dirname( __FILE__ ) ) ) . '/the-events-calendar.php';
				$upgrade_path      = wp_nonce_url(
					add_query_arg(
						array(
							'action' => 'upgrade-plugin',
							'plugin' => $plugin_short_path,
						), get_admin_url() . 'update.php'
					), 'upgrade-plugin_' . $plugin_short_path
				);
				$output .= '<div class="error">';
				$output .= '<p>' . sprintf( __( 'Your version of The Events Calendar is not up-to-date with one of your The Events Calendar add-ons. Please %supdate now.%s', 'the-events-calendar' ), '<a href="' . esc_url( $upgrade_path ) . '">', '</a>' ) . '</p>';
				$output .= '</div>';
			} elseif ( ! empty( $out_of_date_addons ) ) {
				// Otherwise, if the addons are out of date, generate the proper messaging.
				$output .= '<div class="error">';
				$link = add_query_arg(
					array(
						'utm_campaign' => 'in-app',
						'utm_medium'   => 'plugin-tec',
						'utm_source'   => 'notice',
						), self::$tecUrl . 'knowledgebase/version-compatibility/'
				);
				$output .= '<p>' . sprintf( __( 'The following plugins are out of date: <b>%s</b>. All add-ons contain dependencies on The Events Calendar and will not function properly unless paired with the right version. %sLearn More%s.', 'the-events-calendar' ), join( $out_of_date_addons, ', ' ), "<a href='" . esc_url( $link ) . "' target='_blank'>", '</a>' ) . '</p>';
				$output .= '</div>';
			}
			// Make sure only to show the message if the user has the permissions necessary.
			if ( current_user_can( 'edit_plugins' ) ) {
				echo apply_filters( 'tribe_add_on_compatibility_errors', $output );
			}
		}

		/**
		 * Init the settings API and add a hook to add your own setting tabs
		 *
		 * @return void
		 */
		public function initOptions() {

			Tribe__Events__Settings::instance();
			Tribe__Events__Activation_Page::init();
		}

		/**
		 * Trigger is_404 on single event if no events are found
		 * @return void
		 */
		public function template_redirect() {
			global $wp_query;

			// if JS is disabled, then we need to handle tribe bar submissions manually
			if ( ! empty( $_POST['tribe-bar-view'] ) && ! empty( $_POST['submit-bar'] ) ) {
				$this->handle_submit_bar_redirect( $_POST );
			}

			if ( $wp_query->tribe_is_event_query && self::instance()->displaying == 'single-event' && empty( $wp_query->posts ) ) {
				$wp_query->is_404 = true;
			}
		}

		/**
		 * handles tribe bar post submissions
		 *
		 * @param array $postdata Data from $_POST
		 */
		public function handle_submit_bar_redirect( $postdata ) {
			$url = $postdata['tribe-bar-view'];

			foreach ( $postdata as $key => $value ) {
				if ( 'submit-bar' === $key || 'tribe-bar-view' === $key ) {
					continue;
				}

				$url = add_query_arg( $key, $value, $url );
			}

			wp_redirect( esc_url_raw( $url ) );
			die;
		}//end handle_submit_bar_redirect

		/**
		 * Create setting tabs
		 *
		 * @return void
		 */
		public function doSettingTabs() {
			include_once( $this->pluginPath . 'src/admin-views/tribe-options-general.php' );
			include_once( $this->pluginPath . 'src/admin-views/tribe-options-display.php' );

			$showNetworkTabs = $this->getNetworkOption( 'showSettingsTabs', false );

			new Tribe__Events__Settings_Tab( 'general', __( 'General', 'the-events-calendar' ), $generalTab );
			new Tribe__Events__Settings_Tab( 'display', __( 'Display', 'the-events-calendar' ), $displayTab );

			$this->do_licenses_tab();

			new Tribe__Events__Settings_Tab( 'help', __( 'Help', 'the-events-calendar' ), array(
				'priority'  => 60,
				'show_save' => false,
			) );
		}

		/**
		 * Registers the license key management tab in the Events > Settings screen,
		 * only if premium addons are detected.
		 */
		protected function do_licenses_tab() {
			$show_tab = ( current_user_can( 'update_plugins' ) && $this->have_addons() );

			/**
			 * Provides an oppotunity to override the decision to show or hide the licenses tab
			 *
			 * Normally it will only show if the current user has the "update_plugins" capability
			 * and there are some currently-activated premium plugins.
			 *
			 * @var bool
			 */
			if ( ! apply_filters( 'tribe_events_show_licenses_tab', $show_tab ) ) {
				return;
			}

			/**
			 * @var $licenses_tab
			 */
			include $this->pluginPath . 'src/admin-views/tribe-options-licenses.php';

			/**
			 * Allows the fields displayed in the licenses tab to be modified.
			 *
			 * @var array
			 */
			$license_fields = apply_filters( 'tribe_license_fields', $licenses_tab );

			new Tribe__Events__Settings_Tab( 'licenses', __( 'Licenses', 'the-events-calendar' ), array(
				'priority'      => '40',
				'fields'        => $license_fields,
				'network_admin' => is_network_admin() ? true : false,
			) );
		}

		/**
		 * Tries to discover if licensable addons are activated on the same site.
		 *
		 * @return bool
		 */
		protected function have_addons() {
			$addons = apply_filters( 'tribe_licensable_addons', array() );
			return ! empty( $addons );
		}

		/**
		 * Create the help tab
		 */
		public function doHelpTab() {
			include_once( $this->pluginPath . 'src/admin-views/tribe-options-help.php' );
		}

		/**
		 * Updates the start/end time on all day events to match the EOD cutoff
		 *
		 * @see 'update_option_'.self::OPTIONNAME
		 */
		public function fix_all_day_events( $old_value, $new_value ) {
			// avoid notices for missing indices
			$default_value = '00:00';
			if ( empty( $old_value['multiDayCutoff'] ) ) {
				$old_value['multiDayCutoff'] = $default_value;
			}
			if ( empty( $new_value['multiDayCutoff'] ) ) {
				$new_value['multiDayCutoff'] = $default_value;
			}

			if ( $old_value['multiDayCutoff'] == $new_value['multiDayCutoff'] ) {
				// we only want to continue if the EOD cutoff was changed
				return;
			}
			global $wpdb;
			$event_start_time = $new_value['multiDayCutoff'] . ':00';

			// mysql query to set the start times on all day events to the EOD cutoff
			// this will fix all day events with any start time
			$fix_start_dates = $wpdb->prepare( "UPDATE $wpdb->postmeta AS pm1
				INNER JOIN $wpdb->postmeta pm2
					ON (pm1.post_id = pm2.post_id AND pm2.meta_key = '_EventAllDay' AND pm2.`meta_value` = 'yes')
				SET pm1.meta_value = CONCAT(DATE(pm1.meta_value), ' ', %s)
				WHERE pm1.meta_key = '_EventStartDate'
					AND DATE_FORMAT(pm1.meta_value, '%%H:%%i') <> %s", $event_start_time, $event_start_time );

			// mysql query to set the end time to the start time plus the duration on every all day event
			$fix_end_dates      =
				"UPDATE $wpdb->postmeta AS pm1
				INNER JOIN $wpdb->postmeta pm2
					ON (pm1.post_id = pm2.post_id AND pm2.meta_key = '_EventAllDay' AND pm2.meta_value = 'yes')
				INNER JOIN $wpdb->postmeta pm3
					ON (pm1.post_id = pm3.post_id AND pm3.meta_key = '_EventStartDate')
				INNER JOIN $wpdb->postmeta pm4
					ON (pm1.post_id = pm4.post_id AND pm4.meta_key = '_EventDuration')
				SET pm1.meta_value = DATE_ADD(pm3.meta_value, INTERVAL pm4.meta_value SECOND )
				WHERE pm1.meta_key = '_EventEndDate'";
			$wpdb->query( $fix_start_dates );
			$wpdb->query( $fix_end_dates );
		}

		/**
		 * Test PHP and WordPress versions for compatibility
		 *
		 * @param string $system - system to be tested such as 'php' or 'wordpress'
		 *
		 * @return boolean - is the existing version of the system supported?
		 */
		public function supportedVersion( $system ) {
			if ( $supported = wp_cache_get( $system, 'tribe_version_test' ) ) {
				return $supported;
			} else {
				switch ( strtolower( $system ) ) {
					case 'wordpress' :
						$supported = version_compare( get_bloginfo( 'version' ), '3.0', '>=' );
						break;
					case 'php' :
						$supported = version_compare( phpversion(), '5.2', '>=' );
						break;
				}
				$supported = apply_filters( 'tribe_events_supported_version', $supported, $system );
				wp_cache_set( $system, $supported, 'tribe_version_test' );

				return $supported;
			}
		}

		/**
		 * Display a WordPress or PHP incompatibility error
		 */
		public function notSupportedError() {
			if ( ! self::supportedVersion( 'wordpress' ) ) {
				echo '<div class="error"><p>' . esc_html( sprintf( __( 'Sorry, The Events Calendar requires WordPress %s or higher. Please upgrade your WordPress install.', 'the-events-calendar' ), '3.0' ) ) . '</p></div>';
			}
			if ( ! self::supportedVersion( 'php' ) ) {
				echo '<div class="error"><p>' . esc_html( sprintf( __( 'Sorry, The Events Calendar requires PHP %s or higher. Talk to your Web host about moving you to a newer version of PHP.', 'the-events-calendar' ), '5.2' ) ) . '</p></div>';
			}
		}

		/**
		 * Add a menu item class to the event
		 *
		 * @param array $items
		 * @param array $args
		 *
		 * @return array
		 */
		public function add_current_menu_item_class_to_events( $items, $args ) {
			foreach ( $items as $item ) {
				if ( $item->url == $this->getLink() ) {
					if ( is_singular( self::POSTTYPE )
						 || is_singular( self::VENUE_POST_TYPE )
						 || is_tax( self::TAXONOMY )
						 || ( ( tribe_is_upcoming()
								|| tribe_is_past()
								|| tribe_is_month() )
							  && isset( $wp_query->query_vars['eventDisplay'] ) )
					) {
						$item->classes[] = 'current-menu-item current_page_item';
					}
					break;
				}
			}

			return $items;
		}

		/**
		 * Add a checkbox to the menu
		 *
		 * @param array  $posts
		 * @param array  $args
		 * @param string $post_type
		 *
		 * @return array
		 */
		public function add_events_checkbox_to_menu( $posts, $args, $post_type ) {
			global $_nav_menu_placeholder, $wp_rewrite;
			$_nav_menu_placeholder = ( 0 > $_nav_menu_placeholder ) ? intval( $_nav_menu_placeholder ) - 1 : - 1;
			$archive_slug          = $this->getLink();

			array_unshift(
				$posts, (object) array(
					'ID'           => 0,
					'object_id'    => $_nav_menu_placeholder,
					'post_content' => '',
					'post_excerpt' => '',
					'post_title'   => $post_type['args']->labels->all_items,
					'post_type'    => 'nav_menu_item',
					'type'         => 'custom',
					'url'          => $archive_slug,
				)
			);

			return $posts;
		}

		/**
		 * Tribe debug function. usage: self::debug( 'Message', $data, 'log' );
		 *
		 * @param string      $title  Message to display in log
		 * @param string|bool $data   Optional data to display
		 * @param string      $format Optional format (log|warning|error|notice)
		 *
		 * @return void
		 */
		public static function debug( $title, $data = false, $format = 'log' ) {
			do_action( 'tribe_debug', $title, $data, $format );
		}

		/**
		 * Render the debug logging to the php error log. This can be over-ridden by removing the filter.
		 *
		 * @param string      $title  - message to display in log
		 * @param string|bool $data   - optional data to display
		 * @param string      $format - optional format (log|warning|error|notice)
		 *
		 * @return void
		 */
		public function renderDebug( $title, $data = false, $format = 'log' ) {
			$format = ucfirst( $format );
			if ( $this->getOption( 'debugEvents' ) ) {
				error_log( $this->pluginName . " $format: $title" );
				if ( $data && $data != '' ) {
					error_log( $this->pluginName . " $format: " . print_r( $data, true ) );
				}
			}
		}

		/**
		 * Define an admin notice
		 *
		 * @param string $key
		 * @param string $notice
		 *
		 * @return bool
		 */
		public static function setNotice( $key, $notice ) {
			self::instance()->notices[ $key ] = $notice;

			return true;
		}

		/**
		 * Check to see if an admin notice exists
		 *
		 * @param string $key
		 *
		 * @return bool
		 */
		public static function isNotice( $key ) {
			return ! empty( self::instance()->notices[ $key ] ) ? true : false;
		}

		/**
		 * Remove an admin notice
		 *
		 * @param string $key
		 *
		 * @return bool
		 */
		public static function removeNotice( $key ) {
			if ( self::isNotice( $key ) ) {
				unset( self::instance()->notices[ $key ] );

				return true;
			} else {
				return false;
			}
		}

		/**
		 * Get the admin notices
		 *
		 * @return array
		 */
		public static function getNotices() {
			return self::instance()->notices;
		}

		/**
		 * Get the event taxonomy
		 *
		 * @return string
		 */
		public function get_event_taxonomy() {
			return self::TAXONOMY;
		}

		/**
		 * Add space to the title in RSS
		 *
		 * @param string $title
		 *
		 * @return string
		 */
		public function add_space_to_rss( $title ) {
			global $wp_query;
			if ( get_query_var( 'eventDisplay' ) == 'upcoming' && get_query_var( 'post_type' ) == self::POSTTYPE ) {
				return $title . ' ';
			}

			return $title;
		}

		/**
		 * Update body classes
		 *
		 * @param array $classes
		 *
		 * @return array
		 * @TODO move this to template class
		 */
		public function body_class( $classes ) {
			if ( get_query_var( 'post_type' ) == self::POSTTYPE ) {
				if ( ! is_admin() && tribe_get_option( 'liveFiltersUpdate', true ) ) {
					$classes[] = 'tribe-filter-live';
				}
			}

			return $classes;
		}

		/**
		 * Update post classes
		 *
		 * @param array $classes
		 *
		 * @return array
		 * @TODO move this to template class
		 */
		public function post_class( $classes ) {
			global $post;
			if ( is_object( $post ) && isset( $post->post_type ) && $post->post_type == self::POSTTYPE && $terms = get_the_terms( $post->ID, self::TAXONOMY ) ) {
				foreach ( $terms as $term ) {
					$classes[] = 'cat_' . sanitize_html_class( $term->slug, $term->term_taxonomy_id );
				}
			}

			// Remove the .hentry class if it is a single event page (it is positioned elsewhere in the template markup)
			if ( tribe_is_event( $post->ID ) && is_singular() && in_array( 'hentry', $classes ) ) {
				unset( $classes[ array_search( 'hentry', $classes ) ] );
			}

			return $classes;
		}

		/**
		 * Register the post types.
		 *
		 * @return void
		 */
		public function registerPostType() {
			$this->generatePostTypeLabels();
			register_post_type( self::POSTTYPE, apply_filters( 'tribe_events_register_event_type_args', $this->postTypeArgs ) );
			register_post_type( self::VENUE_POST_TYPE, apply_filters( 'tribe_events_register_venue_type_args', $this->postVenueTypeArgs ) );
			register_post_type( self::ORGANIZER_POST_TYPE, apply_filters( 'tribe_events_register_organizer_type_args', $this->postOrganizerTypeArgs ) );

			register_taxonomy(
				self::TAXONOMY, self::POSTTYPE, array(
					'hierarchical'          => true,
					'update_count_callback' => '',
					'rewrite'               => array(
						'slug'         => $this->taxRewriteSlug,
						'with_front'   => false,
						'hierarchical' => true,
					),
					'public'                => true,
					'show_ui'               => true,
					'labels'                => $this->taxonomyLabels,
					'capabilities'          => array(
						'manage_terms' => 'publish_tribe_events',
						'edit_terms'   => 'publish_tribe_events',
						'delete_terms' => 'publish_tribe_events',
						'assign_terms' => 'edit_tribe_events',
					)
				)
			);

			if ( $this->getOption( 'showComments', 'no' ) == 'yes' ) {
				add_post_type_support( self::POSTTYPE, 'comments' );
			}

		}

		/**
		 * Get the rewrite slug
		 *
		 * @return string
		 */
		public function getRewriteSlug() {
			return sanitize_title( $this->getOption( 'eventsSlug', 'events' ) );
		}

		/**
		 * Get the single post rewrite slug
		 *
		 * @return string
		 */
		public function getRewriteSlugSingular() {
			return sanitize_title( $this->getOption( 'singleEventSlug', 'event' ) );
		}

		/**
		 * Get taxonomy rewrite slug
		 *
		 * @return mixed|void
		 */
		public function getTaxRewriteSlug() {
			$slug = $this->getRewriteSlug() . '/' . sanitize_title( __( 'category', 'the-events-calendar' ) );

			return apply_filters( 'tribe_events_category_rewrite_slug', $slug );
		}

		/**
		 * Get tag rewrite slug
		 *
		 * @return mixed|void
		 */
		public function getTagRewriteSlug() {
			$slug = $this->getRewriteSlug() . '/' . sanitize_title( __( 'tag', 'the-events-calendar' ) );

			return apply_filters( 'tribe_events_tag_rewrite_slug', $slug );
		}

		/**
		 * Get venue post type args
		 *
		 * @return array
		 */
		public function getVenuePostTypeArgs() {
			return $this->postVenueTypeArgs;
		}

		/**
		 * Get organizer post type args
		 *
		 * @return array
		 */
		public function getOrganizerPostTypeArgs() {
			return $this->postOrganizerTypeArgs;
		}

		/**
		 * Generate custom post type lables
		 */
		protected function generatePostTypeLabels() {
			/**
			 * Provides an opportunity to modify the labels used for the event post type.
			 *
			 * @var array
			 */
			$this->postTypeArgs['labels'] = apply_filters( 'tribe_events_register_event_post_type_labels', array(
				'name'               => $this->plural_event_label,
				'singular_name'      => $this->singular_event_label,
				'add_new'            => __( 'Add New', 'the-events-calendar' ),
				'add_new_item'       => sprintf( __( 'Add New %s', 'the-events-calendar' ), $this->singular_event_label ),
				'edit_item'          => sprintf( __( 'Edit %s', 'the-events-calendar' ), $this->singular_event_label ),
				'new_item'           => sprintf( __( 'New %s', 'the-events-calendar' ), $this->singular_event_label ),
				'view_item'          => sprintf( __( 'View %s', 'the-events-calendar' ), $this->singular_event_label ),
				'search_items'       => sprintf( __( 'Search %s', 'the-events-calendar' ), $this->plural_event_label ),
				'not_found'          => sprintf( __( 'No %s found', 'the-events-calendar' ), strtolower( $this->plural_event_label ) ),
				'not_found_in_trash' => sprintf( __( 'No %s found in Trash', 'the-events-calendar' ), strtolower( $this->plural_event_label ) ),
			) );

			/**
			 * Provides an opportunity to modify the labels used for the venue post type.
			 *
			 * @var array
			 */
			$this->postVenueTypeArgs['labels'] = apply_filters( 'tribe_events_register_venue_post_type_labels', array(
				'name'               => $this->plural_venue_label,
				'singular_name'      => $this->singular_venue_label,
				'add_new'            => __( 'Add New', 'the-events-calendar' ),
				'add_new_item'       => sprintf( __( 'Add New %s', 'the-events-calendar' ), $this->singular_venue_label ),
				'edit_item'          => sprintf( __( 'Edit %s', 'the-events-calendar' ), $this->singular_venue_label ),
				'new_item'           => sprintf( __( 'New %s', 'the-events-calendar' ), $this->singular_venue_label ),
				'view_item'          => sprintf( __( 'View %s', 'the-events-calendar' ), $this->singular_venue_label ),
				'search_items'       => sprintf( __( 'Search %s', 'the-events-calendar' ), $this->plural_venue_label ),
				'not_found'          => sprintf( __( 'No %s found', 'the-events-calendar' ), strtolower( $this->plural_venue_label ) ),
				'not_found_in_trash' => sprintf( __( 'No %s found in Trash', 'the-events-calendar' ), strtolower( $this->plural_venue_label ) ),
			) );

			/**
			 * Provides an opportunity to modify the labels used for the organizer post type.
			 *
			 * @var array
			 */
			$this->postOrganizerTypeArgs['labels'] = apply_filters( 'tribe_events_register_organizer_post_type_labels', array(
				'name'               => $this->plural_organizer_label,
				'singular_name'      => $this->singular_organizer_label,
				'add_new'            => __( 'Add New', 'the-events-calendar' ),
				'add_new_item'       => sprintf( __( 'Add New %s', 'the-events-calendar' ), $this->singular_organizer_label ),
				'edit_item'          => sprintf( __( 'Edit %s', 'the-events-calendar' ), $this->singular_organizer_label ),
				'new_item'           => sprintf( __( 'New %s', 'the-events-calendar' ), $this->singular_organizer_label ),
				'view_item'          => sprintf( __( 'View %s', 'the-events-calendar' ), $this->singular_organizer_label ),
				'search_items'       => sprintf( __( 'Search %s', 'the-events-calendar' ), $this->plural_organizer_label ),
				'not_found'          => sprintf( __( 'No %s found', 'the-events-calendar' ), strtolower( $this->plural_organizer_label ) ),
				'not_found_in_trash' => sprintf( __( 'No %s found in Trash', 'the-events-calendar' ), strtolower( $this->plural_organizer_label ) ),
			) );

			/**
			 * Provides an opportunity to modify the labels used for the event category taxonomy.
			 *
			 * @var array
			 */
			$this->taxonomyLabels = apply_filters( 'tribe_events_register_category_taxonomy_labels', array(
				'name'              => sprintf( __( '%s Categories', 'the-events-calendar' ), $this->singular_event_label ),
				'singular_name'     => sprintf( __( '%s Category', 'the-events-calendar' ), $this->singular_event_label ),
				'search_items'      => sprintf( __( 'Search %s Categories', 'the-events-calendar' ), $this->singular_event_label ),
				'all_items'         => sprintf( __( 'All %s Categories', 'the-events-calendar' ), $this->singular_event_label ),
				'parent_item'       => sprintf( __( 'Parent %s Category', 'the-events-calendar' ), $this->singular_event_label ),
				'parent_item_colon' => sprintf( __( 'Parent %s Category:', 'the-events-calendar' ), $this->singular_event_label ),
				'edit_item'         => sprintf( __( 'Edit %s Category', 'the-events-calendar' ), $this->singular_event_label ),
				'update_item'       => sprintf( __( 'Update %s Category', 'the-events-calendar' ), $this->singular_event_label ),
				'add_new_item'      => sprintf( __( 'Add New %s Category', 'the-events-calendar' ), $this->singular_event_label ),
				'new_item_name'     => sprintf( __( 'New %s Category Name', 'the-events-calendar' ), $this->singular_event_label ),
			) );
		}

		/**
		 * Update custom post type messages
		 *
		 * @param $messages
		 *
		 * @return mixed
		 */
		public function updatePostMessage( $messages ) {
			global $post, $post_ID;

			$messages[ self::POSTTYPE ] = array(
				0  => '', // Unused. Messages start at index 1.
				1  => sprintf( __( '%1$s updated. <a href="%2$s">View %3$s</a>', 'the-events-calendar' ), $this->singular_event_label, esc_url( get_permalink( $post_ID ) ), strtolower( $this->singular_event_label ) ),
				2  => __( 'Custom field updated.', 'the-events-calendar' ),
				3  => __( 'Custom field deleted.', 'the-events-calendar' ),
				4  => sprintf( __( '%s updated.', 'the-events-calendar' ), $this->singular_event_label ),
				/* translators: %s: date and time of the revision */
				5  => isset( $_GET['revision'] ) ? sprintf( __( '%1$s restored to revision from %2$s', 'the-events-calendar' ), $this->singular_event_label, wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
				6  => sprintf(
					__( 'Event published. <a href="%1$s">View %2$s</a>', 'the-events-calendar' ),
					esc_url( get_permalink( $post_ID ) ),
					strtolower( $this->singular_event_label )
				),
				7  => sprintf( __( '%s saved.', 'the-events-calendar' ), $this->singular_event_label ),
				8  => sprintf(
					__( '%1$s submitted. <a target="_blank" href="%2$s">Preview %3$s</a>', 'the-events-calendar' ),
					$this->singular_event_label,
					esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ),
					strtolower( $this->singular_event_label )
				),
				9  => sprintf(
					__( '%1$s scheduled for: <strong>%2$s</strong>. <a target="_blank" href="%3$s">Preview %4$s</a>', 'the-events-calendar' ),
					$this->singular_event_label,
					// translators: Publish box date format, see http://php.net/date
					date_i18n( __( 'M j, Y @ G:i', 'the-events-calendar' ), strtotime( $post->post_date ) ),
					esc_url( get_permalink( $post_ID ) ),
					strtolower( $this->singular_event_label )
				),
				10 => sprintf(
					__( '%1$s draft updated. <a target="_blank" href="%2$s">Preview %3$s</a>', 'the-events-calendar' ),
					$this->singular_event_label,
					esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ),
					strtolower( $this->singular_event_label )
				),
			);

			$messages[ self::VENUE_POST_TYPE ] = array(
				0  => '', // Unused. Messages start at index 1.
				1  => sprintf( __( '%s updated.', 'the-events-calendar' ), $this->singular_venue_label ),
				2  => __( 'Custom field updated.', 'the-events-calendar' ),
				3  => __( 'Custom field deleted.', 'the-events-calendar' ),
				4  => sprintf( __( '%s updated.', 'the-events-calendar' ), $this->singular_venue_label ),
				/* translators: %s: date and time of the revision */
				5  => isset( $_GET['revision'] ) ? sprintf( __( '%s restored to revision from %s', 'the-events-calendar' ), $this->singular_venue_label, wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
				6  => sprintf( __( '%s published.', 'the-events-calendar' ), $this->singular_venue_label ),
				7  => sprintf( __( '%s saved.', 'the-events-calendar' ), $this->singular_venue_label ),
				8  => sprintf( __( '%s submitted.', 'the-events-calendar' ), $this->singular_venue_label ),
				9  => sprintf(
					__( '%s scheduled for: <strong>%2$s</strong>.', 'the-events-calendar' ), $this->singular_venue_label,
					// translators: Publish box date format, see http://php.net/date
					date_i18n( __( 'M j, Y @ G:i', 'the-events-calendar' ), strtotime( $post->post_date ) )
				),
				10 => sprintf( __( '%s draft updated.', 'the-events-calendar' ), $this->singular_venue_label ),
			);

			$messages[ self::ORGANIZER_POST_TYPE ] = array(
				0  => '', // Unused. Messages start at index 1.
				1  => sprintf( __( '%s updated.', 'the-events-calendar' ), $this->singular_organizer_label ),
				2  => __( 'Custom field updated.', 'the-events-calendar' ),
				3  => __( 'Custom field deleted.', 'the-events-calendar' ),
				4  => sprintf( __( '%s updated.', 'the-events-calendar' ), $this->singular_organizer_label ),
				/* translators: %s: date and time of the revision */
				5  => isset( $_GET['revision'] ) ? sprintf( __( '%s restored to revision from %s', 'the-events-calendar' ), $this->singular_organizer_label, wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
				6  => sprintf( __( '%s published.', 'the-events-calendar' ), $this->singular_organizer_label ),
				7  => sprintf( __( '%s saved.', 'the-events-calendar' ), $this->singular_organizer_label ),
				8  => sprintf( __( '%s submitted.', 'the-events-calendar' ), $this->singular_organizer_label ),
				9  => sprintf(
					__( '%s scheduled for: <strong>%2$s</strong>.', 'the-events-calendar' ), $this->singular_organizer_label,
					// translators: Publish box date format, see http://php.net/date
					date_i18n( __( 'M j, Y @ G:i', 'the-events-calendar' ), strtotime( $post->post_date ) )
				),
				10 => sprintf( __( '%s draft updated.', 'the-events-calendar' ), $this->singular_organizer_label ),
			);

			return $messages;
		}

		/**
		 * Adds the submenu items for editing the Venues and Organizers.
		 * Used to be a PRO only feature, but as of 3.0, it is part of Core.
		 *
		 *
		 * @return void
		 */
		public function addVenueAndOrganizerEditor() {
			add_submenu_page( 'edit.php?post_type=' . self::POSTTYPE, __( $this->plural_venue_label, 'the-events-calendar' ), __( $this->plural_venue_label, 'the-events-calendar' ), 'edit_tribe_venues', 'edit.php?post_type=' . self::VENUE_POST_TYPE );
			add_submenu_page( 'edit.php?post_type=' . self::POSTTYPE, __( $this->plural_organizer_label, 'the-events-calendar' ), __( $this->plural_organizer_label, 'the-events-calendar' ), 'edit_tribe_organizers', 'edit.php?post_type=' . self::ORGANIZER_POST_TYPE );
			add_submenu_page( 'edit.php?post_type=' . self::VENUE_POST_TYPE, sprintf( __( 'Add New %s', 'the-events-calendar' ), $this->singular_venue_label ), sprintf( __( 'Add New %s', 'the-events-calendar' ), $this->singular_venue_label ), 'edit_tribe_venues', 'post-new.php?post_type=' . self::VENUE_POST_TYPE );
			add_submenu_page( 'edit.php?post_type=' . self::ORGANIZER_POST_TYPE, sprintf( __( 'Add New %s', 'the-events-calendar' ), $this->singular_organizer_label ), sprintf( __( 'Add New %s', 'the-events-calendar' ), $this->singular_organizer_label ), 'edit_tribe_organizers', 'post-new.php?post_type=' . self::ORGANIZER_POST_TYPE );
		}

		/**
		 * displays the saved venue dropdown in the event metabox
		 * Used to be a PRO only feature, but as of 3.0, it is part of Core.
		 *
		 * @param int $post_id the event ID for which to create the dropdown
		 */
		public function displayEventVenueDropdown( $post_id ) {
			$venue_id = get_post_meta( $post_id, '_EventVenueID', true );
			if (
				( ! $post_id || get_post_status( $post_id ) === 'auto-draft' ) &&
				! $venue_id &&
				Tribe__Events__Admin__Helpers::instance()->is_action( 'add' )
			) {
				$venue_id = $this->defaults()->venue_id();
			}
			$venue_id = apply_filters( 'tribe_display_event_venue_dropdown_id', $venue_id );

			?>
			<tr>
				<td style="width:170px"><?php printf( __( 'Use Saved %s:', 'the-events-calendar' ), $this->singular_venue_label ); ?></td>
				<td><?php
					$this->saved_venues_dropdown( $venue_id );
					$venue_pto = get_post_type_object( self::VENUE_POST_TYPE );
					if ( current_user_can( $venue_pto->cap->edit_posts ) ) { ?>
						<div class="edit-venue-link" <?php if ( empty( $venue_id ) ) { ?>style="display:none;"<?php } ?>><a data-admin-url="<?php echo esc_url( admin_url( 'post.php?action=edit&post=' ) ); ?>" href="<?php echo esc_url( admin_url( sprintf( 'post.php?action=edit&post=%s', $venue_id ) ) ); ?>" target="_blank"><?php echo esc_html( sprintf( __( 'Edit %s', 'the-events-calendar' ), $this->singular_venue_label ) ); ?></a></div>
					<?php } ?>
				</td>
			</tr>
		<?php
		}

		/**
		 * Display a helper for the user, about the location and microdata for rich snippets
		 * @param int $postId the event ID to see if the helper is needed
		 */
		public function display_rich_snippets_helper( $post_id ) {
			// Avoid showing this message if we are on the Front End
			if ( ! is_admin() ) {
				return;
			}

			$venue_id = get_post_meta( $post_id, '_EventVenueID', true );
			if (
				( ! $post_id || get_post_status( $post_id ) == 'auto-draft' ) &&
				! $venue_id &&
				Tribe__Events__Admin__Helpers::instance()->is_action( 'add' )
			) {
				$venue_id = $this->defaults()->venue_id();
			}
			$venue_id = apply_filters( 'tribe_display_event_venue_dropdown_id', $venue_id );

			// If there is a Venue of some sorts, don't display this message
			if ( $venue_id ) {
				return;
			}
			?>
			<tr class="">
				<td colspan="2"><?php _e( 'Without a defined location your event will not display a <a href="https://support.google.com/webmasters/answer/164506" target="_blank">Google Rich Snippet</a> on the search results.', 'the-events-calendar' ) ?></td>
			</tr>
			<?php
		}

		/**
		 * displays the saved organizer dropdown in the event metabox
		 * Used to be a PRO only feature, but as of 3.0, it is part of Core.
		 *
		 * @param int $post_id the event ID for which to create the dropdown
		 *
		 * @return void
		 */
		public function displayEventOrganizerDropdown( $post_id ) {
			$current_organizer = get_post_meta( $post_id, '_EventOrganizerID', true );
			if (
				( ! $post_id || get_post_status( $post_id ) === 'auto-draft' ) &&
				! $current_organizer &&
				Tribe__Events__Admin__Helpers::instance()->is_action( 'add' )
			) {
				$current_organizer = $this->defaults()->organizer_id();
			}
			$current_organizer = apply_filters( 'tribe_display_event_organizer_dropdown_id', $current_organizer );

			?>
			<tr class="">
				<td style="width:170px">
					<label for="saved_organizer"><?php printf( esc_html__( 'Use Saved %s:', 'the-events-calendar' ), $this->singular_organizer_label ); ?></label>
				</td>
				<td><?php $this->saved_organizers_dropdown( $current_organizer ); ?> <div class="edit-organizer-link"<?php if ( empty( $current_organizer ) ) { ?> style="display:none;"<?php } ?>><a data-admin-url="<?php echo esc_url( admin_url( 'post.php?action=edit&post=' ) ); ?>" href="<?php echo esc_url( admin_url( sprintf( 'post.php?action=edit&post=%s', $current_organizer ) ) ); ?>" target="_blank"><?php echo esc_html( sprintf( __( 'Edit %s', 'the-events-calendar' ), $this->singular_organizer_label ) ); ?></a></div></td>
			</tr>
		<?php
		}

		/**
		 * helper function for displaying the saved venue dropdown
		 * Used to be a PRO only feature, but as of 3.0, it is part of Core.
		 *
		 * @param mixed  $current the current saved venue
		 * @param string $name    the name value for the field
		 */
		public function saved_venues_dropdown( $current = null, $name = 'venue[VenueID]' ) {
			$my_venue_ids     = array();
			$current_user     = wp_get_current_user();
			$my_venues        = false;
			$my_venue_options = '';
			if ( 0 != $current_user->ID ) {
				$my_venues = $this->get_venue_info(
					null,
					array(
						'post_status' => array(
							'publish',
							'draft',
							'private',
							'pending',
						),
						'author' => $current_user->ID,
					)
				);

				if ( ! empty( $my_venues ) ) {
					foreach ( $my_venues as $my_venue ) {
						$my_venue_ids[] = $my_venue->ID;
						$venue_title    = wp_kses( get_the_title( $my_venue->ID ), array() );
						$my_venue_options .= '<option data-address="' . esc_attr( $this->fullAddressString( $my_venue->ID ) ) . '" value="' . esc_attr( $my_venue->ID ) . '"';
						$my_venue_options .= selected( $current, $my_venue->ID, false );
						$my_venue_options .= '>' . $venue_title . '</option>';
					}
				}
			}

			if ( current_user_can( 'edit_others_tribe_venues' ) ) {
				$venues = $this->get_venue_info(
					null,
					array(
						'post_status'  => array(
							'publish',
							'draft',
							'private',
							'pending',
						),
						'post__not_in' => $my_venue_ids,
					)
				);
			} else {
				$venues = $this->get_venue_info(
					null,
					array(
						'post_status'  => 'publish',
						'post__not_in' => $my_venue_ids,
					)
				);
			}
			if ( $venues || $my_venues ) {
				echo '<select class="chosen venue-dropdown" name="' . esc_attr( $name ) . '" id="saved_venue">';
				echo '<option value="0">' . esc_html( sprintf( __( 'Use New %s', 'the-events-calendar' ), $this->singular_venue_label ) ) . '</option>';

				if ( $my_venues ) {
					echo $venues ? '<optgroup label="' . esc_attr( apply_filters( 'tribe_events_saved_venues_dropdown_my_optgroup', sprintf( __( 'My %s', 'the-events-calendar' ), $this->plural_venue_label ) ) ) . '">' : '';
					echo $my_venue_options;
					echo $venues ? '</optgroup>' : '';
				}
				if ( $venues ) {
					echo $my_venues ? '<optgroup label="' . esc_attr( apply_filters( 'tribe_events_saved_venues_dropdown_optgroup', sprintf( __( 'Available %s', 'the-events-calendar' ), $this->plural_venue_label ) ) ) . '">' : '';
					foreach ( $venues as $venue ) {
						$venue_title = wp_kses( get_the_title( $venue->ID ), array() );
						echo '<option data-address="' . esc_attr( $this->fullAddressString( $venue->ID ) ) . '" value="' . esc_attr( $venue->ID ) . '"';
						selected( ( $current == $venue->ID ) );
						echo '>' . $venue_title . '</option>';
					}
					echo $my_venues ? '</optgroup>' : '';
				}
				echo '</select>';
			} else {
				echo '<p class="nosaved">' . esc_html( sprintf( __( 'No saved %s exists.', 'the-events-calendar' ), strtolower( $this->singular_venue_label ) ) ) . '</p>';
			}
		}

		/**
		 * helper function for displaying the saved organizer dropdown
		 * Used to be a PRO only feature, but as of 3.0, it is part of Core.
		 *
		 * @param mixed  $current the current saved venue
		 * @param string $name    the name value for the field
		 */
		public function saved_organizers_dropdown( $current = null, $name = 'organizer[OrganizerID]' ) {
			$my_organizer_ids      = array();
			$current_user          = wp_get_current_user();
			$my_organizers         = false;
			$my_organizers_options = '';
			if ( 0 != $current_user->ID ) {
				$my_organizers = $this->get_organizer_info(
					null,
					array(
						'post_status' => array(
							'publish',
							'draft',
							'private',
							'pending',
						),
						'author' => $current_user->ID,
					)
				);

				if ( ! empty( $my_organizers ) ) {
					foreach ( $my_organizers as $my_organizer ) {
						$my_organizer_ids[] = $my_organizer->ID;
						$organizer_title    = wp_kses( get_the_title( $my_organizer->ID ), array() );
						$my_organizers_options .= '<option value="' . esc_attr( $my_organizer->ID ) . '"';
						$my_organizers_options .= selected( $current, $my_organizer->ID, false );
						$my_organizers_options .= '>' . $organizer_title . '</option>';
					}
				}
			}


			if ( current_user_can( 'edit_others_tribe_organizers' ) ) {
				$organizers = $this->get_organizer_info(
					null, array(
						'post_status' => array(
							'publish',
							'draft',
							'private',
							'pending',
						),
						'post__not_in' => $my_organizer_ids,
					)
				);
			} else {
				$organizers = $this->get_organizer_info(
					null, array(
						'post_status'  => 'publish',
						'post__not_in' => $my_organizer_ids,
					)
				);
			}
			if ( $organizers || $my_organizers ) {
				echo '<select class="chosen organizer-dropdown" name="' . esc_attr( $name ) . '" id="saved_organizer">';
				echo '<option value="0">' . esc_html( sprintf( __( 'Use New %s', 'the-events-calendar' ), $this->singular_organizer_label ) ) . '</option>';

				if ( $my_organizers ) {
					echo $organizers ? '<optgroup label="' . esc_attr( apply_filters( 'tribe_events_saved_organizers_dropdown_my_optgroup', sprintf( __( 'My %s', 'the-events-calendar' ), $this->plural_organizer_label ) ) ) . '">' : '';
					echo $my_organizers_options;
					echo $organizers ? '</optgroup>' : '';
				}
				if ( $organizers ) {
					echo $my_organizers ? '<optgroup label="' . esc_attr( apply_filters( 'tribe_events_saved_organizers_dropdown_optgroup', sprintf( __( 'Available %s', 'the-events-calendar' ), $this->plural_organizer_label ) ) ) . '">' : '';
					foreach ( $organizers as $organizer ) {
						$organizer_title = wp_kses( get_the_title( $organizer->ID ), array() );
						echo '<option value="' . esc_attr( $organizer->ID ) . '"';
						selected( $current == $organizer->ID );
						echo '>' . $organizer_title . '</option>';
					}
					echo $my_organizers ? '</optgroup>' : '';
				}
				echo '</select>';
			} else {
				echo '<p class="nosaved">' . esc_html( sprintf( __( 'No saved %s exists.', 'the-events-calendar' ), strtolower( $this->singular_organizer_label ) ) ) . '</p>';
				printf( '<input type="hidden" name="%s" value="%d"/>', esc_attr( $name ), 0 );
			}
		}

		/**
		 * override default wp_terms_checklist arguments to prevent checked items from bubbling to the
		 * top. Instead, retain hierarchy.
		 */
		public function prevent_checked_on_top_terms( $args, $post_id ) {
			$post = get_post( $post_id );

			if ( ! tribe_is_event( $post ) ) {
				return $args;
			}

			$args['checked_ontop'] = false;

			return $args;
		}//end prevent_checked_on_top_terms

		/**
		 * Update admin classes
		 *
		 * @param array $classes
		 *
		 * @return array
		 */
		public function admin_body_class( $classes ) {
			$admin_helpers = Tribe__Events__Admin__Helpers::instance();
			if ( $admin_helpers->is_screen( 'settings_page_tribe-settings' ) || $admin_helpers->is_post_type_screen() ) {
				$classes .= ' events-cal ';
			}

			return $classes;
		}

		/**
		 * Add admin scripts and styles
		 *
		 * @return void
		 */
		public function add_admin_assets() {
			$admin_helpers = Tribe__Events__Admin__Helpers::instance();

			// setup plugin resources & 3rd party vendor urls
			$vendor_url    = trailingslashit( $this->pluginUrl ) . 'vendor/';

			// admin stylesheet - only load admin stylesheet when on Tribe pages
			if ( $admin_helpers->is_screen() ) {
				wp_enqueue_style( self::POSTTYPE . '-admin', tribe_events_resource_url( 'events-admin.css' ), array(), apply_filters( 'tribe_events_css_version', self::VERSION ) );
			}

			// settings screen
			if ( $admin_helpers->is_screen( 'settings_page_tribe-settings' ) ) {

				// chosen
				Tribe__Events__Template_Factory::asset_package( 'chosen' );

				// JS admin
				Tribe__Events__Template_Factory::asset_package( 'admin' );

				// JS settings
				Tribe__Events__Template_Factory::asset_package( 'settings' );

				wp_enqueue_script( 'thickbox' );
				wp_enqueue_style( 'thickbox' );

				// hook for other plugins
				do_action( 'tribe_settings_enqueue' );
			}

			if ( $admin_helpers->is_screen( 'widgets' ) ) {
				Tribe__Events__Template_Factory::asset_package( 'chosen' );
			}

			// events, organizer, or venue editing
			if ( $admin_helpers->is_post_type_screen() ) {

				// chosen
				Tribe__Events__Template_Factory::asset_package( 'chosen' );

				// select 2
				Tribe__Events__Template_Factory::asset_package( 'select2' );

				// smoothness
				Tribe__Events__Template_Factory::asset_package( 'smoothness' );

				// date picker
				Tribe__Events__Template_Factory::asset_package( 'datepicker' );

				// dialog
				Tribe__Events__Template_Factory::asset_package( 'dialog' );

				// UI admin
				Tribe__Events__Template_Factory::asset_package( 'admin-ui' );

				// JS admin
				Tribe__Events__Template_Factory::asset_package( 'admin' );

				// ecp placeholders
				Tribe__Events__Template_Factory::asset_package( 'ecp-plugins' );

				if ( $admin_helpers->is_post_type_screen( self::POSTTYPE ) ){
					add_action( 'admin_footer', array( $this, 'printLocalizedAdmin' ) );
					// hook for other plugins
					do_action( 'tribe_events_enqueue' );
				} elseif ( $admin_helpers->is_post_type_screen( self::VENUE_POST_TYPE ) ){
					// hook for other plugins
					do_action( 'tribe_venues_enqueue' );
				} elseif ( $admin_helpers->is_post_type_screen( self::ORGANIZER_POST_TYPE ) ){
					do_action( 'tribe_organizers_enqueue' );
				}
			}
		}

		/**
		 * Modify the post type args to set Dashicon if we're in WP 3.8+
		 *
		 * @return array post type args
		 **/
		public function setDashicon( $postTypeArgs ) {
			global $wp_version;

			if ( version_compare( $wp_version, 3.8 ) >= 0 ) {
				$postTypeArgs['menu_icon'] = 'dashicons-calendar';
			}

			return $postTypeArgs;

		}

		/**
		 * Localize admin
		 *
		 * @return array
		 */
		public function localizeAdmin() {
			$bits = array(
				'dayNames'        => $this->daysOfWeek,
				'dayNamesShort'   => $this->daysOfWeekShort,
				'dayNamesMin'     => $this->daysOfWeekMin,
				'monthNames'      => array_values( $this->monthNames() ),
				'monthNamesShort' => array_values( $this->monthNames( true ) ),
				'nextText'        => __( 'Next', 'the-events-calendar' ),
				'prevText'        => __( 'Prev', 'the-events-calendar' ),
				'currentText'     => __( 'Today', 'the-events-calendar' ),
				'closeText'       => __( 'Done', 'the-events-calendar' ),
			);

			return $bits;
		}

		/**
		 * Output localized admin javascript
		 *
		 * @return void
		 */
		public function printLocalizedAdmin() {
			wp_localize_script( 'tribe-events-admin', 'TEC', $this->localizeAdmin() );
		}

		/**
		 * Get all options for the Events Calendar
		 *
		 * @return array of options
		 */
		public static function getOptions() {
			$options = get_option( self::OPTIONNAME, array() );
			if ( has_filter( 'tribe_get_options' ) ) {
				_deprecated_function( 'tribe_get_options', '3.10', 'option_' . self::OPTIONNAME );
				$options = apply_filters( 'tribe_get_options', $options );
			}
			return $options;
		}

		/**
		 * Get value for a specific option
		 *
		 * @param string $optionName name of option
		 * @param string $default    default value
		 *
		 * @return mixed results of option query
		 */
		public static function getOption( $optionName, $default = '' ) {
			if ( ! $optionName ) {
				return null;
			}
			$options = self::getOptions();

			$option = $default;
			if ( isset( $options[ $optionName ] ) ) {
				$option = $options[ $optionName ];
			} elseif ( is_multisite() && isset( self::$tribeEventsMuDefaults ) && is_array( self::$tribeEventsMuDefaults ) && in_array( $optionName, array_keys( self::$tribeEventsMuDefaults ) ) ) {
				$option = self::$tribeEventsMuDefaults[ $optionName ];
			}

			return apply_filters( 'tribe_get_single_option', $option, $default, $optionName );
		}

		/**
		 * Saves the options for the plugin
		 *
		 * @param array $options formatted the same as from getOptions()
		 * @param bool  $apply_filters
		 *
		 * @return void
		 */
		public function setOptions( $options, $apply_filters = true ) {
			if ( ! is_array( $options ) ) {
				return;
			}
			if ( $apply_filters == true ) {
				$options = apply_filters( 'tribe-events-save-options', $options );
			}
			update_option( self::OPTIONNAME, $options );
			}

		/**
		 * Set an option
		 *
		 * @param string $name
		 * @param mixed  $value
		 *
		 * @return void
		 */
		public function setOption( $name, $value ) {
			$newOption        = array();
			$newOption[ $name ] = $value;
			$options          = self::getOptions();
			self::setOptions( wp_parse_args( $newOption, $options ) );
		}

		/**
		 * Get all network options for the Events Calendar
		 *
		 * @return array of options
		 * @TODO add force option, implement in setNetworkOptions
		 */
		public static function getNetworkOptions() {
			if ( ! isset( self::$networkOptions ) ) {
				$options              = get_site_option( self::OPTIONNAMENETWORK, array() );
				self::$networkOptions = apply_filters( 'tribe_get_network_options', $options );
			}

			return self::$networkOptions;
		}

		/**
		 * Get value for a specific network option
		 *
		 * @param string $optionName name of option
		 * @param string $default    default value
		 *
		 * @return mixed results of option query
		 */
		public function getNetworkOption( $optionName, $default = '' ) {
			if ( ! $optionName ) {
				return null;
			}

			if ( ! isset( self::$networkOptions ) ) {
				self::getNetworkOptions();
			}

			if ( isset( self::$networkOptions[ $optionName ] ) ) {
				$option = self::$networkOptions[ $optionName ];
			} else {
				$option = $default;
			}

			return apply_filters( 'tribe_get_single_network_option', $option, $default );
		}

		/**
		 * Saves the network options for the plugin
		 *
		 * @param array $options formatted the same as from getOptions()
		 * @param bool  $apply_filters
		 *
		 * @return void
		 */
		public function setNetworkOptions( $options, $apply_filters = true ) {
			if ( ! is_array( $options ) ) {
				return;
			}
			if ( $apply_filters == true ) {
				$options = apply_filters( 'tribe-events-save-network-options', $options );
			}

			// @TODO use getNetworkOptions + force
			if ( update_site_option( self::OPTIONNAMENETWORK, $options ) ) {
				self::$networkOptions = apply_filters( 'tribe_get_network_options', $options );
			} else {
				self::$networkOptions = self::getNetworkOptions();
			}
		}

		/**
		 * Add the network admin options page
		 *
		 * @return void
		 */
		public function addNetworkOptionsPage() {
			$tribe_settings = Tribe__Events__Settings::instance();
			add_submenu_page(
				'settings.php', $this->pluginName, $this->pluginName, 'manage_network_options', 'the-events-calendar', array(
					$tribe_settings,
					'generatePage',
				)
			);
		}

		/**
		 * Render network admin options view
		 *
		 * @return void
		 */
		public function doNetworkSettingTab() {
			include_once( $this->pluginPath . 'src/admin-views/tribe-options-network.php' );

			new Tribe__Events__Settings_Tab( 'network', __( 'Network', 'the-events-calendar' ), $networkTab );
		}

		/**
		 * Get the post types that are associated with TEC.
		 *
		 * @return array The post types associated with this plugin
		 */
		public static function getPostTypes() {
			return apply_filters(
				'tribe_events_post_types', array(
					self::POSTTYPE,
					self::ORGANIZER_POST_TYPE,
					self::VENUE_POST_TYPE,
				)
			);
		}

		/**
		 * An event can have one or more start dates. This gives
		 * the earliest of those.
		 *
		 * @param int $post_id
		 *
		 * @return string The date string for the earliest occurrence of the event
		 */
		public static function get_series_start_date( $post_id ) {
			if ( function_exists( 'tribe_get_recurrence_start_dates' ) ) {
				$start_dates = tribe_get_recurrence_start_dates( $post_id );

				return reset( $start_dates );
			} else {
				return get_post_meta( $post_id, '_EventStartDate', true );
			}
		}

		/**
		 * Save hidden tabs
		 *
		 * @return void
		 * @TODO move somewhere else
		 */
		public function saveAllTabsHidden() {
			$all_tabs_keys = array_keys( apply_filters( 'tribe_settings_all_tabs', array() ) );

			$network_options = (array) get_site_option( self::OPTIONNAMENETWORK );

			if ( isset( $_POST['hideSettingsTabs'] ) && $_POST['hideSettingsTabs'] == $all_tabs_keys ) {
				$network_options['allSettingsTabsHidden'] = '1';
			} else {
				$network_options['allSettingsTabsHidden'] = '0';
			}

			$this->setNetworkOptions( $network_options );
		}

		/**
		 * Clean up trashed venues
		 *
		 * @param int $postId
		 *
		 * @return void
		 */
		public function cleanupPostVenues( $postId ) {
			$this->removeDeletedPostTypeAssociation( '_EventVenueID', $postId );
		}

		/**
		 * Clean up trashed organizers.
		 *
		 * @param int $postId
		 *
		 * @return void
		 */
		public function cleanupPostOrganizers( $postId ) {
			$this->removeDeletedPostTypeAssociation( '_EventOrganizerID', $postId );
		}

		/**
		 * Clean up trashed venues or organizers.
		 *
		 * @param string $key
		 * @param int    $postId
		 *
		 * @return void
		 */
		protected function removeDeletedPostTypeAssociation( $key, $postId ) {
			$the_query = new WP_Query( array(
				'meta_key'   => $key,
				'meta_value' => $postId,
				'post_type'  => self::POSTTYPE,
			) );

			while ( $the_query->have_posts() ): $the_query->the_post();
				delete_post_meta( get_the_ID(), $key );
			endwhile;

			wp_reset_postdata();
		}

		/**
		 * Truncate a given string.
		 *
		 * @param string $text           The text to truncate.
		 * @param int    $excerpt_length How long you want it to be truncated to.
		 *
		 * @return string The truncated text.
		 */
		public function truncate( $text, $excerpt_length = 44 ) {

			$text = apply_filters( 'the_content', $text );
			$text = str_replace( ']]>', ']]&gt;', $text );
			$text = strip_tags( $text );

			$words = explode( ' ', $text, $excerpt_length + 1 );
			if ( count( $words ) > $excerpt_length ) {
				array_pop( $words );
				$text = implode( ' ', $words );
				$text = rtrim( $text );
				$text .= '&hellip;';
			}

			return $text;
		}

		/**
		 * Load the text domain.
		 *
		 * @return void
		 */
		public function loadTextDomain() {
			load_plugin_textdomain( 'the-events-calendar', false, $this->pluginDir . 'lang/' );

			// Setup the l10n strings
			$this->setup_l10n_strings();
		}

		/**
		 * Load asset packages.
		 *
		 * @return void
		 */
		public function loadStyle() {
			if ( tribe_is_event_query() || tribe_is_event_organizer() || tribe_is_event_venue() ) {

				// jquery-resize
				Tribe__Events__Template_Factory::asset_package( 'jquery-resize' );

				// smoothness
				Tribe__Events__Template_Factory::asset_package( 'smoothness' );

				// Tribe Calendar JS
				Tribe__Events__Template_Factory::asset_package( 'calendar-script' );

				Tribe__Events__Template_Factory::asset_package( 'events-css' );
			} else {
				if ( is_active_widget( false, false, 'tribe-events-list-widget' ) ) {

					Tribe__Events__Template_Factory::asset_package( 'events-css' );

				}
			}
		}

		/**
		 * Initializes admin-specific items for the events admin list dashboard page. Hooked to the
		 * current_screen action
		 *
		 * @param WP_Screen $screen WP Admin screen object for the current page
		 */
		public function init_admin_list_screen( $screen ) {
			if ( 'edit' !== $screen->base ) {
				return;
			}

			if ( self::POSTTYPE !== $screen->post_type ) {
				return;
			}

			Tribe__Events__Admin_List::init();
		}

		/**
		 * Set the displaying class property.
		 *
		 * @return void
		 */
		public function setDisplay() {
			if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {
				$this->displaying = 'admin';
			} else {
				global $wp_query;
				if ( $wp_query && $wp_query->is_main_query() && ! empty( $wp_query->tribe_is_event_query ) ) {
					$this->displaying = isset( $wp_query->query_vars['eventDisplay'] ) ? $wp_query->query_vars['eventDisplay'] : tribe_get_option( 'viewOption', 'list' );

					if ( is_single() && $this->displaying != 'all' ) {
						$this->displaying = 'single-event';
					}
				}
			}
		}

		/**
		 * Returns the default view, providing a fallback if the default is no longer availble.
		 *
		 * This can be useful is for instance a view added by another plugin (such as PRO) is
		 * stored as the default but can no longer be generated due to the plugin being deactivated.
		 *
		 * @return string
		 */
		public function default_view() {
			// Compare the stored default view option to the list of available views
			$default         = $this->getOption( 'viewOption', 'month' );
			$available_views = (array) apply_filters( 'tribe-events-bar-views', array(), false );

			foreach ( $available_views as $view ) {
				if ( $default === $view['displaying'] ) {
					return $default;
				}
			}

			// If the stored option is no longer available, pick the first available one instead
			$first_view = array_shift( $available_views );
			$view       = $first_view['displaying'];

			// Update the saved option
			$this->setOption( 'viewOption', $view );

			return $view;
		}

		protected function setup_l10n_strings() {
			global $wp_locale;

			// Localize month names
			$this->monthsFull = array(
				'January'   => $wp_locale->get_month( '01' ),
				'February'  => $wp_locale->get_month( '02' ),
				'March'     => $wp_locale->get_month( '03' ),
				'April'     => $wp_locale->get_month( '04' ),
				'May'       => $wp_locale->get_month( '05' ),
				'June'      => $wp_locale->get_month( '06' ),
				'July'      => $wp_locale->get_month( '07' ),
				'August'    => $wp_locale->get_month( '08' ),
				'September' => $wp_locale->get_month( '09' ),
				'October'   => $wp_locale->get_month( '10' ),
				'November'  => $wp_locale->get_month( '11' ),
				'December'  => $wp_locale->get_month( '12' ),
			);

			// yes, it's awkward. easier this way than changing logic elsewhere.
			$this->monthsShort = $months = array(
				'Jan' => $wp_locale->get_month_abbrev( $wp_locale->get_month( '01' ) ),
				'Feb' => $wp_locale->get_month_abbrev( $wp_locale->get_month( '02' ) ),
				'Mar' => $wp_locale->get_month_abbrev( $wp_locale->get_month( '03' ) ),
				'Apr' => $wp_locale->get_month_abbrev( $wp_locale->get_month( '04' ) ),
				'May' => $wp_locale->get_month_abbrev( $wp_locale->get_month( '05' ) ),
				'Jun' => $wp_locale->get_month_abbrev( $wp_locale->get_month( '06' ) ),
				'Jul' => $wp_locale->get_month_abbrev( $wp_locale->get_month( '07' ) ),
				'Aug' => $wp_locale->get_month_abbrev( $wp_locale->get_month( '08' ) ),
				'Sep' => $wp_locale->get_month_abbrev( $wp_locale->get_month( '09' ) ),
				'Oct' => $wp_locale->get_month_abbrev( $wp_locale->get_month( '10' ) ),
				'Nov' => $wp_locale->get_month_abbrev( $wp_locale->get_month( '11' ) ),
				'Dec' => $wp_locale->get_month_abbrev( $wp_locale->get_month( '12' ) ),
			);

			// Get the localized weekday names
			for ( $i = 0; $i <= 6; $i ++ ) {
				$day = $wp_locale->get_weekday( $i );
				$this->daysOfWeek[ $i ] = $day;
				$this->daysOfWeekShort[ $i ] = $wp_locale->get_weekday_abbrev( $day );
				$this->daysOfWeekMin[ $i ] = $wp_locale->get_weekday_initial( $day );
			}

			// Setup the Strings for Rewrite Translations
			__( 'tag', 'the-events-calendar' );
			__( 'category', 'the-events-calendar' );
			__( 'page', 'the-events-calendar' );
			__( 'event', 'the-events-calendar' );
			__( 'events', 'the-events-calendar' );
			__( 'all', 'the-events-calendar' );
		}

		/**
		 * Helper method to return an array of translated month names or short month names
		 *
		 * @param bool $short
		 *
		 * @return array Translated month names
		 */
		public function monthNames( $short = false ) {
			if ( $short ) {
				return $this->monthsShort;
			}

			return $this->monthsFull;
		}

		/**
		 * Flush rewrite rules to support custom links
		 *
		 * @todo This is only registering the events post type, not the meta types
		 *
		 * @link http://codex.wordpress.org/Custom_Queries#Permalinks_for_Custom_Archives
		 */
		public static function flushRewriteRules() {

			$tec = self::instance();

			// reregister custom post type to make sure slugs are updated
			$tec->postTypeArgs['rewrite']['slug'] = sanitize_title( $tec->getRewriteSlugSingular() );
			register_post_type( self::POSTTYPE, apply_filters( 'tribe_events_register_event_type_args', $tec->postTypeArgs ) );

			add_action( 'shutdown', 'flush_rewrite_rules' );
		}

		/**
		 * If a themer usees get_post_type_archive_link() to find the event archive URL, this
		 * ensures they get the correct result.
		 *
		 * @param  string $link
		 * @param  string $post_type
		 * @return string
		 */
		public function event_archive_link( $link, $post_type ) {
			return ( self::POSTTYPE === $post_type )
				? tribe_get_events_link()
				: $link;
		}

		/**
		 * Adds the event specific query vars to WordPress
		 *
		 * @param array $qvars
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
			$qvars[] = self::TAXONOMY;

			return $qvars;
		}

		/**
		 * Get all possible translations for a String based on the given Languages and Domains
		 *
		 * WARNING: This function is slow because it deals with files, so don't overuse it!
		 *
		 * @todo Include support for the `load_theme_textdomain` + `load_muplugin_textdomain`
		 *
		 * @param  array  $strings          An array of strings (required)
		 * @param  array  $languages        Which l10n to fetch the string (required)
		 * @param  array  $domains          Possible Domains to re-load
		 * @param  string $default_language The default language to avoid re-doing that
		 *
		 * @return array                    A multi level array with the possible translations for the given strings
		 */
		public function get_i18n_strings( $strings, $languages, $domains = array(), $default_language = 'en_US' ) {
			$domains = wp_parse_args( $domains, array(
				'default' => true, // Default doesn't need file path
				'the-events-calendar' => $this->pluginDir . 'lang/',
			) );

			foreach ( $languages as $language ) {
				foreach ( (array) $domains as $domain => $file ) {
					// Configure the language
					$this->_locale = $language;
					add_filter( 'locale', array( $this, '_set_locale' ) );

					// Reload it with the correct language
					unload_textdomain( $domain );

					if ( 'default' === $domain ) {
						load_default_textdomain();
					} else {
						load_plugin_textdomain( $domain, false, $file );
					}

					// Loop on the strings the build the possible translations
					foreach ( $strings as $key => $value ) {
						$value = is_array( $value ) ? reset( $value ) : $value;
						if ( ! is_string( $value ) ) {
							continue;
						}

						// Make sure we have an Array
						$strings[ $key ] = (array) $strings[ $key ];

						// Grab the possible strings for Default and Any other domain
						if ( 'default' === $domain ) {
							$strings[ $key ][] = __( $value );
							$strings[ $key ][] = __( strtolower( $value ) );
							$strings[ $key ][] = __( ucfirst( $value ) );
						} else {
							$strings[ $key ][] = __( $value, $domain );
							$strings[ $key ][] = __( strtolower( $value ), $domain );
							$strings[ $key ][] = __( ucfirst( $value ), $domain );
						}
					}

					// Set back to the default language
					remove_filter( 'locale', array( $this, '_set_locale' ) );

					// Reload it with the correct language
					unload_textdomain( $domain );

					if ( 'default' === $domain ) {
						load_default_textdomain();
					} else {
						load_plugin_textdomain( $domain, false, $file );
					}
				}
			}

			// Prevent Empty Strings and Duplicates
			foreach ( $strings as $key => $value ) {
				$strings[ $key ] = array_filter( array_unique( array_map( 'sanitize_title_with_dashes', $value ) ) );
			}

			return $strings;
		}

		/**
		 * DO NOT USE THIS INTERNAL USE
		 * A way to quickly filter the locale based on a Local Class Variable
		 *
		 * @return string The Locale set on _locale
		 */
		public function _set_locale() {
			return empty( $this->_locale ) ? 'en_US' : $this->_locale;
		}

		/**
		 * Redirect the legacy past/upcoming view URLs to list
		 */
		public function redirect_past_upcoming_view_urls() {

			if ( strpos( $_SERVER['REQUEST_URI'], $this->getRewriteSlug() . '/' . $this->pastSlug ) !== false ) {
				wp_redirect( esc_url_raw( add_query_arg( array( 'tribe_event_display' => 'past' ), str_replace( '/' . $this->pastSlug . '/', '/' . $this->listSlug . '/', $_SERVER['REQUEST_URI'] ) ) ) );
				die;
			} elseif ( strpos( $_SERVER['REQUEST_URI'], $this->getRewriteSlug() . '/' . $this->upcomingSlug ) !== false ) {
				wp_redirect( str_replace( '/' . $this->upcomingSlug . '/', '/' . $this->listSlug . '/', $_SERVER['REQUEST_URI'] ) );
				die;
			}

		}

		/**
		 * Returns various internal events-related URLs
		 *
		 * @param string        $type      type of link. See switch statement for types.
		 * @param string        $secondary for $type = month, pass a YYYY-MM string for a specific month's URL
		 *                                 for $type = week, pass a Week # string for a specific week's URL
		 * @param int|bool|null $term
		 *
		 * @return string The link.
		 */
		public function getLink( $type = 'home', $secondary = false, $term = null ) {
			// if permalinks are off or user doesn't want them: ugly.
			if ( '' === get_option( 'permalink_structure' ) ) {
				return esc_url_raw( $this->uglyLink( $type, $secondary ) );
			}

			// account for semi-pretty permalinks
			if ( false !== strpos( get_option( 'permalink_structure' ), 'index.php' ) ) {
				$event_url = home_url( '/index.php/' );
			} else {
				$event_url = home_url( '/' );
			}

			// URL Arguments on home_url() pre-check
			$url_query = @parse_url( $event_url, PHP_URL_QUERY );
			$url_args = wp_parse_args( $url_query, array() );

			// Remove the "args"
			if ( ! empty( $url_query ) ) {
				$event_url = str_replace( '?' . $url_query, '', $event_url );
			}

			// Append Events structure
			$event_url .= trailingslashit( sanitize_title( $this->getOption( 'eventsSlug', 'events' ) ) );

			// if we're on an Event Cat, show the cat link, except for home and days.
			if ( $type !== 'home' && is_tax( self::TAXONOMY ) && $term !== false && ! is_numeric( $term ) ) {
				$term_link = get_term_link( get_query_var( 'term' ), self::TAXONOMY );
				if ( ! is_wp_error( $term_link ) ) {
					$event_url = trailingslashit( $term_link );
				}
			} else {
				if ( $term ) {
					$term_link = get_term_link( (int) $term, self::TAXONOMY );
					if ( ! is_wp_error( $term_link ) ) {
						$event_url = trailingslashit( $term_link );
					}
				}
			}

			switch ( $type ) {
				case 'home':
					$event_url = trailingslashit( esc_url_raw( $event_url ) );
					break;
				case 'month':
					if ( $secondary ) {
						$event_url = trailingslashit( esc_url_raw( $event_url . $secondary ) );
					} else {
						$event_url = trailingslashit( esc_url_raw( $event_url . $this->monthSlug ) );
					}
					break;
				case 'list':
					$event_url = trailingslashit( esc_url_raw( $event_url . $this->listSlug ) );
					break;
				case 'upcoming':
					$event_url = trailingslashit( esc_url_raw( $event_url . $this->listSlug ) );
					break;
				case 'past':
					$event_url = esc_url_raw( add_query_arg( 'tribe_event_display', 'past', trailingslashit( $event_url . $this->listSlug ) ) );
					break;
				case 'dropdown':
					$event_url = esc_url_raw( $event_url );
					break;
				case 'single':
					global $post;
					$p         = $secondary ? $secondary : $post;
					$link      = trailingslashit( get_permalink( $p ) );
					$event_url = trailingslashit( esc_url_raw( $link ) );
					break;
				case 'day':
					if ( empty( $secondary ) ) {
						$secondary = $this->todaySlug;
					} else {
						$secondary = tribe_event_format_date( $secondary, false, Tribe__Events__Date_Utils::DBDATEFORMAT );
					}
					$event_url = trailingslashit( esc_url_raw( $event_url . $secondary ) );
					break;
				default:
					$event_url = esc_url_raw( $event_url );
					break;
			}

			// Filter get link
			$event_url = apply_filters( 'tribe_events_get_link', $event_url, $type, $secondary, $term, $url_args );

			// @todo deprecate on 4.2
			$event_url = apply_filters( 'tribe_events_getLink', $event_url, $type, $secondary, $term, $url_args );

			// Add the Arguments back
			$event_url = add_query_arg( $url_args, $event_url );

			return $event_url;
		}

		/**
		 * If pretty perms are off, get the ugly link.
		 *
		 * @param string $type      The type of link requested.
		 * @param bool|string       $secondary Some secondary data for the link.
		 *
		 * @return string The ugly link.
		 */
		public function uglyLink( $type = 'home', $secondary = false ) {

			$eventUrl = add_query_arg( 'post_type', self::POSTTYPE, home_url() );

			// if we're on an Event Cat, show the cat link, except for home.
			if ( $type !== 'home' && is_tax( self::TAXONOMY ) ) {
				$eventUrl = add_query_arg( self::TAXONOMY, get_query_var( 'term' ), $eventUrl );
			}

			switch ( $type ) {
				case 'day':
					$eventUrl = add_query_arg( array( 'eventDisplay' => $type ), $eventUrl );
					if ( $secondary ) {
						$eventUrl = add_query_arg( array( 'eventDate' => $secondary ), $eventUrl );
					}
					break;
				case 'week':
				case 'month':
					$eventUrl = add_query_arg( array( 'eventDisplay' => $type ), $eventUrl );
					if ( is_string( $secondary ) ) {
						$eventUrl = add_query_arg( array( 'eventDate' => $secondary ), $eventUrl );
					} elseif ( is_array( $secondary ) ) {
						$eventUrl = add_query_arg( $secondary, $eventUrl );
					}
					break;
				case 'list':
				case 'past':
				case 'upcoming':
					$eventUrl = add_query_arg( array( 'eventDisplay' => $type ), $eventUrl );
					break;
				case 'dropdown':
					$dropdown = add_query_arg( array( 'eventDisplay' => 'month', 'eventDate' => ' ' ), $eventUrl );
					$eventUrl = rtrim( $dropdown ); // tricksy
					break;
				case 'single':
					global $post;
					$p        = $secondary ? $secondary : $post;
					$eventUrl = get_permalink( $p );
					break;
				case 'home':
				default:
					break;
			}

			return apply_filters( 'tribe_events_ugly_link', $eventUrl, $type, $secondary );
		}

		/**
		 * Returns the GCal export link for a given event id.
		 *
		 * @param int $postId The post id requested.
		 *
		 * @return string The URL for the GCal export link.
		 */
		public function googleCalendarLink( $postId = null ) {
			global $post;
			$tribeEvents = self::instance();

			if ( $postId === null || ! is_numeric( $postId ) ) {
				$postId = $post->ID;
			}
			// protecting for reccuring because the post object will have the start/end date available
			$start_date = isset( $post->EventStartDate )
				? strtotime( $post->EventStartDate )
				: strtotime( get_post_meta( $postId, '_EventStartDate', true ) );
			$end_date   = isset( $post->EventEndDate )
				? strtotime( $post->EventEndDate . ( get_post_meta( $postId, '_EventAllDay', true ) ? ' + 1 day' : '' ) )
				: strtotime( get_post_meta( $postId, '_EventEndDate', true ) . ( get_post_meta( $postId, '_EventAllDay', true ) ? ' + 1 day' : '' ) );

			$dates    = ( get_post_meta( $postId, '_EventAllDay', true ) ) ? date( 'Ymd', $start_date ) . '/' . date( 'Ymd', $end_date ) : date( 'Ymd', $start_date ) . 'T' . date( 'Hi00', $start_date ) . '/' . date( 'Ymd', $end_date ) . 'T' . date( 'Hi00', $end_date );
			$location = trim( $tribeEvents->fullAddressString( $postId ) );
			$base_url = 'http://www.google.com/calendar/event';

			$event_details = apply_filters( 'the_content', get_the_content() );

 			// Hack: Add space after paragraph
			// Normally Google Cal understands the newline character %0a
			// And that character will automatically replace newlines on urlencode()
			$event_details = str_replace ( '</p>', '</p> ', $event_details );

			$event_details = strip_tags( $event_details );

			//Truncate Event Description and add permalink if greater than 996 characters
			if ( strlen( $event_details ) > 996 ) {

				$event_url     = get_permalink();
				$event_details = substr( $event_details, 0, 996 );

				//Only add the permalink if it's shorter than 900 characters, so we don't exceed the browser's URL limits
				if ( strlen( $event_url ) < 900 ) {
					$event_details .= sprintf( ' (View Full %1$s Description Here: %2$s)', $this->singular_event_label, $event_url );
				}
			}

			$params = array(
				'action'   => 'TEMPLATE',
				'text'     => urlencode( strip_tags( $post->post_title ) ),
				'dates'    => $dates,
				'details'  => urlencode( $event_details ),
				'location' => urlencode( $location ),
				'sprop'    => get_option( 'blogname' ),
				'trp'      => 'false',
				'sprop'    => 'website:' . home_url(),
			);
			$params = apply_filters( 'tribe_google_calendar_parameters', $params, $postId );
			$url    = add_query_arg( $params, $base_url );

			return $url;
		}

		/**
		* Custom Escape for gCal Description to keep spacing characters in the url
		*
		* @return santized url
		*/
		public function esc_gcal_url( $url ) {
			$url = str_replace( '%0A', 'TRIBE-GCAL-LINEBREAK', $url );
			$url = esc_url( $url );
			$url = str_replace( 'TRIBE-GCAL-LINEBREAK', '%0A', $url );
			return $url;
		}

		/**
		 * Returns a link to google maps for the given event. This link can be filtered
		 * using the tribe_events_google_map_link hook.
		 *
		 * @param int|null $post_id
		 *
		 * @return string a fully qualified link to http://maps.google.com/ for this event
		 */
		public function googleMapLink( $post_id = null ) {
			if ( $post_id === null || ! is_numeric( $post_id ) ) {
				global $post;
				$post_id = $post->ID;
			}

			$locationMetaSuffixes = array( 'address', 'city', 'region', 'zip', 'country' );
			$to_encode = '';
			$url = '';

			foreach ( $locationMetaSuffixes as $val ) {
				$metaVal = call_user_func( 'tribe_get_' . $val, $post_id );
				if ( $metaVal ) {
					$to_encode .= $metaVal . ' ';
				}
			}

			if ( $to_encode ) {
				$url = 'http://maps.google.com/maps?f=q&amp;source=s_q&amp;hl=en&amp;geocode=&amp;q=' . urlencode( trim( $to_encode ) );
			}

			return apply_filters( 'tribe_events_google_map_link', $url, $post_id );
		}

		/**
		 *  Returns the full address of an event along with HTML markup.  It
		 *  loads the full-address template to generate the HTML
		 */
		public function fullAddress( $post_id = null, $includeVenueName = false ) {
			global $post;
			if ( ! is_null( $post_id ) ) {
				$tmp_post = $post;
				$post     = get_post( $post_id );
			}
			ob_start();
			tribe_get_template_part( 'modules/address' );
			$address = ob_get_contents();
			ob_end_clean();
			if ( ! empty( $tmp_post ) ) {
				$post = $tmp_post;
			}

			return $address;
		}

		/**
		 *  Returns a string version of the full address of an event
		 *
		 * @param int|WP_Post The post object or post id.
		 *
		 * @return string The event's address.
		 */
		public function fullAddressString( $postId = null ) {
			$address = '';
			if ( tribe_get_address( $postId ) ) {
				$address .= tribe_get_address( $postId );
			}

			if ( tribe_get_city( $postId ) ) {
				if ( $address != '' ) {
					$address .= ', ';
				}
				$address .= tribe_get_city( $postId );
			}

			if ( tribe_get_region( $postId ) ) {
				if ( $address != '' ) {
					$address .= ', ';
				}
				$address .= tribe_get_region( $postId );
			}

			if ( tribe_get_zip( $postId ) ) {
				if ( $address != '' ) {
					$address .= ', ';
				}
				$address .= tribe_get_zip( $postId );
			}

			if ( tribe_get_country( $postId ) ) {
				if ( $address != '' ) {
					$address .= ', ';
				}
				$address .= tribe_get_country( $postId );
			}

			return $address;
		}

		/**
		 * plugin activation callback
		 * @see register_deactivation_hook()
		 *
		 * @param bool $network_deactivating
		 */
		public static function activate() {
			self::flushRewriteRules();

			if ( ! is_network_admin() && ! isset( $_GET['activate-multi'] ) ) {
				set_transient( '_tribe_events_activation_redirect', 1, 30 );
			}
		}

		/**
		 * plugin deactivation callback
		 * @see register_deactivation_hook()
		 *
		 * @param bool $network_deactivating
		 */
		public static function deactivate( $network_deactivating ) {
			require_once( dirname( __FILE__ ) . '/Deactivation.php' );
			$deactivation = new Tribe__Events__Deactivation( $network_deactivating );
			add_action( 'shutdown', array( $deactivation, 'deactivate' ) );
		}

		/**
		 * Converts a set of inputs to YYYY-MM-DD HH:MM:SS format for MySQL
		 *
		 * @param string $date     The date.
		 * @param int    $hour     The hour of the day.
		 * @param int    $minute   The minute of the hour.
		 * @param string $meridian "am" or "pm".
		 *
		 * @return string The date and time.
		 * @todo remove - unused
		 */
		public function dateToTimeStamp( $date, $hour, $minute, $meridian ) {
			_deprecated_function( __METHOD__, '3.11', 'strtotime' );
			if ( preg_match( '/(PM|pm)/', $meridian ) && $hour < 12 ) {
				$hour += '12';
			}
			if ( preg_match( '/(AM|am)/', $meridian ) && $hour == 12 ) {
				$hour = '00';
			}
			$date = $this->dateHelper( $date );

			return "$date $hour:$minute:00";
		}

		/**
		 * Ensures date follows proper YYYY-MM-DD format
		 * converts /, - and space chars to -
		 *
		 * @param string $date The date.
		 *
		 * @return string The cleaned-up date.
		 * @todo remove - unused
		 */
		protected function dateHelper( $date ) {
			_deprecated_function( __METHOD__, '3.11', 'date' );

			if ( $date == '' ) {
				return date( Tribe__Events__Date_Utils::DBDATEFORMAT );
			}

			$date = str_replace( array( '-', '/', ' ', ':', chr( 150 ), chr( 151 ), chr( 45 ) ), '-', $date );
			// ensure no extra bits are added
			list( $year, $month, $day ) = explode( '-', $date );

			if ( ! checkdate( $month, $day, $year ) ) {
				$date = date( Tribe__Events__Date_Utils::DBDATEFORMAT );
			} // today's date if error
			else {
				$date = $year . '-' . $month . '-' . $day;
			}

			return $date;
		}

		/**
		 * Adds an alias for get_post_meta so we can override empty values with defaults.
		 * If you need the raw unfiltered data, use get_post_meta directly.
		 * This is mainly for templates.
		 *
		 * @param int    $id     The post id.
		 * @param string $meta   The meta key.
		 * @param bool   $single Return as string? Or array?
		 *
		 * @return mixed The meta.
		 */
		public function getEventMeta( $id, $meta, $single = true ) {
			$value = get_post_meta( $id, $meta, $single );
			if ( $value === false ) {
				$method = str_replace( '_Event', '', $meta );
				$default = call_user_func( array( $this->defaults(), strtolower( $method ) ) );
				$value = apply_filters( 'filter_eventsDefault' . $method, $default );
			}
			return $value;
		}

		/**
		 * ensure only one venue or organizer is created during post preview
		 * subsequent previews will reuse that same post
		 *
		 * ensure that preview post is the one that's used when the event is published,
		 * unless we're publishing with a saved venue
		 *
		 * @param $post_type can be 'venue' or 'organizer'
		 */
		protected function manage_preview_metapost( $post_type, $event_id ) {

			if ( ! in_array( $post_type, array( 'venue', 'organizer' ) ) ) {
				return;
			}

			$posttype        = ucfirst( $post_type );
			$posttype_id     = $posttype . 'ID';
			$meta_key        = '_preview_' . $post_type . '_id';
			$valid_post_id   = "tribe_get_{$post_type}_id";
			$create          = "create$posttype";
			$preview_post_id = get_post_meta( $event_id, $meta_key, true );
			$doing_preview   = $_REQUEST['wp-preview'] == 'dopreview' ? true : false;

			if ( empty( $_POST[ $posttype ][ $posttype_id ] ) ) {
				// the event is set to use a new metapost
				if ( $doing_preview ) {
					// we're previewing
					if ( $preview_post_id && $preview_post_id == $valid_post_id( $preview_post_id ) ) {
						// a preview post has been created and is valid, update that
						wp_update_post(
							array(
								'ID'         => $preview_post_id,
								'post_title' => $_POST[ $posttype ][ $posttype ],
							)
						);
					} else {
						// a preview post has not been created yet, or is not valid - create one and save the ID
						$preview_post_id = Tribe__Events__API::$create( $_POST[ $posttype ], 'draft' );
						update_post_meta( $event_id, $meta_key, $preview_post_id );
					}
				}

				if ( $preview_post_id ) {
					// set the preview post id as the event metapost id in the $_POST array
					// so Tribe__Events__API::saveEventVenue() doesn't make a new post
					$_POST[ $posttype ][ $posttype_id ] = (int) $preview_post_id;
				}
			} else {
				// we're using a saved metapost, discard any preview post
				if ( $preview_post_id ) {
					wp_delete_post( $preview_post_id );
					global $wpdb;
					$wpdb->query( "DELETE FROM $wpdb->postmeta WHERE `meta_key` = '$meta_key' AND `meta_value` = $preview_post_id" );
				}
			}
		}

		/**
		 * Adds / removes the event details as meta tags to the post.
		 *
		 * @param int     $postId
		 * @param WP_Post $post
		 *
		 * @return void
		 */
		public function addEventMeta( $postId, $post ) {

			// only continue if it's an event post
			if ( $post->post_type !== self::POSTTYPE || defined( 'DOING_AJAX' ) ) {
				return;
			}
			// don't do anything on autosave or auto-draft either or massupdates
			if ( wp_is_post_autosave( $postId ) || $post->post_status == 'auto-draft' || isset( $_GET['bulk_edit'] ) || ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'inline-save' ) ) {
				return;
			}

			// don't do anything on other wp_insert_post calls
			if ( isset( $_POST['post_ID'] ) && $postId != $_POST['post_ID'] ) {
				return;
			}

			if ( ! isset( $_POST['ecp_nonce'] ) ) {
				return;
			}

			if ( ! wp_verify_nonce( $_POST['ecp_nonce'], self::POSTTYPE ) ) {
				return;
			}

			if ( ! current_user_can( 'edit_tribe_events' ) ) {
				return;
			}

			// Remove this hook to avoid an infinite loop, because saveEventMeta calls wp_update_post when the post is set to always show in calendar
			remove_action( 'save_post', array( $this, 'addEventMeta' ), 15, 2 );

			$_POST['Organizer'] = isset( $_POST['organizer'] ) ? stripslashes_deep( $_POST['organizer'] ) : null;
			$_POST['Venue']     = isset( $_POST['venue'] ) ? stripslashes_deep( $_POST['venue'] ) : null;


			/**
			 * handle previewed venues and organizers
			 */
			$this->manage_preview_metapost( 'venue', $postId );
			$this->manage_preview_metapost( 'organizer', $postId );

			/**
			 * When we have a VenueID/OrganizerID, we just save the ID, because we're not
			 * editing the venue/organizer from within the event.
			 */
			$venue_pto = get_post_type_object( self::VENUE_POST_TYPE );
			if ( isset( $_POST['Venue']['VenueID'] ) && ! empty( $_POST['Venue']['VenueID'] ) ) {
				$_POST['Venue'] = array( 'VenueID' => intval( $_POST['Venue']['VenueID'] ) );
			} elseif (
				empty( $venue_pto->cap->create_posts )
				|| ! current_user_can( $venue_pto->cap->create_posts )
			) {
				$_POST['Venue'] = array();
			}

			$_POST['Organizer'] = $this->normalize_organizer_submission( $_POST['Organizer'] );


			Tribe__Events__API::saveEventMeta( $postId, $_POST, $post );

			// Add this hook back in
			add_action( 'save_post_' . self::POSTTYPE, array( $this, 'addEventMeta' ), 15, 2 );
		}

		public function normalize_organizer_submission( $submission ) {
			$organizer_pto = get_post_type_object( self::ORGANIZER_POST_TYPE );
			$organizers = array();
			if ( ! isset( $submission['OrganizerID'] ) ) {
				return $organizers; // not a valid submission
			}

			if ( is_array( $submission['OrganizerID'] ) ) {
				foreach ( $submission['OrganizerID'] as $key => $organizer_id ) {
					if ( ! empty( $organizer_id ) ) {
						$organizers[] = array( 'OrganizerID' => intval( $organizer_id ) );
					} elseif (
						! empty( $organizer_pto->cap->create_posts )
						&& current_user_can( $organizer_pto->cap->create_posts )
					) {
						$o = array();
						foreach ( array( 'Organizer', 'Phone', 'Website', 'Email' ) as $field_name ) {
							$o[ $field_name ] = isset( $submission[ $field_name ][ $key ] ) ? $submission[ $field_name ][ $key ] : '';
						}
						$organizers[] = $o;
					}
				}
				return $organizers;
			}

			// old style with single organizer fields
			if ( current_user_can( $organizer_pto->cap->create_posts ) ) {
				$o = array();
				foreach ( array( 'Organizer', 'Phone', 'Website', 'Email' ) as $field_name ) {
					$o[ $field_name ] = isset( $submission[ $field_name ] ) ? $submission[ $field_name ] : '';
				}
				$organizers[] = $o;
				$o[ $field_name ] = isset( $submission[ $field_name ] ) ? $submission[ $field_name ] : '';
			}
			return $organizers;
		}

		/**
		 * Intended to run when the save_post_tribe_events action is fired.
		 *
		 * At this point we know an event is being updated or created and, if the post is going to
		 * be visible, we can set up a further action to handle updating our record of the
		 * populated date range once the post meta containing the start and end date for the post
		 * has saved.
		 */
		public function maybe_update_known_range( $post_id ) {
			// If the event isn't going to be visible (perhaps it's been trashed) rebuild dates and bail
			if ( ! in_array( get_post_status( $post_id ), array( 'publish', 'private', 'protected' ) ) ) {
				$this->rebuild_known_range();
				return;
			}

			add_action( 'tribe_events_update_meta', array( $this, 'update_known_range' ) );
		}

		/**
		 * Intelligently updates our record of the earliest start date/latest event date in
		 * the system. If the existing earliest/latest values have not been superseded by the new post's
		 * start/end date then no update takes place.
		 *
		 * This is deliberately hooked into save_post, rather than save_post_tribe_events, to avoid issues
		 * where the removal/restoration of hooks within addEventMeta() etc might stop this method from
		 * actually being called (relates to a core WP bug).
		 */
		public function update_known_range( $object_id ) {

			$current_min = tribe_events_earliest_date();
			$current_max = tribe_events_latest_date();

			$event_start = tribe_get_start_date( $object_id, false, Tribe__Events__Date_Utils::DBDATETIMEFORMAT );
			$event_end   = tribe_get_end_date( $object_id, false, Tribe__Events__Date_Utils::DBDATETIMEFORMAT );

			if ( $current_min > $event_start ) {
				tribe_update_option( 'earliest_date', $event_start );
			}
			if ( $current_max < $event_end ) {
				tribe_update_option( 'latest_date', $event_end );
			}
		}

		/**
		 * Fires on delete_post and decides whether or not to rebuild our record or
		 * earliest/latest event dates (which will be done when deleted_post fires,
		 * so that the deleted event is removed from the db before we recalculate).
		 *
		 * @param $post_id
		 */
		public function maybe_rebuild_known_range( $post_id ) {
			if ( self::POSTTYPE === get_post_type( $post_id ) ) {
				add_action( 'deleted_post', array( $this, 'rebuild_known_range' ) );
			}
		}

		/**
		 * Determine the earliest start date and latest end date currently in the database
		 * and store those values for future use.
		 */
		public function rebuild_known_range() {
			global $wpdb;
			remove_action( 'deleted_post', array( $this, 'rebuild_known_range' ) );

			$earliest = strtotime(
				$wpdb->get_var(
					 $wpdb->prepare(
						  "
				SELECT MIN(meta_value) FROM $wpdb->postmeta
				JOIN $wpdb->posts ON post_id = ID
				WHERE meta_key = '_EventStartDate'
				AND post_type = '%s'
				AND post_status IN ('publish', 'private', 'protected')
			", self::POSTTYPE
					 )
				)
			);

			$latest = strtotime(
				$wpdb->get_var(
					 $wpdb->prepare(
						  "
				SELECT MAX(meta_value) FROM $wpdb->postmeta
				JOIN $wpdb->posts ON post_id = ID
				WHERE meta_key = '_EventEndDate'
				AND post_type = '%s'
				AND post_status IN ('publish', 'private', 'protected')
			", self::POSTTYPE
					 )
				)
			);

			if ( $earliest ) {
				tribe_update_option( 'earliest_date', date( Tribe__Events__Date_Utils::DBDATETIMEFORMAT, $earliest ) );
			}
			if ( $latest ) {
				tribe_update_option( 'latest_date', date( Tribe__Events__Date_Utils::DBDATETIMEFORMAT, $latest ) );
			}
		}

		/**
		 * Adds the '_<posttype>Origin' meta field for a newly inserted events-calendar post.
		 *
		 * @param int     $postId , the post ID
		 * @param WP_Post $post   , the post object
		 *
		 * @return void
		 */
		public function addPostOrigin( $postId, $post ) {
			// Only continue of the post being added is an event, venue, or organizer.
			if ( isset( $postId ) && isset( $post->post_type ) ) {
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
				$origin = get_post_meta( $postId, $post_type . 'Origin', true );
				if ( ! $origin ) {
					add_post_meta( $postId, $post_type . 'Origin', apply_filters( 'tribe-post-origin', 'events-calendar', $postId, $post ) );
				}
			}
		}

		/**
		 * Publishes associated venue/organizer when an event is published
		 *
		 * @param int     $postID , the post ID
		 * @param WP_Post $post   , the post object
		 *
		 * @return void
		 */
		public function publishAssociatedTypes( $postID, $post ) {

			// don't need to save the venue or organizer meta when we are just publishing
			remove_action( 'save_post_' . self::VENUE_POST_TYPE, array( $this, 'save_venue_data' ), 16, 2 );
			remove_action( 'save_post_' . self::ORGANIZER_POST_TYPE, array( $this, 'save_organizer_data' ), 16, 2 );

			// save venue and organizer info on first pass
			if ( isset( $post->post_status ) && $post->post_status == 'publish' ) {

				//get venue and organizer and publish them
				$pm = get_post_custom( $post->ID );

				do_action( 'log', 'publishing an event with a venue', 'tribe-events', $post );

				// save venue on first setup
				if ( ! empty( $pm['_EventVenueID'] ) ) {
					$venue_id = is_array( $pm['_EventVenueID'] ) ? current( $pm['_EventVenueID'] ) : $pm['_EventVenueID'];
					if ( $venue_id ) {
						do_action( 'log', 'event has a venue', 'tribe-events', $venue_id );
						$venue_post = get_post( $venue_id );
						if ( ! empty( $venue_post ) && $venue_post->post_status != 'publish' ) {
							do_action( 'log', 'venue post found', 'tribe-events', $venue_post );
							$venue_post->post_status = 'publish';
							wp_update_post( $venue_post );
							$did_save = true;
						}
					}
				}

				// save organizer on first setup
				if ( ! empty( $pm['_EventOrganizerID'] ) ) {
					$org_id = is_array( $pm['_EventOrganizerID'] ) ? current( $pm['_EventOrganizerID'] ) : $pm['_EventOrganizerID'];
					if ( $org_id ) {
						$org_post = get_post( $org_id );
						if ( ! empty( $org_post ) && $org_post->post_status != 'publish' ) {
							$org_post->post_status = 'publish';
							wp_update_post( $org_post );
							$did_save = true;
						}
					}
				}
			}

			// put the actions back
			add_action( 'save_post_' . self::VENUE_POST_TYPE, array( $this, 'save_venue_data' ), 16, 2 );
			add_action( 'save_post_' . self::ORGANIZER_POST_TYPE, array( $this, 'save_organizer_data' ), 16, 2 );

		}

		/**
		 * Make sure the venue meta gets saved
		 *
		 * @param int     $postID The venue id.
		 * @param WP_Post $post   The post object.
		 *
		 * @return null|void
		 */
		public function save_venue_data( $postID = null, $post = null ) {
			// was a venue submitted from the single venue post editor?
			if ( empty( $_POST['post_ID'] ) || $_POST['post_ID'] != $postID || empty( $_POST['venue'] ) ) {
				return;
			}

			// is the current user allowed to edit this venue?
			if ( ! current_user_can( 'edit_tribe_venue', $postID ) ) {
				return;
			}

			$data = stripslashes_deep( $_POST['venue'] );
			Tribe__Events__API::updateVenue( $postID, $data );
		}

		/**
		 * Get venue info.
		 *
		 * @param int $p          post id
		 * @param     $args
		 *
		 * @return WP_Query->posts || false
		 */
		public function get_venue_info( $p = null, $args = array() ) {
			$defaults = array(
				'post_type'            => self::VENUE_POST_TYPE,
				'nopaging'             => 1,
				'post_status'          => 'publish',
				'ignore_sticky_posts ' => 1,
				'orderby'              => 'title',
				'order'                => 'ASC',
				'p'                    => $p,
			);

			$args = wp_parse_args( $args, $defaults );
			$r    = new WP_Query( $args );
			if ( $r->have_posts() ) :
				return $r->posts;
			endif;

			return false;
		}

		/**
		 * Make sure the organizer meta gets saved
		 *
		 * @param int     $postID The organizer id.
		 * @param WP_Post $post   The post object.
		 *
		 * @return null|void
		 */
		public function save_organizer_data( $postID = null, $post = null ) {
			// was an organizer submitted from the single organizer post editor?
			if ( empty( $_POST['post_ID'] ) || $_POST['post_ID'] != $postID || empty( $_POST['organizer'] ) ) {
				return;
			}

			// is the current user allowed to edit this venue?
			if ( ! current_user_can( 'edit_tribe_organizer', $postID ) ) {
				return;
			}

			$data = stripslashes_deep( $_POST['organizer'] );
			Tribe__Events__API::updateOrganizer( $postID, $data );
		}

		/**
		 * Add a new Organizer
		 *
		 * @param      $data
		 * @param null $post
		 *
		 * @return int|WP_Error
		 */
		public function add_new_organizer( $data, $post = null ) {
			if ( $data['OrganizerID'] ) {
				return $data['OrganizerID'];
			}

			if ( $post->post_type == self::ORGANIZER_POST_TYPE && $post->ID ) {
				$data['OrganizerID'] = $post->ID;
			}

			//google map checkboxes
			$postdata = array(
				'post_title'  => $data['Organizer'],
				'post_type'   => self::ORGANIZER_POST_TYPE,
				'post_status' => 'publish',
				'ID'          => $data['OrganizerID'],
			);

			if ( isset( $data['OrganizerID'] ) && $data['OrganizerID'] != '0' ) {
				$organizer_id = $data['OrganizerID'];
				wp_update_post( array( 'post_title' => $data['Organizer'], 'ID' => $data['OrganizerID'] ) );
			} else {
				$organizer_id = wp_insert_post( $postdata, true );
			}

			if ( ! is_wp_error( $organizer_id ) ) {
				foreach ( $data as $key => $var ) {
					update_post_meta( $organizer_id, '_Organizer' . $key, $var );
				}

				return $organizer_id;
			}
		}

		/**
		 * Get Organizer info.
		 *
		 * @param int $p          post id
		 * @param     $args
		 *
		 * @return WP_Query->posts || false
		 */
		public function get_organizer_info( $p = null, $args = array() ) {
			$defaults = array(
				'post_type'            => self::ORGANIZER_POST_TYPE,
				'nopaging'             => 1,
				'post_status'          => 'publish',
				'ignore_sticky_posts ' => 1,
				'orderby'              => 'title',
				'order'                => 'ASC',
				'p'                    => $p,
			);

			$args = wp_parse_args( $args, $defaults );
			$r    = new WP_Query( $args );
			if ( $r->have_posts() ) :
				return $r->posts;
			endif;

			return false;
		}

		/**
		 * Generates the main events settings meta box used within the event editor to configure
		 * event dates, times and more.
		 *
		 * @param WP_Post $event
		 */
		public function EventsChooserBox( $event = null ) {
			new Tribe__Events__Admin__Event_Meta_Box( $event );
				}

		/**
		 * Adds a style chooser to the write post page
		 *
		 * @return void
		 */
		public function VenueMetaBox() {
			global $post;
			$options = '';
			$style   = '';
			$event    = $post;

			if ( $post->post_type == self::VENUE_POST_TYPE ) {

				if ( ( is_admin() && isset( $_GET['post'] ) && $_GET['post'] ) || ( ! is_admin() && isset( $event->ID ) ) ) {
					$saved = true;
				}

				foreach ( $this->venueTags as $tag ) {
					if ( $event->ID && isset( $saved ) && $saved ) { //if there is a post AND the post has been saved at least once.
						$$tag = esc_html( get_post_meta( $event->ID, $tag, true ) );
					} else {
						$cleaned_tag = str_replace( '_Venue', '', $tag );
						$$tag = call_user_func( array( $this->defaults(), $cleaned_tag ) );
					}
				}
			}

			?>
			<style type="text/css">
				#EventInfo {
					border: none;
				}
			</style>
			<div id='eventDetails' class="inside eventForm">
				<table cellspacing="0" cellpadding="0" id="EventInfo" class="VenueInfo">
					<?php
					$venue_meta_box_template = apply_filters( 'tribe_events_venue_meta_box_template', $this->pluginPath . 'src/admin-views/venue-meta-box.php' );
					if ( ! empty( $venue_meta_box_template ) ) {
						include( $venue_meta_box_template );
					}
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
			$style   = '';
			$postId  = $post->ID;
			$saved   = false;

			if ( $post->post_type == self::ORGANIZER_POST_TYPE ) {

				if ( ( is_admin() && isset( $_GET['post'] ) && $_GET['post'] ) || ( ! is_admin() && isset( $postId ) ) ) {
					$saved = true;
				}

				foreach ( $this->organizerTags as $tag ) {
					if ( $postId && $saved ) { //if there is a post AND the post has been saved at least once.
						$$tag = get_post_meta( $postId, $tag, true );
					}
				}
			}
			?>
			<style type="text/css">
				#EventInfo {
					border: none;
				}
			</style>
			<div id='eventDetails' class="inside eventForm">
				<table cellspacing="0" cellpadding="0" id="EventInfo" class="OrganizerInfo">
					<?php
					$hide_organizer_title = true;
					$organizer_meta_box_template = apply_filters( 'tribe_events_organizer_meta_box_template', $this->pluginPath . 'src/admin-views/organizer-meta-box.php' );
					if ( ! empty( $organizer_meta_box_template ) ) {
						include( $organizer_meta_box_template );
					}
					?>
				</table>
			</div>
		<?php
		}

		/**
		 * Handle ajax requests from admin form
		 *
		 * @return void
		 */
		public function ajax_form_validate() {
			if ( $_REQUEST['name'] && $_REQUEST['nonce'] && wp_verify_nonce( $_REQUEST['nonce'], 'tribe-validation-nonce' ) ) {
				if ( $_REQUEST['type'] == 'venue' ) {
					echo $this->verify_unique_name( $_REQUEST['name'], 'venue' );
					exit;
				} elseif ( $_REQUEST['type'] == 'organizer' ) {
					echo $this->verify_unique_name( $_REQUEST['name'], 'organizer' );
					exit;
				}
			}
		}

		/**
		 * Allow programmatic override of defaultValueReplace setting
		 *
		 * @return boolean
		 */
		public function defaultValueReplaceEnabled() {

			if ( ! is_admin() ) {
				return false;
			}

			return tribe_get_option( 'defaultValueReplace' );

		}

		/**
		 * Get the current default value strategy
		 * @return Tribe__Events__Default_Values
		 */
		public function defaults() {
			return $this->default_values;
		}

		/**
		 * Verify that a venue or organizer is unique
		 *
		 * @param string $name - name of venue or organizer
		 * @param string $type - post type (venue or organizer)
		 *
		 * @return boolean
		 */
		public function verify_unique_name( $name, $type ) {
			global $wpdb;
			$name = stripslashes( $name );
			if ( '' == $name ) {
				return 1;
			}
			if ( $type == 'venue' ) {
				$post_type = self::VENUE_POST_TYPE;
			} elseif ( $type == 'organizer' ) {
				$post_type = self::ORGANIZER_POST_TYPE;
			}
			// TODO update this verification to check all post_status <> 'trash'
			$results = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$wpdb->posts} WHERE post_type = %s && post_title = %s && post_status = 'publish'", $post_type, $name ) );

			return ( $results ) ? 0 : 1;
		}

		/**
		 * Given a date (YYYY-MM-DD), returns the first of the next month
		 * hat tip to Dan Bernadict for method cleanup
		 *
		 * @param string $date
		 *
		 * @return string Next month's date
		 * @throws OverflowException
		 */
		public function nextMonth( $date ) {
			if ( PHP_INT_SIZE <= 4 ) {
				if ( date( 'Y-m-d', strtotime( $date ) ) > '2037-11-30' ) {
					throw new OverflowException( __( 'Date out of range.', 'the-events-calendar' ) );
				}
			}

			// Create a new date object: a badly formed date can trigger an exception - in such
			// a scenario try again and default to the current time instead
			try {
			$date = new DateTime( $date );
			}
			catch ( Exception $e ) {
				$date = new DateTime;
			}

			// set date object to be the first of the month -- all months have this day!
			$date->setDate( $date->format( 'Y' ), $date->format( 'm' ), 1 );

			// add a month
			$date->modify( '+1 month' );

			// return the year-month
			return $date->format( 'Y-m' );
		}

		/**
		 * Given a date (YYYY-MM-DD), return the first of the previous month
		 * hat tip to Dan Bernadict for method cleanup
		 *
		 * @param string $date
		 *
		 * @return string Previous month's date
		 * @throws OverflowException
		 */
		public function previousMonth( $date ) {
			if ( PHP_INT_SIZE <= 4 ) {
				if ( date( 'Y-m-d', strtotime( $date ) ) < '1902-02-01' ) {
					throw new OverflowException( __( 'Date out of range.', 'the-events-calendar' ) );
				}
			}

			// Create a new date object: a badly formed date can trigger an exception - in such
			// a scenario try again and default to the current time instead
			try {
			$date = new DateTime( $date );
			}
			catch ( Exception $e ) {
				$date = new DateTime;
			}

			// set date object to be the first of the month -- all months have this day!
			$date->setDate( $date->format( 'Y' ), $date->format( 'm' ), 1 );

			// subtract a month
			$date->modify( '-1 month' );

			// return the year-month
			return $date->format( 'Y-m' );
		}

		/**
		 * Callback for adding the Meta box to the admin page
		 *
		 * @return void
		 */
		public function addEventBox() {
			add_meta_box(
				'tribe_events_event_details', $this->pluginName, array(
					$this,
					'EventsChooserBox',
				), self::POSTTYPE, 'normal', 'high'
			);
			add_meta_box(
				'tribe_events_event_options', sprintf( __( '%s Options', 'the-events-calendar' ), $this->singular_event_label ), array(
					$this,
					'eventMetaBox',
				), self::POSTTYPE, 'side', 'default'
			);

			add_meta_box(
				'tribe_events_venue_details', sprintf( __( '%s Information', 'the-events-calendar' ), $this->singular_venue_label ), array(
					$this,
					'VenueMetaBox',
				), self::VENUE_POST_TYPE, 'normal', 'high'
			);

			if ( ! class_exists( 'Tribe__Events__Pro__Main' ) ) {
				remove_meta_box( 'slugdiv', self::VENUE_POST_TYPE, 'normal' );
			}

			add_meta_box(
				'tribe_events_organizer_details', sprintf( __( '%s Information', 'the-events-calendar' ), $this->singular_organizer_label ), array(
					$this,
					'OrganizerMetaBox',
				), self::ORGANIZER_POST_TYPE, 'normal', 'high'
			);
		}

		/**
		 * Include the event editor meta box.
		 *
		 * @return void
		 */
		public function eventMetaBox() {
			include( $this->pluginPath . 'src/admin-views/event-sidebar-options.php' );
		}

		/**
		 * Get the date string (shortened).
		 *
		 * @param string $date The date.
		 *
		 * @return string The pretty (and shortened) date.
		 */
		public function getDateStringShortened( $date ) {
			$monthNames = $this->monthNames();
			$dateParts  = explode( '-', $date );
			$timestamp  = mktime( 0, 0, 0, $dateParts[1], 1, $dateParts[0] );

			return $monthNames[ date( 'F', $timestamp ) ];
		}

		/**
		 * Return the next tab index
		 *
		 * @return void
		 */
		public function tabIndex() {
			$this->tabIndexStart ++;

			return $this->tabIndexStart - 1;
		}

		/**
		 * Check whether a post is an event.
		 *
		 * @param int|WP_Post The event/post id or object.
		 *
		 * @return bool Is it an event?
		 */
		public function isEvent( $event ) {
			if ( $event === null || ( ! is_numeric( $event ) && ! is_object( $event ) ) ) {
				global $post;
				if ( is_object( $post ) && isset( $post->ID ) ) {
					$event = $post->ID;
				}
			}
			if ( is_numeric( $event ) ) {
				if ( get_post_type( $event ) == self::POSTTYPE ) {
					return true;
				}
			} elseif ( is_object( $event ) ) {
				if ( get_post_type( $event ) == self::POSTTYPE ) {
					return true;
				}
			}

			return false;
		}

		/**
		 * Check whether a post is a venue.
		 *
		 * @param int|WP_Post The venue/post id or object.
		 *
		 * @return bool Is it a venue?
		 */
		public function isVenue( $postId = null ) {
			if ( $postId === null || ! is_numeric( $postId ) ) {
				global $post;
				if ( isset( $post->ID ) ) {
					$postId = $post->ID;
				}
			}
			if ( isset( $postId ) && get_post_field( 'post_type', $postId ) == self::VENUE_POST_TYPE ) {
				return true;
			}

			return false;
		}

		/**
		 * Check whether a post is an organizer.
		 *
		 * @param int|WP_Post The organizer/post id or object.
		 *
		 * @return bool Is it an organizer?
		 */
		public function isOrganizer( $postId = null ) {
			if ( $postId === null || ! is_numeric( $postId ) ) {
				global $post;
				$postId = $post->ID;
			}
			if ( isset( $postId ) && get_post_field( 'post_type', $postId ) == self::ORGANIZER_POST_TYPE ) {
				return true;
			}

			return false;
		}

		/**
		 * Get a "previous/next post" link for events. Ordered by start date instead of ID.
		 *
		 * @param WP_Post $post The post/event.
		 * @param string  $mode Either 'next' or 'previous'.
		 * @param mixed   $anchor
		 *
		 * @return string The link (with <a> tags).
		 */
		public function get_event_link( $post, $mode = 'next', $anchor = false ) {
			global $wpdb;
			$link = '';

			if ( 'previous' === $mode ) {
				$order      = 'DESC';
				$direction  = '<';
			} else {
				$order      = 'ASC';
				$direction  = '>';
				$mode       = 'next';
			}

			$args = array(
				'post__not_in'   => array( $post->ID ),
				'order'          => $order,
				'orderby'        => "TIMESTAMP( $wpdb->postmeta.meta_value ) ID",
				'posts_per_page' => 1,
				'meta_query'     => array(
					array(
						'key'     => '_EventStartDate',
						'value'   => $post->EventStartDate,
						'type'    => 'DATETIME',
						'compare' => $direction,
					),
					array(
						'key'     => '_EventHideFromUpcoming',
						'compare' => 'NOT EXISTS',
					),
					'relation'    => 'AND',
				),
			);

			/**
			 * Allows the query arguments used when retrieving the next/previous event link
			 * to be modified.
			 *
			 * @var array   $args
			 * @var WP_Post $post
			 * @var boolean $anchor
			 */
			$args = (array) apply_filters( "tribe_events_get_{$mode}_event_link", $args, $post, $anchor );
			$results = tribe_get_events( $args );

			// If we successfully located the next/prev event, we should have precisely one element in $results
			if ( 1 === count( $results ) ) {
				$event = current( $results );

				if ( ! $anchor ) {
					$anchor = apply_filters( 'the_title', $event->post_title );
				} elseif ( strpos( $anchor, '%title%' ) !== false ) {
					// get the nicely filtered post title
					$title = apply_filters( 'the_title', $event->post_title, $event->ID );

					// escape special characters used in the second parameter of preg_replace
					$title = str_replace(
						array(
							'\\',
							'$',
						),
						array(
							'\\\\',
							'\$',
						),
						$title
					);

					$anchor = preg_replace( '|%title%|', $title, $anchor );
				}

				$link = '<a href="' . esc_url( tribe_get_event_link( $event ) ) . '">' . $anchor . '</a>';
			}

			/**
			 * Affords an opportunity to modify the event link (typically for the next or previous
			 * event in relation to $post).
			 *
			 * @var string  $link
			 * @var WP_Post $post
			 * @var string  $mode (typically "previous" or "next")
			 * @var string  $anchor
			 */
			return apply_filters( 'tribe_events_get_event_link', $link, $post, $mode, $anchor );
		}

		/**
		 * Add meta links to the Plugins list page.
		 *
		 * @param array  $links The current action links.
		 * @param string $file  The plugin to see if we are on TEC.
		 *
		 * @return array The modified action links array.
		 */
		public function addMetaLinks( $links, $file ) {
			if ( $file == $this->pluginDir . 'the-events-calendar.php' ) {
				$anchor   = __( 'Support', 'the-events-calendar' );
				$links[] = '<a href="' . esc_url( self::$dotOrgSupportUrl ) . '" target="_blank">' . $anchor . '</a>';

				$anchor   = __( 'View All Add-Ons', 'the-events-calendar' );
				$link     = add_query_arg(
					array(
						'utm_campaign' => 'in-app',
						'utm_medium'   => 'plugin-tec',
						'utm_source'   => 'plugins-manager',
					), self::$tribeUrl . self::$addOnPath
				);
				$links[] = '<a href="' . esc_url( $link ) . '" target="_blank">' . $anchor . '</a>';
			}

			return $links;
		}

		/**
		 * Register the dashboard widget.
		 *
		 * @return void
		 */
		public function dashboardWidget() {
			wp_add_dashboard_widget(
				'tribe_dashboard_widget', __( 'News from Modern Tribe', 'the-events-calendar' ), array(
					$this,
					'outputDashboardWidget',
				)
			);
		}

		/**
		 * Echo the dashboard widget.
		 *
		 * @param int $items
		 *
		 * @return void
		 */
		public function outputDashboardWidget( $items = 10 ) {
			echo '<div class="rss-widget">';
			wp_widget_rss_output( self::FEED_URL, array( 'items' => $items ) );
			echo '</div>';
		}

		/**
		 * Set the class property postExceptionThrown.
		 *
		 * return void
		 */
		public function setPostExceptionThrown( $thrown ) {
			$this->postExceptionThrown = $thrown;
		}

		/**
		 * Get the thrown post exception.
		 *
		 * @return mixed
		 */
		public function getPostExceptionThrown() {
			return $this->postExceptionThrown;
		}

		/**
		 * Echoes upsell stuff, if it should.
		 *
		 * @param int $postId
		 *
		 * @return void
		 */
		public function maybeShowMetaUpsell( $postId ) {
			?>
			<tr class="eventBritePluginPlug">
			<td colspan="2" class="tribe_sectionheader">
				<h4><?php esc_html_e( 'Additional Functionality', 'the-events-calendar' ); ?></h4>
			</td>
			</tr>
			<tr class="eventBritePluginPlug">
			<td colspan="2">
				<p><?php esc_html_e( 'Looking for additional functionality including recurring events, ticket sales, publicly submitted events, new views and more?', 'the-events-calendar' ) ?> <?php printf(
						__( 'Check out the <a href="%s">available add-ons</a>.', 'the-events-calendar' ),
						esc_url(
							add_query_arg(
								array(
									'utm_campaign' => 'in-app',
									'utm_medium'   => 'plugin-tec',
									'utm_source'   => 'post-editor',
								),
								self::$tribeUrl . self::$addOnPath
							)
						)
					); ?></p>
			</td>
			</tr><?php
		}


		/**
		 * Helper function for getting Post Id. Accepts null or a post id. If no $post object exists, returns false to avoid a PHP NOTICE
		 *
		 * @param int $postId (optional)
		 *
		 * @return int post ID
		 */
		public static function postIdHelper( $postId = null ) {
			if ( $postId != null && is_numeric( $postId ) > 0 ) {
				return (int) $postId;
			} elseif ( is_object( $postId ) && ! empty( $postId->ID ) ) {
				return (int) $postId->ID;
			} else {
				global $post;
				if ( is_object( $post ) ) {
					return get_the_ID();
				} else {
					return false;
				}
			}
		}

		/**
		 * Add the buttons/dropdown to the admin toolbar
		 *
		 * @return null
		 */
		public function addToolbarItems() {
			if ( ( ! defined( 'TRIBE_DISABLE_TOOLBAR_ITEMS' ) || ! TRIBE_DISABLE_TOOLBAR_ITEMS ) && ! is_network_admin() ) {
				global $wp_admin_bar;

				$wp_admin_bar->add_menu(
					array(
						'id'    => 'tribe-events',
						'title' => '<span class="ab-icon dashicons-before dashicons-calendar"></span>' . sprintf( __( '%s', 'the-events-calendar' ), $this->plural_event_label ),
						'href'  => $this->getLink( 'home' ),
					)
				);

				$wp_admin_bar->add_group(
					array(
						'id'     => 'tribe-events-group',
						'parent' => 'tribe-events',
					)
				);

				$wp_admin_bar->add_group(
					array(
						'id'     => 'tribe-events-add-ons-group',
						'parent' => 'tribe-events',
					)
				);

				$wp_admin_bar->add_group(
					array(
						'id'     => 'tribe-events-settings-group',
						'parent' => 'tribe-events',
					)
				);
				if ( current_user_can( 'edit_tribe_events' ) ) {
					$wp_admin_bar->add_group(
						array(
							'id'     => 'tribe-events-import-group',
							'parent' => 'tribe-events-add-ons-group',
						)
					);
				}

				$wp_admin_bar->add_menu(
					array(
						'id'     => 'tribe-events-view-calendar',
						'title'  => __( 'View Calendar', 'the-events-calendar' ),
						'href'   => $this->getLink( 'home' ),
						'parent' => 'tribe-events-group',
					)
				);

				if ( current_user_can( 'edit_tribe_events' ) ) {
					$wp_admin_bar->add_menu(
						array(
							'id'     => 'tribe-events-add-event',
							'title'  => sprintf( __( 'Add %s', 'the-events-calendar' ), $this->singular_event_label ),
							'href'   => trailingslashit( get_admin_url() ) . 'post-new.php?post_type=' . self::POSTTYPE,
							'parent' => 'tribe-events-group',
						)
					);
				}

				if ( current_user_can( 'edit_tribe_events' ) ) {
					$wp_admin_bar->add_menu(
						array(
							'id'     => 'tribe-events-edit-events',
							'title'  => sprintf( __( 'Edit %s', 'the-events-calendar' ), $this->plural_event_label ),
							'href'   => trailingslashit( get_admin_url() ) . 'edit.php?post_type=' . self::POSTTYPE,
							'parent' => 'tribe-events-group',
						)
					);
				}

				if ( current_user_can( 'publish_tribe_events' ) ) {
					$import_node = $wp_admin_bar->get_node( 'tribe-events-import' );
					if ( ! is_object( $import_node ) ) {
						$wp_admin_bar->add_menu(
							array(
								'id'     => 'tribe-events-import',
								'title'  => __( 'Import', 'the-events-calendar' ),
								'parent' => 'tribe-events-import-group',
							)
						);
					}
					$wp_admin_bar->add_menu(
						array(
							'id'     => 'tribe-csv-import',
							'title'  => __( 'CSV', 'the-events-calendar' ),
							'href'   => esc_url(
								add_query_arg(
									array(
										'post_type' => self::POSTTYPE,
										'page'      => 'events-importer',
									),
									admin_url( 'edit.php' )
								)
							),
							'parent' => 'tribe-events-import',
						)
					);
				}

				if ( current_user_can( 'manage_options' ) ) {

					$hide_all_settings = self::instance()->getNetworkOption( 'allSettingsTabsHidden', '0' );
					if ( $hide_all_settings == '0' ) {
						$wp_admin_bar->add_menu(
							array(
								'id'     => 'tribe-events-settings',
								'title'  => __( 'Settings', 'the-events-calendar' ),
								'href'   => trailingslashit( get_admin_url() ) . 'edit.php?post_type=' . self::POSTTYPE . '&amp;page=tribe-events-calendar',
								'parent' => 'tribe-events-settings-group',
							)
						);
					}

					// Only show help link if it's not blocked in network admin.
					$hidden_settings_tabs = self::instance()->getNetworkOption( 'hideSettingsTabs', array() );
					if ( ! in_array( 'help', $hidden_settings_tabs ) ) {
						$wp_admin_bar->add_menu(
									 array(
										 'id'     => 'tribe-events-help',
										 'title'  => __( 'Help', 'the-events-calendar' ),
										 'href'   => trailingslashit( get_admin_url() ) . 'edit.php?post_type=' . self::POSTTYPE . '&amp;page=tribe-events-calendar&amp;tab=help',
										 'parent' => 'tribe-events-settings-group',
									 )
						);
					}
				}
			}
		}

		/**
		 * Displays the View Calendar link at the top of the Events list in admin.
		 *
		 *
		 * @return void
		 */
		public function addViewCalendar() {
			if ( Tribe__Events__Admin__Helpers::instance()->is_screen( 'edit-' . self::POSTTYPE ) ) {
				//Output hidden DIV with Calendar link to be displayed via javascript
				echo '<div id="view-calendar-link-div" style="display:none;"><a class="add-new-h2" href="' . esc_url( $this->getLink() ) . '">' . esc_html__( 'View Calendar', 'the-events-calendar' ) . '</a></div>';
			}
		}

		/**
		 * Set the menu-edit-page to default display the events-related items.
		 *
		 *
		 * @return void
		 */
		public function setInitialMenuMetaBoxes() {
			global $current_screen;
			if ( empty( $current_screen->id ) || 'nav-menus' !== $current_screen->id ) {
				return;
			}

			$user_id = wp_get_current_user()->ID;
			if ( get_user_option( 'tribe_setDefaultNavMenuBoxes', $user_id ) ) {
				return;
			}

			$current_hidden_boxes = array();
			$current_hidden_boxes = get_user_option( 'metaboxhidden_nav-menus', $user_id );

			if ( $array_key = array_search( 'add-' . self::POSTTYPE, $current_hidden_boxes ) ) {
				unset( $current_hidden_boxes[ $array_key ] );
			}
			if ( $array_key = array_search( 'add-' . self::VENUE_POST_TYPE, $current_hidden_boxes ) ) {
				unset( $current_hidden_boxes[ $array_key ] );
			}
			if ( $array_key = array_search( 'add-' . self::ORGANIZER_POST_TYPE, $current_hidden_boxes ) ) {
				unset( $current_hidden_boxes[ $array_key ] );
			}
			if ( $array_key = array_search( 'add-' . self::TAXONOMY, $current_hidden_boxes ) ) {
				unset( $current_hidden_boxes[ $array_key ] );
			}

			update_user_option( $user_id, 'metaboxhidden_nav-menus', $current_hidden_boxes, true );
			update_user_option( $user_id, 'tribe_setDefaultNavMenuBoxes', true, true );
		}

		/**
		 * Add links to the plugins row
		 *
		 * @param $actions
		 *
		 * @return mixed
		 * @todo move to an admin class
		 */
		public function addLinksToPluginActions( $actions ) {
			$actions['settings']       = '<a href="' . esc_url(
					add_query_arg(
						array(
							'post_type' => self::POSTTYPE,
							'page'      => 'tribe-events-calendar',
						),
						admin_url( 'edit.php' )
					)
				) . '">' . __( 'Settings', 'the-events-calendar' ) . '</a>';
			$actions['tribe-calendar'] = '<a href="' . $this->getLink() . '">' . __( 'Calendar', 'the-events-calendar' ) . '</a>';

			return $actions;
		}

		/**
		 * Add help menu item to the admin (unless blocked via network admin settings).
		 *
		 * @todo move to an admin class
		 */
		public function addHelpAdminMenuItem() {
			$hidden_settings_tabs = self::instance()->getNetworkOption( 'hideSettingsTabs', array() );
			if ( in_array( 'help', $hidden_settings_tabs ) ) {
				return;
			}

			$parent = 'edit.php?post_type=' . self::POSTTYPE;
			$title  = __( 'Help', 'the-events-calendar' );
			$slug   = esc_url(
				add_query_arg(
					array(
						'post_type' => self::POSTTYPE,
						'page'      => 'tribe-events-calendar',
						'tab'       => 'help',
					),
					'edit.php'
				)
			);

			add_submenu_page( $parent, $title, $title, 'manage_options', $slug, '' );
		}

		/**
		 * When the edit-tags.php screen loads, setup filters
		 * to fix the tagcloud links
		 *
		 * @return void
		 */
		public function prepare_to_fix_tagcloud_links() {
			if ( Tribe__Events__Admin__Helpers::instance()->is_post_type_screen( self::POSTTYPE ) ) {
				add_filter( 'get_edit_term_link', array( $this, 'add_post_type_to_edit_term_link' ), 10, 4 );
			}
		}

		/**
		 * Tag clouds in the admin don't pass the post type arg
		 * when getting the edit link. If we're on the tag admin
		 * in Events post type context, make sure we add that
		 * arg to the edit tag link
		 *
		 * @param string $link
		 * @param int    $term_id
		 * @param string $taxonomy
		 * @param string $context
		 *
		 * @return string
		 */
		public function add_post_type_to_edit_term_link( $link, $term_id, $taxonomy, $context ) {
			if ( $taxonomy == 'post_tag' && empty( $context ) ) {
				$link = add_query_arg( array( 'post_type' => self::POSTTYPE ), $link );
			}

			return esc_url( $link );
		}

		/**
		 * Set up the list view in the view selector in the tribe events bar.
		 *
		 * @param array $views The current views array.
		 *
		 * @return array The modified views array.
		 */
		public function setup_listview_in_bar( $views ) {
			$views[] = array(
				'displaying'     => 'list',
				'event_bar_hook' => 'tribe_events_before_template',
				'anchor'         => __( 'List', 'the-events-calendar' ),
				'url'            => tribe_get_listview_link(),
			);

			return $views;
		}

		/**
		 * Set up the calendar view in the view selector in the tribe events bar.
		 *
		 * @param array $views The current views array.
		 *
		 * @return array The modified views array.
		 */
		public function setup_gridview_in_bar( $views ) {
			$views[] = array(
				'displaying'     => 'month',
				'event_bar_hook' => 'tribe_events_month_before_template',
				'anchor'         => __( 'Month', 'the-events-calendar' ),
				'url'            => tribe_get_gridview_link(),
			);

			return $views;
		}

		/**
		 * Add day view to the views selector in the tribe events bar.
		 *
		 * @param array $views The current array of views registered to the tribe bar.
		 *
		 * @return array The views registered with day view added.
		 */
		public function setup_dayview_in_bar( $views ) {
			$views[] = array(
				'displaying'     => 'day',
				'anchor'         => __( 'Day', 'the-events-calendar' ),
				'event_bar_hook' => 'tribe_events_before_template',
				'url'            => tribe_get_day_link(),
			);

			return $views;
		}

		/**
		 * Set up the keyword search in the tribe events bar.
		 *
		 * @param array $filters The current filters in the bar array.
		 *
		 * @return array The modified filters array.
		 */
		public function setup_keyword_search_in_bar( $filters ) {

			$value = '';
			if ( ! empty( $_REQUEST['tribe-bar-search'] ) ) {
				$value = esc_attr( $_REQUEST['tribe-bar-search'] );
			}

			if ( tribe_get_option( 'tribeDisableTribeBar', false ) == false ) {
				$filters['tribe-bar-search'] = array(
					'name'    => 'tribe-bar-search',
					'caption' => __( 'Search', 'the-events-calendar' ),
					'html'    => '<input type="text" name="tribe-bar-search" id="tribe-bar-search" value="' . esc_attr( $value ) . '" placeholder="' . esc_attr__( 'Search', 'the-events-calendar' ) . '">',
				);
			}

			return $filters;
		}

		/**
		 * Set up the date search in the tribe events bar.
		 *
		 * @param array $filters The current filters in the bar array.
		 *
		 * @return array The modified filters array.
		 */
		public function setup_date_search_in_bar( $filters ) {

			global $wp_query;

			$value = apply_filters( 'tribe-events-bar-date-search-default-value', '' );

			if ( ! empty( $_REQUEST['tribe-bar-date'] ) ) {
				$value = $_REQUEST['tribe-bar-date'];
			}

			$caption = __( 'Date', 'the-events-calendar' );

			if ( tribe_is_month() ) {
				$caption = sprintf( __( '%s In', 'the-events-calendar' ), $this->plural_event_label );
			} elseif ( tribe_is_list_view() ) {
				$caption = sprintf( __( '%s From', 'the-events-calendar' ), $this->plural_event_label );
			} elseif ( tribe_is_day() ) {
				$caption = __( 'Day Of', 'the-events-calendar' );
				$value   = date( Tribe__Events__Date_Utils::DBDATEFORMAT, strtotime( $wp_query->query_vars['eventDate'] ) );
			}

			$caption = apply_filters( 'tribe_bar_datepicker_caption', $caption );

			$filters['tribe-bar-date'] = array(
				'name'    => 'tribe-bar-date',
				'caption' => $caption,
				'html'    => '<input type="text" name="tribe-bar-date" style="position: relative;" id="tribe-bar-date" value="' . esc_attr( $value ) . '" placeholder="' . esc_attr__( 'Date', 'the-events-calendar' ) . '"><input type="hidden" name="tribe-bar-date-day" id="tribe-bar-date-day" class="tribe-no-param" value="">',
			);

			return $filters;
		}

		/**
		 * Removes views that have been deselected in the Template Settings as hidden from the view array.
		 *
		 *
		 * @param array $views The current views array.
		 * @param bool  $visible
		 *
		 * @return array The new views array.
		 */
		public function remove_hidden_views( $views, $visible = true ) {
			$enable_views_defaults = array();
			foreach ( $views as $view ) {
				$enable_views_defaults[] = $view['displaying'];
			}
			if ( $visible ) {
				$enable_views = tribe_get_option( 'tribeEnableViews', $enable_views_defaults );
				foreach ( $views as $index => $view ) {
					if ( ! in_array( $view['displaying'], $enable_views ) ) {
						unset( $views[ $index ] );
					}
				}
			}

			return $views;
		}

		/**
		 * Disable the canonical redirect if tribe_paged is set
		 *
		 * @param WP_Query $query The current query object.
		 *
		 * @return WP_Query The modified query object.
		 */
		public function set_tribe_paged( $query ) {
			if ( ! empty( $_REQUEST['tribe_paged'] ) ) {
				add_filter( 'redirect_canonical', '__return_false' );
			}

			return $query;
		}

		/**
		 * Add filters to register custom cron schedules
		 *
		 *
		 * @return void
		 */
		public function filter_cron_schedules() {
			add_filter( 'cron_schedules', array( $this, 'register_30min_interval' ) );
		}

		/**
		 * Add a new scheduled task interval (of 30mins).
		 *
		 * @param  array $schedules
		 * @return array
		 */
		public function register_30min_interval( $schedules ) {
			$schedules['every_30mins'] = array(
				'interval' => 30 * MINUTE_IN_SECONDS,
				'display'  => __( 'Once Every 30 Mins', 'tribe-events-pro' ),
			);

			return $schedules;
		}

		/**
		 * Facilitates the import of events in WXR format (ie, via the core WP importer).
		 *
		 * When WP imports posts it avoids duplication by comparing the post name, date and
		 * type of each. Once a post has been imported, if another post matching the above
		 * criteria is found it is discarded.
		 *
		 * In the case of recurring events this would cause all but the first in a series
		 * to be discarded and so we workaround the problem by altering the title (and
		 * restoring it afterwards - during "wp_import_post_data_processed").
		 *
		 * We apply this to *all* events being imported because we also need to cater for
		 * a scenario where events that were originally created as part of a set of
		 * recurring events may later have been broken out of the chain into standalone
		 * events (otherwise we could restrict this operation to only those events with
		 * a post parent).
		 *
		 * We're retaining this logic in core (rather than move it to PRO) since it's
		 * posible for data from a site running PRO to be imported into a site running only
		 * core.
		 *
		 * @see Tribe__Events__Main::filter_wp_import_data_after()
		 *
		 * @param array $post
		 *
		 * @return array
		 */
		public function filter_wp_import_data_before( $post ) {
			if ( $post['post_type'] === self::POSTTYPE ) {
				$start_date = '';
				if ( isset( $post['postmeta'] ) && is_array( $post['postmeta'] ) ) {
					foreach ( $post['postmeta'] as $meta ) {
						if ( $meta['key'] == '_EventStartDate' ) {
							$start_date = $meta['value'];
							break;
						}
					}
				}
				if ( ! empty( $start_date ) ) {
					$post['post_title'] .= '[tribe_start_date]' . $start_date . '[/tribe_start_date]';
				}
			}

			return $post;
		}

		/**
		 * Event titles have been modified by filter_wp_import_data_before().
		 * This puts them back how they belong.
		 *
		 * @param array $post
		 *
		 * @return array
		 * @see Tribe__Events__Main::filter_wp_import_data_before()
		 */
		public function filter_wp_import_data_after( $post ) {
			if ( $post['post_type'] == self::POSTTYPE ) {
				$post['post_title'] = preg_replace( '#\[tribe_start_date\].*?\[\/tribe_start_date\]#', '', $post['post_title'] );
			}

			return $post;
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
		 * Insert an array immediately before a specified key within another array.
		 *
		 * @param $key
		 * @param $source_array
		 * @param $insert_array
		 *
		 * @return array
		 */
		public static function array_insert_before_key( $key, $source_array, $insert_array ) {
			if ( array_key_exists( $key, $source_array ) ) {
				$position     = array_search( $key, array_keys( $source_array ) );
				$source_array = array_slice( $source_array, 0, $position, true ) + $insert_array + array_slice( $source_array, $position, null, true );
			} else {
				// If no key is found, then add it to the end of the array.
				$source_array += $insert_array;
			}

			return $source_array;
		}

		public function run_updates() {
			$updater = new Tribe__Events__Updater( self::VERSION );
			if ( $updater->update_required() ) {
				$updater->do_updates();
					}
				}

		/**
		 * Helper used to test if PRO is present and activated.
		 *
		 * This method should no longer be used, but is being retained to avoid potential
		 * for fatal errors where core is updated before an addon plugin - such as Community
		 * Events 3.4 or earlier - which might otherwise occur were it removed completely.
		 *
		 * @deprecated as of 3.7, remove in 4.0
		 *
		 * @param string $version
		 *
		 * @return bool
		 */
		public static function ecpActive( $version = '2.0.7' ) {
			return class_exists( 'Tribe__Events__Pro__Main' ) && defined( 'Tribe__Events__Pro__Main::VERSION' ) && version_compare( Tribe__Events__Pro__Main::VERSION, $version, '>=' );
		}

		protected function init_autoloading() {
			$autoloader = Tribe__Events__Autoloader::instance();

			$prefixes = array(
				'Tribe__Events__' => $this->pluginPath . 'src/Tribe',
			);
			$autoloader->register_prefixes( $prefixes );

			// deprecated classes are registered in a class to path fashion
			foreach ( glob( $this->pluginPath . 'src/deprecated/*.php' ) as $file ) {
				$class_name = str_replace( '.php', '', basename( $file ) );
				$autoloader->register_class( $class_name, $file );
			}
			$autoloader->register_autoloader();
		}

		/**
		 * Registers the list widget
		 *
		 * @return void
		 */
		public function register_list_widget() {
			register_widget( 'Tribe__Events__List_Widget' );
		}

		/**
		 * Sets the globally shared `$_tribe_meta_factory` object
		 */
		public function set_meta_factory_global() {
			global $_tribe_meta_factory;
			$_tribe_meta_factory = new Tribe__Events__Meta_Factory();
		}

	} // end Tribe__Events__Main class
} // end if !class_exists Tribe__Events__Main
