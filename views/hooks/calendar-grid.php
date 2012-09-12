<?php
/**
 * Full Calendar Template
 * This file outputs the actual days of the month in the TEC calendar view
 *
 * You can customize this view by putting a replacement file of the same name
 * (/modules/calendar-grid.php) in the tribe-events/ directory of your theme.
 *
 * @package TribeEventsCalendar
 * @since  1.0
 * @author Modern Tribe Inc.
 *
 */

// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }

if( !function_exists('display_day_title')) {
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
}
if( !function_exists('display_day')) {
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
}