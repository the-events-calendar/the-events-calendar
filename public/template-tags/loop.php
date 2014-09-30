<?php
/**
 * Loop Functions
 *
 * Display functions (template-tags) for use in WordPress templates.
 */

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( class_exists( 'TribeEvents' ) ) {

	/**
	 * New Day Test
	 *
	 * Called inside of the loop, returns true if the current post's meta_value (EventStartDate)
	 * is different than the previous post. Will always return true for the first event in the loop.
	 *
	 * @return bool
	 */
	function tribe_is_new_event_day() {
		global $post;
		$tribe_ecp = TribeEvents::instance();
		$retval    = false;
		$now       = time();
		if ( isset( $post->EventStartDate ) ) {
			$postTimestamp = strtotime( $post->EventStartDate, $now );
			$postTimestamp = strtotime( date( TribeDateUtils::DBDATEFORMAT, $postTimestamp ), $now ); // strip the time
			if ( $postTimestamp != $tribe_ecp->currentPostTimestamp ) {
				$retval = true;
			}
			$tribe_ecp->currentPostTimestamp = $postTimestamp;
			$return                          = $retval;
		} else {
			$return = true;
		}

		return apply_filters( 'tribe_is_new_event_day', $return );
	}

	/**
	 * Past Loop View Test
	 *
	 * Returns true if the query is set for past events, false otherwise
	 *
	 * @return bool
	 */
	function tribe_is_past() {
		global $wp_query;
		$is_past = ! empty( $wp_query->tribe_is_past ) && ! tribe_is_showing_all() ? $wp_query->tribe_is_past : false;

		return apply_filters( 'tribe_is_past', $is_past );
	}

	/**
	 * Upcoming Loop View Test
	 *
	 * Returns true if the query is set for upcoming events, false otherwise
	 *
	 * @return bool
	 */
	function tribe_is_upcoming() {
		global $wp_query;
		$is_upcoming = ( tribe_is_list_view() && ! tribe_is_past() ) ? true : false;

		return apply_filters( 'tribe_is_upcoming', $is_upcoming );
	}

	/**
	 * Show All Test
	 *
	 * Returns true if the query is set to show all events, false otherwise
	 *
	 * @return bool
	 * @todo move to ECP
	 */
	function tribe_is_showing_all() {
		$tribe_ecp            = TribeEvents::instance();
		$tribe_is_showing_all = ( $tribe_ecp->displaying == 'all' ) ? true : false;
		if ( $tribe_is_showing_all ) {
			add_filter( 'tribe_events_recurrence_tooltip', '__return_false' );
		}

		return apply_filters( 'tribe_is_showing_all', $tribe_is_showing_all );
	}

	/**
	 * Date View Test
	 *
	 *  Check if current display is "bydate"
	 *
	 * @return bool
	 */
	function tribe_is_by_date() {
		$tribe_ecp        = TribeEvents::instance();
		$tribe_is_by_date = ( $tribe_ecp->displaying == 'bydate' ) ? true : false;

		return apply_filters( 'tribe_is_by_date', $tribe_is_by_date );
	}

	/**
	 * Event Title (Display)
	 *
	 * Display an event's title with pseudo-breadcrumb if on a category
	 *
	 * @param bool $depth include linked title
	 */
	function tribe_events_title( $depth = true ) {
		echo apply_filters( 'tribe_events_title', tribe_get_events_title( $depth ) );
	}

	/**
	 * Event Title
	 *
	 * Return an event's title with pseudo-breadcrumb if on a category
	 *
	 * @param bool $depth include linked title
	 *
	 * @return string title
	 * @todo move logic to template classes
	 */
	function tribe_get_events_title( $depth = true ) {

		global $wp_query;

		$tribe_ecp = TribeEvents::instance();

		$title = __( 'Upcoming Events', 'tribe-events-calendar' );

		if ( isset( $_REQUEST['tribe-bar-date'] ) && $wp_query->have_posts() ) {
			$first_event_date = tribe_get_end_date( $wp_query->posts[0], false );
			$last_event_date = tribe_get_end_date( $wp_query->posts[count( $wp_query->posts ) - 1], false );
			$title = sprintf( __( 'Events for %1$s - %2$s', 'tribe-events-calendar'), $first_event_date, $last_event_date );
		} elseif ( tribe_is_past() ) {
			$title = __( 'Past Events', 'tribe-events-calendar' );
		}

		if ( tribe_is_month() ) {
			$title = sprintf(
				__( 'Events for %s', 'tribe-events-calendar' ),
				date_i18n( tribe_get_option( 'monthAndYearFormat', 'F Y' ), strtotime( tribe_get_month_view_date() ) )
			);
		}

		// day view title
		if ( tribe_is_day() ) {
			$title = __( 'Events for', 'tribe-events-calendar' ) . ' ' .
					 date_i18n( tribe_get_date_format( true ), strtotime( $wp_query->get( 'start_date' ) ) );
		}

		if ( is_tax( $tribe_ecp->get_event_taxonomy() ) && $depth ) {
			$cat = get_queried_object();
			$title = '<a href="' . tribe_get_events_link() . '">' . $title . '</a>';
			$title .= ' &#8250; ' . $cat->name;
		}

		return apply_filters( 'tribe_get_events_title', $title, $depth );
	}

	/**
	 * Link to Upcoming Events
	 *
	 * Returns a link to the upcoming events in list view. Used in the loop view.
	 *
	 * @return string URL
	 */
	function tribe_get_upcoming_link() {
		$tribe_ecp = TribeEvents::instance();
		$output    = $tribe_ecp->getLink( 'upcoming' );

		return apply_filters( 'tribe_get_upcoming_link', $output );
	}

	/**
	 * Are there any events previous to the current events in $wp_query
	 *
	 * @param string $return what to return, 'bool' or 'event'
	 *
	 * @return mixed
	 */
	function tribe_has_previous_event( $return = 'bool' ) {
		global $wp_query;
		$previous_event = false;

		if ( tribe_is_event_query() ) {
			// Edit the current event query to look for an upcoming event
			$args                 = (array) $wp_query->query;
			$args['eventDisplay'] = 'past';

			if ( tribe_is_past() ) {
				$args['paged'] = $wp_query->query_vars['paged'] ? $wp_query->query_vars['paged'] + 1 : 2;
			} else {
				$args['paged'] = $wp_query->query_vars['paged'] ? $wp_query->query_vars['paged'] - 1 : 0;
			}

			$events = tribe_get_events( $args );

			$previous_event = count( $events ) > 0;

		}

		// @todo 'tribe_has_past_events' filter is @deprecated as of 3.7
		return apply_filters( 'tribe_has_previous_event', apply_filters( 'tribe_has_past_events', $previous_event ) );
	}

	/**
	 * Are there any events next (in the future) to the current events in $wp_query
	 *
	 * @param string $return what to return, 'bool' or 'event'
	 *
	 * @return mixed
	 */
	function tribe_has_next_event( $return = 'bool' ) {
		global $wp_query;
		$next_event = false;

		if ( tribe_is_event_query() && ! empty( $wp_query->posts ) ) {

			// Edit the current event query to look for an upcoming event
			$args                 = (array) $wp_query->query;
			$args['eventDisplay'] = 'list';

			if ( tribe_is_past() ) {
				$args['paged'] = $wp_query->query_vars['paged'] ? $wp_query->query_vars['paged'] - 1 : 0;
			} else {
				$args['paged'] = $wp_query->query_vars['paged'] ? $wp_query->query_vars['paged'] + 1 : 2;
			}

			$events = tribe_get_events( $args );

			$next_event = count( $events ) > 0;

		}

		// @todo 'tribe_has_past_events' filter is @deprecated as of 3.7
		return apply_filters( 'tribe_has_next_event', apply_filters( 'tribe_has_next_events', $next_event ) );
	}


	/**
	 * Link to Past Events
	 *
	 * Returns a link to the previous events in list view. Used in the loop view.
	 *
	 * @return string URL
	 */
	function tribe_get_past_link() {
		$tribe_ecp = TribeEvents::instance();
		$output    = $tribe_ecp->getLink( 'past' );

		return apply_filters( 'tribe_get_past_link', $output );
	}

	/**
	 * Determines if we are in the main Loop (home/archives/tags)
	 *
	 * @return bool
	 */
	function tribe_is_in_main_loop() {
		return apply_filters( 'tribe_is_main_loop', TribeEventsTemplates::$isMainLoop );
	}

	/**
	 * Determines if we are in list view.
	 *
	 * @return bool
	 */
	function tribe_is_list_view() {
		$is_list_view = (TribeEvents::instance()->displaying == 'list') ? true : false;
		return apply_filters( 'tribe_is_list_view', $is_list_view );
	}

	/**
	 * Used in list loop, displays the date headers between events in the loop when the month / year has changed
	 *
	 * @return void
	 **/
	function tribe_events_list_the_date_headers() {

		/* Month and year separators (on every month and year change) */

		$show_headers = apply_filters( 'tribe_events_list_show_date_headers', true );

		$html = '';

		if ( $show_headers ) {

			global $post, $wp_query;

			$event_year        = tribe_get_start_date( $post, false, 'Y' );
			$event_month       = tribe_get_start_date( $post, false, 'm' );
			$month_year_format = tribe_get_option( 'monthAndYearFormat', 'F Y' );

			if ( $wp_query->current_post > 0 ) {
				$prev_post        = $wp_query->posts[$wp_query->current_post - 1];
				$prev_event_year  = tribe_get_start_date( $prev_post, false, 'Y' );
				$prev_event_month = tribe_get_start_date( $prev_post, false, 'm' );
			}


			/*
			 * If the event month changed since the last event in the loop,
			 * or is the same month but the year changed.
			 *
			 */
			if ( $wp_query->current_post === 0 || ( $prev_event_month != $event_month || ( $prev_event_month == $event_month && $prev_event_year != $event_year ) ) ) {
				$html .= sprintf( "<span class='tribe-events-list-separator-month'><span>%s</span></span>", tribe_get_start_date( $post, false, $month_year_format ) );
			}

			echo apply_filters( 'tribe_events_list_the_date_headers', $html, $event_month, $event_year );
		}
	}

	/**
	 * Determine classes to apply to left side nav links
	 *
	 * @param $side
	 */
	function tribe_left_navigation_classes() {

		$classes     = array();
		$tribe_paged = ( ! empty( $_REQUEST['tribe_paged'] ) ) ? $_REQUEST['tribe_paged'] : 1;

		$classes['direction'] = tribe_is_upcoming() ? 'tribe-events-nav-previous' : 'tribe-events-nav-next';
		$classes['side']      = 'tribe-events-nav-left';

		if ( tribe_is_past() || ( ( tribe_is_upcoming() && $tribe_paged == 1 ) ) ) {
			$classes['past'] = 'tribe-events-past';
		}
		$classes = apply_filters( 'tribe_left_navigation_classes', $classes );

		return implode( ' ', $classes );
	}

	/**
	 * Determine classes to apply to right side nav links
	 *
	 * @param $side
	 */
	function tribe_right_navigation_classes() {

		$classes     = array();
		$tribe_paged = ( ! empty( $_REQUEST['tribe_paged'] ) ) ? $_REQUEST['tribe_paged'] : 1;

		$classes['direction'] = tribe_is_upcoming() ? 'tribe-events-nav-next' : 'tribe-events-nav-previous';
		$classes['side']      = 'tribe-events-nav-right';

		if ( tribe_is_past() && $tribe_paged > 1 ) {
			$classes['past'] = 'tribe-events-past';
		}

		$classes = apply_filters( 'tribe_right_navigation_classes', $classes );

		return implode( ' ', $classes );
	}

	/**
	 * Checks whether we're on a particular view
	 *
	 * @return bool
	 **/
	function tribe_is_view( $view = false ) {
		return $view === TribeEvents::instance()->displaying;
	}
}
?>
