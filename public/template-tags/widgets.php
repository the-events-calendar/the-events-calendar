<?php
/**
 * The Events Calendar Template Tags
 *
 * Display functions (template-tags) for use in WordPress templates.
 */

// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }

if( class_exists( 'TribeEvents' ) ) {

	function tribe_mini_display_day( $day, $monthView ) {
		$return = '<div id="tribe-events-daynum-'. $day .'">';

		$return .= ( count( $monthView[$day] ) ) ? '<a class="tribe-events-day-has-event">'. $day .'</a>' : $day;
		$return .= '<div id="tribe-events-tooltip-day-'. $day .'" class="tribe-events-tooltip hentry vevent">';
		for( $i = 0; $i < count( $monthView[$day] ); $i++ ) {
			$post = $monthView[$day][$i];
			setup_postdata( $post );

			$return .= '<h5 class="entry-title summary"><a href="'. tribe_get_event_link( $post ) .'" rel="bookmark">' . $post->post_title . '</a></h5>';
		}
		$return .= '<span class="tribe-events-arrow"></span>';
		$return .= '</div><!-- .tribe-events-tooltip -->';

		$return .= '</div><!-- #tribe-events-daynum-# -->';
		return $return;
	}

}