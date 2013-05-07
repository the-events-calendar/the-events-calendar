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
		 * @var int
		 **/
		protected $excerpt_more = '&hellip;';

		/**
		 * Run include packages, set up hooks
		 *
		 * @return void
		 * @since 3.0
		 **/
		public function __construct() {
			$this->hooks();
			$this->asset_packages();
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

			// set up meta used in this view
			add_action( 'tribe_events_before_view', array( $this, 'setup_meta') );

			// cleanup after view (reset query, etc)
			add_action( 'tribe_events_after_view', array( $this, 'shutdown_view' ) );

			// add filters for template paths
			add_filter( 'tribe_get_template_part_path', array( $this, 'filter_template_paths' ), 10, 2 );

			// add wrapper html and input hash to non-ajax request
			if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) {
				add_action( 'tribe_events_before_view', array( $this, 'view_wrapper_open' ) );
				add_action( 'tribe_events_after_view', array( $this, 'view_wrapper_close' ) );
				add_filter( 'tribe_events_before_view', array( $this, 'add_input_hash' ) );
			}
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

			add_filter( 'tribe_event_meta_venue_address_gmap', '__return_false' );
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
				TribeEvents::setNotice( 'event-search-no-results', sprintf( __( 'There  were no results found for <strong>"%s"</strong>.', 'tribe-events-calendar' ), $search_term ) );
			}

			// Our various messages if there are no events for the query
			else if ( empty($search_term) && empty( $wp_query->query_vars['s'] ) && !have_posts() ) { // Messages if currently no events, and no search term
				$tribe_ecp = TribeEvents::instance();
				$is_cat_message = '';
				if ( is_tax( $tribe_ecp->get_event_taxonomy() ) ) {
					$cat = get_term_by( 'slug', get_query_var( 'term' ), $tribe_ecp->get_event_taxonomy() );
					if( tribe_is_upcoming() ) {
						$is_cat_message = sprintf( __( 'listed under %s. Check out past events for this category or view the full calendar.', 'tribe-events-calendar' ), $cat->name );
					} else if( tribe_is_past() ) {
						$is_cat_message = sprintf( __( 'listed under %s. Check out upcoming events for this category or view the full calendar.', 'tribe-events-calendar' ), $cat->name );
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
			echo '<div id="tribe-events-content-wrapper">';
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

			// reset the excerpt
			if (is_int($this->excerpt_length)) {
				remove_filter( 'excerpt_length', array($this, 'excerpt_length'));
			}
			if (is_string($this->excerpt_more)) {
				remove_filter( 'excerpt_more', array($this, 'excerpt_more'));
			}
		}

		/**
		 * Filter tribe_get_template_part()
		 *
		 * @return string
		 * @since 3.0
		 **/
		public function filter_template_paths( $file, $template ) {
			// don't return the tribe bar on ajax requests
			if ( $template == 'modules/bar.php' && ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
				return false;
			}
			return $file;
		}

		/**
		 * Return an empty file as the comments template (to disable comments)
		 *
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
		 * @param $length
		 *
		 * @return int
		 * @since 3.0
		 */
		public function excerpt_more( $more ) {
			return $this->excerpt_more;
		}

		/**
		 * Asset calls for vendor packages
		 * @param  string $name
		 * @return null
		 */
		public static function asset_package( $name, $deps = array() ){

			$tec = TribeEvents::instance();
			$prefix = 'tribe-events'; // TribeEvents::POSTTYPE;

			// setup plugin resources & 3rd party vendor urls
			$resouces_url = trailingslashit( $tec->pluginUrl ) . 'resources/';
			$vendor_url = trailingslashit( $tec->pluginUrl ) . 'vendor/';

			switch( $name ) {
				case 'jquery-resize':
					wp_enqueue_script( $prefix . '-jquery-resize', $vendor_url . 'jquery-resize/jquery.ba-resize.min.js', array_merge( array( 'jquery' ), $deps ), '1.1', false );
					break;
				case 'chosen' : // Vendor: jQuery Chosen
					wp_enqueue_style( $prefix . '-chosen-style', $vendor_url . 'chosen/chosen/chosen.css' );
					wp_enqueue_script( $prefix . '-chosen-jquery', $vendor_url . 'chosen/chosen/chosen.jquery.min.js', array_merge( array( 'jquery' ), $deps ), '0.9.5', false );
					break;
				case 'smoothness' : // Vendor: jQuery Custom Styles
					wp_enqueue_style( $prefix . '-custom-jquery-styles', $vendor_url . 'jquery/smoothness/jquery-ui-1.8.23.custom.css' );
					break;
				case 'select2' : // Vendor: Select2
					wp_enqueue_style( $prefix . '-select2-css', $vendor_url . 'select2/select2.css' );
					if (defined('WP_DEBUG') && WP_DEBUG) {
						wp_enqueue_script( $prefix . '-select2', $vendor_url . 'select2/select2.js', 'jquery', '3.2' );
					} else {
						wp_enqueue_script( $prefix . '-select2', $vendor_url . 'select2/select2.min.js', 'jquery', '3.2' );
					}
					break;
				case 'calendar-script' : // Tribe Events JS
					wp_enqueue_script( $prefix . '-calendar-script', $resouces_url . 'tribe-events.js', array_merge( array( 'jquery' ), $deps ), apply_filters( 'tribe_events_js_version', TribeEvents::VERSION ) );
					break;
				case 'datepicker' : // Vendor: jQuery Datepicker
					wp_enqueue_script( 'jquery-ui-datepicker' );
					wp_enqueue_style( 'jquery-ui-datepicker' );
					break;
				case 'bootstrap-datepicker' : // Vendor: Bootstrap Datepicker
					wp_enqueue_style( $prefix . '-bootstrap-datepicker-css', $vendor_url . 'bootstrap-datepicker/css/datepicker.css' );
					wp_enqueue_script( $prefix . '-bootstrap-datepicker', $vendor_url . 'bootstrap-datepicker/js/bootstrap-datepicker.js', 'jquery', '3.2' );						
				case 'dialog' : // Vendor: jQuery Dialog
					wp_enqueue_script( 'jquery-ui-dialog' );
					break;
				case 'admin-ui' : // Tribe Events 
					wp_enqueue_style( $prefix . '-admin-ui', $resouces_url . 'events-admin.css' );
					break;
				case 'admin' :
					wp_enqueue_script( $prefix . '-admin', $resouces_url . 'events-admin.js', array_merge( array('jquery-ui-datepicker'), $deps ), apply_filters( 'tribe_events_js_version', TribeEvents::VERSION ), true );
					break;
				case 'settings' :
					wp_enqueue_script( $prefix . '-settings', $resouces_url . 'tribe-settings.js', array_merge( array( 'jquery' ), $deps ), apply_filters( 'tribe_events_js_version', TribeEvents::VERSION ), true );
					break;
				case 'ecp-plugins' : 
					wp_enqueue_script( $prefix . '-ecp-plugins', $resouces_url . 'jquery-ecp-plugins.js', array_merge( array( 'jquery' ), $deps ), apply_filters( 'tribe_events_js_version', TribeEvents::VERSION ) );
					break;
				case 'tribe-events-bar' :
					wp_enqueue_script( $prefix . '-bar', $resouces_url . 'tribe-events-bar.js', array_merge( array( 'jquery' ), $deps ), apply_filters( 'tribe_events_js_version', TribeEvents::VERSION ) );
					break;
				case 'jquery-placeholder' : // Vendor: jQuery Placeholder
					wp_enqueue_script( 'jquery-placeholder', $vendor_url . 'jquery-placeholder/jquery.placeholder.min.js', array_merge( array( 'jquery' ), $deps ), '2.0.7', false );
					break;
				case 'ajax-calendar':
					$ajax_data = array( "ajaxurl"   => admin_url( 'admin-ajax.php', ( is_ssl() ? 'https' : 'http' ) ) );
					wp_enqueue_script( 'tribe-events-calendar', $resouces_url . 'tribe-events-ajax-calendar.js', array_merge( array( 'jquery' ), $deps ), apply_filters( 'tribe_events_js_version', TribeEvents::VERSION ), true );
					wp_localize_script( 'tribe-events-calendar', 'TribeCalendar', $ajax_data );
					break;
				case 'ajax-list':
					$tribe_paged = ( !empty( $_REQUEST['tribe_paged'] ) ) ? $_REQUEST['tribe_paged'] : 0;
					$ajax_data = array( "ajaxurl"     => admin_url( 'admin-ajax.php', ( is_ssl() ? 'https' : 'http' ) ),
					                    'tribe_paged' => $tribe_paged );
					wp_enqueue_script( 'tribe-events-list', $resouces_url . 'tribe-events-ajax-list.js', array_merge( array( 'jquery' ), $deps ), apply_filters( 'tribe_events_js_version', TribeEvents::VERSION ), true );
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
					
					// Is there a core override file in the theme?
					$styleUrl = trailingslashit( $tec->pluginUrl ) . 'resources/' . $event_file_option;
					$styleUrl = TribeEventsTemplates::locate_stylesheet('tribe-events/'.$event_file, $styleUrl);
					$styleUrl = apply_filters( 'tribe_events_stylesheet_url', $styleUrl );

					// Load up stylesheet from theme or plugin
					if( $styleUrl && $stylesheet_option == 'tribe' ) {
						wp_enqueue_style( 'full-calendar-style', trailingslashit( $tec->pluginUrl ) . 'resources/tribe-events-full.css' );
						wp_enqueue_style( TribeEvents::POSTTYPE . '-calendar-style', $styleUrl );
					} else {
						wp_enqueue_style( TribeEvents::POSTTYPE . '-calendar-style', $styleUrl );
					}
					break;
				default :
					do_action($prefix . '-' . $name);
					break;
			}
		}
	}
}