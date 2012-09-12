<?php
/**
 * Full Calendar Template
 * This file outputs the actual days of the month in the TEC calendar view
 *
 * You can customize this view by putting a replacement file of the same name
 * (/modules/table.php) in the tribe-events/ directory of your theme.
 *
 * @package TribeEventsCalendar
 * @since  1.0
 * @author Modern Tribe Inc.
 *
 */

// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }

$tribe_ecp = TribeEvents::instance();

// in an events cat
if ( is_tax( $tribe_ecp->get_event_taxonomy() ) ) {
	$cat = get_term_by( 'slug', get_query_var( 'term' ), $tribe_ecp->get_event_taxonomy() );
	$eventCat = (int) $cat->term_id;
	$eventPosts = tribe_get_events( array( 'eventCat' => $eventCat, 'time_order' => 'ASC', 'eventDisplay'=>'month' ) );
} // not in a cat
else {
	$eventPosts = tribe_get_events( array( 'eventDisplay'=>'month' ) );
}

$daysInMonth = isset( $date ) ? date( 't', $date ) : date( 't' );
$startOfWeek = get_option( 'start_of_week', 0 );
list( $year, $month ) = split( '-', $tribe_ecp->date );
$date = mktime( 12, 0, 0, $month, 1, $year ); // 1st day of month as unix stamp
$rawOffset = date( 'w', $date ) - $startOfWeek;
$offset = ( $rawOffset < 0 ) ? $rawOffset + 7 : $rawOffset; // month begins on day x
$rows = 1;

$monthView = tribe_sort_by_month( $eventPosts, $tribe_ecp->date );
?>

<table class="tribe-events-calendar" id="big">
	<thead>
		<tr>
		<?php
			for( $n = $startOfWeek; $n < count( $tribe_ecp->daysOfWeek ) + $startOfWeek; $n++ ) {
				$dayOfWeek = ( $n >= 7 ) ? $n - 7 : $n;
				echo '<th id="tribe-events-' . strtolower( $tribe_ecp->daysOfWeek[$dayOfWeek] ) . '" abbr="' . $tribe_ecp->daysOfWeek[$dayOfWeek] . '">' . $tribe_ecp->daysOfWeekShort[$dayOfWeek] . '</th>';
			} ?>
		</tr>
	</thead>

	<tbody>
		<tr>
		<?php
			// skip last month
			for( $i = 1; $i <= $offset; $i++ ){ 
				echo '<td class="tribe-events-othermonth"></td>';
			}
			// output this month
         	$days_in_month = date( 't', intval($date) );
			for( $day = 1; $day <= $days_in_month; $day++ ) {
			    if( ( $day + $offset - 1 ) % 7 == 0 && $day != 1 ) {
			        echo "</tr>\n\t<tr>";
			        $rows++;
			    }
			
				// Var'ng up days, months and years
				$current_day = date_i18n( 'd' );
				$current_month = date_i18n( 'm' );
				$current_year = date_i18n( 'Y' );
            	$date = "$year-$month-$day";
				
				if ( $current_month == $month && $current_year == $year) {
					// Past, Present, Future class
					if ( $current_day == $day ) {
						$ppf = ' tribe-events-present';
					} elseif ( $current_day > $day ) {
						$ppf = ' tribe-events-past';
					} elseif ( $current_day < $day ) {
						$ppf = ' tribe-events-future';
					}
				} elseif ( $current_month > $month && $current_year == $year || $current_year > $year ) {
					$ppf = ' tribe-events-past';
				} elseif ( $current_month < $month && $current_year == $year || $current_year < $year ) {
					$ppf = ' tribe-events-future';
				} else { $ppf = false; }
				
			    echo "<td class=\"tribe-events-thismonth". $ppf ."\">". display_day_title( $day, $monthView, $date ) ."\n";
				echo display_day( $day, $monthView );
				echo '</td>';
			}
			// skip next month
			while( ( $day + $offset ) <= $rows * 7 ) {
			    echo '<td class="tribe-events-othermonth"></td>';
			    $day++;
			}
		?>
		</tr>
	</tbody>
	
</table><!-- .tribe-events-calendar -->

<?php
