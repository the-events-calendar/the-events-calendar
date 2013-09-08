<?php
/**
 * Template Factory
 *
 * The parent class for managing the view methods in core and addons
 *
 * @since  3.0
 * @author tim@imaginesimplicity.com
 * @author jessica@
 */

if ( !defined('ABSPATH') )
	die('-1');

if( !class_exists('Tribe_Template_Factory') ) {
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
		 * Run include packages, set up hooks
		 *
		 * @return void
		 * @since 3.0
		 **/
		public function __construct() {
			$this->hooks();
			$this->asset_packages();
			$this->body_class();
		}

		/**
		 * Set up hooks for this template
		 *
		 * @return void
		 * @since 3.0
		 **/
		protected function hooks() {

			// set up queries, vars, etc that needs to be used in this view
			add_action( 'tribe_events_before_view', array( $this, 'setup_view') );

			// set notices
			add_action( 'tribe_events_before_view', array( $this, 'set_notices') );

			// Don't show the comments form inside the view (if comments are enabled, 
			// they'll show on their own after the loop)
			add_filter('comments_template', array( $this, 'remove_comments_template' ) );

			// Remove the comments template entirely if needed
			add_filter('tribe_get_option', array( $this, 'comments_off' ), 10, 2 );

			// set up meta used in this view
			add_action( 'tribe_events_before_view', array( $this, 'setup_meta') );

			// cleanup after view (reset query, etc)
			add_action( 'tribe_events_after_view', array( $this, 'shutdown_view' ) );

			// add wrapper html and input hash to non-ajax request
			add_action( 'tribe_events_before_template', array( $this, 'view_wrapper_open' ) );
			add_filter( 'tribe_events_before_template', array( $this, 'add_input_hash' ) );
			add_action( 'tribe_events_after_template', array( $this, 'view_wrapper_close' ) );

			// hide sensitive event info if post is password protected
			add_action( 'the_post', array( $this, 'manage_sensitive_info' ) );

			// add body class
			add_filter( 'body_class', array($this, 'body_class') );

			// event classes 
			add_filter( 'tribe_events_event_classes', array( $this, 'event_classes' ) );

		}

		/**
		 * Manage the asset packages defined for this template
		 *
		 * @return void
		 * @since 3.0
		 **/
		protected function asset_packages()	{
			foreach ($this->asset_packages as $asset_package) {
				$this->asset_package($asset_package);
			}
		}

		/**
		 * Filter the body class
		 *
		 * @param array $classes
		 * @return void
		 * @since 3.0
		 **/
		public function body_class($classes = array() )	{

			// view class
			$classes[] = $this->body_class;

			// category class
			if ( is_tax( TribeEvents::TAXONOMY ) ) {
				$classes[] = 'events-category';
				$category = get_term_by('name', single_cat_title( '', false ), TribeEvents::TAXONOMY );
				$classes[] = 'events-category-' . $category->slug;
			}

			// archive class
			if ( ! is_single() || tribe_is_showing_all() ) {
				$single_id = array_search( 'single-tribe_events', $classes );
				if( !empty( $single_id ) )
					$classes[ $single_id ] = 'events-list';
				$classes[] = 'events-archive';
			}

			return $classes;
		}

		/**
		 * Add classes to events on this view
		 *
		 * @return array
		 * @author Jessica Yazbek
		 * @since 3.0
		 **/
		public function event_classes( $classes ) {

			global $post, $wp_query;

			$classes = array_merge($classes, array( 'hentry', 'vevent', 'type-tribe_events', 'post-' . $post->ID, 'tribe-clearfix' ));
			$tribe_cat_slugs = tribe_get_event_cat_slugs( $post->ID );

			foreach( $tribe_cat_slugs as $tribe_cat_slug ) {
				$classes[] = 'tribe-events-category-'. $tribe_cat_slug;
			}
			if ( $venue_id = tribe_get_venue_id( $post->ID ) ) {
				$classes[] = 'tribe-events-venue-'. $venue_id;
			}
			if ( $organizer_id = tribe_get_organizer_id( $post->ID ) ) {
				$classes[] = 'tribe-events-organizer-'. $organizer_id;
			}
			// added first class for css
			if ( ( $wp_query->current_post == 0 ) && !tribe_is_day() ) {
				$classes[] = 'tribe-events-first';
			}
			// added last class for css
			if ( $wp_query->current_post == $wp_query->post_count-1 ) {
				$classes[] = 'tribe-events-last';
			}

			return $classes;
		}

		/**
		 * Setup meta display in this template
		 *
		 * @return void
		 * @since 3.0
		 **/
		public function setup_meta() {

			// customize meta items
			tribe_set_the_meta_template( 'tribe_event_venue_name', array(
				'before'=>'',
				'after'=>'',
				'label_before'=>'',
				'label_after'=>'',
				'meta_before'=>'<span class="%s">',
				'meta_after'=>'</span>'
			));
			tribe_set_meta_label( 'tribe_event_venue_address', '' );
			tribe_set_the_meta_template( 'tribe_event_venue_address', array(
				'before'=>'',
				'after'=>'',
				'label_before'=>'',
				'label_after'=>'',
				'meta_before'=>'',
				'meta_after'=>''
			));
		}

		/**
		 * Set up the notices for this template
		 *
		 * @return void
		 * @since 3.0
		 **/
		public function set_notices() {
			global $wp_query;

			// Look for a search query
			if ( ! empty( $wp_query->query_vars['s'] )) {
				$search_term = $wp_query->query_vars['s'];
			} else if ( !empty( $_POST['tribe-bar-search'] ) ) {
				$search_term = $_POST['tribe-bar-search'];
			}

			// Search term based notices
			if ( ! empty($search_term) && ! have_posts() ) {
				TribeEvents::setNotice( 'event-search-no-results', sprintf( __( 'There  were no results found for <strong>"%s"</strong>.', 'tribe-events-calendar' ), esc_html($search_term) ) );
			}

			// Our various messages if there are no events for the query
			else if ( empty($search_term) && empty( $wp_query->query_vars['s'] ) && !have_posts() ) { // Messages if currently no events, and no search term
				$tribe_ecp = TribeEvents::instance();
				$is_cat_message = '';
				if ( is_tax( $tribe_ecp->get_event_taxonomy() ) ) {
					$cat = get_term_by( 'slug', get_query_var( 'term' ), $tribe_ecp->get_event_taxonomy() );
					if( tribe_is_upcoming() ) {
						$is_cat_message = sprintf( __( 'listed under %s. Check out past events for this category or view the full calendar.', 'tribe-events-calendar' ), esc_html($cat->name) );
					} else if( tribe_is_past() ) {
						$is_cat_message = sprintf( __( 'listed under %s. Check out upcoming events for this category or view the full calendar.', 'tribe-events-calendar' ), esc_html($cat->name) );
					}
				}
				if( tribe_is_day() ) {
					TribeEvents::setNotice( 'events-not-found', sprintf( __( 'No events scheduled for <strong>%s</strong>. Please try another day.', 'tribe-events-calendar' ), date_i18n( 'F d, Y', strtotime( get_query_var( 'eventDate' ) ) ) ) );
				} elseif( tribe_is_upcoming() ) {
					$date = date('Y-m-d', strtotime($tribe_ecp->date));
					if ( $date == date('Y-m-d') ) {
						TribeEvents::setNotice( 'events-not-found', __('No upcoming events ', 'tribe-events-calendar') . $is_cat_message );
					} else {
						TribeEvents::setNotice( 'events-not-found', __('No matching events ', 'tribe-events-calendar') . $is_cat_message );
					}
				} elseif( tribe_is_past() ) {
					TribeEvents::setNotice( 'events-past-not-found', __('No previous events ', 'tribe-events-calendar') . $is_cat_message );
				}
			}
		}

		/**
		 * Setup the view, query hijacking, etc. This happens right before the view file is included
		 *
		 * @return void
		 * @since 3.0
		 **/
		public function setup_view() {

			// set up the excerpt
			if ( is_int( $this->excerpt_length ) ) {
				add_filter( 'excerpt_length', array( $this, 'excerpt_length' ) );
			}
			if ( is_string( $this->excerpt_more ) ) {
				add_filter( 'excerpt_more', array( $this, 'excerpt_more' ) );
			}
		}

		/**
		 * Echo open tags for wrapper around view
		 *
		 * @return void
		 * @since
		 **/
		public function view_wrapper_open() {
			echo '<div id="tribe-events-content-wrapper" class="tribe-clearfix">';
		}

		/**
		 * Output an input to store the hash for the current query
		 *
		 * @return void
		 * @since 3.0
		 **/
		public function add_input_hash() {
			echo '<input type="hidden" id="tribe-events-list-hash" value="">';
		}

		/**
		 * Echo open tags for wrapper around view
		 *
		 * @return void
		 * @since
		 **/
		public function view_wrapper_close() {
			echo '</div> <!-- #tribe-events-content-wrapper -->';
		}

		/**
		 * Shutdown the view, restore the query, etc. This happens right after the view file is included
		 *
		 * @return void
		 * @since 3.0
		 **/
		public function shutdown_view() {

			$this->unhook();

		}

		/**
		 * Unhook all the hooks set up on this view
		 *
		 * @return void
		 * @author 
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
			remove_action( 'tribe_events_before_view', array( $this, 'setup_view') );

			// set notices
			remove_action( 'tribe_events_before_view', array( $this, 'set_notices') );

			// Remove the comments template
			remove_filter('comments_template', array( $this, 'remove_comments_template' ) );

			// set up meta used in this view
			remove_action( 'tribe_events_before_view', array( $this, 'setup_meta') );

			// cleanup after view (reset query, etc)
			remove_action( 'tribe_events_after_view', array( $this, 'shutdown_view' ) );

			// add wrapper html and input hash to non-ajax request
			remove_action( 'tribe_events_before_template', array( $this, 'view_wrapper_open' ) );
			remove_filter( 'tribe_events_before_template', array( $this, 'add_input_hash' ) );
			remove_action( 'tribe_events_after_template', array( $this, 'view_wrapper_close' ) );

			// hide sensitive event info if post is password protected
			remove_action( 'the_post', array( $this, 'manage_sensitive_info' ) );

			// add body class
			remove_filter( 'body_class', array($this, 'body_class') );

			// event classes 
			remove_filter( 'tribe_events_event_classes', array( $this, 'event_classes' ) );

		}

		/**
		 * Add/remove filters to hide/show sensitive event info on password protected posts
		 *
		 * @param WP_Post $post
		 * @return void
		 * @since 3.0
		 **/
		public function manage_sensitive_info( $post ) {
			if ( post_password_required( $post ) ) {
				add_filter( 'tribe_events_event_schedule_details', '__return_null' );
				add_filter( 'tribe_events_event_recurring_info_tooltip', '__return_null' );
				add_filter( 'tribe_event_meta_venue_name', '__return_null' );
				add_filter( 'tribe_event_meta_venue_address', '__return_null' );
				add_filter( 'tribe_event_featured_image', '__return_null' );
				add_filter( 'tribe_events_single_event_meta', '__return_null' );
				add_filter( 'tribe_get_venue', '__return_null' );
			} else {
				remove_filter( 'tribe_events_event_schedule_details', '__return_null' );
				remove_filter( 'tribe_events_event_recurring_info_tooltip', '__return_null' );
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
		 * @return string
		 * @since 3.0
		 **/
		public function remove_comments_template( $template ) {
			remove_filter( 'comments_template', array( $this, 'remove_comments_template' ) );
			return TribeEvents::instance()->pluginPath . 'admin-views/no-comments.php';
		}

		/**
		 * Limit the excerpt length on this template
		 *
		 * @param $length
		 *
		 * @return int
		 * @since 3.0
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
		 * @since 3.0
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
		 * @since 3.0
		 */
		public function comments_off( $option_value, $option_name ) {
			if ( $option_name != 'showComments')
				return $option_value;

			if ( $this->comments_off == true )
				return false;

			return $option_value;

		}

		/**
		 * Asset calls for vendor packages
		 *
		 * @param string $name
		 * @param array $deps Dependents
		 */
		public static function asset_package( $name, $deps = array() ){

			$tec = TribeEvents::instance();
			$prefix = 'tribe-events'; // TribeEvents::POSTTYPE;

			// setup plugin resources & 3rd party vendor urls
			$resources_url = trailingslashit( $tec->pluginUrl ) . 'resources/';
			$vendor_url = trailingslashit( $tec->pluginUrl ) . 'vendor/';

			switch( $name ) {
				case 'jquery-resize':
					$path = self::getMinFile( $vendor_url . 'jquery-resize/jquery.ba-resize.js', true );
					$deps = array_merge( $deps, array( 'jquery' ) );
					wp_enqueue_script( $prefix . '-jquery-resize', $path, $deps, '1.1', false );
					self::$vendor_scripts[] = $prefix . '-jquery-resize';
					break;
				case 'chosen' : // Vendor: jQuery Chosen
					$deps = array_merge( $deps, array( 'jquery' ) );
					$css_path = self::getMinFile( $vendor_url . 'chosen/public/chosen.css', true );
					$path = self::getMinFile( $vendor_url . 'chosen/public/chosen.jquery.js', true );
					wp_enqueue_style( $prefix . '-chosen-style',$css_path );
					wp_enqueue_script( $prefix . '-chosen-jquery', $path, $deps, '0.9.5', false );
					self::$vendor_scripts[] = $prefix . '-chosen-jquery';
					break;
				case 'smoothness' : // Vendor: jQuery Custom Styles
					$path = self::getMinFile( $vendor_url . 'jquery/smoothness/jquery-ui-1.8.23.custom.css', true );
					wp_enqueue_style( $prefix . '-custom-jquery-styles', $path );
					break;
				case 'select2' : // Vendor: Select2
					$css_path = self::getMinFile( $vendor_url . 'select2/select2.css', true );
					$path = self::getMinFile( $vendor_url . 'select2/select2.js', true );
					wp_enqueue_style( $prefix . '-select2-css', $css_path );
					wp_enqueue_script( $prefix . '-select2', $path, 'jquery', '3.2' );
					self::$vendor_scripts[] = $prefix . '-select2';
					break;
				case 'calendar-script' : // Tribe Events JS
					$deps = array_merge( $deps, array( 'jquery' ), self::$vendor_scripts );
					$path = self::getMinFile( $resources_url . 'tribe-events.js', true );
					wp_enqueue_script( $prefix . '-calendar-script', $path, $deps, apply_filters( 'tribe_events_js_version', TribeEvents::VERSION ) );
					break;
				case 'datepicker' : // Vendor: jQuery Datepicker
					wp_enqueue_script( 'jquery-ui-datepicker' );
					wp_enqueue_style( 'jquery-ui-datepicker' );
					self::$vendor_scripts[] = 'jquery-ui-datepicker';
					break;
				case 'bootstrap-datepicker' : // Vendor: Bootstrap Datepicker
					$css_path = self::getMinFile( $vendor_url . 'bootstrap-datepicker/css/datepicker.css', true );
					$path = self::getMinFile( $vendor_url . 'bootstrap-datepicker/js/bootstrap-datepicker.js', true );
					wp_enqueue_style( $prefix . '-bootstrap-datepicker-css', $css_path );
					wp_enqueue_script( $prefix . '-bootstrap-datepicker', $path, 'jquery', '3.2' );
					self::$vendor_scripts[] = $prefix . '-bootstrap-datepicker';
					$localized_datepicker_array = array(
						'days' => array_merge( $tec->daysOfWeek, array( $tec->daysOfWeek[0] ) ),
						'daysShort' => array_merge( $tec->daysOfWeekShort, array( $tec->daysOfWeekShort[0] ) ),
						'daysMin' => array_merge( $tec->daysOfWeekMin, array( $tec->daysOfWeekMin[0] ) ),
						'months' => array_values( $tec->monthsFull ),
						'monthsShort' => array_values( $tec->monthsShort ),
					);
					wp_localize_script( $prefix . '-bootstrap-datepicker', 'tribe_bootstrap_datepicker_strings', array( 'dates' => $localized_datepicker_array ) );
					break;
				case 'dialog' : // Vendor: jQuery Dialog
					wp_enqueue_script( 'jquery-ui-dialog' );
					self::$vendor_scripts[] = 'jquery-ui-dialog';
					break;
				case 'admin-ui' : // Tribe Events
					$path = self::getMinFile( $resources_url . 'events-admin.css', true );
					wp_enqueue_style( $prefix . '-admin-ui', $path );
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
					$deps = array_merge( $deps, array( 'jquery', $prefix . '-calendar-script', $prefix . '-bootstrap-datepicker', $prefix . '-jquery-resize', self::get_placeholder_handle() ) );
					$path = self::getMinFile( $resources_url . 'tribe-events-bar.js', true );
					wp_enqueue_script( $prefix . '-bar', $path, $deps, apply_filters( 'tribe_events_js_version', TribeEvents::VERSION ) );
					break;
				case 'jquery-placeholder' : // Vendor: jQuery Placeholder
					$deps = array_merge( $deps, array( 'jquery' ) );
					$path = self::getMinFile( $vendor_url . 'jquery-placeholder/jquery.placeholder.js', true );
					$placeholder_handle = self::get_placeholder_handle();
					wp_enqueue_script( $placeholder_handle, $path, $deps, '2.0.7', false );
					self::$vendor_scripts[] = $placeholder_handle;
					break;
				case 'ajax-calendar':
					$deps = array_merge( $deps, array( 'jquery', $prefix . '-calendar-script' ) );
					$ajax_data = array( "ajaxurl"   => admin_url( 'admin-ajax.php', ( is_ssl() ? 'https' : 'http' ) ) );
					$path = self::getMinFile( $resources_url . 'tribe-events-ajax-calendar.js', true );
					wp_enqueue_script( 'tribe-events-calendar', $path, $deps, apply_filters( 'tribe_events_js_version', TribeEvents::VERSION ), true );
					wp_localize_script( 'tribe-events-calendar', 'TribeCalendar', $ajax_data );
					break;
				case 'ajax-list':
					$deps = array_merge( $deps, array( 'jquery', $prefix . '-calendar-script' ) );
					$tribe_paged = ( !empty( $_REQUEST['tribe_paged'] ) ) ? $_REQUEST['tribe_paged'] : 0;
					$ajax_data = array( "ajaxurl"     => admin_url( 'admin-ajax.php', ( is_ssl() ? 'https' : 'http' ) ),
					                    'tribe_paged' => $tribe_paged );
					$path = self::getMinFile( $resources_url . 'tribe-events-ajax-list.js', true );
					wp_enqueue_script( 'tribe-events-list', $path, $deps, apply_filters( 'tribe_events_js_version', TribeEvents::VERSION ), true );
					wp_localize_script( 'tribe-events-list', 'TribeList', $ajax_data );
					break;
				case 'events-css':
					// Tribe Events CSS filename
					$event_file = 'tribe-events.css';
					$stylesheet_option = tribe_get_option( 'stylesheetOption', 'tribe' );

					// What Option was selected
					switch( $stylesheet_option ) {
						case 'skeleton':
						case 'full':
							$event_file_option = 'tribe-events-'. $stylesheet_option .'.css';
							break;
						default:
							$event_file_option = 'tribe-events-theme.css';
							break;
					}

					$styleUrl = trailingslashit( $tec->pluginUrl ) . 'resources/' . $event_file_option;
					$styleUrl = self::getMinFile( $styleUrl, true );
					$styleUrl = apply_filters( 'tribe_events_stylesheet_url', $styleUrl );

					// Is there a core override file in the theme?
					$styleOverrideUrl = TribeEventsTemplates::locate_stylesheet('tribe-events/'.$event_file);

					// Load up stylesheet from theme or plugin
					if( $styleUrl && $stylesheet_option == 'tribe' ) {
						$full_path = self::getMinFile( trailingslashit( $tec->pluginUrl ) . 'resources/tribe-events-full.css', true );
						wp_enqueue_style( 'full-calendar-style', $full_path );
						wp_enqueue_style( TribeEvents::POSTTYPE . '-calendar-style', $styleUrl );
					} else {
						wp_enqueue_style( TribeEvents::POSTTYPE . '-calendar-style', $styleUrl );
					}
					if( $styleOverrideUrl ) {
						wp_enqueue_style( TribeEvents::POSTTYPE . '-calendar-override-style', $styleOverrideUrl );		
					}
					break;
				default :
					do_action($prefix . '-' . $name);
					break;
			}
		}

		/**
		 * Returns the path to a minified version of a js or css file, if it exists.
		 * If the file does not exist, returns false.
		 *
		 * @param string $url The path or URL to the un-minified file.
		 * @param bool $default_to_original Whether to just return original path if min version not found.
		 * @return string|false The path/url to minified version or false, if file not found.
		 * @author Paul Hughes
		 * @since 3.0
		 */
		public static function getMinFile( $url, $default_to_original = false ) {
			if ( !defined( 'SCRIPT_DEBUG' ) || SCRIPT_DEBUG === false ) {
				if ( substr( $url, -3, 3 ) == '.js' )
					$url_new = substr_replace( $url, '.min', -3, 0 );
				if ( substr( $url, -4, 4 ) == '.css' )
					$url_new = substr_replace( $url, '.min', -4, 0 );
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
