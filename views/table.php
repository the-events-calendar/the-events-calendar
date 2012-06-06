<?php
/**
* This file outputs the actual days of the month in the TEC calendar view
*
* You can customize this view by putting a replacement file of the same name (table.php) in the events/ directory of your theme.
*/

// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }

$tribe_ecp = TribeEvents::instance();

// in an events cat
if ( is_tax( $tribe_ecp->get_event_taxonomy() ) ) {
	$cat = get_term_by( 'slug', get_query_var('term'), $tribe_ecp->get_event_taxonomy() );
	$eventCat = (int) $cat->term_id;
	$eventPosts = tribe_get_events( array( 'eventCat' => $eventCat, 'time_order' => 'ASC', 'eventDisplay'=>'month' ) );
} // not in a cat
else {
	$eventPosts = tribe_get_events(array( 'eventDisplay'=>'month' ));
}


$daysInMonth = isset($date) ? date("t", $date) : date("t");
$startOfWeek = get_option( 'start_of_week', 0 );
list( $year, $month ) = split( '-', $tribe_ecp->date );
$date = mktime(12, 0, 0, $month, 1, $year); // 1st day of month as unix stamp
$rawOffset = date("w", $date) - $startOfWeek;
$offset = ( $rawOffset < 0 ) ? $rawOffset + 7 : $rawOffset; // month begins on day x
$rows = 1;

$monthView = tribe_sort_by_month( $eventPosts, $tribe_ecp->date );

?>
<table class="tribe-events-calendar" id="big">
	<thead>
			<tr>
				<?php
				for( $n = $startOfWeek; $n < count($tribe_ecp->daysOfWeek) + $startOfWeek; $n++ ) {
					$dayOfWeek = ( $n >= 7 ) ? $n - 7 : $n;
					
					echo '<th id="tribe-events-' . strtolower($tribe_ecp->daysOfWeek[$dayOfWeek]) . '" abbr="' . $tribe_ecp->daysOfWeek[$dayOfWeek] . '">' . $tribe_ecp->daysOfWeekShort[$dayOfWeek] . '</th>';
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
         $days_in_month = date("t", intval($date));
			for( $day = 1; $day <= $days_in_month; $day++ ) {
			    if( ($day + $offset - 1) % 7 == 0 && $day != 1) {
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
				
			    echo "<td class='tribe-events-thismonth" . $ppf . "'>" . display_day_title( $day, $monthView, $date ) . "\n";
				echo display_day( $day, $monthView );
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

function display_day_title( $day, $monthView, $date ) {
	$return = "<div class='daynum tribe-events-event' id='daynum_$day'>";
	if( function_exists('tribe_get_linked_day') && count( $monthView[$day] ) > 0 ) {
		$return .= tribe_get_linked_day($date, $day); // premium
	} else {
    	$return .= $day;
	}
	$return .= "<div id='tooltip_day_$day' class='tribe-events-tooltip' style='display:none;'>";
	for( $i = 0; $i < count( $monthView[$day] ); $i++ ) {
		$post = $monthView[$day][$i];
		setup_postdata( $post );
		$return .= '<h5 class="tribe-events-event-title">' . get_the_title() . '</h5>';
	}
	$return .= '<span class="tribe-events-arrow"></span>';
	$return .= '</div>';

	$return .= "</div>";
	return $return;
}

function display_day( $day, $monthView ) {
	global $post;
	$output = '';
	$posts_per_page = tribe_get_option( 'postsPerPage', 10 );
	for ( $i = 0; $i < count( $monthView[$day] ); $i++ ) {
		$post = $monthView[$day][$i];
		setup_postdata( $post );
		$eventId	= $post->ID.'-'.$day;
		$start		= tribe_get_start_date( $post->ID, false, 'U' );
		$end		= tribe_get_end_date( $post->ID, false, 'U' );
		$cost		= tribe_get_cost( $post->ID );
		?>
		<div id='event_<?php echo $eventId; ?>' <?php post_class('tribe-events-event tribe-events-real-event') ?>>
			<a href="<?php tribe_event_link(); ?>"><?php the_title(); ?></a>
			<div id='tooltip_<?php echo $eventId; ?>' class="tribe-events-tooltip" style="display:none;">
				<h5 class="tribe-events-event-title"><?php the_title();?></h5>
				<div class="tribe-events-event-body">
					<div class="tribe-events-event-date">
						<?php if ( !empty( $start ) )	echo date_i18n( get_option('date_format', 'F j, Y'), $start);
						if ( !tribe_get_event_meta($post->ID, '_EventAllDay', true) )
							echo ' ' . date_i18n( get_option('time_format', 'g:i a'), $start); ?>
						<?php if ( !empty( $end )  && $start !== $end ) {
							if ( date_i18n( 'Y-m-d', $start ) == date_i18n( 'Y-m-d', $end ) ) {
								$time_format = get_option( 'time_format', 'g:i a' );
								if ( !tribe_get_event_meta($post->ID, '_EventAllDay', true) )
									echo " – " . date_i18n( $time_format, $end );
							} else {
								echo " – " . date_i18n( get_option('date_format', 'F j, Y'), $end);
								if ( !tribe_get_event_meta($post->ID, '_EventAllDay', true) )
								 	echo ' ' . date_i18n( get_option('time_format', 'g:i a'), $end) . '<br />';
							}
						} ?>
					</div>
					<?php if ( function_exists('has_post_thumbnail') && has_post_thumbnail() ) { ?>
						<div class="tribe-events-event-thumb"><?php the_post_thumbnail( array(75,75));?></div>
					<?php } ?>
					<?php echo has_excerpt() ? TribeEvents::truncate($post->post_excerpt) : TribeEvents::truncate(get_the_content(), 30); ?>

				</div>
				<span class="tribe-events-arrow"></span>
			</div>
		</div>
		<?php
		if( $i < count( $monthView[$day] ) - 1 ) { 
			echo "<hr />";
		}
	}
}
?>
