<?php
/**
 * Calendar Widget Template
 * The template view for the calendar widget. 
 *
 * This view contains the filters required to create an effective calendar widget view.
 * 
 * You can recreate an ENTIRELY new calendar widget view by doing a template override,
 * and placing a calendar-widget.php file in a tribe-events/widgets/ directory 
 * within your theme directory, which will override the /views/widgets/calendar-widget.php.
 *
 * You can use any or all filters included in this file or create your own filters in 
 * your functions.php. In order to modify or extend a single filter, please see our
 * readme on templates hooks and filters (TO-DO)
 *
 * @package TribeEventsCalendar
 * @since  2.1
 * @author Modern Tribe Inc.
 *
 */

if ( !defined('ABSPATH') ) { die('-1'); }

$tribe_ecp = TribeEvents::instance();

global $wp_query;
$old_date = null;
if ( !defined( "DOING_AJAX" ) || !DOING_AJAX ) {
	$current_date = date_i18n( TribeDateUtils::DBYEARMONTHTIMEFORMAT ) . "-01";
	if ( isset( $wp_query->query_vars['eventDate'] ) ) {
		$old_date                          = $wp_query->query_vars['eventDate'];
		$wp_query->query_vars['eventDate'] = $current_date;
	}
} else {
	$current_date = $tribe_ecp->date;
}

if ( !$current_date ) {
	$current_date = $tribe_ecp->date;
}

// is daysInMonth still used?
$daysInMonth = isset( $date ) ? date( 't', $date ) : date( 't' );
$startOfWeek = get_option( 'start_of_week', 0 );
list( $year, $month ) = split( '-', $current_date );
$date = mktime( 12, 0, 0, $month, 1, $year ); // 1st day of month as unix stamp
$rawOffset = date( "w", $date ) - $startOfWeek;

// setup args for filter: tribe_events_calendar_widget_the_dates
$the_dates_args = array(
	'offset' => ( $rawOffset < 0 ) ? $rawOffset + 7 : $rawOffset, // month begins on day x
	'rows' => 1,
	'monthView' => tribe_sort_by_month( tribe_get_events( array( 'eventDisplay'=>'month' ) ), $current_date ),
	'date' => $date,
	'month' => $month,
	'year' => $year
	);

// the div tribe-events-widget-nav controls ajax navigation for the calendar widget. 
// Modify with care and do not remove any class names or elements inside that element 
// if you wish to retain ajax functionality.

// start calendar widget template
echo apply_filters( 'tribe_events_calendar_widget_before_template', get_the_ID() );

	// calendar ajax navigation
	echo apply_filters( 'tribe_events_calendar_widget_before_the_nav', get_the_ID() );
	echo apply_filters( 'tribe_events_calendar_widget_the_nav', get_the_ID(), $current_date, $date );
	echo apply_filters( 'tribe_events_calendar_widget_after_the_nav', get_the_ID() );

	// start calendar
	echo apply_filters( 'tribe_events_calendar_widget_before_the_cal', get_the_ID() );
	
		// calendar days of the week
		echo apply_filters( 'tribe_events_calendar_widget_before_the_days', get_the_ID() );
		
			for( $n = $startOfWeek; $n < count( $tribe_ecp->daysOfWeekMin ) + $startOfWeek; $n++ ) {
				$dayOfWeek = ( $n >= 7 ) ? $n - 7 : $n;
				echo '<th id="tribe-events-' . strtolower( $tribe_ecp->daysOfWeekMin[$dayOfWeek] ) . '" title="' . $tribe_ecp->daysOfWeek[$dayOfWeek] . '">' . $tribe_ecp->daysOfWeekMin[$dayOfWeek] . '</th>';
			}
		
		echo apply_filters( 'tribe_events_calendar_widget_after_the_days', get_the_ID() );

		// calendar dates
		echo apply_filters( 'tribe_events_calendar_widget_before_the_dates', get_the_ID() );
		echo apply_filters( 'tribe_events_calendar_widget_the_dates', get_the_ID(), $the_dates_args );
		echo apply_filters( 'tribe_events_calendar_widget_after_the_dates', get_the_ID() );
	
	// end calendar
	echo apply_filters( 'tribe_events_calendar_widget_after_the_cal', get_the_ID() );

// end calendar widget template
echo apply_filters( 'tribe_events_calendar_widget_after_template', get_the_ID() );

if ( $old_date ) {
	$wp_query->query_vars['eventDate'] = $old_date;
}
