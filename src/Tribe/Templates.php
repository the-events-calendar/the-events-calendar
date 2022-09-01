<?php
/**
 * Templating functionality for Tribe Events Calendar
 */


/**
 * Handle views and template files.
 */
class Tribe__Events__Templates extends Tribe__Templates {
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
	 * @return string Template path.
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
}
