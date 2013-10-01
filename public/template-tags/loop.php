<?php
/**
 * Loop Functions
 *
 * Display functions (template-tags) for use in WordPress templates.
 */

// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }

if( class_exists( 'TribeEvents' ) ) {

	/**
	 * New Day Test
	 *
	 * Called inside of the loop, returns true if the current post's meta_value (EventStartDate)
	 * is different than the previous post. Will always return true for the first event in the loop.
	 *
	 * @return bool
	 * @since 2.0
	 */
	function tribe_is_new_event_day()  {
		global $post;
		$tribe_ecp = TribeEvents::instance();
		$retval = false;
		$now = time();
		if(isset($post->EventStartDate)) {
			$postTimestamp = strtotime( $post->EventStartDate, $now );
			$postTimestamp = strtotime( date( TribeDateUtils::DBDATEFORMAT, $postTimestamp ), $now); // strip the time
			if ( $postTimestamp != $tribe_ecp->currentPostTimestamp ) {
				$retval = true;
			}
			$tribe_ecp->currentPostTimestamp = $postTimestamp;
			$return = $retval;
		} else {
			$return = true;
		}
		return apply_filters('tribe_is_new_event_day', $return);
	}

	/**
	 * Single Day Test
	 *
	 * Returns true if the query is set for single day, false otherwise
	 * 
	 * @return bool
	 * @since 2.0
	 */
	function tribe_is_day()  {
		$tribe_ecp = TribeEvents::instance();
		$is_day = ($tribe_ecp->displaying == 'day') ? true : false;
		return apply_filters('tribe_is_day', $is_day);
	}

	/**
	 * Past Loop View Test
	 *
	 * Returns true if the query is set for past events, false otherwise
	 * 
	 * @return bool
	 * @since 2.0
	 */
	function tribe_is_past()  {
		global $wp_query;
		$is_past = !empty( $wp_query->tribe_is_past ) && !tribe_is_showing_all() ? $wp_query->tribe_is_past : false;
		return apply_filters('tribe_is_past', $is_past );
	}

	/**
	 * Upcoming Loop View Test
	 *
	 * Returns true if the query is set for upcoming events, false otherwise
	 * 
	 * @return bool
	 * @since 2.0
	 */
	function tribe_is_upcoming()  {
		global $wp_query;
		$is_upcoming = ( isset( $wp_query->query_vars['eventDisplay'] ) && $wp_query->query_vars['eventDisplay'] == 'upcoming' ) ? true : false;
		return apply_filters('tribe_is_upcoming', $is_upcoming );
	}
	
	/**
	 * Show All Test
	 *
	 * Returns true if the query is set to show all events, false otherwise
	 * 
	 * @return bool
	 * @since 2.0
	 */
	function tribe_is_showing_all()  {
		$tribe_ecp = TribeEvents::instance();
		$tribe_is_showing_all = ($tribe_ecp->displaying == 'all') ? true : false;
		if( $tribe_is_showing_all )
			add_filter( 'tribe_events_event_recurring_info_tooltip', '__return_false' );
		return apply_filters('tribe_is_showing_all', $tribe_is_showing_all);
	}

	/**
	 * Date View Test
	 *
	 *  Check if current display is "bydate"
	 *
	 * @return bool
	 * @since 2.0
	 */
	function tribe_is_by_date() {
		$tribe_ecp = TribeEvents::instance();
		$tribe_is_by_date = ( $tribe_ecp->displaying == 'bydate' ) ? true : false;
		return apply_filters('tribe_is_by_date', $tribe_is_by_date);
	}

	/**
	 * Event Title (Display)
	 *
	 * Display an event's title with pseudo-breadcrumb if on a category
	 *
	 * @param bool $depth include linked title
	 * @since 2.0
	 */ 
	function tribe_events_title( $depth = true )  {
		echo apply_filters('tribe_events_title', tribe_get_events_title( $depth ));
	}
	
	/**
	 * Event Title
	 *
	 * Return an event's title with pseudo-breadcrumb if on a category
	 *
	 * @param bool $depth include linked title
	 * @return string title
	 * @since 2.0
	 */
	function tribe_get_events_title( $depth = true )  {
		$tribe_ecp = TribeEvents::instance();

		$title = __('Upcoming Events', 'tribe-events-calendar');

		if ( tribe_is_upcoming() || isset( $_REQUEST['tribe-bar-date'] ) ) {

			// Use the displayed dates for the title
			$start_date = date('Y-m-d', strtotime($tribe_ecp->date));
			if ( $tribe_ecp->date && $start_date != date('Y-m-d') ) {
				global $wp_query;
				if ( get_query_var('paged') > 1 ) {
					// get the date of the first post
					$first_post = reset($wp_query->posts);
					$start_date = date('Y-m-d', strtotime($first_post->EventStartDate));
				}
				$format = __('Events for %1$s', 'tribe-events-calendar');
				$args = array(date_i18n( get_option( 'date_format', 'Y-m-d' ), strtotime($start_date) ));

				// Get the date of the last post
				if ( count($wp_query->posts) > 1 ) {
					$last_post = end($wp_query->posts);
					$last_post_date = date('Y-m-d', strtotime($last_post->EventStartDate));
					if ( $last_post_date != $start_date ) {
						$format = __('Events for %1$s through %2$s', 'tribe-events-calendar');
						$args[] = date_i18n( get_option( 'date_format', 'Y-m-d' ), strtotime($last_post_date) );
					}
				}
				$title = vsprintf($format, $args);
			}
		} else if ( tribe_is_past() ) {
			$title = __( 'Past Events', 'tribe-events-calendar' );
		}


		if( tribe_is_month() ){
			$title = sprintf( __( 'Events for %s', 'tribe-events-calendar' ),
				date_i18n( 'F Y', strtotime( tribe_get_month_view_date() ) )
			);
		}

		if ( is_tax( $tribe_ecp->get_event_taxonomy() ) ) {
			$cat = get_queried_object();
			if ( $depth ) {
				$title = '<a href="'.tribe_get_events_link().'">'.$title.'</a>';
				$title .= ' &#8250; ' . $cat->name;
			} else {
				$title = $cat->name;
			}
		}

		return apply_filters('tribe_template_factory_debug', apply_filters('tribe_get_events_title', $title), 'tribe_get_events_title');
	}

	/**
	 * Link to Upcoming Events
	 * 
	 * Returns a link to the upcoming events in list view. Used in the loop view.
	 *
	 * @return string URL
	 * @since 2.0
	 */
	function tribe_get_upcoming_link()  {
		$tribe_ecp = TribeEvents::instance();
		$output = $tribe_ecp->getLink('upcoming');
		return apply_filters('tribe_get_upcoming_link', $output);
	}
	
	/**
	 * Link to Past Events
	 * 
	 * Returns a link to the previous events in list view. Used in the loop view.
	 *
	 * @return string URL
	 * @since 2.0
	 */
	function tribe_get_past_link()  {
		$tribe_ecp = TribeEvents::instance();
		$output = $tribe_ecp->getLink('past');
		return apply_filters('tribe_get_past_link', $output);
	}


	/**
	 * Determines if we are in the main Loop (home/archives/tags)
	 *
	 * @return bool
	 * @since 2.1
	 */
	function tribe_is_in_main_loop()  {
		return apply_filters('tribe_is_main_loop', TribeEventsTemplates::$isMainLoop);
	}

	/**
	 * Determines if we are in list view.
	 *
	 * @return bool
	 * @since 2.1
	 */
	function tribe_is_list_view()  {
		if ( tribe_is_event_query() && ( tribe_is_upcoming() || tribe_is_past() || tribe_is_day() || ( is_single() && tribe_is_showing_all() ) ) ) {
			$return = true;
		} else {
			$return = false;
		}
	
		return apply_filters( 'tribe_is_list_view', $return );
	}

	/**
	 * Used in list loop, displays the date headers between events in the loop when the month / year has changed
	 *
	 * @return void
	 * @since 3.0
	 **/
	function tribe_events_list_the_date_headers() {

		/* Month and year separators (on every month and year change) */

		$show_headers = apply_filters( 'tribe_events_list_show_date_headers', true );

		$html = '';

		if ($show_headers) {

			global $post, $wp_query;

			$event_year = tribe_get_start_date( $post, false, 'Y' );
			$event_month = tribe_get_start_date( $post, false, 'm' );

			if ($wp_query->current_post > 0) {
				$prev_post = $wp_query->posts[$wp_query->current_post - 1];
				$prev_event_year = tribe_get_start_date( $prev_post, false, 'Y' );
				$prev_event_month = tribe_get_start_date( $prev_post, false, 'm' );
			}

			/*
			 * If the event month changed since the last event in the loop,
			 * or is the same month but the year changed.
			 *
			 */
			if ( $wp_query->current_post === 0 || ( $prev_event_month != $event_month || ( $prev_event_month == $event_month && $prev_event_year != $event_year ) ) ) {
				$html .= sprintf( "<span class='tribe-events-list-separator-month'><span>%s</span></span>", tribe_get_start_date( $post, false, 'F Y' ) );
			}

			/*
			 * If this event year is different to the year of the previous event in the loop,
			 * and it's not it's not the first event in the loop (we don't want to start the loop with a year separator)
			 */
			if ( $wp_query->current_post > 0 && $prev_event_year != $event_year ) {
				$html .= sprintf( "<span class='tribe-events-list-separator-year'>%s</span>", $event_year );
			}

			echo apply_filters('tribe_events_list_the_date_headers', $html, $event_month, $event_year);
		}		
	}

	/**
	 * Checks whether we're on a particular view
	 *
	 * @return bool
	 * @since 3.0
	 **/
	function tribe_is_view( $view = false ) {
		return $view === TribeEvents::instance()->displaying;
	}
}
?>
