<?php
/**
 * Calendar Month Functions
 *
 * Display functions (template-tags) for use in WordPress templates.
 */

/**
 * Month view conditional tag
 *
 * Returns true when on the "real" Month View itself, but not in other secondary instances of the
 * Month View like instance of the [tribe_events] shortcode.
 *
 * @return bool
 */
function tribe_is_month() {
	$context  = tribe_context();
	$is_month = tec_is_view( \Tribe\Events\Views\V2\Views\Month_View::get_view_slug() );

	/**
	 * Allows filtering of the tribe_is_month boolean value.
	 *
	 * @since 4.6.15 Added inline documentation for this filter.
	 *
	 * @param boolean $is_month Whether you're on the main Month View or not
	 * @param Tribe__Context The global context object.
	 */
	return apply_filters( 'tribe_is_month', $is_month, $context );
}

/**
 * Returns whether there are any events in the specific month.
 * Independently if there are filters or not.
 *
 * @return bool
 * @since 3.1.1
 **/
function tribe_events_month_has_events() {
	return apply_filters( 'tribe_events_month_has_events', false );
}

/**
 * Month View Date
 *
 * Get current calendar month view date
 *
 * @return string Date currently queried
 */
function tribe_get_month_view_date() {

	if ( ! $wp_query = tribe_get_global_query_object() ) {
		return;
	}

	$today = date_i18n( Tribe__Date_Utils::DBDATEFORMAT, strtotime( date( 'Y-m-01', current_time( 'timestamp' ) ) ) );
	$date  = $today;

	if ( ! empty( $_REQUEST['tribe-bar-date'] ) ) {
		$date = $_REQUEST['tribe-bar-date'] . '-01';
	} else {
		if ( isset( $_REQUEST['eventDate'] ) && $_REQUEST['eventDate'] ) {
			$date = $_REQUEST['eventDate'] . '-01';
		} else {
			if ( ! empty( $wp_query->query_vars['eventDate'] ) ) {
				$date = $wp_query->query_vars['eventDate'] . '-01';
			}
		}
	}

	// Confirm the date is valid (who knows what was passed in through $_REQUEST) and revert to today if necessary
	try {
		new DateTime( $date );
	}
	catch ( Exception $e ) {
		$date = $today;
	}

	return apply_filters( 'tribe_get_month_view_date', $date );
}

/**
 * Display an html link to the previous month. Used in the month navigation.
 *
 * No link will be returned if the link is to a month that precedes any existing
 * events.
 *
 * @uses tribe_get_previous_month_text()
 **/
function tribe_events_the_previous_month_link() {
	// $html = '<a data-month="' . $date . '" href="' . esc_url( $url ) . '" rel="prev"><span>&laquo;</span> ' . $text . ' </a>';
	echo apply_filters( 'tribe_events_the_previous_month_link', null );
}

/**
 * Display an html link to the next month. Used in the month navigation.
 *
 * @uses tribe_get_next_month_text()
 **/
function tribe_events_the_next_month_link() {

	// $html = '<a data-month="' . $date . '" href="' . esc_url( $url ) . '" rel="next">' . $text . ' <span>&raquo;</span></a>';

	echo apply_filters( 'tribe_events_the_next_month_link', null );
}

/**
 * Link to Previous Month
 *
 * Returns a link to the previous month's events page. Used in the month view.
 *
 * @return string URL
 */
function tribe_get_previous_month_link() {
	return apply_filters( 'tribe_get_previous_month_link', null );
}

/**
 * Previous Month Text
 *
 * Returns a textual description of the previous month
 *
 * @return string Name of the previous month.
 */
function tribe_get_previous_month_text() {
	return apply_filters( 'tribe_get_previous_month_text', null );
}

/**
 * Link to Next Month
 *
 * Returns a link to the next month's events page. Used in the month view.
 *
 * @return string URL
 */
function tribe_get_next_month_link() {
	return apply_filters( 'tribe_get_next_month_link', null );
}

/**
 * Current Month Text
 *
 * Returns a textual description of the current month
 *
 * @return string Name of the current month.
 */
function tribe_get_current_month_text() {
	$output = date( 'F', strtotime( tribe_get_month_view_date() ) );

	return apply_filters( 'tribe_get_current_month_text', $output );
}

/**
 * Next Month Text
 *
 * Returns a textual description of the next month
 *
 * @return string Name of the next month.
 */
function tribe_get_next_month_text() {

	return apply_filters( 'tribe_get_next_month_text', null );
}
