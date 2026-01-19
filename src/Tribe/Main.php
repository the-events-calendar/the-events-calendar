<?php
/**
 * Main Tribe Events Calendar class.
 */

use TEC\Common\StellarWP\Assets\Config as Assets_Config;
use Tribe\DB_Lock;
use Tribe\Events\Views\V2;
use Tribe\Events\Admin\Settings;
use Tribe\Events\Views\V2\Views\Day_View;
use Tribe\Events\Views\V2\Views\List_View;
use Tribe\Events\Views\V2\Views\Month_View;

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
		use Tribe__Events__Main_Deprecated;

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
		const VERSION             = '6.15.15';

		/**
		 * Min Pro Addon.
		 *
		 * @deprecated 4.8
		 */
		const MIN_ADDON_VERSION = '6.7.0';

		/**
		 * Min Common.
		 *
		 * @deprecated 4.8
		 */
		const MIN_COMMON_VERSION = '5.2.7-dev';

		const WP_PLUGIN_URL = 'https://wordpress.org/extend/plugins/the-events-calendar/';

		/**
		 * Min Version of WordPress
		 *
		 * @since 4.8
		 */
		protected $min_wordpress = '6.2';

		/**
		 * Min Version of PHP
		 *
		 * @since 4.8
		 */
		protected $min_php = '7.4.0';

		/**
		 * Min Version of Event Tickets
		 *
		 * @since 4.8
		 */
		protected $min_et_version = '5.27.0-dev';

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
		 * @var Tribe__Events__Event_Cleaner_Scheduler $scheduler
		 */
		public $scheduler;

		/**
		 * @deprecated 5.14.0 use Tribe__Events__Venue::$valid_venue_keys instead.
		*/
		public $valid_venue_keys = [];

		/**
		 * @deprecated 4.5.8 use `Tribe__Events__Pro__Main::instance()->all_slug` instead.
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
		 * A Stored version of the Welcome and Update Pages.
		 * @var Tribe__Admin__Activation_Page
		 *
		 * @deprecated 6.8.2
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
			'_EventCurrencyCode',
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

		public $singular_event_label_lowercase;
		public $plural_event_label_lowercase;

		public $singular_event_label;
		public $plural_event_label;

		public $currentDay;
		public $errors;
		public $registered;

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
			add_action( 'plugins_loaded', [ $this, 'maybe_bail_if_old_et_is_present' ], -3 );
			add_action( 'plugins_loaded', [ $this, 'maybe_bail_if_invalid_wp_or_php' ], -3 );
			add_action( 'plugins_loaded', [ $this, 'plugins_loaded' ], -2 );

			add_filter( 'tribe_tickets_integrations_should_load_freemius', '__return_false' );

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
		 * @link https://github.com/the-events-calendar/tribe-common
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
		 * Resets the global common info back to ET's common path.
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
			add_filter( 'tribe_ecp_to_run_or_not_to_run', '__return_false' );
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
			if ( $this->supportedVersion( 'wordpress' ) && $this->supportedVersion( 'php' ) ) {
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

			add_filter( 'tec_common_parent_plugin_file', [ $this, 'include_parent_plugin_path_to_common' ] );

			Tribe__Main::instance();

			add_action( 'tribe_common_loaded', [ $this, 'bootstrap' ], 0 );
		}

		/**
		 * Adds our main plugin file to the list of paths.
		 *
		 * @since 6.1.0
		 *
		 *
		 * @param array<string> $paths The paths to TCMN parent plugins.
		 *
		 * @return array<string>
		 */
		public function include_parent_plugin_path_to_common( $paths ): array {
			$paths[] = TRIBE_EVENTS_FILE;

			return $paths;
		}

		/**
		 * Load Text Domain on tribe_common_loaded as it requires common
		 *
		 * @since 4.8
		 */
		public function bootstrap() {
			/*
			 * Register the `/build` directory assets as a different group to ensure back-compatibility.
			 * This needs to happen here, early enough for the assets registration to find the group already defined.
			 */
			Assets_Config::add_group_path(
				self::class,
				self::instance()->plugin_path,
				'build/',
				true
			);

			/*
			 * Register the `/build` directory as root for packages.
			 * The difference from the group registration above is that packages are not expected to use prefix directories
			 * like `/js` or `/css`.
			 */
			Assets_Config::add_group_path(
				self::class . '-packages',
				self::instance()->plugin_path,
				'build/',
				false
			);

			$this->bind_implementations();
			$this->loadLibraries();
			$this->addHooks();
			$this->register_active_plugin();

			/**
			 * Fires when The Events Calendar is fully loaded.
			 *
			 * @since 6.12.0
			 */
			do_action( 'tec_events_fully_loaded' );
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
		 * @since 6.11.0 Add Calendar Embed functionality.
		 *
		 * @return void
		 */
		public function bind_implementations(  ) {
			tribe_singleton( 'tec.main', $this );

			// Admin provider.
			tribe_register_provider( \Tribe\Events\Admin\Provider::class );

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
			tribe_register_provider( Tribe\Events\Taxonomy\Taxonomy_Provider::class );
			tribe_register_provider( 'Tribe__Events__Editor__Provider' );
			tribe_register_provider( TEC\Events\Configuration\Provider::class );

			tribe_register_provider( TEC\Events\Legacy\Views\V1\Provider::class );

			// Shortcodes
			tribe_singleton( 'tec.shortcodes.event-details', 'Tribe__Events__Shortcode__Event_Details', [ 'hook' ] );

			// Ignored Events
			tribe_singleton( 'tec.ignored-events', 'Tribe__Events__Ignored_Events', [ 'hook' ] );

			// Capabilities.
			tribe_singleton( Tribe__Events__Capabilities::class, Tribe__Events__Capabilities::class, [ 'hook' ] );

			// Assets loader
			tribe_singleton( 'tec.assets', 'Tribe__Events__Assets', [ 'register', 'hook' ] );

			// iCal
			tribe_singleton( 'tec.iCal', 'Tribe__Events__iCal', [ 'hook' ] );

			// REST API v1
			tribe_singleton( 'tec.rest-v1.main', 'Tribe__Events__REST__V1__Main', [ 'bind_implementations', 'hook' ] );
			tribe( 'tec.rest-v1.main' );

			// Integrations
			tribe_singleton( 'tec.integrations.twenty-seventeen', 'Tribe__Events__Integrations__Twenty_Seventeen', [ 'hook' ] );

			// Linked Posts
			tribe_singleton( 'tec.linked-posts', 'Tribe__Events__Linked_Posts' );
			tribe_singleton( 'tec.linked-posts.venue', 'Tribe__Events__Venue' );
			tribe_singleton( 'tec.linked-posts.organizer', 'Tribe__Events__Organizer' );

			// Adjacent Events
			tribe_singleton( 'tec.adjacent-events', 'Tribe__Events__Adjacent_Events' );

			// Purge Expired events
			tribe_singleton( 'tec.event-cleaner', new Tribe__Events__Event_Cleaner() );

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

			// Database locks.
			tribe_singleton( 'db-lock', DB_Lock::class );

			tribe_register_provider( TEC\Events\Controller::class );

			/**
			 * Allows other plugins and services to override/change the bound implementations.
			 *
			 * DO NOT put anything after this unless you _need to_ and know the implications!
			 */
			do_action( 'tribe_events_bound_implementations' );
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
			// Tribe common resources
			require_once $this->plugin_path . 'vendor/tribe-common-libraries/tribe-common-libraries.class.php';

			// Load Template Tags
			require_once $this->plugin_path . 'src/functions/template-tags/url.php';
			require_once $this->plugin_path . 'src/functions/template-tags/query.php';
			require_once $this->plugin_path . 'src/functions/template-tags/general.php';
			require_once $this->plugin_path . 'src/functions/template-tags/month.php';
			require_once $this->plugin_path . 'src/functions/template-tags/day.php';
			require_once $this->plugin_path . 'src/functions/template-tags/loop.php';
			require_once $this->plugin_path . 'src/functions/template-tags/google-map.php';
			require_once $this->plugin_path . 'src/functions/template-tags/event.php';
			require_once $this->plugin_path . 'src/functions/template-tags/organizer.php';
			require_once $this->plugin_path . 'src/functions/template-tags/venue.php';
			require_once $this->plugin_path . 'src/functions/template-tags/date.php';
			require_once $this->plugin_path . 'src/functions/template-tags/link.php';
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

			add_action( 'parse_query', [ Tribe__Events__Query::class, 'parse_query' ], 50 );
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

			// Initialize default settings on activation
			add_action( 'init', 'tribe_events_settings_defaults_initializer', 25 );

			add_action( 'init', [ $this, 'init' ], 10 );
			add_action( 'admin_init', [ $this, 'admin_init' ] );

			add_filter( 'tribe_events_before_html', [ $this, 'before_html_data_wrapper' ] );
			add_filter( 'tribe_events_after_html', [ $this, 'after_html_data_wrapper' ] );

			// Styling
			add_filter( 'post_class', [ $this, 'post_class' ] );
			add_filter( 'body_class', [ $this, 'body_class' ] );
			add_filter( 'admin_body_class', [ $this, 'admin_body_class' ] );

			add_filter( 'post_type_archive_link', [ $this, 'event_archive_link' ], 10, 2 );
			add_filter( 'bloginfo_rss', [ $this, 'add_space_to_rss' ] );
			add_filter( 'post_updated_messages', [ $this, 'updatePostMessage' ] );

			/* Add nav menu item - thanks to https://wordpress.org/extend/plugins/cpt-archives-in-nav-menus/ */
			add_filter( 'nav_menu_items_' . self::POSTTYPE, [ $this, 'add_events_checkbox_to_menu' ], null, 3 );
			add_filter( 'wp_nav_menu_objects', [ $this, 'add_current_menu_item_class_to_events' ], null, 2 );

			/* edit-post metaboxes */
			add_action( 'admin_menu', [ $this, 'addEventBox' ] );
			add_action( 'add_meta_boxes', [ 'Tribe__Events__Venue', 'add_post_type_metabox' ] );
			add_action( 'add_meta_boxes', [ 'Tribe__Events__Organizer', 'add_post_type_metabox' ] );

			add_action( 'wp_insert_post', [ $this, 'addPostOrigin' ], 10, 2 );
			add_action( 'save_post', [ $this, 'addEventMeta' ], 15, 2 );

			add_action( 'save_post_' . Tribe__Events__Venue::POSTTYPE, [ $this, 'save_venue_data' ], 16, 2 );
			add_action( 'save_post_' . Tribe__Events__Organizer::POSTTYPE, [ $this, 'save_organizer_data' ], 16, 2 );
			add_action( 'save_post_' . static::POSTTYPE, [ Tribe__Events__Dates__Known_Range::instance(), 'maybe_update_known_range' ] );
			add_action( 'tribe_events_csv_import_complete', [ Tribe__Events__Dates__Known_Range::instance(), 'rebuild_known_range' ] );
			add_action( 'publish_' . static::POSTTYPE, [ $this, 'publishAssociatedTypes' ], 25, 2 );
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

			// Load Organizer and Venue editors.
			add_action( 'admin_menu', [ $this, 'addVenueAndOrganizerEditor' ] );

			add_action( 'tribe_venue_table_top', [ $this, 'display_rich_snippets_helper' ], 5 );

			add_action( 'plugin_row_meta', [ $this, 'addMetaLinks' ], 10, 2 );
			// Organizer and venue.
			if ( ! tec_should_hide_upsell() ) {
				add_action( 'wp_dashboard_setup', [ $this, 'dashboardWidget' ] );
				add_action( 'tribe_events_cost_table', [ $this, 'maybeShowMetaUpsell' ] );
			}


			add_action(
				'load-tribe_events_page_' . Tribe\Events\Admin\Settings::$settings_page_id,
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
			add_action( 'plugin_action_links_' . trailingslashit( $this->plugin_dir ) . 'the-events-calendar.php', [ $this, 'addLinksToPluginActions' ] );

			// override default wp_terms_checklist arguments to prevent checked items from bubbling to the top. Instead, retain hierarchy.
			add_filter( 'wp_terms_checklist_args', [ $this, 'prevent_checked_on_top_terms' ], 10, 2 );

			add_action( 'tribe_events_pre_get_posts', [ $this, 'set_tribe_paged' ] );

			// Upgrade material.
			add_action( 'init', [ $this, 'run_updates' ], 0, 0 );

			if ( defined( 'WP_LOAD_IMPORTERS' ) && WP_LOAD_IMPORTERS ) {
				add_filter( 'wp_import_post_data_raw', [ $this, 'filter_wp_import_data_before' ], 10, 1 );
				add_filter( 'wp_import_post_data_processed', [ $this, 'filter_wp_import_data_after' ], 10, 1 );
			}

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

			// Setup Help Tab texting
			add_action( 'tribe_help_pre_get_sections', [ $this, 'add_help_section_feature_box_content' ] );
			add_action( 'tribe_help_pre_get_sections', [ $this, 'add_help_section_support_content' ] );
			add_action( 'tribe_help_pre_get_sections', [ $this, 'add_help_section_extra_content' ] );

			// Google Maps API key setting
			$google_maps_api_key = Tribe__Events__Google__Maps_API_Key::instance();
			add_filter( 'tribe_addons_tab_fields', [ $google_maps_api_key, 'filter_tribe_addons_tab_fields' ] );
			add_filter( 'tribe_events_google_maps_api', [ $google_maps_api_key, 'filter_tribe_events_google_maps_api' ] );
			add_filter( 'tribe_events_pro_google_maps_api', [ $google_maps_api_key, 'filter_tribe_events_google_maps_api' ] );
			add_filter( 'tribe_field_value', [ $google_maps_api_key, 'populate_field_with_default_api_key' ], 10, 2 );
			add_filter( 'tribe_field_append', [ $google_maps_api_key, 'populate_field_tooltip_with_helper_text' ], 10, 2 );

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
				tribe_notice( 'archive-slug-conflict', [ $this, 'render_notice_archive_slug_conflict' ], 'dismiss=1&type=error' );
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
			$this->monthSlug                                  = sanitize_title( __( 'month', 'the-events-calendar' ) );
			$this->listSlug                               	  = sanitize_title( __( 'list', 'the-events-calendar' ) );
			$this->upcomingSlug                               = sanitize_title( __( 'upcoming', 'the-events-calendar' ) );
			$this->pastSlug                                   = sanitize_title( __( 'past', 'the-events-calendar' ) );
			$this->daySlug                                    = sanitize_title( __( 'day', 'the-events-calendar' ) );
			$this->todaySlug                                  = sanitize_title( __( 'today', 'the-events-calendar' ) );
			$this->featured_slug                              = sanitize_title( _x( 'featured', 'featured events slug', 'the-events-calendar' ) );

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

			/* Deprecated 4.0 */
			$this->taxRewriteSlug                             = $this->rewriteSlug . '/' . $this->category_slug;
			$this->tagRewriteSlug                             = $this->rewriteSlug . '/' . $this->tag_slug;
			$this->all_slug                                   = sanitize_title( _x( 'all', 'all events slug', 'the-events-calendar' ) );

			Tribe__Credits::init();
			Tribe__Events__Timezones::init();
			$this->registerPostType();

			tribe( 'tec.admin.event-meta-box' )->display_wp_custom_fields_metabox();

			Tribe__Debug::debug( sprintf( esc_html__( 'Initializing Tribe Events on %s', 'the-events-calendar' ), date( 'M, jS \a\t h:m:s a' ) ) );
			$this->maybeSetTECVersion();

			$this->run_scheduler();
		}

		/**
		 * Settings page object accessor.
		 *
		 * @since 5.15.0
		 *
		 * @return Settings
		 */
		public function settings() {
			return tribe( Settings::class );
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
					static::VERSION,
					$this->plugin_dir . 'the-events-calendar.php'
				);
			}
		}

		/**
		 * Controller object accessor method
		 */
		public function updater() {
			static $updater;

			if ( ! $updater ) {
				$updater = new Tribe__Events__Updater( static::VERSION );
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
		 *
		 * @deprecated 6.8.2 Activation page no longer used.
		 */
		public function activation_page() {
			_deprecated_function( __METHOD__, '6.8.2', 'No replacement' );

			// Setup the activation page only if the relevant class exists (in some edge cases, if another
			// plugin hosting an earlier version of tribe-common is already active we could hit fatals
			// if we don't take this precaution).
			//
			// @todo [BTRIA-604]: Remove class_exists() test once enough time has elapsed and the risk has reduced.
			if ( empty( $this->activation_page ) && class_exists( 'Tribe__Admin__Activation_Page' ) ) {
				$this->activation_page = new Tribe__Admin__Activation_Page(
					[
						'slug'                  => 'the-events-calendar',
						'admin_page'            => 'tribe_events_page_tec-events-settings',
						'admin_url'             => tribe( Settings::class )->get_url(),
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
				$setting_page_link = tribe( Tribe\Events\Admin\Settings::class )->get_url(
					[
						'anchor' => 'tribe-field-eventsSlug',
						'tab'    => 'general-viewing-tab',
					]
				);

				$edit_settings_link = sprintf( '<a href="%1$s">%2$s</a>', $setting_page_link, __( 'edit Events settings.', 'the-events-calendar' ) );
			}

			$line_2 = sprintf( __( '%1$s or %2$s', 'the-events-calendar' ), $edit_post_link, $edit_settings_link );

			return Tribe__Admin__Notices::instance()->render( 'archive-slug-conflict', sprintf( '<p>%s</p><p>%s</p>', $line_1, $line_2 ) );
		}

		/**
		 * Initialize the addons api settings tab.
		 *
		 * @since 5.15.0 Added check to see if we are on TEC settings page.
		 *
		 * @deprecated 6.0.5
		 */
		public function do_addons_api_settings_tab( $admin_page ) {
			_deprecated_function( __METHOD__, '6.0.5', 'tribe( Settings::class )->do_addons_api_settings_tab()' );
			tribe( Settings::class )->do_addons_api_settings_tab( $admin_page );
		}

		/**
		 * Should we show the upgrade nags?
		 *
		 * @since 4.9.12
		 *
		 * @deprecated 6.0.5
		 *
		 * @return bool
		 */
		public function show_upgrade() {
			_deprecated_function( __METHOD__, '6.0.5', 'tribe( Settings::class )->show_upgrade()' );
			return tribe( Settings::class)->show_upgrade();
		}

		/**
		 * Create the upgrade tab.
		 *
		 * @since 4.9.12
		 * @since 5.15.0 Added check to see if we are on TEC settings page.
		 *
		 * @deprecated 6.0.5
		 */
		public function do_upgrade_tab( $admin_page ) {
			_deprecated_function( __METHOD__, '6.0.5', 'tribe( Settings::class )->do_upgrade_tab()' );
			tribe( Settings::class )->do_upgrade_tab( $admin_page );
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
		 * Runs on the "wp" action. Inspects the main query object and if it relates to an events
		 * query makes a decision to add a noindex meta tag based on whether events were returned
		 * in the query results or not.
		 *
		 * @since ??
		 * @since 6.0.0 Relies on √2 code.
		 *
		 * Disabling this behavior completely is possible with:
		 *
		 *     add_filter( 'tec_events_add_no_index_meta_tag', '__return_false' );
		 *
		 *  Always adding the noindex meta tag for all event views is possible with:
		 *
		 *     add_filter( 'tribe_events_add_no_index_meta', '__return_true' );
		 *
		 *  Always adding the noindex meta tag for a specific event view is possible with:
		 *
		 *     add_filter( "tribe_events_{$view}_add_no_index_meta", '__return_true' );
		 *
		 *  Where `$view` above is the view slug, e.g. `month`, `day`, `list`, etc.
		 */
		public function issue_noindex() {
			_deprecated_function( __METHOD__, '6.2.3', 'TEC\Events\SEO\Controller::issue_noindex()' );

			global $wp_query;

			/**
			 * Allows filtering of if a noindex meta tag will be set for the current event view.
			 *
			 * @since 6.2.3
			 *
			 * @var bool $do_noindex_meta Whether to add the noindex meta tag.
			 */
			$do_noindex_meta = apply_filters( 'tec_events_add_no_index_meta_tag', true );

			if ( ! tribe_is_truthy( $do_noindex_meta ) ) {
				return;
			}

			if ( is_home() || is_front_page() ) {
				return;
			}

			if ( ! $wp_query = tribe_get_global_query_object() ) {
				return;
			}

			$context = tribe_context();

			if ( ! $context->is( 'tec_post_type' ) ) {
				return;
			}

			$view = $context->get( 'view' );

			$start_date = ! empty( $wp_query->query[ 'eventDate' ] ) ? $wp_query->get( 'eventDate' ) : $context->get( 'now' );
			$start_date = Tribe__Date_Utils::build_date_object( $start_date );

			/**
			 * Allow specific views to hook in and add their own calculated events.
			 * This *bypasses* the cached query immediately after it.
			 *
			 * @since 6.2.3
			 *
			 * @param ?Tribe__Repository|null $events     The events repository. False if not hooked in to.
			 * @param DateTime                $start_date The start date (object) of the query.
			 * @param Tribe__Context          $context    The current context.
			 *
			 */
			$events = apply_filters( 'tec_events_noindex', null, $start_date, $context );

			// If nothing has hooked in ($events is boolean false), we assume a list-style view (no end-date limiter)
			//  with no params and do a quick query for a single event after the start date.
			if ( null === $events ) {
				$cache     = tribe_cache();
				$trigger   = Tribe__Cache_Listener::TRIGGER_SAVE_POST;
				$cache_key = $cache->make_key(
					[
						'context' => $context->get( 'view_data' ),
						'view'    => $view,
						'start'   => $start_date->format( Tribe__Date_Utils::DBDATEFORMAT ),
					],
					'tec_noindex_'
				);

				$events = $cache->get( $cache_key, $trigger );

				if ( ! $events ) {
					$events = tribe_events()->per_page( 1 )->where( 'ends_after', $start_date->format( Tribe__Date_Utils::DBDATEFORMAT ) );
				}
			}

			// No posts = no index.
			$add_noindex = empty( $events->found() );

			/**
			 * Determines if a noindex meta tag will be set for the current event view.
			 *
			 * @since  3.12.4
			 *
			 * @var bool $add_noindex
			 * @var Tribe__Context $context The view context.
			 */
			$add_noindex = apply_filters( 'tribe_events_add_no_index_meta', $add_noindex, $context );

			/**
			 * Determines if a noindex meta tag will be set for a specific event view.
			 *
			 * @since 6.2.3
			 *
			 * @var bool $add_noindex
			 * @var Tribe__Context $context The view context.
			 */
			$add_noindex = apply_filters( "tec_events_{$view}_add_no_index_meta", $add_noindex, $context );

			if ( $add_noindex ) {
				add_action( 'wp_head', [ $this, 'print_noindex_meta' ] );
			}
		}

		/**
		 * Prints a "noindex,follow" robots tag.
		 *
		 * @since 6.2.3
		 *
		 */
		public function print_noindex_meta() {
			$noindex_meta = ' <meta name="robots" id="tec_noindex" content="noindex,follow" />' . "\n";

			/**
			 * Filters the noindex meta tag.
			 *
			 * @since 6.2.3
			 *
			 * @param string $noindex_meta
			 */
			$noindex_meta = apply_filters( 'tec_events_no_index_meta', $noindex_meta );

			echo $noindex_meta;
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
			if ( ! $this->supportedVersion( 'wordpress' ) ) {
				echo '<div class="error"><p>' . sprintf( esc_html__( 'Sorry, The Events Calendar requires WordPress %s or higher. Please upgrade your WordPress install.', 'the-events-calendar' ), $this->min_wordpress ) . '</p></div>';
			}
			if ( ! $this->supportedVersion( 'php' ) ) {
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
						 || (
								(
									tribe_is_upcoming()
									|| tribe_is_past()
									|| tribe_is_month()
								)
							  && isset( $wp_query->query_vars['eventDisplay'] )
							)
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
				if ( $terms instanceof WP_Error ) {
					// IF the taxonomy is not registered, we get a WP_Error object.
					return $classes;
				}

				foreach ( $terms as $term ) {
					if ( ! $term instanceof WP_Term ) {
						continue;
					}

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

			$this->register_taxonomy();

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
					'view_items'                => sprintf(
						esc_html__( 'View %s', 'the-events-calendar' ), $this->plural_event_label
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
				(
					! $post_id
					|| get_post_status( $post_id ) === 'auto-draft'
				)
				&& ! $venue_id
				&& function_exists( 'tribe_is_community_edit_event_page' )
				&& tribe_is_community_edit_event_page()
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
		 * @deprecated 6.0.0
		 *
		 * @return array The post types associated with this plugin
		 */
		public static function getPostTypes() {
			_deprecated_function( __METHOD__, '6.0.0', 'Tribe__Main::get_post_types()' );
			return apply_filters( 'tribe_events_post_types', Tribe__Main::get_post_types() );
		}

		/**
		 * Set the displaying class property.
		 *
		 * @deprecated 6.0.0
		 */
		public function setDisplay( $query = null ) {
			_deprecated_function( __METHOD__, '6.0.0' );
		}

		/**
		 * Returns the default view, providing a fallback if the default is no longer available.
		 *
		 * This can be useful is for instance a view added by another plugin (such as PRO) is
		 * stored as the default but can no longer be generated due to the plugin being deactivated.
		 *
		 * @deprecated 6.0.0
		 *
		 * @since 3.3
		 * @since 5.12.3 - Add a filter to the default view determination.
		 * @since 6.0.0  - Deprecated.
		 *
		 * @return string $view The slug of the default view.
		 */
		public function default_view() {
			_deprecated_function( __METHOD__, '6.0.0', 'tribe( \Tribe\Events\Views\V2\Manager::class )->get_default_view_slug()' );

			return tribe( V2\Manager::class )->get_default_view_slug();
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
		 * @deprecated 6.0.0
		 *
		 * @param bool $short
		 *
		 * @return array Translated month names
		 */
		public function monthNames( $short = false ) {
			_deprecated_function( __METHOD__, '6.0.0', 'Tribe__Date_Utils::get_localized_months_short() or Tribe__Date__Utils::get_localized_months_full()' );

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

			// Ensure the URL ends with a trailing slash.
			$event_url = trailingslashit( $event_url );

			// URL Arguments on home_url() pre-check
			$url_query = @parse_url( $event_url, PHP_URL_QUERY );
			if ( null === $url_query ) {
				$url_args = [];
			} else {
				$url_args = wp_parse_args( $url_query, [] );
			}

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
				case Month_View::get_view_slug():
					if ( $secondary ) {
						$event_url = trailingslashit( esc_url_raw( $event_url . $secondary ) );
					} else {
						$event_url = trailingslashit( esc_url_raw( $event_url . $this->monthSlug ) );
					}
					break;
				case List_View::get_view_slug():
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
				case Day_View::get_view_slug():
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
				case Day_View::get_view_slug():
					$eventUrl = add_query_arg( [ 'tribe_event_display' => $type ], $eventUrl );
					if ( $secondary ) {
						$eventUrl = add_query_arg( [ 'eventDate' => $secondary ], $eventUrl );
					}
					break;
				case 'week':
				case Month_View::get_view_slug():
					$eventUrl = add_query_arg( [ 'tribe_event_display' => $type ], $eventUrl );
					if ( is_string( $secondary ) ) {
						$eventUrl = add_query_arg( [ 'eventDate' => $secondary ], $eventUrl );
					} elseif ( is_array( $secondary ) ) {
						$eventUrl = add_query_arg( $secondary, $eventUrl );
					}
					break;
				case List_View::get_view_slug():
				case 'past':
				case 'upcoming':
					$eventUrl = add_query_arg( [ 'tribe_event_display' => $type ], $eventUrl );
					break;
				case 'dropdown':
					$dropdown = add_query_arg( [ 'tribe_event_display' => Month_View::get_view_slug(), 'eventDate' => ' ' ], $eventUrl );
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
		 * @deprecated 5.14.0
		 *
		 * @param int|WP_Post|null $post The Event Post Object or ID, if left empty will give get the current post.
		 *
		 * @return string The URL for the GCal export link.
		 */
		public function googleCalendarLink( $post = null ) {
			_deprecated_function( __METHOD__, '5.14.0', 'tribe( \Tribe\Events\Views\V2\iCalendar\Links\Google_Calendar::class )->generate_single_url( $post_id )' );
			return tribe( V2\iCalendar\Links\Google_Calendar::class )->generate_single_url( $post );
		}

		/**
		* Custom Escape for gCal Description to keep spacing characters in the url
		*
		* @return string sanitized url
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
		 * @deprecated 6.0.0
		 *
		 * @param int|null $post_id
		 *
		 * @return string a fully qualified link to https://maps.google.com/ for this event
		 */
		public function googleMapLink( $post_id = null ) {
			_deprecated_function( __METHOD__, '6.0.0', 'tribe_get_map_link( $post_id )' );
			return tribe_get_map_link( $post_id );
		}

		/**
		 *  Returns the full address of an event along with HTML markup.  It
		 *  loads the address template to generate the HTML
		 *
		 * @deprecated 6.0.0
		 */
		public function fullAddress( $post_id = null, $includeVenueName = false ) {
			_deprecated_function( __METHOD__, '6.0.0', 'Tribe__Events__Venue::get_address_full_string( $post_id )' );
			return \Tribe__Events__Venue::get_address_full_string( $post_id );
		}

		/**
		 *  Returns a string version of the full address of an event
		 *
		 * @deprecated 6.0.0
		 *
		 * @param int|WP_Post The post object or post id.
		 *
		 * @return string The event's address.
		 */
		public function fullAddressString( $post_id = null ) {
			_deprecated_function( __METHOD__, '6.0.0', 'Tribe__Events__Venue::get_address_full_string( $post_id )' );
			return \Tribe__Events__Venue::get_address_full_string( $post_id );
		}

		/**
		 * plugin activation callback
		 * @see register_activation_hook()
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

			if ( ! is_network_admin()  ) {
				// We set with a string to avoid having to include a file here.
				set_transient( '_tribe_events_delayed_flush_rewrite_rules', 'yes', 0 );

				self::clear_ct1_activation_state();
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

			self::clear_ct1_activation_state();

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

			if ( wp_is_post_revision( $postId ) ) {
				// Do not save meta for revisions: it would be saved to the original post anyway.
				return;
			}

			$avoid_recursion = true;

			// When not an instance of Post we bail to avoid revision problems.
			if ( ! $post instanceof WP_Post ) {
				return;
			}

			$event_meta = new Tribe__Events__Meta__Save( $postId, $post );
			$event_meta->maybe_save();

			// Allow this callback to run
			$avoid_recursion = false;
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
		 * @since 6.15.4 Added new logic to generate permalinks for Organizer/Venue when the `post_name` is blank.
		 *
		 * @param int     $post_id The post ID.
		 * @param WP_Post $post    The post.
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

				$linked_post_prefixes = [
					'venue'     => '_EventVenue',
					'organizer' => '_EventOrganizer',
				];

				foreach ( $linked_post_prefixes as $type => $linked_post_prefix ) {
					$id_index = "{$linked_post_prefix}ID";

					if ( empty( $pm[ $id_index ] ) ) {
						continue;
					}

					$linked_post_ids = is_array( $pm[ $id_index ] ) ? $pm[ $id_index ] : [ $pm[ $id_index ] ];

					foreach ( $linked_post_ids as $linked_post_id ) {
						if ( ! $linked_post_id ) {
							continue;
						}

						if ( in_array( get_post_status( $linked_post_id ), [ 'publish', 'private' ], true ) ) {
							continue;
						}

						wp_publish_post( $linked_post_id );

						// Generate a unique slug if missing.
						if ( empty( get_post_field( 'post_name', $linked_post_id ) ) ) {
							$title = get_the_title( $linked_post_id );

							// Provide a fallback if title is empty.
							if ( empty( $title ) ) {
								$title = $type . '-' . $linked_post_id;
							}

							$slug = wp_unique_post_slug(
								sanitize_title( $title ),
								$linked_post_id,
								'publish',
								get_post_type( $linked_post_id ),
								0
							);

							wp_update_post(
								[
									'ID'        => $linked_post_id,
									'post_name' => $slug,
								]
							);
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
		 * @deprecated 6.0.0
		 *
		 * @param      $data
		 * @param null $post
		 *
		 * @return int|WP_Error
		 */
		public function add_new_organizer( $data, $post = null ) {
			_deprecated_function( __METHOD__, '6.0.0', 'tribe( \'tec.linked-posts.organizer\' )->create( [ \'Organizer\' => $data ] )' );

			return tribe( 'tec.linked-posts.organizer' )->create( [ 'Organizer' => $data ] );
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
		 * Handle ajax requests from admin form
		 *
		 */
		public function ajax_form_validate() {
			$post_type = get_post_type_object( self::POSTTYPE );

			if (
				$_REQUEST['name']
				&& $_REQUEST['nonce']
				&& $_REQUEST['type']
				&& wp_verify_nonce( $_REQUEST['nonce'], 'tribe-validation-nonce' )
				&& current_user_can( $post_type->cap->edit_posts )
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
		 * @deprecated 6.0.0
		 *
		 * @param string $date
		 *
		 * @return string Next month's date
		 * @throws OverflowException
		 */
		public function nextMonth( $date ) {
			_deprecated_function( __METHOD__, '6.0.0', 'Use standard PHP date functions' );

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
		 * @deprecated 6.0.0
		 *
		 * @param string $date
		 *
		 * @return string Previous month's date
		 * @throws OverflowException
		 */
		public function previousMonth( $date ) {
			_deprecated_function( __METHOD__, '6.0.0', 'Use standard PHP date functions' );

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
				'tribe_events_event_options',
				sprintf( esc_html__( '%s Options', 'the-events-calendar' ), $this->singular_event_label ),
				[ $this, 'eventMetaBox' ],
				self::POSTTYPE,
				'side',
				'default'
			);


			if ( tribe( 'editor' )->should_load_blocks() ) {
				return;
			}

			add_meta_box(
				'tribe_events_event_details',
				$this->plugin_name,
				[ tribe( 'tec.admin.event-meta-box' ), 'init_with_event' ],
				self::POSTTYPE,
				'normal',
				'high',
				[
						'__back_compat_meta_box' => ! class_exists( 'Tribe__Events__Pro__Main' ),
				]
			);

			if ( ! class_exists( 'Tribe__Events__Pro__Main' ) ) {
				remove_meta_box( 'slugdiv', Tribe__Events__Venue::POSTTYPE, 'normal' );
			}
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
		 * @deprecated 6.0.0
		 *
		 * @param string $date The date.
		 *
		 * @return string The pretty (and shortened) date.
		 */
		public function getDateStringShortened( $date ) {
			_deprecated_function( __METHOD__, '6.0.0', 'Use standard PHP date functions' );

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
				'tribe_dashboard_widget',
				esc_html__( 'News from The Events Calendar', 'the-events-calendar' ),
				[ $this, 'outputDashboardWidget' ]
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

			if ( ! is_array( $current_hidden_boxes ) ) {
				return;
			}

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
			$actions['settings']       = '<a href="' . tribe( Settings::class )->get_url() . '">' . esc_html__( 'Settings', 'the-events-calendar' ) . '</a>';
			$actions['tribe-calendar'] = '<a href="' . $this->getLink() . '">' . esc_html__( 'Calendar', 'the-events-calendar' ) . '</a>';

			return $actions;
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
		 * Inject TEC specific setting fields into the general tab.
		 *
		 * @param array $general_tab_fields Fields for the general settings tab.
		 *
		 * @deprecated 6.0.5
		 *
		 * @return array
		 */
		public function general_settings_tab_fields( $general_tab_fields ) {
			_deprecated_function( __METHOD__, '6.0.5', 'No replacement. Handled by Settings::settings_ui()' );
		}

		/**
		 * Inject TEC specific setting fields into the display tab.
		 *
		 * @param array $display_tab_fields Fields for the display settings tab.
		 *
		 * @deprecated 6.0.5
		 *
		 * @return array
		 */
		public function display_settings_tab_fields( $display_tab_fields ) {
			_deprecated_function( __METHOD__, '6.0.5', 'No replacement. Handled by Settings::settings_ui()' );
		}

		/**
		 * When TEC is activated, the Events top level menu item in the dashboard needs the post_type appended to it.
		 *
		 * @param string $url Settings URL to filter.
		 *
		 * @deprecated 6.0.5
		 *
		 * @return string
		 */
		public function tribe_settings_url( $url ) {
			_deprecated_function( __METHOD__, '6.0.5', 'tribe( Settings::class )->filter_url()' );
			return tribe( Settings::class )->filter_url( $url );
		}

		/**
		 * Adds post types to the post_types array used to determine if on a post type screen
		 *
		 * @param array $post_types Collection of post types
		 *
		 * @return array
		 */
		public function is_post_type_screen_post_types( $post_types ) {
			foreach ( Tribe__Main::get_post_types() as $post_type ) {
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

		/**
		 * Registers the Events' category taxonomy in WordPress.
		 *
		 * @since 6.0.9
		 *
		 * @return WP_Taxonomy|WP_Error The registered taxonomy object on success, WP_Error object on failure.
		 */
		public function register_taxonomy() {
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
			 * @since 4.5.5
			 *
			 * @param array $taxonomy_args
			 *
			 */
			$taxonomy_args = apply_filters( 'tribe_events_register_event_cat_type_args', $taxonomy_args );

			return register_taxonomy( self::TAXONOMY, self::POSTTYPE, $taxonomy_args );
		}

		/**
		 * Idempotent method to clear the state of the Custom Tables v1 activation state.
		 *
		 * Note the state might be persisted in the database, as a transient, or in the cache.
		 * The method will handle both cases.
		 *
		 * @since 6.0.8
		 *
		 * @return void The method will clear the state of the Custom Tables v1 activation.
		 */
		public static function clear_ct1_activation_state(): void {
			/*
			 * Value is hard-coded to avoid autoloading the Activation class for the sole purpose of getting the
			 * transient name.
			 *
			 * @see TEC\Events\Custom_Tables\V1\Activation::ACTIVATION_TRANSIENT
			 */
			$transient_key = 'tec_custom_tables_v1_initialized';

			delete_transient( $transient_key );
			wp_cache_delete( $transient_key );
		}
	}
}
