<?php
/**
 * Templating functionality for Tribe Events Calendar
 */

// don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( ! class_exists( 'Tribe__Events__Templates' ) ) {

	/**
	 * Handle views and template files.
	 */
	class Tribe__Events__Templates extends Tribe__Templates {

		/**
		 * @var bool Is wp_head complete?
		 */
		public static $wpHeadComplete = false;

		/**
		 * @var bool Is this the main loop?
		 */
		public static $isMainLoop = false;

		/**
		 * If the global post title has to be modified the original is stored here.
		 *
		 * @var bool|string
		 */
		protected static $original_post_title = false;

		/**
		 * The template name currently being used
		 */
		protected static $template = false;

		/*
		 * List of templates which have compatibility fixes
		 */
		public static $themes_with_compatibility_fixes = [
			'twentysixteen',
			'twentyfifteen',
			'twentyfourteen',
			'twentythirteen',
		];

		/**
		 * Initialize the Template Yumminess!
		 */
		public static function init() {

			// Choose the wordpress theme template to use
			add_filter( 'template_include', [ __CLASS__, 'templateChooser' ] );

			// include our view class
			add_action( 'template_redirect', 'tribe_initialize_view' );

			// make sure we enter the loop by always having some posts in $wp_query
			add_action( 'wp_head', [ __CLASS__, 'maybeSpoofQuery' ], 100 );

			// maybe modify the global post object to blank out the title
			add_action( 'tribe_tec_template_chooser', [ __CLASS__, 'maybe_modify_global_post_title' ] );

			// don't query the database for the spoofed post
			wp_cache_set( self::spoofed_post()->ID, self::spoofed_post(), 'posts' );
			wp_cache_set( self::spoofed_post()->ID, [ true ], 'post_meta' );

			// there's no template redirect on ajax, so we include the template class right before the view is included
			if ( tribe_is_ajax_view_request() ) {
				add_action( 'admin_init', 'tribe_initialize_view' );
			}

			add_action( 'wp_head', [ __CLASS__, 'wpHeadFinished' ], 999 );

			// add the theme name to the body class when needed
			if ( self::needs_compatibility_fix() ) {
				add_filter( 'body_class', [ __CLASS__, 'theme_body_class' ] );
			}

			add_filter( 'get_post_time', [ __CLASS__, 'event_date_to_pubDate' ], 10, 3 );
		}

		/**
		 * Pick the correct template to include
		 *
		 * @param string $template Path to template
		 *
		 * @return string Path to template
		 */
		public static function templateChooser( $template ) {
			$events = Tribe__Events__Main::instance();
			do_action( 'tribe_tec_template_chooser', $template );

			// no non-events need apply
			if ( ! tribe_is_event_query() ) {
				return $template;
			}

			// if it's a single 404 event
			if ( is_single() &&  is_404() ) {
				return get_404_template();
			}

			if (
				! is_single()
				&& ! tribe_events_is_view_enabled( $events->displaying )
				// we want the day view to display if visited (this allows it to be largely disabled while
				// still allowing month overflow links to work correctly)
				&& 'day' != $events->displaying
			) {
				return get_404_template();
			}

			// add the theme slug to the body class
			add_filter( 'body_class', [ __CLASS__, 'theme_body_class' ] );

			// add the template name to the body class
			add_filter( 'body_class', [ __CLASS__, 'template_body_class' ] );

			// user has selected a page/custom page template
			if ( tribe_get_option( 'tribeEventsTemplate', 'default' ) != '' ) {
				if ( ! is_single() || ! post_password_required() ) {
					add_action( 'loop_start', [ __CLASS__, 'setup_ecp_template' ] );
				}

				$template = tribe_get_option( 'tribeEventsTemplate', 'default' ) !== 'default'
					? locate_template( tribe_get_option( 'tribeEventsTemplate', 'default' ) )
					: get_page_template();

				if ( $template == '' ) {
					$template = get_index_template();
				}

				// remove singular body class if sidebar-page.php
				if ( $template == get_stylesheet_directory() . '/sidebar-page.php' ) {
					add_filter( 'body_class', [ __CLASS__, 'remove_singular_body_class' ] );
				} else {
					add_filter( 'body_class', [ __CLASS__, 'add_singular_body_class' ] );
				}
			} else {
				$template = self::getTemplateHierarchy( 'default-template' );

			}

			// if this is an oembed, override the wrapping template and use the embed template
			if ( Tribe__Templates::is_embed() ) {
				$template = self::getTemplateHierarchy( 'embed' );
			}

			self::$template = $template;

			return $template;

		}

		/**
		 * Include the class for the current view
		 *
		 * @param bool $class
		 *
		 **/
		public static function instantiate_template_class( $class = false ) {
			if ( tribe_is_event_query() || tribe_is_ajax_view_request() ) {
				if ( ! $class ) {
					$class = self::get_current_template_class();
				}
				if ( class_exists( $class ) ) {
					new $class;
				}
			}
		}

		/**
		 * Include page template body class
		 *
		 * @param array $classes List of classes to filter
		 *
		 * @return mixed
		 */
		public static function template_body_class( $classes ) {

			$template_filename = basename( self::$template );

			if ( $template_filename == 'default-template.php' ) {
				$classes[] = 'tribe-events-page-template';
			} else {
				$classes[] = 'page-template-' . sanitize_title( $template_filename );
			}

			return $classes;
		}

		/**
		 * Remove "singular" from available body class
		 *
		 * @param array $classes List of classes to filter
		 *
		 * @return mixed
		 */
		public function remove_singular_body_class( $classes ) {
			$key = array_search( 'singular', $classes );
			if ( $key ) {
				unset( $classes[ $key ] );
			}

			return $classes;
		}

		/**
		 * Add the "singular" body class
		 *
		 * @param array $classes List of classes to filter
		 *
		 * @return array
		 */
		public static function add_singular_body_class( $classes ) {
			$classes[] = 'singular';

			return $classes;
		}

		/**
		 * Add the theme to the body class
		 *
		 * @return array $classes
		 **/
		public static function theme_body_class( $classes ) {
			$child_theme  = get_option( 'stylesheet' );
			$parent_theme = get_option( 'template' );

			// if the 2 options are the same, then there is no child theme
			if ( $child_theme == $parent_theme ) {
				$child_theme = false;
			}

			if ( $child_theme ) {
				$theme_classes = "tribe-theme-parent-$parent_theme tribe-theme-child-$child_theme";
			} else {
				$theme_classes = "tribe-theme-$parent_theme";
			}

			$classes[] = $theme_classes;

			return $classes;
		}


		/**
		 * Checks if theme needs a compatibility fix
		 *
		 * @param string $theme Name of template from WP_Theme->Template, defaults to current active template
		 *
		 *@return mixed
		 */
		public static function needs_compatibility_fix ( $theme = null ) {
			// Defaults to current active theme
			if ( $theme === null ) {
				$theme = get_stylesheet();
			}

			$theme_compatibility_list = apply_filters( 'tribe_themes_compatibility_fixes', self::$themes_with_compatibility_fixes );

			return in_array( $theme, $theme_compatibility_list );
		}


		/**
		 * Determine when wp_head has been triggered.
		 */
		public static function wpHeadFinished() {
			self::$wpHeadComplete = true;
		}


		/**
		 * This is where the magic happens where we run some ninja code that hooks the query to resolve to an events template.
		 *
		 * @param WP_Query $query
		 */
		public static function setup_ecp_template( $query ) {

			do_action( 'tribe_events_filter_the_page_title' );

			if ( self::is_main_loop( $query ) && self::$wpHeadComplete ) {
				// on loop start, unset the global post so that template tags don't work before the_content()
				add_action( 'the_post', [ __CLASS__, 'spoof_the_post' ] );

				// on the_content, load our events template
				add_filter( 'the_content', [ __CLASS__, 'load_ecp_into_page_template' ] );

				// remove the comments template
				add_filter( 'comments_template', [ __CLASS__, 'load_ecp_comments_page_template' ] );

				// only do this once
				remove_action( 'loop_start', [ __CLASS__, 'setup_ecp_template' ] );
			}
		}

		/**
		 * Spoof the global post just once
		 *
		 **/
		public static function spoof_the_post() {
			$GLOBALS['post'] = self::spoofed_post();
			remove_action( 'the_post', [ __CLASS__, 'spoof_the_post' ] );
		}


		/**
		 * Fix issues where themes display the_title() before the main loop starts.
		 *
		 * With some themes the title of single events can be displayed twice and, more crucially, it may result in the
		 * event views such as month view prominently displaying the title of the most recent event post (which may
		 * not even be included in the view output).
		 *
		 * There's no bulletproof solution to this problem, but for affected themes a preventative measure can be turned
		 * on by adding the following to wp-config.php:
		 *
		 *     define( 'TRIBE_MODIFY_GLOBAL_TITLE', true );
		 *
		 * Note: this reverses the situation in version 3.2, when this behaviour was enabled by default. In response to
		 * feedback it will now be disabled by default and will need to be turned on by adding the above line.
		 *
		 * @see issues #24294, #23260
		 */
		public static function maybe_modify_global_post_title() {
			global $post;

			// We will only interfere with event queries, where a post is set and this behaviour is enabled
			if ( ! tribe_is_event_query() || ! defined( 'TRIBE_MODIFY_GLOBAL_TITLE' ) || ! TRIBE_MODIFY_GLOBAL_TITLE ) {
				return;
			}
			if ( ! isset( $post ) || ! $post instanceof WP_Post ) {
				return;
			}

			// Wait until late in the wp_title|document_title_parts hook to actually make a change - this should allow single event titles
			// to be used within the title element itself
			add_filter( 'document_title_parts', [ __CLASS__, 'modify_global_post_title' ], 1000 );
			add_filter( 'wp_title', [ __CLASS__, 'modify_global_post_title' ], 1000 );
		}

		/**
		 * Actually modifies the global $post object's title property, setting it to an empty string.
		 *
		 * This is expected to be called late on during the wp_title action, but does not in fact alter the string
		 * it is passed.
		 *
		 * @see Tribe__Events__Templates::maybe_modify_global_post_title()
		 *
		 * @param string $title
		 *
		 * @return string
		 */
		public static function modify_global_post_title( $title = '' ) {
			global $post;

			// When in the loop, no need to override titles.
			if ( in_the_loop() ) {
				return $title;
			}

			// Set the title to an empty string (but record the original)
			self::$original_post_title = $post->post_title;
			$post->post_title          = apply_filters( 'tribe_set_global_post_title', '' );

			// Restore as soon as we're ready to display one of our own views
			add_action( 'tribe_pre_get_view', [ __CLASS__, 'restore_global_post_title' ] );

			// Now return the title unmodified
			return $title;
		}


		/**
		 * Restores the global $post title if it has previously been modified.
		 *
		 * @see Tribe__Events__Templates::modify_global_post_title().
		 */
		public static function restore_global_post_title() {
			global $post;
			$post->post_title = self::$original_post_title;
			remove_action( 'tribe_pre_get_view', [ __CLASS__, 'restore_global_post_title' ] );
		}


		/**
		 * Get the correct internal page template
		 *
		 * @return string Template path
		 */
		public static function get_current_page_template() {

			$template = '';

			// list view
			if ( tribe_is_list_view() ) {
				$template = self::getTemplateHierarchy( 'list', [ 'disable_view_check' => true ] );
			}

			// month view
			if ( tribe_is_month() ) {
				$template = self::getTemplateHierarchy( 'month', [ 'disable_view_check' => true ] );
			}

			// day view
			if ( tribe_is_day() ) {
				$template = self::getTemplateHierarchy( 'day' );
			}

			if ( Tribe__Templates::is_embed() ) {
				$template = self::getTemplateHierarchy( 'embed' );
			}

			// single event view
			if (
				is_singular( Tribe__Events__Main::POSTTYPE )
				&& ! tribe_is_showing_all()
				&& ! Tribe__Templates::is_embed()
			) {
				$template = self::getTemplateHierarchy( 'single-event', [ 'disable_view_check' => true ] );
			}

			// apply filters
			return apply_filters( 'tribe_events_current_view_template', $template );

		}


		/**
		 * Get the correct internal page template
		 *
		 * @return string Template class
		 */
		public static function get_current_template_class() {

			$class = '';

			// list view
			if ( tribe_is_list_view() || tribe_is_showing_all() || tribe_is_ajax_view_request( 'list' ) ) {
				$class = 'Tribe__Events__Template__List';
			}
			// month view
			elseif ( tribe_is_month() || tribe_is_ajax_view_request( 'month' ) ) {
				$class = 'Tribe__Events__Template__Month';
			}
			// day view
			elseif ( tribe_is_day() || tribe_is_ajax_view_request( 'day' ) ) {
				$class = 'Tribe__Events__Template__Day';
			}
			elseif ( Tribe__Templates::is_embed() ) {
				$class = 'Tribe__Events__Template__Embed';
			}
			// single event view
			elseif ( is_singular( Tribe__Events__Main::POSTTYPE ) ) {
				$class = 'Tribe__Events__Template__Single_Event';
			}

			// apply filters
			return apply_filters( 'tribe_events_current_template_class', $class );

		}


		/**
		 * Loads the contents into the page template
		 *
		 * @return string Page content
		 */
		public static function load_ecp_into_page_template( $contents = '' ) {
			// only run once!!!
			remove_filter( 'the_content', [ __CLASS__, 'load_ecp_into_page_template' ] );

			self::restoreQuery();

			ob_start();

			echo tribe_events_before_html();

			tribe_get_view();

			echo tribe_events_after_html();

			$contents = ob_get_clean();

			// make sure the loop ends after our template is included
			if ( ! is_404() ) {
				self::endQuery();
			}

			return $contents;
		}


		public static function load_ecp_comments_page_template( $template ) {

			remove_filter( 'comments_template', [ __CLASS__, 'load_ecp_comments_page_template' ] );
			if ( ! is_single() || tribe_is_showing_all() || ( tribe_get_option( 'showComments', false ) === false ) ) {
				return Tribe__Events__Main::instance()->pluginPath . 'src/admin-views/no-comments.php';
			}

			return $template;
		}

		/**
		 * Checks where we are are and determines if we should show events in the main loop
		 *
		 * @param WP_Query $query
		 *
		 * @return WP_Query
		 */
		public static function showInLoops( $query ) {

			if ( ! is_admin() && tribe_get_option( 'showInLoops' ) && ( $query->is_home() || $query->is_tag ) && empty( $query->query_vars['post_type'] ) && false == $query->query_vars['suppress_filters'] ) {

				// @todo [BTRIA-608]: Remove - 3.3 know-how for main query check.
				if ( self::is_main_loop( $query ) ) {
					self::$isMainLoop = true;
					$post_types       = [ 'post', Tribe__Events__Main::POSTTYPE ];
					$query->set( 'post_type', $post_types );
				}
			}

			return $query;
		}

		/**
		 * Loads theme files in appropriate hierarchy: 1) child theme,
		 * 2) parent template, 3) plugin resources. will look in the events/
		 * directory in a theme and the views/ directory in the plugin
		 *
		 * @param string $template template file to search for
		 * @param array  $args     additional arguments to affect the template path
		 *                         - namespace
		 *                         - plugin_path
		 *                         - disable_view_check - bypass the check to see if the view is enabled
		 *
		 * @return template path
		 **/
		public static function getTemplateHierarchy( $template, $args = [] ) {
			if ( ! is_array( $args ) ) {
				$passed        = func_get_args();
				$args          = [];
				$backwards_map = [ 'namespace', 'plugin_path' ];
				$count = count( $passed );

				if ( $count > 1 ) {
					for ( $i = 1; $i < $count; $i ++ ) {
						$args[ $backwards_map[ $i - 1 ] ] = $passed[ $i ];
					}
				}
			}

			$args = wp_parse_args(
				$args, [
					'namespace'          => '/',
					'plugin_path'        => '',
					'disable_view_check' => false,
				]
			);
			/**
			 * @var string $namespace
			 * @var string $plugin_path
			 * @var bool   $disable_view_check
			 */
			extract( $args );

			$tec = Tribe__Events__Main::instance();

			// append .php to file name
			if ( substr( $template, - 4 ) != '.php' ) {
				$template .= '.php';
			}

			// Allow base path for templates to be filtered
			$template_base_paths = apply_filters( 'tribe_events_template_paths', ( array ) Tribe__Events__Main::instance()->pluginPath );

			// backwards compatibility if $plugin_path arg is used
			if ( $plugin_path && ! in_array( $plugin_path, $template_base_paths ) ) {
				array_unshift( $template_base_paths, $plugin_path );
			}

			// ensure that addon plugins look in the right override folder in theme
			$namespace = ! empty( $namespace ) ? trailingslashit( $namespace ) : $namespace;

			$file = false;

			/* potential scenarios:

			- the user has no template overrides
				-> we can just look in our plugin dirs, for the specific path requested, don't need to worry about the namespace
			- the user created template overrides without the namespace, which reference non-overrides without the namespace and, their own other overrides without the namespace
				-> we need to look in their theme for the specific path requested
				-> if not found, we need to look in our plugin views for the file by adding the namespace
			- the user has template overrides using the namespace
				-> we should look in the theme dir, then the plugin dir for the specific path requested, don't need to worry about the namespace

			*/

			// check if there are overrides at all
			if ( locate_template( [ 'tribe-events/' ] ) ) {
				$overrides_exist = true;
			} else {
				$overrides_exist = false;
			}

			if ( $overrides_exist ) {
				// check the theme for specific file requested
				$file = locate_template( [ 'tribe-events/' . $template ], false, false );
				if ( ! $file ) {
					// if not found, it could be our plugin requesting the file with the namespace,
					// so check the theme for the path without the namespace
					$files = [];
					foreach ( array_keys( $template_base_paths ) as $namespace ) {
						if ( ! empty( $namespace ) && ! is_numeric( $namespace ) ) {
							$files[] = 'tribe-events' . str_replace( $namespace, '', $template );
						}
					}
					$file = locate_template( $files, false, false );
					if ( $file ) {
						_deprecated_function( sprintf( esc_html__( 'Template overrides should be moved to the correct subdirectory: %s', 'the-events-calendar' ), str_replace( get_stylesheet_directory() . '/tribe-events/', '', $file ) ), '3.2', $template );
					}
				} else {
					$file = apply_filters( 'tribe_events_template', $file, $template );
				}
			}

			// if the theme file wasn't found, check our plugins views dirs
			if ( ! $file ) {

				foreach ( $template_base_paths as $template_base_path ) {

					// make sure directories are trailingslashed
					$template_base_path = ! empty( $template_base_path ) ? trailingslashit( $template_base_path ) : $template_base_path;

					$file = $template_base_path . 'src/views/' . $template;

					$file = apply_filters( 'tribe_events_template', $file, $template );

					// return the first one found
					if ( file_exists( $file ) ) {
						break;
					} else {
						$file = false;
					}
				}
			}

			// file wasn't found anywhere in the theme or in our plugin at the specifically requested path,
			// and there are overrides, so look in our plugin for the file with the namespace added
			// since it might be an old override requesting the file without the namespace
			if ( ! $file && $overrides_exist ) {
				foreach ( $template_base_paths as $_namespace => $template_base_path ) {

					// make sure directories are trailingslashed
					$template_base_path = ! empty( $template_base_path ) ? trailingslashit( $template_base_path ) : $template_base_path;
					$_namespace         = ! empty( $_namespace ) ? trailingslashit( $_namespace ) : $_namespace;

					$file = $template_base_path . 'src/views/' . $_namespace . $template;

					$file = apply_filters( 'tribe_events_template', $file, $template );

					// return the first one found
					if ( file_exists( $file ) ) {
						_deprecated_function( sprintf( esc_html__( 'Template overrides should be moved to the correct subdirectory: tribe_get_template_part(\'%s\')', 'the-events-calendar' ), $template ), '3.2', 'tribe_get_template_part(\'' . $_namespace . $template . '\')' );
						break;
					}
				}
			}

			return apply_filters( 'tribe_events_template_' . $template, $file );
		}


		/**
		 * Convert the post_date_gmt to the event date for feeds
		 *
		 * @param $time the post_date
		 * @param $d    the date format to return
		 * @param $gmt  whether this is a gmt time
		 *
		 * @return int|string
		 */
		public static function event_date_to_pubDate( $time, $d, $gmt ) {
			global $post;

			if ( is_object( $post ) && $post->post_type == Tribe__Events__Main::POSTTYPE && is_feed() && $gmt ) {

				//WordPress always outputs a pubDate set to 00:00 (UTC) so account for that when returning the Event Start Date and Time
				$zone = get_option( 'timezone_string', false );

				if ( $zone ) {
				  $zone = new DateTimeZone( $zone );
				} else {
				  $zone = new DateTimeZone( 'UTC' );
				}

				$time = new DateTime( tribe_get_start_date( $post->ID, false, $d ), $zone );
				$time->setTimezone( new DateTimeZone( 'UTC' ) );
				$time = $time->format( $d );

			}

			return $time;
		}


		/**
		 * Query is complete: stop the loop from repeating.
		 */
		private static function endQuery() {

			$wp_query = tribe_get_global_query_object();

			$wp_query->current_post = -1;
			$wp_query->post_count   = 0;
		}


		/**
		 * Spoof the query so that we can operate independently of what has been queried.
		 *
		 * @return object
		 */
		private static function spoofed_post() {
			return (object) [
				'ID'                    => 0,
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
				'is_404'                => false,
				'is_page'               => false,
				'is_single'             => false,
				'is_archive'            => false,
				'is_tax'                => false,
			];
		}


		/**
		 * Decide if we need to spoof the query.
		 */
		public static function maybeSpoofQuery() {

			// hijack this method right up front if it's a password protected post and the password isn't entered
			if ( is_single() && post_password_required() || is_feed() ) {
				return;
			}

			$wp_query = tribe_get_global_query_object();

			if ( $wp_query->is_main_query() && tribe_is_event_query() && tribe_get_option( 'tribeEventsTemplate', 'default' ) != '' ) {

				// we need to ensure that we always enter the loop, whether or not there are any events in the actual query

				$spoofed_post = self::spoofed_post();

				$GLOBALS['post']      = $spoofed_post;
				$wp_query->posts[]    = $spoofed_post;
				$wp_query->post_count = count( $wp_query->posts );

				$wp_query->spoofed = true;
				$wp_query->rewind_posts();

			}
		}


		/**
		 * Restore the original query after spoofing it.
		 */
		public static function restoreQuery() {
			$wp_query = tribe_get_global_query_object();

			// If the query hasn't been spoofed we need take no action
			if ( ! isset( $wp_query->spoofed ) || ! $wp_query->spoofed ) {
				return;
			}

			// Remove the spoof post and fix the post count
			array_pop( $wp_query->posts );
			$wp_query->post_count = count( $wp_query->posts );

			// If we have other posts besides the spoof, rewind and reset
			if ( $wp_query->post_count > 0 ) {
				$wp_query->rewind_posts();
				wp_reset_postdata();
			}
			// If there are no other posts, unset the $post property
			elseif ( 0 === $wp_query->post_count ) {
				$wp_query->current_post = -1;
				unset( $wp_query->post );
			}

			// Don't do this again
			unset( $wp_query->spoofed );
		}
	}
}
