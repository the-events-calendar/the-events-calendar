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
			add_filter( 'template_include', array( __CLASS__, 'templateChooser') );
			add_action( 'wp_head', array( __CLASS__, 'wpHeadFinished'), 999 );
		}

		// pick the correct template to include
		public static function templateChooser($template) {
         $events = TribeEvents::instance();
         do_action('tribe_tec_template_chooser', $template);
  
			// no non-events need apply
			if ( get_query_var( 'post_type' ) != TribeEvents::POSTTYPE && ! is_tax( TribeEvents::TAXONOMY ) && get_query_var( 'post_type' ) != TribeEvents::VENUE_POST_TYPE ) {
				return $template;
			}

			//is_home fixer
			global $wp_query;
			$wp_query->is_home = false;

			if( tribe_get_option('spEventsTemplate', 'default') == '' ) {
				if(is_single() && !tribe_is_showing_all() ) {
					return TribeEventsTemplates::getTemplateHierarchy('ecp-single-template');
				} else {
					return TribeEventsTemplates::getTemplateHierarchy('ecp-page-template');
				}
			} else {
				// we need to ensure that we always enter the loop, whether or not there are any events in the actual query
				self::spoofQuery();
				add_filter( 'wp_title', array(__CLASS__, 'remove_default_title'), 1);
				add_action( 'loop_start', array(__CLASS__, 'setup_ecp_template'));
			
				$template = locate_template( tribe_get_option('spEventsTemplate', 'default') == 'default' ? 'page.php' : tribe_get_option('spEventsTemplate', 'default') );
				if ($template ==  '') $template = get_index_template();
			
				return $template;
			}			
		}
	
		public static function wpHeadFinished() {
			self::$throughHead = true;
		}

		public static function remove_default_title($title) {
			return '';
		}
	
		public static function setup_ecp_template($query) {
			if( self::is_main_loop($query) && self::$throughHead) {
				add_filter('the_title', array(__CLASS__, 'load_ecp_title_into_page_template') );		
				add_filter('the_content', array(__CLASS__, 'load_ecp_into_page_template') );		
				add_filter('comments_template', array(__CLASS__, 'load_ecp_comments_page_template') );
				remove_action( 'loop_start', array(__CLASS__, 'setup_ecp_template') );
			}
		}					
	
		private static function is_main_loop($query) {
			global $wp_the_query;
			return $query === $wp_the_query;
		}
		
		// get the correct internal page template
		public static function get_current_page_template() {
         $template = '';

			if ( is_tax( TribeEvents::TAXONOMY) ) {
				if ( tribe_is_upcoming() || tribe_is_past() )
					$template = TribeEventsTemplates::getTemplateHierarchy('list');
				else
					$template = TribeEventsTemplates::getTemplateHierarchy('gridview');
			}
			// single event
			if ( is_single() && !tribe_is_showing_all() ) {
				$template = TribeEventsTemplates::getTemplateHierarchy('single');
			}
			// list view
			elseif ( tribe_is_upcoming() || tribe_is_past() || tribe_is_day() || (is_single() && tribe_is_showing_all()) ) {
				$template = TribeEventsTemplates::getTemplateHierarchy('list');
			}
			// grid view
			else 
			{
				$template = TribeEventsTemplates::getTemplateHierarchy('gridview');
			}

         return apply_filters('tribe_current_events_page_template', $template);
		}

		// loads the contents into the page template
		public static function load_ecp_into_page_template() {
			// only run once!!!
			remove_filter('the_content', array(__CLASS__, 'load_ecp_into_page_template') );	
		
			// restore the query so that our page template can do a normal loop
			self::restoreQuery();
		
			ob_start();
			echo stripslashes(tribe_get_option('spEventsBeforeHTML'));
			include TribeEventsTemplates::get_current_page_template();
			echo stripslashes(tribe_get_option('spEventsAfterHTML'));				
			$contents = ob_get_contents();
			ob_end_clean();
		
			// spoof the query again because not all of our templates make use of the loop
			self::endQuery();

			return $contents;
		} 
	
		public static function load_ecp_title_into_page_template($title) {
			global $post;
			if (is_single() && !tribe_is_showing_all()) {
				return $title;
			} else {
				return tribe_get_events_title();	
			}
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
		 * Loads theme files in appropriate hierarchy: 1) child theme, 
		 * 2) parent template, 3) plugin resources. will look in the events/
		 * directory in a theme and the views/ directory in the plugin
		 *
		 * @param string $template template file to search for
		 * @return template path
		 * @author Matt Wiebe
		 **/
		public static function getTemplateHierarchy($template) {
			$tribe_ecp = TribeEvents::instance();

			if ( substr($template, -4) != '.php' ) {
				$template .= '.php';
			}

			if ( $theme_file = locate_template(array('events/'.$template)) ) {
				$file = $theme_file;
			}
			else {
				$file = $tribe_ecp->pluginPath . 'views/' . $template;
			}
			return apply_filters( 'tribe_events_template_'.$template, $file);
		}
	
		private static function spoofQuery() {
			global $wp_query, $withcomments;
			TribeEventsTemplates::$origPostCount = $wp_query->post_count;
			TribeEventsTemplates::$origCurrentPost =  $wp_query->current_post;
			$wp_query->current_post = -1;
			$wp_query->post_count = 2;		
			$wp_query->is_page = true; // don't show comments
			//$wp_query->is_single = false; // don't show comments
			$wp_query->is_singular = true;
		
		}
	
		private static function endQuery() {
			global $wp_query;
		
			$wp_query->current_post = 0;
			$wp_query->post_count = 1;		
		}	
	
		private static function restoreQuery() {
			global $wp_query;
			remove_filter('the_title', array(__CLASS__, 'load_ecp_title_into_page_template') );			
			$wp_query->current_post = TribeEventsTemplates::$origCurrentPost;
			$wp_query->post_count = TribeEventsTemplates::$origPostCount;
			$wp_query->rewind_posts();
		}
	}

	TribeEventsTemplates::init();
}
