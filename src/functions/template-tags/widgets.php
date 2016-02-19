<?php

/**
 * Output data attributes needed to update the month with ajax
 *
 * @return void
 **/
function tribe_events_the_mini_calendar_header_attributes() {

	$args = tribe_events_get_mini_calendar_args();

	if ( is_array( $args['tax_query'] ) ) {
		$args['tax_query'] = json_encode( $args['tax_query'] );
	}

	$html = '';
	$html .= ' data-count="' . esc_attr( $args['count'] ) . '"';
	$html .= ' data-eventDate="' . esc_attr( tribe_get_month_view_date() ) . '"';
	$html .= ' data-tax-query="' . esc_attr( $args['tax_query'] ) . '"';
	$html .= ' data-nonce="' . wp_create_nonce( 'calendar-ajax' ) . '"';

	echo apply_filters( 'tribe_events_the_mini_calendar_header_attributes', $html );
}

/**
 * Output a link for the mini calendar month previous nav, includes data attributes needed to update the month with ajax
 *
 * @return void
 **/
function tribe_events_the_mini_calendar_prev_link() {
	$tribe_ecp = Tribe__Events__Main::instance();
	$args      = tribe_events_get_mini_calendar_args();
	$html      = '<a class="tribe-mini-calendar-nav-link prev-month" href="#" data-month="' . $tribe_ecp->previousMonth( $args['eventDate'] ) . '-01" title="' . tribe_get_previous_month_text() . '"><span>&laquo;</span></a>';
	echo apply_filters( 'tribe_events_the_mini_calendar_prev_link', $html );
}

/**
 * Output a link for the mini calendar month previous nav, includes data attributes needed to update the month with ajax
 *
 * @return void
 **/
function tribe_events_the_mini_calendar_title() {
	$args        = tribe_events_get_mini_calendar_args();
	$date        = strtotime( $args['eventDate'] );
	$date_format = tribe_get_option( 'monthAndYearFormat', 'M Y' );
	$title       = tribe_format_date( $date, false, $date_format );
	echo apply_filters( 'tribe_events_the_mini_calendar_title', $title );
}

/**
 * Output a link for the mini calendar month next nav, includes data attributes needed to update the month with ajax
 *
 * @return void
 **/
function tribe_events_the_mini_calendar_next_link() {
	$tribe_ecp = Tribe__Events__Main::instance();
	$args      = tribe_events_get_mini_calendar_args();
	try {
		$html = '<a class="tribe-mini-calendar-nav-link next-month" href="#" data-month="' . $tribe_ecp->nextMonth( $args['eventDate'] ) . '-01" title="' . tribe_get_next_month_text() . '"><span>&raquo;</span></a>';
	} catch ( OverflowException $e ) {
		$html = '';
	}
	echo apply_filters( 'tribe_events_the_mini_calendar_next_link', $html );
}

/**
 * Output a link for the mini calendar day, includes data attributes needed to update the event list below with ajax
 *
 * @return void
 **/
function tribe_events_the_mini_calendar_day_link() {
	$day  = tribe_events_get_current_month_day();
	$args = tribe_events_get_mini_calendar_args();

	if ( $day['total_events'] > 0 ) {
		// there are events on this day
		if ( $args['count'] > 0 ) {
			// there is an event list under the calendar
			$html = '<a href="#" data-day="' . $day['date'] . '" class="tribe-mini-calendar-day-link">' . $day['daynum'] . '</a>';
		} else {
			// there are no events under the calendar
			if ( tribe_events_is_view_enabled( 'day' ) ) {
				// day view is enabled
				ob_start();
				tribe_the_day_link( $day['date'], $day['daynum'] );
				$html = ob_get_clean();
			} else {
				// day view is disabled, just show that there are events on the day but don't link anywhere
				$html = '<a href="javascript:void(0)">' . $day['daynum'] . '</a>';
			}
		}
	} else {
		$html = '<span class="tribe-mini-calendar-no-event">' . $day['daynum'] . '</span>';
	}

	echo apply_filters( 'tribe_events_the_mini_calendar_day_link', $html );
}

/**
 * Return arguments passed to mini calendar widget
 *
 * @return array
 **/
function tribe_events_get_mini_calendar_args() {
	return apply_filters( 'tribe_events_get_mini_calendar_args', Tribe__Events__Pro__Mini_Calendar::instance()->get_args() );
}

/**
 * Returns 'current_post', the location in the loop, and 'class', which is empty unless it's the
 * first post in loop ("first") or the last ("last").
 *
 * @return array
 **/
function tribe_events_get_widget_event_atts() {

	global $post, $wp_query;

	$class = '';

	if ( 0 == $wp_query->current_post ) {
		$class = ' first ';
	}

	if ( $wp_query->current_post + 1 == $wp_query->post_count ) {
		$class .= ' last ';
	}

	$atts = array(
		'current_post' => $wp_query->current_post,
		'class'        => $class,
	);

	return apply_filters( 'tribe_events_get_widget_event_atts', $atts );
}

/**
 * Returns the event date, or today's date if the event has started and is not over yet.
 *
 * @return int
 **/
function tribe_events_get_widget_event_post_date() {
	global $post, $wp_query;

	if ( class_exists( 'Tribe__Events__Timezones' ) ) {
		$startDate = Tribe__Events__Timezones::event_start_timestamp( $post->ID, null );
		$endDate = Tribe__Events__Timezones::event_end_timestamp( $post->ID, null );
	} else {
		$startDate = strtotime( $post->EventStartDate );
		$endDate   = strtotime( $post->EventEndDate );
	}

	$is_multiday = tribe_event_is_multiday( $post->ID );
	$is_all_day = tribe_event_is_all_day( $post->ID );

	$today     = current_time( 'timestamp' );
	$yesterday = $today - DAY_IN_SECONDS;

	// Gets Yesterday cutoff to check which date we pick
	$yesterday_end = tribe_end_of_day( date( Tribe__Date_Utils::DBDATETIMEFORMAT, $yesterday ), 'U' ) + 1;

	// Check if the yesterday cutoff will get the start date of the event
	if ( $yesterday_end >= $startDate && ! $is_multiday && ! $is_all_day ) {
		$postDate = $yesterday;
	// If the event starts way in the past or ends way in the future, let's show today's date
	} elseif ( $today > $startDate && $today < $endDate ) {
		$postDate = $today;
	} else {
		$postDate = $startDate;
	}

	/* If the user clicked in a particular day, let's show that day as the event date, even if the event spans a few days */
	if ( defined( 'DOING_AJAX' ) && DOING_AJAX && isset( $_POST['action'] ) && $_POST['action'] == 'tribe-mini-cal-day' ) {
		$postDate = strtotime( $_POST['eventDate'] );
	}

	return apply_filters( 'tribe_events_get_widget_event_post_date', $postDate );
}

/**
 * Returns the URL for the list widget's "View All" link.
 *
 * @param array $instance
 *
 * @return string
 **/
function tribe_events_get_list_widget_view_all_link( $instance ) {

	$link_to_all = '';

	if ( empty( $instance['filters'] ) && empty( $instance['raw_filters'] ) ) {
		$link_to_archive = false;
		$link_to_all     = tribe_get_events_link();

		return apply_filters( 'tribe_events_get_list_widget_view_all_link', $link_to_all );
	}

	// Have taxonomy filters been applied?
	if ( empty( $instance['filters'] ) ) {
		$filters = $instance['raw_filters'];
	} else {
		$filters = json_decode( $instance['filters'], true );
	}

	// Is the filter restricted to a single taxonomy?
	$single_taxonomy = ( is_array( $filters ) && 1 === count( $filters ) );
	$single_term     = false;

	// Pull the actual taxonomy and list of terms into scope
	if ( $single_taxonomy ) foreach ( $filters as $taxonomy => $terms );

	// If we have a single taxonomy and a single term, the View All link should point to the relevant archive page
	if ( $single_taxonomy && 1 === count( $terms ) ) {
		$link_to_archive = true;
		$link_to_all     = get_term_link( absint( $terms[0] ), $taxonomy );
	}// Otherwise link to the main events page
	else {
		$link_to_archive = false;
		$link_to_all     = tribe_get_events_link();
	}

	return apply_filters( 'tribe_events_get_list_widget_view_all_link', $link_to_all );
}
/**
 * This Week - Title
 *
 * @param string $this_week_title
 *
 * @return string
 */
function tribe_events_get_this_week_title( $start_date ) {
	$events_label_plural = tribe_get_event_label_plural();
	$date_format = apply_filters( 'tribe_events_pro_page_title_date_format', tribe_get_date_format( true ) );

	$this_week_title = sprintf( __( '%s for week of %s', 'tribe-events-calendar-pro' ),
		$events_label_plural,
		date_i18n( $date_format, strtotime( $start_date ) )
	);

	return $this_week_title;
}
/**
 * This Week Widget - Build the previous week link.
 *
 * @param string $text The text to be linked.
 *
 * @return string
 */
function tribe_events_this_week_previous_link( $start_date, $text = '' ) {

	if ( empty( $text ) ) {
		$text = __( '<span>&laquo;</span> <span class="tribe-nav-label">Previous</span>', 'tribe-events-calendar-pro' );
	}

	$attributes = sprintf( ' data-week="%s" ', date( Tribe__Date_Utils::DBDATEFORMAT, strtotime( $start_date . ' -7 days' ) ) );

	return sprintf( '<a %s href="#" rel="prev">%s</a>', $attributes, $text );

}

/**
 * This Week Widget - Build the next week link
 *
 * @param string $text the text to be linked
 * @param string $start_date the date for start of the week
 *
 * @return string
 */
function tribe_events_this_week_next_link( $start_date, $text = '' ) {

	if ( empty( $text ) ) {
		$text = __( '<span class="tribe-nav-label">Next</span> <span>&raquo;</span>', 'tribe-events-calendar-pro' );
	}

	$attributes = sprintf( ' data-week="%s" ', $start_date );

	return sprintf( '<a %s href="#" rel="next">%s</a>', $attributes, $text );

}

/**
 * This Week Widget - Get the first day of current week with provided date or current week
 *
 * @param null|mixed $date  given date or week # (week # assumes current year)
 * @param null $week_offset # offset from current week the start date for this widget
 *
 * @return DateTime
 */
function tribe_get_this_week_first_week_day( $date = null, $week_offset = null ) {

	//Start of Week in WordPress Settings - Zero = Sunday, 1 = Monday, etc
	$offset = 7 - get_option( 'start_of_week', 0 );

	//Check for Valid Date
	try {
		$date = new DateTime( $date );
	} catch ( exception $e ) {
		$date = new DateTime();
	}

	// Clone to avoid altering the original date
	$r = clone $date;
	//Use Offset to get Start of Week for Widget
	$r->modify( - ( ( $date->format( 'w' ) + $offset ) % 7 ) . 'days' );

	//Add Week Offset if there
	empty( $week_offset ) ? null : $r->modify( '+' . absint( $week_offset ) . ' weeks' );

	return $r->format( Tribe__Date_Utils::DBDATEFORMAT );
}

/**
 * This Week Widget - Get the last day of the week from a provided date
 *
 * @param string|int $date a given date of the week
 *
 * @return DateTime
 */
function tribe_get_this_week_last_week_day( $date ) {
	return date( Tribe__Date_Utils::DBDATEFORMAT, strtotime( tribe_get_this_week_first_week_day( $date ) . ' +7 days' ) );
}

/**
 * This Week Widget - Set class if today or in past for Day Grid
 *
 * @return string
 */
function tribe_get_this_week_day_class( $day ) {
	if ( $day['is_today'] ) {
		$class = 'this-week-today';

		return $class;

	} elseif ( $day['is_past'] ) {
		$class = 'this-week-past';

		return $class;
	}

	return null;
}
/**
 * This Week Widget - Get Event Category Names Selected for Individual Widget
 *
 * @return string
 */
function tribe_this_week_widget_class( $tax_query ) {
	if ( empty( $tax_query ) ) {
		return null;
	}

	$tax_query_class = null;

	foreach ( $tax_query as $tax => $terms ) {
		if ( empty( $terms ) ) {
			continue;
		}

		foreach ( $terms['terms'] as $term_id ) {

			$term = get_term( $term_id, $terms['taxonomy'] );

			$tax_query_class .= $term->slug . ' ';
		}
	}

	//Filter Classes Added to the This Week Widget Wrap
	$tax_query_class = apply_filters( 'tribe_this_week_widget_class', $tax_query_class );

	return $tax_query_class;
}
