<?php
/**
 * Map Template
 * Render our Map template.
 *
 * This view contains the filters required to create an effective map view.
 *
 * You can recreate an ENTIRELY new map view by doing a template override, and placing
 * a map.php file in a tribe-events/pro/ directory within your theme directory, which
 * will override the /views/map.php. 
 *
 * You can use any or all filters included in this file or create your own filters in 
 * your functions.php. In order to modify or extend a single filter, please see our
 * readme on templates hooks and filters (TO-DO)
 *
 * @package TribeEventsCalendar
 * @since  3.0
 * @author Modern Tribe Inc.
 *
 */

if ( !defined('ABSPATH') ) { die('-1'); }

// Start map template
echo apply_filters( 'tribe_events_map_before_template', '', get_the_ID() );

	// Map
	echo apply_filters( 'tribe_events_map_before_the_map', '', get_the_ID() );
	echo apply_filters( 'tribe_events_map_the_map', '', get_the_ID() );
	echo apply_filters( 'tribe_events_map_after_the_map', '', get_the_ID() );
	
	// Options
	echo apply_filters( 'tribe_events_map_before_the_options', '', get_the_ID() );
	echo apply_filters( 'tribe_events_map_the_options', '', get_the_ID() );
	echo apply_filters( 'tribe_events_map_after_the_options', '', get_the_ID() );
	
	// Results
	echo apply_filters( 'tribe_events_map_before_the_results', '', get_the_ID() );
	
		$result_count = count( $data );
		$counter      = 0;
		foreach ( $data as $event ) {
			global $post;
			$post = $event;
			$counter++;
			$noThumb = true;
	
			echo apply_filters( 'tribe_events_map_the_results', '', get_the_ID() );
			
			// Pagination
			echo apply_filters( 'tribe_events_map_before_pagination', '', get_the_ID() );
			echo apply_filters( 'tribe_events_map_pagination', '', get_the_ID() );
			echo apply_filters( 'tribe_events_map_after_pagination', '', get_the_ID() );
	
		}
	
	echo apply_filters( 'tribe_events_map_after_the_results', '', get_the_ID() );

// End map template
echo apply_filters( 'tribe_events_map_after_template', '', get_the_ID() );