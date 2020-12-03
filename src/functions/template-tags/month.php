<?php
/**
 * Calendar Month Functions
 *
 * Display functions (template-tags) for use in WordPress templates.
 */

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( class_exists( 'Tribe__Events__Main' ) ) {

	/**
	 * Display a month
	 *
	 * Inline example:
	 * < code >
	 * <?php
	 * // output the events in May 2016 using the full month view template
	 * tribe_show_month( array( 'eventDate' => '2016-05-01' ) )
	 * ?>
	 * </ code >
	 *
	 * @param array  $args          query args to pass to the month view
	 * @param string $template_path template to use, defaults to the full month view
	 *
	 **/
	function tribe_show_month( $args = [], $template_path = 'month/content' ) {

		// temporarily unset the tribe bar params so they don't apply
		$hold_tribe_bar_args = [];
		foreach ( $_REQUEST as $key => $value ) {
			if ( $value && strpos( $key, 'tribe-bar-' ) === 0 ) {
				$hold_tribe_bar_args[ $key ] = $value;
				unset( $_REQUEST[ $key ] );
			}
		}

		$month_class = new Tribe__Events__Template__Month( $args );
		$month_class->setup_view();

		do_action( 'tribe_events_before_show_month' );

		tribe_get_template_part( $template_path );

		do_action( 'tribe_events_after_show_month' );

		$month_class->shutdown_view();

		// reinstate the tribe bar params
		if ( ! empty( $hold_tribe_bar_args ) ) {
			foreach ( $hold_tribe_bar_args as $key => $value ) {
				$_REQUEST[ $key ] = $value;
			}
		}

		return $month_class->get_events_in_month_ids();
	}

	/**
	 * Month view conditional tag
	 *
	 * Returns true when on the "real" Month View itself, but not in other secondary instances of the
	 * Month View like instance of the [tribe_events] shortcode.
	 *
	 * @return bool
	 */
	function tribe_is_month() {
		$tribe_ecp = Tribe__Events__Main::instance();
		$is_month  = ( 'month' === $tribe_ecp->displaying ) ? true : false;

		/**
		 * Allows filtering of the tribe_is_month boolean value.
		 *
		 * @since 4.6.15 Added inline documentation for this filter.
		 *
		 * @param boolean $is_month Whether you're on the main Month View or not
		 * @param Tribe__Events__Main $tribe_ecp The current Tribe__Events__Main instance.
		 */
		return apply_filters( 'tribe_is_month', $is_month, $tribe_ecp );
	}

	/**
	 * Used in the month loop.
	 * Returns true if there are more calendar days available in the loop.
	 *
	 * Example:
	 * < code >
	 * <?php
	 * // loop through the days in the current month query
	 * while( tribe_events_have_month_days() ) : tribe_events_the_month_day();
	 *        // do stuff
	 * endwhile;
	 * ?>
	 * </ code >
	 *
	 * @return bool
	 * @see Tribe__Events__Template__Month::have_days()
	 **/
	function tribe_events_have_month_days() {
		return Tribe__Events__Template__Month::have_days();
	}

	/**
	 * Used in the month loop.
	 * Advances the loop pointer to the next day, and sets that day up for use.
	 *
	 * Example:
	 * < code >
	 * <?php
	 * // loop through the days in the current month query
	 * while( tribe_events_have_month_days() ) : tribe_events_the_month_day();
	 *        // do stuff
	 * endwhile;
	 * ?>
	 * </ code >
	 *
	 * @see Tribe__Events__Template__Month::the_day()
	 **/
	function tribe_events_the_month_day() {
		Tribe__Events__Template__Month::the_day();
	}

	/**
	 * Used in the month loop.
	 * Returns the counter for the current week in the month loop
	 *
	 * Example:
	 * < code >
	 * <?php
	 * // loop through the days in the current month query
	 * if( tribe_events_get_current_week == 3 );
	 *        // do stuff
	 * endif;
	 * ?>
	 * </ code >
	 *
	 * @return int
	 * @see Tribe__Events__Template__Month::get_current_week()
	 **/
	function tribe_events_get_current_week() {
		return Tribe__Events__Template__Month::get_current_week();
	}

	/**
	 * Used in the month loop.
	 * Gets the current day in the month loop
	 *
	 * Returned array contains the following elements if the day is in the currently displaying month:
	 *  'daynum'       => Day of the month (int)
	 *  'date'         => Complete date (Y-m-d)
	 *  'events'       => Object containing events on this day (WP_Query)
	 *  'total_events' => Number of events on this day (int)
	 *  'view_more'    => Link to the single day (URL)
	 *
	 * If the day is part of the previous or next month, the array simply contains:
	 *    'date' => 'previous' or 'next'
	 *
	 * @return array
	 * @see Tribe__Events__Template__Month::get_current_day()
	 **/
	function tribe_events_get_current_month_day() {
		return apply_filters( 'tribe_events_get_current_month_day', Tribe__Events__Template__Month::get_current_day() );
	}

	/**
	 * Used in the month loop.
	 * Outputs classes for the current month day, including special classes for past / present / future days
	 *
	 * @see Tribe__Events__Template__Month::day_classes()
	 **/
	function tribe_events_the_month_day_classes() {
		echo apply_filters( 'tribe_events_the_month_day_class', Tribe__Events__Template__Month::day_classes() );
	}

	/**
	 * Used in the month loop.
	 * Outputs classes for the current single event in the month loop
	 *
	 * @see Tribe__Events__Template__Month::event_classes()
	 **/
	function tribe_events_the_month_single_event_classes() {
		echo apply_filters( 'tribe_events_the_month_single_event_classes', Tribe__Events__Template__Month::event_classes() );
	}

	/**
	 * Returns whether there are any events in the specific month.
	 * Independently if there are filters or not.
	 *
	 * @return bool
	 * @see Tribe__Events__Template__Month::get_daily_counts()
	 * @since 3.1.1
	 **/
	function tribe_events_month_has_events() {
		return apply_filters( 'tribe_events_month_has_events', false );
	}

	/**
	 * Returns whether there are any events in the month,
	 * with the filtered results.
	 *
	 * @return bool
	 *
	 * @since 4.6.19
	 * @see Tribe__Events__Template__Month::has_events_filtered()
	 **/
	function tribe_events_month_has_events_filtered() {
		$tribe_month = new Tribe__Events__Template__Month();
		$has_events  = $tribe_month->has_events_filtered();

		/**
		 * Filter the result for the check if the month has events after the filters.
		 *
		 * @since 4.6.19
		 */
		return apply_filters( 'tribe_events_month_has_events_filtered', $has_events );
	}


	/**
	 * Drop Menu Post Link
	 *
	 * Returns the URL where the jump menu sends the month/year request.
	 *
	 * @return string URL
	 */
	function tribe_get_dropdown_link_prefix() {
		$tribe_ecp = Tribe__Events__Main::instance();
		$output    = $tribe_ecp->getLink( 'dropdown' );

		return apply_filters( 'tribe_get_dropdown_link_prefix', $output );
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
		$html = '';
		$url  = tribe_get_previous_month_link();
		$date = Tribe__Events__Main::instance()->previousMonth( tribe_get_month_view_date() );
		$earliest_event_date = tribe_events_earliest_date( Tribe__Date_Utils::DBYEARMONTHTIMEFORMAT );

		// Only form the link if a) we have a known earliest event date and b) the previous month date is the same or later
		if ( $earliest_event_date && $date >= $earliest_event_date ) {
			$text = tribe_get_previous_month_text();
			$html = '<a data-month="' . $date . '" href="' . esc_url( $url ) . '" rel="prev"><span>&laquo;</span> ' . $text . ' </a>';
		}

		echo apply_filters( 'tribe_events_the_previous_month_link', $html );
	}

	/**
	 * Display an html link to the next month. Used in the month navigation.
	 *
	 * @uses tribe_get_next_month_text()
	 **/
	function tribe_events_the_next_month_link() {
		$html = '';
		$url  = tribe_get_next_month_link();
		$text = tribe_get_next_month_text();

		// Check if $url is populated (an empty string may indicate the date was out-of-bounds, ie on 32bit servers)
		if ( ! empty( $url ) ) {
			$date = Tribe__Events__Main::instance()->nextMonth( tribe_get_month_view_date() );
			if ( $date <= tribe_events_latest_date( Tribe__Date_Utils::DBYEARMONTHTIMEFORMAT ) ) {
				$html = '<a data-month="' . $date . '" href="' . esc_url( $url ) . '" rel="next">' . $text . ' <span>&raquo;</span></a>';
			}
		}

		echo apply_filters( 'tribe_events_the_next_month_link', $html );
	}

	/**
	 * Link to Previous Month
	 *
	 * Returns a link to the previous month's events page. Used in the month view.
	 *
	 * @return string URL
	 */
	function tribe_get_previous_month_link() {

		if ( ! $wp_query = tribe_get_global_query_object() ) {
			return;
		}

		$term      = null;
		$tribe_ecp = Tribe__Events__Main::instance();
		if ( isset( $wp_query->query_vars[ Tribe__Events__Main::TAXONOMY ] ) ) {
			$term = $wp_query->query_vars[ Tribe__Events__Main::TAXONOMY ];
		}
		$output = $tribe_ecp->getLink( 'month', $tribe_ecp->previousMonth( tribe_get_month_view_date() ), $term );

		return apply_filters( 'tribe_get_previous_month_link', $output );
	}

	/**
	 * Previous Month Text
	 *
	 * Returns a textual description of the previous month
	 *
	 * @return string Name of the previous month.
	 */
	function tribe_get_previous_month_text() {
		$tribe_ecp = Tribe__Events__Main::instance();
		$output    = $tribe_ecp->getDateStringShortened( $tribe_ecp->previousMonth( tribe_get_month_view_date() ) );

		return apply_filters( 'tribe_get_previous_month_text', $output );
	}

	/**
	 * Link to Next Month
	 *
	 * Returns a link to the next month's events page. Used in the month view.
	 *
	 * @return string URL
	 */
	function tribe_get_next_month_link() {

		if ( ! $wp_query = tribe_get_global_query_object() ) {
			return;
		}

		$term      = null;
		$tribe_ecp = Tribe__Events__Main::instance();
		if ( isset( $wp_query->query_vars[ Tribe__Events__Main::TAXONOMY ] ) ) {
			$term = $wp_query->query_vars[ Tribe__Events__Main::TAXONOMY ];
		}
		try {
			$output = $tribe_ecp->getLink( 'month', $tribe_ecp->nextMonth( tribe_get_month_view_date() ), $term );
		} catch ( OverflowException $e ) {
			$output = '';
		}

		return apply_filters( 'tribe_get_next_month_link', $output );
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
		$tribe_ecp = Tribe__Events__Main::instance();
		try {
			$output = $tribe_ecp->getDateStringShortened( $tribe_ecp->nextMonth( tribe_get_month_view_date() ) );
		} catch ( OverflowException $e ) {
			$output = '';
		}

		return apply_filters( 'tribe_get_next_month_text', $output );
	}
}
