<?php
/**
 * Main Tribe Events Calendar class.
 */
use Tribe\DB_Lock;

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
		/**
		 * This constant is deprecated (as of 4.0) in favor of Tribe__Main::OPTIONNAME
		 */
		const OPTIONNAME          = 'tribe_events_calendar_options';

		/**
		 * This constant is deprecated (as of 4.0) in favor of Tribe__Main::OPTIONNAME
		 */
		const OPTIONNAMENETWORK   = 'tribe_events_calendar_network_options';

		const EVENTSERROROPT      = '_tribe_events_errors';
		const TAXONOMY            = 'tribe_events_cat';
		const POSTTYPE            = 'tribe_events';
		const VENUE_POST_TYPE     = 'tribe_venue';
		const ORGANIZER_POST_TYPE = 'tribe_organizer';

		const VERSION             = '5.9.0';

		/**
		 * Min Pro Addon
		 *
		 * @deprecated 4.8
		 */
		const MIN_ADDON_VERSION   = '5.7-dev';

		/**
		 * Min Common
		 *
		 * @deprecated 4.8
		 */
		const MIN_COMMON_VERSION  = '4.9.2-dev';

		const WP_PLUGIN_URL       = 'https://wordpress.org/extend/plugins/the-events-calendar/';

		/**
		 * Min Version of WordPress
		 *
		 * @since 4.8
		 */
		protected $min_wordpress = '4.7';

		/**
		 * Min Version of PHP
		 *
		 * @since 4.8
		 */
		protected $min_php = '5.6.0';

		/**
		 * Min Version of Event Tickets
		 *
		 * @since 4.8
		 */
		protected $min_et_version = '4.11.2-dev';

		/**
		 * Maybe display data wrapper
		 * @var array
		 */
		private $show_data_wrapper = [ 'before' => true, 'after' => true ];

		/**
		 * Args for the event post type
		 *
		 * @var array
		 */
		protected $post_type_args = [
			'public'          => true,
			'rewrite'         => [ 'slug' => 'event', 'with_front' => false ],
			'menu_position'   => 6,
			'supports'        => [
				'title',
				'editor',
				'excerpt',
				'author',
				'thumbnail',
				'custom-fields',
				'comments',
				'revisions',
			],
			'taxonomies'      => [ 'post_tag' ],
			'capability_type' => [ 'tribe_event', 'tribe_events' ],
			'map_meta_cap'    => true,
			'has_archive'     => true,
			'menu_icon'       => 'dashicons-calendar',
		];

		/**
		 * Args for venue post type
		 * @var array
		 */
		public $postVenueTypeArgs = [];

		protected $taxonomyLabels;

		/**
		 * Args for organizer post type
		 * @var array
		 */
		public $postOrganizerTypeArgs = [];

		public static $tribeUrl = 'https://tri.be/';
		public static $tecUrl = 'https://theeventscalendar.com/';

		public static $addOnPath = 'products/';

		public static $dotOrgSupportUrl = 'https://wordpress.org/support/plugin/the-events-calendar';

		public $rewriteSlug         = 'events';
		public $rewriteSlugSingular = 'event';
		public $category_slug       = 'category';
		public $tag_slug            = 'tag';
		public $monthSlug           = 'month';
		public $featured_slug       = 'featured';

		/**
		 * @deprecated 4.5.8 use `Tribe__Events__Pro__Main::instance()->all_slug` instead
		 *
		 * @var string
		 */
		public $all_slug = 'all';

		/** @deprecated 4.0 */
		public $taxRewriteSlug = 'event/category';

		/** @deprecated 4.0 */
		public $tagRewriteSlug = 'event/tag';

		/** @var Tribe__Events__Admin__Timezone_Settings */
		public $timezone_settings;

		/**
		 * A Stored version of the Welcome and Update Pages
		 * @var Tribe__Admin__Activation_Page
		 */
		public $activation_page;

		// @todo [BTRIA-602]: Remove in 4.0.
		public $upcomingSlug = 'upcoming';
		public $pastSlug     = 'past';

		public $listSlug               = 'list';
		public $daySlug                = 'day';
		public $todaySlug              = 'today';
		protected $postExceptionThrown = false;

		/**
		 * Deprecated property in 4.0. Use plugin_dir instead
		 */
		public $pluginDir;

		/**
		 * Deprecated property in 4.0. Use plugin_path instead
		 */
		public $pluginPath;

		/**
		 * Deprecated property in 4.0. Use plugin_url instead
		 */
		public $pluginUrl;

		/**
		 * Deprecated property in 4.0. Use plugin_name instead
		 */
		public $pluginName;

		public $displaying;
		public $plugin_file;
		public $plugin_dir;
		public $plugin_path;
		public $plugin_url;
		public $plugin_name;
		public $date;
		protected $tabIndexStart = 2000;

		public $metaTags = [
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
			'_EventOrigin',
			'_tribe_featured',
		];

		public $venueTags = [
			'_VenueCountry',
			'_VenueAddress',
			'_VenueCity',
			'_VenueStateProvince',
			'_VenueState',
			'_VenueProvince',
			'_VenueZip',
			'_VenuePhone',
			'_VenueURL',
			'_VenueShowMap',
			'_VenueShowMapLink',
		];

		public $organizerTags = [
			'_OrganizerEmail',
			'_OrganizerWebsite',
			'_OrganizerPhone',
		];

		public $currentPostTimestamp;

		/**
		 * @deprecated 4.4
		 */
		public $daysOfWeekShort;

		/**
		 * @deprecated 4.4
		 */
		public $daysOfWeek;

		/**
		 * @deprecated 4.4
		 */
		public $daysOfWeekMin;

		/**
		 * @deprecated 4.4
		 */
		public $monthsFull;

		/**
		 * @deprecated 4.4
		 */
		public $monthsShort;

		public $singular_venue_label;
		public $plural_venue_label;

		public $singular_organizer_label;
		public $plural_organizer_label;

		public $singular_event_label;
		public $plural_event_label;

		/** @var Tribe__Events__Default_Values */
		private $default_values = null;

		/**
		 * @var bool Prevent autoload initialization
		 */
		private $should_prevent_autoload_init = false;

		/**
		 * @var string tribe-common VERSION regex
		 */
		private $common_version_regex = "/const\s+VERSION\s*=\s*'([^']+)'/m";

		public static $tribeEventsMuDefaults;

		/**
		 * Where in the themes we will look for templates
		 *
		 * @since 4.7
		 *
		 * @var string
		 */
		public $template_namespace = 'events';

		/**
		 * Static Singleton Holder
		 * @var self
		 */
		protected static $instance;

		/**
		 * Get (and instantiate, if necessary) the instance of the class
		 *
		 * @return self
		 */
		public static function instance() {
			if ( ! self::$instance ) {
				self::$instance = new self;
			}
			return self::$instance;
		}

		/**
		 * Initializes plugin variables and sets up WordPress hooks/actions.
		 */
		protected function __construct() {
			$this->plugin_file = TRIBE_EVENTS_FILE;
			$this->pluginPath  = $this->plugin_path = trailingslashit( dirname( $this->plugin_file ) );
			$this->pluginDir   = $this->plugin_dir = trailingslashit( basename( $this->plugin_path ) );
			$this->pluginUrl   = $this->plugin_url = str_replace( basename( $this->plugin_file ), '', plugins_url( basename( $this->plugin_file ), $this->plugin_file ) );

			// Set common lib information, needs to happen file load
			$this->maybe_set_common_lib_info();

			// let's initialize tec
			add_action( 'plugins_loaded', [ $this, 'maybe_bail_if_old_et_is_present' ], -1 );
			add_action( 'plugins_loaded', [ $this, 'maybe_bail_if_invalid_wp_or_php' ], -1 );
			add_action( 'plugins_loaded', [ $this, 'plugins_loaded' ], 0 );

			// Prevents Image Widget Plus from been problematic
			$this->compatibility_unload_iwplus_v102();
		}

		/**
		 * To avoid duplication of our own methods and to provide a underlying system
		 * The Events Calendar maintains a Library called Common to store a base for our plugins
		 *
		 * Currently we will read the File `common/package.json` to determine which version
		 * of the Common Lib we will pass to the Auto-Loader of PHP.
		 *
		 * In the past we used to parse `common/src/Tribe/Main.php` for the Common Lib version.
		 *
		 * @link https://github.com/moderntribe/tribe-common
		 * @see  self::init_autoloading
		 *
		 * @return void
		 */
		public function maybe_set_common_lib_info() {
			// if there isn't a tribe-common version, bail with a notice
			$common_version = file_get_contents( $this->plugin_path . 'common/src/Tribe/Main.php' );
			if ( ! preg_match( $this->common_version_regex, $common_version, $matches ) ) {
				return add_action( 'admin_head', [ $this, 'missing_common_libs' ] );
			}

			$common_version = $matches[1];

			/**
			 * If we don't have a version of Common or a Older version of the Lib
			 * overwrite what should be loaded by the auto-loader
			 */
			if (
				empty( $GLOBALS['tribe-common-info'] )
				|| version_compare( $GLOBALS['tribe-common-info']['version'], $common_version, '<' )
			) {
				$GLOBALS['tribe-common-info'] = [
					'dir'     => "{$this->plugin_path}common/src/Tribe",
					'version' => $common_version,
				];
			}
		}

		/**
		 * Resets the global common info back to ET's common path
		 *
		 * @since 4.9.3.2
		 */
		private function reset_common_lib_info_back_to_et() {
			if ( ! class_exists( 'Tribe__Tickets__Main' ) ) {
				return;
			}

			$et          = Tribe__Tickets__Main::instance();
			$main_source = file_get_contents( $et->plugin_path . 'common/src/Tribe/Main.php' );

			// if there isn't a VERSION, don't override the common path
			if ( ! preg_match( $this->common_version_regex, $main_source, $matches ) ) {
				return;
			}

			$GLOBALS['tribe-common-info'] = [
				'dir'     => "{$et->plugin_path}common/src/Tribe",
				'version' => $matches[1],
			];
		}

		/**
		 * Prevents bootstrapping and autoloading if the version of ET that is running is too old
		 *
		 * @since 4.9.3.2
		 */
		public function maybe_bail_if_old_et_is_present() {
			// early check for an older version of Event Tickets to prevent fatal error
			if ( ! class_exists( 'Tribe__Tickets__Main' ) ) {
				return;
			}

			if ( version_compare( Tribe__Tickets__Main::VERSION, $this->min_et_version, '>=' ) ) {
				return;
			}

			$this->should_prevent_autoload_init = true;

			add_action( 'admin_notices', [ $this, 'compatibility_notice' ] );
			add_action( 'network_admin_notices', [ $this, 'compatibility_notice' ] );
			add_filter( 'tribe_ecp_to_run_or_not_to_run', [ $this, 'disable_pro' ] );
			add_action( 'tribe_plugins_loaded', [ $this, 'remove_exts' ], 0 );
			/*
			* After common was loaded by another source (e.g. Event Tickets) let's append this plugin source files
			* to the ones the Autoloader will search. Since we're appending them the ones registered by the plugin
			* "owning" common will be searched first.
			*/
			add_action( 'tribe_common_loaded', [ $this, 'register_plugin_autoload_paths' ] );

			// if we get in here, we need to reset the global common to ET's version so that we don't cause a fatal
			$this->reset_common_lib_info_back_to_et();

			// Disable older versions of Community Events to prevent fatal Error.
			remove_action( 'plugins_loaded', 'Tribe_CE_Load', 2 );
		}

		/**
		 * Prevents bootstrapping and autoloading if the version of WP or PHP are too old
		 *
		 * @since 4.9.3.2
		 */
		public function maybe_bail_if_invalid_wp_or_php() {
			if ( self::supportedVersion( 'wordpress' ) && self::supportedVersion( 'php' ) ) {
				return;
			}

			add_action( 'admin_notices', [ $this, 'notSupportedError' ] );

			// if we get in here, we need to reset the global common to ET's version so that we don't cause a fatal
			$this->reset_common_lib_info_back_to_et();

			$this->should_prevent_autoload_init = true;
		}

		/**
		 * Plugins shouldn't include their functions before `plugins_loaded` because this will allow
		 * better compatibility with the autoloader methods.
		 *
		 * @return void
		 */
		public function plugins_loaded() {
			if ( $this->should_prevent_autoload_init ) {
				return;
			}

			/**
			 * Before any methods from this plugin are called, we initialize our Autoloading
			 * After this method we can use any `Tribe__` classes
			 */
			$this->init_autoloading();

			Tribe__Main::instance();

			add_action( 'tribe_common_loaded', [ $this, 'bootstrap' ], 0 );
		}

		/**
		 * Load Text Domain on tribe_common_loaded as it requires common
		 *
		 * @since 4.8
		 *
		 */
		public function bootstrap() {
			$this->bind_implementations();
			$this->loadLibraries();
			$this->addHooks();
			$this->register_active_plugin();
		}

		/**
		 * To allow easier usage of classes on our files we have a AutoLoader that will match
		 * class names to it's required file inclusion into the Request.
		 *
		 * @return void
		 */
		protected function init_autoloading() {
			$autoloader = $this->get_autoloader_instance();
			$this->register_plugin_autoload_paths( $autoloader );

			// Deprecated classes are registered in a class to path fashion.
			foreach ( glob( $this->plugin_path . 'src/deprecated/*.php', GLOB_NOSORT ) as $file ) {
				$class_name = str_replace( '.php', '', basename( $file ) );
				$autoloader->register_class( $class_name, $file );
			}

			$autoloader->register_autoloader();
		}

		/**
		 * Load The Events Calendar text domain.
		 *
		 * @since 5.1.0
		 *
		 * @return bool
		 */
		public function load_text_domain() {
			return Tribe__Main::instance( $this )->load_text_domain( 'the-events-calendar', $this->plugin_dir . 'lang/' );
		}

		/**
		 * Registers the implementations in the container.
		 *
		 * Classes that should be built at `plugins_loaded` time are also instantiated.
		 *
		 * @since  4.4
		 *
		 * @return void
		 */
		public function bind_implementations(  ) {
			tribe_singleton( 'tec.main', $this );

			// i18n.
			tribe_singleton( 'tec.i18n', new Tribe\Events\I18n( $this ) );

			// Utils
			tribe_singleton( 'tec.cost-utils', 'Tribe__Events__Cost_Utils' );

			// Front page events archive support
			tribe_singleton( 'tec.front-page-view', 'Tribe__Events__Front_Page_View' );

			// Metabox for Single Edit
			tribe_singleton( 'tec.admin.event-meta-box', 'Tribe__Events__Admin__Event_Meta_Box' );

			// Featured Events
			tribe_singleton( 'tec.featured_events', 'Tribe__Events__Featured_Events' );
			tribe_singleton( 'tec.featured_events.query_helper', new Tribe__Events__Featured_Events__Query_Helper );
			tribe_singleton( 'tec.featured_events.permalinks_helper', new Tribe__Events__Featured_Events__Permalinks_Helper );

			// Event Aggregator
			tribe_singleton( 'events-aggregator.main', 'Tribe__Events__Aggregator', [ 'load', 'hook' ] );
			tribe_singleton( 'events-aggregator.service', 'Tribe__Events__Aggregator__Service' );
			tribe_singleton( 'events-aggregator.settings', 'Tribe__Events__Aggregator__Settings' );
			tribe_singleton( 'events-aggregator.records', 'Tribe__Events__Aggregator__Records', [ 'hook' ] );
			tribe_register_provider( 'Tribe__Events__Aggregator__REST__V1__Service_Provider' );
			tribe_register_provider( 'Tribe__Events__Aggregator__CLI__Service_Provider' );
			tribe_register_provider( 'Tribe__Events__Aggregator__Processes__Service_Provider' );
			tribe_register_provider( 'Tribe__Events__Editor__Provider' );

			// Shortcodes
			tribe_singleton( 'tec.shortcodes.event-details', 'Tribe__Events__Shortcode__Event_Details', [ 'hook' ] );

			// Ignored Events
			tribe_singleton( 'tec.ignored-events', 'Tribe__Events__Ignored_Events', [ 'hook' ] );

			// Capabilities.
			tribe_singleton( Tribe__Events__Capabilities::class, Tribe__Events__Capabilities::class, [ 'hook' ] );

			// Assets loader
			tribe_singleton( 'tec.assets', 'Tribe__Events__Assets', [ 'register', 'hook' ] );

			// Tribe Bar
			tribe_singleton( 'tec.bar', 'Tribe__Events__Bar', [ 'hook' ] );

			// iCal
			tribe_singleton( 'tec.iCal', 'Tribe__Events__iCal', [ 'hook' ] );

			// REST API v1
			tribe_singleton( 'tec.rest-v1.main', 'Tribe__Events__REST__V1__Main', [ 'bind_implementations', 'hook' ] );
			tribe( 'tec.rest-v1.main' );

			// Integrations
			tribe_singleton( 'tec.integrations.twenty-seventeen', 'Tribe__Events__Integrations__Twenty_Seventeen', [ 'hook' ] );
			tribe_singleton( \Tribe\Events\Integrations\WP_Rocket::class, \Tribe\Events\Integrations\WP_Rocket::class );
			tribe_singleton( \Tribe\Events\Integrations\Beaver_Builder::class, \Tribe\Events\Integrations\Beaver_Builder::class );

			// Linked Posts
			tribe_singleton( 'tec.linked-posts', 'Tribe__Events__Linked_Posts' );
			tribe_singleton( 'tec.linked-posts.venue', 'Tribe__Events__Venue' );
			tribe_singleton( 'tec.linked-posts.organizer', 'Tribe__Events__Organizer' );

			// Adjacent Events
			tribe_singleton( 'tec.adjacent-events', 'Tribe__Events__Adjacent_Events' );

			// Purge Expired events
			tribe_singleton( 'tec.event-cleaner', new Tribe__Events__Event_Cleaner() );

			// Gutenberg Extension
			tribe_singleton( 'tec.gutenberg', 'Tribe__Events__Gutenberg', [ 'hook' ] );

			// Admin Notices
			tribe_singleton( 'tec.admin.notice.timezones', 'Tribe__Events__Admin__Notice__Timezones', [ 'hook' ] );
			tribe_singleton( 'tec.admin.notice.marketing', 'Tribe__Events__Admin__Notice__Marketing', [ 'hook' ] );
			tribe_singleton( Tribe\Events\Admin\Notice\Legacy_Views_Deprecation::class, Tribe\Events\Admin\Notice\Legacy_Views_Deprecation::class, [ 'hook' ] );

			// GDPR Privacy
			tribe_singleton( 'tec.privacy', 'Tribe__Events__Privacy', [ 'hook' ] );

			// The ORM/Repository service provider.
			tribe_register_provider( 'Tribe__Events__Service_Providers__ORM' );

			tribe_singleton( 'events.rewrite', Tribe__Events__Rewrite::class );

			// The Context service provider.
			tribe_register_provider( Tribe\Events\Service_Providers\Context::class );

			// The Views v2 service provider.
			tribe_register_provider( Tribe\Events\Views\V2\Service_Provider::class );

			// Register and start the legacy v2 Customizer Sections
			// @todo: deprecate this when we deprecate v1 views!
			if ( ! tribe_events_views_v2_is_enabled() ) {
				tribe_singleton( 'tec.customizer.global-elements', new Tribe__Events__Customizer__Global_Elements() );
				tribe_singleton( 'tec.customizer.general-theme', new Tribe__Events__Customizer__General_Theme() );
				tribe_singleton( 'tec.customizer.day-list-view', new Tribe__Events__Customizer__Day_List_View() );
				tribe_singleton( 'tec.customizer.month-week-view', new Tribe__Events__Customizer__Month_Week_View() );
				tribe_singleton( 'tec.customizer.widget', new Tribe__Events__Customizer__Widget() );
			}

			// First boot.
			tribe_register_provider( Tribe\Events\Service_Providers\First_Boot::class );


			/**
			 * Allows other plugins and services to override/change the bound implementations.
			 */
			do_action( 'tribe_events_bound_implementations' );

			// Database locks.
			tribe_singleton( 'db-lock', DB_Lock::class );
		}

		/**
		 * Registers this plugin as being active for other tribe plugins and extensions
		 */
		protected function register_active_plugin() {
			$this->registered = new Tribe__Events__Plugin_Register();
		}

		/**
		 * Load all the required library files.
		 */
		protected function loadLibraries() {
			// Setup the Activation page
			$this->activation_page();

			// Tribe common resources
			require_once $this->plugin_path . 'vendor/tribe-common-libraries/tribe-common-libraries.class.php';

			// Load Template Tags
			require_once $this->plugin_path . 'src/functions/template-tags/url.php';
			require_once $this->plugin_path . 'src/functions/template-tags/query.php';
			require_once $this->plugin_path . 'src/functions/template-tags/general.php';
			require_once $this->plugin_path . 'src/functions/template-tags/month.php';
			require_once $this->plugin_path . 'src/functions/template-tags/loop.php';
			require_once $this->plugin_path . 'src/functions/template-tags/google-map.php';
			require_once $this->plugin_path . 'src/functions/template-tags/event.php';
			require_once $this->plugin_path . 'src/functions/template-tags/organizer.php';
			require_once $this->plugin_path . 'src/functions/template-tags/venue.php';
			require_once $this->plugin_path . 'src/functions/template-tags/date.php';
			require_once $this->plugin_path . 'src/functions/template-tags/link.php';
			require_once $this->plugin_path . 'src/functions/template-tags/widgets.php';
			require_once $this->plugin_path . 'src/functions/template-tags/ical.php';
			require_once $this->plugin_path . 'src/deprecated/functions.php';

			// Load Advanced Functions
			require_once $this->plugin_path . 'src/functions/advanced-functions/event.php';
			require_once $this->plugin_path . 'src/functions/advanced-functions/venue.php';
			require_once $this->plugin_path . 'src/functions/advanced-functions/organizer.php';
			require_once $this->plugin_path . 'src/functions/advanced-functions/linked-posts.php';
			require_once $this->plugin_path . 'src/functions/utils/array.php';
			require_once $this->plugin_path . 'src/functions/utils/labels.php';
			require_once $this->plugin_path . 'src/functions/utils/install.php';

			// Load Deprecated Template Tags
			if ( ! defined( 'TRIBE_DISABLE_DEPRECATED_TAGS' ) ) {
				require_once $this->plugin_path . 'src/functions/template-tags/deprecated.php';
			}
		}

		/**
		 * Prevents Image Widget Plus weird version of Tribe Common Lib to
		 * conflict with The Events Calendar
		 *
		 * It will make IW+ not load on version 1.0.2
		 *
		 * @since   4.8.1
		 *
		 * @return  void
		 */
		private function compatibility_unload_iwplus_v102() {
			if ( ! class_exists( 'Tribe__Image__Plus__Main' ) ) {
				return;
			}

			if ( ! defined( 'Tribe__Image__Plus__Main::VERSION' ) ) {
				return;
			}

			if ( ! version_compare( Tribe__Image__Plus__Main::VERSION, '1.0.2', '<=' ) ) {
				return;
			}

			remove_action( 'plugins_loaded', [ Tribe__Image__Plus__Main::instance(), 'plugins_loaded' ], 0 );
		}

		/**
		 * Add filters and actions
		 */
		protected function addHooks() {
			/**
			 * It's important that anything related to Text Domain happens at `init`
			 * because of the way $wp_locale works
			 */
			add_action( 'init', [ $this, 'setup_l10n_strings' ], 5 );
			add_action( 'tribe_load_text_domains', [ $this, 'load_text_domain' ], 5 );

			// Since TEC is active, change the base page for the Event Settings page
			Tribe__Settings::$parent_page = 'edit.php';

			// Load Rewrite
			add_action( 'plugins_loaded', [ Tribe__Events__Rewrite::instance(), 'hooks' ] );

			// Trigger smart activation of V2 after we triggered the Update version on Init@P10.
			add_action( 'init', 'tribe_events_views_v2_smart_activation', 25 );

			add_action( 'init', [ $this, 'init' ], 10 );
			add_action( 'admin_init', [ $this, 'admin_init' ] );

			add_filter( 'tribe_events_before_html', [ $this, 'before_html_data_wrapper' ] );
			add_filter( 'tribe_events_after_html', [ $this, 'after_html_data_wrapper' ] );

			// Styling
			add_filter( 'post_class', [ $this, 'post_class' ] );
			add_filter( 'body_class', [ $this, 'body_class' ] );
			add_filter( 'admin_body_class', [ $this, 'admin_body_class' ] );

			add_filter( 'post_type_archive_link', [ $this, 'event_archive_link' ], 10, 2 );
			add_filter( 'query_vars', [ $this, 'eventQueryVars' ] );
			add_action( 'parse_query', [ $this, 'setDisplay' ], 51, 1 );
			add_filter( 'bloginfo_rss', [ $this, 'add_space_to_rss' ] );
			add_filter( 'post_updated_messages', [ $this, 'updatePostMessage' ] );

			/* Add nav menu item - thanks to https://wordpress.org/extend/plugins/cpt-archives-in-nav-menus/ */
			add_filter( 'nav_menu_items_' . self::POSTTYPE, [ $this, 'add_events_checkbox_to_menu' ], null, 3 );
			add_filter( 'wp_nav_menu_objects', [ $this, 'add_current_menu_item_class_to_events' ], null, 2 );

			add_action( 'template_redirect', [ $this, 'redirect_past_upcoming_view_urls' ], 9 );

			/* Setup Tribe Events Bar */
			add_filter( 'tribe-events-bar-views', [ $this, 'setup_listview_in_bar' ], 1, 1 );
			add_filter( 'tribe-events-bar-views', [ $this, 'setup_gridview_in_bar' ], 5, 1 );
			add_filter( 'tribe-events-bar-views', [ $this, 'setup_dayview_in_bar' ], 15, 1 );

			add_filter( 'tribe-events-bar-filters', [ $this, 'setup_date_search_in_bar' ], 1, 1 );
			add_filter( 'tribe-events-bar-filters', [ $this, 'setup_keyword_search_in_bar' ], 1, 1 );

			add_filter( 'tribe-events-bar-views', [ $this, 'remove_hidden_views' ], 9999, 2 );
			/* End Setup Tribe Events Bar */

			add_action( 'admin_menu', [ $this, 'addEventBox' ] );
			add_action( 'wp_insert_post', [ $this, 'addPostOrigin' ], 10, 2 );
			add_action( 'save_post', [ $this, 'addEventMeta' ], 15, 2 );

			/* Registers the list widget */
			add_action( 'widgets_init', [ $this, 'register_list_widget' ], 90 );

			add_action( 'save_post_' . Tribe__Events__Venue::POSTTYPE, [ $this, 'save_venue_data' ], 16, 2 );
			add_action( 'save_post_' . Tribe__Events__Organizer::POSTTYPE, [ $this, 'save_organizer_data' ], 16, 2 );
			add_action(
				'save_post_' . self::POSTTYPE, [
					Tribe__Events__Dates__Known_Range::instance(),
					'maybe_update_known_range',
				]
			);
			add_action(
				'tribe_events_csv_import_complete', [
					Tribe__Events__Dates__Known_Range::instance(),
					'rebuild_known_range',
				]
			);
			add_action( 'publish_' . self::POSTTYPE, [ $this, 'publishAssociatedTypes' ], 25, 2 );
			add_action( 'delete_post', [ Tribe__Events__Dates__Known_Range::instance(), 'maybe_rebuild_known_range' ] );
			add_action( 'tribe_events_post_errors', [ 'Tribe__Events__Post_Exception', 'displayMessage' ] );
			add_action( 'tribe_settings_top', [ 'Tribe__Events__Options_Exception', 'displayMessage' ] );
			add_action( 'trash_' . Tribe__Events__Venue::POSTTYPE, [ $this, 'cleanupPostVenues' ] );
			add_action( 'trash_' . Tribe__Events__Organizer::POSTTYPE, [ $this, 'cleanupPostOrganizers' ] );
			add_action( 'wp_ajax_tribe_event_validation', [ $this, 'ajax_form_validate' ] );
			add_action( 'plugins_loaded', [ 'Tribe__Cache_Listener', 'instance' ] );
			add_action( 'plugins_loaded', [ 'Tribe__Cache', 'setup' ] );
			add_action( 'plugins_loaded', [ 'Tribe__Support', 'getInstance' ] );

			add_filter( 'tribe_tracker_post_types', [ $this, 'filter_tracker_event_post_types' ] );
			add_filter( 'tribe_tracker_taxonomies', [ $this, 'filter_tracker_event_taxonomies' ] );

			if ( ! tribe( 'context' )->doing_ajax() ) {
				add_action( 'current_screen', [ $this, 'init_admin_list_screen' ] );
			} else {
				add_action( 'admin_init', [ $this, 'init_admin_list_screen' ] );
			}

			// Load organizer and venue editors
			add_action( 'admin_menu', [ $this, 'addVenueAndOrganizerEditor' ] );

			add_action( 'tribe_venue_table_top', [ $this, 'display_rich_snippets_helper' ], 5 );

			add_action( 'template_redirect', [ $this, 'template_redirect' ] );

			add_action( 'wp', [ $this, 'issue_noindex' ] );
			add_action( 'plugin_row_meta', [ $this, 'addMetaLinks' ], 10, 2 );
			// organizer and venue
			if ( ! defined( 'TRIBE_HIDE_UPSELL' ) || ! TRIBE_HIDE_UPSELL ) {
				add_action( 'wp_dashboard_setup', [ $this, 'dashboardWidget' ] );
				add_action( 'tribe_events_cost_table', [ $this, 'maybeShowMetaUpsell' ] );
			}

			add_action(
				'load-tribe_events_page_' . Tribe__Settings::$parent_slug,
				[
					'Tribe__Events__Amalgamator',
					'listen_for_migration_button',
				],
				10,
				0
			);
			add_action( 'tribe_settings_after_save', [ $this, 'flushRewriteRules' ] );

			add_action( 'update_option_' . Tribe__Main::OPTIONNAME, [ $this, 'fix_all_day_events' ], 10, 2 );

			add_action( 'wp_before_admin_bar_render', [ $this, 'add_toolbar_items' ], 10 );
			add_action( 'all_admin_notices', [ $this, 'addViewCalendar' ] );
			add_action( 'admin_head', [ $this, 'setInitialMenuMetaBoxes' ], 500 );
			add_action(
				'plugin_action_links_' . trailingslashit( $this->plugin_dir ) . 'the-events-calendar.php',
				[
					$this,
					'addLinksToPluginActions',
				]
			);

			// override default wp_terms_checklist arguments to prevent checked items from bubbling to the top. Instead, retain hierarchy.
			add_filter( 'wp_terms_checklist_args', [ $this, 'prevent_checked_on_top_terms' ], 10, 2 );

			add_action( 'tribe_events_pre_get_posts', [ $this, 'set_tribe_paged' ] );

			// Upgrade material.
			add_action( 'init', [ $this, 'run_updates' ], 0, 0 );

			if ( defined( 'WP_LOAD_IMPORTERS' ) && WP_LOAD_IMPORTERS ) {
				add_filter( 'wp_import_post_data_raw', [ $this, 'filter_wp_import_data_before' ], 10, 1 );
				add_filter( 'wp_import_post_data_processed', [ $this, 'filter_wp_import_data_after' ], 10, 1 );
			}

			add_action( 'plugins_loaded', [ $this, 'init_day_view' ], 2 );

			add_action( 'plugins_loaded', [ 'Tribe__Events__Templates', 'init' ] );
			tribe( 'tec.bar' );

			add_action( 'init', [ $this, 'filter_cron_schedules' ] );

			add_action( 'plugins_loaded', [ 'Tribe__Events__Event_Tickets__Main', 'instance' ] );

			// Add support for tickets plugin
			add_action( 'tribe_tickets_ticket_added', [ 'Tribe__Events__API', 'update_event_cost' ] );
			add_action( 'tribe_tickets_ticket_deleted', [ 'Tribe__Events__API', 'update_event_cost' ] );
			add_filter( 'tribe_tickets_default_end_date', [ $this, 'default_end_date_for_tickets' ], 10, 2 );

			add_filter( 'tribe_post_types', [ $this, 'filter_post_types' ] );
			add_filter( 'tribe_is_post_type_screen_post_types', [ $this, 'is_post_type_screen_post_types' ] );
			add_filter( 'tribe_currency_symbol', [ $this, 'maybe_set_currency_symbol_with_post' ], 10, 2 );
			add_filter( 'tribe_reverse_currency_position', [ $this, 'maybe_set_currency_position_with_post' ], 10, 2 );

			// Settings page hooks
			add_action( 'tribe_settings_do_tabs', [ $this, 'do_addons_api_settings_tab' ] );
			add_filter( 'tribe_general_settings_tab_fields', [ $this, 'general_settings_tab_fields' ] );
			add_filter( 'tribe_display_settings_tab_fields', [ $this, 'display_settings_tab_fields' ] );
			add_filter( 'tribe_settings_url', [ $this, 'tribe_settings_url' ] );
			add_action( 'tribe_settings_do_tabs', [ $this, 'do_upgrade_tab' ] );

			// Setup Help Tab texting
			add_action( 'tribe_help_pre_get_sections', [ $this, 'add_help_section_feature_box_content' ] );
			add_action( 'tribe_help_pre_get_sections', [ $this, 'add_help_section_support_content' ] );
			add_action( 'tribe_help_pre_get_sections', [ $this, 'add_help_section_extra_content' ] );

			// Google Maps API key setting
			$google_maps_api_key = Tribe__Events__Google__Maps_API_Key::instance();
			add_filter( 'tribe_addons_tab_fields', [ $google_maps_api_key, 'filter_tribe_addons_tab_fields' ] );
			add_filter(
				'tribe_events_google_maps_api',
				[
					$google_maps_api_key,
					'filter_tribe_events_google_maps_api',
				]
			);
			add_filter(
				'tribe_events_pro_google_maps_api',
				[
					$google_maps_api_key,
					'filter_tribe_events_google_maps_api',
				]
			);
			add_filter( 'tribe_field_value', [ $google_maps_api_key, 'populate_field_with_default_api_key' ], 10, 2 );
			add_filter(
				'tribe_field_tooltip',
				[
					$google_maps_api_key,
					'populate_field_tooltip_with_helper_text',
				],
				10,
				2
			);

			// Preview handling
			add_action( 'template_redirect', [ Tribe__Events__Revisions__Preview::instance(), 'hook' ] );

			// Register all of the post types in the chunker and start the chunker
			add_filter( 'tribe_meta_chunker_post_types', [ $this, 'filter_meta_chunker_post_types' ] );

			// Purge old events
			add_action( 'update_option_' . Tribe__Main::OPTIONNAME, tribe_callback( 'tec.event-cleaner', 'move_old_events_to_trash' ), 10, 2 );
			add_action( 'update_option_' . Tribe__Main::OPTIONNAME, tribe_callback( 'tec.event-cleaner', 'permanently_delete_old_events' ), 10, 2 );

			// Register slug conflict notices (but test to see if tribe_notice() is indeed available, in case another plugin
			// is hosting an earlier version of tribe-common which is already active)
			//
			// @todo [BTRIA-603]: Remove this safety check when we're confident the risk has diminished.
			if ( function_exists( 'tribe_notice' ) ) {
				tribe_notice(
					'archive-slug-conflict',
					[
						$this,
						'render_notice_archive_slug_conflict',
					],
					'dismiss=1&type=error'
				);
			}

			// Prevent duplicate venues and organizers from being created on event preview.
			add_action( 'tribe_events_after_view', [ $this, 'maybe_add_preview_venues_and_organizers' ] );

			/**
			 * Expire notices
			 */
			add_action( 'transition_post_status', [ $this, 'action_expire_archive_slug_conflict_notice' ], 10, 3 );

			tribe( 'tec.featured_events.query_helper' )->hook();
			tribe( 'tec.featured_events.permalinks_helper' )->hook();

			// Add support for positioning the main events view on the site homepage
			tribe( 'tec.front-page-view' )->hook();

			tribe( 'events-aggregator.main' );
			tribe( 'tec.shortcodes.event-details' );
			tribe( 'tec.ignored-events' );
			tribe( 'tec.assets' );
			tribe( 'tec.iCal' );
			tribe( 'tec.rest-v1.main' );
			tribe( 'tec.gutenberg' );
			tribe( 'tec.admin.notice.timezones' );
			tribe( 'tec.admin.notice.marketing' );
			tribe( Tribe\Events\Admin\Notice\Legacy_Views_Deprecation::class );
			tribe( 'tec.privacy' );
			tribe( Tribe__Events__Capabilities::class );
		}

		/**
		 * Run on applied action init
		 */
		public function init() {
			// Start the integrations manager
			Tribe__Events__Integrations__Manager::instance()->load_integrations();

			$rewrite = Tribe__Events__Rewrite::instance();

			$venue                       = Tribe__Events__Venue::instance();
			$organizer                   = Tribe__Events__Organizer::instance();
			$this->postVenueTypeArgs     = $venue->post_type_args;
			$this->postOrganizerTypeArgs = $organizer->post_type_args;

			$this->pluginName = $this->plugin_name            = esc_html__( 'The Events Calendar', 'the-events-calendar' );
			$this->rewriteSlug                                = $this->getRewriteSlug();
			$this->rewriteSlugSingular                        = $this->getRewriteSlugSingular();
			$this->category_slug                              = $this->get_category_slug();
			$this->tag_slug                                   = $this->get_tag_slug();
			$this->taxRewriteSlug                             = $this->rewriteSlug . '/' . $this->category_slug;
			$this->tagRewriteSlug                             = $this->rewriteSlug . '/' . $this->tag_slug;
			$this->monthSlug                                  = sanitize_title( __( 'month', 'the-events-calendar' ) );
			$this->listSlug                               	  = sanitize_title( __( 'list', 'the-events-calendar' ) );
			$this->upcomingSlug                               = sanitize_title( __( 'upcoming', 'the-events-calendar' ) );
			$this->pastSlug                                   = sanitize_title( __( 'past', 'the-events-calendar' ) );
			$this->daySlug                                    = sanitize_title( __( 'day', 'the-events-calendar' ) );
			$this->todaySlug                                  = sanitize_title( __( 'today', 'the-events-calendar' ) );
			$this->featured_slug                              = sanitize_title( _x( 'featured', 'featured events slug', 'the-events-calendar' ) );
			$this->all_slug                                   = sanitize_title( _x( 'all', 'all events slug', 'the-events-calendar' ) );

			$this->singular_venue_label                       = $this->get_venue_label_singular();
			$this->plural_venue_label                         = $this->get_venue_label_plural();
			$this->singular_organizer_label                   = $this->get_organizer_label_singular();
			$this->plural_organizer_label                     = $this->get_organizer_label_plural();
			$this->singular_event_label                       = $this->get_event_label_singular();
			$this->plural_event_label                         = $this->get_event_label_plural();
			$this->singular_event_label_lowercase             = tribe_get_event_label_singular_lowercase();
			$this->plural_event_label_lowercase               = tribe_get_event_label_plural_lowercase();

			$this->post_type_args['rewrite']['slug']          = $rewrite->prepare_slug( $this->rewriteSlugSingular, self::POSTTYPE, false );
			$this->post_type_args['show_in_rest']             = current_user_can( 'manage_options' );
			$this->currentDay                                 = '';
			$this->errors                                     = '';

			$this->default_values                             = apply_filters( 'tribe_events_default_value_strategy', new Tribe__Events__Default_Values() );

			Tribe__Events__Query::init();
			Tribe__Events__Backcompat::init();
			Tribe__Credits::init();
			Tribe__Events__Timezones::init();
			$this->registerPostType();
			tribe( 'tec.admin.event-meta-box' )->display_wp_custom_fields_metabox();

			Tribe__Debug::debug( sprintf( esc_html__( 'Initializing Tribe Events on %s', 'the-events-calendar' ), date( 'M, jS \a\t h:m:s a' ) ) );
			$this->maybeSetTECVersion();

			$this->run_scheduler();
		}

		/**
		 * Initializes any admin-specific code (expects to be called when admin_init fires).
		 */
		public function admin_init() {
			global $pagenow;

			$this->timezone_settings = new Tribe__Events__Admin__Timezone_Settings;

			// Right now it only makes sense to add these extra upgrade notices within the plugins.php screen
			if ( 'plugins.php' === $pagenow ) {
				new Tribe__Admin__Notice__Plugin_Upgrade_Notice(
					self::VERSION,
					$this->plugin_dir . 'the-events-calendar.php'
				);
			}
		}

		/**
		 * Updater object accessor method
		 */
		public function updater() {
			static $updater;

			if ( ! $updater ) {
				$updater = new Tribe__Events__Updater( self::VERSION );
			}

			return $updater;
		}

		public function run_updates() {
			if ( $this->updater()->update_required() ) {
				$this->updater()->do_updates();
			}
		}

		/**
		 * @return Tribe__Admin__Activation_Page
		 */
		public function activation_page() {
			// Setup the activation page only if the relevant class exists (in some edge cases, if another
			// plugin hosting an earlier version of tribe-common is already active we could hit fatals
			// if we don't take this precaution).
			//
			// @todo [BTRIA-604]: Remove class_exists() test once enough time has elapsed and the risk has reduced.
			if ( empty( $this->activation_page ) && class_exists( 'Tribe__Admin__Activation_Page' ) ) {
				$this->activation_page = new Tribe__Admin__Activation_Page(
					[
						'slug'                  => 'the-events-calendar',
						'activation_transient'  => '_tribe_events_activation_redirect',
						'version'               => self::VERSION,
						'plugin_path'           => $this->plugin_dir . 'the-events-calendar.php',
						'version_history_slug'  => 'previous_ecp_versions',
						'update_page_title'     => __( 'Welcome to The Events Calendar!', 'the-events-calendar' ),
						'update_page_template'  => $this->plugin_path . 'src/admin-views/admin-update-message.php',
						'welcome_page_title'    => __( 'Welcome to The Events Calendar!', 'the-events-calendar' ),
						'welcome_page_template' => $this->plugin_path . 'src/admin-views/admin-welcome-message.php',
					]
				);
			}

			return $this->activation_page;
		}

		/**
		 * before_html_data_wrapper adds a persistent tag to wrap the event display with a
		 * way for jQuery to maintain state in the dom. Also has a hook for filtering data
		 * attributes for inclusion in the dom
		 *
		 * @param  string $html
		 *
		 * @return string
		 */
		public function before_html_data_wrapper( $html ) {

			if ( ! $wp_query = tribe_get_global_query_object() ) {
				return;
			}

			if ( ! $this->show_data_wrapper['before'] ) {
				return $html;
			}

			/*
			 * The category might come from a query for a taxonomy archive or as
			 * an additional query variable: let's check both.
			 */
			$event_taxonomy = self::instance()->get_event_taxonomy();
			$category       = '';
			if ( is_tax( $event_taxonomy ) ) {
				$category = get_query_var( 'term' );
			} elseif ( $tribe_cat = $wp_query->get( $event_taxonomy, false ) ) {
				$category = $tribe_cat;
			}

			$data_attributes = [
				'live_ajax'         => 'automatic' === tribe_get_option( 'liveFiltersUpdate', 'automatic' ) ? 1 : 0,
				'datepicker_format' => \Tribe__Date_Utils::get_datepicker_format_index(),
				'category'          => $category,
				'featured'          => tribe( 'tec.featured_events' )->is_featured_query(),
			];
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
		 * after_html_data_wrapper close out the persistent dom wrapper
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
		 * When a post transitions from a post_status to another, we remove the archive-slug-conflict notice
		 *
		 * @param  string $new_status New Status on Post
		 * @param  string $old_status Old Status on Post
		 * @param  int|WP_Post $post  A Post ID or Post Object
		 *
		 * @return bool
		 */
		public function action_expire_archive_slug_conflict_notice( $new_status, $old_status, $post ) {
			// If there is no change we bail
			if ( $new_status === $old_status ) {
				return false;
			}

			$post = get_post( $post );
			// translators: For compatibility with WPML and other multilingual plugins, not to be translated directly on .mo files.
			$archive_slug = _x( Tribe__Settings_Manager::get_option( 'eventsSlug', 'events' ), 'Archive Events Slug', 'the-events-calendar' );

			// is it a real post?
			if ( ! $post instanceof WP_Post ) {
				return false;
			}

			// Is it a conflict?
			if ( $archive_slug !== $post->post_name ) {
				return false;
			}

			return Tribe__Admin__Notices::instance()->undismiss( 'archive-slug-conflict' );
		}

		/**
		 * Displays the Archive conflict notice using Tribe__Admin__Notices code
		 *
		 * @return string
		 */
		public function render_notice_archive_slug_conflict() {
			// translators: For compatibility with WPML and other multilingual plugins, not to be translated directly on .mo files.
			$archive_slug   = _x( Tribe__Settings_Manager::get_option( 'eventsSlug', 'events' ), 'Archive Events Slug', 'the-events-calendar' );
			$conflict_query = new WP_Query(
				[
					'name'                   => $archive_slug,
					'post_type'              => 'any',
					'post_status'            => [ 'publish', 'private', 'inherit' ],
					'no_found_rows'          => true,
					'update_post_meta_cache' => false,
					'update_post_term_cache' => false,
					'posts_per_page'         => 1,
					'post_parent'            => 0,
				]
			);

			if ( ! $conflict_query->have_posts() ) {
				return false;
			}

			// Set the Conflicted Post
			$conflict = $conflict_query->post;

			// Fetch the Post Type and Post Name
			$post_type = get_post_type_object( $conflict->post_type );
			$name      = empty( $post_type->labels->singular_name ) ? ucfirst( $conflict->post_type ) : $post_type->labels->singular_name;

			// What's happening?
			$page_title = apply_filters( 'the_title', $conflict->post_title, $conflict->ID );
			$line_1     = sprintf( __( 'The %3$s "%1$s" uses the "/%2$s" slug: the Events Calendar plugin will show its calendar in place of the page.', 'the-events-calendar' ), $page_title, $archive_slug, $name );

			// What the user can do
			$edit_post_link = sprintf( __( 'Ask the site administrator to edit the %s slug', 'the-events-calendar' ), $name );
			if ( isset( $post_type->cap->edit_posts ) && current_user_can( $post_type->cap->edit_posts ) ) {
				$edit_post_link = sprintf( '<a href="%1$s">%2$s</a>', get_edit_post_link( $conflict->ID ), sprintf( __( 'Edit the %s slug', 'the-events-calendar' ), $name ) );
			}

			$settings_cap       = apply_filters( 'tribe_settings_req_cap', 'manage_options' );
			$edit_settings_link = __( ' ask the site administrator to set a different Events URL slug.', 'the-events-calendar' );

			if ( current_user_can( $settings_cap ) ) {
				$admin_slug         = apply_filters( 'tribe_settings_admin_slug', 'tribe-common' );
				$setting_page_link  = apply_filters( 'tribe_settings_url', admin_url( 'edit.php?page=' . $admin_slug . '#tribe-field-eventsSlug' ) );
				$edit_settings_link = sprintf( '<a href="%1$s">%2$s</a>', $setting_page_link, __( 'edit Events settings.', 'the-events-calendar' ) );
			}

			$line_2 = sprintf( __( '%1$s or %2$s', 'the-events-calendar' ), $edit_post_link, $edit_settings_link );

			return Tribe__Admin__Notices::instance()->render( 'archive-slug-conflict', sprintf( '<p>%s</p><p>%s</p>', $line_1, $line_2 ) );
		}

		/**
		 * Initialize the addons api settings tab
		 */
		public function do_addons_api_settings_tab() {
			include_once $this->plugin_path . 'src/admin-views/tribe-options-addons-api.php';
		}

		/**
		 * should we show the upgrade nags?
		 *
		 * @since 4.9.12
		 *
		 * @return boolean
		 */
		public function show_upgrade() {
			$show_tab = current_user_can( 'activate_plugins' );

			/**
			 * Provides an opportunity to override the decision to show or hide the upgrade tab
			 *
			 * Normally it will only show if the current user has the "activate_plugins" capability
			 * and there are some currently-activated premium plugins.
			 *
			 * @since 4.9.12
			 *
			 * @param bool $show_tab True or False for showing the Upgrade Tab.
			 */
			if ( ! apply_filters( 'tribe_events_show_upgrade_tab', $show_tab ) ) {
				return false;
			}

			/**
			 * This constant was introduced in the View Alpha
			 * for agencies or others who'd like to hide the V2 Views upgrade prompt.
			 * Let them set a constant in wp-config that will be respected.
			 */
			if ( defined( 'TRIBE_HIDE_V2_VIEWS_UPGRADE' ) ) {
				return false;
			}

			return true;
		}

		/**
		 * Create the upgrade tab
		 *
		 * @since 4.9.12
		 */
		public function do_upgrade_tab() {
			if ( ! $this->show_upgrade() ) {
				return;
			}

			tribe_asset(
				self::instance(),
				'tribe-admin-upgrade-page',
				'admin-upgrade-page.js',
				[ 'tribe-common' ],
				'admin_enqueue_scripts',
				[
					'localize' => [
						'name' => 'tribe_upgrade',
						'data' => [
							'v2_is_enabled' => tribe_events_views_v2_is_enabled(),
							'button_text' => __( 'Upgrade your calendar views', 'the-events-calendar' ),
						],
					],
				]
			);

			/**
			 * Get Upgrade tab template.
			 */
			ob_start();
			include_once $this->plugin_path . 'src/admin-views/tribe-options-upgrade.php';
			$upgrade_tab_html = ob_get_clean();

			$upgrade_tab = [
				'info-box-description' => [
					'type' => 'html',
					'html' => $upgrade_tab_html,
				],
				'views_v2_enabled' => [
					'type'            => 'checkbox_bool',
					'default'         => true,
					'value'           => true,
					'validation_type' => 'boolean',
					'conditional'     => true,
				],
			];

			/**
			 * Allows the fields displayed in the upgrade tab to be modified.
			 *
			 * @since 4.9.12
			 *
			 * @param array $upgrade_tab Array of fields used to setup the Upgrade Tab.
			 */
			$upgrade_fields = apply_filters( 'tribe_upgrade_fields', $upgrade_tab );

			new Tribe__Settings_Tab(
				'upgrade', esc_html__( 'Upgrade', 'tribe-common' ),
				[
					'priority'      => 100,
					'fields'        => $upgrade_fields,
					'network_admin' => is_network_admin(),
					'show_save'     => true,
				]
			);
		}

		/**
		 * By default Tribe__Tracker won't track Event Post Types, so we add them here.
		 *
		 * @since  4.5
		 *
		 * @param  array $post_types
		 *
		 * @return array
		 */
		public function filter_tracker_event_post_types( array $post_types ) {
			$post_types[] = self::POSTTYPE;
			$post_types[] = Tribe__Events__Venue::POSTTYPE;
			$post_types[] = Tribe__Events__Organizer::POSTTYPE;

			return $post_types;
		}

		/**
		 * By default Tribe__Tracker won't track our Post Types taxonomies, so we add them here.
		 *
		 * @since  4.5
		 *
		 * @param  array $taxonomies
		 *
		 * @return array
		 */
		public function filter_tracker_event_taxonomies( array $taxonomies ) {
			$taxonomies[] = 'post_tag';
			$taxonomies[] = self::TAXONOMY;

			return $taxonomies;
		}

		/**
		 * Append the text about The Events Calendar to the feature box on the Help page
		 *
		 * @filter "tribe_help_pre_get_sections"
		 * @param Tribe__Admin__Help_Page $help The Help Page Instance
		 * @return void
		 */
		public function add_help_section_feature_box_content( $help ) {
			$link = '<a href="https://evnt.is/18j8" target="_blank">' . esc_html__( 'New User Primer', 'the-events-calendar' ) . '</a>';

			$help->add_section_content( 'feature-box', sprintf( __( 'We are committed to helping make your calendar spectacular and have a wealth of resources available, including a handy %s to get your calendar up and running.', 'the-events-calendar' ), $link ) );
		}

		/**
		 * Append the text about The Events Calendar to the support section on the Help page
		 *
		 * @filter "tribe_help_pre_get_sections"
		 * @param Tribe__Admin__Help_Page $help The Help Page Instance
		 * @return void
		 */
		public function add_help_section_support_content( $help ) {
			$help->add_section_content( 'support', '<strong>' . esc_html__( 'Support for The Events Calendar', 'the-events-calendar' ) . '</strong>', 15 );

			$help->add_section_content(
				'support',
				[
					sprintf(
						__(
							'%s: A thorough walkthrough of The Events Calendar and the settings that are available to you.',
							'the-events-calendar'
						),
                        '<strong><a href="https://evnt.is/18je" target="_blank">' . esc_html__( 'Settings overview', 'the-events-calendar' ) . '</a></strong>'
					),

					sprintf(
						__(
							'%s: A complete look at the features you can expect to see right out of the box as well as how to use them.',
							'the-events-calendar'
						),
                        '<strong><a href="https://evnt.is/18jc" target="_blank">' . esc_html__( 'Features overview', 'the-events-calendar' ) . '</a></strong>'
					),

					sprintf(
						__(
							'%s: Our most comprehensive outline for customizing the calendar to suit your needs, including custom layouts and styles.',
							'the-events-calendar'
						),
                        '<strong><a href="https://evnt.is/18jg" target="_blank">' . esc_html__( "Themer's Guide", 'the-events-calendar' ) . '</a></strong>'
					),

					sprintf(
						__(
							'%s: An overview of the default templates and styles that are included in the plugin, as well as how to change them.',
							'the-events-calendar'
						),
                        '<strong><a href="https://evnt.is/18jd" target="_blank">' . esc_html__( 'Using stylesheets and page templates', 'the-events-calendar' ) . '</a></strong>'
					),

					sprintf(
						__(
							'%s: Do you see an issue with your calendar? Go here first to find where it’s coming from and how to fix it.',
							'the-events-calendar'
						),
                        '<strong><a href="https://evnt.is/18jb" target="_blank">' . esc_html__( 'Troubleshooting common problems', 'the-events-calendar' ) . '</a></strong>'
					),

					sprintf(
						__(
							'%s: Code and guides for customizing your calendar in useful and interesting ways.',
							'the-events-calendar'
						),
                        '<strong><a href="https://evnt.is/18ja" target="_blank">' . esc_html__( 'Customizing the Events plugins', 'the-events-calendar' ) . '</a></strong>'
					),
				],
                15
			);
		}

		/**
		 * Append the text about The Events Calendar to the Extra Help section on the Help page
		 *
		 * @filter "tribe_help_pre_get_sections"
		 * @param Tribe__Admin__Help_Page $help The Help Page Instance
		 * @return void
		 */
		public function add_help_section_extra_content( $help ) {
			if ( ! $help->is_active( [ 'events-calendar-pro', 'event-tickets-plus' ] ) && $help->is_active( 'event-tickets' ) ) {

				$link_tec = '<a href="https://wordpress.org/support/plugin/the-events-calendar/" target="_blank">' . esc_html__( 'The Events Calendar', 'the-events-calendar' ) . '</a>';
				$link_et = '<a href="https://wordpress.org/support/plugin/event-tickets/" target="_blank">' . esc_html__( 'Events Tickets', 'the-events-calendar' ) . '</a>';
				$help->add_section_content( 'extra-help', sprintf( __( 'If you have tried the above steps and are still having trouble, you can post a new thread to our WordPress.org forums for %1$s or %2$s. Our support staff monitors these forums once a week and would be happy to assist you there. ', 'the-events-calendar' ), $link_tec, $link_et ), 20 );

				$link = '<a href="https://evnt.is/4w/" target="_blank">' . esc_html__( 'premium support on our website', 'the-events-calendar' ) . '</a>';
				$help->add_section_content( 'extra-help', sprintf( __( '<strong>Looking for more immediate support?</strong> We offer %s with the purchase of any of our premium plugins. Pick up a license and you can post there directly and expect a response within 24-48 hours during weekdays', 'the-events-calendar' ), $link ), 20 );

			} elseif ( ! $help->is_active( [ 'events-calendar-pro', 'event-tickets' ] ) ) {

				$link = '<a href="https://wordpress.org/support/plugin/the-events-calendar" target="_blank">' . esc_html__( 'open-source forum on WordPress.org', 'the-events-calendar' ) . '</a>';
				$help->add_section_content( 'extra-help', sprintf( __( 'If you have tried the above steps and are still having trouble, you can post a new thread to our %s. Our support staff monitors these forums once a week and would be happy to assist you there.', 'the-events-calendar' ), $link ), 20 );

				$link_forum = '<a href="https://evnt.is/4w/" target="_blank">' . esc_html__( 'premium support on our website', 'the-events-calendar' ) . '</a>';
				$link_plus = '<a href="https://evnt.is/18n0" target="_blank">' . esc_html__( 'Events Calendar PRO', 'the-events-calendar' ) . '</a>';
				$help->add_section_content( 'extra-help', sprintf( __( '<strong>Looking for more immediate support?</strong> We offer %1$s with the purchase of any of our premium plugins (like %2$s). Pick up a license and you can post there directly and expect a response within 24-48 hours during weekdays.', 'the-events-calendar' ), $link_forum, $link_plus ), 20 );

			} else {

				$link = '<a href="https://evnt.is/4w/" target="_blank">' . esc_html__( 'post a thread', 'the-events-calendar' ) . '</a>';
				$help->add_section_content( 'extra-help', sprintf( __( 'If you have a valid license for one of our paid plugins, you can %s in our premium support forums. Our support team monitors the forums and will respond to your thread within 24-48 hours (during the week).', 'the-events-calendar' ), $link ), 20 );

			}
		}

		/**
		 * Allow users to specify their own plural label for Venues
		 *
		 * @return string
		 */
		public function get_venue_label_plural() {
			return Tribe__Events__Venue::instance()->get_venue_label_plural();
		}

		/**
		 * Allow users to specify their own singular label for Venues
		 * @return string
		 */
		public function get_venue_label_singular() {
			return Tribe__Events__Venue::instance()->get_venue_label_singular();
		}

		/**
		 * Allow users to specify their own plural label for Organizers
		 * @return string
		 */
		public function get_organizer_label_plural() {
			return Tribe__Events__Organizer::instance()->get_organizer_label_plural();
		}

		/**
		 * Allow users to specify their own singular label for Organizers
		 * @return string
		 */
		public function get_organizer_label_singular() {
			return Tribe__Events__Organizer::instance()->get_organizer_label_singular();
		}

		/**
		 * Allow users to specify their own plural label for Events
		 * @return string
		 */
		public function get_event_label_plural() {
			return apply_filters( 'tribe_event_label_plural', esc_html__( 'Events', 'the-events-calendar' ) );
		}

		/**
		 * Allow users to specify their own singular label for Events
		 * @return string
		 */
		public function get_event_label_singular() {
			return apply_filters( 'tribe_event_label_singular', esc_html__( 'Event', 'the-events-calendar' ) );
		}

		/**
		 * Load the day view template tags
		 * Loaded late due to potential upgrade conflict since moving them from pro
		 *
		 * @todo [BTRIA-620]: move this require to be with the rest of the template tag includes in 3.9
		 */
		public function init_day_view() {
			// load day view functions
			require_once $this->plugin_path . 'src/functions/template-tags/day.php';
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

			if ( ! $wp_query = tribe_get_global_query_object() ) {
				return;
			}

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
				add_action( 'wp_head', [ $this, 'print_noindex_meta' ] );
			}
		}

		/**
		 * Prints a "noindex,follow" robots tag.
		 */
		public function print_noindex_meta() {
			echo ' <meta name="robots" content="noindex,follow" />' . "\n";
		}

		/**
		 * Set the Calendar Version in the options table if it's not already set.
		 */
		public function maybeSetTECVersion() {
			if ( version_compare( Tribe__Settings_Manager::get_option( 'latest_ecp_version' ), self::VERSION, '<' ) ) {
				$previous_versions   = Tribe__Settings_Manager::get_option( 'previous_ecp_versions' ) ? Tribe__Settings_Manager::get_option( 'previous_ecp_versions' ) : [];
				$previous_versions[] = ( Tribe__Settings_Manager::get_option( 'latest_ecp_version' ) ) ? Tribe__Settings_Manager::get_option( 'latest_ecp_version' ) : '0';

				Tribe__Settings_Manager::set_option( 'previous_ecp_versions', $previous_versions );
				Tribe__Settings_Manager::set_option( 'latest_ecp_version', self::VERSION );
			}
		}

		/**
		 * Trigger is_404 on single event if no events are found
		 */
		public function template_redirect() {
			if ( ! $wp_query = tribe_get_global_query_object() ) {
				return;
			}

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
			$url_parts = parse_url( $url );
			$safe_domains = $this->safe_redirect_domains();

			// if the site isn't a safe domain, spoofing is probably being attempted. Bail
			if ( ! in_array( $url_parts['host'], $safe_domains ) ) {
				return;
			}

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
		 * Provides a list of URLs that are considered safe for redirecting
		 */
		public function safe_redirect_domains() {
			$home      = home_url( '/' );
			$site      = site_url( '/' );
			$domains   = [];
			$domains[] = parse_url( $home, PHP_URL_HOST );
			$domains[] = parse_url( $site, PHP_URL_HOST );

			/**
			 * Filters the list of safe redirect domains
			 *
			 * @var array Array of domains that are safe to redirect to
			 */
			$domains = apply_filters( 'tribe_events_safe_redirect_domains', $domains );

			$domains = array_unique( $domains );

			return $domains;
		}

		/**
		 * Updates the start/end time on all day events to match the EOD cutoff
		 *
		 * @see 'update_option_'.Tribe__Main::OPTIONNAME
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
						$supported = version_compare( get_bloginfo( 'version' ), $this->min_wordpress, '>=' );
						break;
					case 'php' :
						$supported = version_compare( phpversion(), $this->min_php, '>=' );
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
				echo '<div class="error"><p>' . sprintf( esc_html__( 'Sorry, The Events Calendar requires WordPress %s or higher. Please upgrade your WordPress install.', 'the-events-calendar' ), $this->min_wordpress ) . '</p></div>';
			}
			if ( ! self::supportedVersion( 'php' ) ) {
				echo '<div class="error"><p>' . sprintf( esc_html__( 'Sorry, The Events Calendar requires PHP %s or higher. Talk to your Web host about moving you to a newer version of PHP.', 'the-events-calendar' ), $this->min_php ) . '</p></div>';
			}
		}

		/**
		 * Display Notice if Event Tickets is Running an Older Version
		 *
		 * @since 4.8
		 *
		 */
		public function compatibility_notice() {

			$mopath = $this->pluginDir . 'lang/';
			$domain = 'the-events-calendar';

			// If we don't have Common classes load the old fashioned way
			if ( ! class_exists( 'Tribe__Main' ) ) {
				load_plugin_textdomain( $domain, false, $mopath );
			} else {
				// This will load `wp-content/languages/plugins` files first
				Tribe__Main::instance()->load_text_domain( $domain, $mopath );
			}

			$url = add_query_arg(
				[
					'tab'       => 'plugin-information',
					'plugin'    => 'event-tickets',
					'TB_iframe' => 'true',
				],
				admin_url( 'plugin-install.php' )
			);

			echo '<div class="error"><p>'
			. sprintf(
				'%1s <a href="%2s" class="thickbox" title="%3s">%4s</a>.',
				esc_html__( 'To continue using The Events Calendar, please install the latest version of', 'the-events-calendar' ),
				esc_url( $url ),
				esc_html__( 'Event Tickets', 'the-events-calendar' ),
				esc_html__( 'Event Tickets', 'the-events-calendar' )
				) .
			'</p></div>';

		}

		/**
		 * Disable Pro from Running if TEC shuts down because Event Tickets is an older version
		 *
		 * @return bool
		 */
		public function disable_pro() {
			return false;
		}

		/**
		 * Prevents Extensions from running if ET is on an Older Version
		 *
		 * @since 4.9.3.1
		 *
		 */
		public function remove_exts() {

			remove_all_actions( 'tribe_plugins_loaded', 10 );

		}

		/**
		 * Display a missing-tribe-common library error
		 */
		public function missing_common_libs() {
			?>
			<div class="error">
				<p>
					<?php
					echo esc_html__(
						'It appears as if the tribe-common libraries cannot be found! The directory should be in the "common/" directory in the events calendar plugin.',
						'the-events-calendar'
					);
					?>
				</p>
			</div>
			<?php
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
			if ( ! $wp_query = tribe_get_global_query_object() ) {
				return;
			}

			foreach ( $items as $item ) {
				if ( $item->url == $this->getLink() ) {
					if ( is_singular( self::POSTTYPE )
						 || is_singular( Tribe__Events__Venue::POSTTYPE )
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
		 * @param object $post_type
		 *
		 * @return array
		 */
		public function add_events_checkbox_to_menu( $posts, $args, $post_type ) {
			global $_nav_menu_placeholder, $wp_rewrite;
			$_nav_menu_placeholder = ( 0 > $_nav_menu_placeholder ) ? intval( $_nav_menu_placeholder ) - 1 : - 1;
			$archive_slug          = $this->getLink();

			// In WP 4.4, $post_type is an object rather than an array
			if ( is_array( $post_type ) ) {
				// support pre WP 4.4
				$all_items = $post_type['args']->labels->all_items;
			} else {
				// support WP 4.4+
				$all_items = $post_type->labels->all_items;
			}

			array_unshift(
				$posts,
				(object) [
					'ID'           => 0,
					'object_id'    => $_nav_menu_placeholder,
					'post_content' => '',
					'post_excerpt' => '',
					'post_title'   => $all_items,
					'post_type'    => 'nav_menu_item',
					'type'         => 'custom',
					'url'          => $archive_slug,
				]
			);

			return $posts;
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
			if ( ! $wp_query = tribe_get_global_query_object() ) {
				return;
			}

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
		 * @todo move this to template class
		 */
		public function body_class( $classes ) {
			if ( get_query_var( 'post_type' ) == self::POSTTYPE ) {
				if ( ! is_admin() && 'automatic' === tribe_get_option( 'liveFiltersUpdate', 'automatic' ) ) {
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
		 * @todo move this to template class
		 */
		public function post_class( $classes ) {
			global $post;
			if ( is_object( $post ) && isset( $post->post_type ) && $post->post_type == self::POSTTYPE && $terms = get_the_terms( $post->ID, self::TAXONOMY ) ) {
				foreach ( $terms as $term ) {
					$classes[] = 'cat_' . sanitize_html_class( $term->slug, $term->term_taxonomy_id );
				}
			}

			return $classes;
		}

		/**
		 * Register the post types.
		 */
		public function registerPostType() {
			$this->generatePostTypeLabels();

			$post_type_args = $this->post_type_args;

			/**
			 * Filter the event post type arguments used in register_post_type.
			 *
			 * @param array $post_type_args
			 *
			 * @since 3.2
			 */
			$post_type_args['rewrite']['slug'] = $this->getRewriteSlugSingular();
			$post_type_args = apply_filters( 'tribe_events_register_event_type_args', $post_type_args );

			register_post_type( self::POSTTYPE, $post_type_args );

			// Setup Linked Posts singleton after we've set up the post types that we care about
			Tribe__Events__Linked_Posts::instance();

			$taxonomy_args = [
				'hierarchical'          => true,
				'update_count_callback' => '',
				'rewrite'               => [
					'slug'         => $this->rewriteSlug . '/' . $this->category_slug,
					'with_front'   => false,
					'hierarchical' => true,
				],
				'public'                => true,
				'show_ui'               => true,
				'labels'                => $this->taxonomyLabels,
				'capabilities'          => [
					'manage_terms' => 'publish_tribe_events',
					'edit_terms'   => 'publish_tribe_events',
					'delete_terms' => 'publish_tribe_events',
					'assign_terms' => 'edit_tribe_events',
				],
			];

			/**
			 * Filter the event category taxonomy arguments used in register_taxonomy.
			 *
			 * @param array $taxonomy_args
			 *
			 * @since 4.5.5
			 */
			$taxonomy_args = apply_filters( 'tribe_events_register_event_cat_type_args', $taxonomy_args );

			register_taxonomy( self::TAXONOMY, self::POSTTYPE, $taxonomy_args );

			if ( Tribe__Settings_Manager::get_option( 'showComments', 'no' ) == 'yes' ) {
				add_post_type_support( self::POSTTYPE, 'comments' );
			}

		}

		/**
		 * Get the rewrite slug
		 *
		 * @return string
		 */
		public function getRewriteSlug() {
			// translators: For compatibility with WPML and other multilingual plugins, not to be translated directly on .mo files.
			return sanitize_title( _x( Tribe__Settings_Manager::get_option( 'eventsSlug', 'events' ), 'Archive Events Slug', 'the-events-calendar' ) );
		}

		/**
		 * Get the single post rewrite slug
		 *
		 * @return string
		 */
		public function getRewriteSlugSingular() {
			// translators: For compatibility with WPML and other multilingual plugins, not to be translated directly on .mo files.
			return sanitize_title( _x( Tribe__Settings_Manager::get_option( 'singleEventSlug', 'event' ), 'Rewrite Singular Slug', 'the-events-calendar' ) );
		}

		/**
		 * Returns the string to be used as the taxonomy slug.
		 *
		 * @return string
		 */
		public function get_category_slug() {
			/**
			 * Provides an opportunity to modify the category slug.
			 *
			 * @var string
			 */
			return apply_filters( 'tribe_events_category_slug', sanitize_title( __( 'category', 'the-events-calendar' ) ) );
		}

		/**
		 * Returns the string to be used as the tag slug.
		 *
		 * @return string
		 */
		public function get_tag_slug() {
			/**
			 * Provides an opportunity to modify the tag slug.
			 *
			 * @var string
			 */
			return apply_filters( 'tribe_events_tag_slug', sanitize_title( __( 'tag', 'the-events-calendar' ) ) );
		}

		/**
		 * Get venue post type args
		 *
		 * @return array
		 */
		public function getVenuePostTypeArgs() {
			return Tribe__Events__Venue::instance()->post_type_args;
		}

		/**
		 * Get organizer post type args
		 *
		 * @return array
		 */
		public function getOrganizerPostTypeArgs() {
			return Tribe__Events__Organizer::instance()->post_type_args;
		}

		/**
		 * Generate custom post type labels
		 */
		protected function generatePostTypeLabels() {
			/**
			 * Provides an opportunity to modify the labels used for the event post type.
			 *
			 * @var array
			 */
			$this->post_type_args['labels'] = apply_filters(
				'tribe_events_register_event_post_type_labels',
				[
					'name'                     => $this->plural_event_label,
					'singular_name'            => $this->singular_event_label,
					'add_new'                  => esc_html__( 'Add New', 'the-events-calendar' ),
					'add_new_item'             => sprintf(
						esc_html__( 'Add New %s', 'the-events-calendar' ), $this->singular_event_label
					),
					'edit_item'                => sprintf(
						esc_html__( 'Edit %s', 'the-events-calendar' ), $this->singular_event_label
					),
					'new_item'                 => sprintf(
						esc_html__( 'New %s', 'the-events-calendar' ), $this->singular_event_label
					),
					'view_item'                => sprintf(
						esc_html__( 'View %s', 'the-events-calendar' ), $this->singular_event_label
					),
					'search_items'             => sprintf(
						esc_html__( 'Search %s', 'the-events-calendar' ), $this->plural_event_label
					),
					'not_found'                => sprintf(
						esc_html__( 'No %s found', 'the-events-calendar' ), $this->plural_event_label_lowercase
					),
					'not_found_in_trash'       => sprintf(
						esc_html__( 'No %s found in Trash', 'the-events-calendar' ), $this->plural_event_label_lowercase
					),
					'item_published'           => sprintf(
						esc_html__( '%s published.', 'the-events-calendar' ), $this->singular_event_label
					),
					'item_published_privately' => sprintf(
						esc_html__( '%s published privately.', 'the-events-calendar' ), $this->singular_event_label
					),
					'item_reverted_to_draft'   => sprintf(
						esc_html__( '%s reverted to draft.', 'the-events-calendar' ), $this->singular_event_label
					),
					'item_scheduled'           => sprintf(
						esc_html__( '%s scheduled.', 'the-events-calendar' ), $this->singular_event_label
					),
					'item_updated'             => sprintf(
						esc_html__( '%s updated.', 'the-events-calendar' ), $this->singular_event_label
					),
					'item_link'                => sprintf(
						// Translators: %s: Event singular.
						esc_html__( '%s Link', 'the-events-calendar' ), $this->singular_event_label
					),
					'item_link_description'    => sprintf(
						// Translators: %s: Event singular.
						esc_html__( 'A link to a particular %s.', 'the-events-calendar' ), $this->singular_event_label
					),
				]
			);

			/**
			 * Provides an opportunity to modify the labels used for the event category taxonomy.
			 *
			 * @var array
			 */
			$this->taxonomyLabels = apply_filters(
				'tribe_events_register_category_taxonomy_labels',
				[
					'name'              => sprintf(
						esc_html__( '%s Categories', 'the-events-calendar' ), $this->singular_event_label
					),
					'singular_name'     => sprintf(
						esc_html__( '%s Category', 'the-events-calendar' ), $this->singular_event_label
					),
					'search_items'      => sprintf(
						esc_html__( 'Search %s Categories', 'the-events-calendar' ), $this->singular_event_label
					),
					'all_items'         => sprintf(
						esc_html__( 'All %s Categories', 'the-events-calendar' ), $this->singular_event_label
					),
					'parent_item'       => sprintf(
						esc_html__( 'Parent %s Category', 'the-events-calendar' ), $this->singular_event_label
					),
					'parent_item_colon' => sprintf(
						esc_html__( 'Parent %s Category:', 'the-events-calendar' ), $this->singular_event_label
					),
					'edit_item'         => sprintf(
						esc_html__( 'Edit %s Category', 'the-events-calendar' ), $this->singular_event_label
					),
					'update_item'       => sprintf(
						esc_html__( 'Update %s Category', 'the-events-calendar' ), $this->singular_event_label
					),
					'add_new_item'      => sprintf(
						esc_html__( 'Add New %s Category', 'the-events-calendar' ), $this->singular_event_label
					),
					'new_item_name'     => sprintf(
						esc_html__( 'New %s Category Name', 'the-events-calendar' ), $this->singular_event_label
					),
					'item_link'         => sprintf(
						// Translators: %s: Event singular.
						esc_html__( '%s Category Link', 'the-events-calendar' ), $this->singular_event_label
					),
					'item_link_description' => sprintf(
						// Translators: %s: Event singular.
						esc_html__( 'A link to a particular %s category.', 'the-events-calendar' ), $this->singular_event_label
					),
				]
			);
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

			$messages[ self::POSTTYPE ] = [
				0 => '', // Unused. Messages start at index 1.
				1 => sprintf(
					esc_html__( '%1$s updated. %2$sView %1$s%3$s', 'the-events-calendar' ),
					esc_html( $this->singular_event_label ),
					'<a href="' . esc_url( get_permalink( $post_ID ) ) . '">',
					'</a>'
				),
				2 => esc_html__( 'Custom field updated.', 'the-events-calendar' ),
				3 => esc_html__( 'Custom field deleted.', 'the-events-calendar' ),
				4 => sprintf( esc_html__( '%s updated.', 'the-events-calendar' ), $this->singular_event_label ),
				/* translators: %s: date and time of the revision */
				5 => isset( $_GET['revision'] )
                    ? sprintf( esc_html__( '%1$s restored to revision from %2$s', 'the-events-calendar' ), $this->singular_event_label, wp_post_revision_title( (int) $_GET['revision'], false ) )
                    : false,
				6 => sprintf(
					esc_html__( '%1$s published. %2$sView %3$s', 'the-events-calendar' ),
					$this->singular_event_label,
					'<a href="' . esc_url( get_permalink( $post_ID ) ) . '">',
					$this->singular_event_label_lowercase . '</a>'
				),
				7 => sprintf( esc_html__( '%s saved.', 'the-events-calendar' ), $this->singular_event_label ),
				8 => sprintf(
					esc_html__( '%1$s submitted. %2$sPreview %3$s', 'the-events-calendar' ),
					$this->singular_event_label,
					'<a target="_blank" href="' . esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) . '">',
					$this->singular_event_label_lowercase . '</a>'
				),
				9 => sprintf(
					esc_html__( '%1$s scheduled for: %2$s. %3$sPreview %4$s', 'the-events-calendar' ),
					$this->singular_event_label,
					// translators: Publish box date format, see http://php.net/date
					'<strong>' . date_i18n( esc_html__( 'M j, Y @ G:i', 'the-events-calendar' ), strtotime( $post->post_date ) ) . '</strong>',
					'<a target="_blank" href="' . esc_url( get_permalink( $post_ID ) ) . '">',
					$this->singular_event_label_lowercase . '</a>'
				),
				10 => sprintf(
					esc_html__( '%1$s draft updated. %2$sPreview %3$s', 'the-events-calendar' ),
					$this->singular_event_label,
					'<a target="_blank" href="' . esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) . '">',
					$this->singular_event_label_lowercase . '</a>'
				),
			];

			$messages[ Tribe__Events__Venue::POSTTYPE ] = [
				0  => '', // Unused. Messages start at index 1.
				1  => sprintf( esc_html__( '%s updated.', 'the-events-calendar' ), $this->singular_venue_label ),
				2  => esc_html__( 'Custom field updated.', 'the-events-calendar' ),
				3  => esc_html__( 'Custom field deleted.', 'the-events-calendar' ),
				4  => sprintf( esc_html__( '%s updated.', 'the-events-calendar' ), $this->singular_venue_label ),
				/* translators: %s: date and time of the revision */
				5  => isset( $_GET['revision'] )
                    ? sprintf( esc_html__( '%1$s restored to revision from %2$s', 'the-events-calendar' ), $this->singular_venue_label, wp_post_revision_title( (int) $_GET['revision'], false ) )
                    : false,
				6  => sprintf( esc_html__( '%s published.', 'the-events-calendar' ), $this->singular_venue_label ),
				7  => sprintf( esc_html__( '%s saved.', 'the-events-calendar' ), $this->singular_venue_label ),
				8  => sprintf( esc_html__( '%s submitted.', 'the-events-calendar' ), $this->singular_venue_label ),
				9  => sprintf(
					esc_html__( '%1$s scheduled for: %2$s.', 'the-events-calendar' ),
					$this->singular_venue_label,
					// translators: Publish box date format, see http://php.net/date
					'' . date_i18n( esc_html__( 'M j, Y @ G:i', 'the-events-calendar' ), strtotime( $post->post_date ) ) . '</strong>'
				),
				10 => sprintf( esc_html__( '%s draft updated.', 'the-events-calendar' ), $this->singular_venue_label ),
			];

			$messages[ Tribe__Events__Organizer::POSTTYPE ] = [
				0 => '', // Unused. Messages start at index 1.
				1 => sprintf( esc_html__( '%s updated.', 'the-events-calendar' ), $this->singular_organizer_label ),
				2 => esc_html__( 'Custom field updated.', 'the-events-calendar' ),
				3 => esc_html__( 'Custom field deleted.', 'the-events-calendar' ),
				4 => sprintf( esc_html__( '%s updated.', 'the-events-calendar' ), $this->singular_organizer_label ),
				/* translators: %s: date and time of the revision */
				5 => isset( $_GET['revision'] )
                    ? sprintf( esc_html__( '%s restored to revision from %s', 'the-events-calendar' ), $this->singular_organizer_label, wp_post_revision_title( (int) $_GET['revision'], false ) )
                    : false,
				6 => sprintf( esc_html__( '%s published.', 'the-events-calendar' ), $this->singular_organizer_label ),
				7 => sprintf( esc_html__( '%s saved.', 'the-events-calendar' ), $this->singular_organizer_label ),
				8 => sprintf( esc_html__( '%s submitted.', 'the-events-calendar' ), $this->singular_organizer_label ),
				9 => sprintf(
					esc_html__( '%1$s scheduled for: %2$s.', 'the-events-calendar' ),
					$this->singular_organizer_label,
					// translators: Publish box date format, see http://php.net/date
					'<strong>' . date_i18n( esc_html__( 'M j, Y @ G:i', 'the-events-calendar' ), strtotime( $post->post_date ) ) . '</strong>'
				),
				10 => sprintf(
					esc_html__( '%s draft updated.', 'the-events-calendar' ), $this->singular_organizer_label
				),
			];

			return $messages;
		}

		/**
		 * Adds the submenu items for editing the Venues and Organizers.
		 * Used to be a PRO only feature, but as of 3.0, it is part of Core.
		 *
		 */
		public function addVenueAndOrganizerEditor() {
			add_submenu_page( 'edit.php?post_type=' . self::POSTTYPE, __( $this->plural_venue_label, 'the-events-calendar' ), __( $this->plural_venue_label, 'the-events-calendar' ), 'edit_tribe_venues', 'edit.php?post_type=' . Tribe__Events__Venue::POSTTYPE );
			add_submenu_page( 'edit.php?post_type=' . self::POSTTYPE, __( $this->plural_organizer_label, 'the-events-calendar' ), __( $this->plural_organizer_label, 'the-events-calendar' ), 'edit_tribe_organizers', 'edit.php?post_type=' . Tribe__Events__Organizer::POSTTYPE );
			add_submenu_page( 'edit.php?post_type=' . Tribe__Events__Venue::POSTTYPE, sprintf( esc_html__( 'Add New %s', 'the-events-calendar' ), $this->singular_venue_label ), sprintf( esc_html__( 'Add New %s', 'the-events-calendar' ), $this->singular_venue_label ), 'edit_tribe_venues', 'post-new.php?post_type=' . Tribe__Events__Venue::POSTTYPE );
			add_submenu_page( 'edit.php?post_type=' . Tribe__Events__Organizer::POSTTYPE, sprintf( esc_html__( 'Add New %s', 'the-events-calendar' ), $this->singular_organizer_label ), sprintf( esc_html__( 'Add New %s', 'the-events-calendar' ), $this->singular_organizer_label ), 'edit_tribe_organizers', 'post-new.php?post_type=' . Tribe__Events__Organizer::POSTTYPE );
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
				tribe_is_community_edit_event_page()
			) {
				$venue_id = $this->defaults()->venue_id();
			}
			$venue_id = apply_filters( 'tribe_display_event_venue_dropdown_id', $venue_id );

			// If there is a Venue of some sorts, don't display this message
			if ( $venue_id ) {
				return;
			}
			?>
			<tr class="tribe-rich-snippet-notice">
				<td colspan="2"><?php printf( esc_html__( 'Without a defined location your event will not display a %sGoogle Rich Snippet%s on the search results.', 'the-events-calendar' ), '<a href="https://support.google.com/webmasters/answer/164506" target="_blank">', '</a>' ) ?></td>
			</tr>
			<?php
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
		}

		/**
		 * Update admin classes
		 *
		 * @param array $classes
		 *
		 * @return array
		 */
		public function admin_body_class( $classes ) {
			$admin_helpers = Tribe__Admin__Helpers::instance();
			if ( $admin_helpers->is_screen( 'settings_page_tribe-settings' ) || $admin_helpers->is_post_type_screen() ) {
				$classes .= ' events-cal ';
			}

			return $classes;
		}

		/**
		 * Clean up trashed venues
		 *
		 * @param int $postId
		 *
		 */
		public function cleanupPostVenues( $postId ) {
			$this->removeDeletedPostTypeAssociation( '_EventVenueID', $postId );
		}

		/**
		 * Clean up trashed organizers.
		 *
		 * @param int $postId
		 *
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
		 */
		protected function removeDeletedPostTypeAssociation( $key, $postId ) {
			$the_query = new WP_Query(
				[
					'meta_key'   => $key,
					'meta_value' => $postId,
					'post_type'  => self::POSTTYPE,
				]
			);

			while ( $the_query->have_posts() ) {
				$the_query->the_post();
				delete_post_meta( get_the_ID(), $key );
			}

			wp_reset_postdata();
		}

		/**
		 * Filters the post types across all of the Tribe plugins
		 */
		public function filter_post_types( $post_types ) {
			$post_types[] = self::POSTTYPE;
			$post_types[] = Tribe__Events__Organizer::POSTTYPE;
			$post_types[] = Tribe__Events__Venue::POSTTYPE;

			return $post_types;
		}

		/**
		 * Get the post types that are associated with TEC.
		 *
		 * @return array The post types associated with this plugin
		 */
		public static function getPostTypes() {
			return apply_filters( 'tribe_events_post_types', Tribe__Main::get_post_types() );
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
		 * Runs the Event Scheduler to purge old events
		 *
		 * @return void
		 */
		public function run_scheduler() {
			if ( ! empty( $this->scheduler ) ) {
				$this->scheduler->remove_hooks();
			}

			$this->scheduler = new Tribe__Events__Event_Cleaner_Scheduler(
				tribe_get_option( tribe( 'tec.event-cleaner' )->key_trash_events, null ),
				tribe_get_option( tribe( 'tec.event-cleaner' )->key_delete_events, null )
			);
			$this->scheduler->add_hooks();
		}

		/**
		 * Initializes admin-specific items for the events admin list dashboard page. Hooked to the
		 * current_screen action
		 *
		 * @param WP_Screen $screen WP Admin screen object for the current page
		 */
		public function init_admin_list_screen( $screen ) {
			// If we are dealing with a AJAX call just drop these checks
			if ( ! tribe( 'context' )->doing_ajax() ) {
				if ( 'edit' !== $screen->base ) {
					return;
				}

				if ( self::POSTTYPE !== $screen->post_type ) {
					return;
				}
			}

			Tribe__Events__Admin_List::init();
		}

		/**
		 * Set the displaying class property.
		 *
		 */
		public function setDisplay( $query = null ) {
			// If we didn't get a Query Instance we fetch from the globals
			if ( ! $query instanceof WP_Query ) {
				$query = $GLOBALS['wp_query'];
			}

			// If we are in Admin and Not inside of the Default WP AJAX request
			if ( is_admin() && ! tribe( 'context' )->doing_ajax() ) {
				$this->displaying = 'admin';
				return;
			}

			// Bail if we are not dealing with the main WP Query or a non-event Query
			if ( ! $query->is_main_query() || empty( $query->tribe_is_event_query ) ) {
				return;
			}

			// If we have an embed we just set it and bail
			$embed = $query->get( 'embed' );
			if ( ! empty( $embed ) ) {
				$this->displaying = 'embed';
				return;
			}

			// Fetch what ever display we have so far
			$display = $query->get( 'eventDisplay', false );

			// If we don't have a Permalink structure we see if we have something on the _GET param
			if ( ! get_option( 'permalink_structure' ) ) {
				$display = Tribe__Utils__Array::get( $_GET, 'tribe_event_display', $display );
			}

			// Fetch the default if we have nothing
			if ( false === $display ) {
				$display = tribe_get_option( 'viewOption', 'list' );
			}

			// If single and not All for Recurring events From Pro
			if ( $query->is_single() && 'all' !== $display ) {
				$display = 'single-event';
			}

			// Only do this by the end
			$this->displaying = filter_var( $display, FILTER_SANITIZE_STRING );
		}

		/**
		 * Returns the default view, providing a fallback if the default is no longer available.
		 *
		 * This can be useful is for instance a view added by another plugin (such as PRO) is
		 * stored as the default but can no longer be generated due to the plugin being deactivated.
		 *
		 * @return string
		 */
		public function default_view() {
			// Compare the stored default view option to the list of available views
			$default         = Tribe__Settings_Manager::instance()->get_option( 'viewOption', 'month' );
			$available_views = (array) apply_filters( 'tribe-events-bar-views', [], false );

			foreach ( $available_views as $view ) {
				if ( $default === $view['displaying'] ) {
					return $default;
				}
			}

			// If the stored option is no longer available, pick the first available one instead
			$first_view = array_shift( $available_views );
			$view       = $first_view['displaying'];

			// Update the saved option
			Tribe__Settings_Manager::instance()->set_option( 'viewOption', $view );

			return $view;
		}

		public function setup_l10n_strings() {
			// @todo [BTRIA-605]: These members became deprecated in 4.4 - remove in future release.
			$this->monthsFull      = Tribe__Date_Utils::get_localized_months_full();
			$this->monthsShort     = Tribe__Date_Utils::get_localized_months_short();
			$this->daysOfWeek      = Tribe__Date_Utils::get_localized_weekdays_full();
			$this->daysOfWeekShort = Tribe__Date_Utils::get_localized_weekdays_short();
			$this->daysOfWeekMin   = Tribe__Date_Utils::get_localized_weekdays_initial();

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
				return Tribe__Date_Utils::get_localized_months_short();
			}

			return Tribe__Date_Utils::get_localized_months_full();
		}

		/**
		 * Flush rewrite rules to support custom links
		 *
		 * @todo This is only registering the events post type, not the meta types
		 *
		 * @link https://codex.wordpress.org/Custom_Queries#Permalinks_for_Custom_Archives
		 */
		public static function flushRewriteRules() {

			$tec = self::instance();

			// reregister custom post type to make sure slugs are updated
			$tec->post_type_args['rewrite']['slug'] = sanitize_title( $tec->getRewriteSlugSingular() );
			register_post_type( self::POSTTYPE, apply_filters( 'tribe_events_register_event_type_args', $tec->post_type_args ) );

			add_action( 'shutdown', 'flush_rewrite_rules' );
		}

		/**
		 * If a themer uses get_post_type_archive_link() to find the event archive URL, this
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
		 * @link https://codex.wordpress.org/Custom_Queries#Permalinks_for_Custom_Archives
		 * @return mixed array of query variables that this plugin understands
		 */
		public function eventQueryVars( $qvars ) {
			$qvars[] = 'eventDisplay';
			$qvars[] = 'eventDate';
			$qvars[] = 'eventSequence';
			$qvars[] = 'ical';
			$qvars[] = 'start_date';
			$qvars[] = 'end_date';
			$qvars[] = 'featured';
			$qvars[] = self::TAXONOMY;
			$qvars[] = 'tribe_remove_date_filters';

			return $qvars;
		}

		/**
		 * Get all possible translations for a String based on the given Languages and Domains
		 *
		 * WARNING: This function is slow because it deals with files, so don't overuse it!
		 *
		 * @since 5.1.1 Deprecated and moved code to the `Tribe\Events\I18n` class.
		 *
		 * @param  array  $strings          An array of strings (required)
		 * @param  array  $languages        Which l10n to fetch the string (required)
		 * @param  array  $domains          Possible Domains to re-load
		 * @param  string $default_language The default language to avoid re-doing that
		 *
		 * @return array                    A multi level array with the possible translations for the given strings
		 *
		 * @deprecated Since 5.1.1, use `tribe( 'tec.i18n' )->get_i18n_strings()` instead.
		 */
		public function get_i18n_strings( $strings, $languages, $domains = [], $default_language = 'en_US' ) {
			_deprecated_function( __METHOD__, '5.1.5', "tribe( 'tec.i18n' )->get_i18n_strings()" );

			return tribe( 'tec.i18n' )->get_i18n_strings( $strings, $languages, $domains, $default_language );
		}

		/**
		 * Redirect the legacy past/upcoming view URLs to list
		 *
		 * @since 4.6.5 revised to avoid unwanted redirects
		 */
		public function redirect_past_upcoming_view_urls() {
			// We are only interested in the path and not any query args
			if ( ! $requested_path = wp_parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ) ) {
				return;
			}

			// Prep strings so we can compare paths sans any leading/trailing slashes
			$requested_path = trim( $requested_path, '/' );
			$legacy_past_events_url = $this->getRewriteSlug() . '/' . $this->pastSlug;
			$legacy_upcoming_events_url = $this->getRewriteSlug() . '/' . $this->upcomingSlug;

			// Check if the request is for either the legacy past or upcoming event views and redirect if appropriate
			switch ( $requested_path ) {
				case $legacy_past_events_url:
					$search = '#/' . $this->pastSlug . '/?#';
					$replace = '/' . $this->listSlug . '/';
					$redirect_url = preg_replace( $search, $replace, $_SERVER['REQUEST_URI'] );
					$redirect_url = esc_url_raw( add_query_arg( [ 'tribe_event_display' => 'past' ], $redirect_url ) );
					wp_redirect( $redirect_url );
					tribe_exit();
					break;

				case $legacy_upcoming_events_url:
					$search = '#/' . $this->upcomingSlug . '/?#';
					$replace = '/' . $this->listSlug . '/';
					$redirect_url = preg_replace( $search, $replace, $_SERVER['REQUEST_URI'] );
					wp_redirect( $redirect_url );
					tribe_exit();
					break;
			}
		}

		/**
		 * Returns various internal events-related URLs
		 *
		 * @param string        $type      type of link. See switch statement for types.
		 * @param string|bool   $secondary for $type = month, pass a YYYY-MM string for a specific month's URL
		 *                                 for $type = week, pass a Week # string for a specific week's URL
		 * @param int|bool|null $term
		 * @param bool|null     $featured
		 *
		 * @return string The link.
		 */
		public function getLink( $type = 'home', $secondary = false, $term = null, $featured = null ) {
			static $cache_var_name = __METHOD__;

			// if permalinks are off or user doesn't want them: ugly.
			if ( '' === get_option( 'permalink_structure' ) ) {
				return esc_url_raw( $this->uglyLink( $type, $secondary ) );
			}

			if ( apply_filters( 'tribe_events_force_ugly_link', false ) ) {
				return esc_url_raw( $this->uglyLink( $type, $secondary ) );
			}

			// if this is an ajax request where the baseurl is provided, use that as the base url and use semi-ugly links
			if ( defined( 'DOING_AJAX' ) && DOING_AJAX && ! empty( $_POST['baseurl'] ) ) {
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
			$url_args = wp_parse_args( $url_query, [] );

			// Remove the "args"
			if ( ! empty( $url_query ) ) {
				$event_url = str_replace( '?' . $url_query, '', $event_url );
			}

			// Append Events structure
			$slug = _x( Tribe__Settings_Manager::get_option( 'eventsSlug', 'events' ), 'Archive Events Slug', 'the-events-calendar' );

			$cache_event_url_slugs = tribe_get_var( $cache_var_name, [] );

			if ( ! isset( $cache_event_url_slugs[ $slug ] ) ) {
				$cache_event_url_slugs[ $slug ] = trailingslashit( sanitize_title( $slug ) );
				tribe_set_var( $cache_var_name, $cache_event_url_slugs );
			}

			$event_url .= $cache_event_url_slugs[ $slug ];

			// if we're on an Event Cat, show the cat link, except for home and days.
			if ( $type !== 'home' && is_tax( self::TAXONOMY ) && $term !== false && ! is_numeric( $term ) ) {
				$term_link = get_term_link( get_query_var( 'term' ), self::TAXONOMY );
				if ( ! is_wp_error( $term_link ) ) {
					$event_url = trailingslashit( $term_link );
				}
			} elseif ( $term && is_numeric( $term ) ) {
				$term_link = get_term_link( (int) $term, self::TAXONOMY );
				if ( ! is_wp_error( $term_link ) ) {
					$event_url = trailingslashit( $term_link );
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
				case 'upcoming':
					$event_url = trailingslashit( esc_url_raw( $event_url . $this->listSlug ) );
					break;
				case 'past':
					$event_url = esc_url_raw( add_query_arg( 'tribe_event_display', 'past', trailingslashit( $event_url . $this->listSlug ) ) );
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
						$secondary = tribe_format_date( $secondary, false, Tribe__Date_Utils::DBDATEFORMAT );
					}
					$event_url = trailingslashit( esc_url_raw( $event_url . $secondary ) );
					break;
				default:
					$event_url = esc_url_raw( $event_url );
					break;
			}

			// Filter get link
			$event_url = apply_filters( 'tribe_events_get_link', $event_url, $type, $secondary, $term, $url_args, $featured );

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

			/**
			 * If we need a specific base url, use that.
			 *
			 * @return string The base url.
			 */
			$eventUrl = apply_filters( 'tribe_events_ugly_link_baseurl', $eventUrl );


			/**
			 * if this is an ajax request where the baseurl is provided, use that as the base url.
			 *
			 * @return string The AJAX provided base url.
			 */
			if ( tribe( 'context' )->doing_ajax() && ! empty( $_POST['baseurl'] ) ) {
				$eventUrl = trailingslashit( $_POST['baseurl'] );
			}

			// if we're on an Event Cat, show the cat link, except for home.
			if ( $type !== 'home' && is_tax( self::TAXONOMY ) ) {
				if (
					(
						tribe( 'context' )->doing_ajax()
						&& ! empty( $_POST['baseurl'] )
					)
					|| apply_filters( 'tribe_events_force_ugly_link', false )
				) {
					$eventUrl = add_query_arg( 'tribe_event_category', get_query_var( 'term' ), $eventUrl );
				} else {
					$eventUrl = add_query_arg( self::TAXONOMY, get_query_var( 'term' ), $eventUrl );
				}
			}

			switch ( $type ) {
				case 'day':
					$eventUrl = add_query_arg( [ 'tribe_event_display' => $type ], $eventUrl );
					if ( $secondary ) {
						$eventUrl = add_query_arg( [ 'eventDate' => $secondary ], $eventUrl );
					}
					break;
				case 'week':
				case 'month':
					$eventUrl = add_query_arg( [ 'tribe_event_display' => $type ], $eventUrl );
					if ( is_string( $secondary ) ) {
						$eventUrl = add_query_arg( [ 'eventDate' => $secondary ], $eventUrl );
					} elseif ( is_array( $secondary ) ) {
						$eventUrl = add_query_arg( $secondary, $eventUrl );
					}
					break;
				case 'list':
				case 'past':
				case 'upcoming':
					$eventUrl = add_query_arg( [ 'tribe_event_display' => $type ], $eventUrl );
					break;
				case 'dropdown':
					$dropdown = add_query_arg( [ 'tribe_event_display' => 'month', 'eventDate' => ' ' ], $eventUrl );
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
		 * @param int|WP_Post|null $post The Event Post Object or ID, if left empty will give get the current post.
		 *
		 * @return string The URL for the GCal export link.
		 */
		public function googleCalendarLink( $post = null ) {
			if ( is_null( $post ) ) {
				$post = self::postIdHelper( $post );
			}

			if ( is_numeric( $post ) ) {
				$post = WP_Post::get_instance( $post );
			}

			if ( ! $post instanceof WP_Post ) {
				return false;
			}

			// After this point we know that we have a safe WP_Post object
			// Fetch if the Event is a Full Day Event
			$is_all_day = Tribe__Date_Utils::is_all_day( get_post_meta( $post->ID, '_EventAllDay', true ) );

			// Fetch the required Date TimeStamps
			$start_date = Tribe__Events__Timezones::event_start_timestamp( $post->ID );
			// Google Requires that a Full Day event end day happens on the next Day
			$end_date   = Tribe__Events__Timezones::event_end_timestamp( $post->ID ) + ( $is_all_day ? DAY_IN_SECONDS : 0 );

			if ( $is_all_day ) {
				$dates = date( 'Ymd', $start_date ) . '/' . date( 'Ymd', $end_date );
			} else {
				$dates = date( 'Ymd', $start_date ) . 'T' . date( 'Hi00', $start_date ) . '/' . date( 'Ymd', $end_date ) . 'T' . date( 'Hi00', $end_date );
			}

			// Fetch the
			$location = trim( $this->fullAddressString( $post->ID ) );

			$event_details = tribe_get_the_content( null, false, $post->ID );

			// Hack: Add space after paragraph
			// Normally Google Cal understands the newline character %0a
			// And that character will automatically replace newlines on urlencode()
			$event_details = str_replace ( '</p>', '</p> ', $event_details );
			$event_details = strip_tags( $event_details );

			//Truncate Event Description and add permalink if greater than 996 characters
			if ( strlen( $event_details ) > 996 ) {

				$event_url     = get_permalink( $post->ID );
				$event_details = substr( $event_details, 0, 996 );

				//Only add the permalink if it's shorter than 900 characters, so we don't exceed the browser's URL limits
				if ( strlen( $event_url ) < 900 ) {
					$event_details .= sprintf( esc_html__( ' (View Full %1$s Description Here: %2$s)', 'the-events-calendar' ), $this->singular_event_label, $event_url );
				}
			}

			$params = [
				'action'   => 'TEMPLATE',
				'text'     => urlencode( strip_tags( $post->post_title ) ),
				'dates'    => $dates,
				'details'  => urlencode( $event_details ),
				'location' => urlencode( $location ),
				'trp'      => 'false',
				'sprop'    => 'website:' . home_url(),
			];

			if ( Tribe__Timezones::is_mode( Tribe__Timezones::SITE_TIMEZONE ) ) {
				$timezone = Tribe__Timezones::build_timezone_object();
			} else {
				$timezone = Tribe__Events__Timezones::get_timezone(
					Tribe__Events__Timezones::get_event_timezone_string( $post->ID )
				);
			}

			// If we have a good timezone string we setup it; UTC doesn't work on Google
			if ( $timezone instanceof DateTimeZone) {
				$params['ctz'] = urlencode( $timezone->getName() );
			}

			/**
			 * Allow users to Filter our Google Calendar Link params
			 * @var array Params used in the add_query_arg
			 * @var int   Event ID
			 */
			$params = apply_filters( 'tribe_google_calendar_parameters', $params, $post->ID );

			$base_url = 'https://www.google.com/calendar/event';
			$url    = add_query_arg( $params, $base_url );

			return $url;
		}

		/**
		* Custom Escape for gCal Description to keep spacing characters in the url
		*
		* @return sanitized url
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
		 * @return string a fully qualified link to https://maps.google.com/ for this event
		 */
		public function googleMapLink( $post_id = null ) {
			if ( $post_id === null || ! is_numeric( $post_id ) ) {
				global $post;
				$post_id = $post->ID;
			}

			$locationMetaSuffixes = [ 'address', 'city', 'region', 'zip', 'country' ];
			$to_encode = '';
			$url = '';

			foreach ( $locationMetaSuffixes as $val ) {
				$metaVal = call_user_func( 'tribe_get_' . $val, $post_id );
				if ( $metaVal ) {
					$to_encode .= $metaVal . ' ';
				}
			}

			if ( $to_encode ) {
				$url = 'https://maps.google.com/maps?f=q&amp;source=s_q&amp;hl=en&amp;geocode=&amp;q=' . urlencode( trim( $to_encode ) );
			}

			return apply_filters( 'tribe_events_google_map_link', $url, $post_id );
		}

		/**
		 *  Returns the full address of an event along with HTML markup.  It
		 *  loads the address template to generate the HTML
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
			if ( tribe_get_venue( $postId ) ) {
				$address .= tribe_get_venue( $postId );
			}

			if ( tribe_get_address( $postId ) ) {
				if ( $address != '' ) {
					$address .= ', ';
				}
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
			$plugin_path = dirname( TRIBE_EVENTS_FILE );

			self::instance()->plugins_loaded();

			//check if class is available before continuing
			if ( ! class_exists( 'Tribe__Settings_Manager' ) ) {
				return;
			}

			if ( ! class_exists( 'Tribe__Events__Editor__Compatibility' ) ) {
				require_once $plugin_path . '/src/Tribe/Editor/Compatibility.php';
			}

			$editor_compatibility = new Tribe__Events__Editor__Compatibility();
			$editor_compatibility->deactivate_gutenberg_extension_plugin();

			if ( ! is_network_admin()  ) {
				// We set with a string to avoid having to include a file here.
				set_transient( '_tribe_events_delayed_flush_rewrite_rules', 'yes', 0 );
			}

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

			// We cannot run deactivation logic because the tribe-common loaded is not compatible.
			if ( ! class_exists( 'Tribe__Abstract_Deactivation' ) ) {
				return;
			}

			/**
			 * If Deactivation Class not found manually load all classes to deactivate
			 */
			if ( ! class_exists( 'Tribe__Events__Deactivation' ) ) {
				require_once dirname( __FILE__ ) . '/Capabilities.php';
				require_once dirname( __FILE__ ) . '/Aggregator/Records.php';
				require_once dirname( __FILE__ ) . '/Deactivation.php';
			}

			if ( ! class_exists( 'Tribe__Cache' ) ) {
				require_once dirname( dirname( __FILE__ ) ) . '/common/src/Tribe/Cache.php';
			}

			$hook_name = 'tribe_schedule_transient_purge';

			// Make sure we look for the constant if possible.
			if ( defined( '\Tribe__Cache::SCHEDULED_EVENT_DELETE_TRANSIENT' ) ) {
				$hook_name = \Tribe__Cache::SCHEDULED_EVENT_DELETE_TRANSIENT;
			}

			wp_clear_scheduled_hook( $hook_name );

			$deactivation = new Tribe__Events__Deactivation( $network_deactivating );
			add_action( 'shutdown', [ $deactivation, 'deactivate' ] );
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
			// Fetch Status to check what we need to do
			$status = get_post_status( $id );

			// If the post doesn't exist just bail the get_post_meta
			if ( is_string( $status ) && 'auto-draft' !== $status ) {
				$value = get_post_meta( $id, $meta, $single );
			} else {
				$value = false;
			}

			if ( $value === false ) {
				$method = str_replace( [ '_Event', '_Organizer', '_Venue' ], '', $meta );
				$filter = str_replace( [ '_Event', '_Organizer', '_Venue' ], [ '', 'Organizer', 'Venue' ], $meta );

				$default = call_user_func( [ $this->defaults(), strtolower( $method ) ] );

				/**
				 * Used to Filter the default value for a Specific meta
				 *
				 * @since 4.0.7
				 * @var $value
				 * @var $id
				 * @var $meta
				 * @var $single
				 */
				$value = apply_filters( "tribe_get_meta_default_value_{$filter}", $default, $id, $meta, $single );
			}
			return $value;
		}

		/**
		 * Get all possible translations for a String based on the given Languages and Domains
		 *
		 * WARNING: This function is slow because it deals with files, so don't overuse it!
		 * Differently from the `get_i18n_strings` method this will not use any domain that's not specified.
		 *
		 * @since 5.1.1 Deprecated and moved to the `Tribe\Events\I18n` class.
		 *
		 * @param array $strings   An array of strings (required).
		 * @param array $languages Which l10n to fetch the string (required).
		 * @param array $domains   Possible Domains to re-load.
		 *
		 * @return array                    A multi level array with the possible translations for the given strings.
		 *
		 * @deprecated Since 5.1.1, use `tribe( 'tec.i18n' )->get_i18n_strings_for_domains()` instead.
		 */
		public function get_i18n_strings_for_domains( $strings, $languages, $domains = [ 'default' ] ) {
			_deprecated_function( __METHOD__, '5.1.5', "tribe( 'tec.i18n' )->get_i18n_strings_for_domains()" );

			return tribe( 'tec.i18n' )->get_i18n_strings_for_domains( $strings, $languages, $domains );
		}

		/**
		 * Adds / removes the event details as meta tags to the post.
		 *
		 * @param int     $postId
		 * @param WP_Post $post
		 *
		 */
		public function addEventMeta( $postId, $post ) {
			static $avoid_recursion = false;

			// Avoid an infinite loop, because saveEventMeta calls wp_update_post when the post is set to always show in calendar
			if ( $avoid_recursion ) {
				return;
			}

			$avoid_recursion = true;

			$original_post     = wp_is_post_revision( $post );
			$is_event_revision = $original_post && tribe_is_event( $original_post );

			if ( $is_event_revision ) {
				$revision = Tribe__Events__Revisions__Post::new_from_post( $post );
				$revision->save();

				$avoid_recursion = false;

				return;
			}

			// When not an instance of Post we bail to avoid revision problems.
			if ( ! $post instanceof WP_Post ) {
				return;
			}

			$event_meta = new Tribe__Events__Meta__Save( $postId, $post );
			$event_meta->maybe_save();

			// Allow this callback to run
			$avoid_recursion = false;
		}

		public function normalize_organizer_submission( $submission ) {
			$organizer_pto = get_post_type_object( Tribe__Events__Organizer::POSTTYPE );
			$organizers    = [];
			if ( ! isset( $submission['OrganizerID'] ) ) {
				return $organizers; // not a valid submission
			}

			if ( is_array( $submission['OrganizerID'] ) ) {
				foreach ( $submission['OrganizerID'] as $key => $organizer_id ) {
					if ( ! empty( $organizer_id ) ) {
						$organizers[] = [ 'OrganizerID' => intval( $organizer_id ) ];
					} elseif (
						! empty( $organizer_pto->cap->create_posts )
						&& current_user_can( $organizer_pto->cap->create_posts )
					) {
						$o = [];
						foreach ( [ 'Organizer', 'Phone', 'Website', 'Email' ] as $field_name ) {
							$o[ $field_name ] = isset( $submission[ $field_name ][ $key ] ) ? $submission[ $field_name ][ $key ] : '';
						}
						$organizers[] = $o;
					}
				}
				return $organizers;
			}

			// old style with single organizer fields
			if ( current_user_can( $organizer_pto->cap->create_posts ) ) {
				$o = [];
				foreach ( [ 'Organizer', 'Phone', 'Website', 'Email' ] as $field_name ) {
					$o[ $field_name ] = isset( $submission[ $field_name ] ) ? $submission[ $field_name ] : '';
				}
				$organizers[]     = $o;
				$o[ $field_name ] = isset( $submission[ $field_name ] ) ? $submission[ $field_name ] : '';
			}

			return $organizers;
		}

		/**
		 * Adds the '_<posttype>Origin' meta field for a newly inserted events-calendar post.
		 *
		 * @param int     $postId , the post ID
		 * @param WP_Post $post   , the post object
		 *
		 */
		public function addPostOrigin( $postId, $post ) {
			// Only continue of the post being added is an event, venue, or organizer.
			if ( isset( $postId ) && isset( $post->post_type ) ) {
				if ( $post->post_type == self::POSTTYPE ) {
					$post_type = '_Event';
				} elseif ( $post->post_type == Tribe__Events__Venue::POSTTYPE ) {
					$post_type = '_Venue';
				} elseif ( $post->post_type == Tribe__Events__Organizer::POSTTYPE ) {
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
		 * @param int     $post_id The post ID.
		 * @param WP_Post $post    The post object.
		 *
		 */
		public function publishAssociatedTypes( $post_id, $post ) {

			// don't need to save the venue or organizer meta when we are just publishing
			remove_action( 'save_post_' . Tribe__Events__Venue::POSTTYPE, [ $this, 'save_venue_data' ], 16 );
			remove_action( 'save_post_' . Tribe__Events__Organizer::POSTTYPE, [ $this, 'save_organizer_data' ], 16 );

			// Remove any "preview" venues and organizers (duplicates) attached to this event.
			$this->remove_preview_venues( $post_id, true );
			$this->remove_preview_organizers( $post_id, true );

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
			add_action( 'save_post_' . Tribe__Events__Venue::POSTTYPE, [ $this, 'save_venue_data' ], 16, 2 );
			add_action( 'save_post_' . Tribe__Events__Organizer::POSTTYPE, [ $this, 'save_organizer_data' ], 16, 2 );
		}

		/**
		 * Make sure the venue meta gets saved
		 *
		 * @param int     $postID The venue id.
		 * @param WP_Post $post   The post object.
		 *
		 * @return null
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
			/**
			 * When a new venue is saved set the value for ShowMap and ShowMapLink if they are not present as if
			 * a checkbox is set to false $_POST is not going to send the value and is not going to be present inside
			 * of the array. This action is fired on update or creation from the admin so it's not going to affect
			 * external components like Community Plugin.
			 */
			$data = wp_parse_args(
				(array) $data,
				[
					'ShowMap'     => 'false',
					'ShowMapLink' => 'false',
				]
			);
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
		public function get_venue_info( $p = null, $args = [] ) {
			$defaults = [
				'post_type'            => Tribe__Events__Venue::POSTTYPE,
				'nopaging'             => 1,
				'post_status'          => 'publish',
				'ignore_sticky_posts ' => 1,
				'orderby'              => 'title',
				'order'                => 'ASC',
				'p'                    => $p,
			];

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
		 * @return null
		 */
		public function save_organizer_data( $postID = null, $post = null ) {
			// was an organizer submitted from the single organizer post editor?
			if ( empty( $_POST['post_ID'] ) || $_POST['post_ID'] != $postID || empty( $_POST['organizer'] ) ) {
				return;
			}

			// is the current user allowed to edit this organizer?
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

			if ( $post->post_type == Tribe__Events__Organizer::POSTTYPE && $post->ID ) {
				$data['OrganizerID'] = $post->ID;
			}

			//google map checkboxes
			$postdata = [
					'post_title'  => $data['Organizer'],
					'post_type'   => Tribe__Events__Organizer::POSTTYPE,
					'post_status' => 'publish',
					'ID'          => $data['OrganizerID'],
			];

			if ( isset( $data['OrganizerID'] ) && $data['OrganizerID'] != '0' ) {
				$organizer_id = $data['OrganizerID'];
				wp_update_post( [ 'post_title' => $data['Organizer'], 'ID' => $data['OrganizerID'] ] );
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
		public function get_organizer_info( $p = null, $args = [] ) {
			$defaults = [
					'post_type'            => Tribe__Events__Organizer::POSTTYPE,
					'nopaging'             => 1,
					'post_status'          => 'publish',
					'ignore_sticky_posts ' => 1,
					'orderby'              => 'title',
					'order'                => 'ASC',
					'p'                    => $p,
			];

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
			tribe( 'tec.admin.event-meta-box' )->init_with_event( $event );
		}

		/**
		 * Adds a style chooser to the write post page
		 *
		 */
		public function VenueMetaBox() {
			global $post;
			$options = '';
			$style   = '';
			$event   = $post;

			if ( $post->post_type == Tribe__Events__Venue::POSTTYPE ) {

				if ( ( is_admin() && isset( $_GET['post'] ) && $_GET['post'] ) || ( ! is_admin() && isset( $event->ID ) ) ) {
					$saved = true;
				}

				$is_saved = $event->ID && isset( $saved ) && $saved;

				if ( $is_saved ) {
					$venue_title = apply_filters( 'the_title', $post->post_title, $post->ID );
				}

				foreach ( $this->venueTags as $tag ) {
					if ( metadata_exists( 'post', $event->ID, $tag ) ) {
						$$tag = esc_html( get_post_meta( $event->ID, $tag, true ) );
					} else {
						$cleaned_tag = str_replace( '_Venue', '', $tag );
						$$tag = call_user_func( [ $this->defaults(), $cleaned_tag ] );
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
					$venue_meta_box_template = apply_filters( 'tribe_events_venue_meta_box_template', $this->plugin_path . 'src/admin-views/venue-meta-box.php' );
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
		 */
		public function OrganizerMetaBox() {
			global $post;
			$options = '';
			$style   = '';
			$postId  = $post->ID;
			$saved   = false;

			if ( $post->post_type == Tribe__Events__Organizer::POSTTYPE ) {

				if ( ( is_admin() && isset( $_GET['post'] ) && $_GET['post'] ) || ( ! is_admin() && isset( $postId ) ) ) {
					$saved = true;
				}

				if ( $postId ) {

					if ( $saved ) { //if there is a post AND the post has been saved at least once.
						$organizer_title = apply_filters( 'the_title', $post->post_title, $post->ID );
					}

					foreach ( $this->organizerTags as $tag ) {
						if ( metadata_exists( 'post', $postId, $tag ) ) {
							$$tag = get_post_meta( $postId, $tag, true );
						}
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
					$organizer_meta_box_template = apply_filters( 'tribe_events_organizer_meta_box_template', $this->plugin_path . 'src/admin-views/organizer-meta-box.php' );
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
		 */
		public function ajax_form_validate() {
			if (
				$_REQUEST['name']
				&& $_REQUEST['nonce']
				&& $_REQUEST['type']
				&& wp_verify_nonce( $_REQUEST['nonce'], 'tribe-validation-nonce' )
			) {
				echo $this->verify_unique_name( $_REQUEST['name'], $_REQUEST['type'] );
				die;
			}
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
		 * @param string $type - post type
		 *
		 * @return boolean
		 */
		public function verify_unique_name( $name, $post_type ) {
			global $wpdb;
			$name = stripslashes( $name );
			if ( '' == $name ) {
				return 1;
			}

			if ( 'venue' === $post_type ) {
				$post_type = Tribe__Events__Venue::POSTTYPE;
			} elseif ( 'organizer' === $post_type ) {
				$post_type = Tribe__Events__Organizer::POSTTYPE;
			}

			// @todo [BTRIA-606]: Update this verification to check all post_status <> 'trash'.
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
					throw new OverflowException( esc_html__( 'Date out of range.', 'the-events-calendar' ) );
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
					throw new OverflowException( esc_html__( 'Date out of range.', 'the-events-calendar' ) );
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
		 */
		public function addEventBox() {
			add_meta_box(
					'tribe_events_event_details',
					$this->plugin_name,
					[ $this, 'EventsChooserBox' ],
					self::POSTTYPE,
					'normal',
					'high',
					[
							'__back_compat_meta_box' => tribe( 'tec.gutenberg' )->should_display() || ! class_exists( 'Tribe__Events__Pro__Main' ),
					]
			);

			add_meta_box(
					'tribe_events_event_options',
					sprintf( esc_html__( '%s Options', 'the-events-calendar' ), $this->singular_event_label ),
					[ $this, 'eventMetaBox' ],
					self::POSTTYPE,
					'side',
					'default'
			);

			add_meta_box(
					'tribe_events_venue_details',
					sprintf( esc_html__( '%s Information', 'the-events-calendar' ), $this->singular_venue_label ),
					[ $this, 'VenueMetaBox' ],
					Tribe__Events__Venue::POSTTYPE,
					'normal',
					'high'
			);

			if ( ! class_exists( 'Tribe__Events__Pro__Main' ) ) {
				remove_meta_box( 'slugdiv', Tribe__Events__Venue::POSTTYPE, 'normal' );
			}

			add_meta_box(
					'tribe_events_organizer_details',
					sprintf( esc_html__( '%s Information', 'the-events-calendar' ), $this->singular_organizer_label ),
					[ $this, 'OrganizerMetaBox' ],
					Tribe__Events__Organizer::POSTTYPE,
					'normal',
					'high'
			);
		}

		/**
		 * Include the event editor meta box.
		 *
		 */
		public function eventMetaBox() {
			include( $this->plugin_path . 'src/admin-views/event-sidebar-options.php' );
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
			static $cache_var_name = __METHOD__;

			$is_venue = tribe_get_var( $cache_var_name, [] );

			if ( $postId === null || ! is_numeric( $postId ) ) {
				global $post;
				if ( isset( $post->ID ) ) {
					$postId = $post->ID;
				}
			}

			// Return if we've already fetched this info.
			if ( isset( $is_venue[ $postId ] ) ) {
				return $is_venue[ $postId ];
			}

			if ( isset( $postId ) && get_post_field( 'post_type', $postId ) == Tribe__Events__Venue::POSTTYPE ) {
				$is_venue[ $postId ] = true;
			} else {
				$is_venue[ $postId ] = false;
			}

			tribe_set_var( $cache_var_name, $is_venue );
			return $is_venue[ $postId ];
		}

		/**
		 * Check whether a post is an organizer.
		 *
		 * @param int|WP_Post The organizer/post id or object.
		 *
		 * @return bool Is it an organizer?
		 */
		public function isOrganizer( $postId = null ) {
			static $cache_var_name = __METHOD__;

			$is_organizer = tribe_get_var( $cache_var_name, [] );

			if ( $postId === null || ! is_numeric( $postId ) ) {
				global $post;
				if ( isset( $post->ID ) ) {
					$postId = $post->ID;
				}
			}

			// Return if we've already fetched this info.
			if ( isset( $is_organizer[ $postId ] ) ) {
				return $is_organizer[ $postId ];
			}

			if ( isset( $postId ) && get_post_field( 'post_type', $postId ) == Tribe__Events__Organizer::POSTTYPE ) {
				$is_organizer[ $postId ] = true;
			} else {
				$is_organizer[ $postId ] = false;
			}

			tribe_set_var( $cache_var_name, $is_organizer );

			return $is_organizer[ $postId ];
		}

		/**
		 * Modify the WHERE clause of query when fetching next/prev posts so events with identical times are not excluded
		 *
		 * This method ensures that when viewing single events that occur at a given time, other events
		 * that occur at the exact same time are are not excluded from the prev/next links
		 *
		 * @since 4.0.2
		 *
		 * @param string $where_sql WHERE SQL statement
		 *
		 * @return string
		 */
		public function get_closest_event_where( $where_sql, WP_Query $query ) {
			// If we're here the temporary properties should be set, but let's check and bail if not.
			if ( ! isset( $this->_where_direction, $this->_where_compare, $this->_where_post_id, $this->_where_start_date ) ) {
				return $where_sql;
			}

			// Find out the `postmeta` table alias from the Meta Query; use  the comparison operator to discriminate.
			$meta_query = $query->meta_query;
			$start_date = null;
			foreach ( $meta_query->get_clauses() as $clause ) {
				if ( '_EventStartDate' === $clause['key'] && $this->_where_compare === $clause['compare'] ) {
					$start_date = $clause['alias'];
					break;
				}
			}

			if ( null === $start_date ) {
				// This is weird but some other filtering might be working here, bail.
				return $where_sql;
			}

			global $wpdb;
			/*
			 * If a post has the same date and time as the one we restrict the posts.ID direction.
			 * If the date is the same pick one with a smaller (prev) or bigger (next) posts.ID; if not then
			 * use just the date.
			 */
			$where_sql .= $wpdb->prepare( "\nAND (
					(
						{$start_date}.meta_value = %s
						AND {$wpdb->posts}.ID {$this->_where_direction} {$this->_where_post_id}
					)
					OR
					{$start_date}.meta_value {$this->_where_direction} %s
				)",
				$this->_where_start_date,
				$this->_where_start_date
			);

			return $where_sql;
		}

		/**
		 * Get the prev/next post for a given event. Ordered by start date instead of ID.
		 *
		 * @param WP_Post $post The post/event.
		 * @param string  $mode Either 'next' or 'previous'.
		 *
		 * @return null|WP_Post
		 */
		public function get_closest_event( $post, $mode = 'next' ) {
			if ( 'previous' === $mode ) {
				$order      = 'DESC';
				$direction  = '<=';
				$this->_where_direction = '<';
			} else {
				$order      = 'ASC';
				$direction  = '>=';
				$this->_where_direction = '>';
				$mode       = 'next';
			}

			// This property and the `_where_x` ones are temporary by design to get around 5.2 lack of closures.
			$this->_where_compare = $direction;
			$this->_where_post_id   = $post->ID;
			$start_date = get_post_meta( $post->ID, '_EventStartDate', true );
			$this->_where_start_date = $start_date;

			$args = [
				'post__not_in'   => [ $post->ID ],
				'orderby'        => [
					'start_date_clause' => $order,
					'ID'                => $order,
				],
				'posts_per_page' => 1,
				'meta_query'     => [
					'start_date_clause'         => [
						'key'     => '_EventStartDate',
						'value'   => $start_date,
						'compare' => $direction,
						'type'    => 'DATETIME',
					],
					'hide_from_upcoming_clause' => [
						'key'     => '_EventHideFromUpcoming',
						'compare' => 'NOT EXISTS',
					],
					'relation'                  => 'AND',
				],
			];

			/**
			 * Allows the query arguments used when retrieving the next/previous event link
			 * to be modified.
			 *
			 * @var array   $args
			 * @var WP_Post $post
			 */
			$args = (array) apply_filters( "tribe_events_get_{$mode}_event_link", $args, $post );
			add_filter( 'posts_where', [ $this, 'get_closest_event_where' ], 10, 2 );
			$results = tribe_get_events( $args );
			remove_filter( 'posts_where', [ $this, 'get_closest_event_where' ] );
			// Unset the temporary properties we set to get around lack of closure support in PHP 5.2.
			unset( $this->_where_direction, $this->_where_compare, $this->_where_post_id, $this->_where_start_date );

			$event = null;

			// If we successfully located the next/prev event, we should have precisely one element in $results
			if ( 1 === count( $results ) ) {
				$event = current( $results );
			}

			/**
			 * Affords an opportunity to modify the event used to generate the event link (typically for
			 * the next or previous event in relation to $post).
			 *
			 * @var WP_Post $post
			 * @var string  $mode (typically "previous" or "next")
			 */
			return apply_filters( 'tribe_events_get_closest_event', $event, $post, $mode );
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
			$link = null;
			$event = $this->get_closest_event( $post, $mode );

			// If we successfully located the next/prev event, we should have precisely one element in $results
			if ( $event ) {
				if ( ! $anchor ) {
					$anchor = apply_filters( 'the_title', $event->post_title, $event->ID );
				} elseif ( strpos( $anchor, '%title%' ) !== false ) {
					// get the nicely filtered post title
					$title = apply_filters( 'the_title', $event->post_title, $event->ID );

					// escape special characters used in the second parameter of preg_replace
					$title = str_replace(
							[
									'\\',
									'$',
							],
							[
									'\\\\',
									'\$',
							],
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
			if ( $file == $this->plugin_dir . 'the-events-calendar.php' ) {
				$anchor   = esc_html__( 'Support', 'the-events-calendar' );
				$links[] = '<a href="' . esc_url( self::$dotOrgSupportUrl ) . '" target="_blank">' . $anchor . '</a>';

				$anchor   = esc_html__( 'View All Add-Ons', 'the-events-calendar' );
				$link = add_query_arg(
						[
								'utm_campaign' => 'in-app',
								'utm_medium'   => 'plugin-tec',
								'utm_source'   => 'plugins-manager',
						],
						self::$tecUrl . self::$addOnPath
				);
				$links[] = '<a href="' . esc_url( $link ) . '" target="_blank">' . $anchor . '</a>';
			}

			return $links;
		}

		/**
		 * Register the dashboard widget.
		 *
		 */
		public function dashboardWidget() {
			wp_add_dashboard_widget(
					'tribe_dashboard_widget', esc_html__( 'News from The Events Calendar', 'the-events-calendar' ),
					[
							$this,
							'outputDashboardWidget',
					]
			);
		}

		/**
		 * Echo the dashboard widget.
		 *
		 * @param int $items
		 *
		 */
		public function outputDashboardWidget( $items = 10 ) {
			echo '<div class="rss-widget">';
			wp_widget_rss_output( Tribe__Main::FEED_URL, [ 'items' => $items ] );
			echo '</div>';
		}

		/**
		 * Set the class property postExceptionThrown.
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
						esc_html__( 'Check out the %savailable add-ons%s.', 'the-events-calendar' ),
						'<a href="' .
						esc_url(
							add_query_arg(
									[
											'utm_campaign' => 'in-app',
											'utm_medium'   => 'plugin-tec',
											'utm_source'   => 'post-editor',
									],
									self::$tecUrl . self::$addOnPath
							)
						)
						. '">',
						'</a>'
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
			return Tribe__Main::post_id_helper( $postId );
		}

		/**
		 * Add the buttons/dropdown to the admin toolbar
		 *
		 * @return null
		 */
		public function add_toolbar_items() {
			$admin_bar = Tribe__Events__Admin__Bar__Admin_Bar::instance();
			if ( ! $admin_bar->is_enabled() ) {
				return;
			}
			global $wp_admin_bar;
			$admin_bar->init( $wp_admin_bar );
		}

		/**
		 * Displays the View Calendar link at the top of the Events list in admin.
		 *
		 */
		public function addViewCalendar() {
			if ( Tribe__Admin__Helpers::instance()->is_screen( 'edit-' . self::POSTTYPE ) ) {
				//Output hidden DIV with Calendar link to be displayed via javascript
				echo '<div id="view-calendar-link-div" style="display:none;"><a class="add-new-h2" href="' . esc_url( $this->getLink() ) . '">' . esc_html__( 'View Calendar', 'the-events-calendar' ) . '</a></div>';
			}
		}

		/**
		 * Set the menu-edit-page to default display the events-related items.
		 *
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

			$current_hidden_boxes = get_user_option( 'metaboxhidden_nav-menus', $user_id );

			if ( $array_key = array_search( 'add-' . self::POSTTYPE, $current_hidden_boxes ) ) {
				unset( $current_hidden_boxes[ $array_key ] );
			}
			if ( $array_key = array_search( 'add-' . Tribe__Events__Venue::POSTTYPE, $current_hidden_boxes ) ) {
				unset( $current_hidden_boxes[ $array_key ] );
			}
			if ( $array_key = array_search( 'add-' . Tribe__Events__Organizer::POSTTYPE, $current_hidden_boxes ) ) {
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
			$actions['settings']       = '<a href="' . Tribe__Settings::instance()->get_url() . '">' . esc_html__( 'Settings', 'the-events-calendar' ) . '</a>';
			$actions['tribe-calendar'] = '<a href="' . $this->getLink() . '">' . esc_html__( 'Calendar', 'the-events-calendar' ) . '</a>';

			return $actions;
		}

		/**
		 * Set up the list view in the view selector in the tribe events bar.
		 *
		 * @param array $views The current views array.
		 *
		 * @return array The modified views array.
		 */
		public function setup_listview_in_bar( $views ) {
			$views[] = [
				'displaying'     => 'list',
				'event_bar_hook' => 'tribe_events_before_template',
				'anchor'         => esc_html__( 'List', 'the-events-calendar' ),
				'url'            => tribe_get_listview_link(),
			];

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
			$views[] = [
				'displaying'     => 'month',
				'event_bar_hook' => 'tribe_events_month_before_template',
				'anchor'         => esc_html__( 'Month', 'the-events-calendar' ),
				'url'            => tribe_get_gridview_link(),
			];

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
			$views[] = [
				'displaying'     => 'day',
				'anchor'         => esc_html__( 'Day', 'the-events-calendar' ),
				'event_bar_hook' => 'tribe_events_before_template',
				'url'            => tribe_get_day_link(),
			];

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
				$label = sprintf( esc_html__( 'Search for %s by Keyword.', 'the-events-calendar' ), $this->plural_event_label );
				$filters['tribe-bar-search'] = [
						'name'    => 'tribe-bar-search',
						'caption' => esc_html__( 'Search', 'the-events-calendar' ),
						'html'    => '<input type="text" name="tribe-bar-search" id="tribe-bar-search" aria-label="' . esc_attr( $label ) . '" value="' . esc_attr( $value ) . '" placeholder="' . esc_attr__( 'Keyword', 'the-events-calendar' ) . '">',
				];
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

			$formats_full = [
				'0'  => __( '4 digit year hyphen 2 digit month hyphen 2 digit day', 'the-events-calendar' ),
				'1'  => __( '1 digit month slash 1 digit day slash 4 digit year', 'the-events-calendar' ),
				'2'  => __( '2 digit month slash 2 digit day slash 4 digit year', 'the-events-calendar' ),
				'3'  => __( '1 digit day slash 1 digit month slash 4 digit year', 'the-events-calendar' ),
				'4'  => __( '2 digit day slash 2 digit month slash 4 digit year', 'the-events-calendar' ),
				'5'  => __( '1 digit month hyphen 1 digit day hyphen 4 digit year', 'the-events-calendar' ),
				'6'  => __( '1 digit month hyphen 2 digit day hyphen 4 digit year', 'the-events-calendar' ),
				'7'  => __( '1 digit day hyphen 1 digit month hyphen 4 digit year', 'the-events-calendar' ),
				'8'  => __( '2 digit day hyphen 2 digit month hyphen 4 digit year', 'the-events-calendar' ),
				'9'  => __( '4 digit year dot 2 digit month dot 2 digit day', 'the-events-calendar' ),
				'10' => __( '2 digit month dot 2 digit day dot 4 digit year', 'the-events-calendar' ),
				'11' => __( '2 digit day dot 2 digit month dot 4 digit year', 'the-events-calendar' ),
			];

			$formats_month = [
				'0'  => __( '4 digit year hyphen 2 digit month', 'the-events-calendar' ),
				'1'  => __( '1 digit month slash 4 digit year', 'the-events-calendar' ),
				'2'  => __( '2 digit month slash 4 digit year', 'the-events-calendar' ),
				'3'  => __( '1 digit month slash 4 digit year', 'the-events-calendar' ),
				'4'  => __( '2 digit month slash 4 digit year', 'the-events-calendar' ),
				'5'  => __( '1 digit month hyphen 4 digit year', 'the-events-calendar' ),
				'6'  => __( '1 digit month hyphen 4 digit year', 'the-events-calendar' ),
				'7'  => __( '1 digit month hyphen 4 digit year', 'the-events-calendar' ),
				'8'  => __( '2 digit month hyphen 4 digit year', 'the-events-calendar' ),
				'9'  => __( '4 digit year dot 2 digit month', 'the-events-calendar' ),
				'10' => __( '2 digit month dot 4 digit year', 'the-events-calendar' ),
				'11' => __( '2 digit month dot 4 digit year', 'the-events-calendar' ),
			];

			if ( ! $wp_query = tribe_get_global_query_object() ) {
				return;
			}

			/**
			 * Allows for customizing the "date search" field value.
			 *
			 * @deprecated 4.6.1 Use tribe_events_bar_date_search_default_value instead.
			 *
			 * @param string $value The "date search" field value, which defaults to an empty string.
			 */
			$value = apply_filters( 'tribe-events-bar-date-search-default-value', '' );

			/**
			 * Allows for customizing the "date search" field value.
			 *
			 * @param string $value The "date search" field value, which defaults to an empty string.
			 */
			$value = apply_filters( 'tribe_events_bar_date_search_default_value', $value );

			if ( ! empty( $_REQUEST['tribe-bar-date'] ) ) {
				$value = $_REQUEST['tribe-bar-date'];
			}

			$datepicker_format = \Tribe__Date_Utils::get_datepicker_format_index();

			$caption = esc_html__( 'Date', 'the-events-calendar' );
			$format  = Tribe__Utils__Array::get( $formats_full, $datepicker_format, $formats_full[0] );
			$label   = sprintf( esc_html__( 'Search for %s by Date. Please use the format %s.', 'the-events-calendar' ), $this->plural_event_label, $format );

			// If there is a value set via the query string or a filter, use that.
			// Otherwise use wp_query if possible. Failover to today's date.
			if ( $value ) {
				$date = $value;
			} elseif ( ! empty( $wp_query->query_vars['eventDate'] ) ) {
				$date = $wp_query->query_vars['eventDate'];
			} else {
				$date = date( 'Y-m-d' );
			}

			if ( tribe_is_month() ) {
				$caption = sprintf( esc_html__( '%s In', 'the-events-calendar' ), $this->plural_event_label );
				$format  = Tribe__Utils__Array::get( $formats_month, $datepicker_format, $formats_month[0] );
				$label   = sprintf( esc_html__( 'Search for %s by month. Please use the format %s.', 'the-events-calendar' ), $this->plural_event_label, $format );
				$value   = date( Tribe__Date_Utils::datepicker_formats( "m{$datepicker_format}" ), strtotime( $date ) );
			} elseif ( tribe_is_list_view() ) {
				$caption = sprintf( esc_html__( '%s From', 'the-events-calendar' ), $this->plural_event_label );
				$value   = date( Tribe__Date_Utils::datepicker_formats( $datepicker_format ), strtotime( $date ) );
			} elseif ( tribe_is_day() ) {
				$caption = esc_html__( 'Day Of', 'the-events-calendar' );
				$value   = date( Tribe__Date_Utils::datepicker_formats( $datepicker_format ), strtotime( $wp_query->query_vars['eventDate'] ) );
			}

			/**
			 * Allows for modifying the "date search" field's caption (e.g. "in" or "from").
			 *
			 * @param string $caption The "date search" field's caption string.
			 */
			$caption = apply_filters( 'tribe_bar_datepicker_caption', $caption );

			$filters['tribe-bar-date'] = [
					'name'    => 'tribe-bar-date',
					'caption' => $caption,
					'html'    => '<input type="text" name="tribe-bar-date" style="position: relative;" id="tribe-bar-date" aria-label="' . esc_attr( $label ) . '" value="' . esc_attr( $value ) . '" placeholder="' . esc_attr__( 'Date', 'the-events-calendar' ) . '"><input type="hidden" name="tribe-bar-date-day" id="tribe-bar-date-day" class="tribe-no-param" value="">',
			];

			return $filters;
		}

		/**
		 * Removes views that have been deselected in the Template Settings as hidden from the view array.
		 *
		 * @param array $views The current views array.
		 * @param bool  $visible
		 *
		 * @return array The new views array.
		 */
		public function remove_hidden_views( $views, $visible = true ) {
			$enable_views_defaults = [];

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
		 */
		public function filter_cron_schedules() {
			add_filter( 'cron_schedules', [ $this, 'register_30min_interval' ] );
		}

		/**
		 * Add a new scheduled task interval (of 30mins).
		 *
		 * @param  array $schedules
		 * @return array
		 */
		public function register_30min_interval( $schedules ) {
			$schedules['every_30mins'] = [
				'interval' => 30 * MINUTE_IN_SECONDS,
				'display'  => esc_html__( 'Once Every 30 Mins', 'the-events-calendar' ),
			];

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
		 * possible for data from a site running PRO to be imported into a site running only
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
		 * Registers the list widget
		 */
		public function register_list_widget() {
			if ( tribe_events_widgets_v2_is_enabled() ) {
				return;
			}

			register_widget( 'Tribe__Events__List_Widget' );
		}

		/**
		 * Inject TEC specific setting fields into the general tab
		 *
		 * @param array $general_tab_fields Fields for the general settings tab
		 *
		 * @return array
		 */
		public function general_settings_tab_fields( $general_tab_fields ) {
			require_once $this->plugin_path . 'src/admin-views/tribe-options-general.php';

			return $general_tab_fields;
		}

		/**
		 * Inject TEC specific setting fields into the display tab
		 *
		 * @param array $display_tab_fields Fields for the display settings tab
		 *
		 * @return array
		 */
		public function display_settings_tab_fields( $display_tab_fields ) {
			require_once $this->plugin_path . 'src/admin-views/tribe-options-display.php';

			return $display_tab_fields;
		}

		/**
		 * When TEC is activated, the Events top level menu item in the dashboard needs the post_type appended to it
		 *
		 * @param string $url Settings URL to filter
		 *
		 * @return string
		 */
		public function tribe_settings_url( $url ) {
			if ( is_network_admin() ) {
				return $url;
			}

			return add_query_arg( [ 'post_type' => self::POSTTYPE ], $url );
		}

		/**
		 * Adds post types to the post_types array used to determine if on a post type screen
		 *
		 * @param array $post_types Collection of post types
		 *
		 * @return array
		 */
		public function is_post_type_screen_post_types( $post_types ) {
			foreach ( self::getPostTypes() as $post_type ) {
				$post_types[] = $post_type;
			}

			return $post_types;
		}

		/**
		 * If tickets don't have an end date, let's provide the end date from the event
		 *
		 * @param string $date
		 * @param int $post_id
		 *
		 * @return string
		 */
		public function default_end_date_for_tickets( $date, $post_id ) {
			$post = get_post( $post_id );

			if ( self::POSTTYPE !== $post->post_type ) {
				return $date;
			}

			return tribe_get_end_date( $post_id, false, 'Y-m-d G:i' );
		}

		/**
		 * Set the currency symbol from tribe_events meta data if available
		 *
		 * @param boolean $currency_symbol Currency symbol to use
		 * @param int $post_id Post ID
		 *
		 * @return string
		 */
		public function maybe_set_currency_symbol_with_post( $currency_symbol, $post_id ) {
			// if the currency symbol is already set, don't alter it
			if ( null !== $currency_symbol ) {
				return $currency_symbol;
			}

			// if there isn't a post id, don't change the symbol
			if ( ! $post_id ) {
				return $currency_symbol;
			}

			// if the post isn't a tribe_events post type, don't alter the symbol
			$post = get_post( $post_id );
			if ( self::POSTTYPE !== $post->post_type ) {
				return $currency_symbol;
			}

			$currency_symbol = tribe_get_event_meta( $post_id, '_EventCurrencySymbol', true );

			return $currency_symbol;
		}

		/**
		 * Set the currency position from tribe_events meta data if available
		 *
		 * @param boolean $reverse_position Whether to reverse the location of the currency symbol
		 * @param int $post_id Post ID
		 *
		 * @return boolean
		 */
		public function maybe_set_currency_position_with_post( $reverse_position, $post_id ) {
			// if the currency symbol is already set, don't alter it
			if ( null !== $reverse_position ) {
				return $reverse_position;
			}

			// if there isn't a post id, don't change the symbol
			if ( ! $post_id ) {
				return $reverse_position;
			}

			// if the post isn't a tribe_events post type, don't alter the symbol
			$post = get_post( $post_id );
			if ( self::POSTTYPE !== $post->post_type ) {
				return $reverse_position;
			}

			$reverse_position = tribe_get_event_meta( $post_id, '_EventCurrencyPosition', true );
			$reverse_position = ( 'suffix' === $reverse_position );

			return $reverse_position;
		}

		/**
		 * Filters the chunkable post types.
		 *
		 * @param array $post_types
		 * @return array The filtered post types
		 */
		public function filter_meta_chunker_post_types( array $post_types ) {
			$post_types = array_merge(
					$post_types,
					[
							self::POSTTYPE,
							Tribe__Events__Venue::POSTTYPE,
							Tribe__Events__Organizer::POSTTYPE,
							Tribe__Events__Aggregator__Records::$post_type,
					]
			);

			return $post_types;
		}

		/************************
		 *                      *
		 *  Deprecated Methods  *
		 *                      *
		 ************************/

		/**
		 * Make sure we are loading a style for all logged-in users when we have the admin menu
		 *
		 * @deprecated 4.6.21
		 *
		 * @return void
		 */
		public function enqueue_wp_admin_menu_style() {
			_deprecated_function(
				__METHOD__,
				'We are now using `Assets.php` class to load all CSS files',
				'4.6.21'
			);
		}

		/**
		 * Load asset packages.
		 *
		 * @deprecated 4.6.21
		 */
		public function loadStyle() {
			_deprecated_function(
				__METHOD__,
				'We are now using `Assets.php` class to load all CSS files',
				'4.6.21'
			);
		}

		/**
		 * Add admin scripts and styles
		 *
		 * @deprecated 4.6.21
		 */
		public function add_admin_assets() {
			_deprecated_function(
				__METHOD__,
				'tribe( "tec.assets" )->load_admin()',
				'4.6.21'
			);
			tribe( 'tec.assets' )->load_admin();
		}

		/**
		 * Compatibility fix: some plugins enqueue jQuery UI/other styles on all post screens,
		 * breaking our own custom styling of event editor components such as the datepicker.
		 *
		 * Needs to execute late enough during admin_enqueue_scripts that the items we are removing
		 * have already been registered and enqueued.
		 *
		 * @deprecated 4.6.21
		 *
		 * @see https://github.com/easydigitaldownloads/easy-digital-downloads/issues/3033
		 */
		public function asset_fixes() {
			_deprecated_function(
				__METHOD__,
				'tribe( "tec.assets" )->dequeue_incompatible()',
				'4.6.21'
			);

			tribe( 'tec.assets' )->dequeue_incompatible();
		}

		/**
		 * Localize admin.
		 *
		 * @deprecated 4.6.21
		 *
		 * @return array
		 */
		public function localizeAdmin() {
			_deprecated_function(
				__METHOD__,
				'We are now using `Assets.php` class to load all JS localization',
				'4.6.21'
			);

			$bits = [
				'ajaxurl' => esc_url_raw(
					admin_url( 'admin-ajax.php', ( is_ssl() || FORCE_SSL_ADMIN ? 'https' : 'http' ) )
				),
			];

			return $bits;
		}

		/**
		 * Output localized admin javascript
		 *
		 * @deprecated 4.6.21
		 */
		public function printLocalizedAdmin() {
			_deprecated_function(
				__METHOD__,
				'We are now using `Assets.php` class to load all JS localization',
				'4.6.21'
			);
			wp_localize_script( 'tribe-events-admin', 'TEC', $this->localizeAdmin() );
		}

		/**
		 * Modify the post type args to set Dashicon if we're in WP 3.8+
		 *
		 * @deprecated 4.6.21
		 *
		 * @return array post type args
		 **/
		public function setDashicon( $postTypeArgs ) {
			_deprecated_function(
				__METHOD__,
				'We add `menu_icon` goes into the base arguments',
				'4.6.21'
			);

			global $wp_version;

			if ( version_compare( $wp_version, 3.8 ) >= 0 ) {
				$postTypeArgs['menu_icon'] = 'dashicons-calendar';
			}

			return $postTypeArgs;
		}

		/**
		 * ensure only one venue or organizer is created during post preview
		 * subsequent previews will reuse that same post
		 *
		 *
		 * ensure that preview post is the one that's used when the event is published,
		 * unless we're publishing with a saved venue
		 *
		 * @param $post_type can be 'venue' or 'organizer'
		 */
		protected function manage_preview_metapost( $post_type, $event_id ) {

			if ( ! in_array( $post_type, [ 'venue', 'organizer' ] ) ) {
				return;
			}

			$posttype        = ucfirst( $post_type );
			$posttype_id     = $posttype . 'ID';
			$meta_key        = '_preview_' . $post_type . '_id';
			$valid_post_id   = "tribe_get_{$post_type}_id";
			$create          = "create$posttype";
			$preview_post_id = get_post_meta( $event_id, $meta_key, true );
			$doing_preview   = ( $_REQUEST['wp-preview'] == 'dopreview' );

			if ( empty( $_POST[ $posttype ][ $posttype_id ] ) ) {
				// the event is set to use a new metapost
				if ( $doing_preview ) {
					// we're previewing
					if ( $preview_post_id && $preview_post_id == $valid_post_id( $preview_post_id ) ) {
						// a preview post has been created and is valid, update that
						wp_update_post(
								[
										'ID'         => $preview_post_id,
										'post_title' => $_POST[ $posttype ][ $posttype ],
								]
						);
					} else {
						// a preview post has not been created yet, or is not valid - create one and save the ID
						$preview_post_id = Tribe__Events__API::$create( $_POST[ $posttype ], 'draft' );
						update_post_meta( $event_id, $meta_key, $preview_post_id );
					}
				}
			}
		}

		/**
		 * Prevents duplicate venues or organizers when previewing an event.
		 *
		 *
		 * @since 4.5.1
		 */
		public function maybe_add_preview_venues_and_organizers() {

			if ( ! is_singular( self::POSTTYPE ) ) {
				return;
			}

			$event_id     = get_the_ID();
			$event_status = get_post_status( $event_id );

			$is_event_preview = is_preview() && ( 'auto-draft' === $event_status );

			if ( ! $is_event_preview ) {
				return;
			}

			$this->add_preview_venues( $event_id );
			$this->add_preview_organizers( $event_id );
		}

		/**
		 * Specify the "preview venue" to link to an event.
		 *
		 *
		 * @since 4.5.1
		 *
		 * @param int $event_id The ID of the event being previewed.
		 */
		public function add_preview_venues( $event_id ) {

			$venue_id = get_post_meta( $event_id, '_EventVenueID', true );

			// Prevent imported venues from being auto-deleted.
			$venue_origin = get_post_meta( $venue_id, '_VenueOrigin', true );

			if ( 'events-calendar' !== $venue_origin ) {
				return;
			}

			$venue_status     = get_post_status( $venue_id );

			$is_preview_venue = 'draft' === $venue_status || 'auto-draft' === $venue_status;

			if ( ! $is_preview_venue ) {
				return;
			}

			$this->link_preview_venue_to_event( $venue_id, $event_id );
		}

		/**
		 * Specify the "preview organizer" to link to an event.
		 *
		 *
		 * @since 4.5.1
		 *
		 * @param int $event_id The ID of the event being previewed.
		 */
		public function add_preview_organizers( $event_id ) {

			$organizer_ids = get_post_meta( $event_id, '_EventOrganizerID', false );

			if ( empty( $organizer_ids ) || ! is_array( $organizer_ids ) ) {
				return;
			}

			foreach ( $organizer_ids as $key => $organizer_id ) {

				$organizer_status = get_post_status( $organizer_id );

				$is_preview_organizer = 'draft' === $organizer_status || 'auto-draft' === $organizer_status;

				if ( ! $is_preview_organizer ) {
					unset( $organizer_ids[ $key ] );
				}
			}

			$this->link_preview_organizer_to_event( $organizer_ids, $event_id );
		}

		/**
		 * Identifies "preview" venues as duplicates and worthy of later deletion.
		 *
		 *
		 * @since 4.5.1
		 *
		 * @param int $venue_id ID of venue being identified as a duplicate.
		 * @param int $event_id ID of event being previewed.
		 */
		public function link_preview_venue_to_event( $venue_id, $event_id ) {

			$preview_venues = (array) get_post_meta( $event_id, '_preview_venues', true );

			$preview_venues[] = $venue_id;

			// Remove empty and duplicate values, which can easily arise here.
			$preview_venues = array_filter( $preview_venues );
			$preview_venues = array_unique( $preview_venues );

			update_post_meta( $event_id, '_preview_venues', array_values( $preview_venues ) );
		}

		/**
		 * Identifies "preview" venues as duplicates and worthy of later deletion.
		 *
		 *
		 * @since 4.5.1
		 *
		 * @param int $venue_id ID of venue being identified as a duplicate.
		 * @param int $event_id ID of event being previewed.
		 */
		public function link_preview_organizer_to_event( $organizer_ids, $event_id ) {

			$preview_organizers = (array) get_post_meta( $event_id, '_preview_organizers', true );

			foreach ( $organizer_ids as $key => $organizer_id ) {
				$preview_organizers[] = $organizer_id;
			}

			// Remove empty and duplicate values, which can easily arise here.
			$preview_organizers = array_filter( $preview_organizers );
			$preview_organizers = array_unique( $preview_organizers );

			update_post_meta( $event_id, '_preview_organizers', array_values( $preview_organizers ) );
		}

		/**
		 * Removes "preview" venues on a given event if any exist.
		 *
		 *
		 * @since 4.5.1
		 *
		 * @param int $event_id The event ID whose preview venues to remove.
		 * @param bool $delete_meta Whether to delete existing _EventVenueID
		 */
		public function remove_preview_venues( $event_id, $delete_meta = false ) {

			$event_id = absint( $event_id );

			if ( ! $event_id ) {
				return;
			}

			$preview_venues = get_post_meta( $event_id, '_preview_venues', true );

			if ( ! is_array( $preview_venues ) || empty( $preview_venues ) ) {
				return;
			}

			foreach ( $preview_venues as $key => $venue_id ) {
				wp_delete_post( $venue_id );
			}

			// In some cases, one must clear the _EventVenueID before it's regenerated.
			if ( $delete_meta ) {
				delete_post_meta( $event_id, '_EventVenueID' );
			}
		}

		/**
		 * Removes "preview" organizers on a given event if any exist.
		 *
		 *
		 * @since 4.5.1
		 *
		 * @param int $event_id The event ID whose preview organizers to remove.
		 * @param bool $delete_meta Whether to delete existing _EventOrganizerID
		 */
		public function remove_preview_organizers( $event_id, $delete_meta = false ) {

			$event_id = absint( $event_id );

			if ( ! $event_id ) {
				return;
			}

			$preview_organizers = get_post_meta( $event_id, '_preview_organizers', true );

			if ( ! is_array( $preview_organizers ) || empty( $preview_organizers ) ) {
				return;
			}

			foreach ( $preview_organizers as $key => $organizer_id ) {
				wp_delete_post( $organizer_id );
			}

			// In some cases, one must clear the _EventOrganizerID before it's regenerated.
			if ( $delete_meta ) {
				delete_post_meta( $event_id, '_EventOrganizerID' );
			}
		}

		/**
		 * Fetches and verify if we had a delayed activation
		 *
		 * @deprecated 4.8
		 *
		 * @since  4.7
		 *
		 * @return boolean [description]
		 */
		public function is_delayed_activation() {
			_deprecated_function(
				__METHOD__,
				'',
				'4.8'
			);

			return (bool) get_transient( $this->key_delayed_activation_outdated_common );
		}

		/**
		 * Checks if currently loaded Common Lib version is incompatible with The Events Calendar
		 * Sets a transient flag for us to be able to trigger plugin activation hooks on a later request
		 *
		 * @deprecated 4.8
		 *
		 * @since  4.7
		 *
		 * @return bool
		 */
		public function maybe_delay_activation_if_outdated_common() {
			_deprecated_function(
				__METHOD__,
				'',
				'4.8'
			);

			// Only if Common is loaded correctly
			if ( ! class_exists( 'Tribe__Main' ) ) {
				return false;
			}

			$common_version = Tribe__Main::VERSION;

			// We need tribe-common-info to be loaded to test
			if ( empty( $GLOBALS['tribe-common-info'] ) ) {
				return false;
			}

			// Only when this common lib is newer than the loaded one on activation we bail
			if ( ! version_compare( $GLOBALS['tribe-common-info']['version'], $common_version, '>' ) ) {
				return false;
			}

			// Set a transient forever to flag delayed activation
			set_transient( $this->key_delayed_activation_outdated_common, 1, 0 );

			return true;
		}

		/**
		 * Check add-ons to make sure they are supported by currently running TEC version.
		 *
		 * @deprecated 4.8
		 *
		 */
		public function checkAddOnCompatibility() {
			_deprecated_function(
				__METHOD__,
				'Use Tribe__Dependency Class instead.',
				'4.8'
			);

			// Variable for storing output to admin notices.
			$output = '';

			// Array to store any plugins that are out of date.
			$out_of_date_addons = [];

			// Array to store all addons and their required CORE versions.
			$tec_addons_required_versions = [];

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
					// don't throw notices for the 4.2 legacy versions of Facebook and iCal
					if (
						(
							false !== strpos( $plugin['plugin_dir_file'], 'the-events-calendar-facebook-importer.php' )
							|| false !== strpos( $plugin['plugin_dir_file'], 'the-events-calendar-ical-importer.php' )
						)
						&& version_compare( $plugin['current_version'], '4.2', '>=' )
					) {
						continue;
					}

					$out_of_date_addons[] = $plugin['plugin_name'] . ' ' . $plugin['current_version'];
				}
			}
			// If Core is out of date, generate the proper message.
			if ( $tec_out_of_date == true ) {
				$plugin_short_path = basename( dirname( dirname( __FILE__ ) ) ) . '/the-events-calendar.php';
				$upgrade_path      = wp_nonce_url(
						add_query_arg(
								[
										'action' => 'upgrade-plugin',
										'plugin' => $plugin_short_path,
								],
								get_admin_url() . 'update.php'
						),
						'upgrade-plugin_' . $plugin_short_path
				);
				$output .= '<div class="error">';
				$output .= '<p>' . sprintf( esc_html__( 'Your version of The Events Calendar is not up-to-date with one of your The Events Calendar add-ons. Please %supdate now.%s', 'the-events-calendar' ), '<a href="' . esc_url( $upgrade_path ) . '">', '</a>' ) . '</p>';
				$output .= '</div>';
			} elseif ( ! empty( $out_of_date_addons ) ) {
				// Otherwise, if the addons are out of date, generate the proper messaging.
				$output .= '<div class="error">';
				$link = add_query_arg(
						[
								'utm_campaign' => 'in-app',
								'utm_medium'   => 'plugin-tec',
								'utm_source'   => 'notice',
						],
						self::$tecUrl . 'knowledgebase/version-compatibility/'
				);
				$output .= '<p>' . sprintf( esc_html__( 'The following plugins are out of date: %1$s. All add-ons contain dependencies on The Events Calendar and will not function properly unless paired with the right version. %2$sLearn More%3$s.', 'the-events-calendar' ), '<b>' . join( ', ', $out_of_date_addons ) . '</b>', "<a href='" . esc_url( $link ) . "' target='_blank'>", '</a>' ) . '</p>';
				$output .= '</div>';
			}
			// Make sure only to show the message if the user has the permissions necessary.
			if ( current_user_can( 'edit_plugins' ) ) {
				echo apply_filters( 'tribe_add_on_compatibility_errors', $output );
			}
		}

		/**
		 * displays the saved venue dropdown in the event metabox
		 * Used to be a PRO only feature, but as of 3.0, it is part of Core.
		 *
		 * @deprecated 4.4
		 *
		 * @param int $post_id the event ID for which to create the dropdown
		 */
		public function displayEventVenueDropdown( $post_id ) {
			_deprecated_function(
				__METHOD__,
				'Tribe__Events__Linked_Posts__Chooser_Meta_Box( $event_id, "tribe_venue" )->render()',
				'4.4'
			);

			$venue_id = get_post_meta( $post_id, '_EventVenueID', true );

			// Strange but true: the following func lives in core so is safe to call without a func_exists check
			$new_community_post = tribe_is_community_edit_event_page() && ! $post_id;
			$new_admin_post     = 'auto-draft' === get_post_status( $post_id );

			if ( ! $venue_id && ( $new_admin_post || $new_community_post ) ) {
				$venue_id = $this->defaults()->venue_id();
			}

			$venue_id = apply_filters( 'tribe_display_event_venue_dropdown_id', $venue_id );
			?>
			<tr class="saved-linked-post">
				<td>
					<label for="saved_tribe_venue">
						<?php printf( __( 'Use Saved %s:', 'the-events-calendar' ), $this->singular_venue_label ); ?>
					</label>
				</td>
				<td>
					<?php
					Tribe__Events__Linked_Posts::instance()->saved_linked_post_dropdown( Tribe__Events__Venue::POSTTYPE, $venue_id );
					$venue_pto = get_post_type_object( Tribe__Events__Venue::POSTTYPE );
					if ( current_user_can( $venue_pto->cap->edit_posts ) ) {
						//Use Admin Link Unless on Community Events Editor then use Front End Link to Edit
						$edit_link = admin_url( sprintf( 'post.php?action=edit&post=%s', $venue_id ) );
						if ( tribe_is_community_edit_event_page() ) {
							$edit_link = Tribe__Events__Community__Main::instance()->getUrl( 'edit', $venue_id, null, Tribe__Events__Venue::POSTTYPE );
						}
						?>
						<div class="edit-linked-post-link">
							<a data-admin-url="<?php echo esc_url( admin_url( 'post.php?action=edit&post=' ) ); ?>" href="<?php echo esc_url( $edit_link ); ?>" target="_blank" <?php if ( empty( $venue_id ) ) { ?>style="display:none;"<?php } ?>>
								<?php echo esc_html( sprintf( __( 'Edit %s', 'the-events-calendar' ), $this->singular_venue_label ) ); ?>
							</a>
						</div>
						<?php
					}//end if
					?>
				</td>
			</tr>
		<?php
		}

		/**
		 * displays the saved organizer dropdown in the event metabox
		 * Used to be a PRO only feature, but as of 3.0, it is part of Core.
		 *
		 * @deprecated 4.4
		 *
		 * @param int $post_id the event ID for which to create the dropdown
		 *
		 */
		public function displayEventOrganizerDropdown( $post_id ) {
			_deprecated_function(
				__FUNCTION__,
				'Tribe__Events__Linked_Posts__Chooser_Meta_Box( $event_id, "tribe_organizer" )->render()',
				'4.4'
			);

			$current_organizer = get_post_meta( $post_id, '_EventOrganizerID', true );

			if (
				( ! $post_id || get_post_status( $post_id ) === 'auto-draft' ) &&
				! $current_organizer &&
				tribe_is_community_edit_event_page()
			) {
				$current_organizer = $this->defaults()->organizer_id();
			}
			$current_organizer = apply_filters( 'tribe_display_event_organizer_dropdown_id', $current_organizer );

			?>
			<tr class="venue-select-posts">
				<td>
					<label for="saved_organizer"><?php printf( esc_html__( 'Use Saved %s:', 'the-events-calendar' ), $this->singular_organizer_label ); ?></label>
				</td>
				<td>
					<?php $this->saved_organizers_dropdown( $current_organizer ); ?>
					<div class="edit-organizer-link"<?php if ( empty( $current_organizer ) ) { ?> style="display:none;"<?php } ?>>
						<a data-admin-url="<?php echo esc_url( admin_url( 'post.php?action=edit&post=' ) ); ?>" href="<?php echo esc_url( admin_url( sprintf( 'post.php?action=edit&post=%s', $current_organizer ) ) ); ?>" target="_blank">
							<?php echo esc_html( sprintf( __( 'Edit %s', 'the-events-calendar' ), $this->singular_organizer_label ) ); ?>
						</a>
					</div>
				</td>
			</tr>
		<?php
		}

		/**
		 * Method to initialize Common Object
		 *
		 * @deprecated 4.3.4
		 *
		 * @return Tribe__Main
		 */
		public function common() {
			_deprecated_function( __METHOD__, '4.3.4', 'Tribe__Main::instance( $context )' );
			return Tribe__Main::instance( $this );
		}

		/**
		 * Load the text domain.
		 *
		 * @deprecated 4.3.4
		 *
		 */
		public function loadTextDomain() {
			_deprecated_function( __METHOD__, '4.3.4', 'Tribe__Main::instance()->load_text_domain( \'the-events-calendar\', $this->plugin_dir . \'lang/\' );' );
		}

		/**
		 * Init the settings API and add a hook to add your own setting tabs (disused since 4.3,
		 * does nothing when called).
		 *
		 * @deprecated 4.3
		 *
		 */
		public function initOptions() {
			_deprecated_function( __METHOD__, '4.3' );
		}

		/**
		 * Sets the globally shared `$_tribe_meta_factory` object
		 *
		 * @deprecated 4.3
		 *
		 */
		public function set_meta_factory_global() {
			_deprecated_function( __METHOD__, '4.3' );
			global $_tribe_meta_factory;
			$_tribe_meta_factory = new Tribe__Events__Meta_Factory();
		}

		/**
		 * helper function for displaying the saved venue dropdown
		 * Used to be a PRO only feature, but as of 3.0, it is part of Core.
		 *
		 * @deprecated 4.2
		 *
		 * @param mixed  $current the current saved venue
		 * @param string $name    the name value for the field
		 */
		public function saved_venues_dropdown( $current = null, $name = 'venue[VenueID]' ) {
			_deprecated_function( __METHOD__, '4.2', 'Tribe__Events__Linked_Posts::saved_linked_post_dropdown' );
			Tribe__Events__Linked_Posts::instance()->saved_linked_post_dropdown( Tribe__Events__Venue::POSTTYPE, $current );
		}

		/**
		 * helper function for displaying the saved organizer dropdown
		 * Used to be a PRO only feature, but as of 3.0, it is part of Core.
		 *
		 * @deprecated 4.2
		 *
		 * @param mixed  $current the current saved venue
		 * @param string $name    the name value for the field
		 */
		public function saved_organizers_dropdown( $current = null, $name = 'organizer[OrganizerID]' ) {
			_deprecated_function( __METHOD__, '4.2', 'Tribe__Events__Linked_Posts::saved_linked_post_dropdown' );
			Tribe__Events__Linked_Posts::instance()->saved_linked_post_dropdown( Tribe__Events__Organizer::POSTTYPE, $current );
		}

		/**
		 * When the edit-tags.php screen loads, setup filters
		 * to fix the tagcloud links
		 *
		 * @deprecated 4.1.2
		 *
		 */
		public function prepare_to_fix_tagcloud_links() {
			_deprecated_function( __METHOD__, '4.1.2' );
			if ( Tribe__Admin__Helpers::instance()->is_post_type_screen( self::POSTTYPE ) ) {
				add_filter( 'get_edit_term_link', [ $this, 'add_post_type_to_edit_term_link' ], 10, 4 );
			}
		}

		/**
		 * Tag clouds in the admin don't pass the post type arg
		 * when getting the edit link. If we're on the tag admin
		 * in Events post type context, make sure we add that
		 * arg to the edit tag link
		 *
		 * @deprecated 4.1.2
		 *
		 * @param string $link
		 * @param int    $term_id
		 * @param string $taxonomy
		 * @param string $context
		 *
		 * @return string
		 */
		public function add_post_type_to_edit_term_link( $link, $term_id, $taxonomy, $context ) {
			_deprecated_function( __METHOD__, '4.1.2' );
			if ( $taxonomy == 'post_tag' && empty( $context ) ) {
				$link = add_query_arg( [ 'post_type' => self::POSTTYPE ], $link );
			}

			return esc_url_raw( $link );
		}

		/**
		 * Insert an array after a specified key within another array.
		 *
		 * @deprecated 4.0
		 *
		 * @param $key
		 * @param $source_array
		 * @param $insert_array
		 *
		 * @return array
		 */
		public static function array_insert_after_key( $key, $source_array, $insert_array ) {
			_deprecated_function( __METHOD__, '4.0', 'Tribe__Main::array_insert_after_key' );

			return self::instance()->common()->array_insert_after_key( $key, $source_array, $insert_array );
		}

		/**
		 * Insert an array immediately before a specified key within another array.
		 *
		 * @deprecated 4.0
		 *
		 * @param $key
		 * @param $source_array
		 * @param $insert_array
		 *
		 * @return array
		 */
		public static function array_insert_before_key( $key, $source_array, $insert_array ) {
			_deprecated_function( __METHOD__, '4.0', 'Tribe__Main::array_insert_before_key' );

			return self::instance()->common()->array_insert_before_key( $key, $source_array, $insert_array );
		}

		/**
		 * Create setting tabs
		 *
		 * @deprecated 4.0
		 *
		 */
		public function doSettingTabs() {
			_deprecated_function( __METHOD__, '4.0', 'Tribe__Settings_Manager::do_setting_tabs' );
			Tribe__Settings_Manager::instance()->do_setting_tabs();
		}

		/**
		 * Create the help tab
		 *
		 * @deprecated 4.0
		 *
		 */
		public function doHelpTab() {
			_deprecated_function( __METHOD__, '4.0', 'Tribe__Settings_Manager::do_help_tab' );
			Tribe__Settings_Manager::instance()->do_help_tab();
		}

		/**
		 * Get taxonomy rewrite slug.
		 *
		 * This method returns a concatenation of the base rewrite slug (ie "events") and the taxonomy slug
		 * (ie "category"). If you only wish the taxonomy slug itself, you should call the get_tax_slug()
		 * method.
		 *
		 * @deprecated 4.0 please use getRewriteSlug() and get_category_slug() instead
		 *
		 * @return string
		 */
		public function getTaxRewriteSlug() {
			_deprecated_function( __METHOD__, '4.0', 'Tribe__Events__Main::get_category_slug' );

			$slug = $this->getRewriteSlug() . '/' . $this->category_slug;

			/**
			 * @deprecated since 4.0
			 */
			return apply_filters( 'tribe_events_category_rewrite_slug', $slug );
		}

		/**
		 * Get tag rewrite slug.
		 *
		 * This method returns a concatenation of the base rewrite slug (ie "events") and the tag taxonomy slug
		 * (ie "tag"). If you only wish the taxonomy slug itself, you should call the get_tag_slug()
		 * method.
		 *
		 * @deprecated 4.0 please use getRewriteSlug() and get_tag_slug() instead
		 *
		 * @return string
		 */
		public function getTagRewriteSlug() {
			_deprecated_function( __METHOD__, '4.0', 'Tribe__Events__Main::get_tag_slug' );

			$slug = $this->getRewriteSlug() . '/' . $this->tag_slug;

			/**
			 * @deprecated since 4.0
			 */
			return apply_filters( 'tribe_events_tag_rewrite_slug', $slug );
		}

		/**
		 * Get all options for the Events Calendar
		 *
		 * @deprecated 4.0
		 *
		 * @return array of options
		 */
		public static function getOptions() {
			_deprecated_function( __METHOD__, '4.0', 'Tribe__Settings_Manager::get_options' );
			return Tribe__Settings_Manager::get_options();
		}

		/**
		 * Get value for a specific option
		 *
		 * @deprecated 4.0
		 *
		 * @param string $optionName name of option
		 * @param string $default    default value
		 *
		 * @return mixed results of option query
		 */
		public static function getOption( $optionName, $default = '' ) {
			_deprecated_function( __METHOD__, '4.0', 'Tribe__Settings_Manager::get_option' );
			return Tribe__Settings_Manager::get_option( $optionName, $default );
		}

		/**
		 * Saves the options for the plugin
		 *
		 * @deprecated 4.0
		 *
		 * @param array $options formatted the same as from getOptions()
		 * @param bool  $apply_filters
		 *
		 */
		public function setOptions( $options, $apply_filters = true ) {
			_deprecated_function( __METHOD__, '4.0', 'Tribe__Settings_Manager::set_options' );
			Tribe__Settings_Manager::set_options( $options, $apply_filters );
		}

		/**
		 * Set an option
		 *
		 * @deprecated 4.0
		 *
		 * @param string $name
		 * @param mixed  $value
		 *
		 */
		public function setOption( $name, $value ) {
			_deprecated_function( __METHOD__, '4.0', 'Tribe__Settings_Manager::set_option' );
			Tribe__Settings_Manager::set_option( $name, $value );
		}

		/**
		 * Get all network options for the Events Calendar
		 *
		 * @deprecated 4.0
		 *
		 * @return array of options
		 */
		public static function getNetworkOptions() {
			_deprecated_function( __METHOD__, '4.0', 'Tribe__Settings_Manager::get_network_options' );
			return Tribe__Settings_Manager::get_network_options();
		}

		/**
		 * Get value for a specific network option
		 *
		 * @deprecated 4.0
		 *
		 * @param string $optionName name of option
		 * @param string $default    default value
		 *
		 * @return mixed results of option query
		 */
		public function getNetworkOption( $optionName, $default = '' ) {
			_deprecated_function( __METHOD__, '4.0', 'Tribe__Settings_Manager::get_network_option' );
			return Tribe__Settings_Manager::get_network_option( $optionName, $default );
		}

		/**
		 * Saves the network options for the plugin
		 *
		 * @deprecated 4.0
		 *
		 * @param array $options formatted the same as from getOptions()
		 * @param bool  $apply_filters
		 *
		 */
		public function setNetworkOptions( $options, $apply_filters = true ) {
			_deprecated_function( __METHOD__, '4.0', 'Tribe__Settings_Manager::set_network_options' );
			Tribe__Settings_Manager::set_network_options( $options, $apply_filters );
		}

		/**
		 * Add the network admin options page
		 *
		 * @deprecated 4.0
		 *
		 */
		public function addNetworkOptionsPage() {
			_deprecated_function( __METHOD__, '4.0', 'Tribe__Settings_Manager::add_network_options_page' );
			Tribe__Settings_Manager::add_network_options_page();
		}

		/**
		 * Render network admin options view
		 *
		 * @deprecated 4.0
		 *
		 */
		public function doNetworkSettingTab() {
			_deprecated_function( __METHOD__, '4.0', 'Tribe__Settings_Manager::do_network_settings_tab' );
			Tribe__Settings_Manager::do_network_settings_tab();
		}

		/**
		 * Save hidden tabs
		 *
		 * @deprecated 4.0
		 *
		 */
		public function saveAllTabsHidden() {
			_deprecated_function( __METHOD__, '4.0', 'Tribe__Settings_Manager::save_all_tabs_hidden' );
			Tribe__Settings_Manager::instance()->save_all_tabs_hidden();
		}

		/**
		 * Truncate a given string.
		 *
		 * @deprecated 4.0
		 *
		 * @param string $text           The text to truncate.
		 * @param int    $excerpt_length How long you want it to be truncated to.
		 *
		 * @return string The truncated text.
		 */
		public function truncate( $text, $excerpt_length = 44 ) {
			_deprecated_function( __FUNCTION__, '4.0', 'tribe_events_get_the_excerpt()' );

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
		 * Tribe debug function. usage: Tribe__Debug::debug( 'Message', $data, 'log' );
		 *
		 * @deprecated 4.0
		 *
		 * @param string      $title  Message to display in log
		 * @param string|bool $data   Optional data to display
		 * @param string      $format Optional format (log|warning|error|notice)
		 *
		 */
		public static function debug( $title, $data = false, $format = 'log' ) {
			_deprecated_function( __METHOD__, '4.0', 'Tribe__Debug::debug' );
			Tribe__Debug::debug( $title, $data, $format );
		}

		/**
		 * Render the debug logging to the php error log. This can be over-ridden by removing the filter.
		 *
		 * @deprecated 4.0
		 *
		 * @param string      $title  - message to display in log
		 * @param string|bool $data   - optional data to display
		 * @param string      $format - optional format (log|warning|error|notice)
		 *
		 */
		public function renderDebug( $title, $data = false, $format = 'log' ) {
			_deprecated_function( __METHOD__, '4.0', 'Tribe__Debug::render' );
			Tribe__Debug::render( $title, $data, $format );
		}

		/**
		 * Define an admin notice
		 *
		 * @deprecated 4.0
		 *
		 * @param string $key
		 * @param string $notice
		 *
		 * @return bool
		 */
		public static function setNotice( $key, $notice ) {
			_deprecated_function( __METHOD__, '4.0', 'Tribe__Notices::set_notice' );
			return Tribe__Notices::set_notice( $key, $notice );
		}

		/**
		 * Check to see if an admin notice exists
		 *
		 * @deprecated 4.0
		 *
		 * @param string $key
		 *
		 * @return bool
		 */
		public static function isNotice( $key ) {
			_deprecated_function( __METHOD__, '4.0', 'Tribe__Notices::is_notice' );
			return Tribe__Notices::is_notice( $key );
		}

		/**
		 * Remove an admin notice
		 *
		 * @deprecated 4.0
		 *
		 * @param string $key
		 *
		 * @return bool
		 */
		public static function removeNotice( $key ) {
			_deprecated_function( __METHOD__, '4.0', 'Tribe__Notices::remove_notice' );
			return Tribe__Notices::remove_notice( $key );
		}

		/**
		 * Get the admin notices
		 *
		 * @deprecated 4.0
		 *
		 * @return array
		 */
		public static function getNotices() {
			_deprecated_function( __METHOD__, '4.0', 'Tribe__Notices::get' );
			return Tribe__Notices::get();
		}

		/**
		 * Add help menu item to the admin (unless blocked via network admin settings).
		 *
		 * @deprecated 4.0
		 *
		 */
		public function addHelpAdminMenuItem() {
			_deprecated_function( __METHOD__, '4.0', 'Tribe__Settings_Manager::add_help_admin_menu_item' );
			Tribe__Settings_Manager::instance()->add_help_admin_menu_item();
		}

		/**
		 * Allow programmatic override of defaultValueReplace setting
		 *
		 * @deprecated 4.0
		 *
		 * @return boolean
		 */
		public function defaultValueReplaceEnabled() {

			_deprecated_function( __METHOD__, '4.0', "tribe_get_option( 'defaultValueReplace' )" );

			if ( ! is_admin() ) {
				return false;
			}

			return tribe_get_option( 'defaultValueReplace' );

		}

		/**
		 * Converts a set of inputs to YYYY-MM-DD HH:MM:SS format for MySQL
		 *
		 * @deprecated 3.11
		 *
		 * @param string $date     The date.
		 * @param int    $hour     The hour of the day.
		 * @param int    $minute   The minute of the hour.
		 * @param string $meridian "am" or "pm".
		 *
		 * @return string The date and time.
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
		 * @deprecated 4.0
		 *
		 * @param string $date The date.
		 *
		 * @return string The cleaned-up date.
		 */
		protected function dateHelper( $date ) {
			_deprecated_function( __METHOD__, '3.11', 'date' );

			if ( $date == '' ) {
				return date( Tribe__Date_Utils::DBDATEFORMAT );
			}

			$date = str_replace( [ '-', '/', ' ', ':', chr( 150 ), chr( 151 ), chr( 45 ) ], '-', $date );
			// ensure no extra bits are added
			list( $year, $month, $day ) = explode( '-', $date );

			if ( ! checkdate( $month, $day, $year ) ) {
				$date = date( Tribe__Date_Utils::DBDATEFORMAT );
			} // today's date if error
			else {
				$date = $year . '-' . $month . '-' . $day;
			}

			return $date;
		}

		/**
		 * Helper used to test if PRO is present and activated.
		 *
		 * This method should no longer be used, but is being retained to avoid potential
		 * for fatal errors where core is updated before an addon plugin - such as Community
		 * Events 3.4 or earlier - which might otherwise occur were it removed completely.
		 *
		 * @deprecated 3.7
		 *
		 * @param string $version
		 *
		 * @return bool
		 */
		public static function ecpActive( $version = '2.0.7' ) {
			return class_exists( 'Tribe__Events__Pro__Main' ) && defined( 'Tribe__Events__Pro__Main::VERSION' ) && version_compare( Tribe__Events__Pro__Main::VERSION, $version, '>=' );
		}

		/**
		 * Returns the autoloader singleton instance to use in a context-aware manner.
		 *
		 * @since 4.9.2
		 *
		 * @return \Tribe__Autoloader The singleton common Autoloader instance.
		 */
		public function get_autoloader_instance() {
			if ( ! class_exists( 'Tribe__Autoloader' ) ) {
				require_once $GLOBALS['tribe-common-info']['dir'] . '/Autoloader.php';

				Tribe__Autoloader::instance()->register_prefixes( [
					'Tribe__' => $GLOBALS['tribe-common-info']['dir'],
				] );
			}

			return Tribe__Autoloader::instance();
		}

		/**
		 * Registers the plugin autoload paths in the Common Autoloader instance.
		 *
		 * @since 4.9.2
		 */
		public function register_plugin_autoload_paths( ) {
			$prefixes = [
				'Tribe__Events__' => $this->plugin_path . 'src/Tribe',
				'ForceUTF8__'     => $this->plugin_path . 'vendor/ForceUTF8',
			];

			$this->get_autoloader_instance()->register_prefixes( $prefixes );
		}
	}
} // end if !class_exists Tribe__Events__Main
