<?php
/**
 * Loop Functions
 *
 * Display functions (template-tags) for use in WordPress templates.
 */

use Tribe\Events\Views\V2\Views\Day_View;
use Tribe\Events\Views\V2\Views\List_View;
use Tribe\Events\Views\V2\Views\Month_View;

/**
 * Past Loop View Test
 *
 * Returns true if the query is set for past events, false otherwise
 *
 * @return bool
 */
function tribe_is_past(): bool {

	if ( ! $wp_query = tribe_get_global_query_object() ) {
		return false;
	}

	$is_past = ! empty( $wp_query->tribe_is_past ) && ! tribe_is_showing_all() ? $wp_query->tribe_is_past : false;

	/**
	 * Allows for customization of the result of whether the current view shows past events.
	 *
	 * @param bool $is_past Whether the current view shows upcoming past or not.
	 */
	return (bool) apply_filters( 'tribe_is_past', $is_past );
}

/**
 * Upcoming Loop View Test
 *
 * Returns true if the query is set for upcoming events, false otherwise
 *
 * @return bool
 */
function tribe_is_upcoming(): bool {

	if ( ! $wp_query = tribe_get_global_query_object() ) {
		return false;
	}

	$is_upcoming = ( tribe_is_list_view() && ! tribe_is_past() ) ? true : false;

	/**
	 * Allows for customization of the result of whether the current view shows upcoming events.
	 *
	 * @param bool $is_upcoming Whether the current view shows upcoming events or not.
	 */
	return (bool) apply_filters( 'tribe_is_upcoming', $is_upcoming );
}

/**
 * Show All Test
 *
 * Returns true if the query is set to show all events, false otherwise
 *
 * @todo  move to ECP
 * @since 6.0.0 Using tribe context.
 *
 * @return bool
 */
function tribe_is_showing_all() : bool {
	$is_all_view = 'all' === tribe_context()->get( 'view' );

	/**
	 * Allows for customization of the result of whether the current view shows all events.
	 *
	 * @since 6.0.0 Using tribe context.
	 *
	 * @param bool $is_all_view Whether the current view shows all events or not.
	 */
	return (bool) apply_filters( 'tribe_is_showing_all', $is_all_view );
}

/**
 * Date View Test
 *
 * Check if current display is a date-based View.
 *
 * @return bool
 */
function tribe_is_by_date(): bool {
	$by_date_views = [
		Month_View::get_view_slug(),
		Day_View::get_view_slug(),
	];

	$context = tribe_context();
	$context_view = $context->get( 'view' );

	$tribe_is_by_date = in_array(
		$context_view,
		$by_date_views
	);

	/**
	 * Allows for customization of the result of whether or not a "by date" view is being viewed.
	 * Typically Month and Day Views.
	 * Events PRO hooks in here to indicate that Week View is a by-date-view at 10.
	 *
	 * @param bool           $tribe_is_by_date Whether a "by date" calendar view is currently being displayed.
	 * @param Tribe__Context $context          The global context object.
	 */
	return (bool) apply_filters( 'tribe_is_by_date', (bool) $tribe_is_by_date, $context );
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
	 * @param bool   $depth Whether to include the linked title or not.
	 */
	echo apply_filters( 'tribe_events_title', tribe_get_events_title( $depth ), $depth );
}

/**
 * Event Title
 *
 * Return an event's title with pseudo-breadcrumb if on a category
 *
 * @todo move logic to template classes
 *
 * @param bool $depth include linked title
 *
 * @return string title
 */
function tribe_get_events_title( $depth = true ) {
	$context = tribe_context();

	if ( ! $wp_query = tribe_get_global_query_object() ) {
		return;
	}

	$events_label_plural = tribe_get_event_label_plural();

	if ( $wp_query->get( 'featured' ) ) {
		$events_label_plural = sprintf( _x( 'Featured %s', 'featured events title', 'the-events-calendar' ), $events_label_plural );
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
		$cat   = get_queried_object();
		$title = '<a href="' . esc_url( tribe_get_events_link() ) . '">' . $title . '</a>';
		$title .= ' &#8250; ' . $cat->name;
	}

	/**
	 * Allows for customization of the "Events" page title.
	 *
	 * @param string         $title   The "Events" page title as it's been generated thus far.
	 * @param bool           $depth   Whether to include the linked title or not.
	 * @param Tribe__Context $context The context used to build the title, it could be the global one
	 */
	return apply_filters( 'tribe_get_events_title', $title, $depth, $context );
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
	$wp_query     = tribe_get_global_query_object();
	$has_previous = false;

	if ( null === $wp_query ) {
		return apply_filters( 'tribe_has_previous_event', $has_previous );
	}

	$past      = tribe_is_past();
	$upcoming  = ! $past;
	$cur_page  = (int) $wp_query->get( 'paged' );
	$max_pages = (int) $wp_query->max_num_pages;
	$page_1    = 0 === $cur_page || 1 === $cur_page;

	// if we are on page "0" or 1, consider it page 1. Otherwise, consider it the current page. This
	// is used for determining which navigation items to show
	$effective_page = $page_1 ? 1 : $cur_page;

	// Simple tests based on pagination properties
	if ( $upcoming && $effective_page > 1 ) {
		$has_previous = true;
	}
	if ( $past && $effective_page < $max_pages ) {
		$has_previous = true;
	}

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

		/**
		 * Filters the arguments that will be used to check if there is a previous page/event.
		 *
		 * @since 4.9
		 *
		 * @param array    $args     An array of arguments that will be used to check if a previous page/event
		 *                           is present.
		 * @param WP_Query $wp_query The query object, if any, the query arguments have been taken from.
		 */
		$args = apply_filters( 'tribe_events_has_previous_args', $args, $wp_query );

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

	$wp_query = tribe_get_global_query_object();
	$has_next = false;

	if ( null === $wp_query ) {
		return apply_filters( 'tribe_has_next_event', $has_next );
	}

	$past      = tribe_is_past();
	$upcoming  = ! $past;
	$cur_page  = (int) $wp_query->get( 'paged' );
	$max_pages = (int) $wp_query->max_num_pages;
	$page_1    = 0 === $cur_page || 1 === $cur_page;

	// if we are on page "0" or 1, consider it page 1. Otherwise, consider it the current page. This
	// is used for determining which navigation items to show
	$effective_page = $page_1 ? 1 : $cur_page;

	// Simple tests based on pagination properties
	if ( $upcoming && $effective_page < $max_pages ) {
		$has_next = true;
	}
	if ( $past && $effective_page > 1 ) {
		$has_next = true;
	}

	// Test for future events (on first page of the past events list only)
	if ( $past && $page_1 && ! $has_next ) {
		// Inherit args from the main query so that taxonomy conditions etc are respected
		$args = (array) $wp_query->query;

		// Make some efficiency savings
		$args['no_paging']      = true;
		$args['no_found_rows']  = true;
		$args['posts_per_page'] = 1;

		/**
		 * Filters the arguments that will be used to check if there is a next page/event.
		 *
		 * @since 4.9
		 *
		 * @param array    $args     An array of arguments that will be used to check if a next page/event
		 *                           is present.
		 * @param WP_Query $wp_query The query object the query arguments have been taken from.
		 */
		$args = apply_filters( 'tribe_events_has_next_args', $args, $wp_query );

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
function tribe_is_in_main_loop(): bool {
	return (bool) apply_filters( 'tribe_is_main_loop', false );
}

/**
 * Determines if we are in list view.
 *
 * @return bool
 */
function tribe_is_list_view(): bool {
	$context  = tribe_context();
	$is_list_view = tec_is_view( List_View::get_view_slug() );

	/**
	 * Allows filtering of the tribe_is_list_view boolean value.
	 *
	 * @since 6.0.7 Added context to parameters.
	 *
	 * @param bool           $is_list_view If we're on the List View
	 * @param Tribe__Context $context      The current context
	 */
	return (bool) apply_filters( 'tribe_is_list_view', $is_list_view, $context );
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
			$prev_post        = $wp_query->posts[ $wp_query->current_post - 1 ];
			$prev_event_year  = tribe_get_start_date( $prev_post, false, 'Y' );
			$prev_event_month = tribe_get_start_date( $prev_post, false, 'm' );
		}


		/*
		 * If the event month changed since the last event in the loop,
		 * or is the same month but the year changed.
		 *
		 */
		if ( $wp_query->current_post === 0 || ( $prev_event_month != $event_month || ( $prev_event_month == $event_month && $prev_event_year != $event_year ) ) ) {
			$html .= sprintf( "<h2 class='tribe-events-list-separator-month'><span>%s</span></h2>", tribe_get_start_date( $post, false, $month_year_format ) );
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

	$classes     = [];
	$tribe_paged = absint( ! empty( $_REQUEST['tribe_paged'] ) ) ? $_REQUEST['tribe_paged'] : 1;

	$classes['direction'] = 'tribe-events-nav-previous';
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

	$classes     = [];
	$tribe_paged = absint( ! empty( $_REQUEST['tribe_paged'] ) ) ? $_REQUEST['tribe_paged'] : 1;

	$classes['direction'] = 'tribe-events-nav-next';
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
 * @deprecated 6.0.7
 *
 * @return bool
 **/
function tribe_is_view( $view = false ) {
	_deprecated_function( __METHOD__, '6.0.7', 'tec_is_view');

	return tec_is_view( $view );
}

/**
 * Check whether we're on a particular view.
 *
 * @since 6.0.7
 *
 * @param  string $view_slug      (optional) The view slug we are looking for. Defaults to checking if we are on the default view.
 * @param Tribe__Context $context (optional) The view context. Generated via tribe_context() if not supplied.
 *
 * @return boolean Whether we're on the requested view.
 */
function tec_is_view( string $view_slug = 'default', $context = null ): bool {
	$is_view   = false;
	$view_slug = strtolower( $view_slug );

	// In case we somehow get passed a non-string.
	if ( ! is_string( $view_slug ) ) {
		$view_slug = 'default';
	}

	if ( ! $context instanceof Tribe__Context ) {
		$context = tribe_context();
	}

	if ( $context->is( 'tec_post_type' ) ) {
		$current_view = $context->get( 'view', 'default' );

		$is_view = ( $view_slug === $current_view );

		// If we get 'default' but are testing for 'month',
		// make sure we return true when the default view is 'month'.
		if ( 'default' !== $view_slug && 'default' === $current_view ) {
			$is_view = ( $view_slug === tribe_get_option( 'viewOption' ) );
		}
	}

	/**
	 * Allows generic filtering of the tec_is_view boolean value.
	 *
	 * @since 6.0.7
	 *
	 * @param boolean        $is_view   Whether you're on the View or not
	 * @param string         $view_slug The view slug.
	 * @param Tribe__Context $context   The global context object.
	 */
	$is_view = apply_filters( 'tec_is_view', $is_view, $view_slug, $context );

	/**
	 * Allows view-specific filtering of the tec_is_view boolean value.
	 *
	 * @since 6.0.7
	 *
	 * @param boolean        $is_view Whether you're on the View or not
	 * @param Tribe__Context $context The global context object.
	 */
	return (bool) apply_filters( "tec_is_{$view_slug}_view", $is_view, $context );
}
