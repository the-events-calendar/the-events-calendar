<?php
/**
 * Templating functionality for Tribe Events Calendar
 */

// don't load directly
if ( !defined('ABSPATH') )
	die('-1');

if (!class_exists('TribeEventsTemplates')) {
	class TribeEventsTemplates {
		public static $origPostCount;
		public static $origCurrentPost;
		public static $throughHead = false;
	
		public static function init() {
			//add_filter( 'parse_query', array( __CLASS__, 'fixIsHome') );
			//add_filter( 'template_include', array( __CLASS__, 'fixIs404') );
			add_filter( 'template_include', array( __CLASS__, 'templateChooser') );
			add_action( 'wp_head', array( __CLASS__, 'wpHeadFinished'), 999 );
		}

		// pick the correct template to include
		public static function templateChooser($template) {
         $events = TribeEvents::instance();
         do_action('tribe_tec_template_chooser', $template);
  
			// no non-events need apply
			if ( ! in_array( get_query_var( 'post_type' ), array( TribeEvents::POSTTYPE, TribeEvents::VENUE_POST_TYPE, TribeEvents::ORGANIZER_POST_TYPE ) ) && ! is_tax( TribeEvents::TAXONOMY ) ) {
				return $template;
			}
			
			if( tribe_get_option('tribeEventsTemplate', 'default') == '' ) {
				if(is_single() && !tribe_is_showing_all() ) {
					return TribeEventsTemplates::getTemplateHierarchy('wrapper-single');
				} else {
					return TribeEventsTemplates::getTemplateHierarchy('wrapper-page');
				}
			} else {
				// we need to ensure that we always enter the loop, whether or not there are any events in the actual query
				self::spoofQuery();
				add_filter( 'wp_title', array(__CLASS__, 'remove_default_title'), 1);
				add_action( 'tribe_events_filter_the_page_title', array( __CLASS__, 'remove_title_from_page' ) );
				add_filter( 'the_title', array( __CLASS__, 'remove_title_filter' ), 2 );
				add_action( 'loop_start', array(__CLASS__, 'setup_ecp_template'));
			
				$template = locate_template( tribe_get_option('tribeEventsTemplate', 'default') == 'default' ? 'page.php' : tribe_get_option('tribeEventsTemplate', 'default') );
				if ($template ==  '') $template = get_index_template();

				// remove singular body class if sidebar-page.php
				if( $template == get_stylesheet_directory() . '/sidebar-page.php' ) {
					add_filter( 'body_class', array( __CLASS__, 'remove_singular_body_class' ) );
				} else {
					add_filter( 'body_class', array( __CLASS__, 'add_singular_body_class' ) );
				}
				return $template;
			}			
		}
	
		// remove "singular" from available body class
		public function remove_singular_body_class( $c ) {
			$key = array_search('singular', $c);
			if( $key ) {
				unset($c[ $key ]);
			}
            return $c;
        }

		/**
		 * Add the "singular" body class
		 *
		 * @param array $c
		 * @return array
		 */
		public function add_singular_body_class( $c ) {
			$c[] = 'singular';
			return $c;
		}

		public static function wpHeadFinished() {
			self::$throughHead = true;
		}

		public static function remove_default_title($title) {
			return '';
		}
		
		// Get rid of the repeating title if the page template is not the default events template.
		public function remove_title_from_page() {
			add_filter( 'the_title', array( __CLASS__, 'remove_default_title' ), 1 );
		}
		
		public function remove_title_filter( $title ) {
			remove_filter( 'the_title', array( __CLASS__, 'remove_default_title' ), 1 );
			return $title;
		}
	
		public static function setup_ecp_template($query) {
			do_action( 'tribe_events_filter_the_page_title' );
			if( self::is_main_loop($query) && self::$throughHead) {
				// add_filter('the_title', array(__CLASS__, 'load_ecp_title_into_page_template'), 10, 2 );		
				add_filter('the_content', array(__CLASS__, 'load_ecp_into_page_template') );		
				add_filter('comments_template', array(__CLASS__, 'load_ecp_comments_page_template') );
				remove_action( 'loop_start', array(__CLASS__, 'setup_ecp_template') );
			}
		}					
	
		private static function is_main_loop($query) {
			if (method_exists($query, 'is_main_query')) // WP 3.3+
     		return $query->is_main_query();

			global $wp_the_query;
			return $query === $wp_the_query;
		}
		
		// get the correct internal page template
		public static function get_current_page_template() {
         $template = '';

			if ( is_tax( TribeEvents::TAXONOMY) ) {
				if ( tribe_is_upcoming() || tribe_is_past() ){
					Tribe_Template_Factory::asset_package( 'ajax-list' );
					$template = TribeEventsTemplates::getTemplateHierarchy('list-view');
				}else{
					$template = TribeEventsTemplates::getTemplateHierarchy('calendar');
				}
			} if ( is_single() && !tribe_is_showing_all() ) {
				// single event
				$template = TribeEventsTemplates::getTemplateHierarchy('single-event');
			} elseif ( tribe_is_upcoming() || tribe_is_past() || (is_single() && tribe_is_showing_all()) ) {
				// list view
				Tribe_Template_Factory::asset_package( 'ajax-list' );
				$template = TribeEventsTemplates::getTemplateHierarchy('list-view');
			} else {
				// calendar view
				$tec = TribeEvents::instance();
				if ( $tec->displaying == 'month' ) {
					$template = TribeEventsTemplates::getTemplateHierarchy( 'calendar' );
				}
			}

         return apply_filters('tribe_current_events_page_template', $template);
		}

		// loads the contents into the page template
		public static function load_ecp_into_page_template() {
			// only run once!!!
			remove_filter('the_content', array(__CLASS__, 'load_ecp_into_page_template') );	
		
			// restore the query so that our page template can do a normal loop
			self::restoreQuery();

			// $notices = array();
			// $gmt_offset = (get_option('gmt_offset') >= '0' ) ? ' +' . get_option('gmt_offset') : " " . get_option('gmt_offset');
			// $gmt_offset = str_replace( array( '.25', '.5', '.75' ), array( ':15', ':30', ':45' ), $gmt_offset );
			// if (strtotime( tribe_get_end_date(get_the_ID(), false, 'Y-m-d G:i') . $gmt_offset ) <= time() ) 
			// 	TribeEvents::setNotice( __('This event has passed.', 'tribe-events-calendar') );
				// $notices[] = __('This event has passed.', 'tribe-events-calendar');
		
			ob_start();
			echo apply_filters( 'tribe_events_before_html', stripslashes( tribe_get_option( 'tribeEventsBeforeHTML' ) ) );
			include TribeEventsTemplates::get_current_page_template();
			echo apply_filters( 'tribe_events_after_html', stripslashes( tribe_get_option( 'tribeEventsAfterHTML' ) ) );				
			$contents = ob_get_contents();
			ob_end_clean();
		
			// spoof the query again because not all of our templates make use of the loop
			self::endQuery();

			return $contents;
		} 
	
		public static function load_ecp_title_into_page_template($title, $post_id) {
			global $post;

			if ( !is_single() ) 
				return tribe_get_events_title();

			// if the helper class for single event template hasn't been loaded fix that
			if( !class_exists('Tribe_Events_Single_Event_Template') )
				TribeEventsTemplates::getTemplateHierarchy('single-event');

			// single event title
			$before_title = apply_filters( 'tribe_events_single_event_before_the_title', '', $post_id );
			$the_title = apply_filters( 'tribe_events_single_event_the_title', $title, $title, $post_id );
			$after_title = apply_filters( 'tribe_events_single_event_after_the_title', '', $post_id );
			return $before_title . $the_title . $after_title;
		}
	
		public static function load_ecp_comments_page_template($template) {
			$tribe_ecp = TribeEvents::instance();
		
			remove_filter('comments_template', array(__CLASS__, 'load_ecp_comments_page_template') );		
			if (!is_single() || tribe_is_showing_all() || (tribe_get_option('showComments',false) === false)) {
				return $tribe_ecp->pluginPath . 'admin-views/no-comments.php';
			}
			return $template;
		}

		/**
		 * checks where we are are and determines if we
		 * should show events in the main loop
		 *
		 * @since 2.1
		 */
		public static function showInLoops($query) {

			if (!is_admin() && tribe_get_option('showInLoops') && ($query->is_home() || $query->is_tag) && empty($query->query_vars['post_type']) && false == $query->query_vars['suppress_filters']) {

				// 3.3 know-how for main query check
        // if (method_exists($query, 'is_main_query')) {
          if (self::is_main_loop($query)) {
            self::$isMainLoop = true;
        		$post_types = array('post', TribeEvents::POSTTYPE);
            $query->set('post_type', $post_types);
          }

			}

			return $query;
		}

		/**
		 * filters the_content to show the event when
		 * we are in the main loop and showing events
		 *
		 * @return string filtered $content
		 * @since 2.1
		 */
		public static function hijackContentInMainLoop($content) {

			// only run once!!!
			remove_filter('the_content', array(__CLASS__, 'hijackContentInMainLoop') );

			global $post;
			if (tribe_is_in_main_loop() && tribe_is_event($post->ID)) {
				ob_start();
				echo stripslashes(tribe_get_option('tribeEventsBeforeHTML'));
				include_once(TribeEventsTemplates::getTemplateHierarchy('in-loop'));
				echo stripslashes(tribe_get_option('tribeEventsAfterHTML'));
				$content = ob_get_contents();
				ob_end_clean();
			}

			return $content;
		}

		/**
		 * Loads theme files in appropriate hierarchy: 1) child theme, 
		 * 2) parent template, 3) plugin resources. will look in the events/
		 * directory in a theme and the views/ directory in the plugin
		 *
		 * @param string $template template file to search for
		 * @return template path
		 * @author Matt Wiebe
		 **/
		public static function getTemplateHierarchy($template, $subfolder = '', $namespace = '/', $pluginPath = '') {

			$tec = TribeEvents::instance();

			if ( substr($template, -4) != '.php' ) {
				$template .= '.php';
			}

			// setup the meta definitions
			require_once( $tec->pluginPath . 'public/advanced-functions/meta.php' );

			// allow pluginPath to be set outside of this method
			$pluginPath = empty($pluginPath) ? $tec->pluginPath : $pluginPath;

			// ensure that addon plugins look in the right override folder in theme
			$namespace = !empty($namespace) && $namespace[0] != '/' ? '/' . trailingslashit($namespace) : trailingslashit($namespace);

			// setup subfolder options
			$subfolder = !empty($subfolder) ? trailingslashit($subfolder) : $subfolder;

			if( file_exists($pluginPath . 'views/hooks/' . $template))
				include_once $pluginPath . 'views/hooks/' . $template;

			if ( $theme_file = locate_template( array('tribe-events' . $namespace . $subfolder . $template ), false, false) ) {
				$file = $theme_file;
			} else {
				// protect from concat folder with filename
				$subfolder = empty($subfolder) ? trailingslashit($subfolder) : $subfolder;
				$subfolder = $subfolder[0] != '/' ? '/' . $subfolder : $subfolder;

				$file = $pluginPath . 'views' . $subfolder . $template;
			}
			
			if ( ! in_array( $tec->displaying, tribe_get_option( 'tribeEnableViews', array() ) ) ) {
				$file = get_404_template();
			}

			return apply_filters( 'tribe_events_template_'.$template, $file);
		}

		public static function locate_stylesheet( $stylesheets, $fallback = FALSE ) {
			if ( !is_array($stylesheets) ) {
				$stylesheets = array( $stylesheets );
			}
			if ( empty( $stylesheets ) ) {
				return $fallback;
			}
			foreach ( $stylesheets as $filename ) {
				if ( file_exists(STYLESHEETPATH . '/' . $filename)) {
					$located = trailingslashit(get_stylesheet_directory_uri()).$filename;
					break;
				} else if ( file_exists(TEMPLATEPATH . '/' . $filename) ) {
					$located = trailingslashit(get_template_directory_uri()).$filename;
					break;
				}
			}
			if ( empty( $located ) ) {
				return $fallback;
			}
			return $located;
		}
	
		private static function spoofQuery() {
			global $wp_query, $withcomments;

			TribeEventsTemplates::$origPostCount = $wp_query->post_count;
			TribeEventsTemplates::$origCurrentPost =  $wp_query->current_post;
			$wp_query->current_post = -1;
			$wp_query->post_count = max($wp_query->post_count, 2);
			//$wp_query->is_page = true; // don't show comments
			//$wp_query->is_single = false; // don't show comments
			//$wp_query->is_singular = true;

			if ( empty ( $wp_query->posts ) ) {

				global $post;

				$spoofed_post = array( "ID"                    => 1,
				                       "post_author"           => 1,
				                       "post_date"             => '1900-10-02 00:00:00',
				                       "post_date_gmt"         => '1900-10-02 00:00:00',
				                       "post_content"          => '',
				                       "post_title"            => '',
				                       "post_excerpt"          => '',
				                       "post_status"           => 'publish',
				                       "comment_status"        => 'closed',
				                       "ping_status"           => 'closed',
				                       "post_password"         => '',
				                       "post_name"             => 'post',
				                       "to_ping"               => '',
				                       "pinged"                => '',
				                       "post_modified"         => '1000-01-01 00:00:00',
				                       "post_modified_gmt"     => '1000-01-01 00:00:00',
				                       "post_content_filtered" => '',
				                       "post_parent"           => 0,
				                       "guid"                  => '_tribe_empty_event',
				                       "menu_order"            => 0,
				                       "post_type"             => 'tribe_events',
				                       "post_mime_type"        => '',
				                       "comment_count"         => 0,
				                       "EventStartDate"        => '1000-01-01 00:00:00',
				                       "EventEndDate"          => '1000-01-01 00:00:00',
				                       "filter"                => 'raw' );

				$post = (object)$spoofed_post;

				$wp_query->post    = $post;
				$wp_query->posts[] = $post;
				$wp_query->posts[] = $post;

			}
		}
	
		private static function endQuery() {
			global $wp_query;
		
			$wp_query->current_post = 0;
			$wp_query->post_count = 1;		
		}	
	
		private static function restoreQuery() {
			global $wp_query;
			// remove_filter('the_title', array(__CLASS__, 'load_ecp_title_into_page_template') );			
			$wp_query->current_post = TribeEventsTemplates::$origCurrentPost;
			$wp_query->post_count = TribeEventsTemplates::$origPostCount;
			$wp_query->rewind_posts();
		}
	}

	TribeEventsTemplates::init();
}
