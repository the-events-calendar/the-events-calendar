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

if ( class_exists( 'Tribe__Events__Main' ) ) {

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

		$tribe_ecp = Tribe__Events__Main::instance();
		$retval    = false;
		$now       = time();

		if ( isset( $post->EventStartDate ) ) {
			$postTimestamp = strtotime( $post->EventStartDate, $now );
			$postTimestamp = strtotime( date( Tribe__Date_Utils::DBDATEFORMAT, $postTimestamp ), $now ); // strip the time

			if ( $postTimestamp != $tribe_ecp->currentPostTimestamp ) {
				$retval = true;
			}

			$tribe_ecp->currentPostTimestamp = $postTimestamp;
			$return                          = $retval;
		} else {
			$return = true;
		}

		/**
		 * Allows for customization of the result of whether the current event is on a different day
		 * from the one preceding it. Defaults to "true" for first event in the loop.
		 *
		 * @param bool $return Whether the current event is on a different day than its antecedent.
		 */
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

		/**
		 * Allows for customization of the result of whether the current view shows past events.
		 *
		 * @param bool $is_past Whether the current view shows upcoming past or not.
		 */
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

		/**
		 * Allows for customization of the result of whether the current view shows upcoming events.
		 *
		 * @param bool $is_upcoming Whether the current view shows upcoming events or not.
		 */
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
		$tribe_ecp            = Tribe__Events__Main::instance();
		$tribe_is_showing_all = ( $tribe_ecp->displaying == 'all' ) ? true : false;

		if ( $tribe_is_showing_all ) {
			add_filter( 'tribe_events_recurrence_tooltip', '__return_false' );
		}

		/**
		 * Allows for customization of the result of whether the current view shows all events.
		 *
		 * @param bool $tribe_is_showing_all Whether the current view shows all events or not.
		 */
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
		$tribe_ecp        = Tribe__Events__Main::instance();
		$tribe_is_by_date = ( $tribe_ecp->displaying == 'bydate' ) ? true : false;
		/**
		 * Allows for customization of the result of whether or not a "bydate" view is being viewed.
		 *
		 * @param bool $tribe_is_by_date Whether a "bydate" calendar view is currently being displayed.
		 */
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
		/**
		 * Allows for customization of the title on the main "Events" page.
		 *
		 * @param string $title The events title as a result of the tribe_get_events_title() template tag.
		 * @param bool $depth Whether to include the linked title or not.
		 */
		echo apply_filters( 'tribe_events_title', tribe_get_events_title( $depth ), $depth );
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

		$events_label_plural = tribe_get_event_label_plural();

		if ( $wp_query->get( 'featured' ) ) {
			$events_label_plural = sprintf( _x( 'Featured %s', 'featured events title', 'the-events-calendar'), $events_label_plural );
		}

		$tribe_ecp = Tribe__Events__Main::instance();

		if ( is_single() && tribe_is_event() ) {
			// For single events, the event title itself is required
			$title = get_the_title();
		} else {
			// For all other cases, start with 'upcoming events'
			$title = sprintf( esc_html__( 'Upcoming %s', 'the-events-calendar' ), $events_label_plural );
		}

		// If there's a date selected in the tribe bar, show the date range of the currently showing events
		if ( isset( $_REQUEST['tribe-bar-date'] ) && $wp_query->have_posts() ) {
			$first_returned_date = tribe_get_start_date( $wp_query->posts[0], false, Tribe__Date_Utils::DBDATEFORMAT );
			$first_event_date    = tribe_get_start_date( $wp_query->posts[0], false );
			$last_event_date     = tribe_get_end_date( $wp_query->posts[ count( $wp_query->posts ) - 1 ], false );

			// If we are on page 1 then we may wish to use the *selected* start date in place of the
			// first returned event date
			if ( 1 == $wp_query->get( 'paged' ) && $_REQUEST['tribe-bar-date'] < $first_returned_date ) {
				$first_event_date = tribe_format_date( $_REQUEST['tribe-bar-date'], false );
			}

			$title = sprintf( __( '%1$s for %2$s - %3$s', 'the-events-calendar' ), $events_label_plural, $first_event_date, $last_event_date );
		} elseif ( tribe_is_past() ) {
			$title = sprintf( esc_html__( 'Past %s', 'the-events-calendar' ), $events_label_plural );
		}

		if ( tribe_is_month() ) {
			$title = sprintf(
				esc_html_x( '%1$s for %2$s', 'month view', 'the-events-calendar' ),
				$events_label_plural,
				date_i18n( tribe_get_date_option( 'monthAndYearFormat', 'F Y' ), strtotime( tribe_get_month_view_date() ) )
			);
		}

		// day view title
		if ( tribe_is_day() ) {
			$title = sprintf(
				esc_html_x( '%1$s for %2$s', 'day_view', 'the-events-calendar' ),
				$events_label_plural,
				date_i18n( tribe_get_date_format( true ), strtotime( $wp_query->get( 'start_date' ) ) )
			);
		}

		if ( is_tax( $tribe_ecp->get_event_taxonomy() ) && $depth ) {
			$cat = get_queried_object();
			$title = '<a href="' . esc_url( tribe_get_events_link() ) . '">' . $title . '</a>';
			$title .= ' &#8250; ' . $cat->name;
		}

		/**
		 * Allows for customization of the "Events" page title.
		 *
		 * @param string $title The "Events" page title as it's been generated thus far.
		 * @param bool $depth Whether to include the linked title or not.
		 */
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
		$tribe_ecp = Tribe__Events__Main::instance();
		$output    = $tribe_ecp->getLink( 'upcoming' );

		return apply_filters( 'tribe_get_upcoming_link', $output );
	}

	/**
	 * Are there any events previous to the current events in $wp_query
	 *
	 * @return bool
	 */
	function tribe_has_previous_event() {
		global $wp_query;

		$has_previous = false;
		$past         = tribe_is_past();
		$upcoming     = ! $past;
		$cur_page     = (int) $wp_query->get( 'paged' );
		$max_pages    = (int) $wp_query->max_num_pages;
		$page_1       = 0 === $cur_page || 1 === $cur_page;

		// if we are on page "0" or 1, consider it page 1. Otherwise, consider it the current page. This
		// is used for determining which navigation items to show
		$effective_page = $page_1 ? 1 : $cur_page;

		// Simple tests based on pagination properties
		if ( $upcoming && $effective_page > 1 ) $has_previous = true;
		if ( $past && $effective_page < $max_pages ) $has_previous = true;

		// Test for past events (on first page of upcoming list only)
		if ( $upcoming && $page_1 && ! $has_previous ) {
			// Inherit args from the main query so that taxonomy conditions etc are respected
			$args = (array) $wp_query->query;

			// Indicate we're interested in past events
			$args['tribe_is_past'] = true;

			// Make some efficiency savings
			$args['no_paging']      = true;
			$args['no_found_rows']  = true;
			$args['posts_per_page'] = 1;

			$past_event   = tribe_get_events( $args );
			$has_previous = ( count( $past_event ) >= 1 );
		}

		return apply_filters( 'tribe_has_previous_event', $has_previous );
	}

	/**
	 * Are there any events next (in the future) to the current events in $wp_query
	 *
	 * @return bool
	 */
	function tribe_has_next_event() {
		global $wp_query;

		$has_next  = false;
		$past      = tribe_is_past();
		$upcoming  = ! $past;
		$cur_page  = (int) $wp_query->get( 'paged' );
		$max_pages = (int) $wp_query->max_num_pages;
		$page_1    = 0 === $cur_page || 1 === $cur_page;

		// if we are on page "0" or 1, consider it page 1. Otherwise, consider it the current page. This
		// is used for determining which navigation items to show
		$effective_page = $page_1 ? 1 : $cur_page;

		// Simple tests based on pagination properties
		if ( $upcoming && $effective_page < $max_pages ) $has_next = true;
		if ( $past && $effective_page > 1 ) $has_next = true;

		// Test for future events (on first page of the past events list only)
		if ( $past && $page_1 && ! $has_next ) {
			// Inherit args from the main query so that taxonomy conditions etc are respected
			$args = (array) $wp_query->query;

			// Make some efficiency savings
			$args['no_paging'] = true;
			$args['no_found_rows'] = true;
			$args['posts_per_page'] = 1;

			$next_event = tribe_get_events( $args );
			$has_next   = ( count( $next_event ) >= 1 );
		}

		return apply_filters( 'tribe_has_next_event', $has_next );
	}

	/**
	 * Link to Past Events
	 *
	 * Returns a link to the previous events in list view. Used in the loop view.
	 *
	 * @return string URL
	 */
	function tribe_get_past_link() {
		$tribe_ecp = Tribe__Events__Main::instance();
		$output    = $tribe_ecp->getLink( 'past' );

		return apply_filters( 'tribe_get_past_link', $output );
	}

	/**
	 * Determines if we are in the main Loop (home/archives/tags)
	 *
	 * @return bool
	 */
	function tribe_is_in_main_loop() {
		return apply_filters( 'tribe_is_main_loop', Tribe__Events__Templates::$isMainLoop );
	}

	/**
	 * Determines if we are in list view.
	 *
	 * @return bool
	 */
	function tribe_is_list_view() {
		$is_list_view = 'list' == Tribe__Events__Main::instance()->displaying ? true : false;
		return apply_filters( 'tribe_is_list_view', $is_list_view );
	}

	/**
	 * Used in list loop, displays the date headers between events in the loop when the month / year has changed
	 *
	 **/
	function tribe_events_list_the_date_headers() {

		/* Month and year separators (on every month and year change) */

		$show_headers = apply_filters( 'tribe_events_list_show_date_headers', true );

		$html = '';

		if ( $show_headers ) {

			global $post, $wp_query;

			$event_year        = tribe_get_start_date( $post, false, 'Y' );
			$event_month       = tribe_get_start_date( $post, false, 'm' );
			$month_year_format = tribe_get_date_option( 'monthAndYearFormat', 'F Y' );

			if ( $wp_query->current_post > 0 ) {
				$prev_post = $wp_query->posts[ $wp_query->current_post - 1 ];
				$prev_event_year = tribe_get_start_date( $prev_post, false, 'Y' );
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
		return $view === Tribe__Events__Main::instance()->displaying;
	}
}