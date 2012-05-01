<?php
/**
* The calendar widget display.
*
* You can customize this view by putting a replacement file of the same name (table-mini.php) in the events/ directory of your theme.
*/

// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }

$tribe_ecp = TribeEvents::instance(); 
$eventPosts = tribe_get_events(array( 'eventDisplay'=>'month' ) );
$daysInMonth = isset($date) ? date("t", $date) : date("t");
$startOfWeek = get_option( 'start_of_week', 0 );
list( $year, $month ) = split( '-', $tribe_ecp->date );
$date = mktime(12, 0, 0, $month, 1, $year); // 1st day of month as unix stamp
$rawOffset = date("w", $date) - $startOfWeek;
$offset = ( $rawOffset < 0 ) ? $rawOffset + 7 : $rawOffset; // month begins on day x
$rows = 1;
$monthView = tribe_sort_by_month( $eventPosts, $tribe_ecp->date );


?>
<h4 class="cal-header"><?php echo $tribe_ecp->monthsShort[date('M',$date)]; echo date(' Y',$date); ?> <a class="tribe-view-all-events" href="<?php echo tribe_get_events_link(); ?>"><?php _e('View all &raquo;', 'tribe-events-calendar'); ?></a></h4>
<table class="tribe-events-calendar tribe-events-calendar-widget" id="small">
	<thead>
			<tr>
				<?php

				for( $n = $startOfWeek; $n < count($tribe_ecp->daysOfWeekMin) + $startOfWeek; $n++ ) {
					$dayOfWeek = ( $n >= 7 ) ? $n - 7 : $n;
					echo '<th id="tribe-events-' . strtolower($tribe_ecp->daysOfWeekMin[$dayOfWeek]) . '" title="' . $tribe_ecp->daysOfWeek[$dayOfWeek] . '">' . $tribe_ecp->daysOfWeekMin[$dayOfWeek] . '</th>';
				}
				?>
			</tr>
	</thead>

	<tbody>
		<tr>
		<?php
			// skip last month
			for( $i = 1; $i <= $offset; $i++ ){ 
				echo "<td class='tribe-events-othermonth'></td>";
			}
			// output this month
			for( $day = 1; $day <= date("t", $date); $day++ ) {
			    if( ($day + $offset - 1) % 7 == 0 && $day != 1) {
			        echo "</tr>\n\t<tr>";
			        $rows++;
			    }

				// Var'ng up days, months and years
				$current_day = date_i18n( 'd' );
				$current_month = date_i18n( 'm' );
				$current_year = date_i18n( 'Y' );
				
				if ( $current_month == $month && $current_year == $year) {
					// Past, Present, Future class
					if ($current_day == $day ) {
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
			    echo "<td class='tribe-events-thismonth" . $ppf . "'>" . tribe_mini_display_day( $day, $monthView ) . "\n";
				echo "</td>";
			}
			// skip next month
			while( ($day + $offset) <= $rows * 7)
			{
			    echo "<td class='tribe-events-othermonth'></td>";
			    $day++;
			}
		?>
		</tr>
	</tbody>
</table>
<?php

function tribe_mini_display_day( $day, $monthView ) {
	$return = "<div class='daynum tribe-events-event' id='daynum_$day'>";

	$return .= ( count($monthView[$day]) ) ? "<a class='tribe-events-mini-has-event'>$day</a>" : $day;
	$return .= "<div id='tooltip_day_$day' class='tribe-events-tooltip' style='display:none;'>";
	for( $i = 0; $i < count( $monthView[$day] ); $i++ ) {
		$post = $monthView[$day][$i];
		setup_postdata( $post );

		$return .= '<h5 class="tribe-events-event-title-mini"><a href="'. tribe_get_event_link($post) .'">' . $post->post_title . '</a></h5>';
	}
	$return .= '<span class="tribe-events-arrow"></span>';
	$return .= '</div>';

	$return .= "</div>";
	return $return;
}
