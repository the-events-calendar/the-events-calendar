<?php
if ( !defined( 'ABSPATH' ) ) {
	die( '-1' );
}
global $wp_query;
$tribe_ecp = TribeEvents::instance();

list( $year, $month ) = explode( '-', $args['month'] );
$hide_upcoming_ids    = TribeEventsQuery::getHideFromUpcomingEvents();
$daysInMonth          = isset( $date ) ? date( 't', $date ) : date( 't' );
$startOfWeek          = get_option( 'start_of_week', 0 );
$date                 = mktime( 12, 0, 0, $month, 1, $year ); // 1st day of month as unix stamp
$rawOffset            = date( 'w', $date ) - $startOfWeek;
$offset               = ( $rawOffset < 0 ) ? $rawOffset + 7 : $rawOffset; // month begins on day x
$rows                 = 1;

echo '<table class="tribe-mini-calendar">';
echo '<thead class="tribe-mini-calendar-nav"><td colspan="7"><div>';
echo '<a class="tribe-mini-calendar-nav-link prev-month" href="#" data-month="' . $tribe_ecp->previousMonth( $args['month'] ) . '" title="' . tribe_get_previous_month_text() . '"><span><</span></a>';
echo '<span id="tribe-mini-calendar-month">' . $tribe_ecp->monthsShort[date( 'M', $date )] . date( ' Y', $date ) . '</span>';
echo '<a class="tribe-mini-calendar-nav-link next-month" href="#" data-month="' . $tribe_ecp->nextMonth( $args['month'] ) . '" title="' . tribe_get_next_month_text() . '"><span>></span></a>';
echo '<img id="ajax-loading-mini" src="' . esc_url( admin_url( 'images/wpspin_light.gif' ) ) . '" alt="loading..." />';

echo '<input type="hidden" name="tribe-mini-calendar-layout" id="tribe-mini-calendar-layout" value="' . esc_attr( $args['layout'] ) . '">';
echo '<input type="hidden" name="tribe-mini-calendar-count" id="tribe-mini-calendar-count" value="' . esc_attr( $args['count'] ) . '">';
echo '<input type="hidden" name="tribe-mini-calendar-month-name" id="tribe-mini-calendar-month-name" value="' . esc_attr( $tribe_ecp->monthsShort[date( 'M', $date )] ) . '">';
echo "<input type='hidden' name='tribe-mini-calendar-tax-query' id='tribe-mini-calendar-tax-query' value='" .  maybe_serialize( $args['tax_query'] ) . "'>";
echo "<input type='hidden' name='tribe-mini-calendar-nonce' id='tribe-mini-calendar-nonce' value='" .  wp_create_nonce( 'calendar-ajax' ) . "'>";

echo '</div></td></thead>';

echo '<tbody class="hfeed vcalendar"><tr>';

// Skip last month
for ( $i = 1; $i <= $offset; $i++ ) {
	echo '<td class="othermonth"></td>';
}
// Output this month
for ( $day = 1; $day <= $daysInMonth; $day++ ) {

	if ( ( $day + $offset - 1 ) % 7 == 0 && $day != 1 ) {
		echo "</tr>\n\t<tr>";
		$rows++;
	}

	// Var'ng up days, months and years
	$current_day   = date_i18n( 'd' );
	$current_month = date_i18n( 'm' );
	$current_year  = date_i18n( 'Y' );

	$date = date( 'Y-m-d', strtotime("$year-$month-$day"));

	$query_args = array( 'eventDate'      => $date,
	               'start_date'     => tribe_event_beginning_of_day( $date ),
	               'end_date'       => tribe_event_end_of_day( $date ),
	               'post__not_in'   => $hide_upcoming_ids,
	               'hide_upcoming'  => false,
	               'posts_per_page' => '1',
	               'orderby'        => 'event_date',
	               'order'          => 'ASC',
	               'eventDisplay'   => 'custom',
	               'no_found_rows'  => true
	);

	if ( !empty( $args['tax_query'] ) ) {
		$query_args['tax_query'] = $args['tax_query'];
	}

	$daily_events = TribeEventsQuery::getEvents( $query_args, true );

	$events_today = ( !empty( $daily_events->posts ) );

	$ppf = "";
	if ( ($current_day == $day) && ($current_month == $month) && ($current_year == $year) ) {
		$ppf = ' today ';
	}
	if ( $events_today ) {
		$ppf .= ' has-events ';
	}

	echo "<td class=\"thismonth" . $ppf . "\">";
	echo '<div id="daynum-' . $day . '">';

	echo ( $events_today ) ? '<a href="#" data-day="' . $year . '-' . $month . '-' . $day . '" class="tribe-mini-calendar-day-link">' . $day . '</a>' : $day;

	echo '</div>';
	echo "</td>";

	unset( $daily_events );

}
// Skip next month
while ( ( $day + $offset ) <= $rows * 7 ) {
	echo '<td class="tribe-events-othermonth"></td>';
	$day++;
}
echo '</tr></tbody>';
echo '</table>';
