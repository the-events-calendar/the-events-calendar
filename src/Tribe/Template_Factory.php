<?php
/**
 * Template Factory
 *
 * The parent class for managing the view methods in core and addons
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( ! class_exists( 'Tribe__Events__Template_Factory' ) ) {
	class Tribe__Events__Template_Factory {

		/**
		 * Array of asset packages needed for this template
		 *
		 * @var array
		 **/
		protected $asset_packages = array();

		/**
		 * Length for excerpts on this template
		 *
		 * @var int
		 **/
		protected $excerpt_length = 80;

		/**
		 * Text for excerpt more on this template
		 *
		 * @var string
		 **/
		protected $excerpt_more = '&hellip;';

		/**
		 * Body class on this view
		 *
		 * @var string
		 **/
		protected $body_class = '';

		/**
		 * Static variable that holds array of vendor script handles, for adding to later deps.
		 *
		 * @static
		 * @var array
		 */
		protected $comments_off = false;

		/**
		 * Static variable that holds array of vendor script handles, for adding to later deps.
		 *
		 * @static
		 * @var array
		 */
		private static $vendor_scripts = array();

		/**
		 * Constant that holds the ajax hook suffix for the view
		 *
		 * @static
		 * @var string
		 */
		const AJAX_HOOK = '';

		/**
		 * Run include packages, set up hooks
		 *
		 * @return void
		 **/
		public function __construct() {
			$this->hooks();
			$this->asset_packages();
		}

		/**
		 * Set up hooks for this template
		 *
		 * @return void
		 **/
		protected function hooks() {

			$current_class = get_class( $this );
			$ajax_hook     = constant( $current_class . '::AJAX_HOOK' );

			// set up queries, vars, etc that needs to be used in this view
			add_action( 'tribe_events_before_view', array( $this, 'setup_view' ), 10 );

			// ajax requests
			add_action( 'wp_ajax_' . $ajax_hook, array( $this, 'ajax_response' ) );
			add_action( 'wp_ajax_nopriv_' . $ajax_hook, array( $this, 'ajax_response' ) );

			// set notices
			add_action( 'tribe_events_before_view', array( $this, 'set_notices' ), 15 );

			// Don't show the comments form inside the view (if comments are enabled,
			// they'll show on their own after the loop)
			if ( ! ( tribe_get_option( 'tribeEventsTemplate', 'default' ) == '' ) ) {
				add_filter( 'comments_template', array( $this, 'remove_comments_template' ) );
			}

			// Remove the comments template entirely if needed
			add_filter( 'tribe_get_option', array( $this, 'comments_off' ), 10, 2 );

			// set up meta used in this view
			add_action( 'tribe_events_before_view', array( $this, 'setup_meta' ) );

			// cleanup after view (reset query, etc)
			add_action( 'tribe_events_after_view', array( $this, 'shutdown_view' ) );

			// add wrapper html and input hash to non-ajax request
			add_action( 'tribe_events_before_template', array( $this, 'view_wrapper_open' ) );
			add_filter( 'tribe_events_before_template', array( $this, 'add_input_hash' ) );
			add_action( 'tribe_events_after_template', array( $this, 'view_wrapper_close' ) );

			// hide sensitive event info if post is password protected
			add_action( 'the_post', array( $this, 'manage_sensitive_info' ) );

			// implement a filter for the page title
			add_filter( 'wp_title', array( $this, 'title_tag' ), 10, 2 );

			// add body class
			add_filter( 'body_class', array( $this, 'body_class' ) );

			// event classes
			add_filter( 'tribe_events_event_classes', array( $this, 'event_classes' ) );

		}

		/**
		 * Manage the asset packages defined for this template
		 *
		 * @return void
		 **/
		protected function asset_packages() {
			foreach ( $this->asset_packages as $asset_package ) {
				$this->asset_package( $asset_package );
			}
		}

		/**
		 * Handles an asset package request.
		 *
		 * @param string              $name          The asset name in the `hyphen-separated-format`
		 * @param array               $deps          An array of dependency handles
		 * @param string              $vendor_url    URL to vendor scripts and styles dir
		 * @param string              $prefix        MT script and style prefix
		 * @param Tribe__Events__Main $tec           An instance of the main plugin class
		 */
		protected static function handle_asset_package_request( $name, $deps, $vendor_url, $prefix, $tec ) {

			$asset = self::get_asset_factory_instance( $name );

			self::prepare_asset_package_request( $asset, $name, $deps, $vendor_url, $prefix, $tec );
		}

		/**
		 * initializes asset package request
		 *
		 * @param object              $asset         The Tribe__Events__*Asset object
		 * @param string              $name          The asset name in the `hyphen-separated-format`
		 * @param array               $deps          An array of dependency handles
		 * @param string              $vendor_url    URL to vendor scripts and styles dir
		 * @param string              $prefix        MT script and style prefix
		 * @param Tribe__Events__Main $tec           An instance of the main plugin class
		 */
		protected static function prepare_asset_package_request( $asset, $name, $deps, $vendor_url, $prefix, $tec ) {
			if ( ! $asset ) {
				do_action( $prefix . '-' . $name );

				return;
			}

			$asset->set_name( $name );
			$asset->set_deps( $deps );
			$asset->set_vendor_url( $vendor_url );
			$asset->set_prefix( $prefix );
			$asset->set_tec( $tec );

			$asset->handle();
		}

		/**
		 * Retrieves the appropriate asset factory instance
		 */
		protected static function get_asset_factory_instance( $name ) {
			$asset = Tribe__Events__Asset__Factory::instance()->make_for_name( $name );
			return $asset;
		}

		/**
		 * @param string $script_handle A registered script handle.
		 */
		public static function add_vendor_script( $script_handle ) {
			if ( in_array( $script_handle, self::$vendor_scripts ) ) {
				return;
			}
			self::$vendor_scripts[] = $script_handle;
		}

		/**
		 * @return string[] An array of registered vendor script handles.
		 */
		public static function get_vendor_scripts() {
			return self::$vendor_scripts;
		}

		/**
		 * Filter the body class
		 *
		 * @param array $classes
		 *
		 * @return void
		 **/
		public function body_class( $classes = array() ) {

			// view class
			$classes[] = $this->body_class;

			// category class
			if ( is_tax( Tribe__Events__Main::TAXONOMY ) ) {
				$classes[] = 'events-category';
				$category  = get_term_by( 'name', single_cat_title( '', false ), Tribe__Events__Main::TAXONOMY );
				$classes[] = 'events-category-' . $category->slug;
			}

			// archive class
			if ( ! is_single() || tribe_is_showing_all() ) {
				$single_id = array_search( 'single-tribe_events', $classes );
				if ( ! empty( $single_id ) ) {
					$classes[ $single_id ] = 'events-list';
				}
				$classes[] = 'events-archive';
			}

			// add selected style to body class for add-on styling
			$style_option = tribe_get_option( 'stylesheetOption', 'tribe' );

			switch ( $style_option ) {
				case 'skeleton':
					$classes[] = 'tribe-events-style-skeleton'; // Skeleton styles
					break;
				case 'full':
					$classes[] = 'tribe-events-style-full'; // Full styles
					break;
				default: // tribe styles is the default so add full and theme (tribe)
					$classes[] = 'tribe-events-style-full';
					$classes[] = 'tribe-events-style-theme';
					break;
			}

			return $classes;
		}

		/**
		 * Add classes to events on this view
		 *
		 * @return array
		 **/
		public function event_classes( $classes ) {
			return $classes;
		}

		/**
		 * Setup meta display in this template
		 *
		 * @return void
		 **/
		public function setup_meta() {

			// customize meta items
			tribe_set_the_meta_template( 'tribe_event_venue_name', array(
					'before'       => '',
					'after'        => '',
					'label_before' => '',
					'label_after'  => '',
					'meta_before'  => '<span class="%s">',
					'meta_after'   => '</span>',
				) );
			tribe_set_meta_label( 'tribe_event_venue_address', '' );
			tribe_set_the_meta_template( 'tribe_event_venue_address', array(
					'before'       => '',
					'after'        => '',
					'label_before' => '',
					'label_after'  => '',
					'meta_before'  => '',
					'meta_after'   => '',
				) );
		}

		/**
		 * Set up the notices for this template
		 *
		 * @return void
		 **/
		public function set_notices() {
			// By default we only display notices if no events could be found
			if ( have_posts() ) {
				return;
			}

			// Set an appropriate no-results-found message
			$this->nothing_found_notice();
		}

		/**
		 * Returns an array containing the search term, tax term and geographic term
		 * for the current request. Each may be empty.
		 *
		 * @return array
		 **/
		protected function get_search_terms() {
			global $wp_query;
			$tribe           = Tribe__Events__Main::instance();
			$geographic_term = '';
			$search_term     = '';
			$tax_term        = '';

			// Do we have a keyword or place name search?
			if ( ! empty( $wp_query->query_vars['s'] ) ) {
				$search_term = $wp_query->query_vars['s'];
			} elseif ( ! empty( $_REQUEST['tribe-bar-search'] ) ) {
				$search_term = $_REQUEST['tribe-bar-search'];
			} elseif ( ! empty( $_REQUEST['tribe-bar-geoloc'] ) ) {
				$geographic_term = $_REQUEST['tribe-bar-geoloc'];
			}
			if ( is_tax( $tribe->get_event_taxonomy() ) ) {
				$tax_term = get_term_by( 'slug', get_query_var( 'term' ), $tribe->get_event_taxonomy() );
				$tax_term = esc_html( $tax_term->name );
			}

			// Set an appropriate no-results-found message
			return array(
				$search_term,
				$tax_term,
				$geographic_term,
			);
		}

		/**
		 * Sets an appropriate no results found message. This may be overridden in child classes.
		 */
		protected function nothing_found_notice() {
			$events_label_plural = strtolower( tribe_get_event_label_plural() );

			list( $search_term, $tax_term, $geographic_term ) = $this->get_search_terms();

			$tribe = Tribe__Events__Main::instance();

			if ( ! empty( $search_term ) ) {
				Tribe__Events__Main::setNotice( 'event-search-no-results', sprintf( __( 'There were no results found for <strong>"%s"</strong>.', 'the-events-calendar' ), esc_html( $search_term ) ) );
			} elseif ( ! empty( $geographic_term ) ) {
				Tribe__Events__Main::setNotice( 'event-search-no-results', sprintf( __( 'No results were found for %1$s in or near <strong>"%2$s"</strong>.', 'the-events-calendar' ), $events_label_plural, esc_html( $geographic_term ) ) );
			} elseif ( ! empty( $tax_term ) && tribe_is_upcoming() && ( date( 'Y-m-d' ) === date( 'Y-m-d', strtotime( $tribe->date ) ) ) ) {
				Tribe__Events__Main::setNotice( 'events-not-found', sprintf( __( 'No upcoming %1$s listed under %2$s. Check out upcoming %3$s for this category or view the full calendar.', 'the-events-calendar' ), $events_label_plural, $tax_term, $events_label_plural ) );
			} elseif ( ! empty( $tax_term ) && tribe_is_upcoming() ) {
				Tribe__Events__Main::setNotice( 'events-not-found', sprintf( __( 'No matching %1$s listed under %2$s. Please try viewing the full calendar for a complete list of %3$s.', 'the-events-calendar' ), $events_label_plural, $tax_term, $events_label_plural ) );
			} elseif ( ! empty( $tax_term ) && tribe_is_past() ) {
				Tribe__Events__Main::setNotice( 'events-past-not-found', sprintf( __( 'No previous %s ', 'the-events-calendar' ), $events_label_plural ) );
			} // if on any other view and attempting to view a category archive.
			elseif ( ! empty( $tax_term ) ) {
				Tribe__Events__Main::setNotice( 'events-not-found', sprintf( __( 'No matching %1$s listed under %2$s. Please try viewing the full calendar for a complete list of %3$s.', 'the-events-calendar' ), $events_label_plural, $tax_term, $events_label_plural ) );
			} else {
				Tribe__Events__Main::setNotice( 'event-search-no-results', __( 'There were no results found.', 'the-events-calendar' ) );
			}
		}

		/**
		 * Setup the view, query hijacking, etc. This happens right before the view file is included
		 *
		 * @return void
		 **/
		public function setup_view() {

			global $wp_query;

			// don't show past posts in reverse order
			if ( $wp_query->tribe_is_past ) {
				$wp_query->posts = array_reverse( $wp_query->posts );
			}

			// set up the excerpt
			if ( is_int( $this->excerpt_length ) ) {
				add_filter( 'excerpt_length', array( $this, 'excerpt_length' ) );
			}
			if ( is_string( $this->excerpt_more ) ) {
				add_filter( 'excerpt_more', array( $this, 'excerpt_more' ) );
			}
		}

		/**
		 * Apply filter to the title tag
		 *
		 * @param string      $title
		 * @param string|null $sep
		 *
		 * @return mixed|void
		 */
		final public function title_tag( $title, $sep = null ) {
			$new_title = $this->get_title( $title, $sep );

			return apply_filters( 'tribe_events_title_tag', $new_title, $title, $sep );
		}

		/**
		 * Get the title for the view
		 *
		 * @param      $title
		 * @param null $sep
		 *
		 * @return string
		 */
		protected function get_title( $title, $sep = null ) {
			return tribe_get_events_title( false ) . ' ' . $sep . ' ' . $title;
		}

		/**
		 * Echo open tags for wrapper around view
		 *
		 * @return void
		 **/
		public function view_wrapper_open() {
			echo '<div id="tribe-events-content-wrapper" class="tribe-clearfix">';
		}

		/**
		 * Output an input to store the hash for the current query
		 *
		 * @return void
		 **/
		public function add_input_hash() {
			echo '<input type="hidden" id="tribe-events-list-hash" value="">';
		}

		/**
		 * Echo open tags for wrapper around view
		 *
		 * @return void
		 **/
		public function view_wrapper_close() {
			echo '</div> <!-- #tribe-events-content-wrapper -->';
		}

		/**
		 * Function to execute when ajax view is requested
		 */
		public function ajax_response() {
			die();
		}

		/**
		 * Shutdown the view, restore the query, etc. This happens right after the view file is included
		 *
		 * @return void
		 **/
		public function shutdown_view() {
			$this->unhook();
		}

		/**
		 * Unhook all the hooks set up on this view
		 *
		 * @return void
		 **/
		protected function unhook() {

			// reset the excerpt
			if ( is_int( $this->excerpt_length ) ) {
				remove_filter( 'excerpt_length', array( $this, 'excerpt_length' ) );
			}
			if ( is_string( $this->excerpt_more ) ) {
				remove_filter( 'excerpt_more', array( $this, 'excerpt_more' ) );
			}

			// set up queries, vars, etc that needs to be used in this view
			remove_action( 'tribe_events_before_view', array( $this, 'setup_view' ) );

			// set notices
			remove_action( 'tribe_events_before_view', array( $this, 'set_notices' ) );

			// Remove the comments template
			if ( ! ( tribe_get_option( 'tribeEventsTemplate', 'default' ) == '' ) ) {
				remove_filter( 'comments_template', array( $this, 'remove_comments_template' ) );
			}

			// set up meta used in this view
			remove_action( 'tribe_events_before_view', array( $this, 'setup_meta' ) );

			// cleanup after view (reset query, etc)
			remove_action( 'tribe_events_after_view', array( $this, 'shutdown_view' ) );

			// add wrapper html and input hash to non-ajax request
			remove_action( 'tribe_events_before_template', array( $this, 'view_wrapper_open' ) );
			remove_filter( 'tribe_events_before_template', array( $this, 'add_input_hash' ) );
			remove_action( 'tribe_events_after_template', array( $this, 'view_wrapper_close' ) );

			// hide sensitive event info if post is password protected
			remove_action( 'the_post', array( $this, 'manage_sensitive_info' ) );

			// add body class
			remove_filter( 'body_class', array( $this, 'body_class' ) );

			// event classes
			remove_filter( 'tribe_events_event_classes', array( $this, 'event_classes' ) );

		}

		/**
		 * Add/remove filters to hide/show sensitive event info on password protected posts
		 *
		 * @param WP_Post $post
		 *
		 * @return void
		 **/
		public function manage_sensitive_info( $post ) {
			if ( post_password_required( $post ) ) {
				add_filter( 'tribe_events_event_schedule_details', '__return_null' );
				add_filter( 'tribe_events_recurrence_tooltip', '__return_null' );
				add_filter( 'tribe_event_meta_venue_name', '__return_null' );
				add_filter( 'tribe_event_meta_venue_address', '__return_null' );
				add_filter( 'tribe_event_featured_image', '__return_null' );
				add_filter( 'tribe_get_venue', '__return_null' );
			} else {
				remove_filter( 'tribe_events_event_schedule_details', '__return_null' );
				remove_filter( 'tribe_events_recurrence_tooltip', '__return_null' );
				remove_filter( 'tribe_event_meta_venue_name', '__return_null' );
				remove_filter( 'tribe_event_meta_venue_address', '__return_null' );
				remove_filter( 'tribe_event_featured_image', '__return_null' );
				remove_filter( 'tribe_get_venue', '__return_null' );
			}
		}

		/**
		 * Return an empty file as the comments template (to disable comments)
		 *
		 * @param string $template
		 *
		 * @return string
		 **/
		public function remove_comments_template( $template ) {
			return Tribe__Events__Main::instance()->pluginPath . 'src/admin-views/no-comments.php';
		}

		/**
		 * Limit the excerpt length on this template
		 *
		 * @param $length
		 *
		 * @return int
		 */
		public function excerpt_length( $length ) {
			return $this->excerpt_length;
		}

		/**
		 * Set up the excerpt more text on this template
		 *
		 * @param int $more
		 *
		 * @return int
		 */
		public function excerpt_more( $more ) {
			return $this->excerpt_more;
		}

		/**
		 * Check if comments are disabled on this view
		 *
		 * @param int $more
		 *
		 * @return int
		 */
		public function comments_off( $option_value, $option_name ) {
			if ( $option_name != 'showComments' ) {
				return $option_value;
			}

			if ( $this->comments_off == true ) {
				return false;
			}

			return $option_value;

		}

		/**
		 * Asset calls for vendor packages
		 *
		 * @param string $name
		 * @param array  $deps Dependents
		 */
		public static function asset_package( $name, $deps = array() ) {

			$tec    = Tribe__Events__Main::instance();
			$prefix = 'tribe-events'; // Tribe__Events__Main::POSTTYPE;

			// setup plugin resources & 3rd party vendor urls
			$vendor_url = trailingslashit( $tec->pluginUrl ) . 'vendor/';

			self::handle_asset_package_request( $name, $deps, $vendor_url, $prefix, $tec );
		}

		/**
		 * Returns the path to a minified version of a js or css file, if it exists.
		 * If the file does not exist, returns false.
		 *
		 * @param string $url                 The path or URL to the un-minified file.
		 * @param bool   $default_to_original Whether to just return original path if min version not found.
		 *
		 * @return string|false The path/url to minified version or false, if file not found.
		 */
		public static function getMinFile( $url, $default_to_original = true ) {
			if ( ! defined( 'SCRIPT_DEBUG' ) || SCRIPT_DEBUG === false ) {
				if ( substr( $url, - 3, 3 ) == '.js' ) {
					$url_new = substr_replace( $url, '.min', - 3, 0 );
				}
				if ( substr( $url, - 4, 4 ) == '.css' ) {
					$url_new = substr_replace( $url, '.min', - 4, 0 );
				}
			}

			if ( isset( $url_new ) && file_exists( str_replace( WP_CONTENT_URL, WP_CONTENT_DIR, $url_new ) ) ) {
				return $url_new;
			} elseif ( $default_to_original ) {
				return $url;
			} else {
				return false;
			}
		}

		/*
		 * Playing ping-pong with WooCommerce. They keep changing their script.
		 * See https://github.com/woothemes/woocommerce/issues/3623
		 */
		public static function get_placeholder_handle() {
			$placeholder_handle = 'jquery-placeholder';
			global $woocommerce;
			if ( class_exists( 'Woocommerce' ) && version_compare( $woocommerce->version, '2.0.11', '>=' ) && version_compare( $woocommerce->version, '2.0.13', '<=' )
			) {
				$placeholder_handle = 'tribe-placeholder';
			}

			return $placeholder_handle;
		}
	}
}
