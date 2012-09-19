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

}