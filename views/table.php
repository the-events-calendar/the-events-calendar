<?php

/**
 * Copy and paste this to events/table.php in your template to customize
 */

global $sp_ecp;

// in an events cat
if ( is_tax( $sp_ecp->get_event_taxonomy() ) ) {
	$cat = get_term_by( 'slug', get_query_var('term'), $sp_ecp->get_event_taxonomy() );
	$eventCat = (int) $cat->term_id;
	$eventPosts = sp_get_events( array( 'eventCat' => $eventCat ) );
} // not in a cat
else {
	$eventPosts = sp_get_events();
}


$daysInMonth = date("t", $date);
$startOfWeek = get_option( 'start_of_week', 0 );
list( $year, $month ) = split( '-', $sp_ecp->date );
$date = mktime(12, 0, 0, $month, 1, $year); // 1st day of month as unix stamp
$rawOffset = date("w", $date) - $startOfWeek;
$offset = ( $rawOffset < 0 ) ? $rawOffset + 7 : $rawOffset; // month begins on day x
$rows = 1;
$monthView = sp_sort_by_month( $eventPosts, $sp_ecp->date );

?>
<table class="tec-calendar" id="big">
	<thead>
			<tr>
				<?php //$sp_ecp->log($sp_ecp->daysOfWeekShort);
				for( $n = $startOfWeek; $n < count($sp_ecp->daysOfWeek) + $startOfWeek; $n++ ) {
					$dayOfWeek = ( $n >= 7 ) ? $n - 7 : $n;
					
					echo '<th id="tec-' . strtolower($sp_ecp->daysOfWeek[$dayOfWeek]) . '" abbr="' . $sp_ecp->daysOfWeek[$dayOfWeek] . '">' . $sp_ecp->daysOfWeekShort[$dayOfWeek] . '</th>';
				}
				?>
			</tr>
	</thead>

	<tbody>
		<tr>
		<?php
			// skip last month
			for( $i = 1; $i <= $offset; $i++ ){ 
				echo "<td class='tec-othermonth'></td>";
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
						$ppf = ' tec-present';
					} elseif ($current_day > $day) {
						$ppf = ' tec-past';
					} elseif ($current_day < $day) {
						$ppf = ' tec-future';
					}
				} elseif ( $current_month > $month && $current_year == $year || $current_year > $year ) {
					$ppf = ' tec-past';
				} elseif ( $current_month < $month && $current_year == $year || $current_year < $year ) {
					$ppf = ' tec-future';
				} else { $ppf = false; }
				
			    echo "<td class='tec-thismonth" . $ppf . "'>" . display_day_title( $day, $monthView ) . "\n";
				echo display_day( $day, $monthView );
				echo "</td>";
			}
			// skip next month
			while( ($day + $offset) <= $rows * 7)
			{
			    echo "<td class='tec-othermonth'></td>";
			    $day++;
			}
		?>
		</tr>
	</tbody>
</table>
<?php

function display_day_title( $day, $monthView ) {
	$return = "<div class='daynum tec-event' id='daynum_$day'>";

	$return .= $day;
	$return .= "<div id='tooltip_day_$day' class='tec-tooltip' style='display:none;'>";
	for( $i = 0; $i < count( $monthView[$day] ); $i++ ) {
		$post = $monthView[$day][$i];
		setup_postdata( $post );
		$return .= '<h5 class="tec-event-title">' . get_the_title() . '</h5>';
	}
	$return .= '<span class="tec-arrow"></span>';
	$return .= '</div>';

	$return .= "</div>";
	return $return;
}

function display_day( $day, $monthView ) {
	global $post;
	$output = '';
	$posts_per_page = get_option( 'posts_per_page' );
	for ( $i = 0; $i < count( $monthView[$day] ); $i++ ) {
		$post = $monthView[$day][$i];
		setup_postdata( $post );
		$eventId	= $post->ID.'-'.$day;
		$start		= sp_get_start_date( $post->ID );
		$end		= sp_get_end_date( $post->ID );
		$cost		= sp_get_cost( $post->ID );
		$address	= sp_get_address( $post->ID );
		$city		= sp_get_city( $post->ID );
		$state		= sp_get_state( $post->ID );
		$province	= sp_get_province( $post->ID );
		$country	= sp_get_country( $post->ID );
		?>
		<div id='event_<?php echo $eventId; ?>' <?php post_class('tec-event') ?>>
			<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
			<div id='tooltip_<?php echo $eventId; ?>' class="tec-tooltip" style="display:none;">
				<h5 class="tec-event-title"><?php the_title();?></h5>
				<div class="tec-event-body">
					<?php if ( !sp_get_all_day($post->ID) ) : ?>
					<div class="tec-event-date">
						<?php if ( !empty( $start ) )	echo $start; ?>
						<?php if ( !empty( $end )  && $start !== $end )		echo " – " . $end . '<br />'; ?>
					</div>
					<?php endif; ?>
					<?php echo Events_Calendar_Pro::truncate(get_the_content(), 30); ?>

				</div>
				<span class="tec-arrow"></span>
			</div>
		</div>
		<?php
		if( $i < count( $monthView[$day] ) - 1 ) { 
			echo "<hr />";
		}
	}
}