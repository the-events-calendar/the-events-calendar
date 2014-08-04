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

if ( class_exists( 'TribeEvents' ) ) {

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
	 * @return void
	 **/
	function tribe_show_month( $args = array(), $template_path = 'month/content' ) {

		// temporarily unset the tribe bar params so they don't apply
		$hold_tribe_bar_args = array();
		foreach ( $_REQUEST as $key => $value ) {
			if ( $value && strpos( $key, 'tribe-bar-' ) === 0 ) {
				$hold_tribe_bar_args[$key] = $value;
				unset( $_REQUEST[$key] );
			}
		}

		$month_class = new Tribe_Events_Month_Template( $args );
		$month_class->setup_view();

		do_action( 'tribe_events_before_show_month' );

		tribe_get_template_part( $template_path );

		do_action( 'tribe_events_after_show_month' );

		$month_class->shutdown_view();

		// reinstate the tribe bar params
		if ( ! empty( $hold_tribe_bar_args ) ) {
			foreach ( $hold_tribe_bar_args as $key => $value ) {
				$_REQUEST[$key] = $value;
			}
		}
	}

	/**
	 * Month view conditional tag
	 *
	 * Returns true if the current view is Month
	 *
	 * @return bool
	 */
	function tribe_is_month() {
		$tribe_ecp = TribeEvents::instance();
		$output    = ( $tribe_ecp->displaying == 'month' ) ? true : false;

		return apply_filters( 'tribe_is_month', $output );
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
	 * @see Tribe_Events_Month_Template::have_days()
	 **/
	function tribe_events_have_month_days() {
		return Tribe_Events_Month_Template::have_days();
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
	 * @return void
	 * @see Tribe_Events_Month_Template::the_day()
	 **/
	function tribe_events_the_month_day() {
		Tribe_Events_Month_Template::the_day();
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
	 * @see Tribe_Events_Month_Template::get_current_week()
	 **/
	function tribe_events_get_current_week() {
		return Tribe_Events_Month_Template::get_current_week();
	}

	/**
	 * Used in the month loop.
	 * Gets the current day in the month loop
	 *
	 * Returned array contains the following elements if the day is in the currently displaying month:
	 *    'daynum'       => Day of the month (int)
	 *  'date'         => Complete date (Y-m-d)
	 *  'events'       => Object containing events on this day (WP_Query)
	 *  'total_events' => Number of events on this day (int)
	 *  'view_more'    => Link to the single day (URL)
	 *
	 * If the day is part of the previous or next month, the array simply contains:
	 *    'date' => 'previous' or 'next'
	 *
	 * @return array
	 * @see Tribe_Events_Month_Template::get_current_day()
	 **/
	function tribe_events_get_current_month_day() {
		return apply_filters( 'tribe_events_get_current_month_day', Tribe_Events_Month_Template::get_current_day() );
	}

	/**
	 * Used in the month loop.
	 * Outputs classes for the current month day, including special classes for past / present / future days
	 *
	 * @return void
	 * @see Tribe_Events_Month_Template::day_classes()
	 **/
	function tribe_events_the_month_day_classes() {
		echo apply_filters( 'tribe_events_the_month_day_class', Tribe_Events_Month_Template::day_classes() );
	}

	/**
	 * Used in the month loop.
	 * Outputs classes for the current single event in the month loop
	 *
	 * @return void
	 * @see Tribe_Events_Month_Template::event_classes()
	 **/
	function tribe_events_the_month_single_event_classes() {
		echo apply_filters( 'tribe_events_the_month_single_event_classes', Tribe_Events_Month_Template::event_classes() );
	}

	/**
	 * Returns whether there are any events in the month
	 *
	 * @return bool
	 * @see Tribe_Events_Month_Template::get_daily_counts()
	 **/
	function tribe_events_month_has_events() {
		return apply_filters( 'tribe_events_month_has_events', false );
	}


	/**
	 * Drop Menu Post Link
	 *
	 * Returns the URL where the jump menu sends the month/year request.
	 *
	 * @return string URL
	 */
	function tribe_get_dropdown_link_prefix() {
		$tribe_ecp = TribeEvents::instance();
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
		global $wp_query;

		$date = date_i18n( TribeDateUtils::DBDATEFORMAT, strtotime( date( 'Y-m-01', current_time( 'timestamp' ) ) ) );
		if ( ! empty( $_REQUEST['tribe-bar-date'] ) ) {
			$date = $_REQUEST["tribe-bar-date"] . '-01';
		} else {
			if ( isset( $_REQUEST["eventDate"] ) && $_REQUEST["eventDate"] ) {
				$date = $_REQUEST["eventDate"] . '-01';
			} else {
				if ( ! empty( $wp_query->query_vars['eventDate'] ) ) {
					$date = $wp_query->query_vars['eventDate'] . '-01';
				}
			}
		}

		return apply_filters( 'tribe_get_month_view_date', $date );
	}

	/**
	 * Display an html link to the previous month. Used in the month navigation.
	 *
	 * No link will be returned if the link is to a month that precedes any existing
	 * events.
	 *
	 * @return void
	 * @uses tribe_get_previous_month_text()
	 **/
	function tribe_events_the_previous_month_link() {
		$html = '';
		$url  = tribe_get_previous_month_link();
		$date = TribeEvents::instance()->previousMonth( tribe_get_month_view_date() );

		if ( $date >= tribe_events_earliest_date( TribeDateUtils::DBYEARMONTHTIMEFORMAT ) ) {
			$text = tribe_get_previous_month_text();
			$html = '<a data-month="' . $date . '" href="' . $url . '" rel="prev"><span>&laquo;</span> ' . $text . ' </a>';
		}

		echo apply_filters( 'tribe_events_the_previous_month_link', $html );
	}

	/**
	 * Display an html link to the next month. Used in the month navigation.
	 *
	 * @return void
	 * @uses tribe_get_next_month_text()
	 **/
	function tribe_events_the_next_month_link() {
		$html = '';
		$url  = tribe_get_next_month_link();
		$text = tribe_get_next_month_text();

		// Check if $url is populated (an empty string may indicate the date was out-of-bounds, ie on 32bit servers)
		if ( ! empty( $url ) ) {
			$date = TribeEvents::instance()->nextMonth( tribe_get_month_view_date() );
			if ( $date <= tribe_events_latest_date( TribeDateUtils::DBYEARMONTHTIMEFORMAT ) ) {
				$html = '<a data-month="' . $date . '" href="' . $url . '" rel="next">' . $text . ' <span>&raquo;</span></a>';
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
		global $wp_query;
		$term      = null;
		$tribe_ecp = TribeEvents::instance();
		if ( isset( $wp_query->query_vars[TribeEvents::TAXONOMY] ) ) {
			$term = $wp_query->query_vars[TribeEvents::TAXONOMY];
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
		$tribe_ecp = TribeEvents::instance();
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
		global $wp_query;
		$term      = null;
		$tribe_ecp = TribeEvents::instance();
		if ( isset( $wp_query->query_vars[TribeEvents::TAXONOMY] ) ) {
			$term = $wp_query->query_vars[TribeEvents::TAXONOMY];
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
		$tribe_ecp = TribeEvents::instance();
		try {
			$output = $tribe_ecp->getDateStringShortened( $tribe_ecp->nextMonth( tribe_get_month_view_date() ) );
		} catch ( OverflowException $e ) {
			$output = '';
		}

		return apply_filters( 'tribe_get_next_month_text', $output );
	}
}
?>
