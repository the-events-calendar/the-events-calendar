<?php 

/**
 * Output data attributes needed to update the month with ajax
 *
 * @return void
 * @since 3.0
 * @author Jessica Yazbek
 **/
function tribe_events_the_mini_calendar_header_attributes () {

	$args = tribe_events_get_mini_calendar_args();

	if (is_array($args['tax_query'])) {
		$args['tax_query'] = json_encode($args['tax_query']);
	}

	$html = '';
	$html .= ' data-count="' . esc_attr( $args['count'] ) . '"';
	$html .= ' data-eventDate="' . tribe_get_month_view_date() . '"';
	$html .= ' data-tax-query="' . esc_attr( $args['tax_query'] ) . '"';
	$html .= ' data-nonce="' . wp_create_nonce( 'calendar-ajax' ) . '"';

	echo apply_filters( 'tribe_events_the_mini_calendar_header_attributes', $html );
}

/**
 * Output a link for the mini calendar month previous nav, includes data attributes needed to update the month with ajax
 *
 * @return void
 * @since 3.0
 * @author Jessica Yazbek
 **/
function tribe_events_the_mini_calendar_prev_link() {
	$tribe_ecp = TribeEvents::instance();
	$args = tribe_events_get_mini_calendar_args();
	$html = '<a class="tribe-mini-calendar-nav-link prev-month" href="#" data-month="'.$tribe_ecp->previousMonth( $args['eventDate'] ).'-01" title="'.tribe_get_previous_month_text().'"><span>&laquo;</span></a>';
	echo apply_filters( 'tribe_events_the_mini_calendar_prev_link', $html );
}

/**
 * Output a link for the mini calendar month previous nav, includes data attributes needed to update the month with ajax
 *
 * @return void
 * @since 3.0
 * @author Jessica Yazbek
 **/
function tribe_events_the_mini_calendar_title() {
	$tribe_ecp = TribeEvents::instance();
	$args = tribe_events_get_mini_calendar_args();
	$date = strtotime($args['eventDate']);
	$title = $tribe_ecp->monthsShort[date( 'M', $date )] . date( ' Y', $date );
	echo apply_filters( 'tribe_events_the_mini_calendar_title', $title );
}

/**
 * Output a link for the mini calendar month next nav, includes data attributes needed to update the month with ajax
 *
 * @return void
 * @since 3.0
 * @author Jessica Yazbek
 **/
function tribe_events_the_mini_calendar_next_link() {
	$tribe_ecp = TribeEvents::instance();
	$args = tribe_events_get_mini_calendar_args();
	try {
		$html = '<a class="tribe-mini-calendar-nav-link next-month" href="#" data-month="'.$tribe_ecp->nextMonth( $args['eventDate'] ).'-01" title="'.tribe_get_next_month_text().'"><span>&raquo;</span></a>';
	} catch ( OverflowException $e ) {
		$html = '';
	}
	echo apply_filters( 'tribe_events_the_mini_calendar_prev_link', $html );
}

/**
 * Output a link for the mini calendar day, includes data attributes needed to update the event list below with ajax
 *
 * @return void
 * @since 3.0
 * @author Jessica Yazbek
 **/
function tribe_events_the_mini_calendar_day_link() {
	$day = tribe_events_get_current_month_day();
	$args = tribe_events_get_mini_calendar_args();

	if ( $day['total_events'] > 0 ) {
		// there are events on this day
		if ( $args['count']  > 0 ) {
			// there is an event list under the calendar
			$html = '<a href="#" data-day="'.$day['date'].'" class="tribe-mini-calendar-day-link">'.$day['daynum'].'</a>';
		} else {
			// there are no events under the calendar
			if ( tribe_events_is_view_enabled( 'day' ) ) {
				// day view is enabled
				ob_start();
				tribe_the_day_link($day['date'], $day['daynum']);
				$html = ob_get_clean();
			} else {
				// day view is disabled, just show that there are events on the day but don't link anywhere
				$html = '<a href="javascript:void(0)">'.$day['daynum'].'</a>';
			}
		}
	} else {
		$html = '<span class="tribe-mini-calendar-no-event">'.$day['daynum'].'</span>';
	}

	echo apply_filters( 'tribe_events_the_mini_calendar_day_link', $html );
}

/**
 * Return arguments passed to mini calendar widget
 *
 * @return array
 * @author Jessica Yazbek
 **/
function tribe_events_get_mini_calendar_args() {
	return apply_filters( 'tribe_events_get_mini_calendar_args', TribeEventsMiniCalendar::instance()->get_args() );
}

/**
 * Return arguments passed to advanced list widget
 *
 * @return array
 * @author Jessica Yazbek
 **/
function tribe_events_get_adv_list_widget_args() {
	return apply_filters( 'tribe_events_get_adv_list_widget_args', TribeEventsAdvancedListWidget::$params );
}
