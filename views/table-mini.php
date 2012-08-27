<?php
/**
* The calendar widget display.
*
* You can customize this view by putting a replacement file of the same name (table-mini.php) in the events/ directory of your theme.
*/

// Don't load directly
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
}else{
	$current_date = $tribe_ecp->date;
}




$eventPosts = tribe_get_events(array( 'eventDisplay'=>'month' ) );
if ( !$current_date ) {
	$current_date = $tribe_ecp->date;
}

$daysInMonth = isset($date) ? date("t", $date) : date("t");
$startOfWeek = get_option( 'start_of_week', 0 );
list( $year, $month ) = split( '-', $current_date );
$date = mktime(12, 0, 0, $month, 1, $year); // 1st day of month as unix stamp
$rawOffset = date("w", $date) - $startOfWeek;
$offset = ( $rawOffset < 0 ) ? $rawOffset + 7 : $rawOffset; // month begins on day x
$rows = 1;
$monthView = tribe_sort_by_month( $eventPosts, $current_date );

// the div tribe-events-widget-nav controls ajax navigation for the calendar widget. Modify with care and do not remove any class names or elements inside that element if you wish to retain ajax functionality.

?>
<div class="tribe-events-widget-nav">  
  <a class="tribe-mini-ajax prev-month" href="#" data-month="<?php echo $tribe_ecp->previousMonth( $current_date ); ?>" title="<?php echo tribe_get_previous_month_text(); ?>">
    <span><?php echo tribe_get_previous_month_text(); ?></span>
  </a>
  <span id="tribe-mini-ajax-month">
    <?php echo $tribe_ecp->monthsShort[date('M',$date)]; echo date(' Y',$date); ?>
  </span>
  <a class="tribe-mini-ajax next-month" href="#" data-month="<?php echo $tribe_ecp->nextMonth( $current_date ); ?>" title="<?php echo tribe_get_next_month_text(); ?>">
    <span><?php echo tribe_get_next_month_text(); ?></span> 
  </a>
  <img id="ajax-loading-mini" src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" alt="loading..." />
</div>  
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

<a class="tribe-view-all-events" href="<?php echo tribe_get_events_link(); ?>"><?php _e('View all &raquo;', 'tribe-events-calendar'); ?></a>

<?php
if ($old_date){
	$wp_query->query_vars['eventDate'] = $old_date;
}

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
