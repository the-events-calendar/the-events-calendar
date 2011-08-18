<?php
/**
 * Templating functionality for Tribe Events Calendar
 */

// don't load directly
if ( !defined('ABSPATH') )
	die('-1');

if (!class_exists('Tribe_ECP_Templates')) {
	class Tribe_ECP_Templates {
		public static $origPostCount;
		public static $origCurrentPost;
		public static $throughHead = false;
	
		public static function init() {
			add_filter( 'template_include', array( __CLASS__, 'templateChooser') );
			add_action( 'wp_head', array( __CLASS__, 'wpHeadFinished'), 999 );
		}

		// pick the correct template to include
		public static function templateChooser($template) {
			// hijack to iCal template
			if ( get_query_var('ical') || isset($_GET['ical']) ) {
				global $wp_query;
				if ( is_single() ) {
					$post_id = $wp_query->post->ID;
					$this->iCalFeed($post_id);
				}
				else if ( is_tax( self::TAXONOMY) ) {
					$this->iCalFeed( null, get_query_var( self::TAXONOMY ) );
				}
				else {
					$this->iCalFeed();
				}
				die;
			}

			// no non-events need apply
			if ( get_query_var( 'post_type' ) != Events_Calendar_Pro::POSTTYPE && ! is_tax( Events_Calendar_Pro::TAXONOMY ) && get_query_var( 'post_type' ) != Events_Calendar_Pro::VENUE_POST_TYPE ) {
				return $template;
			}

			//is_home fixer
			global $wp_query;
			$wp_query->is_home = false;

			//echo  sp_get_option('spEventsTemplate', ''); die();
			if( sp_get_option('spEventsTemplate', '') == '' ) {
				if(is_single() && !sp_is_showing_all() ) {
					return Tribe_ECP_Templates::getTemplateHierarchy('ecp-single-template');
				} else {
					return Tribe_ECP_Templates::getTemplateHierarchy('ecp-page-template');
				}
			} else {
				// we need to ensure that we always enter the loop, whether or not there are any events in the actual query
				self::spoofQuery();
				add_action( 'loop_start', array(__CLASS__, 'setup_ecp_template'));
			
				$template = locate_template( sp_get_option('spEventsTemplate', '') == 'default' ? 'page.php' : sp_get_option('spEventsTemplate', '') );
				if ($template ==  '') $template = get_index_template();
			
				return $template;
			}			
		}
	
		public static function wpHeadFinished() {
			self::$throughHead = true;
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
	      if ( is_singular( Events_Calendar_Pro::VENUE_POST_TYPE ) ) {
	         return Tribe_ECP_Templates::getTemplateHierarchy('single-venue');
	      }

			if ( is_tax( Events_Calendar_Pro::TAXONOMY) ) {
				if ( sp_is_upcoming() || sp_is_past() )
					return Tribe_ECP_Templates::getTemplateHierarchy('list');
				else
					return Tribe_ECP_Templates::getTemplateHierarchy('gridview');
			}
			// single event
			if ( is_single() && !sp_is_showing_all() ) {
				return Tribe_ECP_Templates::getTemplateHierarchy('single');
			}
			// list view
			elseif ( sp_is_upcoming() || sp_is_past() || (is_single() && sp_is_showing_all()) ) {
				return Tribe_ECP_Templates::getTemplateHierarchy('list');
			}
			// grid view
			else 
			{
				return Tribe_ECP_Templates::getTemplateHierarchy('gridview');
			}
		}

		// loads the contents into the page template
		public static function load_ecp_into_page_template() {
			// only run once!!!
			remove_filter('the_content', array(__CLASS__, 'load_ecp_into_page_template') );	
		
			// restore the query so that our page template can do a normal loop
			self::restoreQuery();
		
			ob_start();
			echo stripslashes(sp_get_option('spEventsBeforeHTML'));
			include Tribe_ECP_Templates::get_current_page_template();
			echo stripslashes(sp_get_option('spEventsAfterHTML'));				
			$contents = ob_get_contents();
			ob_end_clean();
		
			// spoof the query again because not all of our templates make use of the loop
			self::endQuery();

			return $contents;
		} 
	
		public static function load_ecp_title_into_page_template($title) {
			global $post;
			if (is_single() && !sp_is_showing_all()) {
				return $title;
			} else {
				return sp_get_events_title();	
			}
		}
	
		public static function load_ecp_comments_page_template($template) {
			$tribe_ecp = Events_Calendar_Pro::instance();
		
			remove_filter('comments_template', array(__CLASS__, 'load_ecp_comments_page_template') );		
			if (!is_single() || sp_is_showing_all() || (sp_get_option('showComments','no') == 'no')) {
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
			$tribe_ecp = Events_Calendar_Pro::instance();

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
			Tribe_ECP_Templates::$origPostCount = $wp_query->post_count;
			Tribe_ECP_Templates::$origCurrentPost =  $wp_query->current_post;
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
			$wp_query->current_post = Tribe_ECP_Templates::$origCurrentPost;
			$wp_query->post_count = Tribe_ECP_Templates::$origPostCount;
			$wp_query->rewind_posts();
		}
	}

	Tribe_ECP_Templates::init();
}
?>