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

$eventPosts = tribe_get_events( array( 'eventDisplay'=>'month' ) );
if ( !$current_date ) {
	$current_date = $tribe_ecp->date;
}

$daysInMonth = isset( $date ) ? date( 't', $date ) : date( 't' );
$startOfWeek = get_option( 'start_of_week', 0 );
list( $year, $month ) = split( '-', $current_date );
$date = mktime( 12, 0, 0, $month, 1, $year ); // 1st day of month as unix stamp
$rawOffset = date( "w", $date ) - $startOfWeek;
$offset = ( $rawOffset < 0 ) ? $rawOffset + 7 : $rawOffset; // month begins on day x
$rows = 1;
$monthView = tribe_sort_by_month( $eventPosts, $current_date );

// the div tribe-events-widget-nav controls ajax navigation for the calendar widget. 
// Modify with care and do not remove any class names or elements inside that element 
// if you wish to retain ajax functionality.

// start calendar widget template
echo apply_filters( 'tribe_events_calendar_widget_before_template', '', get_the_ID() );

	// calendar ajax navigation
	echo apply_filters( 'tribe_events_calendar_widget_before_the_nav', '', get_the_ID() );
	echo apply_filters( 'tribe_events_calendar_widget_the_nav', '', get_the_ID() );
	echo apply_filters( 'tribe_events_calendar_widget_after_the_nav', '', get_the_ID() );

	// start calendar
	echo apply_filters( 'tribe_events_calendar_widget_before_the_cal', '', get_the_ID() );
	
		// calendar days of the week
		echo apply_filters( 'tribe_events_calendar_widget_before_the_days', '', get_the_ID() );
		
			for( $n = $startOfWeek; $n < count( $tribe_ecp->daysOfWeekMin ) + $startOfWeek; $n++ ) {
				$dayOfWeek = ( $n >= 7 ) ? $n - 7 : $n;
				echo '<th id="tribe-events-' . strtolower( $tribe_ecp->daysOfWeekMin[$dayOfWeek] ) . '" title="' . $tribe_ecp->daysOfWeek[$dayOfWeek] . '">' . $tribe_ecp->daysOfWeekMin[$dayOfWeek] . '</th>';
			}
		
		echo apply_filters( 'tribe_events_calendar_widget_after_the_days', '', get_the_ID() );

		// calendar dates
		echo apply_filters( 'tribe_events_calendar_widget_before_the_dates', '', get_the_ID() );

			// skip last month
			for( $i = 1; $i <= $offset; $i++ ) { 
				echo '<td class="tribe-events-othermonth"></td>';
			}
			// output this month
			for( $day = 1; $day <= date( "t", $date ); $day++ ) {
			    
			    if( ( $day + $offset - 1 ) % 7 == 0 && $day != 1 ) {
			        echo "</tr>\n\t<tr>";
			        $rows++;
			    }

				// Var'ng up days, months and years
				$current_day = date_i18n( 'd' );
				$current_month = date_i18n( 'm' );
				$current_year = date_i18n( 'Y' );
				
				if ( $current_month == $month && $current_year == $year) {
					// Past, Present, Future class
					if ( $current_day == $day ) {
						$ppf = ' tribe-events-present';
					} elseif ($current_day > $day) {
						$ppf = ' tribe-events-past';
					} elseif ($current_day < $day) {
						$ppf = ' tribe-events-future';
					}
				} elseif ( $current_month > $month && $current_year == $year || $current_year > $year ) {
					$ppf = ' tribe-events-past';
				} elseif ( $current_month < $month && $current_year == $year || $current_year < $year ) {
					$ppf = ' tribe-events-future';
				} else { $ppf = false; }
			   
			    echo "<td class=\"tribe-events-thismonth". $ppf ."\">". tribe_mini_display_day( $day, $monthView ) ."\n";
				echo "</td>";
			
			}
			// skip next month
			while( ( $day + $offset ) <= $rows * 7 ) {
			    echo '<td class="tribe-events-othermonth"></td>';
			    $day++;
			}

		echo apply_filters( 'tribe_events_calendar_widget_after_the_dates', '', get_the_ID() );
	
	// end calendar
	echo apply_filters( 'tribe_events_calendar_widget_after_the_cal', '', get_the_ID() );

// end calendar widget template
echo apply_filters( 'tribe_events_calendar_widget_after_template', '', get_the_ID() );

if ( $old_date ) {
	$wp_query->query_vars['eventDate'] = $old_date;
}

function tribe_mini_display_day( $day, $monthView ) {
	$return = '<div id="daynum_'. $day .'" class="daynum tribe-events-event">';

	$return .= ( count( $monthView[$day] ) ) ? '<a class="tribe-events-mini-has-event">'. $day .'</a>' : $day;
	$return .= '<div id="tooltip_day_'. $day .'" class="tribe-events-tooltip" style="display:none;">';
	for( $i = 0; $i < count( $monthView[$day] ); $i++ ) {
		$post = $monthView[$day][$i];
		setup_postdata( $post );

		$return .= '<h5 class="tribe-events-event-title-mini"><a href="'. tribe_get_event_link( $post ) .'">' . $post->post_title . '</a></h5>';
	}
	$return .= '<span class="tribe-events-arrow"></span>';
	$return .= '</div>';

	$return .= '</div>';
	return $return;
}
