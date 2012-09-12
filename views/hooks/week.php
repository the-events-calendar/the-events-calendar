<?php
/**
 * Week Grid Template
 * The template for displaying events by week.
 *
 * You can customize this view by putting a replacement file of the same name
 * (week.php) in the tribe-events/ directory of your theme.
 *
 * @package TribeEventsCalendar
 * @since  2.1
 * @author Modern Tribe Inc.
 *
 */

// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }
?>	

<div id="tribe-events-content" class="grid">
	
    <!-- This title is here for ajax loading – do not remove if you want ajax switching between month views -->
    <title><?php wp_title(); ?></title>
      	
	<div id="tribe-events-calendar-header" class="clear fix">
		
		<?php // Week Nav ?>
		<span class="tribe-events-month-nav">
		
			<span class="tribe-events-prev-month">
				<a href="<?php echo tribe_get_previous_month_link(); ?>"> &#x2190; <?php echo tribe_get_previous_month_text(); ?> </a>
			</span><!-- .tribe-events-prev-month -->

			<?php tribe_month_year_dropdowns( "tribe-events-" ); ?>
	
			<span class="tribe-events-next-month">
				<a href="<?php echo tribe_get_next_month_link(); ?>"> <?php echo tribe_get_next_month_text(); ?> &#x2192; </a>
               	<img src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" class="ajax-loading" id="ajax-loading" alt="" style="display: none" />
			</span><!-- .tribe-events-next-month -->
		
		</span><!-- .tribe-events-month-nav -->

		<?php // View Buttons ?>
		<span class="tribe-events-calendar-buttons"> 
			<a class="tribe-events-button-off" href="<?php echo tribe_get_listview_link(); ?>"><?php _e( 'Event List', 'tribe-events-calendar' ); ?></a>
			<a class="tribe-events-button-on" href="<?php echo tribe_get_gridview_link(); ?>"><?php _e( 'Calendar', 'tribe-events-calendar' ); ?></a>
			<a class="tribe-events-button-off" href="#"><?php _e( 'Week', 'tribe-events-calendar' ); ?></a>
		</span><!-- .tribe-events-calendar-buttons -->
			
	</div><!-- #tribe-events-calendar-header -->
		
	
	
	
	
	<?php // Week View Grid
	/*
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
			<?php // skip last month
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
						} else if ( $current_day > $day ) {
							$ppf = ' tribe-events-past';
						} else if ( $current_day < $day ) {
							$ppf = ' tribe-events-future';
						}
					} else if ( $current_month > $month && $current_year == $year || $current_year > $year ) {
						$ppf = ' tribe-events-past';
					} else if ( $current_month < $month && $current_year == $year || $current_year < $year ) {
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
	function display_day_title( $day, $monthView, $date ) {
		$return = '<div id="daynum_'. $day .'" class="daynum tribe-events-event">';
		if( function_exists( 'tribe_get_linked_day' ) && count( $monthView[$day] ) > 0 ) {
			$return .= tribe_get_linked_day( $date, $day ); // premium
		} else {
    		$return .= $day;
		}
		$return .= '<div id="tooltip_day_'. $day .'" class="tribe-events-tooltip" style="display:none;">';
		for( $i = 0; $i < count( $monthView[$day] ); $i++ ) {
			$post = $monthView[$day][$i];
			setup_postdata( $post );
			$return .= '<h5 class="tribe-events-event-title">' . get_the_title() . '</h5>';
		}
		$return .= '<span class="tribe-events-arrow"></span>';
		$return .= '</div>';

		$return .= '</div>';
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
		<div id="event_<?php echo $eventId; ?>" <?php post_class( 'tribe-events-event tribe-events-real-event' ) ?>>
			<a href="<?php tribe_event_link(); ?>"><?php the_title(); ?></a>
			<div id="tooltip_<?php echo $eventId; ?>" class="tribe-events-tooltip" style="display:none;">
				<h5 class="tribe-events-event-title"><?php the_title() ;?></h5>
				<div class="tribe-events-event-body">
					<div class="tribe-events-event-date">
						<?php if ( !empty( $start ) )	echo date_i18n( get_option( 'date_format', 'F j, Y' ), $start );
						if ( !tribe_get_event_meta( $post->ID, '_EventAllDay', true ) )
							echo ' ' . date_i18n( get_option( 'time_format', 'g:i a' ), $start ); ?>
						<?php if ( !empty( $end )  && $start !== $end ) {
							if ( date_i18n( 'Y-m-d', $start ) == date_i18n( 'Y-m-d', $end ) ) {
								$time_format = get_option( 'time_format', 'g:i a' );
								if ( !tribe_get_event_meta( $post->ID, '_EventAllDay', true ) )
									echo " – " . date_i18n( $time_format, $end );
							} else {
								echo " – " . date_i18n( get_option( 'date_format', 'F j, Y' ), $end );
								if ( !tribe_get_event_meta( $post->ID, '_EventAllDay', true ) )
								 	echo ' ' . date_i18n( get_option( 'time_format', 'g:i a' ), $end ) . '<br />';
							}
						} ?>
					</div>
					<?php if ( function_exists( 'has_post_thumbnail' ) && has_post_thumbnail() ) { ?>
						<div class="tribe-events-event-thumb"><?php the_post_thumbnail( array( 75,75 ) );?></div>
					<?php } ?>
					<?php echo has_excerpt() ? TribeEvents::truncate( $post->post_excerpt ) : TribeEvents::truncate( get_the_content(), 30 ); ?>

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
*/
?>






	<table class="tribe-events-calendar" id="big">
	<thead>
		<tr>
		<th id="tribe-events-monday" abbr="Monday">Mon</th><th id="tribe-events-tuesday" abbr="Tuesday">Tue</th><th id="tribe-events-wednesday" abbr="Wednesday">Wed</th><th id="tribe-events-thursday" abbr="Thursday">Thu</th><th id="tribe-events-friday" abbr="Friday">Fri</th><th id="tribe-events-saturday" abbr="Saturday">Sat</th><th id="tribe-events-sunday" abbr="Sunday">Sun</th>		</tr>
	</thead>

	<tbody>
		<tr>
		<td class="tribe-events-othermonth"></td><td class="tribe-events-othermonth"></td><td class="tribe-events-othermonth"></td><td class="tribe-events-othermonth"></td><td class="tribe-events-thismonth tribe-events-future"><div id="daynum_1" class="daynum tribe-events-event"><a href='http://mtplugins:8888/events/2013-11-01'>1</a><div id="tooltip_day_1" class="tribe-events-tooltip" style="display:none;"><h5 class="tribe-events-event-title">Big Art Show 1005</h5><span class="tribe-events-arrow"></span></div></div>
		<div id="event_1021-1" class="post-1021 tribe_events type-tribe_events status-publish hentry tribe-events-event tribe-events-real-event cat_accounting">
			<a href="http://mtplugins:8888/event/big-art-show-1005/2013-10-29">Big Art Show 1005</a>
			<div id="tooltip_1021-1" class="tribe-events-tooltip" style="display:none;">
				<h5 class="tribe-events-event-title">Big Art Show 1005</h5>
				<div class="tribe-events-event-body">
					<div class="tribe-events-event-date">
						October 29, 2013 2:00 am						 – November 2, 2013 8:00 am<br />					</div>
										
				</div>
				<span class="tribe-events-arrow"></span>
			</div>
		</div>
		</td><td class="tribe-events-thismonth tribe-events-future"><div id="daynum_2" class="daynum tribe-events-event"><a href='http://mtplugins:8888/events/2013-11-02'>2</a><div id="tooltip_day_2" class="tribe-events-tooltip" style="display:none;"><h5 class="tribe-events-event-title">Big Art Show 1005</h5><span class="tribe-events-arrow"></span></div></div>
		<div id="event_1021-2" class="post-1021 tribe_events type-tribe_events status-publish hentry tribe-events-event tribe-events-real-event cat_accounting">
			<a href="http://mtplugins:8888/event/big-art-show-1005/2013-10-29">Big Art Show 1005</a>
			<div id="tooltip_1021-2" class="tribe-events-tooltip" style="display:none;">
				<h5 class="tribe-events-event-title">Big Art Show 1005</h5>
				<div class="tribe-events-event-body">
					<div class="tribe-events-event-date">
						October 29, 2013 2:00 am						 – November 2, 2013 8:00 am<br />					</div>
										
				</div>
				<span class="tribe-events-arrow"></span>
			</div>
		</div>
		</td><td class="tribe-events-thismonth tribe-events-future"><div id="daynum_3" class="daynum tribe-events-event">3<div id="tooltip_day_3" class="tribe-events-tooltip" style="display:none;"><span class="tribe-events-arrow"></span></div></div>
</td></tr>
	<tr><td class="tribe-events-thismonth tribe-events-future"><div id="daynum_4" class="daynum tribe-events-event">4<div id="tooltip_day_4" class="tribe-events-tooltip" style="display:none;"><span class="tribe-events-arrow"></span></div></div>
</td><td class="tribe-events-thismonth tribe-events-future"><div id="daynum_5" class="daynum tribe-events-event"><a href='http://mtplugins:8888/events/2013-11-05'>5</a><div id="tooltip_day_5" class="tribe-events-tooltip" style="display:none;"><h5 class="tribe-events-event-title">Big Art Show 1005</h5><span class="tribe-events-arrow"></span></div></div>
		<div id="event_1021-5" class="post-1021 tribe_events type-tribe_events status-publish hentry tribe-events-event tribe-events-real-event cat_accounting">
			<a href="http://mtplugins:8888/event/big-art-show-1005/2013-11-05">Big Art Show 1005</a>
			<div id="tooltip_1021-5" class="tribe-events-tooltip" style="display:none;">
				<h5 class="tribe-events-event-title">Big Art Show 1005</h5>
				<div class="tribe-events-event-body">
					<div class="tribe-events-event-date">
						November 5, 2013 2:00 am						 – November 9, 2013 8:00 am<br />					</div>
										
				</div>
				<span class="tribe-events-arrow"></span>
			</div>
		</div>
		</td><td class="tribe-events-thismonth tribe-events-future"><div id="daynum_6" class="daynum tribe-events-event"><a href='http://mtplugins:8888/events/2013-11-06'>6</a><div id="tooltip_day_6" class="tribe-events-tooltip" style="display:none;"><h5 class="tribe-events-event-title">Big Art Show 1005</h5><span class="tribe-events-arrow"></span></div></div>
		<div id="event_1021-6" class="post-1021 tribe_events type-tribe_events status-publish hentry tribe-events-event tribe-events-real-event cat_accounting">
			<a href="http://mtplugins:8888/event/big-art-show-1005/2013-11-05">Big Art Show 1005</a>
			<div id="tooltip_1021-6" class="tribe-events-tooltip" style="display:none;">
				<h5 class="tribe-events-event-title">Big Art Show 1005</h5>
				<div class="tribe-events-event-body">
					<div class="tribe-events-event-date">
						November 5, 2013 2:00 am						 – November 9, 2013 8:00 am<br />					</div>
										
				</div>
				<span class="tribe-events-arrow"></span>
			</div>
		</div>
		</td><td class="tribe-events-thismonth tribe-events-future"><div id="daynum_7" class="daynum tribe-events-event"><a href='http://mtplugins:8888/events/2013-11-07'>7</a><div id="tooltip_day_7" class="tribe-events-tooltip" style="display:none;"><h5 class="tribe-events-event-title">Big Art Show 1005</h5><span class="tribe-events-arrow"></span></div></div>
		<div id="event_1021-7" class="post-1021 tribe_events type-tribe_events status-publish hentry tribe-events-event tribe-events-real-event cat_accounting">
			<a href="http://mtplugins:8888/event/big-art-show-1005/2013-11-05">Big Art Show 1005</a>
			<div id="tooltip_1021-7" class="tribe-events-tooltip" style="display:none;">
				<h5 class="tribe-events-event-title">Big Art Show 1005</h5>
				<div class="tribe-events-event-body">
					<div class="tribe-events-event-date">
						November 5, 2013 2:00 am						 – November 9, 2013 8:00 am<br />					</div>
										
				</div>
				<span class="tribe-events-arrow"></span>
			</div>
		</div>
		</td><td class="tribe-events-thismonth tribe-events-future"><div id="daynum_8" class="daynum tribe-events-event"><a href='http://mtplugins:8888/events/2013-11-08'>8</a><div id="tooltip_day_8" class="tribe-events-tooltip" style="display:none;"><h5 class="tribe-events-event-title">Big Art Show 1005</h5><span class="tribe-events-arrow"></span></div></div>
		<div id="event_1021-8" class="post-1021 tribe_events type-tribe_events status-publish hentry tribe-events-event tribe-events-real-event cat_accounting">
			<a href="http://mtplugins:8888/event/big-art-show-1005/2013-11-05">Big Art Show 1005</a>
			<div id="tooltip_1021-8" class="tribe-events-tooltip" style="display:none;">
				<h5 class="tribe-events-event-title">Big Art Show 1005</h5>
				<div class="tribe-events-event-body">
					<div class="tribe-events-event-date">
						November 5, 2013 2:00 am						 – November 9, 2013 8:00 am<br />					</div>
										
				</div>
				<span class="tribe-events-arrow"></span>
			</div>
		</div>
		</td><td class="tribe-events-thismonth tribe-events-future"><div id="daynum_9" class="daynum tribe-events-event"><a href='http://mtplugins:8888/events/2013-11-09'>9</a><div id="tooltip_day_9" class="tribe-events-tooltip" style="display:none;"><h5 class="tribe-events-event-title">Big Art Show 1005</h5><span class="tribe-events-arrow"></span></div></div>
		<div id="event_1021-9" class="post-1021 tribe_events type-tribe_events status-publish hentry tribe-events-event tribe-events-real-event cat_accounting">
			<a href="http://mtplugins:8888/event/big-art-show-1005/2013-11-05">Big Art Show 1005</a>
			<div id="tooltip_1021-9" class="tribe-events-tooltip" style="display:none;">
				<h5 class="tribe-events-event-title">Big Art Show 1005</h5>
				<div class="tribe-events-event-body">
					<div class="tribe-events-event-date">
						November 5, 2013 2:00 am						 – November 9, 2013 8:00 am<br />					</div>
										
				</div>
				<span class="tribe-events-arrow"></span>
			</div>
		</div>
		</td><td class="tribe-events-thismonth tribe-events-future"><div id="daynum_10" class="daynum tribe-events-event">10<div id="tooltip_day_10" class="tribe-events-tooltip" style="display:none;"><span class="tribe-events-arrow"></span></div></div>
</td></tr>
	<tr><td class="tribe-events-thismonth tribe-events-future"><div id="daynum_11" class="daynum tribe-events-event">11<div id="tooltip_day_11" class="tribe-events-tooltip" style="display:none;"><span class="tribe-events-arrow"></span></div></div>
</td><td class="tribe-events-thismonth tribe-events-future"><div id="daynum_12" class="daynum tribe-events-event"><a href='http://mtplugins:8888/events/2013-11-12'>12</a><div id="tooltip_day_12" class="tribe-events-tooltip" style="display:none;"><h5 class="tribe-events-event-title">Big Art Show 1005</h5><span class="tribe-events-arrow"></span></div></div>
		<div id="event_1021-12" class="post-1021 tribe_events type-tribe_events status-publish hentry tribe-events-event tribe-events-real-event cat_accounting">
			<a href="http://mtplugins:8888/event/big-art-show-1005/2013-11-12">Big Art Show 1005</a>
			<div id="tooltip_1021-12" class="tribe-events-tooltip" style="display:none;">
				<h5 class="tribe-events-event-title">Big Art Show 1005</h5>
				<div class="tribe-events-event-body">
					<div class="tribe-events-event-date">
						November 12, 2013 2:00 am						 – November 16, 2013 8:00 am<br />					</div>
										
				</div>
				<span class="tribe-events-arrow"></span>
			</div>
		</div>
		</td><td class="tribe-events-thismonth tribe-events-future"><div id="daynum_13" class="daynum tribe-events-event"><a href='http://mtplugins:8888/events/2013-11-13'>13</a><div id="tooltip_day_13" class="tribe-events-tooltip" style="display:none;"><h5 class="tribe-events-event-title">Big Art Show 1005</h5><span class="tribe-events-arrow"></span></div></div>
		<div id="event_1021-13" class="post-1021 tribe_events type-tribe_events status-publish hentry tribe-events-event tribe-events-real-event cat_accounting">
			<a href="http://mtplugins:8888/event/big-art-show-1005/2013-11-12">Big Art Show 1005</a>
			<div id="tooltip_1021-13" class="tribe-events-tooltip" style="display:none;">
				<h5 class="tribe-events-event-title">Big Art Show 1005</h5>
				<div class="tribe-events-event-body">
					<div class="tribe-events-event-date">
						November 12, 2013 2:00 am						 – November 16, 2013 8:00 am<br />					</div>
										
				</div>
				<span class="tribe-events-arrow"></span>
			</div>
		</div>
		</td><td class="tribe-events-thismonth tribe-events-future"><div id="daynum_14" class="daynum tribe-events-event"><a href='http://mtplugins:8888/events/2013-11-14'>14</a><div id="tooltip_day_14" class="tribe-events-tooltip" style="display:none;"><h5 class="tribe-events-event-title">Big Art Show 1005</h5><span class="tribe-events-arrow"></span></div></div>
		<div id="event_1021-14" class="post-1021 tribe_events type-tribe_events status-publish hentry tribe-events-event tribe-events-real-event cat_accounting">
			<a href="http://mtplugins:8888/event/big-art-show-1005/2013-11-12">Big Art Show 1005</a>
			<div id="tooltip_1021-14" class="tribe-events-tooltip" style="display:none;">
				<h5 class="tribe-events-event-title">Big Art Show 1005</h5>
				<div class="tribe-events-event-body">
					<div class="tribe-events-event-date">
						November 12, 2013 2:00 am						 – November 16, 2013 8:00 am<br />					</div>
										
				</div>
				<span class="tribe-events-arrow"></span>
			</div>
		</div>
		</td><td class="tribe-events-thismonth tribe-events-future"><div id="daynum_15" class="daynum tribe-events-event"><a href='http://mtplugins:8888/events/2013-11-15'>15</a><div id="tooltip_day_15" class="tribe-events-tooltip" style="display:none;"><h5 class="tribe-events-event-title">Big Art Show 1005</h5><span class="tribe-events-arrow"></span></div></div>
		<div id="event_1021-15" class="post-1021 tribe_events type-tribe_events status-publish hentry tribe-events-event tribe-events-real-event cat_accounting">
			<a href="http://mtplugins:8888/event/big-art-show-1005/2013-11-12">Big Art Show 1005</a>
			<div id="tooltip_1021-15" class="tribe-events-tooltip" style="display:none;">
				<h5 class="tribe-events-event-title">Big Art Show 1005</h5>
				<div class="tribe-events-event-body">
					<div class="tribe-events-event-date">
						November 12, 2013 2:00 am						 – November 16, 2013 8:00 am<br />					</div>
										
				</div>
				<span class="tribe-events-arrow"></span>
			</div>
		</div>
		</td><td class="tribe-events-thismonth tribe-events-future"><div id="daynum_16" class="daynum tribe-events-event"><a href='http://mtplugins:8888/events/2013-11-16'>16</a><div id="tooltip_day_16" class="tribe-events-tooltip" style="display:none;"><h5 class="tribe-events-event-title">Big Art Show 1005</h5><span class="tribe-events-arrow"></span></div></div>
		<div id="event_1021-16" class="post-1021 tribe_events type-tribe_events status-publish hentry tribe-events-event tribe-events-real-event cat_accounting">
			<a href="http://mtplugins:8888/event/big-art-show-1005/2013-11-12">Big Art Show 1005</a>
			<div id="tooltip_1021-16" class="tribe-events-tooltip" style="display:none;">
				<h5 class="tribe-events-event-title">Big Art Show 1005</h5>
				<div class="tribe-events-event-body">
					<div class="tribe-events-event-date">
						November 12, 2013 2:00 am						 – November 16, 2013 8:00 am<br />					</div>
										
				</div>
				<span class="tribe-events-arrow"></span>
			</div>
		</div>
		</td><td class="tribe-events-thismonth tribe-events-future"><div id="daynum_17" class="daynum tribe-events-event">17<div id="tooltip_day_17" class="tribe-events-tooltip" style="display:none;"><span class="tribe-events-arrow"></span></div></div>
</td></tr>
	<tr><td class="tribe-events-thismonth tribe-events-future"><div id="daynum_18" class="daynum tribe-events-event">18<div id="tooltip_day_18" class="tribe-events-tooltip" style="display:none;"><span class="tribe-events-arrow"></span></div></div>
</td><td class="tribe-events-thismonth tribe-events-future"><div id="daynum_19" class="daynum tribe-events-event"><a href='http://mtplugins:8888/events/2013-11-19'>19</a><div id="tooltip_day_19" class="tribe-events-tooltip" style="display:none;"><h5 class="tribe-events-event-title">Big Art Show 1005</h5><span class="tribe-events-arrow"></span></div></div>
		<div id="event_1021-19" class="post-1021 tribe_events type-tribe_events status-publish hentry tribe-events-event tribe-events-real-event cat_accounting">
			<a href="http://mtplugins:8888/event/big-art-show-1005/2013-11-19">Big Art Show 1005</a>
			<div id="tooltip_1021-19" class="tribe-events-tooltip" style="display:none;">
				<h5 class="tribe-events-event-title">Big Art Show 1005</h5>
				<div class="tribe-events-event-body">
					<div class="tribe-events-event-date">
						November 19, 2013 2:00 am						 – November 23, 2013 8:00 am<br />					</div>
										
				</div>
				<span class="tribe-events-arrow"></span>
			</div>
		</div>
		</td><td class="tribe-events-thismonth tribe-events-future"><div id="daynum_20" class="daynum tribe-events-event"><a href='http://mtplugins:8888/events/2013-11-20'>20</a><div id="tooltip_day_20" class="tribe-events-tooltip" style="display:none;"><h5 class="tribe-events-event-title">Big Art Show 1005</h5><span class="tribe-events-arrow"></span></div></div>
		<div id="event_1021-20" class="post-1021 tribe_events type-tribe_events status-publish hentry tribe-events-event tribe-events-real-event cat_accounting">
			<a href="http://mtplugins:8888/event/big-art-show-1005/2013-11-19">Big Art Show 1005</a>
			<div id="tooltip_1021-20" class="tribe-events-tooltip" style="display:none;">
				<h5 class="tribe-events-event-title">Big Art Show 1005</h5>
				<div class="tribe-events-event-body">
					<div class="tribe-events-event-date">
						November 19, 2013 2:00 am						 – November 23, 2013 8:00 am<br />					</div>
										
				</div>
				<span class="tribe-events-arrow"></span>
			</div>
		</div>
		</td><td class="tribe-events-thismonth tribe-events-future"><div id="daynum_21" class="daynum tribe-events-event"><a href='http://mtplugins:8888/events/2013-11-21'>21</a><div id="tooltip_day_21" class="tribe-events-tooltip" style="display:none;"><h5 class="tribe-events-event-title">Big Art Show 1005</h5><span class="tribe-events-arrow"></span></div></div>
		<div id="event_1021-21" class="post-1021 tribe_events type-tribe_events status-publish hentry tribe-events-event tribe-events-real-event cat_accounting">
			<a href="http://mtplugins:8888/event/big-art-show-1005/2013-11-19">Big Art Show 1005</a>
			<div id="tooltip_1021-21" class="tribe-events-tooltip" style="display:none;">
				<h5 class="tribe-events-event-title">Big Art Show 1005</h5>
				<div class="tribe-events-event-body">
					<div class="tribe-events-event-date">
						November 19, 2013 2:00 am						 – November 23, 2013 8:00 am<br />					</div>
										
				</div>
				<span class="tribe-events-arrow"></span>
			</div>
		</div>
		</td><td class="tribe-events-thismonth tribe-events-future"><div id="daynum_22" class="daynum tribe-events-event"><a href='http://mtplugins:8888/events/2013-11-22'>22</a><div id="tooltip_day_22" class="tribe-events-tooltip" style="display:none;"><h5 class="tribe-events-event-title">Big Art Show 1005</h5><span class="tribe-events-arrow"></span></div></div>
		<div id="event_1021-22" class="post-1021 tribe_events type-tribe_events status-publish hentry tribe-events-event tribe-events-real-event cat_accounting">
			<a href="http://mtplugins:8888/event/big-art-show-1005/2013-11-19">Big Art Show 1005</a>
			<div id="tooltip_1021-22" class="tribe-events-tooltip" style="display:none;">
				<h5 class="tribe-events-event-title">Big Art Show 1005</h5>
				<div class="tribe-events-event-body">
					<div class="tribe-events-event-date">
						November 19, 2013 2:00 am						 – November 23, 2013 8:00 am<br />					</div>
										
				</div>
				<span class="tribe-events-arrow"></span>
			</div>
		</div>
		</td><td class="tribe-events-thismonth tribe-events-future"><div id="daynum_23" class="daynum tribe-events-event"><a href='http://mtplugins:8888/events/2013-11-23'>23</a><div id="tooltip_day_23" class="tribe-events-tooltip" style="display:none;"><h5 class="tribe-events-event-title">Big Art Show 1005</h5><span class="tribe-events-arrow"></span></div></div>
		<div id="event_1021-23" class="post-1021 tribe_events type-tribe_events status-publish hentry tribe-events-event tribe-events-real-event cat_accounting">
			<a href="http://mtplugins:8888/event/big-art-show-1005/2013-11-19">Big Art Show 1005</a>
			<div id="tooltip_1021-23" class="tribe-events-tooltip" style="display:none;">
				<h5 class="tribe-events-event-title">Big Art Show 1005</h5>
				<div class="tribe-events-event-body">
					<div class="tribe-events-event-date">
						November 19, 2013 2:00 am						 – November 23, 2013 8:00 am<br />					</div>
										
				</div>
				<span class="tribe-events-arrow"></span>
			</div>
		</div>
		</td><td class="tribe-events-thismonth tribe-events-future"><div id="daynum_24" class="daynum tribe-events-event">24<div id="tooltip_day_24" class="tribe-events-tooltip" style="display:none;"><span class="tribe-events-arrow"></span></div></div>
</td></tr>
	<tr><td class="tribe-events-thismonth tribe-events-future"><div id="daynum_25" class="daynum tribe-events-event">25<div id="tooltip_day_25" class="tribe-events-tooltip" style="display:none;"><span class="tribe-events-arrow"></span></div></div>
</td><td class="tribe-events-thismonth tribe-events-future"><div id="daynum_26" class="daynum tribe-events-event"><a href='http://mtplugins:8888/events/2013-11-26'>26</a><div id="tooltip_day_26" class="tribe-events-tooltip" style="display:none;"><h5 class="tribe-events-event-title">Big Art Show 1005</h5><span class="tribe-events-arrow"></span></div></div>
		<div id="event_1021-26" class="post-1021 tribe_events type-tribe_events status-publish hentry tribe-events-event tribe-events-real-event cat_accounting">
			<a href="http://mtplugins:8888/event/big-art-show-1005/2013-11-26">Big Art Show 1005</a>
			<div id="tooltip_1021-26" class="tribe-events-tooltip" style="display:none;">
				<h5 class="tribe-events-event-title">Big Art Show 1005</h5>
				<div class="tribe-events-event-body">
					<div class="tribe-events-event-date">
						November 26, 2013 2:00 am						 – November 30, 2013 8:00 am<br />					</div>
										
				</div>
				<span class="tribe-events-arrow"></span>
			</div>
		</div>
		</td><td class="tribe-events-thismonth tribe-events-future"><div id="daynum_27" class="daynum tribe-events-event"><a href='http://mtplugins:8888/events/2013-11-27'>27</a><div id="tooltip_day_27" class="tribe-events-tooltip" style="display:none;"><h5 class="tribe-events-event-title">Big Art Show 1005</h5><span class="tribe-events-arrow"></span></div></div>
		<div id="event_1021-27" class="post-1021 tribe_events type-tribe_events status-publish hentry tribe-events-event tribe-events-real-event cat_accounting">
			<a href="http://mtplugins:8888/event/big-art-show-1005/2013-11-26">Big Art Show 1005</a>
			<div id="tooltip_1021-27" class="tribe-events-tooltip" style="display:none;">
				<h5 class="tribe-events-event-title">Big Art Show 1005</h5>
				<div class="tribe-events-event-body">
					<div class="tribe-events-event-date">
						November 26, 2013 2:00 am						 – November 30, 2013 8:00 am<br />					</div>
										
				</div>
				<span class="tribe-events-arrow"></span>
			</div>
		</div>
		</td><td class="tribe-events-thismonth tribe-events-future"><div id="daynum_28" class="daynum tribe-events-event"><a href='http://mtplugins:8888/events/2013-11-28'>28</a><div id="tooltip_day_28" class="tribe-events-tooltip" style="display:none;"><h5 class="tribe-events-event-title">Big Art Show 1005</h5><span class="tribe-events-arrow"></span></div></div>
		<div id="event_1021-28" class="post-1021 tribe_events type-tribe_events status-publish hentry tribe-events-event tribe-events-real-event cat_accounting">
			<a href="http://mtplugins:8888/event/big-art-show-1005/2013-11-26">Big Art Show 1005</a>
			<div id="tooltip_1021-28" class="tribe-events-tooltip" style="display:none;">
				<h5 class="tribe-events-event-title">Big Art Show 1005</h5>
				<div class="tribe-events-event-body">
					<div class="tribe-events-event-date">
						November 26, 2013 2:00 am						 – November 30, 2013 8:00 am<br />					</div>
										
				</div>
				<span class="tribe-events-arrow"></span>
			</div>
		</div>
		</td><td class="tribe-events-thismonth tribe-events-future"><div id="daynum_29" class="daynum tribe-events-event"><a href='http://mtplugins:8888/events/2013-11-29'>29</a><div id="tooltip_day_29" class="tribe-events-tooltip" style="display:none;"><h5 class="tribe-events-event-title">Big Art Show 1005</h5><span class="tribe-events-arrow"></span></div></div>
		<div id="event_1021-29" class="post-1021 tribe_events type-tribe_events status-publish hentry tribe-events-event tribe-events-real-event cat_accounting">
			<a href="http://mtplugins:8888/event/big-art-show-1005/2013-11-26">Big Art Show 1005</a>
			<div id="tooltip_1021-29" class="tribe-events-tooltip" style="display:none;">
				<h5 class="tribe-events-event-title">Big Art Show 1005</h5>
				<div class="tribe-events-event-body">
					<div class="tribe-events-event-date">
						November 26, 2013 2:00 am						 – November 30, 2013 8:00 am<br />					</div>
										
				</div>
				<span class="tribe-events-arrow"></span>
			</div>
		</div>
		</td><td class="tribe-events-thismonth tribe-events-future"><div id="daynum_30" class="daynum tribe-events-event"><a href='http://mtplugins:8888/events/2013-11-30'>30</a><div id="tooltip_day_30" class="tribe-events-tooltip" style="display:none;"><h5 class="tribe-events-event-title">Big Art Show 1005</h5><span class="tribe-events-arrow"></span></div></div>
		<div id="event_1021-30" class="post-1021 tribe_events type-tribe_events status-publish hentry tribe-events-event tribe-events-real-event cat_accounting">
			<a href="http://mtplugins:8888/event/big-art-show-1005/2013-11-26">Big Art Show 1005</a>
			<div id="tooltip_1021-30" class="tribe-events-tooltip" style="display:none;">
				<h5 class="tribe-events-event-title">Big Art Show 1005</h5>
				<div class="tribe-events-event-body">
					<div class="tribe-events-event-date">
						November 26, 2013 2:00 am						 – November 30, 2013 8:00 am<br />					</div>
										
				</div>
				<span class="tribe-events-arrow"></span>
			</div>
		</div>
		</td><td class="tribe-events-othermonth"></td>		</tr>
	</tbody>
	
	</table><!-- .tribe-events-calendar -->







	
	
		
    <?php if( function_exists( 'tribe_get_ical_link' ) ): ?>
       	<a title="<?php esc_attr_e( 'iCal Import', 'tribe-events-calendar' ); ?>" class="ical" href="<?php echo tribe_get_ical_link(); ?>"><?php _e( 'iCal Import', 'tribe-events-calendar' ); ?></a>
    <?php endif; ?>
	<?php if ( tribe_get_option( 'donate-link', false ) == true ) { ?>
		<p class="tribe-promo-banner"><?php echo apply_filters( 'tribe_promo_banner', sprintf( __( 'Calendar powered by %sThe Events Calendar%s', 'tribe-events-calendar' ), '<a href="http://tri.be/wordpress-events-calendar/">', '</a>' ) ); ?></p>
	<?php } ?>
		
</div><!-- #tribe-events-content -->
