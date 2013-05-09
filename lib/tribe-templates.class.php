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

			// choose the wordpress theme template to use
			add_filter( 'template_include', array( __CLASS__, 'templateChooser') );

			// include our view class
			add_action( 'template_redirect', 'tribe_initialize_view' );

			// there's no template redirect on ajax, so we include the template class right before the view is included
			if (defined('DOING_AJAX') && DOING_AJAX) { 
				add_action( 'tribe_pre_get_view', 'tribe_initialize_view' );
			}

			add_action( 'wp_head', array( __CLASS__, 'wpHeadFinished'), 999 );

			// make sure we enter the loop by always having some posts in $wp_query
			add_action( 'template_redirect', array( __CLASS__, 'maybeSpoofQuery' ) );
		}

		// pick the correct template to include
		public static function templateChooser($template) {
			$events = TribeEvents::instance();
			do_action('tribe_tec_template_chooser', $template);

         	// hijack this method right up front if it's a 404
         	if( is_404() && is_single() && apply_filters( 'tribe_events_templates_is_404', '__return_true') )
         		return get_404_template();

			// no non-events need apply
			if ( ! in_array( get_query_var( 'post_type' ), array( TribeEvents::POSTTYPE, TribeEvents::VENUE_POST_TYPE, TribeEvents::ORGANIZER_POST_TYPE ) ) && ! is_tax( TribeEvents::TAXONOMY ) ) {
				return $template;
			}

			if( tribe_get_option('tribeEventsTemplate', 'default') == '' ) {
				if(is_single() && !tribe_is_showing_all() ) {
					return self::getTemplateHierarchy('wrapper-single');
				} else {
					return self::getTemplateHierarchy('wrapper-page');
				}
			} else {


				// add_filter( 'wp_title', array(__CLASS__, 'remove_default_title'), 1);

				if ( ! is_single() || ! post_password_required()) {
					add_action( 'loop_start', array(__CLASS__, 'setup_ecp_template' ) );
				}
			
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

		/**
		 * Include the class for the current view
		 *
		 * @return void
		 * @since 3.0
		 **/
		public static function instantiate_template_class( $class = false ) {

			// hijack this method right up front if it's a password protected post and the password isn't entered
			if ( is_single() && post_password_required() ) {
				return;
			}
  
			if ( tribe_is_event_query() ) {
				if ( ! $class ) {
					$class = self::get_current_template_class();
				}
				if ( class_exists( $class ) ) {
					new $class;
				}
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

				// on loop start, unset the global post so that template tags don't work before the_content()
				add_action('the_post', array(__CLASS__, 'spoof_the_post'));

				// on the_content, load our events template
				add_filter('the_content', array(__CLASS__, 'load_ecp_into_page_template') );

				// remove the comments template		
				add_filter('comments_template', array(__CLASS__, 'load_ecp_comments_page_template') );

				// only do this once
				remove_action( 'loop_start', array(__CLASS__, 'setup_ecp_template') );
			}
		}

		/**
		 * Spoof the global post just once
		 *
		 * @return void
		 * @since 3.0
		 **/
		public static function spoof_the_post() {
			$GLOBALS['post'] = self::spoofed_post();
			remove_action('the_post', array(__CLASS__, 'spoof_the_post'));
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

			// list view
			if ( tribe_is_list_view() ) {
				$template = self::getTemplateHierarchy( 'list' );
			} 

			// calendar view
			if ( tribe_is_month() ) {
				$template = self::getTemplateHierarchy( 'calendar' );
			} 

			// single event view
			if ( is_singular( TribeEvents::POSTTYPE ) && !tribe_is_showing_all() ) {
				$template = self::getTemplateHierarchy( 'single-event' );
			}

			// apply filters
			return apply_filters('tribe_current_events_page_template', $template);

		}

		// get the correct internal page template
		public static function get_current_template_class() {

			$class = '';

			// list view
			if ( tribe_is_list_view() ) {
				$class = 'Tribe_Events_List_Template';
			} 

			// calendar view
			if ( tribe_is_month() ) {
				$class = 'Tribe_Events_Calendar_Template';
			} 

			// single event view
			if ( is_singular( TribeEvents::POSTTYPE )) {
				$class = 'Tribe_Events_Single_Event_Template';
			}

			// apply filters
			return apply_filters('tribe_current_events_template_class', $class);

		}

		// loads the contents into the page template
		public static function load_ecp_into_page_template() {
			// only run once!!!
			remove_filter('the_content', array(__CLASS__, 'load_ecp_into_page_template') );	
			
			self::restoreQuery();

			ob_start();

			echo tribe_events_before_html();

			tribe_get_view();

			$after = tribe_get_option( 'tribeEventsAfterHTML' );
			$after = wptexturize( $after );
			$after = convert_chars( $after );
			$after = wpautop( $after );
			$after = shortcode_unautop( $after );
			$after = apply_filters( 'tribe_events_after_html', $after );

			echo tribe_events_after_html();	

			$contents = ob_get_contents();
			
			ob_end_clean();
		
			// make sure the loop ends after our template is included
			if ( ! is_404() )
				self::endQuery();

			return $contents;
		} 
	
		public static function load_ecp_title_into_page_template($title, $post_id) {
			global $post;

			if ( !is_single() ) 
				return tribe_get_events_title();

			// if the helper class for single event template hasn't been loaded fix that
			if( !class_exists('Tribe_Events_Single_Event_Template') )
				self::getTemplateHierarchy('single-event');

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
				include_once(self::getTemplateHierarchy('in-loop'));
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
		 * @param array $args additional arguments to affect the template path
		 *  - subfolder
		 *  - namespace
		 *  - plugin_path
		 *  - disable_view_check - bypass the check to see if the view is enabled
		 * @return template path
		 * @author Matt Wiebe
		 **/
		public static function getTemplateHierarchy( $template, $args = array() ) {
			if ( !is_array( $args ) ) {
				$args = array();
				$passed = func_get_args();
				$backwards_map = array( 'subfolder', 'namespace', 'plugin_path' );
				if ( count( $passed > 1 ) ) {
					for ( $i = 1 ; $i < count($passed) ; $i++ ) {
						$args[$backwards_map[$i-1]] = $passed[$i];
					}
				}
			}

			$args = wp_parse_args( $args, array(
				'subfolder' => '',
				'namespace' => '/',
				'plugin_path' => '',
				'disable_view_check' => FALSE,
			));
			/**
			 * @var string $subfolder
			 * @var string $namespace
			 * @var string $pluginpath
			 * @var bool $disable_view_check
			 */
			extract($args);

			$tec = TribeEvents::instance();

			if ( substr($template, -4) != '.php' ) {
				$template .= '.php';
			}

			// setup the meta definitions
			require_once( $tec->pluginPath . 'public/advanced-functions/meta.php' );

			// Allow base path for templates to be filtered
			$template_base_paths = apply_filters( 'tribe_events_template_paths', (array) TribeEvents::instance()->pluginPath);

			// backwards compatibility if $plugin_path arg is used
			if ( $plugin_path && ! in_array($plugin_path, $template_base_paths) ) {
				$template_base_paths[] = $plugin_path;
			}

			// ensure that addon plugins look in the right override folder in theme
			$namespace = !empty($namespace) && $namespace[0] != '/' ? '/' . trailingslashit($namespace) : trailingslashit($namespace);

			// setup subfolder options
			$subfolder = !empty($subfolder) ? trailingslashit($subfolder) : $subfolder;

			foreach ( $template_base_paths as $template_base_path ) {

				if ( $theme_file = locate_template( array('tribe-events' . $namespace . $subfolder . $template ), FALSE, FALSE) ) {
					$file = $theme_file;
				} else {
					// protect from concat folder with filename
					$subfolder = empty($subfolder) ? trailingslashit($subfolder) : $subfolder;
					$subfolder = $subfolder[0] != '/' ? '/' . $subfolder : $subfolder;

					$file = $template_base_path . 'views' . $subfolder . $template;
					// echo $file;
				}
				
				if ( !$disable_view_check && in_array( $tec->displaying, tribe_events_disabled_views() ) ) {
					$file = get_404_template();
				}

				$file = apply_filters( 'tribe_events_template', $file, $template);

				// return the first one found
				if (file_exists($file))
					break;
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

		private static function endQuery() {
			global $wp_query;
		
			$wp_query->current_post = 0;
			$wp_query->post_count = 1;		
		}

		private static function spoofed_post() {
			$spoofed_post = array(
                	'ID'                    => -9999,
	                'post_status'           => 'draft',
	                'post_author'           => 0,
	                'post_parent'           => 0,
	                'post_type'             => 'page',
	                'post_date'             => 0,
	                'post_date_gmt'         => 0,
	                'post_modified'         => 0,
	                'post_modified_gmt'     => 0,
	                'post_content'          => '',
	                'post_title'            => '',
	                'post_excerpt'          => '',
	                'post_content_filtered' => '',
	                'post_mime_type'        => '',
	                'post_password'         => '',
	                'post_name'             => '',
	                'guid'                  => '',
	                'menu_order'            => 0,
	                'pinged'                => '',
	                'to_ping'               => '',
	                'ping_status'           => '',
	                'comment_status'        => 'closed',
	                'comment_count'         => 0,
	                'is_404'          		=> false,
	                'is_page'         		=> false,
	                'is_single'       		=> false,
	                'is_archive'      		=> false,
	                'is_tax'          		=> false,
			);
			return (object) $spoofed_post;
		}
	
		public static function maybeSpoofQuery() {

			// hijack this method right up front if it's a password protected post and the password isn't entered
			if (is_single() && post_password_required()) {
				return;
			}
  
			global $wp_query;

			if ( $wp_query->is_main_query() && tribe_is_event_query() && tribe_get_option('tribeEventsTemplate', 'default') != '' ) {

				// we need to ensure that we always enter the loop, whether or not there are any events in the actual query

				$spoofed_post = self::spoofed_post();

				$GLOBALS['post'] = $spoofed_post;
				$wp_query->posts[] = $spoofed_post;
				$wp_query->post_count = count($wp_query->posts);

				$wp_query->spoofed = true;
				$wp_query->rewind_posts();

			}
		}

		public static function restoreQuery() {
			global $wp_query;
			if ( isset( $wp_query->spoofed ) && $wp_query->spoofed ) {

				// take the spoofed post out of the posts array
				array_pop( $wp_query->posts );

				// fix the post_count
				$wp_query->post_count = count( $wp_query->posts );
				
				// rewind the posts
				$wp_query->rewind_posts();

				if ( $wp_query->have_posts() ) {
					wp_reset_postdata();
				} else {
					// there are no posts, unset the current post
					unset ( $wp_query->post );
				}

				// don't do this again
				unset( $wp_query->spoofed );
			}
			// remove_filter('the_title', array(__CLASS__, 'load_ecp_title_into_page_template') );			
		}
	}

	TribeEventsTemplates::init();
}
