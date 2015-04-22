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

if ( ! class_exists( 'Tribe_Template_Factory' ) ) {
	class Tribe_Template_Factory {

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
		protected static $vendor_scripts = array();

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
			$ajax_hook = constant( $current_class . '::AJAX_HOOK' );

			// set up queries, vars, etc that needs to be used in this view
			add_action( 'tribe_events_before_view', array( $this, 'setup_view' ) );

			// ajax requests
			add_action( 'wp_ajax_' . $ajax_hook, array( $this, 'ajax_response' ) );
			add_action( 'wp_ajax_nopriv_' . $ajax_hook, array( $this, 'ajax_response' ) );

			// set notices 
			add_action( 'tribe_events_before_view', array( $this, 'set_notices' ) );

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
			if ( is_tax( TribeEvents::TAXONOMY ) ) {
				$classes[] = 'events-category';
				$category  = get_term_by( 'name', single_cat_title( '', false ), TribeEvents::TAXONOMY );
				$classes[] = 'events-category-' . $category->slug;
			}

			// archive class
			if ( ! is_single() || tribe_is_showing_all() ) {
				$single_id = array_search( 'single-tribe_events', $classes );
				if ( ! empty( $single_id ) ) {
					$classes[$single_id] = 'events-list';
				}
				$classes[] = 'events-archive';
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
			tribe_set_the_meta_template(
				'tribe_event_venue_name', array(
					'before'       => '',
					'after'        => '',
					'label_before' => '',
					'label_after'  => '',
					'meta_before'  => '<span class="%s">',
					'meta_after'   => '</span>'
				)
			);
			tribe_set_meta_label( 'tribe_event_venue_address', '' );
			tribe_set_the_meta_template(
				'tribe_event_venue_address', array(
					'before'       => '',
					'after'        => '',
					'label_before' => '',
					'label_after'  => '',
					'meta_before'  => '',
					'meta_after'   => ''
				)
			);
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
			$tribe           = TribeEvents::instance();
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
				$geographic_term
			);
		}

		/**
		 * Sets an appropriate no results found message. This may be overridden in child classes.
		 */
		protected function nothing_found_notice() {
			list( $search_term, $tax_term, $geographic_term ) = $this->get_search_terms();

			if ( ! empty( $search_term ) ) {
				TribeEvents::setNotice( 'event-search-no-results', sprintf( __( 'There were no results found for <strong>"%s"</strong>.', 'tribe-events-calendar' ), esc_html( $search_term ) ) );
			} elseif ( ! empty( $geographic_term ) ) {
				TribeEvents::setNotice( 'event-search-no-results', sprintf( __( 'No results were found for events in or near <strong>"%s"</strong>.', 'tribe-events-calendar' ), esc_html( $geographic_term ) ) );
			} elseif ( ! empty( $tax_term ) && tribe_is_upcoming() && ( date( 'Y-m-d' ) === date( 'Y-m-d', strtotime( $tribe->date ) ) ) ) {
				TribeEvents::setNotice( 'events-not-found', sprintf( __( 'No upcoming events listed under %s. Check out upcoming events for this category or view the full calendar.', 'tribe-events-calendar' ), $tax_term ) );
			} elseif ( ! empty( $tax_term ) && tribe_is_upcoming() ) {
				TribeEvents::setNotice( 'events-not-found', sprintf( __( 'No matching events listed under %s. Please try viewing the full calendar for a complete list of events.', 'tribe-events-calendar' ), $tax_term ) );
			} elseif ( ! empty( $tax_term ) && tribe_is_past() ) {
				TribeEvents::setNotice( 'events-past-not-found', __( 'No previous events ', 'tribe-events-calendar' ) );
			} // if on any other view and attempting to view a category archive.
			elseif ( ! empty( $tax_term ) ) {
				TribeEvents::setNotice( 'events-not-found', sprintf( __( 'No matching events listed under %s. Please try viewing the full calendar for a complete list of events.', 'tribe-events-calendar' ), $tax_term ) );
			} else {
				TribeEvents::setNotice( 'event-search-no-results', __( 'There were no results found.', 'tribe-events-calendar' ) );
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
		 * @todo get rid of deprecated tag in 3.10
		 */
		final public function title_tag( $title, $sep = null ) {
			$new_title = $this->get_title( $title, $sep );
			if ( has_filter( 'tribe_events_add_title' ) ) {
				_deprecated_function( "The 'tribe_events_add_title' filter", '3.8', " the 'tribe_events_title_tag' filter" );
			}
			return apply_filters( 'tribe_events_title_tag', apply_filters( 'tribe_events_add_title', $new_title, $title, $sep ), $title, $sep );
		}

		/**
		 * Get the title for the view
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
				add_filter( 'tribe_events_single_event_meta', '__return_null' );
				add_filter( 'tribe_get_venue', '__return_null' );
			} else {
				remove_filter( 'tribe_events_event_schedule_details', '__return_null' );
				remove_filter( 'tribe_events_recurrence_tooltip', '__return_null' );
				remove_filter( 'tribe_event_meta_venue_name', '__return_null' );
				remove_filter( 'tribe_event_meta_venue_address', '__return_null' );
				remove_filter( 'tribe_event_featured_image', '__return_null' );
				remove_filter( 'tribe_events_single_event_meta', '__return_null' );
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
			return TribeEvents::instance()->pluginPath . 'admin-views/no-comments.php';
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

			$tec    = TribeEvents::instance();
			$prefix = 'tribe-events'; // TribeEvents::POSTTYPE;

			// setup plugin resources & 3rd party vendor urls
			$resources_url = trailingslashit( $tec->pluginUrl ) . 'resources/';
			$vendor_url    = trailingslashit( $tec->pluginUrl ) . 'vendor/';

			// @TODO make this more DRY
			switch ( $name ) {
				case 'jquery-resize':
					$path = self::getMinFile( $vendor_url . 'jquery-resize/jquery.ba-resize.js', true );
					$deps = array_merge( $deps, array( 'jquery' ) );
					wp_enqueue_script( $prefix . '-jquery-resize', $path, $deps, '1.1', false );
					self::$vendor_scripts[] = $prefix . '-jquery-resize';
					break;
				case 'chosen' : // Vendor: jQuery Chosen
					$deps     = array_merge( $deps, array( 'jquery' ) );
					$css_path = self::getMinFile( $vendor_url . 'chosen/public/chosen.css', true );
					$path     = self::getMinFile( $vendor_url . 'chosen/public/chosen.jquery.js', true );
					wp_enqueue_style( $prefix . '-chosen-style', $css_path );
					wp_enqueue_script( $prefix . '-chosen-jquery', $path, $deps, '0.9.5', false );
					self::$vendor_scripts[] = $prefix . '-chosen-jquery';
					break;
				case 'smoothness' : // Vendor: jQuery Custom Styles
					$path = self::getMinFile( $vendor_url . 'jquery/smoothness/jquery-ui-1.8.23.custom.css', true );
					wp_enqueue_style( $prefix . '-custom-jquery-styles', $path );
					break;
				case 'select2' : // Vendor: Select2
					$css_path = self::getMinFile( $vendor_url . 'select2/select2.css', true );
					$path     = self::getMinFile( $vendor_url . 'select2/select2.js', true );
					wp_enqueue_style( $prefix . '-select2-css', $css_path );
					wp_enqueue_script( $prefix . '-select2', $path, 'jquery', '3.2' );
					self::$vendor_scripts[] = $prefix . '-select2';
					break;
				case 'calendar-script' : // Tribe Events JS
					$deps = array_merge( $deps, array( 'jquery' ), self::$vendor_scripts );
					$path = self::getMinFile( $resources_url . 'tribe-events.js', true );
					wp_enqueue_script( $prefix . '-calendar-script', $path, $deps, apply_filters( 'tribe_events_js_version', TribeEvents::VERSION ) );
					$js_config_array = array(
						'permalink_settings' => get_option( 'permalink_structure' ),
						'events_post_type'   => TribeEvents::POSTTYPE,
						'events_base' => tribe_get_events_link(),
					);
					wp_localize_script( $prefix . '-calendar-script', 'tribe_js_config', $js_config_array );
					break;
				case 'datepicker' : // Vendor: jQuery Datepicker
					wp_enqueue_script( 'jquery-ui-datepicker' );
					wp_enqueue_style( 'jquery-ui-datepicker' );
					self::$vendor_scripts[] = 'jquery-ui-datepicker';
					break;
				case 'bootstrap-datepicker' : // Vendor: Bootstrap Datepicker
					$css_path = self::getMinFile( $vendor_url . 'bootstrap-datepicker/css/datepicker.css', true );
					$path     = self::getMinFile( $vendor_url . 'bootstrap-datepicker/js/bootstrap-datepicker.js', true );
					wp_enqueue_style( $prefix . '-bootstrap-datepicker-css', $css_path );
					wp_enqueue_script( $prefix . '-bootstrap-datepicker', $path, 'jquery', '3.2' );
					self::$vendor_scripts[]     = $prefix . '-bootstrap-datepicker';
					$localized_datepicker_array = array(
						'days'        => array_merge( $tec->daysOfWeek, array( $tec->daysOfWeek[0] ) ),
						'daysShort'   => array_merge( $tec->daysOfWeekShort, array( $tec->daysOfWeekShort[0] ) ),
						'daysMin'     => array_merge( $tec->daysOfWeekMin, array( $tec->daysOfWeekMin[0] ) ),
						'months'      => array_values( $tec->monthsFull ),
						'monthsShort' => array_values( $tec->monthsShort ),
						'clear'       => 'Clear',
						'today'       => 'Today',
					);
					wp_localize_script( $prefix . '-bootstrap-datepicker', 'tribe_bootstrap_datepicker_strings', array( 'dates' => $localized_datepicker_array ) );
					break;
				case 'dialog' : // Vendor: jQuery Dialog
					wp_enqueue_script( 'jquery-ui-dialog' );
					self::$vendor_scripts[] = 'jquery-ui-dialog';
					break;
				case 'admin-ui' : // Tribe Events
					$path = self::getMinFile( $resources_url . 'events-admin.css', true );
					wp_enqueue_style( $prefix . '-admin-ui', $path, array(), TribeEvents::VERSION );
					break;
				case 'admin' :
					$deps = array_merge( $deps, array( 'jquery', 'jquery-ui-datepicker' ) );
					$path = self::getMinFile( $resources_url . 'events-admin.js', true );
					wp_enqueue_script( $prefix . '-admin', $path, $deps, apply_filters( 'tribe_events_js_version', TribeEvents::VERSION ), true );
					break;
				case 'settings' :
					$deps = array_merge( $deps, array( 'jquery' ) );
					$path = self::getMinFile( $resources_url . 'tribe-settings.js', true );
					wp_enqueue_script( $prefix . '-settings', $path, $deps, apply_filters( 'tribe_events_js_version', TribeEvents::VERSION ), true );
					break;
				case 'ecp-plugins' :
					$deps = array_merge( $deps, array( 'jquery' ) );
					$path = self::getMinFile( $resources_url . 'jquery-ecp-plugins.js', true );
					wp_enqueue_script( $prefix . '-ecp-plugins', $path, $deps, apply_filters( 'tribe_events_js_version', TribeEvents::VERSION ) );
					break;
				case 'tribe-events-bar' :
					$deps = array_merge(
						$deps, array(
							'jquery',
							$prefix . '-calendar-script',
							$prefix . '-bootstrap-datepicker',
							$prefix . '-jquery-resize',
							self::get_placeholder_handle()
						)
					);
					$path = self::getMinFile( $resources_url . 'tribe-events-bar.js', true );
					wp_enqueue_script( $prefix . '-bar', $path, $deps, apply_filters( 'tribe_events_js_version', TribeEvents::VERSION ) );
					break;
				case 'jquery-placeholder' : // Vendor: jQuery Placeholder
					$deps               = array_merge( $deps, array( 'jquery' ) );
					$path               = self::getMinFile( $vendor_url . 'jquery-placeholder/jquery.placeholder.js', true );
					$placeholder_handle = self::get_placeholder_handle();
					wp_enqueue_script( $placeholder_handle, $path, $deps, '2.0.7', false );
					self::$vendor_scripts[] = $placeholder_handle;
					break;
				case 'ajax-calendar':
					$deps      = array_merge(
						$deps, array(
							'jquery',
							$prefix . '-bootstrap-datepicker',
							$prefix . '-calendar-script'
						)
					);
					$ajax_data = array(
						"ajaxurl" => admin_url( 'admin-ajax.php', ( is_ssl() ? 'https' : 'http' ) ),
					);
					$path      = self::getMinFile( $resources_url . 'tribe-events-ajax-calendar.js', true );
					wp_enqueue_script( 'tribe-events-calendar', $path, $deps, apply_filters( 'tribe_events_js_version', TribeEvents::VERSION ), true );
					wp_localize_script( 'tribe-events-calendar', 'TribeCalendar', $ajax_data );
					break;
				case 'ajax-list':
					$deps        = array_merge( $deps, array( 'jquery', $prefix . '-calendar-script' ) );
					$tribe_paged = ( ! empty( $_REQUEST['tribe_paged'] ) ) ? $_REQUEST['tribe_paged'] : 0;
					$ajax_data   = array(
						"ajaxurl"     => admin_url( 'admin-ajax.php', ( is_ssl() ? 'https' : 'http' ) ),
						'tribe_paged' => $tribe_paged,
					);
					$path        = self::getMinFile( $resources_url . 'tribe-events-ajax-list.js', true );
					wp_enqueue_script( 'tribe-events-list', $path, $deps, apply_filters( 'tribe_events_js_version', TribeEvents::VERSION ), true );
					wp_localize_script( 'tribe-events-list', 'TribeList', $ajax_data );
					break;
				case 'ajax-dayview':
					$ajax_data = array(
						"ajaxurl"   => admin_url( 'admin-ajax.php', ( is_ssl() ? 'https' : 'http' ) ),
						'post_type' => TribeEvents::POSTTYPE,
					);
					$path      = self::getMinFile( $resources_url . 'tribe-events-ajax-day.js', true );
					wp_enqueue_script( 'tribe-events-ajax-day', $path, array( 'tribe-events-bar' ), apply_filters( 'tribe_events_js_version', TribeEvents::VERSION ), true );
					wp_localize_script( 'tribe-events-ajax-day', 'TribeCalendar', $ajax_data );
					break;
				case 'events-css':

					// check if responsive should be killed
					if ( apply_filters( 'tribe_events_kill_responsive', false ) ) {
						add_filter( 'tribe_events_mobile_breakpoint', '__return_zero' );
					}

					$stylesheets  = array();
					$mobile_break = tribe_get_mobile_breakpoint();

					// Get the selected style option
					$style_option = tribe_get_option( 'stylesheetOption', 'tribe' );

					// Determine the stylesheet files for the selected option
					switch ( $style_option ) {
						case 'skeleton':
							$stylesheets['tribe-events-calendar-style'] = 'tribe-events-skeleton.css';
							break;
						case 'full':
							$stylesheets['tribe-events-calendar-style'] = 'tribe-events-full.css';
							if ( $mobile_break > 0 ) {
								$stylesheets['tribe-events-calendar-mobile-style'] = 'tribe-events-full-mobile.css';
							}
							break;
						default: // tribe styles
							$stylesheets['tribe-events-full-calendar-style'] = 'tribe-events-full.css';
							$stylesheets['tribe-events-calendar-style']      = 'tribe-events-theme.css';
							if ( $mobile_break > 0 ) {
								$stylesheets['tribe-events-calendar-full-mobile-style'] = 'tribe-events-full-mobile.css';
								$stylesheets['tribe-events-calendar-mobile-style']      = 'tribe-events-theme-mobile.css';
							}
							break;
					}

					// put override css at the end of the array
					$stylesheets['tribe-events-calendar-override-style'] = 'tribe-events/tribe-events.css';

					// do the enqueues
					foreach ( $stylesheets as $name => $css_file ) {
						if ( $name == 'tribe-events-calendar-override-style' ) {
							$user_stylesheet_url = TribeEventsTemplates::locate_stylesheet( 'tribe-events/tribe-events.css' );
							if ( $user_stylesheet_url ) {
								wp_enqueue_style( $name, $user_stylesheet_url );
							}
						} else {

							// get full URL
							$url = tribe_events_resource_url( $css_file );

							// get the minified file
							$url = self::getMinFile( $url, true );

							// apply filters
							$url = apply_filters( 'tribe_events_stylesheet_url', $url, $name );

							// set the $media attribute
							if ( $name == 'tribe-events-calendar-mobile-style' || $name == 'tribe-events-calendar-full-mobile-style' ) {
								$media = "only screen and (max-width: {$mobile_break}px)";
								wp_enqueue_style( $name, $url, array( 'tribe-events-calendar-style' ), TribeEvents::VERSION, $media );
							} else {
								wp_register_style( $name, $url, array(), TribeEvents::VERSION );
								wp_enqueue_style( $name );
							}
						}
					}

					break;
				default :
					do_action( $prefix . '-' . $name );
					break;
			}
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
		public static function getMinFile( $url, $default_to_original = false ) {
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
		protected static function get_placeholder_handle() {
			$placeholder_handle = 'jquery-placeholder';
			global $woocommerce;
			if (
				class_exists( 'Woocommerce' ) &&
				version_compare( $woocommerce->version, '2.0.11', '>=' ) &&
				version_compare( $woocommerce->version, '2.0.13', '<=' )
			) {
				$placeholder_handle = 'tribe-placeholder';
			}

			return $placeholder_handle;
		}
	}
}
